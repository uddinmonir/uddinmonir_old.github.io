<?php
$map = wm_read_map( $mp_id );
$uniqid = uniqid();

$maxWidth = 120;
if( $map['mp_enable_thumbnail'] && $map['mp_thumbnail_size'] > 64 ){
	$maxWidth = 160;
}
?>
<style type="text/css">
	.first_name{color: #<?php echo $map['mp_title_color'] ?>;}
	.first_name a:link, .first_name a:hover, .first_name a:visited, .first_name a:active{color: #<?php echo $map['mp_title_color'] ?>; text-decoration: none;}
	ul.tree li > div { background:#<?php echo $map['mp_bgcolor'] ?>; max-width: <?php echo $maxWidth ?>px !important;}
	ul.tree li div.current { background:#<?php echo $map['mp_current_bgcolor'] ?>; }
	ul.tree li div.children { background:#<?php echo $map['mp_child_bgcolor'] ?>; }
	ul.tree li div.parent { background:#<?php echo $map['mp_parent_bgcolor'] ?>; }
</style>
<div class="wm-tree-container" style="padding: 0px; margin: 0px; background-color: #<?php echo $map['mp_container_bgc'] ?>;" id="treeContainer_<?php echo $uniqid ?>"><?php echo wm_horizontal_tree( $mp_id, false ) ?></div>

<script type="text/javascript">
	if(!(typeof wm_options == "object" && typeof wm_options.admin_url == "string")){
		var wm_options = {
			admin_url: "<?php echo admin_url() ?>",
			images_url: "<?php echo plugins_url( 'lib/horizontal-tree/images/', WM_PLUGIN_MAIN_FILE ) ?>",
			plug_images_url: "<?php echo plugins_url( 'images/', WM_PLUGIN_MAIN_FILE ) ?>"
		};
	}
	if(typeof wm_options.mp_enable_thumbnail == "undefined"){
		wm_options.mp_enable_thumbnail = <?php echo ( $map['mp_enable_thumbnail'] )? '1' : '0'; ?>;
		wm_options.mp_thumbnail_size = <?php echo ( $map['mp_thumbnail_size'] > 0 )? $map['mp_thumbnail_size'] : '0'; ?>;
		wm_options.mp_thumbnail_shape = "<?php echo $map['mp_thumbnail_shape'] ?>";
		wm_options.mp_thumbnail_place = "<?php echo $map['mp_thumbnail_place'] ?>";
	}
</script>