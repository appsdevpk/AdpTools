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
define('PLUGIN_URL',plugin_dir_url( __FILE__ ));

include "vendor/autoload.php";
include "cmb2/init.php";

use eftec\bladeone\BladeOne;
use eftec\bladeonehtml\BladeOneHtml;

$widgetsList = [];

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
	adpRegisterPostTypes('adpforms','Adp Form','Adp Forms', array(
		'supports'=>array('title')
	));
	
	adpInitShortcodes();
	adpInitPostTypesAndTaxonomies();
	adpInitCustomCols();
});

add_action( 'add_meta_boxes', function(){
	add_meta_box('adpFormsDesignerMeta','Form Designer','adpFormsDesignerMetaFunc','adpforms');
} );
function adpFormsDesignerMetaFunc($post){
	$pluginUrl = plugin_dir_url( __FILE__ );
	?>
    <script type="text/javascript" src="https://unpkg.com/knockout/build/output/knockout-latest.js"></script>
    <link  href="https://unpkg.com/survey-core/defaultV2.min.css" type="text/css" rel="stylesheet">
    <script src="https://unpkg.com/survey-core/survey.core.min.js"></script>
    <script src="https://unpkg.com/survey-knockout-ui/survey-knockout-ui.min.js"></script>
    
    <link  href="<?php echo $pluginUrl; ?>/js/formcreator/survey-creator-core.min.css" type="text/css" rel="stylesheet">
    <script src="<?php echo $pluginUrl; ?>/js/formcreator/survey-creator-core.min.js"></script>
    <script src="https://unpkg.com/survey-creator-knockout/survey-creator-knockout.min.js"></script>
	<div id="adpFormEditor" style="height: 70vh;"></div>
	<script>
		const creatorOptions = {
			showLogicTab: true,
			isAutoSave: true
		};
		const creator = new SurveyCreator.SurveyCreator(creatorOptions);
		document.addEventListener("DOMContentLoaded", function() {
			creator.render("adpFormEditor");
		});
	</script>
	<?php
}

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
	echo do_shortcode($blade->run($view,$data));
	return ob_get_clean();
}

add_action('wp_head',function(){
	adpIncludeDynamicCss('header');
	adpIncludeDynamicJs('header');
	adpIncludeComponents();
	adpIncludeLiveComponents();
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

/*
Setup custom help tabs in admin
*/
add_action('admin_head', function(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/helptabs/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$helpTabsList = glob('*.php');
		
		if($helpTabsList){
			global $post_ID;
			$screen = get_current_screen();
			
			$metaBoxes = array();
			$groupFieldsList = array();
			foreach($helpTabsList as $helpTab){
				$fileName = str_ireplace('.blade','',str_ireplace('.php','',basename($helpTab)));
				$configPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/helptabs/'.$fileName.'.json';
				$tabTitle = $fileName;
				if(file_exists($configPath)){
					$configUri = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/helptabs/'.$fileName.'.json';
					$configUriCont = json_decode(file_get_contents($configUri),true);
					$tabTitle = $configUriCont['Title'];
				}
				$screen->add_help_tab( array(
					'id' => $fileName,
					'title' => $tabTitle,
					'content' => adpRenderBladeView($fileName,'helptabs')
				));
			}
		}
	}
});

/*
Setup custom dashboard widgets
*/
add_action( 'wp_dashboard_setup', function(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/admindashwidgets/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$dashWidgets = glob('*.php');
		
		if($dashWidgets){
			$metaBoxes = array();
			$groupFieldsList = array();
			foreach($dashWidgets as $dashWidget){
				$fileName = str_ireplace('.blade','',str_ireplace('.php','',basename($dashWidget)));
				$configPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/admindashwidgets/'.$fileName.'.json';
				$widgetTitle = $fileName;
				if(file_exists($configPath)){
					$configUri = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/admindashwidgets/'.$fileName.'.json';
					$configUriCont = json_decode(file_get_contents($configUri),true);
					$widgetTitle = $configUriCont['Title'];
				}
				
				wp_add_dashboard_widget( $fileName, $widgetTitle, function($post, $callback_args) use ($fileName){
					echo adpRenderBladeView($fileName,'admindashwidgets');
				} );
			}
		}
	}
} );
function adpGetMultipleOptions($prefix){
	$ret = array();
	if($prefix!=''){
		$query = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '".$prefix."%'";
		$optionsList = $wpdb->get_col($query);
		if(!empty($optionsList)){
			foreach ($optionsList as $opt) {
				$ret[$opt] = get_option($opt);
			}
		}
	}
}
function adpCreateSelect($name,$options,$defaultVal='',$className=''){
	?>
	<select name="<?php echo $name; ?>" class="<?php echo $className; ?>">
		<?php
		foreach($options as $opt){
			$str = $opt==$defaultVal ? 'selected' : '';
			echo '<option '.$str.'>'.$opt.'</option>';
		}
		?>
	</select>
	<?php
}
add_action( 'admin_menu', function(){
	add_menu_page( 'Adp Tools', 'Adp Tools', 'manage_options','AdpAdminTools', 'AdpAdminToolsFunc');
	add_submenu_page('AdpAdminTools', 'Wizards', 'Wizards', 'manage_options', 'adpWizards', 'adpWizardsFunc');
	add_submenu_page('AdpAdminTools', 'Libraries', 'Libraries', 'manage_options', 'adpLibariesSearch', 'adpLibariesSearchFunc');
	//add_submenu_page('AdpAdminTools', 'Libraries Presets', 'Libraries Presets', 'manage_options', 'adpLibariesPresetsSearch', 'adpLibariesPresetsSearchFunc');
	add_submenu_page('AdpAdminTools', 'My Libraries', 'My Libraries', 'manage_options', 'adpMyLibaries', 'adpMyLibariesFunc');
	add_submenu_page('AdpAdminTools', 'Conditionals', 'Conditionals', 'manage_options','adpConditionals', 'adpConditionalsFunc');
} );
add_action( 'admin_enqueue_scripts', function($hook){
	wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
	wp_enqueue_script( 'js-code-editor', plugin_dir_url( __FILE__ ) . '/code-editor.js', array( 'jquery' ), '', true );
} );
function adpWizardsFunc(){
	global $wizardsConfig;
	$baseUrl = 'admin.php?page=adpWizards&w=';
	?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<style>
		.adpWizardsList{
			list-style-type: none;
			display:flex;
			flex-wrap: wrap;
			margin-top:30px;
		}
		.adpWizardsList li{
			width:30%;
			min-width:300px;
			margin: 0px 10px 10px 0px;
			position: relative;
		}
		.adpWizardsList li a{
			text-decoration: none;
			background:#fff;
			display:block;
			text-align:center;
			font-size:35px;
			color:#000;
			padding: 15px 15px 20px 15px;
			border:1px solid #000;
			transition: all 0.5s
		}
		.adpWizardsList li a:hover{
			background:#000;
			color:#fff;
			border:1px solid #fff;
		}
		.adpWizardsList li a i{
			display: block;
			width: fit-content;
			margin: 10px auto
		}
		.adpWizardsList li a .adpWizardName{
			font-size: 20px;
		}
		.adpArrayFieldSet{
			margin:10px 0px;
			padding:10px;
			border:1px solid #ccc
		}
		.adpFieldHelp{
			font-size:12px;
			margin: 10px 0px 0px 0px;
			color:#555
		}
	</style>
	<?php
	if(isset($_GET['w']) && isset($wizardsConfig['Wizards'][$_GET['w']])){
		$widgetsConfigFields = $wizardsConfig['Wizards'][$_GET['w']]['Fields'];
		if(isset($_REQUEST['btnSubmit'])){
			$wizardFunction = isset($wizardsConfig['Wizards'][$_GET['w']]['FunctionName']) ? $wizardsConfig['Wizards'][$_GET['w']]['FunctionName'] : '';
			if($wizardFunction!='' && function_exists($wizardFunction)){
				$wizardFunction();
				echo '<p><strong>'.$_GET['w'].'</strong> created successfully</p>';
			}
		}
		?>
		<h1>Create <?php echo $_GET['w']; ?></h1>
		<form action="" method="post">
			<table class="form-table">
				<tbody>
					<?php 
					$postTypes = get_post_types(array(
					   'public'   => true,
					   '_builtin' => false
					));
					sort($postTypes);
					foreach($widgetsConfigFields as $key=>$val){
						adpRenderWizardField($key,$val,$postTypes);
					}
					?>
				</tbody>
			</table>
			<input type="submit" name="btnSubmit" id="submit" class="button button-primary" value="Create" />
		</form>
		<script>
			var editorsList = [];
			jQuery('.codeeditor').each(function(ind){
				editorsList[ind] = wp.codeEditor.initialize( jQuery(this) );
			});
			
			jQuery('.adpAddArrayField').click(function(){
				var currElem = jQuery(this);
				var newFieldSet = currElem.parent().find('.adpFieldsetTemplate').html();
				currElem.parent().find('.arrayFieldSubFieldsList').append(newFieldSet);
			});
			jQuery(document).on('click','.adpRemoveArrayField',function(){
				var currElem = jQuery(this);
				if(confirm('Are you sure?')){
					currElem.parent().remove();
				}
			});
		</script>
		<?php
	}else{
		?>
		<div style="margin:20px 0px;text-align:center;padding:10px;border:1px solid #ccc;font-size:20px;">All these wizards will create their corresponding template and/or configuration files inside the app folder in your current theme</div>
		<ul class="adpWizardsList">
			<?php foreach($wizardsConfig['Wizards'] as $key=>$val){ ?>
			<li>
				<a href="<?php echo $baseUrl.$key; ?>">
					<?php echo $val['Icon']; ?>
					<div class="adpWizardName"><?php echo $key; ?></div>
				</a>
			</li>
			<?php } ?>
		</ul>
		<?php
	}
}
function adpRenderWizardField($fldName,$fldOptions,$postTypes,$parseArrayFields=true){
	$fldKey = strtolower(str_ireplace(' ','_',$fldName));
	if(!$parseArrayFields){
		$fldKey .= '[]';
	}
	?>
	<tr>
		<th>
			<label for="<?php echo $fldKey; ?>"><?php echo $fldName; ?></label>
		</th>
		<td>
		<?php
		if($fldOptions['Type']=='text'){
			echo '<input type="text" class="regular-text" id="'.$fldKey.'" name="'.$fldKey.'" />';
		}elseif($fldOptions['Type']=='textarea'){
			echo '<textarea class="large-text code" id="'.$fldKey.'" name="'.$fldKey.'"></textarea>';
		}elseif($fldOptions['Type']=='editor'){
			$editorClass = $parseArrayFields ? 'codeeditor' : '';
			echo '<textarea class="large-text code '.$editorClass.'" id="'.$fldKey.'" name="'.$fldKey.'"></textarea>';
		}elseif($fldOptions['Type']=='post_types'){
			?>
			<select name="<?php echo $fldKey; ?>" id="<?php echo $fldKey; ?>">
				<?php foreach($postTypes as $postType){ ?>
				<option><?php echo $postType; ?></option>
				<?php } ?>
			</select>
			<?php
		}elseif(in_array($fldOptions['Type'],array('array','group')) && $parseArrayFields){
			$subFields = $fldOptions['Fields'];
			$addButtonText = $fldOptions['AddButtonText'];
			?>
			<div class="arrayFieldContainer">
				<div style="display:none" class="adpFieldsetTemplate">
					<div class="adpArrayFieldSet">
						<table class="form-table">
							<tbody>
								<?php 
								foreach($subFields as $subFldName=>$subFldOptions){
									adpRenderWizardField($subFldName,$subFldOptions,$postTypes,false);
								}
								?>
							</tbody>
						</table>
						<input type="button" class="button button-primary adpRemoveArrayField" value="Remove" />
					</div>
				</div>
				<div class="arrayFieldSubFieldsList"></div>
				<input type="button" class="button button-primary adpAddArrayField" value="<?php echo $addButtonText; ?>" />
			</div>
			<?php
		}elseif($fldOptions['Type']=='select'){
			$optionsList = $fldOptions['Options'];
			?>
			<select name="<?php echo $fldKey; ?>" id="<?php echo $fldKey; ?>">
				<?php foreach($optionsList as $key=>$val){ ?>
				<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
			<?php
		}
		if(isset($fldOptions['HelpText'])){
		?>
		<div class="adpFieldHelp"><?php echo $fldOptions['HelpText']; ?></div>
		<?php } ?>
		</td>
	</tr>
	<?php
}
function AdpAdminToolsFunc(){
	$toolsList = json_decode(file_get_contents(PLUGIN_URL.'/tools/adpTools.json'),true);
	?>
	<style>
		#adpToolsList{
			list-style-type: none;
			display:flex;
			flex-wrap: wrap;
			margin-top:30px;
		}
		#adpToolsList li{
			width:30%;
			min-width:300px;
			margin: 0px 10px 10px 0px;
			background:#fff;
			position: relative;
		}
		#adpToolsList li h3{
			margin:0px 0px 10px 0px;
			padding: 10px;
			background:#000;
			color:#fff;
		}
		#adpToolsList li .adpToolDesc{
			padding: 10px;
			height: 100px;
		}
		#adpToolsList li .adpToolTags{
			padding: 10px;
			text-align:center;
			border-top:1px solid #000;
			margin-bottom: 10px;
		}
		#adpToolsList li a{
			padding: 5px 10px;
			text-align:center;
			background:#fff;
			display: inline-block;
			text-decoration: none;
			color:#000;
			margin: 10px;
			border: 1px solid #000
		}
		#adpToolsList li a:hover{
			background:#000;
			color:#fff
		}
	</style>
	<?php 
	$tagsList = array();
	$tagsListCounts = array();
	ob_start(); 
	?>
	<ul id="adpToolsList">
		<?php 
		foreach($toolsList as $tool){ 
			if(!isset($tool['name'])){
				continue;
			}
			$toolClasses = array();
			foreach($tool['tags'] as $tag){
				$toolClasses[] = str_ireplace(' ','-',$tag);
				if(!in_array($tag,$tagsList)){
					$tagsList[] = $tag;
				}
				if(!isset($tagsListCounts[$tag])){
					$tagsListCounts[$tag] = 1;
				}else{
					$tagsListCounts[$tag] += 1;
				}
			}
		?>
		<li class="<?php echo implode(' ',$toolClasses); ?>">
			<h3 class="adpToolName"><?php echo $tool['name']; ?></h3>
			<div class="adpToolDesc"><?php echo $tool['desc']; ?></div>
			<div class="adpToolTags"><?php echo implode(' , ',$tool['tags']); ?></div>
			<a href="<?php echo $tool['url']; ?>" class="openTool">Open Here</a>
			<a href="<?php echo $tool['url']; ?>" target="_blank">Open Separate</a>
		</li>
		<?php } ?>
	</ul>
	<div id="preview" style="display:none">
		<input type="button" id="btnGoBack" value="Back" />
		<iframe id="previewFrame" src="" style="width:100%;border:0px;height:500px"></iframe>
	</div>
	<script>
		jQuery('.openTool').click(function(e){
			e.preventDefault();
			var currElem = jQuery(this);
			jQuery('#adpToolsList').hide();
			jQuery('#previewFrame').attr('src',currElem.attr('href'));
			jQuery('#preview').show();
		});
		jQuery('#btnGoBack').click(function(e){
			jQuery('#previewFrame').attr('src','');
			jQuery('#adpToolsList').show();
			jQuery('#preview').hide();
		});
		jQuery('#adpToolCat').change(function(){
			var currVal = jQuery(this).val();
			if(currVal===''){
				jQuery('#adpToolsList li').show();
			}else{
				jQuery('#adpToolsList li').hide();
				jQuery('#adpToolsList li.' + currVal).show();
			}
		});
	</script>
	<?php
	$output = ob_get_clean();
	echo '<label>Tools Category</label> ';
	echo '<select id="adpToolCat">';
	echo '<option value="">All</option>';
	sort($tagsList);
	foreach($tagsList as $tag){
		$key = str_ireplace(' ','-',$tag);
		echo '<option value="'.$key.'">'.$tag.' ('.$tagsListCounts[$tag].')</option>';
	}
	echo '</select>';
	echo $output;
}

function adpGetUserRole($userid){
	$user = new WP_User( $userid );
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		return $user->roles[0];
	}
	return '';
}

function adpGetCurrentUser(){
	return new WP_User( get_current_user_id() );
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
	return $post_id;
}
function adpUpdateMeta($postid,$meta){
	if(is_array($meta) && count($meta) > 0){
		foreach($meta as $key=>$val){
			update_post_meta($postid,$key,$val);
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
function adpGetSinglePost($id,$meta=false){
	$postObj = get_post($id);
	$ret = array();
	if($postObj){
		$ret['Post'] = $postObj;
	}
	if($meta){
		$ret['Meta'] = get_post_custom($id);
	}
	return $ret;
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
include "lib/livecomponents.php";
include "lib/pageroutes.php";
include "lib/metaboxes.php";
include "lib/widgets.php";
include "lib/annotations.php";
include "lib/wizards.php";