<?php
add_action( 'template_redirect', function($template){
	if(is_404()){
		adpHandleCustomPagesRoute();
    }
	return $template;
} );
add_filter('pre_get_document_title', function($title){
	if(is_404()){
		$title = adpHandleCustomPagesRoute('title');
    }
	
	return $title;
},30 );
function adpHandleCustomPagesRoute($action='content'){
	$pageName = substr($_SERVER['REQUEST_URI'],1);
	$pagePath = get_stylesheet_directory().'/'.APP_DIRECTORY . '/pages/'.$pageName.'.blade.php';

	$pageUri = 'pages/'.$pageName.'.blade.php';
	$fileName = basename($pageUri);
	$folderPath = str_ireplace($fileName,'',$pageUri);
	$view = str_ireplace('.blade.php','',$fileName);
	
	if(file_exists($pagePath)){
		http_response_code(200);
		if($action=='content'){
			echo adpRenderBladeView($view,$folderPath);
		}elseif($action=='title'){
			ob_start();
			include $pagePath;
			$output = ob_get_clean();
			
			$title = isset($pageTitle) ? $pageTitle : $pageName;
			return $title;
		}
		exit();
	}
}