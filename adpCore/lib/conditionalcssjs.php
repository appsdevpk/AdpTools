<?php
function adpIncludeDynamicCss($location='header'){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/css/';
	$dirUri = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/css/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$cssFiles = glob('*.css');
		
		if($cssFiles){
			foreach($cssFiles as $cssFile){
				$fileNameParts = explode('-',str_ireplace('.css','',basename($cssFile)));
				if(adpParseConditionalCssJs($fileNameParts,$location)){
					echo '<link rel="stylesheet" href="'.$dirUri.basename($cssFile).'" />';
				}
			}
		}
	}
}
function adpIncludeDynamicJs($location='header'){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/js/';
	$dirUri = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/js/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$cssFiles = glob('*.js');
		
		if($cssFiles){
			foreach($cssFiles as $cssFile){
				$fileNameParts = explode('-',str_ireplace('.js','',basename($cssFile)));
				if(adpParseConditionalCssJs($fileNameParts,$location)){
					echo '<script src="'.$dirUri.basename($cssFile).'"></script>';
				}
			}
		}
	}
}
function adpParseConditionalCssJs($fileNameParts,$location){
	if(count($fileNameParts)==2){
		if($fileNameParts[1]=='template' && $fileNameParts[0]==$location && is_page_template()){
			return true;
		}elseif($fileNameParts[1]=='page' && $fileNameParts[0]==$location && is_page()){
			return true;
		}elseif($fileNameParts[1]=='single' && $fileNameParts[0]==$location && is_single()){
			return true;
		}
	}elseif(count($fileNameParts)==3){
		$contentIds = explode(',',$fileNameParts[2]);
		if($fileNameParts[1]=='template' && $fileNameParts[0]==$location && is_page_template($fileNameParts[1].'.php')){
			return true;
		}elseif($fileNameParts[1]=='page' && $fileNameParts[0]==$location && is_page($contentIds)){
			return true;
		}elseif($fileNameParts[1]=='single' && $fileNameParts[0]==$location && is_single($contentIds)){
			return true;
		}
	}
	return false;
}