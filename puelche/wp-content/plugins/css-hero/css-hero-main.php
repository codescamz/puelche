<?php
/* Plugin Name: CSS Hero
Plugin URI: csshero.org
Description: Bringing the future of interactive Web design to a WordPress near you. Requires Wp 3.5+
Version: 3.2
Author: The CSS Hero Team
Author URI: csshero.org
License: Commercial
*/ 

require_once ("storage_engine.php");
 

add_filter('upload_mimes', 'csshero_enable_extended_upload');
function csshero_enable_extended_upload ( $mime_types =array() ) {  $mime_types['css']  = 'text/css'; return $mime_types;}

add_action( 'admin_head', 'csshero_print_launch_icon_styles' ); // on backend area
add_action( 'wp_head', 'csshero_print_launch_icon_styles' ); // on frontend area
function csshero_print_launch_icon_styles(){
	if(!is_user_logged_in() OR !current_user_can('edit_theme_options')) return;
	?>
	<style>
		#csshero-icon-launch-editing:before {
			content: '\f464';
			top: 5px;
			position: relative;
			font: 400 20px/1 dashicons;
			speak: none;
			padding: 0 0;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			background-image: none!important;
			margin-right: 5px;
		}
	</style>
	<?php
}

// Place in admin menu a trigger
add_action('admin_bar_menu', 'csshero_add_toolbar_items', 100);
function csshero_add_toolbar_items($admin_bar){
	if ( !current_user_can('edit_theme_options') OR !wpcss_check_license()  	) return;
	
	$admin_bar->add_menu( array(
								'id'    => 'wpcss-css-hero-go',
								'title' => '<span id="csshero-icon-launch-editing"> </span>'.'Customize with CSS Hero',
								'href'  => csshero_get_trigger_url() 
							));
}

function csshero_get_trigger_url() {
	$the_url=get_bloginfo('wpurl');
	if(is_admin() && isset($_GET['post']) && is_numeric($_GET['post'])) $the_url=get_permalink($_GET['post']);

	if(!is_admin()) { global $wp; $the_url = home_url(add_query_arg(array(),$wp->request)); } //v3
	//if (is_singular() ) { global $wp_query; $the_url=get_permalink($wp_query->post->ID); } //v2
	//if (is_singular() && isset($_GET['p']) )	$the_url=get_permalink($_GET['p']); //old v1

	return esc_url   (   add_query_arg(array( 'csshero_action'=>'edit_page', 'rand'=> (rand(0,1024)) ), $the_url ) );
}

function wpcss_current_theme_slug(){	$theme_name = wp_get_theme(); return sanitize_title($theme_name);}

function wpcss_handle_actions(){		 		
		
		
		//HISTORY OR PRESET LIST AJAX LOADING RESULT CASE
		if (  (  current_user_can('edit_theme_options') or   function_exists('chpr_roller_is_allowed_to_all') ) && isset($_GET['csshero_action']) && $_GET['csshero_action']=='list_saved_snapshots' && isset($_GET['snapshot_type']) ) 
		
		{
		  $wpcss_history_steps_array=csshero_get_saved_steps('frontend-css-step',$_GET['snapshot_type']);
		  
		  
		  if ($wpcss_history_steps_array)
			{ //list is not empty
			echo '<div id="csshero-old-history-wrap">';
			foreach($wpcss_history_steps_array as $history_element):
				 if ($history_element->step_active_flag=='yes' ) { $activeflag="csshero-active-history-element";     } else {$activeflag="csshero-non-active-history-element";}
				 ?>
				 <div class='csshero-step csshero-step-type-old <?php echo $activeflag; ?>' id='csshero-step-id-<?php echo $history_element->step_id ?>' data-csshero-step-id='<?php echo $history_element->step_id ?>'  data-json-load-url='?csshero_action=get_snapshot&step_id=<?php echo $history_element->step_id ?>'>
					<span class="timestamp">
						<?php
						$step_date = date("d.m - H:i:s", strtotime($history_element->step_time));
						echo $step_date;  
						?>
				
					<?php if (  $history_element->step_active_flag=='no' && ($_GET['snapshot_type']=='preset' OR $history_element->step_type=='history-step') ): ?>
					 <!-- a target="_blank" class="delete-saved-step-trigger csshero-ajax-self-action"  href='?csshero_action=delete_snapshot&step_id=<?php echo $history_element->step_id ?>'>Delete</a> -->
					 <!-- <a class="activate-saved-step-trigger" href=''>Load</a> -->
					<?php endif ?>
					</span>
				 </div>
			<?php
			endforeach;
		  
			echo '</div>';
			}
		  else echo "<p style='padding:10px';>None yet.</p>"; //list  is empty
		  
		  die;
		} //END CASE
		
		
		//EDITOR   LOADING FOR DEMO CASE //can be killed in your local install
		if (function_exists('csshero_demo_plugin_is_active')):
		
			    if (isset($_GET['csshero_action']) && $_GET['csshero_action']=='edit_page')  {require_once('edit-page.php'); exit;}
					   
		endif; //END CASE
		
		

		//FOLLOWING ACTIONS ARE ONLY FOR LOGGED IN USERS THAT  can edit_theme_options //////////////////////////////////////////////////////////////
		if (!is_user_logged_in() OR !current_user_can('edit_theme_options')  ) return; //quit function if user cannot edit_theme_options
		 
		//IN ADMIN:
		//CHECK IF PRODUCT ACTIVATED, OR SHOW NOTICE
		if ( is_admin()  && !wpcss_check_license()) {add_action( 'admin_notices', 'wpcss_hero_admin_notice' ); return;}
		
		//reset cookie if gone to wp-admin, forcing stop editing
		if ( is_admin() && csshero_is_editing_page()  ) setcookie('csshero_is_on', 0, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);

		//IN FRONTEND:
		//DELETE LICENSE
		if (!isset($_POST['wpcss_submit_form']) && isset($_GET['wpcss_action']) && $_GET['wpcss_action']=='delete_license') {delete_option('csshero-license');wp_redirect(admin_url()); }
		
		//CHECK LICENSE DEBUG
		if (!isset($_POST['wpcss_submit_form']) && isset($_GET['wpcss_action']) && $_GET['wpcss_action']=='check_license') { echo wpcss_check_license(); die; }
		

		///// SAVING CASES //////////
		
		//THEME RESET case
		if (isset($_GET['csshero_action']) && $_GET['csshero_action']=='upload_image') {include('image_upload.php');die;}
		
		
		//THEME RESET case
		if (isset($_GET['wpcss_action']) && $_GET['wpcss_action']=='reset')
		  {
			//SAVE TO NEW STORAGE AS  A NEW EMPTY STEP
			csshero_storage_save_new_step('Theme Reset '. date('h:i:s a m/d/Y', time()) , '');
			 
			//DELETE old static CSS file
			$old_uploaded=get_option('csshero_static_css_data_'.wpcss_current_theme_slug());
			if (is_array($old_uploaded) && isset($old_uploaded['file']) ) unlink($old_uploaded['file']);
			
			//SAVE THE CSS  static file
			$uploaded= wp_upload_bits( "csshero-static-style.css", FALSE, '' );
			
			if ($uploaded['error']==FALSE) {
				 //echo "<h3>Uploaded ok</h3> File URL : ".$uploaded['url'];
				 $uploaded['relative_upload_path'] = _wp_relative_upload_path( $uploaded['file'] );
				 csshero_update_option('csshero_static_css_data_'.wpcss_current_theme_slug(),$uploaded);
			 } else { echo " <h1 style='text-align:center'>Error saving CSS file:".$uploaded['error']." to your uploads directory.</h1>"; }
			 
			wp_redirect(add_query_arg( array('wpcss_action' => false ) ));die;
		  } // end case
		  
		/*  
		//REMOTE: SHARE PRESET TO CLOUD
		if (isset($_GET['wpcss_action']) && $_GET['wpcss_action']=='share_preset')  { wpcss_share_current_preset(); }
		*/
		
		//MAIN SAVE CSSHERO DATA main case//////////////////////////////																
		if (isset($_POST['wpcss_submit_form'])  && $_POST['wpcss_submit_form']==1  &&  isset($_POST['csshero-livearray-saving-field']))
		  {	
			//SECURITY FIRST
			if ( empty($_POST)  or  !wp_verify_nonce($_POST['csshero_saving_nonce_field'],'csshero_saving_nonce') )  { print '<h1>Sorry, your nonce did not verify.</h1>';  exit; }
					
			//GET FROM $_POST CURRENT SETTINGS   
			$wpcss_current_settings_array=  wp_unslash ($_POST['csshero-livearray-saving-field']); 
			//print_r($wpcss_current_settings_array); //useful for debug  
			
			//SAVE TO STORAGE AS  A NEW STEP
			csshero_storage_save_new_step(date('h:i:s a m/d/Y', time()), $wpcss_current_settings_array);
			
			//DELETE old static CSS file
			$old_uploaded=get_option('csshero_static_css_data_'.wpcss_current_theme_slug());
			if (is_array($old_uploaded) && isset($old_uploaded['file']) ) unlink($old_uploaded['file']);
			
			//SAVE THE CSS  static file
			$uploaded= wp_upload_bits( "csshero-static-style.css", FALSE, wp_unslash( $_POST['csshero-css-saving-field']) );
			
			if ($uploaded['error']==FALSE) {
				 //echo "<h3>Uploaded ok</h3> File URL : ".$uploaded['url'];
				 $uploaded['relative_upload_path'] = _wp_relative_upload_path( $uploaded['file'] );
				 update_option('csshero_static_css_data_'.wpcss_current_theme_slug(),$uploaded);
			 } else {  die ("Error saving CSS file to your uploads directory. ".$uploaded['error']);	}
			 
			//ALL IS SAVED, PRINT STATE
			if ($wpcss_current_settings_array==csshero_get_configuration_array()) die("Saved"); else die("SaveProblems");

		  } ///end SAVE CSSHERO DATA case


		//SAVE QUICK CONFIG
		/*
		if (isset($_POST['wpcss_submit_quick_config_form']) && $_POST['wpcss_submit_quick_config_form']==1)
		  {
			//NONCE SECURITY CHECK 
			if ( empty($_POST)  or !wp_verify_nonce($_POST['csshero_saving_nonce_field'],'csshero_saving_nonce') ) { print '<h1>Sorry, your nonce did not verify.</h1> qc01';  exit; }	
			
			$wpcss_new_quick_config=addslashes($_POST['wp-css-config-quick-editor-textarea']);
			
			//add sanitization
			csshero_update_option('wpcss_quick_config_settings_'.wpcss_current_theme_slug(),$wpcss_new_quick_config);
			
			echo "Config Saved";die;
		  } //end case
		*/

		/*
		//GET REMOTE PRESETS & STORE LOCALLY	case																
		if (isset($_GET['wpcss_remote_get_preset'])  )
		
		  {
			if ( empty($_POST)  or  !wp_verify_nonce($_POST['csshero_saving_nonce_field'],'csshero_saving_nonce') )  { print '<h1>Sorry, your nonce did not verify.</h1>';  exit; }		
			 
			//print_r($_POST);
			$wpcss_current_settings_array= unserialize( base64_decode($_POST['preset_data']));
			
			//SAVE TO STORAGE AS  A NEW STEP
			csshero_storage_save_new_step($_POST['preset_name'], $wpcss_current_settings_array  );
			
		   ?> <script>  parent.window.location.href = "<?php echo get_bloginfo('url') ?>";  </script><?php
			 die("<h1>Activating preset... </h1>");
		
		  } //END GET REMOTE PRESETS CASE
		*/
		
		
		//GET A LOCAL PRESET / HISTORY STEP data
		if (!isset($_POST['wpcss_submit_form']) && isset($_GET['csshero_action']) && $_GET['csshero_action']=='get_snapshot')
		  {
			//global $wpcss_current_settings_array; //sara letto dopo dal ciclo dei font della header percio va settato bene
			$step_id=$_GET['step_id'];
			if (!is_numeric($step_id)) die ("<h1>Invalid step id, not numeric!");
			
			echo csshero_get_configuration_array($step_id);
			die;
			
		  } //end case


		//DELETE LOCAL PRESET CASE
		if (!isset($_POST['wpcss_submit_form']) && isset($_GET['csshero_action']) && $_GET['csshero_action']=='delete_snapshot')
		  {
			if (!is_numeric($_GET['step_id'])) die ("<h1>Invalid step id, not numeric!");
			csshero_storage_delete_step($_GET['step_id']);
			die(" &nbsp; Snapshot  deleted.");
		  } //end delete case
		
		
		//DELETE ALL HISTORY CASE
		if (!isset($_POST['wpcss_submit_form']) && isset($_GET['csshero_action']) && $_GET['csshero_action']=='delete_history_snapshots')
		  {
		   
			$wpcss_history_steps_array=csshero_get_saved_steps('frontend-css-step','history');
			if ($wpcss_history_steps_array)
			  foreach($wpcss_history_steps_array as $history_element):
					 if ($history_element->step_active_flag!='yes' ) csshero_storage_delete_step($history_element->step_id); 
			  endforeach;
			   
			die("History Snapshots deleted.");
		  }
		  
		  
		//SAVE AS PRESET case
		/*
		if (!isset($_POST['wpcss_submit_form']) && isset($_GET['wpcss_action']) && $_GET['wpcss_action']=='rename_snapshot')
		
		  {
			if (csshero_storage_mark_active_step_as_preset($_GET['newname'],'frontend-css-step')) echo "Preset Saved To Site Database.";
			
			die;
		  }
		  */

		
		//EDIT PAGE LOADING case
		if (isset($_GET['csshero_action']) && $_GET['csshero_action']=='edit_page')  {require_once('edit-page.php'); exit;}
						
		//CSSHERO SHUTDOWN case	
		if (isset($_GET['csshero_action']) && $_GET['csshero_action'] =="shutdown") {
			setcookie('csshero_is_on', 0, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
			wp_redirect(add_query_arg( array('csshero_action' => false ) ));die;
		}
		
		//WHEN CSSHERO IS ON ELIMINATE WP ADMIN BAR WHEN PERFORMING EDITING ACTIONS
		if (csshero_is_editing_page() ) { add_filter('show_admin_bar', '__return_false'); add_filter( 'edit_post_link', '__return_false' ); }
	 
	  	//LEGACY IMPORT
		/*
		if (isset($_GET['csshero_action']) && $_GET['csshero_action']=='import_legacy_history')  {csshero_import_old_history_and_presets_from_wpoptions(); exit; }
		if (isset($_GET['csshero_action']) && $_GET['csshero_action']=='dont_import_legacy_history')  {csshero_dontimport_history(); exit; }
		*/
		
		
	 } //end handle actions func
	 
	 
add_action ('wp_loaded','wpcss_handle_actions');
 

function wpcss_admin_actions(){
  

		//GET REMOTE LICENSE
		
		if (!isset($_POST['wpcss_submit_form'])  && isset($_GET['wpcss_action']) && $_GET['wpcss_action']=='get_license'&& get_option('wpcss_accept_license')=='yes' && is_user_logged_in()&& current_user_can('edit_theme_options'))
					{ 
									csshero_update_option('csshero-license',$_GET['license_key']);
			
									delete_option('wpcss_accept_license');
															
									$license=wpcss_check_license();
									if ($license!=FALSE) { ?>
													  <body style="padding: 0; margin: 0; background: #f0f4f3;">
													  <div style="margin: 0; padding:10px 100px; ">	 
															
																<img src="http://www.csshero.org/production-v3/activation-img/new_courtesy_page.png" alt="CSS Hero Activated" style="display:block; margin: 0 auto; max-width:600px" />														
																
																<a href="<?php echo esc_url(admin_url()) ?>">
																<img src="http://www.csshero.org/production-v3/activation-img/new_courtesy_btn.png" alt="CSS Hero Activated" style="display:block; margin: 20px auto 0; max-width:190px" />
															</a>			
													  </div>
													</body>	
														<?php die;
							}
									
									else {
												$redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
												$redirect_url_array=explode('?',$redirect_url);
												$redirect_url=$redirect_url_array[0];
												wp_redirect($redirect_url.'/?wpcss_message=activation_invalid');
										  }
									
										die;
										
					}
									
		 
		 
} //end func
 
add_action( 'admin_init', 'wpcss_admin_actions', 1 );
 
   

function wpcss_hero_admin_notice() {
	csshero_update_option('wpcss_accept_license','yes');
 	$data=array( 'admin_url'=>admin_url(), 'url' => get_bloginfo('wpurl'), 'email' => get_bloginfo('admin_email'),'product'=>'CSSHEROv3');
	$encoded_data= rawurlencode( base64_encode( serialize($data)));
    ?>
    <div class="updated">
			<h2> Welcome to CSSHero.</h2>
			<p> Let's activate your product. It's fast and easy! Click the button and let's go.</p>
			<a id="csshero-get-license-button" class="button button-primary button-hero " href="#">Get my key now!</a></p>
    </div>
	<script>
		var remote_activation_url = "https://www.csshero.org/request-license/?v=2&data=<?php echo $encoded_data ?>&admin_url_js_btoa=" + encodeURIComponent(btoa(window.location));
		document.getElementById('csshero-get-license-button').setAttribute("href",remote_activation_url);
	</script>
    <?php
}
 
function csshero_get_style_url() {
	$old_uploaded=get_option('csshero_static_css_data_'.wpcss_current_theme_slug());
	if (is_array($old_uploaded) && isset($old_uploaded['file']) ) {
		//we have some data
		//new version
		$wp_upload_dir=wp_upload_dir();
		if(isset($old_uploaded['relative_upload_path'])) return $wp_upload_dir['baseurl'] .'/'.$old_uploaded['relative_upload_path'];
		//classic fallback
		return ($old_uploaded['url']);
		}
	else return "#";
}
     
function csshero_is_editing_page() {
	if(!is_user_logged_in() OR !current_user_can('edit_theme_options')) return FALSE;
	return (isset($_COOKIE['csshero_is_on']) && $_COOKIE['csshero_is_on']==1);
}

///INCLUDE THE USER GENERATED CSS STATIC FILE
//add_action('wp_enqueue_scripts', 'csshero_add_scripts');  
add_action('wp_print_styles', 'csshero_add_scripts'); //load'em later
function csshero_add_scripts() { 
	//uncomment following line to readd prefixfree. It's 2018 guys let's skip that generally
	//if (!csshero_is_editing_page() && !function_exists('csshero_demo_plugin_is_active')) wp_enqueue_script('prefixfree', plugins_url('/assets/js/prefixfree.min.js', __FILE__)); //prefix free. Thanks Lea, you're a star!
	 
	//add the csshero main static stylesheet to the page
	if (current_user_can('edit_theme_options')) $version = rand(1,1024); else $version=null;
	wp_register_style( 'csshero-main-stylesheet', csshero_get_style_url(), array(), $version); //add  
	wp_enqueue_style( 'csshero-main-stylesheet' );
   
}

//add attribute to stylesheet
//add_filter('style_loader_tag', 'wpcss1581_add_data');
function wpcss1581_add_data($src) {
	
	$pin="id='csshero-main-stylesheet-css'";
	if (!csshero_is_editing_page()  ) $extra="data-apply-prefixfree"; else $extra=""; 
	return str_replace($pin,$pin . ' '.$extra.' class="csshero-main-stylesheet-css" ', $src);
}

/*

function wpcss_share_current_preset()
{
	//$the_data=(base64_encode( (get_option('wpcss_current_settings_array_'.wpcss_current_theme_slug()))));
	$the_data=base64_encode(serialize(csshero_get_configuration_array()));
	$args = array(
	'body' => array( 'site_url' => get_bloginfo('url'), 'preset_data' => $the_data, 'preset_name' => $_GET['preset_name'],'user_id' => $_GET['user_id'],  'theme_slug'=>wpcss_current_theme_slug()), 'license'=>wpcss_check_license(),
	'user-agent' => 'Css Hero'
	);
	
	$resp = wp_remote_post( 'http://csshero.org/share-preset', $args );
	print_r($resp[body]);
	die();//"Your preset has been saved."
}
*/
  
function wpcss_check_license()
{
  $license= get_option('csshero-license');
   if ($license !=FALSE && strlen($license)>10)
			{ return $license; 	}
		else return FALSE;
 
}
 



 
// THIS GIVES US SOME OPTIONS FOR STYLING THE upload ADMIN AREA
function csshero_custom_upload_style() {
      echo '<style type="text/css">
       							.ml-submit, .theme-layouts-post-layout, tr.post_title , tr.align , tr.image_alt, tr.post_excerpt, tr.post_content ,tr.url{display:none}
						  		
						  		
						  		td.savesend{text-align: right;}
						  		tr.submit .savesend input:hover,
						  		tr.submit .savesend input {background: url(http://www.csshero.org/csshero-nano-service/assets/img/esprit.png) no-repeat 0px -862px; height: 70px !important; z-index:999;border: 0px;padding:0px;width: 208px;border-radius: 0px;-moz-border-radius: 0px;-webkit-border-radius: 0px; text-indent: -9999px;}
						  		#media-upload a.del-link:active,
						  		tr.submit .savesend input:active{position: relative; top: 1px;}
								
								#media-upload a.del-link:hover,
								#media-upload a.del-link{height: 70px; width: 101px; background: url(http://www.csshero.org/csshero-nano-service/assets/img/esprit.png) no-repeat -208px -862px; display: inline-block; float: right; margin: 0px 2px 0px 10px; text-indent: 999px;}
								
								
								tr.submit{border-top: 1px solid #dfdfdf;}
								tr.submit .savesend{padding-top: 15px;}
								
								div#media-upload-header{padding: 0px; border: 0px; background: #222; position: fixed; top: 0px; left: 0px; width: 100%; height: 48px; z-index: 9999;}
								#sidemenu a.current {padding-left: 20px; padding-right: 20px; font-weight: normal; text-decoration: none; background: #3e7cff; color: white;-webkit-border-top-left-radius: 0px;-webkit-border-top-right-radius: 0px;border-top-left-radius: 0px;border-top-right-radius: 0px;border-width: 0px;}
								#sidemenu a{padding: 10px 20px; border: 0px; background: transparent; color: white; font-size: 10px; text-transform: uppercase;}
								body#media-upload{padding-top: 50px; background: #f5f5f5; height: 100%;}
								body#media-upload ul#sidemenu{bottom: 0; margin: 0px; padding: 0px;}
								#sidemenu a:hover{background:#222;}
								h3.media-title{font-family: sans-serif; font-size: 10px; font-weight: bold; text-transform: uppercase;}
								h3.media-title,.upload-flash-bypass,.max-upload-size{display: block;text-align: center;}
								.upload-flash-bypass{margin-top: 20px;}
								.max-upload-size{margin-bottom: 20px;}
								#sidemenu li#tab-type_url,
								#sidemenu li#tab-grabber{display: none;}
         </style>';
}

if (isset($_GET['csshero_upload']) && $_GET['csshero_upload']==1) add_action('admin_head', 'csshero_custom_upload_style');

function wpcss_active_site_plugins() {
	$out="";
  $the_plugs = get_option('active_plugins'); 
	
    if ($the_plugs) foreach($the_plugs as $key => $value) {
        $string = explode('/',$value); // Folder name will be displayed
        $out.=$string[0] .',';
    }
	
	 $the_network_plugs=get_site_option('active_sitewide_plugins');
 
	 if ($the_network_plugs)  foreach($the_network_plugs as $key => $value) {
        $string = explode('/',$key); // Folder name will be displayed
        $out.=$string[0] .',';
    }
    return $out;
}

 
function csshero_update_option($option_name,$new_value)
{
		  if ( get_option( $option_name ) !== false ) {
		  
			  // The option already exists, so we just update it.
			  update_option( $option_name, $new_value );
		  
		  } else {
		  
			  // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
			  $deprecated = null;
			  $autoload = 'no';
			  add_option( $option_name, $new_value, $deprecated, $autoload );
		  }
}
 

//////////////////////////////RSS FEED UPDATES BOX ///////////////////
function csshero_register_widgets() {
	global $wp_meta_boxes;
	if (get_option('wpcss_hidedashnews')!=1)
	    wp_add_dashboard_widget('widget_cssheronews', __('From  the  CSS Hero world', 'csshero'), 'csshero_create_rss_box');
}
add_action('wp_dashboard_setup', 'csshero_register_widgets');

function csshero_make_url_https ($string) { return str_replace('http://','https://',$string); }
function csshero_create_rss_box() {
	
	// Get RSS Feed(s)
	include_once(ABSPATH . WPINC . '/feed.php');
	
	// My feeds list (add your own RSS feeds urls)
	$my_feeds = array( 
				'https://www.csshero.org/feed/' 
				);
	
	// Loop through Feeds
	foreach ( $my_feeds as $feed) :
	
		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( $feed );
		if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
		    // Figure out how many total items there are, and choose a limit 
		    $maxitems = $rss->get_item_quantity( 3 ); 
		
		    // Build an array of all the items, starting with element 0 (first element).
		    $rss_items = $rss->get_items( 0, $maxitems ); 
	
		    // Get RSS title
		    $rss_title = '<a href="'.$rss->get_permalink().'" target="_blank">'.strtoupper( $rss->get_title() ).'</a>'; 
		endif;
	
		// Display the container
		?>
		<style>
		#csshero-blog-feed img { margin: 0 auto;display: block}
		#csshero-blog-feed li {border-bottom: 1px solid #ccc}
		#csshero-blog-feed li p:nth-of-type(2) {display: none}
		</style>
		<?php 
		echo '<div class="rss-widget" id="csshero-blog-feed">';
		//echo '<strong>'.$rss_title.'</strong>';
		echo "<img style='float:left; margin:0 5px' src='https://www.csshero.org/wp-content/uploads/2015/05/diamond.jpg' width='64' height='64' /> <strong>Start earning now! Become a CSS Hero affiliate</strong><br />
		Earn 40% on each sale right now.
		Spread the CSS Hero word and share the wealth with us! </p>
		<a target='_blank' href='http://www.csshero.org/affiliate/' style='float:right' class='button button-primary'>Start Earning</a>
		<div style='clear:both'></div>"; 
			 
		echo '<hr style="border: 0; background-color: #DFDFDF; height: 1px;">';
		echo "<strong>Latest News</strong>"; 
		// Starts items listing within <ul> tag
		
		echo '<ul>';
		
		// Check items
		if ( $maxitems == 0 ) {
			echo '<li>'.__( 'No item', 'rc_mdm').'.</li>';
		} else {
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) :
				// Uncomment line below to display non human date
				//$item_date = $item->get_date( get_option('date_format').' @ '.get_option('time_format') );
				
				// Get human date (comment if you want to use non human date)
				$item_date = human_time_diff( $item->get_date('U'), current_time('timestamp')).' '.__( 'ago', 'rc_mdm' );
				
				// Start displaying item content within a <li> tag
				echo '<li>';
				// create item link
				echo '<a href="'.esc_url( $item->get_permalink() ).'" title="'.$item_date.'">';
				// Get item title
				echo esc_html( $item->get_title() );
				echo '</a>';
				// Display date
				echo ' <span class="rss-date">'.$item_date.'</span><br />';
				// Get item content
				$content =csshero_make_url_https( $item->get_content());
				// Shorten content
				//$content = wp_html_excerpt($content, 240) . ' [...]';
				// Display content
				echo $content;
				// End <li> tag
				echo '</li>';
			endforeach;
		}
		// End <ul> tag
		echo '</ul> </div>';

	endforeach; // End foreach feed
}


///NEXTGEN GALLERY FIX
add_filter('run_ngg_resource_manager',  'wpcss_check_csshero_editpage' );
function wpcss_check_csshero_editpage($valid_request) {    if (!empty($_GET['csshero_action'])) $valid_request = FALSE; return $valid_request; }



//end main csshero file, never close php :)