<?php
add_shortcode('adpPostLoop',function($args,$content=''){
	ob_start();
	if(is_array($args) && count($args) > 0){
		$postlist = new WP_Query( $args );
		if($postlist->have_posts()){
			while($postlist->have_posts()){
				$currPostMeta = get_post_custom($post->ID);
				$postlist->the_post();
				echo do_shortcode($content);
			}
		}
		wp_reset_postdata();
	}
	return ob_get_clean();
});
add_shortcode('adpPostField',function($args,$content=''){
	global $post;
	ob_start();
	$postFld = isset($args['field']) ? $args['field'] : 'ID';
	if(is_object($post)){
		if(property_exists($post,$postFld)){
			echo $post->$postFld;
		}
	}
	return ob_get_clean();
});
add_shortcode('adpHiddenPostField',function($args,$content=''){
	global $post;
	ob_start();
	$postFld = isset($args['field']) ? $args['field'] : 'ID';
	$postFldName = isset($args['fieldname']) ? $args['fieldname'] : 'adpPostID';
	if(is_object($post)){
		if(property_exists($post,$postFld)){
			echo '<input type="hidden" name="'.$postFldName.'" value="'.$post->$postFld.'" />';
		}
	}
	return ob_get_clean();
});
add_shortcode('adpPostMeta',function($args,$content=''){
	global $post;
	ob_start();
	$metaFld = isset($args['field']) ? $args['field'] : '';
	$fldVal = get_post_meta($post->ID,$metaFld,true);
	if(is_array($fldVal)){
		echo implode(',',$fldVal);
	}else{
		echo $fldVal;
	}
	return ob_get_clean();
});
add_shortcode('adpPostThumb',function($args='',$content=''){
	global $post;
	$imgSize = isset($args['size']) ? explode(',',$args['size']) : 'post-thumbnail';
	$cssClass = isset($args['cssclass']) ? explode(',',$args['cssclass']) : '';
	$alt = isset($args['alt']) ? explode(',',$args['alt']) : '';
	$style = isset($args['style']) ? explode(',',$args['style']) : '';
	
	if(!isset($imgSize[1])){
		$imgSize = $imgSize[0];
	}
	$postid = isset($args['postid']) ? $args['postid'] : $post->ID;
	$thumburl = get_the_post_thumbnail_url($postid,$imgSize);
	$imgTag = '';
	if($thumburl){
		$imgTag = '<img src="'.$thumburl.'" alt="'.$alt.'" style="'.$style.'" class="'.$cssClass.'" />';
	}
	return $imgTag;
});
add_shortcode('adpFunctionCall',function($args,$content=''){
	ob_start();
	$func = isset($args['functionname']) ? $args['functionname'] : '';
	if($func!='' && function_exists($func)){
		unset($args['functionname']);
		if(count($args) > 0){
			$ret = $func($args);
			if($ret!=''){
				echo $ret;
			}
		}else{
			$ret = $func();
			if($ret!=''){
				echo $ret;
			}
		}
	}
	return ob_get_clean();
});
add_shortcode('adpIfIsPost',function($args,$content=''){
	ob_start();
	$variable = isset($args['variable']) ? $args['variable'] : '';
	if(isset($_POST[$variable]) || $_POST){
		echo do_shortcode($content);
	}
	return ob_get_clean();
});
add_shortcode('adpUpdatePostMeta',function($args,$content=''){
	global $post;
	ob_start();
	$metakey = isset($args['metakey']) ? $args['metakey'] : '';
	$metaval = isset($args['metaval']) ? $args['metaval'] : '';
	$postid = isset($args['postid']) ? $args['postid'] : $post->ID;
	
	if($metaval=='frompost'){
		$metaval = isset($_POST[$metakey]) ? $_POST[$metakey] : '';
	}
	$adpPostID = isset($_POST['adpPostID']) ? (int) $_POST['adpPostID'] : '';
	if($adpPostID!='' && $adpPostID==$post->ID){
		update_post_meta($postid,$metakey,$metaval);
	}
	return ob_get_clean();
});
add_shortcode('adpDumpVar',function($args,$content=''){
	ob_start();
	$variable = $$args['variable'];
	global $variable;
	echo '<pre>';
	var_dump($variable);
	echo '</pre>';
	return ob_get_clean();
});