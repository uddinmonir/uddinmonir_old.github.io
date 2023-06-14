<?php
$mp_id = ( isset( $_GET['mpid'] ) )? $_GET['mpid'] : NULL;
$type = ( isset( $_GET['tp'] ) )? $_GET['tp'] : NULL;

$mp_name = NULL;
if( $mp_id > 0 ){
	$mp_type = wm_read_column_value( "{$table_prefix}wm_maps", 'mp_type', 'mp_id', $mp_id );
	$type = $mp_type;
	
	$mp_name = wm_read_column_value( "{$table_prefix}wm_maps", 'mp_name', 'mp_id', $mp_id );
}

if( 'hor' == substr( $type, 0, 3 ) ){
	include( "horizontal_map.php" );
}
else if( 'ver' == substr( $type, 0, 3 ) ){
	if( 'POST' == $_SERVER['REQUEST_METHOD'] ){
		wm_save_vertical_tree();
	}
	include( "vertical_map.php" );
}
?>