<?php
add_action('init',function(){
	adpRegisterPostTypes('adpannotations','Annotation','Annotations');
});
add_action('wp_footer',function(){
	$uri = str_ireplace('/','',$_SERVER['REQUEST_URI']);
	if($uri==''){
		$uri = 'home';
	}
	$uriEncoded = base64_encode($uri);
	if(is_user_logged_in()){
		$currUserData = adpGetCurrentUser();
		?>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@recogito/annotorious@2.7.12/dist/annotorious.min.css">
		<script src="https://cdn.jsdelivr.net/npm/@recogito/annotorious@2.7.12/dist/annotorious.min.js"></script>
		
		<script src="<?php echo PLUGIN_URL; ?>js/annotator.min.js"></script>
		<link rel="stylesheet" href="http://assets.annotateit.org/annotator/v1.2.5/annotator.min.css" />
		<script>
			var app = new annotator.App();
			
			app.include(annotator.ui.main);
			app.include(annotator.storage.http,{
				prefix: '<?php echo home_url().'/?adpAnnotations='.$uriEncoded; ?>'
			});

			app.start().then(function () {
				app.ident.identity = '<?php echo $currUserData->display_name; ?>';
				app.annotations.load();
			});
			
			const server = {
				createAnnotation: async (annotation) => {
					const response = await fetch('<?php echo home_url().'/?adpCreateImageAnnotation='.$uriEncoded; ?>',{
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(annotation)
					});
					return response;
				},
				deleteAnnotation: async (annotation) => {
					const response = await fetch('<?php echo home_url().'/?adpDeleteImageAnnotation='.$uriEncoded; ?>',{
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(annotation)
					});
					return response;
				},
				updateAnnotation: async (annotation) => {
					const response = await fetch('<?php echo home_url().'/?adpUpdateImageAnnotation='.$uriEncoded; ?>',{
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(annotation)
					});
					return response;
				}
			};
			
			window.onload = function() {
				var pageImages = document.getElementsByTagName('img');
				var imageAnnoations = [];
				
				for(var i=0;i<pageImages.length;i++){
					if(pageImages[i].classList.contains('annotate')){
						console.log(pageImages[i].src);
						imageAnnoations[i] = Annotorious.init({
							image: pageImages[i]
						});
						imageAnnoations[i].setAuthInfo({
							id: '<?php echo get_current_user_id(); ?>',
							displayName: '<?php echo $currUserData->display_name; ?>'
						});
						var annoUrl = '<?php echo home_url().'/?adpLoadImageAnnotations='.$uriEncoded; ?>-=-' + encodeURIComponent(pageImages[i].src);
						imageAnnoations[i].loadAnnotations(annoUrl);
						
						imageAnnoations[i].on('createAnnotation', async (annotation, overrideId) => {
							const newId = await server.createAnnotation(annotation);
							overrideId(newId);
							console.log(annotation);
						});
						imageAnnoations[i].on('deleteAnnotation', async function(annotation) {
							await server.deleteAnnotation(annotation);
						});
						imageAnnoations[i].on('updateAnnotation', async function(annotation, previous) {
							await server.updateAnnotation(annotation);
						});
					}
					
				}
			}
		</script>
		<?php
	}
});

add_filter( 'query_vars', function($vars){
	$vars[] = "adpAnnotations";
	$vars[] = "adpCreateImageAnnotation";
	$vars[] = "adpLoadImageAnnotations";
	$vars[] = "adpUpdateImageAnnotation";
	$vars[] = "adpDeleteImageAnnotation";
	
	return $vars;
});
add_action('template_redirect', function($template){
	global $wp_query;
	if(!isset( $wp_query->query['adpAnnotations']) && !isset( $wp_query->query['adpCreateImageAnnotation']) && !isset( $wp_query->query['adpLoadImageAnnotations']) && !isset( $wp_query->query['adpUpdateImageAnnotation']) && !isset( $wp_query->query['adpDeleteImageAnnotation'])){
		return $template;
	}
	
	if(isset($wp_query->query['adpDeleteImageAnnotation'])){
		$data = json_decode(file_get_contents('php://input'), true);
		wp_delete_post($data['id'],true);
		exit();
	}
	if(isset($wp_query->query['adpUpdateImageAnnotation'])){
		$data = json_decode(file_get_contents('php://input'), true);
		adpUpdateMeta($data['id'],array(
			'AdpAnnotationBody'=>json_encode($data['body']),
			'AdpAnnotationTarget'=>json_encode($data['target'])
		));
		exit();
	}
	if(isset($wp_query->query['adpLoadImageAnnotations'])){
		$actionParts = explode('-=-',$wp_query->query['adpLoadImageAnnotations']);
		$uri = base64_decode($actionParts[0]);
		$image = urldecode($actionParts[1]);
		
		$args = array(
			'meta_query'=>array(
				'relation'=>'AND',
				array(
					'key'=>'adpAnnotationPage',
					'value'=>$uri
				),
				array(
					'key'=>'adpAnnotationType',
					'value'=>'Image'
				),
				array(
					'key'=>'AdpAnnotationTargetSrc',
					'value'=>$image
				)
			)
		);
		
		$res = adpGetPostsList('adpannotations',-1,$args,true);
		
		$ret = array();
		
		if($res){
			foreach($res['Posts'] as $key=>$val){
				$postID = $val['Post']->ID;
				$ret[] = array(
					'@context'=>'http://www.w3.org/ns/anno.jsonld',
					'type'=>'Annotation',
					'id'=>$postID,
					'body'=>json_decode($val['Meta']['AdpAnnotationBody'][0],true),
					'target'=>json_decode($val['Meta']['AdpAnnotationTarget'][0],true)
				);
			}
		}
		
		header('content-type: application/json');
		echo json_encode($ret);
		exit();
	}
	if(isset($wp_query->query['adpCreateImageAnnotation'])){
		$uri = base64_decode($wp_query->query['adpCreateImageAnnotation']);
		
		$data = json_decode(file_get_contents('php://input'), true);
		$id = uniqid();
		$postid = adpCreatePostWithData(array(
			'post_type'=>'adpannotations',
			'post_title'    => 'Image Comment #'.$id.' On '.$uri,
			'post_content'  => 'Image Comment #'.$id.' On '.$uri,
			'post_status'   => 'publish',
			'post_author'   => get_current_user_id()
		),array(
			'adpAnnotationType'=>'Image',
			'adpAnnotationPage'=>$uri,
			'AdpAnnotationBody'=>json_encode($data['body']),
			'AdpAnnotationTarget'=>json_encode($data['target']),
			'AdpAnnotationTargetSrc'=>$data['target']['source']
		));
		echo $postid;
		exit();
	}
	
	if(isset($wp_query->query['adpAnnotations'])){
		$actionsList = array(
			'search'=>'adpSearchAnnotations',
			'annotations'=>'adpAnnotationsHandler'
		);
		$actionParts = explode('/',$wp_query->query['adpAnnotations']);
		$uri = base64_decode($actionParts[0]);
		
		array_shift($actionParts);
		$action = $actionParts[0];
		
		$actionFunc = isset($actionsList[$action]) ? $actionsList[$action] : '';
		if($actionFunc!='' && function_exists($actionFunc)){
			array_shift($actionParts);
			$data = $actionFunc($uri,$actionParts);
			header('content-type: application/json');
			echo json_encode($data);
		}
		
		exit();
	}
});
function adpSearchAnnotations($uri){
	$res = adpGetPostsList('adpannotations',-1,array(
		'meta_key'=>'adpAnnotationPage',
		'meta_value'=>$uri
	),true);
	
	$total = count($res);
	$ret = array(
		'total'=>$total
	);
	if($res){
		foreach($res['Posts'] as $key=>$val){
			$postID = $val['Post']->ID;
			$ret['rows'][] = array(
				'id'=>$postID,
				'text'=>$val['Meta']['AdpAnnotationText'][0],
				'quote'=>$val['Meta']['AdpAnnotationQuote'][0],
				'ranges'=>json_decode($val['Meta']['AdpAnnotationRanges'][0],true)
			);
		}
	}
	return $ret;
}
function adpAnnotationsHandler($uri,$args){
	$data = json_decode(file_get_contents('php://input'), true);
	if(isset($data['text'])){
		if(isset($data['id'])){
			$method = $_SERVER['REQUEST_METHOD'];
			if($method=='DELETE'){
				wp_delete_post($data['id'],true);
			}elseif($method=='PUT'){
				adpUpdateMeta($data['id'],array(
					'AdpAnnotationQuote'=>$data['quote'],
					'AdpAnnotationText'=>$data['text'],
					'AdpAnnotationRanges'=>json_encode($data['ranges'])
				));
				$currDateTime = str_ireplace(' ','T',date('Y-m-d H:i:s'));
				$data['updated'] = $currDateTime;
			}elseif($method=='GET'){
				$postID = $data['id'];
				$postMeta = get_post_custom($postID);
				return array(
					'id'=>$postID,
					'text'=>$postMeta['AdpAnnotationText'][0],
					'quote'=>$postMeta['AdpAnnotationQuote'][0],
					'ranges'=>json_decode($postMeta['AdpAnnotationRanges'][0],true)
				);
			}
		}else{
			$id = uniqid();
			$postid = adpCreatePostWithData(array(
				'post_type'=>'adpannotations',
				'post_title'    => 'Comment #'.$id.' On '.$uri,
				'post_content'  => 'Comment #'.$id.' On '.$uri,
				'post_status'   => 'publish',
				'post_author'   => get_current_user_id()
			),array(
				'adpAnnotationType'=>'Text',
				'adpAnnotationPage'=>$uri,
				'AdpAnnotationQuote'=>$data['quote'],
				'AdpAnnotationText'=>$data['text'],
				'AdpAnnotationRanges'=>json_encode($data['ranges'])
			));
			$data['id'] = $postid;
		}
	}
	return $data;
}