<?php
/*
Plugin Name: adpCore
Plugin URI: https://appsdevpk.com/
Description: adpCore
Version: 1.0.0
Author: AppsDevPk
Author URI: https://appsdevpk.com
License: GPLv2 or later
*/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('APP_DIRECTORY','AdpApp');
define('META_PREFIX','adp_');

include "vendor/autoload.php";
include "cmb2/init.php";

use eftec\bladeone\BladeOne;
use eftec\bladeonehtml\BladeOneHtml;

class adpBlade extends BladeOne{
    use BladeOneHtml;
	
	protected function compileAuth($expression = ''): string{
        $expression = $this->stripParentheses($expression);
        if ($expression) {
            $roles = '"' . implode('","', explode(',', $expression)) . '"';
            return $this->phpTag . "if(isset(\$this->currentUser) && in_array(\$this->currentRole, [$roles])): ?>";
        }
        return $this->phpTag . 'if(isset($this->currentUser)): ?>';
    }
}

add_action('init',function(){
	adpInitShortcodes();
	adpInitPostTypesAndTaxonomies();
});
function adpInitPostTypesAndTaxonomies(){
	//Init custom post types
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/posttypes/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themePostTypesFiles = glob('*.json');
		
		if($themePostTypesFiles){
			foreach($themePostTypesFiles as $themePostTypeFile){
				$fileName = basename($themePostTypeFile);
				$postTypeName = str_ireplace('.json','',$fileName);
				$configFileUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/posttypes/'.$fileName;
				$postTypeConfig = json_decode(file_get_contents($configFileUrl),true);
				if(isset($postTypeConfig['singular']) && isset($postTypeConfig['plural'])){
					$extraArgs = isset($postTypeConfig['extraArgs']) ? $postTypeConfig['extraArgs'] : array();
					adpRegisterPostTypes($postTypeName,$postTypeConfig['singular'],$postTypeConfig['plural'], $extraArgs);
				}
			}
		}
	}
	//Init custom taxonomies
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/taxonomies/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeTaxonomiesFiles = glob('*.json');
		
		if($themeTaxonomiesFiles){
			foreach($themeTaxonomiesFiles as $themeTaxonomyFile){
				$fileName = basename($themeTaxonomyFile);
				$taxonomyName = str_ireplace('.json','',$fileName);
				$configFileUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/taxonomies/'.$fileName;
				$postTypeConfig = json_decode(file_get_contents($configFileUrl),true);
				if(isset($postTypeConfig['singular']) && isset($postTypeConfig['plural'])){
					adpRegisterTaxonomy($taxonomyName,$postTypeConfig['posttype'],$postTypeConfig['singular'],$postTypeConfig['plural'], $extraArgs);
				}
			}
		}
	}
}
function adpInitShortcodes(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/shortcodes/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeShortcodesFiles = glob('*.php');
		
		if($themeShortcodesFiles){
			foreach($themeShortcodesFiles as $shortcodeFile){
				$fileName = str_ireplace('.blade','',str_ireplace('.php','',basename($shortcodeFile)));
				add_shortcode($fileName,function($args=array(),$content='',$tag){
					return adpRenderBladeView($tag,'shortcodes',$args);
				});
			}
		}
	}
}
function adpRenderBladeView($view,$viewDir,$data=array()){
	$views = get_stylesheet_directory().'/'.APP_DIRECTORY . '/'.$viewDir;
	$cache = get_stylesheet_directory().'/'.APP_DIRECTORY . '/cache';
	$blade = new adpBlade($views,$cache);
	$blade->pipeEnable = true;
	if(is_user_logged_in()){
		$userid = get_current_user_id();
		$blade->setAuth($userid,adpGetUserRole($userid));
	}
	
	ob_start();
	echo $blade->run($view,$data);
	return ob_get_clean();
}
add_action('wp_head',function(){
	adpIncludeDynamicCss('header');
	adpIncludeDynamicJs('header');
	adpIncludeComponents();
});
add_action('wp_footer',function(){
	$pluginUrl = plugin_dir_url( __FILE__ );
	?>
	<script src="https://cdn.jsdelivr.net/npm/@realmorg/realm/dist/realm.production.min.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/@realmorg/realm/dist/realm.min.css" rel="stylesheet" />
	<script src="<?php echo $pluginUrl; ?>js/scripts.js"></script>
	<style>
		@import "https://unpkg.com/open-props";
	</style>
	<?php
	adpIncludeDynamicCss('footer');
	adpIncludeDynamicJs('footer');
});

add_action( 'cmb2_admin_init', function(){
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

add_action( 'admin_menu', function(){
	add_menu_page( 'Adp Tools', 'Adp Tools', 'manage_options','AdpAdminTools', 'AdpAdminToolsFunc');
	add_submenu_page('AdpAdminTools', 'Libraries', 'Libraries', 'manage_options', 'adpLibariesSearch', 'adpLibariesSearchFunc');
	//add_submenu_page('AdpAdminTools', 'Libraries Presets', 'Libraries Presets', 'manage_options', 'adpLibariesPresetsSearch', 'adpLibariesPresetsSearchFunc');
	add_submenu_page('AdpAdminTools', 'My Libraries', 'My Libraries', 'manage_options', 'adpMyLibaries', 'adpMyLibariesFunc');
	add_submenu_page('AdpAdminTools', 'Conditionals', 'Conditionals', 'manage_options','adpConditionals', 'adpConditionalsFunc');
} );
function AdpAdminToolsFunc(){
	echo '<p>All tools listings here</p>';
}

function adpGetUserRole($userid){
	$user = new WP_User( $userid );
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		return $user->roles[0];
	}
	return '';
}

function adpCreatePostWithData($postObj,$data){
	/*$postObj = array(
		'post_title'    => 'Cache for api id : '.$apiID,
		'post_content'  => 'Cache for api id : '.$apiID,
		'post_status'   => 'publish',
		'post_type'   => 'apicache'
	);*/
	$post_id = wp_insert_post($postObj);
	if(!is_wp_error($post_id)){
		foreach($data as $key=>$val){
			update_post_meta($post_id,$key,$val);
		}
	}
}
if(!function_exists('adpSendApiCall')){
	function adpSendApiCall($apilink, $callType='GET', $data='', $headers='', $debug=false, $isGQL=false, $GQLQuery=''){
		if($isGQL && $GQLQuery!=''){
			$GQLQuery = '{"query":"'.$GQLQuery.'"}';
			$GQLQuery = trim(preg_replace('/\s\s+/', ' ', $GQLQuery));
			$headers[] = 'Content-Type: application/json';
		}
		if($debug){
			echo $apilink.'<br />';
			echo '<pre>';
			var_dump($data);
			var_dump($GQLQuery);
			var_dump($headers);
			echo '</pre>';
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apilink);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if($callType=='POST'){
			curl_setopt($ch, CURLOPT_POST, true);
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $callType);
		}
		$ctype = 'form';
		if(is_array($headers) && count($headers) > 0){
			foreach($headers as $hd){
				$hdParts = explode(':', $hd);
				if(strtolower(trim($hdParts[0]))=='content-type' && trim($hdParts[1])=='application/json'){
					$ctype = 'json';
				}
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		}
		if(is_array($data) && count($data) > 0){
			if($ctype=='json'){
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
			}else{
				curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
			}
		}
		
		if($isGQL && $GQLQuery!=''){
			curl_setopt($ch, CURLOPT_POSTFIELDS,$GQLQuery);
		}
		
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			$result = curl_error($ch);
		}
		curl_close($ch);
		return $result;
	}
	function array_map_assoc( $callback , $array ){
		$r = array();
		foreach ($array as $key=>$value){
			$r[$key] = $callback($key,$value);
		}
		return $r;
	}
	function adpIsAssociativeArray($arr){
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}

function adpGetPostsList($postType,$postsPerPage=2,$extraArgs=array(),$meta=false){
	$ret = array();
	$args = array(
		'post_type'=>$postType,
		'posts_per_page'=>$postsPerPage
	);
	if(is_array($extraArgs) && count($extraArgs) > 0){
		foreach($extraArgs as $key=>$val){
			$args[$key] = $val;
		}
	}
	$posts = get_posts($args);
	if($posts){
		foreach($posts as $post){
			if($meta){
				$ret['Posts'][$post->ID] = array(
					'Post'=>$post,
					'Meta'=>get_post_custom($post->ID)
				);
			}else{
				$ret['Posts'][$post->ID] = array(
					'Post'=>$post
				);
			}
		}
	}
	return $ret;
}
if(!function_exists('adpRegisterTaxonomy')){
	function adpRegisterTaxonomy($tax,$ctype, $singular, $plural){
		// Register Custom Taxonomy
		$labels = array(
			'name'                       => _x( $plural, 'Taxonomy General Name', '' ),
			'singular_name'              => _x( $singular, 'Taxonomy Singular Name', '' ),
			'menu_name'                  => __( ucfirst($plural), '' ),
			'all_items'                  => __( 'All Items', '' ),
			'parent_item'                => __( 'Parent Item', '' ),
			'parent_item_colon'          => __( 'Parent Item:', '' ),
			'new_item_name'              => __( 'New Item Name', '' ),
			'add_new_item'               => __( 'Add New Item', '' ),
			'edit_item'                  => __( 'Edit Item', '' ),
			'update_item'                => __( 'Update Item', '' ),
			'view_item'                  => __( 'View Item', '' ),
			'separate_items_with_commas' => __( 'Separate items with commas', '' ),
			'add_or_remove_items'        => __( 'Add or remove items', '' ),
			'choose_from_most_used'      => __( 'Choose from the most used', '' ),
			'popular_items'              => __( 'Popular Items', '' ),
			'search_items'               => __( 'Search Items', '' ),
			'not_found'                  => __( 'Not Found', '' ),
			'no_terms'                   => __( 'No items', '' ),
			'items_list'                 => __( 'Items list', '' ),
			'items_list_navigation'      => __( 'Items list navigation', '' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);
		register_taxonomy( $tax, array( $ctype ), $args );
	}
}

if(!function_exists('adpRegisterPostTypes')){
	function adpRegisterPostTypes($name,$singular,$plural, $extraArgs=array()){
		$labels = array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'archives'              => $singular.' Archives',
			'attributes'            => $singular.' Attributes',
			'parent_item_colon'     => 'Parent '.$singular,
			'all_items'             => 'All '.$plural,
			'add_new_item'          => 'Add New '.$singular,
			'add_new'               => 'Add New '.$singular,
			'new_item'              => 'New '.$singular,
			'edit_item'             => 'Edit '.$singular,
			'update_item'           => 'Update '.$singular,
			'view_item'             => 'View '.$singular,
			'view_items'            => 'View '.$plural,
			'search_items'          => 'Search '.$singular,
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into '.$singular,
			'uploaded_to_this_item' => 'Uploaded to this '.$singular,
			'items_list'            => $plural.' list',
			'items_list_navigation' => $plural.' list navigation',
			'filter_items_list'     => 'Filter '.$plural.' list',
		);
		$args = array(
			'label'                 => ucfirst($name),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor','comments', 'custom-fields', 'thumbnail' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'show_in_rest' => true,
			'rest_base' => $name,
			'capability_type'       => 'page',
		);
		if(count($extraArgs) > 0){
			foreach($extraArgs as $key=>$val){
				$args[$key] = $val;
			}
		}
		register_post_type( $name, $args );
	}
}
include "lib/contentLib.php";
include "lib/shortcodes.php";
include "lib/libraries.php";
include "lib/conditionals.php";
include "lib/servercomponents.php";
include "lib/conditionalcssjs.php";