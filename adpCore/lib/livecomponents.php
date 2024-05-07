<?php
define('LIVE_COMPONENTS_DIR','livecomponents');

function adpIncludeLiveComponents(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/';
	
	if(file_exists($dirPath)){
		chdir($dirPath);
		$livecomponents = glob('*.php');
		
		if($livecomponents){
			echo '<web-app>';
			foreach($livecomponents as $livecomponent){
				$fileName = str_ireplace('.blade','',str_ireplace('.php','',basename($livecomponent)));
				echo '<import-element from="'.home_url().'/?adpLiveComponent='.$fileName.'.html"></import-element>';
			}
			echo '</web-app>';
		}
	}
}
add_filter( 'query_vars', function($vars){
	$vars[] = "adpLiveComponent";
	$vars[] = "adpUpdateLiveComp";
	return $vars;
});
add_action('template_redirect', function($template){
	global $wp_query;
	if(!isset( $wp_query->query['adpLiveComponent']) && !isset( $wp_query->query['adpUpdateLiveComp'])){
		return $template;
	}
	
	if(isset($wp_query->query['adpUpdateLiveComp'])){
		$adpComponent = str_ireplace('.html','',$wp_query->query['adpUpdateLiveComp']);
		$adpComponentParts = explode('-',$adpComponent);
		$componentPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'.blade.php';
		if(file_exists($componentPath) && count($adpComponentParts) > 1){
			$parsedComp = adpRenderBladeView($adpComponent,LIVE_COMPONENTS_DIR);
			header('Content-Type: text/event-stream');
			header('Cache-Control: no-cache');
			
			$str = str_ireplace('[at]','@',$parsedComp);
			$str = trim(preg_replace('/\s+/', ' ', $str));
			echo "data: {$str}\n\n";
			flush();
		}
		exit();
	}
	if(isset($wp_query->query['adpLiveComponent'])){
		$adpComponent = str_ireplace('.html','',$wp_query->query['adpLiveComponent']);
		$adpComponentParts = explode('-',$adpComponent);
		$componentPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'.blade.php';
		if(file_exists($componentPath) && count($adpComponentParts) > 1){
			$parsedComp = adpRenderBladeView($adpComponent,LIVE_COMPONENTS_DIR);
			$componentConfig = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'.json';
			if(file_exists($componentConfig)){
				$componentConfigUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'.json';
				$compConfig = json_decode(file_get_contents($componentConfigUrl),true);
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
				echo '<element-state name="componentCont" type="html"></element-state>';
				$componentFlowsDir = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'-flows/';
				
				if(file_exists($componentFlowsDir)){
					chdir($componentFlowsDir);
					$componentFlows = glob('*.php');
					$mountedEventFound = false;
					ob_start();
					?>
					<script type="module/realm" use="localState,globalState,$,attr,attrs,ref,refs,event">
						var source = new EventSource("<?php echo home_url().'/?adpUpdateLiveComp='.$adpComponent; ?>");
						source.onmessage = function(event) {
							localState.set('componentCont',event.data);
						};
					</script>
					<?php
					$sseScript = ob_get_clean();
					
					if($componentFlows){
						echo '<element-flow>';
						foreach($componentFlows as $componentFlow){
							$fileName = str_ireplace('.php','',$componentFlow);
							$fileNameParts = explode("-",$fileName);
							if(count($fileNameParts) > 0){
								if($fileNameParts[0]=='listen'){
									$eventName = $fileNameParts[1];
									$eventAttrib = isset($fileNameParts[2]) ? '="'.$fileNameParts[2].'"' : '';
									echo '<listen-event '.$eventName.''.$eventAttrib.'>';
									if($eventName=='mounted'){
										$mountedEventFound = true;
										echo $sseScript;
									}
									
									$componentFlowsPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'-flows/'.$componentFlow;
									include $componentFlowsPath;
									echo '</listen-event>';
								}else{
									echo '<trigger-event '.$fileNameParts[0].'="'.$fileNameParts[1].'">';
									$componentFlowsPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/'.LIVE_COMPONENTS_DIR.'/'.$adpComponent.'-flows/'.$componentFlow;
									include $componentFlowsPath;
									echo '</trigger-event>';
								}
							}
						}
						if(!$mountedEventFound){
							echo '<listen-event mounted>';
							echo $sseScript;
							echo '</listen-event>';
						}
						echo '</element-flow>';
					}else{
						echo '<element-flow><listen-event mounted>';
						echo $sseScript;
						echo '</listen-event></element-flow>';
					}
				}
				?>
				<template>
					<?php 
					$comp = str_ireplace('[at]','@',$parsedComp); 
					$strPos = stripos($comp,'name="#componentCont"');
					if($strPos >= 0){
						echo $comp;
					}else{
						echo '<slot name="#componentCont">';
						echo $comp;
						echo '</slot>';
					}
					?>
				</template>
			</custom-element>
			<?php
		}
		
		exit();
	}
});