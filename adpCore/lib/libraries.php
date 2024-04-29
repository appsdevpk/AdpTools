<?php
function adpLibariesSearchFunc(){
	$searchKeyword = isset($_REQUEST['libname']) ? trim($_REQUEST['libname']) : '';
	?>
	<form action="" method="get">
		<input type="hidden" name="page" value="adpLibariesSearch" />
		<table class="form-table">
			<tbody>
				<tr>
					<td style="width:90px">Enter keyword</th>
					<td>
						<input style="width:100%" type="text" name="libname" id="libname" value="<?php echo $searchKeyword; ?>" required />
					</td>
					<td style="width:200px">
						<input type="submit" name="submitFull" id="submitFull" class="button button-primary" value="Search Similar" />
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Search Exact" />
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php
	if($searchKeyword!=''){
		if(isset($_REQUEST['submitFull'])){
			adpCdnJsFullSearchView(urlencode($searchKeyword));
		}elseif(isset($_REQUEST['submit'])){
			adpCdnJsSearchView($searchKeyword);
		}
	}
	if(isset($_REQUEST['btnCreatePreset'])){
		adpCreateLibrariesPreset($searchKeyword,implode(',',$_REQUEST['adpSelectedLib']));
		echo '<p>Preset created</p>';
	}
}
function adpCdnJsFullSearchView($keyword){
	//$currUserID = get_current_user_id();
	//update_user_meta($currUserID,'adpUserLibrariesList','');
	try{
		$results = json_decode(adpSendApiCall('https://api.cdnjs.com/libraries?search='.$keyword.'&fields=filename,description,keywords,alternativeNames,fileType,homepage,repository,version,github', 'GET', '', ''),true);
		$res = $results['results'];
		if($res && count($res) > 0){
			$homeUrl = home_url();
			?>
			<link rel="stylesheet" href="//cdn.datatables.net/1.11.2/css/jquery.dataTables.min.css" />
			<script src="//cdn.datatables.net/1.11.2/js/jquery.dataTables.min.js"></script>
			<style>
				.adpAssetVersionHead strong{
					display:block;
					padding:5px;
					cursor: pointer
				}
			</style>
			<form action="edit.php?page=adpLibariesSearch&post_type=reactapps&libname=<?php echo $keyword; ?>&submitFull=Search+Similar" method="post">
				<input type="submit" name="btnCreatePreset" value="Create Preset" id="btnCreatePreset" style="display:none" />
				<h3>Search Result (<?php echo count($res); ?> Found)</h3>
				<table id="searchResults" class="wp-list-table widefat fixed striped table-view-list posts">
					<thead>
						<tr>
							<th style="width:30px"><input type="checkbox" id="checkAll" value="" /></th>
							<th>Name</th>
							<th>Version</th>
							<th>Description</th>
							<th>Github Stars</th>
							<th>Number of Forks</th>
							<th>Source</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach($res as $rec){
							?>
							<tr class="">
								<!--<td>
									<pre><?php var_dump($rec); ?></pre>
								</td>-->
								<td style="width:30px"><input type="checkbox" class="adpSelectedLib" name="adpSelectedLib[]" value="<?php echo $rec['name']; ?>" /></td>
								<td><?php echo $rec['name']; ?></td>
								<td><?php echo $rec['version']; ?></td>
								<td><?php echo $rec['description']; ?></td>
								<td><?php echo $rec['github']['stargazers_count']; ?></td>
								<td><?php echo $rec['github']['forks']; ?></td>
								<td style="width:100px"><a href="<?php echo $rec['latest']; ?>" target="_blank">View Source</a></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<script>
					jQuery(document).ready( function () {
						//jQuery('#searchResults').DataTable();
						jQuery('#checkAll').change(function(){
							var isChecked = jQuery(this).is(':checked');
							jQuery('.adpSelectedLib').prop('checked',isChecked);
							if(isChecked){
								jQuery('#btnCreatePreset').show();
							}else{
								jQuery('#btnCreatePreset').hide();
							}
						});
						jQuery('.adpSelectedLib').change(function(){
							var numOfChecked = 0;
							jQuery('.adpSelectedLib').each(function(){
								if(jQuery(this).is(':checked')){
									numOfChecked += 1;
								}
							});
							if(numOfChecked > 0){
								jQuery('#btnCreatePreset').show();
							}else{
								jQuery('#btnCreatePreset').hide();
							}
						});
					} );
				</script>
			</form>
			<?php
		}else{
			echo '<p>No match found</p>';
		}
	}catch(Exception $e){
		echo $e->getMessage();
	}
}
function adpCdnJsSearchView($keyword){
	//$currUserID = get_current_user_id();
	//update_user_meta($currUserID,'adpUserLibrariesList','');
	try{
		$results = json_decode(adpSendApiCall('https://api.cdnjs.com/libraries/'.$keyword, 'GET', '', ''),true);
		if($results){
			$userLibrary = adpGetCurrentUserLibrary();
			$homeUrl = home_url();
			if(isset($results['tutorials']) && count($results['tutorials']) > 0){
				//echo '<pre>';
				//var_dump($results['tutorials']);
				//echo '</pre>';
				?>
				<style>
					.adpTutorialContent{
						margin: 10px 0px;
						padding:10px;
						border: 1px solid #000
					}
				</style>
				<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
				<h3>Tutorials</h3>
				<table class="wp-list-table widefat fixed striped table-view-list posts">
					<tbody>
					<?php foreach($results['tutorials'] as $tutorial){ ?>
						<tr>
							<td>
								<h3><?php echo $tutorial['name']; ?></h3>
								<p><?php echo $tutorial['description']; ?></p>
								<a target="_blank" href="<?php echo $tutorial['homepage']; ?>">Home Page</a> 
								<a target="_blank" href="<?php echo $tutorial['repository']; ?>">Repository</a>
								<a class="adpViewTutBtn button button-primary" href="#">View Tutorial</a>
								<div class="adpTutorialContent" style="display:none" data-parsed='no'><?php echo $tutorial['content']; ?></div>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			<?php
			}
			$latestVer = $results['latest'];
			$filename = $results['filename'];
			$cdnPath = str_ireplace($filename,'',$latestVer);
			$assets = $results['assets'];
			?>
			<style>
				.adpAssetVersionHead strong{
					display:block;
					padding:5px;
					cursor: pointer
				}
			</style>
			<h3>Latest</h3>
			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<tbody>
					<tr>
						<td><input style="width:100%" type="text" value="<?php echo $latestVer; ?>" readonly /></td>
						<td style="width:305px">
							<a href="#" class="copyClipBtn button">Copy To Clipboard</a>
							<?php adpLibraryAddRemoveLink($latestVer,$userLibrary,$homeUrl,'yes'); ?>
						</td>
					</tr>
				</tbody>
			</table>
			
			<h3>Other Files</h3>
			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<tbody>
					<?php 
					foreach($assets as $asset){
						?>
						<tr class="adpAssetVersionHead">
							<td>
								<strong>Version <?php echo $asset['version']; ?></strong>
								<table class="wp-list-table widefat fixed striped table-view-list posts" style="display:none">
									<tbody>
										<?php foreach($asset['files'] as $assetFile){ ?>
										<tr>
											<td><input style="width:100%" type="text" value="<?php echo $cdnPath.$assetFile; ?>" readonly /></td>
											<td style="width:305px">
												<a href="#" class="copyClipBtn button">Copy To Clipboard</a>
												<?php adpLibraryAddRemoveLink($cdnPath.$assetFile,$userLibrary,$homeUrl,'yes'); ?>
											</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<script>
				jQuery('.adpAssetVersionHead strong').click(function(){
					jQuery(this).next().slideToggle();
				});
				jQuery('.adpViewTutBtn').click(function(){
					var currElem = jQuery(this);
					if(currElem.next().attr('data-parsed')==='no'){
						var markedText = marked(currElem.next().html());
						currElem.next().html(markedText);
						currElem.next().attr('data-parsed','yes');
					}
					currElem.next().slideToggle();
				});
				jQuery(document).on('click','.copyClipBtn',function(){
					var currElem = jQuery(this);
					currElem.parent().prev().find('input').attr('id','adpCopyArea');
					var copyText = document.getElementById("adpCopyArea");
					copyText.select();
					copyText.setSelectionRange(0, 99999); /* For mobile devices */
					document.execCommand('copy');
					currElem.prev().attr('id','');
					alert('Copied to clipboard');
				});
			</script>
			<?php
		}
	}catch(Exception $e){
		echo $e->getMessage();
	}
}
function adpLibariesPresetsSearchFunc(){
	?>
	<h3 onclick="jQuery(this).next().slideToggle();" class="button" style="width:98%;margin-top:20px">Create New Preset</h3>
	<form action="edit.php?post_type=reactapps&page=adpLibariesPresetsSearch" method="post" style="display:none">
		<table class="form-table">
			<tbody>
				<tr>
					<td style="width:90px">Preset Name</th>
					<td>
						<input style="width:100%" type="text" name="presetName" id="presetName" required />
					</td>
					<td style="width:62px">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Create" />
					</td>
				</tr>
				<tr>
					<td colspan="3">Library Keywords (comma separated)</td>
				</tr>
				<tr>
					<td colspan="3">
						<textarea style="width:100%;height:200px" name="presetKeywords" id="presetKeywords" required></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<style>
		.adpPresetKeywords{
			list-style-type:none;
			margin: 10px 0px;
			padding: 0px;
			display: block;
		}
		.adpPresetKeywords:after{
			content:"";
			display:block;
			clear:both
		}
		.adpPresetKeywords li{
			float:left;
			width: 100px;
			height: 55px;
			border: 1px solid #000;
			padding:10px;
			margin: 0px 10px 10px 0px;
			text-align:center;
			text-transform: uppercase;
		}
		.adpPresetKeywords li a{
			text-decoration: none;
			color:#000
		}
		.adpPresetTitle{
			margin: 10px 0px;
			padding: 10px;
			border: 1px solid #000;
			cursor: pointer
		}
	</style>
	<?php
	if(isset($_REQUEST['submit'])){
		adpCreateLibrariesPreset($_REQUEST['presetName'],$_REQUEST['presetKeywords']);
	}
	$presetsList = adpGetPresetsList();
	if(count($presetsList) > 0){
		foreach($presetsList as $preset){
			$presetKeywords = explode(',',$preset['Keywords']);
			$numOfPresets = count($presetKeywords);
			echo '<h3 class="adpPresetTitle">'.$preset['Title'].' ('.$numOfPresets.')</h3>';
			echo '<ul class="adpPresetKeywords" style="display:none">';
			foreach($presetKeywords as $kw){
				?>
				<li>
					<a href="edit.php?page=adpLibariesSearch&post_type=reactapps&libname=<?php echo $kw; ?>&submit=Search">
						<?php echo $kw; ?>
					</a>
				</li>
				<?php
			}
			echo '</ul>';
		}
		?>
		<script>
			jQuery('.adpPresetTitle').click(function(){
				jQuery(this).next().slideToggle();
			});
		</script>
		<?php
	}
}
function adpCreateLibrariesPreset($lib,$preset){
	$presetsList = adpGetPresetsList();
	$presetsList[] = array(
		'Title'=>$lib,
		'Keywords'=>$preset
	);
	update_option('adpPresetsList',$presetsList,true);
}
function adpGetPresetsList(){
	$presetsList = get_option('adpPresetsList');
	if(!is_array($presetsList)){
		$presetsList = array();
	}
	return $presetsList;
}
function adpMyLibariesFunc(){
	$userLibrary = adpGetCurrentUserLibrary();
	$homeUrl = home_url();
	?>
	<h3>My Library</h3>
	<?php if(count($userLibrary) > 0){ ?>
	<table class="wp-list-table widefat fixed striped table-view-list posts">
		<tbody>
		<?php foreach($userLibrary as $userLib){ ?>
			<tr>
				<td><input style="width:100%" type="text" value="<?php echo $userLib; ?>" readonly /></td>
				<td style="width:305px">
					<a href="#" class="copyClipBtn button">Copy To Clipboard</a>
					<?php adpLibraryAddRemoveLink($userLib,$userLibrary,$homeUrl,'no'); ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<script>
		jQuery(document).on('click','.copyClipBtn',function(){
			var currElem = jQuery(this);
			currElem.parent().prev().find('input').attr('id','adpCopyArea');
			var copyText = document.getElementById("adpCopyArea");
			copyText.select();
			copyText.setSelectionRange(0, 99999); /* For mobile devices */
			document.execCommand('copy');
			currElem.prev().attr('id','');
			alert('Copied to clipboard');
		});
	</script>
	<?php
	}else{
		echo '<p>You have not saved any library yet</p>';
	}
}
function adpLibraryAddRemoveLink($lib,$uesrlib,$homeUrl,$add='yes'){
	if(in_array($lib,$uesrlib)){
		echo '<a href="'.$homeUrl.'/?adpRemoveLibFromMyLib='.urlencode($lib).'" class="removeLibBtn button">Remove From My Library</a>';
	}elseif($add=='yes'){
		echo '<a href="'.$homeUrl.'/?adpSaveLibToMyLib='.urlencode($lib).'" class="saveLibBtn button">Save To My Library</a>';
	}
}
add_filter( 'query_vars', function($vars){
	$vars[] = "adpSaveLibToMyLib";
	$vars[] = "adpRemoveLibFromMyLib";
	
	return $vars;
});
add_action('template_redirect', function($template){
	global $wp_query;
	if(!isset( $wp_query->query['adpSaveLibToMyLib']) && !isset( $wp_query->query['adpRemoveLibFromMyLib'])){
		return $template;
	}
	
	if(isset($wp_query->query['adpRemoveLibFromMyLib'])){
		$libPath = urldecode($wp_query->query['adpRemoveLibFromMyLib']);
		$currUserID = get_current_user_id();
		$userLibrary = adpGetCurrentUserLibrary();
		$newUserLibrary = array();
		foreach($userLibrary as $userLib){
			if($userLib==$libPath){
				continue;
			}
			$newUserLibrary[] = $userLib;
		}
		update_user_meta($currUserID,'adpUserLibrariesList',$newUserLibrary);
		$loc = $_SERVER['HTTP_REFERER'];
		header('location: '.$loc);
		exit();
	}
	if(isset($wp_query->query['adpSaveLibToMyLib'])){
		$libPath = urldecode($wp_query->query['adpSaveLibToMyLib']);
		$currUserID = get_current_user_id();
		$userLibrary = adpGetCurrentUserLibrary();
		if(!in_array($libPath,$userLibrary)){
			$userLibrary[] = $libPath;
			update_user_meta($currUserID,'adpUserLibrariesList',$userLibrary);
		}
		$loc = $_SERVER['HTTP_REFERER'];
		header('location: '.$loc);
		exit();
	}
});
function adpGetCurrentUserLibrary(){
	$currUserID = get_current_user_id();
	$userLibrary = get_user_meta($currUserID,'adpUserLibrariesList',true);
	if(!is_array($userLibrary)){
		$userLibrary = array();
	}
	return $userLibrary;
}