<?php
add_filter( 'query_vars', function($vars){
	$vars[] = "adpListContent";
	$vars[] = "adpAddContent";
	$vars[] = "adpUpdateContent";
	$vars[] = "adpRemoveContent";
	return $vars;
});
add_action('template_redirect', function($template){
	global $wp_query;
	
	if(!isset( $wp_query->query['adpListContent'] ) && !isset( $wp_query->query['adpAddContent'] ) && !isset( $wp_query->query['adpUpdateContent'] ) && !isset( $wp_query->query['adpRemoveContent'] )){
		return $template;
	}
	
	if(isset($wp_query->query['adpListContent'])){
		$content_type = $wp_query->query['adpListContent'];
		$rpp = isset($_REQUEST['rpp']) ? $_REQUEST['rpp'] : 'all';
		$owner = isset($_REQUEST['owner']) ? $_REQUEST['owner'] : 'all';
		adpListContentFunc($content_type,$rpp, $owner);
		exit;
	}
	if(isset($wp_query->query['adpAddContent'])){
		$content_type = $wp_query->query['adpAddContent'];
		$template = isset($_REQUEST['tpl']) ? $_REQUEST['tpl'] : '';
		adpAddContentFunc($content_type,$template);
		exit;
	}
	if(isset($wp_query->query['adpUpdateContent'])){
		$content_type = $wp_query->query['adpUpdateContent'];
		$template = isset($_REQUEST['tpl']) ? $_REQUEST['tpl'] : '';
		adpUpdateContentFunc($content_type,$template);
		exit;
	}
	if(isset($wp_query->query['adpRemoveContent'])){
		$contentID = $wp_query->query['adpRemoveContent'];
		$redirect = isset($_REQUEST['rd']) ? $_REQUEST['rd'] : '';
		adpRemoveContentFunc($contentID,$redirect);
		exit;
	}
});
function adpListContentFunc($content_type,$rpp, $owner='all'){
	$posts_per_page = -1;
	$rpp = (int) $rpp;
	if($rpp > 0){
		$posts_per_page = $rpp;
	}
	$args = array(
		'post_type'=>$content_type,
		'posts_per_page'=>$posts_per_page
	);
	if($owner=='curruser'){
		$args['author'] = get_current_user_id();
	}
	$contentList = get_posts($args);
	include "content_templates/".$content_type."/list.php";
}
function adpGetPostsCount($post_type,$author='all'){
	global $wpdb;
	$tableName = $wpdb->prefix.'posts';
	$qr = "select count(*) as nor from $tableName where post_type='$post_type'";
	if($author!='all'){
		$qr .= " AND post_author=".trim($author);
	}
	$rec = $wpdb->get_row($qr,ARRAY_A);
	return $rec['nor'];
}
function adpGetContentList($content_type,$rpp=10, $owner='all'){
	$rpp = (int) $rpp;
	
	$currPage = isset($_REQUEST['paged']) ? (int) $_REQUEST['paged'] : 1;
	$userid = $owner;
	if($owner=='curruser'){
		$userid = get_current_user_id();
	}
	$totalRecords = (int) adpGetPostsCount($content_type,$userid);
	$totalPages = 1;
	if($totalRecords > $rpp){
		$totalPages = floor($totalRecords/$rpp);
		if($totalRecords%$rpp > 0){
			$totalPages += 1;
		}
	}
	$offset = ($currPage * $rpp) - $rpp;
	$args = array(
		'post_type'=>$content_type,
		'posts_per_page'=>$rpp,
		'offset' => $offset
	);
	if($owner=='curruser'){
		$args['author'] = get_current_user_id();
	}
	ob_start();
	if(function_exists('wp_paginate')){
		wp_paginate('pages='.$totalPages.'&page='.$currPage);
	}
	$pagination = ob_get_clean();
	return array(
		'Posts'=>get_posts($args),
		'Pagination'=>$pagination,
		'TotalRecords'=>$totalRecords
	);
}
function adpAddContentFunc($content_type,$template){
	if(isset($_REQUEST['addContent'])){
		$post_title = isset($_REQUEST['post_title']) ? $_REQUEST['post_title'] : '';
		$post_content = isset($_REQUEST['post_content']) ? $_REQUEST['post_content'] : '';
		if($post_content==''){
			$post_content = $post_title;
		}
		if($post_title!=''){
			$new_post = array(
				'post_title' => $post_title,
				'post_content' => $post_content,
				'post_status' => 'publish',
				'post_type' => $content_type,
				'post_author'=>get_current_user_id()
			);
			$postMeta = array();
			foreach($_REQUEST as $key=>$val){
				if(substr($key,0,5)=='meta_'){
					$metaKey = str_ireplace('meta_','',$key);
					$postMeta[$metaKey] = $val;
				}
			}
			adpCreateCustomPostWithMeta($new_post,$postMeta);
		}
	}
	if($template!=''){
		include "content_templates/".$content_type."/".$template.".php";
	}
}
function adpUpdateContentFunc($content_type,$template){
	if(isset($_REQUEST['updateContent'])){
		$contentID = isset($_REQUEST['updateContent']) ? $_REQUEST['updateContent'] : 0;
		$post_title = isset($_REQUEST['post_title']) ? $_REQUEST['post_title'] : '';
		$post_content = isset($_REQUEST['post_content']) ? $_REQUEST['post_content'] : '';
		if($post_content==''){
			$post_content = $post_title;
		}
		if($contentID > 0){
			$postObj = array(
				'ID' => $contentID,
				'post_title' => $post_title,
				'post_content' => $post_content,
				'post_status' => 'publish'
			);
			$postMeta = array();
			foreach($_REQUEST as $key=>$val){
				if(substr($key,0,5)=='meta_'){
					$metaKey = str_ireplace('meta_','',$key);
					$postMeta[$metaKey] = $val;
				}
			}
			adpUpdateCustomPostWithMeta($postObj,$postMeta);
		}
	}
	if($template!=''){
		include "content_templates/".$content_type."/".$template.".php";
	}
}
function adpRemoveContentFunc($contentID,$redirect=''){
	wp_delete_post($contentID,true);
	if($redirect!=''){
		$loc = $redirect;
		if($redirect=='HTTP_REFERER'){
			$loc = $_SERVER['HTTP_REFERER'];
		}
		header('location: '.$loc);
	}
}
add_action('wp_head',function(){
	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
	<script>
		function adpShowLoader(){
			jQuery.blockUI({ css: { 
				border: 'none', 
				padding: '15px', 
				backgroundColor: '#000', 
				'-webkit-border-radius': '10px', 
				'-moz-border-radius': '10px', 
				opacity: .5, 
				color: '#fff' 
			} });
		}
		function adpHideLoader(){
			jQuery.unblockUI();
		}
	</script>
	<style>
		.removecontlink{
			float:right;
			cursor:pointer
		}
		.removecontlink:hover{
			color:red;
		}
	</style>
	<?php
});
add_shortcode('adpContentView',function($args=''){
	ob_start();
	$endpoints = array(
		'list'=>'adpListContent',
		'add'=>'adpAddContent',
		'update'=>'adpUpdateContent',
		'del'=>'adpRemoveContent'
	);
	
	$isAjaxed = isset($args['isajaxed']) ? $args['isajaxed'] : 'No';
	$contentType = isset($args['contenttype']) ? $args['contenttype'] : 'post';
	$contentView = isset($args['contentview']) ? $args['contentview'] : 'list';
	$currEndPoint = $endpoints[$contentView];
	$addEndPoint = $endpoints['add'];
	$delEndPoint = $endpoints['del'];
	$updateEndPoint = $endpoints['update'];
	$numberOfRecords = isset($args['numberofrecords']) ? $args['numberofrecords'] : 'all';
	$contentOwner = isset($args['contentowner']) ? $args['contentowner'] : 'all';
	$homeurl = home_url();
	$containerKey = 'adpContentViewContainer_'.$contentType.'_'.$contentView;
	$formID = 'new_'.$contentType;
	$formIDUpdate = 'update_'.$contentType;
	
	if($isAjaxed=='Yes'){
		?>
		<div id="notificationsbar"></div>
		<div id="<?php echo $containerKey; ?>"></div>
		<script>
			jQuery(document).ready(function(){
				adpShowLoader();
				adpLoadContent();
				
				jQuery(document).on('submit','#<?php echo $formID; ?>',function(){
					adpShowLoader();
					jQuery.ajax({
						url: "<?php echo $homeurl.'/?'.$addEndPoint.'='.$contentType; ?>",
						data: jQuery('#<?php echo $formID; ?>').serialize(),
						method: 'POST'
					}).done(function(response){
						jQuery('#notificationsbar').html(response);
						adpLoadContent();
					});
					return false;
				});
				jQuery(document).on('click','.removecontlink',function(e){
					e.preventDefault();
					if(confirm('Are you sure?')){
						var contentID = jQuery(this).data('id');
						adpShowLoader();
						jQuery.ajax({
							url: "<?php echo $homeurl.'/?'.$delEndPoint; ?>=" + contentID
						}).done(function(response){
							adpLoadContent();
						});
					}
				});
				
				//Contents specific code here
				jQuery(document).on('change','.adpTodoIsDone',function(e){
					e.preventDefault();
					var currElem = jQuery(this);
					var contentID = currElem.data('id');
					var isdone = 'no';
					if(currElem.is(':checked')){
						isdone = 'Yes';
					}
					jQuery('#update_todos #updateContent').val(contentID);
					jQuery('#update_todos #meta_adpTodoIsDone').val(isdone);
					adpShowLoader();
					adpUpdateContent();
				});
				
				
			});
			function adpUpdateContent(){
				jQuery.ajax({
					url: "<?php echo $homeurl.'/?'.$updateEndPoint.'='.$contentType; ?>",
					data: jQuery('#<?php echo $formIDUpdate; ?>').serialize(),
					method: 'POST'
				}).done(function(response){
					jQuery('#notificationsbar').html(response);
					adpLoadContent();
				});
			}
			function adpLoadContent(){
				jQuery.ajax({
					url: "<?php echo $homeurl.'/?'.$currEndPoint.'='.$contentType; ?>",
					data: {rpp: '<?php echo $numberOfRecords; ?>', view: '<?php echo $contentView; ?>', owner: '<?php echo $contentOwner; ?>'}
				}).done(function(response){
					jQuery('#<?php echo $containerKey; ?>').html(response);
					adpHideLoader();
				});
			}
		</script>
		<?php
	}else{
		adpListContentFunc($contentType);
	}
	return ob_get_clean();
});