<?php
wp_enqueue_style( 'fonts-google-css', 'http://fonts.googleapis.com/css?family=Cabin:400,700,600' );
wp_enqueue_style( 'hor-tree-css', plugins_url( 'lib/horizontal-tree/style.css', WM_PLUGIN_MAIN_FILE ) );
wp_enqueue_style( 'colpick-css', plugins_url('lib/colpick-jQuery-Color-Picker/css/colpick.css', WM_PLUGIN_MAIN_FILE ) );

wp_enqueue_script( 'hor-tree-js', plugins_url( 'lib/horizontal-tree/js/jquery.tree.js', WM_PLUGIN_MAIN_FILE ) );
wp_enqueue_script( 'colpick-js', plugins_url( 'lib/colpick-jQuery-Color-Picker/js/colpick.js', WM_PLUGIN_MAIN_FILE ) );

if( empty( $mp_id ) ){
	$mp_id = wm_create_new_horizontal_map();
		?>
		<div style="background: #CCEBC9 url(<?php echo  plugins_url( 'images/success2_24.png', WM_PLUGIN_MAIN_FILE ) ?>) no-repeat 10px 10px; padding: 10px 10px 10px 40px; border: solid 1px #B0DEA9; color: #508232; margin: 20px 10px;">Horizontal map is created successfully</div>
			<script type="text/javascript">
				window.location.href = "<?php echo get_admin_url() ?>admin.php?page=wm_edit_website_map&mpid=<?php echo $mp_id ?>";
			</script>
		<?php
}

$map = wm_read_map( $mp_id );

$maxWidth = 120;
if( $map['mp_enable_thumbnail'] && $map['mp_thumbnail_size'] > 64 ){
	$maxWidth = 160;
}

$mp_container_width = ( isset( $map['mp_container_width'] ) )? $map['mp_container_width'] : NULL;
$mp_container_height = ( isset( $map['mp_container_height'] ) )? $map['mp_container_height'] : NULL;
?>
<style type="text/css">
	.first_name{color: #<?php echo $map['mp_title_color'] ?>;}
	.first_name a:link, .first_name a:hover, .first_name a:visited, .first_name a:active{color: #<?php echo $map['mp_title_color'] ?>; text-decoration: none;}
	ul.tree li > div { background: #<?php echo $map['mp_bgcolor'] ?>; max-width: <?php echo $maxWidth ?>px;}
	ul.tree li div.current { background: #<?php echo $map['mp_current_bgcolor'] ?>; }
	ul.tree li div.children { background: #<?php echo $map['mp_child_bgcolor'] ?>; }
	ul.tree li div.parent { background: #<?php echo $map['mp_parent_bgcolor'] ?>; }
</style>

<!-- start process message -->
<div id="processMessageContainer" class="processMessageContainer">
	<div id="processMessage" class="processMessage successStatus">
		<span></span>
	</div>
</div>
<!-- end process message -->
<div class="container-fluid wm-form-container wp-tree">
	<form method="post" action="" onsubmit="return false;" id="wm_horizontal_tree_form">
		<input type="hidden" name="mp_id" id="mp_id" value="<?php echo $mp_id ?>" />
		<input type="hidden" name="mp_width" id="mp_width" value="" />
		<input type="hidden" name="mp_height" id="mp_height" value="" />
		<div class="row">
			<div class="col-sm-1">
				<a class="btn btn-primary" style="margin-bottom: 20px;" href="<?php echo get_admin_url() ?>admin.php?page=wm_website_maps"><?php _e('Go back', 'websitemap') ?></a>
			</div>
			
			<div class="col-sm-2">
				<button type="button" class="btn btn-success" onclick="wm_edit_horizontal_map(true);"><?php _e('Save and refresh', 'websitemap') ?></button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-2">
				<label><?php _e('Map Name', 'websitemap') ?></label>
				<input type="text" class="form-control" name="mp_name" value="<?php echo $map['mp_name'] ?>" >
			</div>
			
			<div class="col-sm-2">
				<label><?php _e('Show thumbnail image', 'websitemap') ?></label>
				<select class="form-control" name="mp_enable_thumbnail" onchange="disableElements(['mp_thumbnail_size', 'mp_thumbnail_shape', 'mp_thumbnail_place'], 1, this.value, false)">
					<option value="1" <?php selected( $map['mp_enable_thumbnail'], 1 ) ?>>Yes</option>
					<option value="0" <?php selected( $map['mp_enable_thumbnail'], 0 ) ?>>No</option>
				</select>
			</div>
			
			<div class="col-sm-2">
				<label><?php _e('Thumbnail size', 'websitemap') ?></label>
				<select class="form-control" name="mp_thumbnail_size" <?php echo ( $map['mp_enable_thumbnail'] )? '' : 'disabled'; ?>>
					<option value="32" <?php selected( $map['mp_thumbnail_size'], 32 ) ?>>32x32</option>
					<option value="64" <?php selected( $map['mp_thumbnail_size'], 64 ) ?>>64x64</option>
					<option value="128" <?php selected( $map['mp_thumbnail_size'], 128 ) ?>>128x128</option>
				</select>
			</div>
			
			<div class="col-sm-2">
				<label><?php _e('Thumbnail shape', 'websitemap') ?></label>
				<select class="form-control" name="mp_thumbnail_shape" <?php echo ( $map['mp_enable_thumbnail'] )? '' : 'disabled'; ?>>
					<option value="square" <?php selected( $map['mp_thumbnail_shape'], 'square' ) ?>><?php _e('Square', 'websitemap') ?></option>
					<option value="circle" <?php selected( $map['mp_thumbnail_shape'], 'circle' ) ?>><?php _e('Circle', 'websitemap') ?></option>
				</select>
			</div>
			
			<div class="col-sm-2">
				<label><?php _e('Thumbnail shape', 'websitemap') ?></label>
				<select class="form-control" name="mp_thumbnail_place" <?php echo ( $map['mp_enable_thumbnail'] )? '' : 'disabled'; ?>>
					<option value="above" <?php selected( $map['mp_thumbnail_place'], 'above' ) ?>><?php _e('Above the title', 'websitemap') ?></option>
					<option value="under" <?php selected( $map['mp_thumbnail_place'], 'under' ) ?>><?php _e('Under the title', 'websitemap') ?></option>
				</select>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="col-md-2">
				<label><?php _e('Container width', 'websitemap') ?></label>
				<input type="number" class="form-control" name="mp_container_width" value="<?php echo $mp_container_width; ?>" >
			</div>
			
			<div class="col-md-2">
				<label><?php _e('Container height', 'websitemap') ?></label>
				<input type="number" class="form-control" name="mp_container_height" value="<?php echo $mp_container_height; ?>" >
			</div>
		
			<div class="col-sm-3" style="padding-top: 20px;">
				<div class="row" style="padding: 0 10px;">
					<label class="wm-color-label"><?php _e('Container background color', 'websitemap') ?></label>
					<div id="mp_cont_bgcolor" class="colorPicker"></div>
					<input type="hidden" id="mp_container_bgc" name="mp_container_bgc" value="<?php echo $map['mp_container_bgc'] ?>" />
				</div>
				
				<div class="row" style="padding: 0 10px;">
					<label class="wm-color-label"><?php _e('Title color', 'websitemap') ?></label>
					<div id="mp_titleColor" class="colorPicker"></div>
					<input type="hidden" id="mp_title_color" name="mp_title_color" value="<?php echo $map['mp_title_color'] ?>" />
				</div>
				
                
				<div class="row" style="padding: 0 10px;">
					<label class="wm-color-label"><?php _e('Background color', 'websitemap') ?></label>
					<div id="mp_node_color" class="colorPicker"></div>
					<input type="hidden" id="mp_bgcolor" name="mp_bgcolor" value="<?php echo $map['mp_bgcolor'] ?>" />
				</div>
			</div>
			
			<div class="col-sm-3" style="padding-top: 20px;">
				<div class="row" style="padding: 0 10px;">
					<label class="wm-color-label"><?php _e('Current node on hover', 'websitemap') ?></label>
					<div id="mp_current_node_color" class="colorPicker"></div>
					<input type="hidden" id="mp_current_bgcolor" name="mp_current_bgcolor" value="<?php echo $map['mp_current_bgcolor'] ?>" />
				</div>
				
				<div class="row" style="padding: 0 10px;">
					<label class="wm-color-label"><?php _e('Parent node on hover', 'websitemap') ?></label>
					<div id="mp_parent_node_color" class="colorPicker"></div>
					<input type="hidden" id="mp_parent_bgcolor" name="mp_parent_bgcolor" value="<?php echo $map['mp_parent_bgcolor'] ?>" />
				</div>
				
				<div class="row" style="padding: 0 10px;">
					<label class="wm-color-label"><?php _e('Child node on hover', 'websitemap') ?></label>
					<div id="mp_child_node_color" class="colorPicker"></div>
					<input type="hidden" id="mp_child_bgcolor" name="mp_child_bgcolor" value="<?php echo $map['mp_child_bgcolor'] ?>" />
				</div>
			</div>
		</div>
		

	</form>
	<div class="wm-tree-container" id="treeContainer"><?php echo wm_horizontal_tree( $mp_id ) ?></div>
    		<p>
            	<em>shortcode : [wm_website_map id="<?php echo $mp_id ?>"]</em>
            </p>
</div>
<script type="text/javascript">
	var wm_options = {
		admin_url: '<?php echo admin_url(); ?>',
		images_url: '<?php echo plugins_url( 'lib/horizontal-tree/images/', WM_PLUGIN_MAIN_FILE ); ?>',
		plug_images_url: '<?php echo plugins_url( 'images/', WM_PLUGIN_MAIN_FILE ); ?>',
		mp_enable_thumbnail: <?php echo ( $map['mp_enable_thumbnail'] )? '1' : '0' ?>,
		mp_thumbnail_size: <?php echo ( $map['mp_thumbnail_size'] > 0 )? $map['mp_thumbnail_size'] : '0' ?>,
		mp_thumbnail_shape: '<?php echo $map['mp_thumbnail_shape'] ?>',
		mp_thumbnail_place: '<?php echo $map['mp_thumbnail_place'] ?>'
	};
	
	var WORDPRESS_VER = "<?php echo get_bloginfo("version") ?>";
	
	var formChanged = false;
	
	var fields = [
		'mp_name',
		'mp_enable_thumbnail',
		'mp_thumbnail_size',
		'mp_thumbnail_shape',
		'mp_thumbnail_place',
		'mp_container_bgc',
		'mp_title_color',
		'mp_bgcolor',
		'mp_current_bgcolor',
		'mp_parent_bgcolor',
		'mp_child_bgcolor',
		'mp_container_width',
		'mp_container_height'
	];
	monitorFormChanges(fields);
	
	jQuery(document).ready(function(){
		setInterval(function(){
			if(formChanged){
				wm_edit_horizontal_map();
			}
		}, 10000);
		
		jQuery('#mp_node_color').colpick({
			colorScheme: 'dark',
			layout: 'hex',
			color: jQuery('#mp_node_bgcolor').val(),
			onSubmit:function(hsb, hex, rgb, el){
				jQuery('#mp_bgcolor').val(hex);
				jQuery(el).css('background-color', '#' + hex);
				jQuery(el).colpickHide();
				formChanged = true;
			}
		}).css('background-color', '#' + jQuery('#mp_bgcolor').val());
		
		jQuery('#mp_current_node_color').colpick({
			colorScheme: 'dark',
			layout: 'hex',
			color: jQuery('#mp_current_bgcolor').val(),
			onSubmit:function(hsb, hex, rgb, el){
				jQuery('#mp_current_bgcolor').val(hex);
				jQuery(el).css('background-color', '#' + hex);
				jQuery(el).colpickHide();
				formChanged = true;
			}
		}).css('background-color', '#' + jQuery('#mp_current_bgcolor').val());
		
		jQuery('#mp_parent_node_color').colpick({
			colorScheme: 'dark',
			layout: 'hex',
			color: jQuery('#mp_parent_bgcolor').val(),
			onSubmit:function(hsb, hex, rgb, el){
				jQuery('#mp_parent_bgcolor').val(hex);
				jQuery(el).css('background-color', '#' + hex);
				jQuery(el).colpickHide();
				formChanged = true;
			}
		}).css('background-color', '#' + jQuery('#mp_parent_bgcolor').val());
		
		jQuery('#mp_child_node_color').colpick({
			colorScheme: 'dark',
			layout: 'hex',
			color: jQuery('#mp_child_bgcolor').val(),
			onSubmit:function(hsb, hex, rgb, el){
				jQuery('#mp_child_bgcolor').val(hex);
				jQuery(el).css('background-color', '#' + hex);
				jQuery(el).colpickHide();
				formChanged = true;
			}
		}).css('background-color', '#' + jQuery('#mp_child_bgcolor').val());
		
		jQuery('#mp_titleColor').colpick({
			colorScheme: 'dark',
			layout: 'hex',
			color: jQuery('#mp_title_color').val(),
			onSubmit:function(hsb, hex, rgb, el){
				jQuery('#mp_title_color').val(hex);
				jQuery(el).css('background-color', '#' + hex);
				jQuery(el).colpickHide();
				formChanged = true;
			}
		}).css('background-color', '#' + jQuery('#mp_title_color').val());
		
		
		jQuery('#mp_cont_bgcolor').colpick({
			colorScheme: 'dark',
			layout: 'hex',
			color: jQuery('#mp_container_bgc').val(),
			onSubmit:function(hsb, hex, rgb, el){
				jQuery('#mp_container_bgc').val(hex);
				jQuery(el).css('background-color', '#' + hex);
				jQuery(el).colpickHide();
				formChanged = true;
			}
		}).css('background-color', '#' + jQuery('#mp_container_bgc').val());
		
		setTimeout(function(){
			if(isEmpty(jQuery('[name="mp_container_width"]').val()) && isEmpty(jQuery('[name="mp_container_height"]').val())){
				wm_setContainerDimentions();
			}
		}, 2000);
	});
	
	window.onbeforeunload = function(){
		if(formChanged){
			return false;
		}
	}
</script>

