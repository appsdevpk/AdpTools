<?php
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
function adpConditionalsFunc(){
	$conditionals = array();
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/conditionals/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeConditionalFiles = glob('*.php');
		
		if($themeConditionalFiles){
			foreach($themeConditionalFiles as $conditionalFile){
				$fileName = str_ireplace('.blade','',str_ireplace('.php','',basename($conditionalFile)));
				$configFile = get_stylesheet_directory().'/'.APP_DIRECTORY.'/conditionals/'.$fileName.'.json';
				if(file_exists($configFile)){
					$configFileUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/conditionals/'.$fileName.'.json';
					$conditionalConfig = json_decode(file_get_contents($configFileUrl),true);
					
					$conditionals[] = array(
						'Slug'=>$fileName,
						'Name'=>$conditionalConfig['Name'],
						'Contents'=>$conditionalConfig['Contents']
					);
				}
			}
		}
	}
	
	if(is_array($conditionals) && count($conditionals) > 0){
		$postTypes = get_post_types(array(
			'public'=>true
		),'objects');
		$taxonomies = get_taxonomies(array(
			'public'=>true
		),'objects');
		
		if(isset($_REQUEST['btnSubmit'])){
			$conditionalSlug = $_REQUEST['conditionalSlug'].'_conditions';
			$conditionalSlugDef = $_REQUEST['conditionalSlug'].'_default';
			
			$conditionsData = array();
			foreach($_POST as $key=>$val){
				if(in_array($key,array('btnSubmit','conditionalSlug','defshowhide'))){
					continue;
				}
				$conditionsData[$key] = $val;
			}
			update_option($conditionalSlug,json_encode($conditionsData),true);
			update_option($conditionalSlugDef,$_POST['defshowhide'],true);
		}
		
		?>
		<div style="display: none" id="conditionalTemplate">
			<table class="form-table conditionalTable">
				<tbody>
					<tr>
						<th>Show/Hide</th>
						<td>
							<select class="showhideSelect" name="showhide[]">
								<option>Show</option>
								<option>Hide</option>
							</select>
						</td>
					</tr>
					<tr>
						<th>Condition</th>
						<td>
							<select class="conditionSelect" name="condition[]">
								<option></option>
								<option>Post Type Is</option>
								<option>Post ID In</option>
								<option>Url Is</option>
								<option>Function Is</option>
								<option>Taxonomy Is</option>
								<option>Terms In</option>
								<option>Is Mobile</option>
								<option>Is Desktop</option>
							</select>
						</td>
					</tr>
					<tr class="posttyperow" style="display:none">
						<th>Select Post Type</th>
						<td>
							<select name="posttype[]">
								<?php foreach($postTypes as $key=>$postType){ ?>
								<option value="<?php echo $postType->name; ?>"><?php echo $postType->label; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr class="taxonomyrow" style="display:none">
						<th>Select Taxonomy</th>
						<td>
							<select name="taxonomies[]">
								<?php foreach($taxonomies as $key=>$taxonomy){ ?>
								<option value="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->label; ?> (<?php echo $taxonomy->name; ?>)</option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr class="customvalrow" style="display:none">
						<th></th>
						<td>
							<input type="text" class="regular-text" name="conditionVal[]" />
						</td>
					</tr>
					<tr><td colspan="2" class="contentCol"></td></tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2">
							<p class="submit">
								<input type="button" class="button btnDelCondition" value="Delete" />
							</p>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
		foreach($conditionals as $conditional){
			$conditionalSlug = $conditional['Slug'].'_conditions';
			$conditionalSlugDef = $conditional['Slug'].'_default';
			$defshowhide = get_option($conditionalSlugDef,'Show');
			
			$conditionsData = get_option($conditionalSlug,array());
			$conditionsDataNew = array();
			
			if(!is_array($conditionsData)){
				$conditionsData = json_decode($conditionsData,true);
				$conditionsCount = count($conditionsData['showhide']);
				for($i=0;$i<$conditionsCount;$i++){
					$conditionData = array(
						'showhide'=>$conditionsData['showhide'][$i],
						'condition'=>$conditionsData['condition'][$i],
						'posttype'=>$conditionsData['posttype'][$i],
						'taxonomies'=>$conditionsData['taxonomies'][$i],
						'conditionVal'=>$conditionsData['conditionVal'][$i]
					);
					if(isset($conditional['Contents'])){
						foreach($conditional['Contents'] as $key=>$val){
							$contentSlug = $key.'_content';
							$conditionData[$contentSlug] = $conditionsData[$contentSlug][$i];
						}
					}
					$conditionsDataNew[] = $conditionData;
				}
			}
			?>
			<details>
				<summary><?php echo $conditional['Name']; ?></summary>
				<div class="conditionalForm">
					<div class="contentsRows" style="display:none">
						<?php 
						if(isset($conditional['Contents'])){
							foreach($conditional['Contents'] as $key=>$val){
								?>
							<h2 class="title"><?php echo $key; ?></h2>
							<textarea class="large-text code" name="<?php echo $key; ?>_content[]"><?php echo $val; ?></textarea>
								<?php
							}
						}
						?>
					</div>
					<!--<pre><?php var_dump($conditionsDataNew); ?></pre>-->
					<form action="" method="post">
						<input type="hidden" name="conditionalSlug" value="<?php echo $conditional['Slug']; ?>" />
						<table class="form-table conditionalTable">
							<tbody>
								<tr>
									<th>Default Show/Hide</th>
									<td>
										<?php adpCreateSelect('defshowhide',array('Show','Hide'),$defshowhide,''); ?>
									</td>
								</tr>
							</tbody>
						</table>
						<input type="button" class="btnAddCondition button" value="Add Condition" style="margin: 10px 0px 5px 0px" />
						<div class="conditionsList">
							<?php
							$conditionLabels = array(
								'Post ID In'=>'Enter comma separated Post IDs',
								'Url Is'=>'Enter Url',
								'Function Is'=>'Function Name,function arguments',
								'Terms In'=>'Terms Ids (comma separated)'
							);
							$submitDisplay = 'none';
							if(count($conditionsDataNew) > 0){
								$submitDisplay = 'block';
								foreach($conditionsDataNew as $conditionData){
									$posttyperowDisplay = $conditionData['condition']=='Post Type Is' ? '' : 'none';
									$taxonomyrowDisplay = $conditionData['condition']=='Taxonomy Is' ? '' : 'none';
									$customvalrowDisplay = 'none';
									$conditionLabel = '';
									
									if(!in_array($conditionData['condition'],array('','Post Type Is','Taxonomy Is'))){
										$customvalrowDisplay = '';
										$conditionLabel = $conditionLabels[$conditionData['condition']];
									}
									?>
							<table class="form-table conditionalTable">
								<tbody>
									<tr>
										<th>Show/Hide</th>
										<td>
											<?php adpCreateSelect('showhide[]',array('Show','Hide'),$conditionData['showhide'],'showhideSelect'); ?>
										</td>
									</tr>
									<tr>
										<th>Condition</th>
										<td>
											<?php 
											adpCreateSelect('condition[]',array('','Post Type Is','Post ID In','Url Is','Function Is','Taxonomy Is','Terms In'),$conditionData['condition'],'conditionSelect'); 
											?>
										</td>
									</tr>
									<tr class="posttyperow" style="display:<?php echo $posttyperowDisplay; ?>">
										<th>Select Post Type</th>
										<td>
											<select name="posttype[]">
												<?php foreach($postTypes as $key=>$postType){ ?>
												<option <?php echo $conditionData['posttype']==$postType->name ? 'selected' : ''; ?> value="<?php echo $postType->name; ?>"><?php echo $postType->label; ?></option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr class="taxonomyrow" style="display:<?php echo $taxonomyrowDisplay; ?>">
										<th>Select Taxonomy</th>
										<td>
											<select name="taxonomies[]">
												<?php foreach($taxonomies as $key=>$taxonomy){ ?>
												<option <?php echo $conditionData['taxonomies']==$taxonomy->name ? 'selected' : ''; ?> value="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->label; ?> (<?php echo $taxonomy->name; ?>)</option>
												<?php } ?>
											</select>
										</td>
									</tr>
									<tr class="customvalrow" style="display:<?php echo $customvalrowDisplay; ?>">
										<th><?php echo $conditionLabel; ?></th>
										<td>
											<input type="text" class="regular-text" name="conditionVal[]" value="<?php echo $conditionData['conditionVal']; ?>" />
										</td>
									</tr>
									<tr>
										<td colspan="2" class="contentCol">
											<?php 
											if(isset($conditional['Contents'])){
												foreach($conditional['Contents'] as $key=>$val){
													$key2 = $key.'_content';
													$contentVal = $conditionData[$key2];
													?>
												<h2 class="title"><?php echo $key; ?></h2>
												<textarea class="large-text code" name="<?php echo $key; ?>_content[]"><?php echo $contentVal; ?></textarea>
													<?php
												}
											}
											?>
										</td>
									</tr>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2">
											<p class="submit">
												<input type="button" class="button btnDelCondition" value="Delete" />
											</p>
										</td>
									</tr>
								</tfoot>
							</table>
									<?php
								}
							}
							?>
						</div>
						<p class="submit" id="saveBtnPara" style="display:<?php echo $submitDisplay; ?>">
							<input type="submit" name="btnSubmit" class="button button-primary" value="Save Changes" />
						</p>
					</form>
				</div>
			</details>
			<?php
		}
	}
	?>
	<script>
		jQuery('.btnAddCondition').click(function(){
			let currElem = jQuery(this);
			let conditionTempl = jQuery('#conditionalTemplate table').clone();
			conditionTempl.find('.contentCol').append(currElem.parent().prev().html());
			currElem.next().append(conditionTempl);
			jQuery('#saveBtnPara').show();
		});
		jQuery(document).on('change','.conditionSelect',function(){
			let currElem = jQuery(this);
			let currVal = currElem.val();
			let conditionsList = ['Post ID In','Url Is','Function Is','Terms In'];
			let conditionsLabels = ['Enter comma separated Post IDs','Enter Url','Function Name,function arguments','Terms Ids (comma separated)'];
			
			currElem.parent().parent().parent().find('.posttyperow').hide();
			currElem.parent().parent().parent().find('.taxonomyrow').hide();
			currElem.parent().parent().parent().find('.customvalrow').hide();
			
			if(currVal!==''){
				if(currVal==='Post Type Is'){
					currElem.parent().parent().parent().find('.posttyperow').show();
				}else if(currVal==='Taxonomy Is'){
					currElem.parent().parent().parent().find('.taxonomyrow').show();
				}else{
					let conditionInd = conditionsList.indexOf(currVal);
					let conditionLabel = conditionsLabels[conditionInd];
					currElem.parent().parent().parent().find('.customvalrow').show();
					currElem.parent().parent().parent().find('.customvalrow th').html(conditionLabel);
				}
			}
		});
		jQuery(document).on('click','.btnDelCondition',function(){
			var currElem = jQuery(this);
			if(confirm('Are you sure?')){
				if(currElem.parent().parent().parent().parent().parent().parent().find('table').length < 2){
					jQuery('#saveBtnPara').hide();
				}
				currElem.parent().parent().parent().parent().parent().remove();
			}
		});
		
	</script>
	<style>
		summary{
			border: 1ps solid #000;
			padding: 10px;
			background-color:#000;
			color:#fff;
		}
		.conditionalForm{
			background-color:#fff;
			border:1px solid #000;
			padding: 10px;
		}
		.conditionalTable{
			background-color:#f2f2f2;
			border: 1px solid #000
		}
		.conditionalTable th{
			padding-left: 10px
		}
	</style>
	<?php
}
add_shortcode('adpConditionals',function($args){
	$slug = isset($args['slug']) ? $args['slug'] : '';
	
	if(trim($slug)!=''){
		return adpConditionals($slug);
	}
	return '';
});
function adpConditionals($slug){
	$res = adpShouldDisplay($slug);
	
	if($res['Result']){
		$args = ($res['Contents'] && is_array($res['Contents'])) ? $res['Contents'] : array();
		
		$views = get_stylesheet_directory().'/'.APP_DIRECTORY . '/conditionals';
		$cache = get_stylesheet_directory().'/'.APP_DIRECTORY . '/cache';
		$blade = new adpBlade($views,$cache);
		$blade->pipeEnable = true;
		if(is_user_logged_in()){
			$userid = get_current_user_id();
			$blade->setAuth($userid,adpGetUserRole($userid));
		}
		
		ob_start();
		echo $blade->run($slug,$args);
		return ob_get_clean();
	}
}
function adpShouldDisplay($slug){
	$conditionalTpl = get_stylesheet_directory().'/'.APP_DIRECTORY.'/conditionals/'.$slug.'.blade.php';
	$conditionalCon = get_stylesheet_directory().'/'.APP_DIRECTORY.'/conditionals/'.$slug.'.json';
	
	$shouldDisplay = false;
	$ret = array();
	$conditionsResults = array();
	$currConditional = array();
	$conditionsDataNew = array();
	$hideCount = 0;
	
	if(file_exists($conditionalTpl) && file_exists($conditionalCon)){
		
		$conditionalConUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/conditionals/'.$slug.'.json';
		$currConditional = json_decode(file_get_contents($conditionalConUrl),true);
		$conditionalSlug = $slug.'_conditions';
		$conditionalSlugDef = $slug.'_default';
		$defshowhide = get_option($conditionalSlugDef,'Show');
		$shouldDisplay = $defshowhide=='Show' ? true : false;
		
		$conditionsData = get_option($conditionalSlug,array());
		
		if(!is_array($conditionsData)){
			$conditionsData = json_decode($conditionsData,true);
			$conditionsCount = count($conditionsData['showhide']);
			for($i=0;$i<$conditionsCount;$i++){
				$conditionData = array(
					'showhide'=>$conditionsData['showhide'][$i],
					'condition'=>$conditionsData['condition'][$i],
					'posttype'=>$conditionsData['posttype'][$i],
					'taxonomies'=>$conditionsData['taxonomies'][$i],
					'conditionVal'=>$conditionsData['conditionVal'][$i]
				);
				if(isset($currConditional['Contents'])){
					foreach($currConditional['Contents'] as $key=>$val){
						$contentSlug = $key.'_content';
						$conditionData['Contents'][$key] = $conditionsData[$contentSlug][$i];
					}
				}
				$conditionsDataNew[] = $conditionData;
			}
		}
		
		if(count($conditionsDataNew) > 0){
			foreach($conditionsDataNew as $conditionData){
				$ret = adpParseConditionData($conditionData);
				if($ret){
					if($conditionData['showhide']=='Hide'){
						$hideCount += 1;
					}else{
						$conditionsResults = array(
							'Contents'=>(isset($conditionData['Contents']) ? $conditionData['Contents'] : '')
						);
					}						
				}
			}
		}
	}
	
	if($hideCount > 0){
		return array(
			'Result'=>false
		);
	}else{
		if(count($conditionsResults) < 1){
			return array(
				'Result'=>$shouldDisplay,
				'Contents'=>(isset($currConditional['Contents']) ? $currConditional['Contents'] : '')
			);
		}else{
			return array(
				'Result'=>true,
				'Contents'=>$conditionsResults['Contents']
			);
		}
	}
}
function adpParseConditionData($conditionData){
	global $post;
	$ret = false;
	
	if($conditionData['condition']=='Post Type Is'){
		if(get_post_type()==$conditionData['posttype']){
			$ret = true;
		}
	}elseif($conditionData['condition']=='Post ID In'){
		$postIDs = explode(',',$conditionData['conditionVal']);
		if(is_singular() && in_array($post->ID.'',$postIDs)){
			$ret = true;
		}
	}elseif($conditionData['condition']=='Url Is'){
		if($_SERVER['REQUEST_URI']==$conditionData['conditionVal']){
			$ret = true;
		}
	}elseif($conditionData['condition']=='Function Is'){
		$funcParts = explode(',',$conditionData['conditionVal']);
		$funcName = $funcParts[0];
		array_shift($funcParts);
		
		if(count($funcParts) > 0){
			$ret = $funcName($funcParts);
		}else{
			$ret = $funcName();
		}
	}elseif($conditionData['condition']=='Taxonomy Is'){
		if(is_tax($conditionData['taxonomies'])){
			$ret = true;
		}
	}elseif($conditionData['condition']=='Terms In'){
		if(is_tax()){
			$current_term = get_queried_object();
			$termIds = explode(',',$conditionData['conditionVal']);
			if(in_array($current_term->term_id,$termIds)){
				$ret = true;
			}
		}
	}elseif($conditionData['condition']=='Is Mobile'){
		return wp_is_mobile();
	}elseif($conditionData['condition']=='Is Desktop'){
		return !wp_is_mobile();
	}
	
	return $ret;
}