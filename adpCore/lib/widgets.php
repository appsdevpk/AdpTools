<?php
add_action( 'widgets_init', function(){
	adpInitWidgets();
	adpInitWidgetAreas();
} );
function adpInitWidgetAreas(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/widgetareas/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$widgetAreasFiles = glob('*.json');
		
		if($widgetAreasFiles){
			foreach($widgetAreasFiles as $areaFile){
				$fileName = basename($areaFile);
				$areaID = str_ireplace('.json','',$fileName);
				$configFileUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/widgetareas/'.$fileName;
				$areaConfig = json_decode(file_get_contents($configFileUrl),true);
				if(!isset($areaConfig['id'])){
					$areaConfig['id'] = $areaID;
				}
				register_sidebar($areaConfig);
			}
		}
	}
}
function adpEmbedWidgetArea($areaid){
	ob_start();
	if ( is_active_sidebar( $areaid ) ){
		?>
		<div id="widgetarea-<?php echo $areaid; ?>" class="<?php echo $areaid; ?>">
			<?php dynamic_sidebar( $areaid ); ?>
		</div>
		<?php
	}
	$output = ob_get_clean();
	return do_shortcode($output);
}
function adpInitWidgets(){
	$dirPath = get_stylesheet_directory().'/'.APP_DIRECTORY.'/widgets/';
	if(file_exists($dirPath)){
		chdir($dirPath);
		$themeWidgetFiles = glob('*.php');
		
		if($themeWidgetFiles){
			foreach($themeWidgetFiles as $widgetFile){
				$fileName = str_ireplace('.blade.php','',basename($widgetFile));
				
				$configFile = get_stylesheet_directory().'/'.APP_DIRECTORY.'/widgets/'.$fileName.'.json';
				
				if(file_exists($configFile)){
					$configFileUrl = get_stylesheet_directory_uri().'/'.APP_DIRECTORY.'/widgets/'.$fileName.'.json';
					$widgetConfig = json_decode(file_get_contents($configFileUrl),true);
					
					if(isset($widgetConfig['Config'])){
						adpInitWidgetClass($widgetConfig,$fileName);
					}
				}
			}
		}
	}
}

class AdpWidget extends WP_Widget {
	public $widgetConfig = [];
	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="widget-wrap">',
		'after_widget'  => '</div></div>',
	);
	
	public function __construct($widgetConfig=array()) {
		$this->widgetConfig = $widgetConfig;
		$this->ars = array(
			'before_title'  => $this->widgetConfig['Config']['before_title'],
			'after_title'   => $this->widgetConfig['Config']['after_title'],
			'before_widget' => $this->widgetConfig['Config']['before_widget'],
			'after_widget'  => $this->widgetConfig['Config']['after_widget'],
		);
		parent::__construct($this->widgetConfig['Config']['WidgetID'],$this->widgetConfig['Config']['Name']);
		
		$className = $this->widgetConfig['Config']['ClassName'];
	}

	public function widget( $args, $instance ) {
		echo adpRenderBladeView($this->widgetConfig['Config']['WidgetID'],'widgets',array(
			'args'=>$args,
			'instance'=>$instance
		));
	}

	public function form( $instance ) {
		if(isset($this->widgetConfig['Fields'])){
			$fieldVals = array();
			foreach($this->widgetConfig['Fields'] as $key=>$val){
				$fieldVals[$key] = ! empty( $instance[$key] ) ? $instance[$key] : '';
			}
			foreach($this->widgetConfig['Fields'] as $key=>$val){
				?>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html__( $val['label'], '' ); ?></label>
					<?php 
					adpRenderWidgetField($val,esc_attr( $this->get_field_id( $key ) ),esc_attr( $this->get_field_name( $key ) ),esc_attr( $fieldVals[$key] )); 
					?>
				</p>
				<?php
			}
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		if(isset($this->widgetConfig['Fields'])){
			$fieldVals = array();
			foreach($this->widgetConfig['Fields'] as $key=>$val){
				$fieldVals[$key] = ! empty( $instance[$key] ) ? $instance[$key] : '';
				$instance[$key]  = ( ! empty( $new_instance[$key] ) ) ? $new_instance[$key] : '';
			}
		}

		return $instance;
	}
}
function adpRenderWidgetField($fldOptions,$fldID,$fldName,$defaultval=''){
	$wtype = $fldOptions['type'];
	$wtypeParts = explode('-',$wtype);
	$inputAttribs = isset($fldOptions['attributes']) ?  str_ireplace("'",'"',$fldOptions['attributes']) : '';
	$defaultValue = isset($fldOptions['default']) ?  $fldOptions['default'] : '';
	
	if($wtypeParts[0]=='textarea'){
		echo '<textarea '.$inputAttribs.' class="widefat" id="'.$fldID.'" name="'.$fldName.'" type="text" cols="30" rows="10">'.$defaultval.'</textarea>';
	}elseif($wtypeParts[0]=='input' && count($wtypeParts) > 1){
		echo '<input '.$inputAttribs.' class="widefat" id="'.$fldID.'" name="'.$fldName.'" type="'.$wtypeParts[1].'" value="'.$defaultval.'" />';
	}elseif($wtypeParts[0]=='select' && isset($fldOptions['options'])){
		?>
		<select <?php echo $inputAttribs; ?> name="<?php echo $fldName; ?>" id="<?php echo $fldID; ?>">
			<?php foreach($fldOptions['options'] as $key=>$val){ ?>
			<option <?php echo $defaultval==$key ? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
			<?php } ?>
		</select>
		<?php
	}
}

function adpInitWidgetClass($widgetConfig,$fileName){
	$widgetConfig['Config']['WidgetID'] = $fileName;
	class_alias('AdpWidget', $widgetConfig['Config']['ClassName']);
	
	$widgetsList[$fileName] = new $widgetConfig['Config']['ClassName']($widgetConfig);
	register_widget($widgetsList[$fileName]);
}