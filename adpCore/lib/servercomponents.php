<?php
function adpIncludeComponents(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/servercomponents/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$serverComponents = glob('*.php');
		
		if($serverComponents){
			echo '<web-app>';
			$globalStatesPath = $dirPath.'global-states.json';
			if(file_exists($globalStatesPath)){
				$globalStatesUri = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/servercomponents/global-states.json';
				$globalStates = json_decode(file_get_contents($globalStatesUri),true);
				if(is_array($globalStates) && count($globalStates) > 0){
					foreach($globalStates as $key=>$val){
						$compType = isset($val['type']) ? $val['type'] : 'string';
						$defaultVal = isset($val['default']) ? $val['default'] : '';
						$storageStr = isset($val['storage']) ? 'storage="'.$val['storage'].'"' : '';
						echo '<global-state name="'.$key.'" type="'.$compType.'" '.$storageStr.'>'.$defaultVal.'</global-state>';
					}
				}
			}
			foreach($serverComponents as $serverComponent){
				$fileName = str_ireplace('.blade','',str_ireplace('.php','',basename($serverComponent)));
				echo '<import-element from="'.home_url().'/?adpComponent='.$fileName.'.html"></import-element>';
			}
			echo '</web-app>';
		}
	}
}
add_filter( 'query_vars', function($vars){
	$vars[] = "adpComponent";
	return $vars;
});
add_action('template_redirect', function($template){
	global $wp_query;
	if(!isset( $wp_query->query['adpComponent'])){
		return $template;
	}
	
	if(isset($wp_query->query['adpComponent'])){
		$adpComponent = str_ireplace('.html','',$wp_query->query['adpComponent']);
		$adpComponentParts = explode('-',$adpComponent);
		$componentPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/servercomponents/'.$adpComponent.'.blade.php';
		if(file_exists($componentPath) && count($adpComponentParts) > 1){
			$parsedComp = adpRenderBladeView($adpComponent,'servercomponents');
			$componentConfig = get_stylesheet_directory().'/'.APP_DIRECTORY.'/servercomponents/'.$adpComponent.'.json';
			if(file_exists($componentConfig)){
				$componentConfigUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/servercomponents/'.$adpComponent.'.json';
				$compConfig = json_decode(file_get_contents($componentConfigUrl),true);
			}
			$children = function(){
				echo '<slot children></slot>';
			}
			?>
			<custom-element name="<?php echo $adpComponent; ?>">
				<?php
				if(isset($compConfig['attributes']) && is_array($compConfig['attributes']) && count($compConfig['attributes']) > 0){
					foreach($compConfig['attributes'] as $key=>$val){
						$compType = isset($val['type']) ? $val['type'] : 'string';
						$defaultVal = isset($val['default']) ? $val['default'] : '';
						echo '<element-attr name="'.$key.'" type="'.$compType.'">'.$defaultVal.'</element-attr>';
					}
				}
				if(isset($compConfig['states']) && is_array($compConfig['states']) && count($compConfig['states']) > 0){
					foreach($compConfig['states'] as $key=>$val){
						$compType = isset($val['type']) ? $val['type'] : 'string';
						$defaultVal = isset($val['default']) ? $val['default'] : '';
						echo '<element-state name="'.$key.'" type="'.$compType.'">'.$defaultVal.'</element-state>';
					}
				}
				$componentFlowsDir = get_stylesheet_directory().'/'.APP_DIRECTORY.'/servercomponents/'.$adpComponent.'-flows/';
				
				if(file_exists($componentFlowsDir)){
					chdir($componentFlowsDir);
					$componentFlows = glob('*.php');
					
					if($componentFlows){
						echo '<element-flow>';
						foreach($componentFlows as $componentFlow){
							$fileName = str_ireplace('.php','',$componentFlow);
							$fileNameParts = explode("-",$fileName);
							if(count($fileNameParts) > 0){
								if($fileNameParts[0]=='listen'){
									$eventName = $fileNameParts[1];
									$eventAttrib = isset($fileNameParts[2]) ? '="'.$fileNameParts[2].'"' : '';;
									echo '<listen-event '.$eventName.''.$eventAttrib.'>';
									$componentFlowsPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/servercomponents/'.$adpComponent.'-flows/'.$componentFlow;
									include $componentFlowsPath;
									echo '</listen-event>';
								}else{
									echo '<trigger-event '.$fileNameParts[0].'="'.$fileNameParts[1].'">';
									$componentFlowsPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/servercomponents/'.$adpComponent.'-flows/'.$componentFlow;
									include $componentFlowsPath;
									echo '</trigger-event>';
								}
							}
						}
						echo '</element-flow>';
					}
				}
				?>
				<template>
					<?php echo str_ireplace('[at]','@',$parsedComp); ?>
				</template>
			</custom-element>
			<?php
		}
		
		exit();
	}
});