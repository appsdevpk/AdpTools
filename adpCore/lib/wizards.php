<?php
$wizardsConfig = array(
	'FieldTypes'=>array(),
	'Wizards'=>array(
		'Dashboard Widgets'=>array(
			'Icon'=>'<i class="fa-solid fa-gauge"></i>',
			'FunctionName'=>'adpCreateDashboardWidget',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Title'=>array(
					'Type'=>'text'
				),
				'Template'=>array(
					'Type'=>'editor'
				)
			)
		),
		'Help Tabs'=>array(
			'Icon'=>'<i class="fa-brands fa-hire-a-helper"></i>',
			'FunctionName'=>'adpCreateHelpTabFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Title'=>array(
					'Type'=>'text'
				),
				'Content'=>array(
					'Type'=>'editor'
				)
			)
		),
		'Live Components'=>array(
			'Icon'=>'<i class="fa-solid fa-chart-line"></i>',
			'FunctionName'=>'adpCreateLiveComponentFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Content'=>array(
					'Type'=>'editor'
				)
			)
		),
		'Post types'=>array(
			'Icon'=>'<i class="fa-regular fa-address-book"></i>',
			'FunctionName'=>'adpCreatePostTypeFunc',
			'Fields'=>array(
				'Name'=>array(
					'Type'=>'text'
				),
				'Singular'=>array(
					'Type'=>'text'
				),
				'Plural'=>array(
					'Type'=>'text'
				)
			)
		),
		'Taxonomies'=>array(
			'Icon'=>'<i class="fa-solid fa-layer-group"></i>',
			'FunctionName'=>'adpCreateTaxonomyFunc',
			'Fields'=>array(
				'Name'=>array(
					'Type'=>'text'
				),
				'Content Type'=>array(
					'Type'=>'post_types'
				),
				'Singular'=>array(
					'Type'=>'text'
				),
				'Plural'=>array(
					'Type'=>'text'
				)
			)
		),
		'Shortcodes'=>array(
			'Icon'=>'<i class="fa-solid fa-code"></i>',
			'FunctionName'=>'adpCreateShortcodeFunc',
			'Fields'=>array(
				'Name'=>array(
					'Type'=>'text'
				),
				'Content'=>array(
					'Type'=>'editor'
				)
			)
		),
		'Widget Areas'=>array(
			'Icon'=>'<i class="fa-solid fa-warehouse"></i>',
			'FunctionName'=>'adpCreateWidgetAreaFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Name'=>array(
					'Type'=>'text'
				),
				'Description'=>array(
					'Type'=>'textarea'
				),
				'Before Widget'=>array(
					'Type'=>'editor'
				),
				'After Widget'=>array(
					'Type'=>'editor'
				),
				'Before Title'=>array(
					'Type'=>'editor'
				),
				'After Title'=>array(
					'Type'=>'editor'
				)
			)
		),
		'Conditionals'=>array(
			'Icon'=>'<i class="fa-solid fa-diagram-project"></i>',
			'FunctionName'=>'adpCreateConditionalsFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Name'=>array(
					'Type'=>'text'
				),
				'Template'=>array(
					'Type'=>'editor'
				),
				'Contents List'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add Content',
					'Fields'=>array(
						'Variable Name'=>array(
							'Type'=>'text'
						),
						'Variable Content'=>array(
							'Type'=>'editor'
						)
					)
				)
			)
		),
		'Widgets'=>array(
			'Icon'=>'<i class="fa-solid fa-gear"></i>',
			'FunctionName'=>'adpCreateWidgetFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Name'=>array(
					'Type'=>'text'
				),
				'Class Name'=>array(
					'Type'=>'text'
				),
				'Before Title'=>array(
					'Type'=>'text'
				),
				'After Title'=>array(
					'Type'=>'text'
				),
				'Before Widget'=>array(
					'Type'=>'text'
				),
				'After Widget'=>array(
					'Type'=>'text'
				),
				'Template'=>array(
					'Type'=>'editor'
				),
				'Fields'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add Field',
					'HelpText'=>'You can enter multiple options in default value for select, checkboxes or radio. For select you can have a pair of key|value for each option like opt1|Option 1,opt2|Option 2',
					'Fields'=>array(
						'Field ID'=>array(
							'Type'=>'text'
						),
						'Field Type'=>array(
							'Type'=>'select',
							'Options'=>array(
								'input-text'=>'Text',
								'input-checkbox'=>'Checkbox',
								'input-color'=>'Color',
								'input-date'=>'Date',
								'input-email'=>'Email',
								'input-file'=>'File',
								'input-hidden'=>'Hidden',
								'input-image'=>'Image Input',
								'input-month'=>'Month',
								'input-number'=>'Number',
								'input-password'=>'Password',
								'input-radio'=>'Radio',
								'input-range'=>'Range',
								'input-search'=>'Search',
								'input-tel'=>'Tel',
								'input-time'=>'Time',
								'input-url'=>'Url',
								'input-week'=>'Week',
								'textarea'=>'Text Area',
								'select'=>'Select'
							)
						),
						'Label'=>array(
							'Type'=>'text'
						),
						'Default Value'=>array(
							'Type'=>'text'
						)
					)
				)
			)
		),
		'Server Components'=>array(
			'Icon'=>'<i class="fa-solid fa-server"></i>',
			'FunctionName'=>'adpCreateServerComponentsFunc',
			'Fields'=>array(
				'Name'=>array(
					'Type'=>'text'
				),
				'Content'=>array(
					'Type'=>'editor'
				),
				'States'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add State',
					'Fields'=>array(
						'State Name'=>array(
							'Type'=>'text'
						),
						'State Type'=>array(
							'Type'=>'text'
						),
						'State Value'=>array(
							'Type'=>'textarea'
						)
					)
				),
				'Attributes'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add Attribute',
					'Fields'=>array(
						'Attribute Name'=>array(
							'Type'=>'text'
						),
						'Attribute Type'=>array(
							'Type'=>'text'
						),
						'Attribute Value'=>array(
							'Type'=>'textarea'
						)
					)
				),
				'Flows'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add Flow',
					'Fields'=>array(
						'Flow Type'=>array(
							'Type'=>'select',
							'Options'=>array(
								'listen'=>'Listen',
								'handle'=>'Handle'
							)
						),
						'Flow Name'=>array(
							'Type'=>'text'
						),
						'Flow Ref'=>array(
							'Type'=>'text'
						),
						'Flow Content'=>array(
							'Type'=>'textarea'
						)
					)
				)
			)
		),
		'Meta Boxes'=>array(
			'Icon'=>'<i class="fa-solid fa-boxes-stacked"></i>',
			'FunctionName'=>'adpCreateMetaBoxFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Title'=>array(
					'Type'=>'text'
				),
				'Description'=>array(
					'Type'=>'textarea'
				),
				'Object Types'=>array(
					'Type'=>'text',
					'HelpText'=>'Separate multiple object types with comma'
				),
				'Fields'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add Field',
					'HelpText'=>'Options are for select, checkboxes and redio (separate multiple options with comma, each option can have key|value pair)',
					'Fields'=>array(
						'Field ID'=>array(
							'Type'=>'text'
						),
						'Field Type'=>array(
							'Type'=>'select',
							'Options'=>array(
								'title'=>'Title',
								'text'=>'Text',
								'text_small'=>'Text Small',
								'text_medium'=>'Text Medium',
								'text_email'=>'Text Email',
								'text_url'=>'Text Url',
								'text_money'=>'Text Money',
								'textarea'=>'Textarea',
								'textarea_small'=>'Textarea Small',
								'textarea_code'=>'Textarea Code',
								'text_time'=>'Text Time',
								'select_timezone'=>'Select Timezone',
								'text_date'=>'Text Date',
								'text_date_timestamp'=>'Text Date Timestamp',
								'text_datetime_timestamp'=>'Text Datetime Timestamp',
								'text_datetime_timestamp_timezone'=>'Text Datetime Timestamp Timezone',
								'hidden'=>'Hidden',
								'colorpicker'=>'Color Picker',
								'radio'=>'Radio',
								'radio_inline'=>'Radio Inline',
								'taxonomy_radio'=>'Taxonomy Radio',
								'taxonomy_radio_inline'=>'Taxonomy Radio Inline',
								'taxonomy_radio_hierarchical'=>'Taxonomy Radio Hierarchical',
								'select'=>'Select',
								'taxonomy_select'=>'Taxonomy Select',
								'checkbox'=>'Checkbox',
								'multicheck'=>'Multicheck',
								'multicheck_inline'=>'Multicheck Inline',
								'taxonomy_multicheck'=>'Taxonomy Multicheck',
								'taxonomy_multicheck_inline'=>'Taxonomy Multicheck Inline',
								'taxonomy_multicheck_hierarchical'=>'Taxonomy Multicheck Hierarchical',
								'wysiwyg'=>'Wysiwyg',
								'file'=>'File',
								'file_list'=>'File List',
								'oembed'=>'OEmbed',
								'group'=>'Group'
							)
						),
						'Field Title'=>array(
							'Type'=>'text'
						),
						'Field Options'=>array(
							'Type'=>'text'
						),
						'Parent Group'=>array(
							'Type'=>'text',
							'HelpText'=>'Optional: ID of parent group field'
						)
					)
				)
			)
		),
		'Admin Pages'=>array(
			'Icon'=>'<i class="fa-solid fa-screwdriver-wrench"></i>',
			'FunctionName'=>'adpCreateAdminFunc',
			'Fields'=>array(
				'ID'=>array(
					'Type'=>'text'
				),
				'Title'=>array(
					'Type'=>'text'
				),
				'Option Key'=>array(
					'Type'=>'text'
				),
				'Parent Key'=>array(
					'Type'=>'text'
				),
				'Fields'=>array(
					'Type'=>'array',
					'AddButtonText'=>'Add Field',
					'HelpText'=>'Options are for select, checkboxes and redio (separate multiple options with comma, each option can have key|value pair)',
					'Fields'=>array(
						'Field ID'=>array(
							'Type'=>'text'
						),
						'Field Type'=>array(
							'Type'=>'select',
							'Options'=>array(
								'title'=>'Title',
								'text'=>'Text',
								'text_small'=>'Text Small',
								'text_medium'=>'Text Medium',
								'text_email'=>'Text Email',
								'text_url'=>'Text Url',
								'text_money'=>'Text Money',
								'textarea'=>'Textarea',
								'textarea_small'=>'Textarea Small',
								'textarea_code'=>'Textarea Code',
								'text_time'=>'Text Time',
								'select_timezone'=>'Select Timezone',
								'text_date'=>'Text Date',
								'text_date_timestamp'=>'Text Date Timestamp',
								'text_datetime_timestamp'=>'Text Datetime Timestamp',
								'text_datetime_timestamp_timezone'=>'Text Datetime Timestamp Timezone',
								'hidden'=>'Hidden',
								'colorpicker'=>'Color Picker',
								'radio'=>'Radio',
								'radio_inline'=>'Radio Inline',
								'taxonomy_radio'=>'Taxonomy Radio',
								'taxonomy_radio_inline'=>'Taxonomy Radio Inline',
								'taxonomy_radio_hierarchical'=>'Taxonomy Radio Hierarchical',
								'select'=>'Select',
								'taxonomy_select'=>'Taxonomy Select',
								'checkbox'=>'Checkbox',
								'multicheck'=>'Multicheck',
								'multicheck_inline'=>'Multicheck Inline',
								'taxonomy_multicheck'=>'Taxonomy Multicheck',
								'taxonomy_multicheck_inline'=>'Taxonomy Multicheck Inline',
								'taxonomy_multicheck_hierarchical'=>'Taxonomy Multicheck Hierarchical',
								'wysiwyg'=>'Wysiwyg',
								'file'=>'File',
								'file_list'=>'File List',
								'oembed'=>'OEmbed',
								'group'=>'Group'
							)
						),
						'Field Title'=>array(
							'Type'=>'text'
						),
						'Field Options'=>array(
							'Type'=>'text'
						),
						'Parent Group'=>array(
							'Type'=>'text',
							'HelpText'=>'Optional: ID of parent group field'
						)
					)
				)
			)
		)
	)
);
function adpCreateDashboardWidget(){
	$widgetid = $_REQUEST['id'];
	$title = $_REQUEST['title'];
	$template = $_REQUEST['template'];
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathDashWidgets = $directoryPath.'admindashwidgets/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathDashWidgets)){
		mkdir($directoryPathDashWidgets,0777);
	}
	$widgetConfigFile = $directoryPathDashWidgets.$widgetid.'.json';
	$widgetTemplateFile = $directoryPathDashWidgets.$widgetid.'.blade.php';
	
	$widgetConfigFileContent = '{"Title":"'.$title.'"}';
	$widgetTemplateFileContent = $template;
	
	file_put_contents($widgetConfigFile,$widgetConfigFileContent);
	file_put_contents($widgetTemplateFile,$widgetTemplateFileContent);
}
function adpCreateHelpTabFunc(){
	$tabid = $_REQUEST['id'];
	$title = $_REQUEST['title'];
	$template = $_REQUEST['content'];
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathHelpTabs = $directoryPath.'helptabs/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathHelpTabs)){
		mkdir($directoryPathHelpTabs,0777);
	}
	$tabConfigFile = $directoryPathHelpTabs.$tabid.'.json';
	$tabTemplateFile = $directoryPathHelpTabs.$tabid.'.blade.php';
	
	$tabConfigFileContent = '{"Title":"'.$title.'"}';
	$tabTemplateFileContent = $template;
	
	file_put_contents($tabConfigFile,$tabConfigFileContent);
	file_put_contents($tabTemplateFile,$tabTemplateFileContent);
}
function adpCreateAppFolder(){
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathCache = $directoryPath.'cache/';
	
	if(!file_exists($directoryPath)){
		mkdir($directoryPath,0777);
		mkdir($directoryPathCache,0777);
	}
}
function adpCreateLiveComponentFunc(){
	$componentID = $_REQUEST['id'];
	$componentCode = $_REQUEST['content'];
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathLiveComponents = $directoryPath.'livecomponents/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathLiveComponents)){
		mkdir($directoryPathLiveComponents,0777);
	}
	$componentTemplateFile = $directoryPathLiveComponents.$componentID.'.blade.php';
	
	file_put_contents($componentTemplateFile,$componentCode);
}
function adpCreatePostTypeFunc(){
	$name = $_REQUEST['name'];
	$singular = $_REQUEST['singular'];
	$plural = $_REQUEST['plural'];
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathPostTypes = $directoryPath.'posttypes/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathPostTypes)){
		mkdir($directoryPathPostTypes,0777);
	}
	$postTypeConfigFile = $directoryPathPostTypes.$name.'.json';
	$postTypeConfigFileContent = '{"singular":"'.$singular.'","plural":"'.$plural.'"}';
	
	file_put_contents($postTypeConfigFile,$postTypeConfigFileContent);
}
function adpCreateTaxonomyFunc(){
	$name = $_REQUEST['name'];
	$content_type = $_REQUEST['content_type'];
	$singular = $_REQUEST['singular'];
	$plural = $_REQUEST['plural'];
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathTaxonomies = $directoryPath.'taxonomies/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathTaxonomies)){
		mkdir($directoryPathTaxonomies,0777);
	}
	$taxonomyConfigFile = $directoryPathTaxonomies.$name.'.json';
	$taxonomyConfigFileContent = '{"posttype":"'.$content_type.'","singular":"'.$singular.'","plural":"'.$plural.'"}';
	
	file_put_contents($taxonomyConfigFile,$taxonomyConfigFileContent);
}
function adpCreateShortcodeFunc(){
	$shortcodeName = $_REQUEST['name'];
	$shortcodeContent = $_REQUEST['content'];
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathShortcodes = $directoryPath.'shortcodes/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathShortcodes)){
		mkdir($directoryPathShortcodes,0777);
	}
	$shortcodeTemplateFile = $directoryPathShortcodes.$shortcodeName.'.blade.php';
	
	file_put_contents($shortcodeTemplateFile,$shortcodeContent);
}
function adpCreateWidgetAreaFunc(){
	$widgetAreaID = $_REQUEST['id'];
	$widgetAreaName = $_REQUEST['name'];
	$widgetAreaDescription = $_REQUEST['description'];
	$before_widget = $_REQUEST['before_widget'];
	$after_widget = $_REQUEST['after_widget'];
	$before_title = $_REQUEST['before_title'];
	$after_title = $_REQUEST['after_title'];
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathWidgetAreas = $directoryPath.'widgetareas/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathWidgetAreas)){
		mkdir($directoryPathWidgetAreas,0777);
	}
	$widgetAreaTemplateFile = $directoryPathWidgetAreas.$widgetAreaID.'.json';
	$fileContent = '{"id":"'.$widgetAreaID.'","name":"'.$widgetAreaName.'","description":"'.$widgetAreaDescription.'","before_widget":"'.$before_widget.'","after_widget":"'.$after_widget.'","before_title":"'.$before_title.'","after_title":"'.$after_title.'"}';
	
	file_put_contents($widgetAreaTemplateFile,$fileContent);
}
function adpCreateConditionalsFunc(){
	$conditionalId = $_REQUEST['id'];
	$conditionalName = $_REQUEST['name'];
	$conditionalTemplate = $_REQUEST['template'];
	
	$fileContent = array(
		"Name"=>$conditionalName
	);
	if(isset($_REQUEST['variable_name']) && is_array($_REQUEST['variable_name']) && count($_REQUEST['variable_name']) > 1){
		$contentsList = array();
		foreach($_REQUEST['variable_name'] as $ind=>$variableName){
			if(trim($variableName)!=''){
				$contentsList[$variableName] = $_REQUEST['variable_content'][$ind];
			}
		}
		if(count($contentsList) > 0){
			$fileContent['Contents'] = $contentsList;
		}
	}
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathConditionals = $directoryPath.'conditionals/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathConditionals)){
		mkdir($directoryPathConditionals,0777);
	}
	$conditionalsConfigFile = $directoryPathConditionals.$conditionalId.'.json';
	$conditionalsTemplateFile = $directoryPathConditionals.$conditionalId.'.blade.php';
	
	file_put_contents($conditionalsConfigFile,json_encode($fileContent));
	file_put_contents($conditionalsTemplateFile,stripslashes($conditionalTemplate));
}
function adpCreateWidgetFunc(){
	$widgetId = $_REQUEST['id'];
	$widgetName = $_REQUEST['name'];
	$widgetClassName = $_REQUEST['class_name'];
	$widgetTemplate = $_REQUEST['template'];
	
	$before_title = $_REQUEST['before_title'];
	$after_title = $_REQUEST['after_title'];
	$before_widget = $_REQUEST['before_widget'];
	$after_widget = $_REQUEST['after_widget'];
	
	$fileContent = array(
		"Config"=>array(
			"ClassName"=>$widgetClassName,
			"Name"=>$widgetName,
			"before_title"=>$before_title,
			"after_title"=>$after_title,
			"before_widget"=>$before_widget,
			"after_widget"=>$after_widget
		)
	);
	if(isset($_REQUEST['field_id']) && is_array($_REQUEST['field_id']) && count($_REQUEST['field_id']) > 1){
		foreach($_REQUEST['field_id'] as $ind=>$field_id){
			if(trim($field_id)!=''){
				$fileContent['Fields'][$field_id] = array(
					"type"=>$_REQUEST['field_type'][$ind],
					"label"=>$_REQUEST['label'][$ind]
				);
				if($_REQUEST['field_type'][$ind]=='select' && $_REQUEST['default_value'][$ind]!=''){
					$optionsList = explode(',',$_REQUEST['default_value'][$ind]);
					foreach($optionsList as $opt){
						$optParts = explode('|',$opt);
						$optKey = $optParts[0];
						$optVal = isset($optParts[1]) ? $optParts[1] : $optKey;
						$fileContent['Fields'][$field_id]['options'][$optKey] = $optVal;
					}
				}
			}
		}
	}
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathWidgets = $directoryPath.'widgets/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathWidgets)){
		mkdir($directoryPathWidgets,0777);
	}
	$widgetsConfigFile = $directoryPathWidgets.$widgetId.'.json';
	$widgetsTemplateFile = $directoryPathWidgets.$widgetId.'.blade.php';
	
	file_put_contents($widgetsConfigFile,json_encode($fileContent));
	file_put_contents($widgetsTemplateFile,stripslashes($widgetTemplate));
}
function adpCreateServerComponentsFunc(){
	$componentName = $_REQUEST['name'];
	$componentTemplate = $_REQUEST['content'];
	$fileContent = array();
	
	if(isset($_REQUEST['state_name']) && is_array($_REQUEST['state_name']) && count($_REQUEST['state_name']) > 1){
		foreach($_REQUEST['state_name'] as $ind=>$state_name){
			if(trim($state_name)!=''){
				$fileContent['states'][$state_name] = array(
					"type"=>$_REQUEST['state_type'][$ind],
					"default"=>$_REQUEST['state_value'][$ind]
				);
			}
		}
	}
	if(isset($_REQUEST['attribute_name']) && is_array($_REQUEST['attribute_name']) && count($_REQUEST['attribute_name']) > 1){
		foreach($_REQUEST['attribute_name'] as $ind=>$attribute_name){
			if(trim($attribute_name)!=''){
				$fileContent['attributes'][$attribute_name] = array(
					"type"=>$_REQUEST['attribute_type'][$ind],
					"default"=>$_REQUEST['attribute_value'][$ind]
				);
			}
		}
	}
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathComponents = $directoryPath.'servercomponents/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathComponents)){
		mkdir($directoryPathComponents,0777);
	}
	$componentConfigFile = $directoryPathComponents.$componentName.'.json';
	$componentTemplateFile = $directoryPathComponents.$componentName.'.blade.php';
	$directoryPathComponentsFlow = $directoryPathComponents.$componentName.'-flows/';
	mkdir($directoryPathComponentsFlow,0777);
	
	file_put_contents($componentConfigFile,json_encode($fileContent));
	file_put_contents($componentTemplateFile,stripslashes($componentTemplate));
	
	if(isset($_REQUEST['flow_type']) && is_array($_REQUEST['flow_type']) && count($_REQUEST['flow_type']) > 1){
		foreach($_REQUEST['flow_type'] as $ind=>$flow_type){
			$fileName = '';
			if($flow_type=='listen'){
				$fileName = 'listen-'.$_REQUEST['flow_name'];
				if($_REQUEST['flow_ref']!=''){
					$fileName .= '-'.$_REQUEST['flow_ref'];
				}
			}else{
				$fileName = $_REQUEST['flow_name'].'-'.$_REQUEST['flow_ref'];
			}
			$componentFlowFile = $directoryPathComponentsFlow.$fileName.'.php';
			file_put_contents($componentFlowFile,stripslashes($_REQUEST['flow_content']));
		}
	}
}
function adpCreateMetaBoxFunc(){
	$metaboxID = $_REQUEST['id'];
	$metaboxTitle = $_REQUEST['title'];
	$metaboxDescription = $_REQUEST['description'];
	$metaboxObjectTypes = $_REQUEST['object_types'];
	$fileContent = array(
		"config"=>array(
			"title"=>$metaboxTitle,
			"object_types"=>explode(",",$metaboxObjectTypes),
			"context"=>"normal",
			"priority"=>"high",
			"show_names"=>true
		)
	);
	
	if(isset($_REQUEST['field_id']) && is_array($_REQUEST['field_id']) && count($_REQUEST['field_id']) > 1){
		$groupFields = array();
		foreach($_REQUEST['field_id'] as $ind=>$field_id){
			if(trim($field_id)!=''){
				$fieldType = $_REQUEST['field_type'][$ind];
				$parentGroup = trim($_REQUEST['parent_group'][$ind]);
				
				if($parentGroup!=''){
					$groupFields[$parentGroup][$field_id] = array(
						"name"=>$_REQUEST['field_title'][$ind],
						"type"=>$fieldType
					);
				}else{
					$fileContent['fields'][$field_id] = array(
						"name"=>$_REQUEST['field_title'][$ind],
						"type"=>$fieldType
					);
				}
				
				if($fieldType=='group'){
					$fileContent['fields'][$field_id]['options'] = array(
						"group_title"=>"Entry {#}",
						"add_button"=>"Add Another Entry",
						"remove_button"=>"Remove Entry",
						"sortable"=>true
					);
				}
				
				if(in_array($fieldType,array('multicheck','select','radio','radio_inline'))){
					$fieldOptions = explode(',',$_REQUEST['field_options'][$ind]);
					foreach($fieldOptions as $opt){
						$optParts = explode('|',$opt);
						$optKey = $optParts[0];
						$optValue = isset($optParts[1]) ? $optParts[1] : $optKey;
						if($parentGroup!=''){
							$groupFields[$parentGroup][$field_id]['options'][$optKey] = $optValue;
						}else{
							$fileContent['fields'][$field_id]['options'][$optKey] = $optValue;
						}
					}
				}
			}
		}
		if(count($groupFields) > 0){
			foreach($groupFields as $key=>$val){
				if(isset($fileContent['fields'][$key])){
					foreach($val as $k=>$v){
						$fileContent['fields'][$key]['fields'][$k] = $v;
					}
				}
			}
		}
	}
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathMetaboxes = $directoryPath.'metaboxes/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathMetaboxes)){
		mkdir($directoryPathMetaboxes,0777);
	}
	$metaboxConfigFile = $directoryPathMetaboxes.$metaboxID.'.json';
	
	file_put_contents($metaboxConfigFile,json_encode($fileContent));
}
function adpCreateAdminFunc(){
	$metaboxID = $_REQUEST['id'];
	$metaboxTitle = $_REQUEST['title'];
	$option_key = $_REQUEST['option_key'];
	$parent_key = $_REQUEST['parent_key'];
	$fileContent = array(
		"config"=>array(
			"title"=>$metaboxTitle,
			"option_key"=>$option_key,
			"parent_slug"=>$parent_key
		)
	);
	
	if(isset($_REQUEST['field_id']) && is_array($_REQUEST['field_id']) && count($_REQUEST['field_id']) > 1){
		$groupFields = array();
		foreach($_REQUEST['field_id'] as $ind=>$field_id){
			if(trim($field_id)!=''){
				$fieldType = $_REQUEST['field_type'][$ind];
				$parentGroup = trim($_REQUEST['parent_group'][$ind]);
				
				if($parentGroup!=''){
					$groupFields[$parentGroup][$field_id] = array(
						"name"=>$_REQUEST['field_title'][$ind],
						"type"=>$fieldType
					);
				}else{
					$fileContent['fields'][$field_id] = array(
						"name"=>$_REQUEST['field_title'][$ind],
						"type"=>$fieldType
					);
				}
				
				if($fieldType=='group'){
					$fileContent['fields'][$field_id]['options'] = array(
						"group_title"=>"Entry {#}",
						"add_button"=>"Add Another Entry",
						"remove_button"=>"Remove Entry",
						"sortable"=>true
					);
				}
				
				if(in_array($fieldType,array('multicheck','select','radio','radio_inline'))){
					$fieldOptions = explode(',',$_REQUEST['field_options'][$ind]);
					foreach($fieldOptions as $opt){
						$optParts = explode('|',$opt);
						$optKey = $optParts[0];
						$optValue = isset($optParts[1]) ? $optParts[1] : $optKey;
						if($parentGroup!=''){
							$groupFields[$parentGroup][$field_id]['options'][$optKey] = $optValue;
						}else{
							$fileContent['fields'][$field_id]['options'][$optKey] = $optValue;
						}
					}
				}
			}
		}
		if(count($groupFields) > 0){
			foreach($groupFields as $key=>$val){
				if(isset($fileContent['fields'][$key])){
					foreach($val as $k=>$v){
						$fileContent['fields'][$key]['fields'][$k] = $v;
					}
				}
			}
		}
	}
	
	$directoryPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/';
	$directoryPathMetaboxes = $directoryPath.'adminpages/';
	
	adpCreateAppFolder();
	
	if(!file_exists($directoryPathMetaboxes)){
		mkdir($directoryPathMetaboxes,0777);
	}
	$metaboxConfigFile = $directoryPathMetaboxes.$metaboxID.'.json';
	
	file_put_contents($metaboxConfigFile,json_encode($fileContent));
}