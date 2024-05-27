<?php
function adpInitCustomCols(){
	//Initialize metaboxes
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/metaboxes/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeMetaBoxes = glob('*.json');
		
		if($themeMetaBoxes){
			$metaBoxes = array();
			
			foreach($themeMetaBoxes as $metaBox){
				$fileName = basename($metaBox);
				$metaBoxFile = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/metaboxes/'.$fileName;
				$metaBoxConfig = json_decode(file_get_contents($metaBoxFile),true);
				
				if(isset($metaBoxConfig['fields']) && isset($metaBoxConfig['config'])){
					$metaFields = $metaBoxConfig['fields'];
					foreach($metaBoxConfig['config']['object_types'] as $postType){
						add_filter( 'manage_'.$postType.'_posts_columns', function($columns) use ($metaFields){
							foreach($metaFields as $key=>$fldConfig){
								if(isset($fldConfig['showinadmin']) && $fldConfig['showinadmin']){
									$columns[$key] = $fldConfig['name'];
								}
							}
							
							return $columns;
						} );
						add_action( 'manage_'.$postType.'_posts_custom_column' , function($column, $post_id) use ($metaFields){
							foreach($metaFields as $key=>$fldConfig){
								if(isset($fldConfig['showinadmin']) && $key==$column && $fldConfig['showinadmin']){
									echo get_post_meta($post_id,$key,true);
								}
							}
						}, 10, 2 );
					}
				}
			}
		}
	}
}
add_action( 'cmb2_init', function(){
	//Initialize metaboxes
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/metaboxes/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeMetaBoxes = glob('*.json');
		
		if($themeMetaBoxes){
			$metaBoxes = array();
			$groupFieldsList = array();
			foreach($themeMetaBoxes as $metaBox){
				$fileName = basename($metaBox);
				$metaBoxFile = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/metaboxes/'.$fileName;
				$metaBoxConfig = json_decode(file_get_contents($metaBoxFile),true);
				if(isset($metaBoxConfig['config'])){
					$metaBoxID = isset($metaBoxConfig['config']['id']) ? $metaBoxConfig['config']['id'] : str_ireplace('.json','',$fileName);
					$metaBoxID = META_PREFIX.$metaBoxID;
					$metaBoxConfig['config']['id'] = $metaBoxID;
					if(isset($metaBoxConfig['showon'])){
						$showOnCB = $metaBoxConfig['showon'];
						$metaBoxConfig['config']['show_on_cb'] = function($cmb) use ($showOnCB){
							if(isset($showOnCB['ids'])){
								$idsList = explode(',',$showOnCB['ids']);
								global $post;
								if(in_array($post->ID,$idsList)){
									return true;
								}
							}elseif(isset($showOnCB['templates'])){
								$templatesList = explode(',',$showOnCB['templates']);
								$templateFound = false;
								foreach($templatesList as $template){
									if(is_page_template($template)){
										$templateFound = true;
										break;
									}
								}
								return $templateFound;
							}
							return false;
						};
					}
					
					$metaBoxes[$metaBoxID] = new_cmb2_box($metaBoxConfig['config']);
					if(isset($metaBoxConfig['fields'])){
						foreach($metaBoxConfig['fields'] as $key=>$fldConfig){
							if(isset($fldConfig['showinadmin'])){
								unset($fldConfig['showinadmin']);
							}
							if($fldConfig['type']=='group'){
								if(isset($fldConfig['fields'])){
									$groupFields = isset($fldConfig['fields']) ? $fldConfig['fields'] : array();
									unset($fldConfig['fields']);
									$fldConfig['id'] = isset($fldConfig['id']) ? $fldConfig['id'] : $key;
									
									
									$groupFieldsList[$key] = $metaBoxes[$metaBoxID]->add_field($fldConfig);
									foreach($groupFields as $gFldKey=>$gFldConfig){
										$gFldConfig['id'] = $gFldKey;
										
										$metaBoxes[$metaBoxID]->add_group_field($groupFieldsList[$key],$gFldConfig);
									}
								}
							}else{
								$fldConfig['id'] = $key;
								$metaBoxes[$metaBoxID]->add_field($fldConfig);
							}
						}
					}
				}
			}
		}
	}
	
	//Initialize admin pages
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/adminpages/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeMetaBoxes = glob('*.json');
		
		if($themeMetaBoxes){
			$ret = adpInitAdminPages($themeMetaBoxes);
			//var_dump($ret);
			if(count($ret) > 0){
				$ret1 = adpInitAdminPages($ret,true);
			}
		}
	}
} );
function adpShowHideMetaBox($cmb){
	$currTemplate = get_post_meta($cmb->object_id, '_wp_page_template', true);
	if(in_array($currTemplate,array('farmerTpl.php','farmerTplVer2.php','farmerTplVer3.php'))){
		return true;
	}
	return false;
}
function adpInitAdminPages($themeMetaBoxes,$subpages=false){
	$ret = array();
	if($themeMetaBoxes){
		//var_dump($themeMetaBoxes);
		$metaBoxes = array();
		$groupFieldsList = array();
		foreach($themeMetaBoxes as $metaBox){
			$fileName = basename($metaBox);
			$metaBoxFile = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/adminpages/'.$fileName;
			$metaBoxConfig = json_decode(file_get_contents($metaBoxFile),true);
			if(isset($metaBoxConfig['config'])){
				if(!$subpages && isset($metaBoxConfig['config']['parent_slug'])){
					$ret[] = $metaBox;
					continue;
				}elseif($subpages && !isset($metaBoxConfig['config']['parent_slug'])){
					continue;
				}
				
				$metaBoxID = isset($metaBoxConfig['config']['id']) ? $metaBoxConfig['config']['id'] : str_ireplace('.json','',$fileName);
				$metaBoxID = META_PREFIX.$metaBoxID;
				$metaBoxConfig['config']['id'] = $metaBoxID;
				$metaBoxConfig['config']['object_types'] = array('options-page');
				
				$metaBoxes[$metaBoxID] = new_cmb2_box($metaBoxConfig['config']);
				
				if(isset($metaBoxConfig['fields'])){
					foreach($metaBoxConfig['fields'] as $key=>$fldConfig){
						if($fldConfig['type']=='group'){
							if(isset($fldConfig['fields'])){
								$groupFields = isset($fldConfig['fields']) ? $fldConfig['fields'] : array();
								unset($fldConfig['fields']);
								$fldConfig['id'] = isset($fldConfig['id']) ? $fldConfig['id'] : $key;
								
								
								$groupFieldsList[$key] = $metaBoxes[$metaBoxID]->add_field($fldConfig);
								foreach($groupFields as $gFldKey=>$gFldConfig){
									$gFldConfig['id'] = $gFldKey;
									
									$metaBoxes[$metaBoxID]->add_group_field($groupFieldsList[$key],$gFldConfig);
								}
							}
						}else{
							$fldConfig['id'] = $key;
							$metaBoxes[$metaBoxID]->add_field($fldConfig);
						}
					}
				}
			}
		}
	}
	return $ret;
}

function adpGetOptionPageData($metaid,$key = '', $default = false){
	if ( function_exists( 'cmb2_get_option' ) ) {
		return cmb2_get_option( $metaid, $key, $default );
	}
	$opts = get_option( $metaid, $default );
	$val = $default;
	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}

add_shortcode('adpMetaForm',function($atts){
	ob_start();
	$metaBoxID = isset($atts['metaboxid']) ? $atts['metaboxid'] : '';
	$objectID = isset($atts['objectid']) ? $atts['objectid'] : '';
	adpMetaForm($metaBoxID, $objectID);
	return ob_get_clean();
});
function adpMetaForm($metaBoxID, $objectID){
	global $post;
	if ( ! current_user_can( 'edit_posts' ) ) {
		echo 'You do not have permissions to edit this post.';
	}
	if ($objectID=='') {
		$objectID = $post->ID;
	}
	
	if ( empty( $metaBoxID ) ) {
		echo "Please add a 'metaboxid' attribute to specify the metabox to display";
	}else{
		$objectID = (int) $objectID;
		echo cmb2_get_metabox_form(META_PREFIX.$metaBoxID,$objectID);
	}
}