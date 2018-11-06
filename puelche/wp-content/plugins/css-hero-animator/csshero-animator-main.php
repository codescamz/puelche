<?php

/* Plugin Name: CSS Hero Animator
	Plugin URI: csshero.org/animator
	Description: An easy interface to animate your WordPress Site elements.
	Version: 0.71
	Plugin Author:  CSS Hero Team
	Author URI: http://www.csshero.org
*/ 

// Place in admin menu a trigger for launching animation editor
add_action('admin_bar_menu', 'chanimator_add_toolbar_items', 110);

function chanimator_add_toolbar_items($admin_bar){
	if (!current_user_can('edit_theme_options') or is_admin()) return;
	$admin_bar->add_menu(array(
		'id'    => 'chanimator-go-trigger',
		'title' => 'CSS Hero Animator',
		'href'  => chanimator_get_trigger_url()
	));
}

function chanimator_get_trigger_url(){
    return esc_url(add_query_arg(array('chanimator_action'=>'edit_page','rand'=> (rand(0,1024)) )));
}

function chanimator_current_theme_slug(){
	$theme_name = wp_get_theme();
	return sanitize_title($theme_name);
}

///ADD SCRIPTS
function chanimator_add_scripts(){    
    
    //ALWAYS
    wp_enqueue_script( 'hero-animations-lib', plugins_url( '/assets/lib/css3-animate-it.js', __FILE__ ), array(), '1.0.0', true );
    wp_enqueue_style( 'hero-animations-lib', plugins_url( '/assets/lib/animations.css', __FILE__ ));
    wp_enqueue_script( 'hero-animator-applier', plugins_url( '/assets/animator-applier.js', __FILE__ ), array(), '1.0.0', true );
    wp_enqueue_script( 'hero-animator', plugins_url( '/assets/animator.js', __FILE__ ), array('hero-rocket-mode','hero-hover-elements','jquery-ui-draggable','jquery-form' ), '1.0.0', true );
	
	$current_array=chanimator_get_step_array();
	if($current_array)
		$json_step_string = (json_encode($current_array));
	else $json_step_string="";
	
	wp_localize_script( 'hero-animator-applier', 'ajax_object',   array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'chanimator_current_step' => $json_step_string ) );
    
	// EXIT IF NOT EDITING - those scripts are included upon editing only:
    if(!current_user_can('edit_theme_options') OR !isset($_GET['chanimator_action']) OR $_GET['chanimator_action']!='edit_page') return;
    
    //WHEN EDITING ONLY
    wp_enqueue_style( 'hero-animator-style', plugins_url( '/assets/animator.css', __FILE__ ));
    wp_enqueue_script( 'hero-rocket-mode', plugins_url( '/assets/chanimator-rocket-mode.js', __FILE__ ), array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'hero-hover-elements', plugins_url('/assets/chanimator-hover-elements.js',__FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script( 'hero-animator', 'ajax_object',   array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'chanimator_current_step' => $json_step_string ) );

} //end scripts func

add_action('wp_enqueue_scripts','chanimator_add_scripts');

function chanimator_on_load_func(){
	if(!current_user_can('edit_theme_options') OR !isset($_GET['chanimator_action']) OR $_GET['chanimator_action']!='edit_page') return;

	//WHEN EDITING ONLY
	add_filter('show_admin_bar', '__return_false');
	add_filter( 'edit_post_link', '__return_false' ); 
}

add_action( 'wp_loaded', 'chanimator_on_load_func' );

function chanimator_add_to_footer() {	
	//EXIT IF NOT EDITING - those scripts are included upon editing only:
    if(!current_user_can('edit_theme_options') OR !isset($_GET['chanimator_action']) OR $_GET['chanimator_action']!='edit_page') return;
    include("assets/animator.html");
}
add_action( 'wp_footer', 'chanimator_add_to_footer' );

//HANDLE AJAX SAVING
add_action( 'wp_ajax_chanimator_save_step', 'chanimator_save_step_func' );
function chanimator_save_step_func(){
	if (!current_user_can("edit_theme_options")) return; //Only for super admins
	if (isset($_POST['csshero-animator-cancel'])){
		echo "Cancelled.";
	}
	if (isset($_POST['csshero-animator-save'])){
		$insert= chanimator_storage_save_new_step(date('h:i:s a m/d/Y', time()), json_decode(stripslashes( ($_POST['csshero-animator-form-sender']))));
		if ($insert) echo "Saved.";
	}
    
	//print_r($_POST);// useful for debug
    
	wp_die(); // this is required to terminate immediately and return a proper response

} //end function


//ANIMATOR STORAGE ENGINE ////////////////////

function chanimator_initialize_storage(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'chanimator';
	
	//check if table is created
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name):
	  
		//TABLE IS NOT CREATED. LET'S CREATE THE TABLE HERE.
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			step_id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			step_time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
			step_type VARCHAR(30) NOT NULL,
			step_name VARCHAR(100) NOT NULL,
			step_data MEDIUMBLOB NOT NULL,
			step_theme VARCHAR(100) NOT NULL,
			step_context VARCHAR(30) NOT NULL,
			step_active_flag VARCHAR(3) NOT NULL,
			UNIQUE KEY step_id (step_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	endif;
} //end function


// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'chanimator_initialize_storage');

//////// SAVING FUNCTIONS ////////////////////////////////
function chanimator_storage_save_new_step($name, $current_settings_array, $context='frontend-animation-step'){
  //check if table exists or create it
  //chanimator_storage_perform_existance_check(); //no need no more: we do it before on read
  
  global $wpdb;
  $table_name = $wpdb->prefix . 'chanimator';
  //insert
  $the_insert=$wpdb->insert( 
		$table_name, 
		array( 
			'step_time' => current_time( 'mysql' ),
			'step_type' =>'history-step',
			'step_name' => $name,
			'step_data' => (gzcompress( serialize($current_settings_array))),
			'step_theme' => chanimator_current_theme_slug(),
			'step_context' => $context,
			'step_active_flag' =>'n-y'
		) 
	);
  
  //if inserted, mark as not active other ones, and bless this one
  if ($the_insert) chanimator_storage_bless_row($wpdb->insert_id,$context);
  
  return $the_insert;
}

function chanimator_storage_bless_row($step_id, $context){
  if (!is_numeric($step_id)) die("<h1>Error in chanimator_storage_bless_row, exiting</h1>");
  
  global $wpdb;
  $table_name = $wpdb->prefix . 'chanimator';
  
  //unbless others
  $wpdb->query(
	"UPDATE $table_name SET step_active_flag = 'no'
	WHERE step_active_flag = 'yes' AND step_context = '$context' AND step_theme='".chanimator_current_theme_slug()."'
	"
  );
  
  //bless me
  $wpdb->update( 
		  $table_name, 
		  array( 
			  'step_active_flag' => 'yes',	// string
		  ), 
		  array( 'step_id' => $step_id ,'step_context'  => $context, 'step_theme' =>chanimator_current_theme_slug() )
	  );
  
} //end func

////READ FUNCTIONS ////////////////////////////////////////////////////////////////

function chanimator_get_step_array($step_id="default",$step_context='frontend-animation-step',$field_name='step_data'){
   
  global $wpdb;
  $table_name = $wpdb->prefix . 'chanimator';
  
  //GET THE DATA FROM DB
  if ($step_id=="default") {
	  $value = $wpdb->get_var( "SELECT $field_name FROM $table_name WHERE step_theme='".chanimator_current_theme_slug()."' AND step_context='".$step_context."' AND step_active_flag='yes' ORDER BY step_id DESC LIMIT 0,1" ); 
	}
    else {
	  if (!is_numeric($_GET['step_id'])) die ("<h1>Invalid step id, not numeric!");     
      $value = $wpdb->get_var( "SELECT $field_name FROM $table_name WHERE step_theme='".chanimator_current_theme_slug()."' AND step_context='".$step_context."' and step_id=".$_GET['step_id'] );
  }
  //EXTRACT THE COMPRESSED DATA
  if ($value) $current_settings_array=unserialize(gzuncompress($value)); else $current_settings_array=array();
  
  if (is_array($current_settings_array) && $current_settings_array==array()) return false;
     
  return $current_settings_array;  
}