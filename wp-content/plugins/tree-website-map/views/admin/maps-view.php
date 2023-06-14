<?php $admin_url = get_admin_url(); ?>
<?php $page = ( isset( $_GET['pg'] ) )? (int) $_GET['pg'] : 1; ?>
<?php $page = ( !empty( $page ) )? $page : 1; ?>
<?php $title = ( isset( $_GET['title'] ) && !empty( $_GET['title'] ) )? $_GET['title'] : ''; ?>
<?php $type = ( isset( $_GET['type'] ))? $_GET['type'] : NULL; ?>
<?php $limit = 7; ?>
<?php $offset = ( $page - 1 ) * $limit; ?>
<?php $website_maps = wm_get_all_maps( $title, $type, $limit, $offset); ?>
<!-- start process message -->
<div id="processMessageContainer" class="processMessageContainer">
	<div id="processMessage" class="processMessage successStatus">
		<span></span>
	</div>
</div>
<!-- end process message -->

<div class="container-fluid wm-plugin-logo">
	<div class="row">
		<div class="col-md-8">
			<div class="row">
				
				<div class="col-md-12">
                <div class="wpLogo">
                <img src="<?php echo plugins_url( 'images/wp-tree.png', WM_PLUGIN_MAIN_FILE ) ?>" border="0" style="width:32px; height:32px" />
                </div>
					<h1>&nbsp;WP Tree</h1>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container-fluid wp-tree" >
	<div class="row" style="margin-top: 20px;">
		<div class="col-md-8 col-sm-12">
			<div class="row">
				<div class="col-sm-3">
					<a class="btn btn-primary" href="<?php echo $admin_url ?>admin.php?page=wm_edit_website_map&tp=ver"><?php _e('Add New Vertical Map', 'websitemap'); ?></a>
				</div>
				
				<div class="col-sm-3">
					<a class="btn btn-primary" href="<?php echo $admin_url ?>admin.php?page=wm_edit_website_map&tp=hor"><?php _e('Add New Horizontal Map', 'websitemap'); ?></a>
				</div>
				
			</div>
		</div>
	</div>
    <?php
           if(!empty($website_maps['data']))
		   {
		   ?>
	
    <?php }?>
	<p></p>
	<div class="row">
		<div class="col-sm-12">
           <?php
           if(!empty($website_maps['data']))
		   {
		   ?>
            <table class="table  table-striped table-hover wm-maps-table">
                <thead>
					<tr>
						
						<th><?php _e('Map Title', 'websitemap') ?></th>
						<th><?php _e('Map Type', 'websitemap') ?></th>
						<th><?php _e('Shortcode', 'websitemap') ?></th>
						<th>Edit</th>
						<th>Duplicate</th>
						<th>Delete</th>
					</tr>
                </thead>
                <tbody>
				<?php foreach( $website_maps['data'] as $map ): ?>
					<tr style="height:25px" id="tr-mp_id-<?php echo $map['mp_id'] ?>">
						
						<td style="width:40%"><img src="<?php echo plugins_url( 'images/' . strtolower( $map['mp_type'] ) . '_16.png', WM_PLUGIN_MAIN_FILE ) ?>" border="0" width="16" height="16" />&nbsp; <a href="<?php echo $admin_url ?>admin.php?page=wm_edit_website_map&mpid=<?php echo $map['mp_id'] ?>"><?php echo $map['mp_name'] ?></a></td>
						<td>&nbsp;<?php echo $map['mp_type'] ?></td>
						<td>&nbsp;[wm_website_map id="<?php echo $map['mp_id'] ?>"]</td>
						<td style="width:5%">&nbsp;
							<a href="<?php echo $admin_url ?>admin.php?page=wm_edit_website_map&mpid=<?php echo $map['mp_id'] ?>">
								Edit
							</a>
						</td>
						<td style="width:5%">&nbsp;
							<a href="javascript: void(0);" onclick="wm_DuplicateMap('<?php echo $map['mp_id'] ?>');">
								Duplicate
							</a>
						</td>
						
						<td style="width:5%">&nbsp;
							<a href="javascript: void(0);" onclick="wm_deleteMap('<?php echo $map['mp_id'] ?>');">
								Delete
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
                </tbody>
            </table>
			<?php }else{
			echo '<br /><br /><p><em>Get started now and create your first tree !</em></p>';	
			}
			
			if( $website_maps['total'] > $limit ): ?>
				<?php echo wm_pagination( $admin_url.'admin.php?page=wm_website_maps&pg=', $website_maps['total'], $limit, $page); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
	var wm_options = {
		admin_url: '<?php echo admin_url(); ?>',
		images_url: '<?php echo plugins_url( 'lib/horizontal-tree/images/', WM_PLUGIN_MAIN_FILE ); ?>'
	};
</script>