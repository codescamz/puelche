var _chCDIS=function(a,b,c){a.is("#csshero-animator *")||a.is("#csshero-animator")||a.attr("editableclass",b).attr("editablesuggestion",c).addClass("editable")},csshero_rocket_mode=function(a,b,c,e){var f=["editable","clearfix","clear","hfeed","animatedParent","animate","animated","clr","fa-","lazy","js","sf-","type-","format-","category-","tag-","status","post-","cat-item","page-item","widget-","widget_","menu-item","odd","even","page_item","cat_item","menu_item","byuser","col-","portrait","landscape","bounceIn","bounceInDown","bounceInRight","bounceInUp","bounceInLeft","fadeInDownShort","fadeInUpShort","fadeInLeftShort","fadeInRightShort","fadeInDown","fadeInUp","fadeInLeft","fadeInRight","fadeIn","growIn","shake","shakeUp","rotateIn","rotateInUpLeft","rotateInDownLeft","rotateInUpRight","rotateInDownRight","rollIn","wiggle","swing","tada","wobble","pulse","lightSpeedInRight","lightSpeedInLeft","flip","flipInX","flipInY","go","animatedParent"],g=["menu-","comment-"];b&&(b=jQuery.isArray(b)?b:b.replace(/ /g,"").split(","),f=jQuery.merge(f,b)),c&&(c=jQuery.isArray(c)?c:c.replace(/ /g,"").split(","),g=jQuery.merge(g,c));var h=function(a,b){for(var c=0,d=b.length;d>c;c++)if(a.indexOf(b[c])>-1)return null;return a},i=function(a){data=[],ele_obj=jQuery(a),_chT=ele_obj.prop("tagName"),_chI=ele_obj.attr("id");var b=[];return _chS=ele_obj.attr("class"),_chDS="",_chS?(_chS=_chS.split(" "),_chS.length<100?(jQuery(_chS).each(function(a,c){valid_class=h(c,f),c&&""!=c&&valid_class&&b.push(c)}),_chS="."+b.join("."),_chDS=b.join(" ")):_chS=""):_chS="","."==_chS&&(_chS=""),_chI&&""!=_chI&&h(_chI,g)?(_chDS=_chI,_chI="#"+_chI,_chS=""):_chI="",_chI.indexOf("post-")>-1&&(_chI="",_chDS="Post"),scope=_chT+_chS+_chI,_chDTG=_chT,"A"==_chT&&(_chDTG="Link"),"UL"==_chT&&(_chDTG="List"),"OL"==_chT&&(_chDTG="Ordered List"),"LI"==_chT&&(_chDTG="Item"),"I"==_chT&&(_chDTG="Icon"),"P"==_chT&&(_chDTG="Paragraph"),ele_obj.hasClass("widget")&&(scope=_chT,_chDS=_chDTG),""==_chS&&""==_chI&&(PARENT__chDS=ele_obj.parent().attr("editablesuggestion"),PARENT__chDS||(PARENT__chDS=""),_chDS=PARENT__chDS+" "+_chDTG),("HTML"==_chT||"BODY"==_chT)&&(scope=_chT,_chDS=_chT),identifier_object=[],identifier_object.push({scope:scope,desc:_chDS}),identifier_object},j=function(a,b){return b+=" ",splitstring=b.split(a),fullstring="",jQuery(splitstring).each(function(b,c){b>0&&(fullstring+=a+c)}),new_scope="",new_string=fullstring.replace(/#.+?\s/g,""),new_scope=splitstring[0]+new_string,new_scope},k=function(b){var c=[],d=[];return a&&""!=a?parents=jQuery(b).parentsUntil(a):parents=jQuery(b).parents(),parents.each(function(a,b){element_data=i(b),c.push(element_data[0].scope),d.push(element_data[0].desc)}),fullscope=c.join(" > ",c.reverse()),fulldesc=d.join(" ",d.reverse()),a&&""!=a&&(fullscope=a+" "+fullscope),full_element_data=[],full_element_data.push({fullscope:fullscope,fulldesc:fulldesc}),full_element_data};rocketframe=document,a&&""!=a?(_chCDIS(jQuery(a),a,a),range=jQuery(a+" *",rocketframe)):(_chCDIS(jQuery("body"),"body","Site Body"),range=jQuery("body *",rocketframe)),pseudo_checker=[],range.each(function(a,b){thi=i(jQuery(this)),thi_scope=thi[0].scope,thi_desc=thi[0].desc,t=k(jQuery(this))[0].fullscope,d=k(jQuery(this))[0].fulldesc,t=t+" > "+thi_scope,splits=["widget","sidebar","comment","-meta","aside","menu","nav"],jQuery(splits).each(function(a,b){t.indexOf(b)>-1&&(t=j(b,t))}),_chCDIS(jQuery(this),t,thi_desc)})};