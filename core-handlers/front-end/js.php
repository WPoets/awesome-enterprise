<?php
namespace aw2\js;

\aw2_library::add_service('js','JS Functions',['namespace'=>__NAMESPACE__]);

\aw2_library::add_service('js.minify','Minify JS',['namespace'=>__NAMESPACE__]);

function minify($atts,$content=null,$shortcode){
	
	$string=\aw2_library::parse_shortcode($content);
	
	$minifier = new \MatthiasMullie\Minify\JS();
	$minifier->add($string);
	
	$return_value = $minifier->minify();
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}

\aw2_library::add_service('js.run_on_activity','Load scripts and runs the JS code on user interation',['namespace'=>__NAMESPACE__]);

function run_on_activity($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content)==false)return;
	
	extract(\aw2_library::shortcode_atts( array(
	'src'=>null
	), $atts) );
	
	if(empty($src) && empty($content)) return;

	$chars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
	$uniqueid=\substr( \str_shuffle( $chars ), 0, 12 );
	
	$string='
	<script>
	var app_'.$uniqueid.' = {
        init: function() {
            window.addEventListener("scroll", function() {
                if (window.__'.$uniqueid.' == undefined) {
                    app_'.$uniqueid.'.load();
                }
            });
            window.addEventListener("mousemove", function() {
                if (window.__'.$uniqueid.' == undefined) {
                    app_'.$uniqueid.'.load();
                }
            });
             window.addEventListener("keydown", function() {
                if (window.__'.$uniqueid.' == undefined) {
                    app_'.$uniqueid.'.load();
                }
            });
             window.addEventListener("touchstart", function() {
                if (window.__'.$uniqueid.' == undefined) {
                    app_'.$uniqueid.'.load();
                }
            });
        },';
    
    if(!empty($src)){
		  $string .=' load: function() {
            var script = document.createElement("script");
            script.src = "'.$src.'";
            document.head.appendChild(script);
            script.onload = function() {
            ';
            if(!empty($content)){
				$string .=$content;
			}    
			
           $string .=' window["__he"] = true;
            }
            window["__'.$uniqueid.'"] = true;
        }';
	} else {
		  $string .=' load: function() {
            ';
            if(!empty($content)){
				$string .=$content;
			}    
			
           $string .=' 
            window["__'.$uniqueid.'"] = true;
        }';
	}    
  
    
        
    $string .=' };
    
    app_'.$uniqueid.'.init(); </script>';
	
	$minifier = new \MatthiasMullie\Minify\JS();
	$minifier->add($string);
	
	$return_value = $minifier->minify();
	$return_value=\aw2_library::post_actions('all',$return_value,$atts);
	return $return_value;	
}
