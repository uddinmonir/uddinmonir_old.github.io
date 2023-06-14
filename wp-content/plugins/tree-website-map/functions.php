<?php
/**
 * Called when plugin is activated or upgraded
 *
 * @uses add_option()
 * @uses get_option()
 * @uses update_option()
 *
 * @return void
 */
 

function wm_set_settings()
{
	global $wpdb;
    $options = array(
        'version' => '1.0',
        'default_h_map_options' => array(
            'mp_enable_thumbnail' => 1,
            'mp_thumbnail_size' => 64,
            'mp_thumbnail_shape' => 'circle',
            'mp_thumbnail_place' => 'above',
            'mp_title_color' => 'FFFFFF',
            'mp_container_bgc' => 'FFFFFF',
            'mp_bgcolor' => '27a9e3',
            'mp_current_bgcolor' => '28b779',
            'mp_parent_bgcolor' => '852b99',
            'mp_child_bgcolor' => 'ffb848',
        ),
        'default_v_map_options' => array(
            'mp_enable_thumbnail' => 0,
            'mp_enable_link' => 1,
            'mp_tab_link' => 0,
            'mp_enable_totals' => 0,
            'mp_list_posts' => 20,
        ),
    );

    if (get_option('wm_website_map_settings') === false) {
        add_option('wm_website_map_settings', $options);
        set_time_limit(120);
        wm_create_database_tables();
    } else {

        update_option('wm_website_map_settings', $options);
    }
	
		
			
}

//---------------------------------------------------
/**
 * to hook including scripts & styles to admin_enqueue_scripts, wp_enqueue_scripts at a very late order
 *
 * @uses add_action()
 *
 * @return void
 */
function wm_manage_enqueue()
{
    global $wp_filter;

    $adn_priority = 1;
    if (isset($wp_filter['admin_enqueue_scripts'])) {
        foreach ($wp_filter['admin_enqueue_scripts'] as $key => $val) {
            $adn_priority = ($key >= $adn_priority) ? $key + 1 : $adn_priority;
        }
    }

    add_action('admin_enqueue_scripts', 'wm_admin_styles_scripts', $adn_priority);

    $cln_priority = 1;
    if (isset($wp_filter['wp_enqueue_scripts'])) {
        foreach ($wp_filter['wp_enqueue_scripts'] as $key => $val) {
            $cln_priority = ($key >= $cln_priority) ? $key + 1 : $cln_priority;
        }
    }
    add_action('wp_enqueue_scripts', 'wm_styles_scripts', $cln_priority);
}

//---------------------------------------------------
function wm_add_shortcode_to_editor()
{
    if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
        add_filter('mce_external_plugins', 'wm_mce_plugin');
        add_filter('mce_buttons', 'wm_mce_button');
    }
}


function wm_duplicate_tree()
{
    global $wpdb, $table_prefix;
	
	
	if($wpdb->get_var("SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_NAME = '{$wpdb->prefix}wm_trees' AND COLUMN_NAME = 'old_id' ") == '0') {

                $sql="
                ALTER TABLE `{$wpdb->prefix}wm_trees`
                ADD COLUMN old_id int DEFAULT NULL";
                $wpdb->query($sql);


            }
			
			
			
	$mp_id = (isset($_POST['mp_id'])) ? (int)$_POST['mp_id'] : 0;

	
	$duplicate_done = false;

	if($mp_id >0)
	{
	$getMapsNextMaxId = getMapsNextMaxId();
	
	
	$sql = "insert into `{$table_prefix}wm_maps` (SELECT ".$getMapsNextMaxId.", concat(`mp_name`,' - Copy'), `mp_type`, `mp_options` FROM `{$table_prefix}wm_maps`  WHERE mp_id = ".$mp_id.")";
	 
	 if ($wpdb->query($sql) == true)
		{
			$lastid = $wpdb->insert_id;

            $sql2 = "insert into `{$table_prefix}wm_trees` (SELECT 0, ".$lastid.",  `tr_parent_id`, `tr_title`, `tr_link`, `tr_active_link`, `tr_hide`, `tr_thumbnail_url`, `tr_type`, `tr_order`, `tr_id` FROM `{$table_prefix}wm_trees` WHERE mp_id = ".$mp_id.")";
		   
		    $wpdb->query($sql2);

		    wm_update_map_parents($lastid);
		    $duplicate_done = true;
        }
	
	}

	if($duplicate_done)
	{
		echo '1';
	}else
	{
		echo '0';
	}
	exit();

}
function wm_update_map_parents($mp_id)
{
    global $wpdb, $table_prefix;
	
     $get_results = $wpdb->get_results("select `tr_id`, `old_id`, `tr_parent_id` from `{$table_prefix}wm_trees` where `mp_id` = ".$mp_id);
	 
        if ($get_results)
		{
            foreach ($get_results as $vals)
			{
				if($vals->tr_parent_id > 0)
				{
				$get_results2 = $wpdb->get_results("select `tr_id` from `{$table_prefix}wm_trees` where `old_id` = ".$vals->tr_parent_id." and `mp_id`= ".$mp_id);	
				
				$sql2 = "update `{$table_prefix}wm_trees`  set  `tr_parent_id` = ".$get_results2[0]->tr_id." where `tr_parent_id` is not null and `tr_id` = ".$vals->tr_id;
				
				
				$wpdb->query($sql2);
				}
			
			
			}
		}		

}

//---------------------------------------------------
/**
 * dequeue unwanted scripts and enqueue scripts & styles only when required
 *
 * @uses get_bloginfo()
 * @uses wp_dequeue_script()
 * @uses wp_deregister_script()
 * @uses plugins_url()
 * @uses wp_enqueue_style()
 * @uses wp_enqueue_script()
 *
 * @return void
 */
function wm_admin_styles_scripts()
{
    global $current_screen, $wp_scripts, $wp_styles, $wm_wordpress_scripts, $wm_wordpress_scripts_35;
    $version = get_bloginfo('version');
    $version = substr($version, 0, 3);
    $version = (float)$version;
    $wp_reg_scripts = ($version >= 3.5) ? $wm_wordpress_scripts_35 : $wm_wordpress_scripts;

    if (preg_match('/^.*wm_website_maps$/', $current_screen->id) || preg_match('/^.*wm_edit_website_map$/', $current_screen->id)) {
        $registeredScripts = array();
        foreach ($wp_scripts->registered as $reg) {
            $registeredScripts[] = $reg->handle;
        }

        $queuedScripts = array();
        foreach ($wp_scripts->queue as $q) {
            $queuedScripts[] = $q;
        }

        foreach ($queuedScripts as $q) {
            if (!in_array($q, $wp_reg_scripts)) {
                //wp_dequeue_script($q);
            }
        }

        foreach ($registeredScripts as $r) {
            if (!in_array($r, $wp_reg_scripts)) {
                //wp_deregister_script($r);
            }
        }

        if (!in_array('jquery', $queuedScripts)) {
            wp_enqueue_script('jquery');
        }

        if ($version >= 3.5) {
            if (!in_array('jquery-migrate', $queuedScripts)) {
                wp_enqueue_script('jquery-migrate');
            }
        } else {
            wp_enqueue_script('jquery-migrate', plugins_url('lib/horizontal-tree/js/jquery-migrate-1.2.1.min.js', WM_PLUGIN_MAIN_FILE));
        }

        $jq_ui_comp = array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-draggable', 'jquery-ui-droppable',
            'jquery-ui-resizable', 'jquery-ui-selectable', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-ui-autocomplete',
            'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-menu', 'jquery-ui-position',
            'jquery-ui-progressbar', 'jquery-ui-slider', 'jquery-ui-spinner', 'jquery-ui-tabs', 'jquery-ui-tooltip');

        foreach ($queuedScripts as $q) {
            if (in_array($q, $jq_ui_comp)) {
                //wp_dequeue_script($q);
            }
        }
        wp_enqueue_script('jquery-ui-js', plugins_url('lib/horizontal-tree/js/jquery-ui.js', WM_PLUGIN_MAIN_FILE));

        if (!in_array('media-upload', $queuedScripts)) {
            wp_enqueue_script('media-upload');
        }
        if (!in_array('thickbox', $queuedScripts)) {
            wp_enqueue_script('thickbox');
        }

        wp_enqueue_media();

        wp_enqueue_style('bootstrap-css', plugins_url('lib/bootstrap/css/bootstrap.min.css', WM_PLUGIN_MAIN_FILE));
        wp_enqueue_style('bootstrap-theme-css', plugins_url('lib/bootstrap/css/bootstrap-theme.min.css', WM_PLUGIN_MAIN_FILE));
        wp_enqueue_style('wm-admin-css', plugins_url('css/admin_style-css.css', WM_PLUGIN_MAIN_FILE), $deps=array(), '1.1');
        wp_enqueue_style('wm-css', plugins_url('css/style_css.css', WM_PLUGIN_MAIN_FILE), $deps = array(), '1.1');

        wp_enqueue_script('bootstrap-js', plugins_url('lib/bootstrap/js/bootstrap.min.js', WM_PLUGIN_MAIN_FILE));
        wp_enqueue_script('bootstrap-checkbox', plugins_url('lib/bootstrap-checkbox/bootstrap-checkbox.min.js', WM_PLUGIN_MAIN_FILE));
        wp_enqueue_script('wm-admin-js', plugins_url('js/adminjavascript.js', WM_PLUGIN_MAIN_FILE), false, '1.1');
        wp_enqueue_script('wm-js', plugins_url('js/js.js', WM_PLUGIN_MAIN_FILE));
		
			
		
    }
}

//---------------------------------------------------
/**
 * dequeue unwanted scripts and enqueue scripts & styles only when required
 *
 * @uses get_bloginfo()
 * @uses plugins_url()
 * @uses wp_dequeue_script()
 * @uses wp_enqueue_style()
 * @uses wp_enqueue_script()
 *
 * @return void
 */
function wm_styles_scripts()
{
    global $wp_scripts, $wp_styles, $wm_wordpress_scripts, $wm_wordpress_scripts_35;
    $version = get_bloginfo('version');
    $version = substr($version, 0, 3);
    $version = (float)$version;

    $queuedScripts = array();
    foreach ($wp_scripts->queue as $q) {
        $queuedScripts[] = $q;
    }
    // horizontal
    wp_enqueue_style('fonts-google-css', 'http://fonts.googleapis.com/css?family=Cabin:400,700,600');
    wp_enqueue_style('hor-tree-css', plugins_url('lib/horizontal-tree/style.css', WM_PLUGIN_MAIN_FILE));
    // vertical
    wp_enqueue_style('wm-awesome-css', plugins_url('lib/font-awesome/css/font-awesome.min.css', WM_PLUGIN_MAIN_FILE));
    wp_enqueue_style('wm-jstree-proton-theme-css', plugins_url('lib/jstree-bootstrap-theme/src/themes/proton/style.css', WM_PLUGIN_MAIN_FILE));
    wp_enqueue_style('jstree-css', plugins_url('lib/jstree/dist/themes/default/style.css', WM_PLUGIN_MAIN_FILE));

    if (!in_array('jquery', $queuedScripts)) {
        wp_enqueue_script('jquery');
    }

    if ($version >= 3.5) {
        if (!in_array('jquery-migrate', $queuedScripts)) {
            wp_enqueue_script('jquery-migrate');
        }
    } else {
        wp_enqueue_script('jquery-migrate', plugins_url('lib/horizontal-tree/js/jquery-migrate-1.2.1.min.js', WM_PLUGIN_MAIN_FILE));
    }

    $jq_ui_comp = array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-draggable', 'jquery-ui-droppable',
        'jquery-ui-resizable', 'jquery-ui-selectable', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-ui-autocomplete',
        'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-menu', 'jquery-ui-position',
        'jquery-ui-progressbar', 'jquery-ui-slider', 'jquery-ui-spinner', 'jquery-ui-tabs', 'jquery-ui-tooltip');

    foreach ($queuedScripts as $q) {
        if (in_array($q, $jq_ui_comp)) {
            wp_dequeue_script($q);
        }
    }
    wp_enqueue_script('jquery-ui-js', plugins_url('lib/horizontal-tree/js/jquery-ui.js', WM_PLUGIN_MAIN_FILE));

    wp_enqueue_style('wm-css', plugins_url('css/style_css.css', WM_PLUGIN_MAIN_FILE), $deps = array(), '1.1');

    wp_enqueue_script('wm-js', plugins_url('js/js.js', WM_PLUGIN_MAIN_FILE));
}

//---------------------------------------------------
/**
 * creates admin menu pages
 *
 * @uses add_menu_page()
 * @uses add_submenu_page()
 *
 * @return void
 */
function wm_add_menu_page()
{
    add_menu_page('WP Tree', 'WP Tree', 'manage_options', 'wm_website_maps', 'wm_create_maps_page', 'dashicons-networking', 70);
    add_submenu_page(NULL, 'Add New Map', NULL, 'manage_options', 'wm_edit_website_map', 'wm_create_edit_map_page');
}

//---------------------------------------------------
/**
 * creates maps view page
 *
 * @uses current_user_can()
 *
 * @return void
 */
function wm_create_maps_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permissions to access this page!', 'websitemap'));
    }

    include(WM_PLUGIN_ROOT_DIR . WM_DS . 'views' . WM_DS . 'admin' . WM_DS . 'maps-view.php');
}


function wmpro_rate_us($plugin_url, $box_color = '#1D1F21')
{

    $ret = '
	
	<script language="javascript">
	setTimeout(function() {
		$(\'#ratingdiv\').hide();
	}, 1000);
	</script>
    
	<style type="text/css">
	
	.rate_box{
		
		background-color:' . $box_color . ';
		color:#ffffff;
		
		
		
	}
	.rating {
	  unicode-bidi: bidi-override;
	  direction: rtl;
	  
	  
	}
	.link_wp{
		
		color:#EDAE42 !important
	}
	.rating > span {
	  display: inline-block;
	  position: relative;
	  width: 1.1em;
	  font-size:40px;
	 color:yellow;
	  content: "\2605";
	}
	.rating > span:hover:before,
	.rating > span:hover ~ span:before {
	   content: "\2605";
	   position: absolute;
	   color:yellow;
	}
	</style>';

    $ret .= '<div class="row rate_box" id="ratingdiv">
<div class="col-md-6">
<br />
<p>
<strong>Do you like this plugin?</strong><br /> Please take a few seconds to <a class="link_wp" href="' . $plugin_url . '" target="_blank">rate it on WordPress.org!</a></p>
</div>
<div class="col-md-6">
<div class="rating">';

    for ($r = 1; $r <= 5; $r++) {

        $ret .= '<span onclick="window.open(\'' . $plugin_url . '\',\'_blank\')">☆</span>';
    }

    $ret .= '</div>
</div>
</div>';
    return $ret;
}


//---------------------------------------------------
/**
 * creates map edit page
 *
 * @uses current_user_can()
 *
 * @return void
 */
function wm_create_edit_map_page()
{
    global $table_prefix;
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permissions to access this page!', 'websitemap'));
    }

    include(WM_PLUGIN_ROOT_DIR . WM_DS . 'views' . WM_DS . 'admin' . WM_DS . 'map-edit.php');
}

//---------------------------------------------------
/**
 * reads hierarchical posts (like pages) from database
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return array
 */
function wm_read_hierarchical_posts($post_type, $post_parent = '', $limit = NULL)
{
    global $wpdb;
    $sql = "SELECT ID, post_title, post_parent 
			FROM " . $wpdb->posts . " 
			WHERE post_type = %s AND post_status = 'publish' ";
    $sql .= (NULL !== $post_parent) ? " AND post_parent = %d " : "";
    $sql .= " ORDER BY menu_order";
    $sql .= (!empty($limit)) ? " LIMIT {$limit} OFFSET 0" : "";
    return $wpdb->get_results($wpdb->prepare($sql, $post_type, $post_parent));
}

//---------------------------------------------------
/**
 * counts hierarchical posts (like pages) from database
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return integer
 */
function wm_count_hierarchical_posts($post_type, $post_parent = '')
{
    global $wpdb;
    $sql = "SELECT COUNT(ID) cnt  
			FROM " . $wpdb->posts . " 
			WHERE post_type = %s AND post_status = 'publish' ";
    $sql .= (NULL !== $post_parent) ? " AND post_parent = %d " : "";
    $results = $wpdb->get_results($wpdb->prepare($sql, $post_type, $post_parent));
    if (false !== $results) {
        return $results[0]->cnt;
    }
}

//---------------------------------------------------
/**
 * arranges pages hierarchical
 *
 * @return array
 */
function wm_arrange_hierarchical_posts($pages, $parent = 0)
{
    $res = array();
    $c = 0;
    if (is_array($pages)) {
        for ($i = 0; $i < count($pages); $i++) {
            if ($parent == $pages[$i]->post_parent) {
                $res[$c]['id'] = $pages[$i]->ID;
                $res[$c]['title'] = $pages[$i]->post_title;
                $res[$c]['parent'] = $pages[$i]->post_parent;
                $res[$c]['children'] = wm_arrange_hierarchical_posts($pages, $pages[$i]->ID);
                $c++;
            }
        }
    }
    return $res;
}

//---------------------------------------------------
/**
 * counts a post children
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return boolean
 */
function wm_count_post_children($post_type, $ID)
{
    global $wpdb;
    $sql = "SELECT COUNT(ID) cnt FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = %d";
    $results = $wpdb->get_results($wpdb->prepare($sql, $post_type, $ID));
    if (false !== $results) {
        return ($results[0]->cnt);
    }
}

//---------------------------------------------------
/**
 * counts a taxonomy term children
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return boolean
 */
function wm_count_taxonomy_term_children($term_id)
{
    global $wpdb;
    $sql = "SELECT COUNT(term_id) cnt FROM {$wpdb->term_taxonomy} WHERE parent = %d";
    $results = $wpdb->get_results($wpdb->prepare($sql, $term_id));
    if (false !== $results) {
        return ($results[0]->cnt);
    }
    return false;
}

//---------------------------------------------------
/**
 * counts a taxonomy children
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return boolean
 */
function wm_count_taxonomy_children($taxonomy)
{
    global $wpdb;
    $sql = "SELECT COUNT(term_id) cnt FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s";
    $results = $wpdb->get_results($wpdb->prepare($sql, $taxonomy));
    if (false !== $results) {
        return ($results[0]->cnt);
    }
    return false;
}

//---------------------------------------------------
/**
 * counts excludeds children
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return boolean
 */
function wm_count_excludeds_children($mp_id, $ex_parent_node_id)
{
    global $wpdb;
    $sql = "SELECT COUNT(ex_id) cnt 
			FROM {$wpdb->prefix}wm_excludeds 
			WHERE mp_id = %d AND ex_parent_node_id = %s";
    $results = $wpdb->get_results($wpdb->prepare($sql, $mp_id, $ex_parent_node_id), OBJECT);
    if (false !== $results) {
        return ($results[0]->cnt);
    }
    return false;
}

//---------------------------------------------------
/**
 * reads non hierarchical posts (like posts) from database
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return array
 */
function wm_read_non_hierarchical_posts($post_type, $term_id, $limit = NULL)
{
    global $wpdb;
    /*$sql = "SELECT p.ID, p.post_title, p.post_parent
			FROM {$wpdb->posts} p, {$wpdb->term_relationships} tr
			WHERE tr.term_taxonomy_id = %d AND tr.object_id = p.ID AND post_type = %s AND post_status = 'publish'
			ORDER BY menu_order";*/

    $sql = "SELECT p.ID, p.post_title, p.post_parent 
			FROM {$wpdb->posts} p, {$wpdb->term_relationships} tr, {$wpdb->term_taxonomy} trm
			WHERE tr.term_taxonomy_id = trm.term_taxonomy_id and trm.term_id = %d AND tr.object_id = p.ID AND post_type = %s AND post_status = 'publish' 
			ORDER BY menu_order";


    $sql .= (!empty($limit)) ? " LIMIT {$limit} OFFSET 0" : "";


    return $wpdb->get_results($wpdb->prepare($sql, $term_id, $post_type));
}

//---------------------------------------------------
/**
 * counts non hierarchical posts (like posts) from database
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return integer
 */
function wm_count_non_hierarchical_posts($post_type, $term_id)
{


    global $wpdb;
    /*$sql = "SELECT COUNT(p.ID) cnt
			FROM {$wpdb->posts} p, {$wpdb->term_relationships} tr
			WHERE tr.term_taxonomy_id = %d AND tr.object_id = p.ID AND post_type = %s AND post_status = 'publish'";
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $term_id, $post_type ) );*/


    $sql = "SELECT COUNT(p.ID) cnt 
			FROM {$wpdb->posts} p, {$wpdb->term_relationships} tr, {$wpdb->term_taxonomy} trm
			WHERE tr.term_taxonomy_id = trm.term_taxonomy_id and trm.term_id = %d AND tr.object_id = p.ID AND post_type = %s AND post_status = 'publish'";


    $results = $wpdb->get_results($wpdb->prepare($sql, $term_id, $post_type));


    if (false !== $results) {
        return ($results[0]->cnt);
    }
}

//---------------------------------------------------
/**
 * retrieves taxonomies terms in hierarchical way
 *
 * @return array
 */
function wm_get_all_taxonomies_terms($post_type)
{
    $response = array();
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    foreach ($taxonomies as $key => $tax) {
        if ($tax->public && $tax->hierarchical) {
            $response[$key] = array(
                'name' => $tax->labels->name,
                'terms' => wm_arrange_taxonomy_terms(wm_read_taxonomy_terms($key)),
            );
        }
    }
    return $response;
}

//---------------------------------------------------
/**
 * reads taxonomy terms from database
 *
 * @uses wpdb::get_results()
 *
 * @return array
 */
function wm_read_taxonomy_terms($taxonomy, $parent = NULL)
{
    global $wpdb, $table_prefix;
    $sql = "SELECT t.term_id, t.name, t.slug, tt.description, tt.parent, tt.`count` 
			FROM " . $table_prefix . "terms t, " . $table_prefix . "term_taxonomy tt 
			WHERE t.term_id = tt.term_id AND tt.taxonomy = '" . $taxonomy . "'";

    $sql .= (NULL !== $parent) ? " AND tt.parent = " . $parent : "";

    return $wpdb->get_results($sql);
}

//---------------------------------------------------
/**
 * read taxonomies
 *
 * @uses wpdb::get_results()
 *
 * @return array
 */
function wm_read_other_taxonomies()
{
    global $wpdb, $table_prefix;
    $sql = "SELECT DISTINCT taxonomy 
			FROM " . $table_prefix . "term_taxonomy 
			WHERE taxonomy <> 'category' AND taxonomy <> 'post_tag' AND taxonomy <> 'nav_menu'";

    $results = $wpdb->get_results($sql);
    $response = array();
    if (false !== $results) {
        for ($i = 0; $i < count($results); $i++) {
            $response[] = $results[$i]->taxonomy;
        }
    }
    return $response;
}

//---------------------------------------------------
/**
 * arranges taxonomy terms in hierarchical way
 *
 * @return array
 */
function wm_arrange_taxonomy_terms($terms, $parent = 0)
{
    $response = array();
    $c = 0;
    if (is_array($terms)) {
        for ($i = 0; $i < count($terms); $i++) {
            if ($parent == $terms[$i]->parent) {
                $response[$c]['id'] = $terms[$i]->term_id;
                $response[$c]['name'] = $terms[$i]->name;
                $response[$c]['slug'] = $terms[$i]->slug;
                $response[$c]['description'] = $terms[$i]->description;
                $response[$c]['parent'] = $terms[$i]->parent;
                $response[$c]['count'] = $terms[$i]->count;
                $response[$c]['children'] = wm_arrange_taxonomy_terms($terms, $terms[$i]->term_id);
                $c++;
            }
        }
    }
    return $response;
}

//---------------------------------------------------
/**
 * reads custom post types
 *
 * @uses get_post_types()
 *
 * @return array
 */
function wm_read_custom_post_types($hierarchical, $query_var = NULL)
{
    $args = array(
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'show_ui' => true,
        'hierarchical' => $hierarchical,
        '_builtin' => false,
    );
    if (!empty($query_var)) {
        $args['query_var'] = $query_var;
    }

    return get_post_types($args, 'objects');
}

//---------------------------------------------------
/**
 * creates needed tables
 *
 * @uses wpdb::query()
 *
 * @return boolean
 */
function wm_create_database_tables()
{
	
    global $wpdb;
    $sqlQueries = array();
    $sqlQueries[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wm_maps` (
						`mp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						`mp_name` varchar(150) DEFAULT NULL,
						`mp_type` varchar(45) NOT NULL COMMENT 'vertical, horizontal',
						`mp_options` TEXT NOT NULL,
						PRIMARY KEY (`mp_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    $sqlQueries[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wm_trees` (
						`tr_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						`mp_id` int(11) unsigned NOT NULL,
						`tr_parent_id` int(11) unsigned DEFAULT NULL,
						`tr_title` varchar(200) DEFAULT NULL,
						`tr_link` varchar(300) DEFAULT NULL,
						`tr_active_link` tinyint(1) unsigned NOT NULL DEFAULT '1',
						`tr_hide` tinyint(1) unsigned NOT NULL DEFAULT '0',
						`tr_thumbnail_url` varchar(200) DEFAULT NULL,
						`tr_type` varchar(45) DEFAULT NULL,
						`tr_order` INT(10) UNSIGNED NOT NULL, 
						PRIMARY KEY (`tr_id`) USING BTREE, 
						KEY `FK_{$wpdb->prefix}wm_trees_1` (`mp_id`), 
						KEY `FK_{$wpdb->prefix}wm_trees_2` (`tr_parent_id`), 
						CONSTRAINT `FK_{$wpdb->prefix}wm_trees_2` FOREIGN KEY (`tr_parent_id`) REFERENCES `{$wpdb->prefix}wm_trees` (`tr_id`) ON DELETE CASCADE, 
						CONSTRAINT `FK_{$wpdb->prefix}wm_trees_1` FOREIGN KEY (`mp_id`) REFERENCES `{$wpdb->prefix}wm_maps` (`mp_id`) ON DELETE CASCADE 
					) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    foreach ($sqlQueries as $sql) {
        if (false === $wpdb->query($sql)) {
            return false;
        }
    }
    return true;
}

//---------------------------------------------------
/**
 * retrieves all maps
 *
 * @uses wpdb::get_results()
 *
 * @return array
 */
function wm_get_all_maps($title = '', $type = '', $limit = 20, $offset = 0)
{
    global $wpdb, $table_prefix;
    $response = array();
    $where = "";
    $where .= (!empty($title)) ? " AND mp_name LIKE '%" . $title . "%'" : "";
    $where .= (!empty($type)) ? " AND mp_type = '" . $type . "'" : "";

    $cSql = "SELECT COUNT(mp_id) tot 
			FROM `{$table_prefix}wm_maps` 
			WHERE 1 = 1" . $where;
    $count = $wpdb->get_results($cSql, OBJECT);

    $response['total'] = isset($count[0]->tot) ? $count[0]->tot : 0;

    $sql = "SELECT mp_id, mp_name, mp_type 
			FROM `{$table_prefix}wm_maps` 
			WHERE 1 = 1";
    $sql .= $where;
    $sql .= " ORDER BY mp_id DESC ";
    $sql .= (!empty($limit)) ? " LIMIT " . $limit . " OFFSET " . $offset : "";

    $results = $wpdb->get_results($sql, OBJECT);
    $response['data'] = array();
    if (false !== $results) {
        for ($i = 0; $i < count($results); $i++) {
            $response['data'][$i]['mp_id'] = $results[$i]->mp_id;
            $response['data'][$i]['mp_name'] = $results[$i]->mp_name;
            $response['data'][$i]['mp_type'] = ucfirst($results[$i]->mp_type);
        }
    }
    return $response;
}

//---------------------------------------------------
/**
 * retrieves a map
 *
 * @uses wpdb::prepare()
 * @uses wpdb::get_row()
 * @uses wp_parse_args()
 *
 * @return array
 */
function wm_read_map($mp_id)
{
    global $wpdb;

    $default_options = get_option('wm_website_map_settings');

    $sql = "SELECT mp_id, mp_name, mp_type, mp_options 
			FROM `{$wpdb->prefix}wm_maps` 
			WHERE mp_id = %d";

    $map = $wpdb->get_row($wpdb->prepare($sql, $mp_id), OBJECT);
    $response = array();
    if (is_object($map)) {
        $response['mp_id'] = $map->mp_id;
        $response['mp_name'] = $map->mp_name;
        $response['mp_type'] = $map->mp_type;
        $defaults = ('horizontal' == $map->mp_type) ? $default_options['default_h_map_options'] : $default_options['default_v_map_options'];
        $mp_options = unserialize($map->mp_options);
        $mp_options = wp_parse_args($mp_options, $defaults);
		
        $response = array_merge($response, $mp_options);
        return $response;
    }
}

//---------------------------------------------------
/**
 * pagination
 *
 * @return string
 */
function wm_pagination($link, $total, $per_page, $current_page, $total_links = 5)
{
    $total_pages = (int)ceil($total / $per_page);
    $out = '<nav>
		<ul class="pagination">
			<li ' . (($current_page == 1) ? ' class="disabled"' : '') . '>
				<a href="' . $link . ($current_page - 1) . '" aria-label="Previous">
					<span aria-hidden="true">&laquo;</span>
				</a>
			</li>';

    for ($i = 1; $i <= $total_pages; $i++) {
        $out .= '<li ' . (($current_page == $i) ? ' class="active"' : '') . '>
			<a href="' . $link . $i . '">' . $i . (($current_page == $i) ? ' <span class="sr-only">(current)</span>' : '') . '</a>
		</li>';
    }

    $out .= '<li' . (($current_page == $total_pages) ? ' class="disabled"' : '') . '>
			  <a href="' . $link . ($current_page + 1) . '" aria-label="Next">
				<span aria-hidden="true">&raquo;</span>
			  </a>
			</li>
		</ul>
	</nav>';

    return $out;
}

//---------------------------------------------------
/**
 * renders horizontal tree
 *
 * @return string
 */
function wm_horizontal_tree($mp_id = 0, $forAdmin = true)
{
    $mp_id = (int)$mp_id;

    $map = wm_read_map($mp_id);
    $tree = wm_get_map_tree($mp_id);
    $id = 'tree_' . uniqid();
    $forAdmin = ($forAdmin) ? 'true' : 'false';
    $out = '<ul class="tree" id ="' . $id . '">' . wm_render_horizontal_tree_nodes($tree, $map) . '</ul>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#' . $id . '").tree_structure({
					"add_option": ' . $forAdmin . ',
					"edit_option": ' . $forAdmin . ',
					"delete_option": ' . $forAdmin . ',
					"confirm_before_delete": true,
					"animate_option": false,
					"fullwidth_option": false,
					"align_option": "center",
					"draggable_option": ' . $forAdmin . '
				});
			});
		</script>';
    return $out;
}

//---------------------------------------------------
/**
 * renders horizontal tree node
 *
 * @return string
 */
function wm_render_horizontal_tree_nodes($tree, $map)
{
    $out = '';
    foreach ($tree as $node) {
        $out .= '<li' . (($node['tr_hide']) ? ' class="thide"' : '') . '>';
        $out .= '<div id="' . $node['tr_id'] . '" data-parent-id="' . $node['tr_parent_id'] . '">';

        $title = ($node['tr_active_link'] && !empty($node['tr_link'])) ? '<a href="' . $node['tr_link'] . '" target="_parent">' . $node['tr_title'] . '</a>' : $node['tr_title'];

        $content = array();
        if ($map['mp_enable_thumbnail']) {
            $tr_thumbnail_url = (!empty($node['tr_thumbnail_url'])) ? $node['tr_thumbnail_url'] : plugins_url('images/no_image_128.png', WM_PLUGIN_MAIN_FILE);
            $content[] = '<img src="' . $tr_thumbnail_url . '" width="' . $map['mp_thumbnail_size'] . '" height="' . $map['mp_thumbnail_size'] . '" 
						class="wm-thumbnail-img ' . $map['mp_thumbnail_shape'] . '" />';
            $content[] = '<div class="wm-cleaner"></div>';
        }
        $content[] = '<span class="first_name">' . $title . '</span>';
        if ('under' == $map['mp_thumbnail_place']) {
            $content = array_reverse($content);
        }
        $out .= implode('', $content);

        $out .= '</div>';
        $out .= (!empty($node['children'])) ?
            '<ul>' . wm_render_horizontal_tree_nodes($node['children'], $map) . '</ul>' : '';
        $out .= '</li>';
    }
    return $out;
}

//---------------------------------------------------
/**
 * creates new horizontal map
 *
 * @uses home_url()
 * @uses wpdb::insert()
 *
 * @return boolean
 */
function wm_create_new_horizontal_map()
{
    global $wpdb, $table_prefix;
    /* start saving map */
    $default_options = get_option('wm_website_map_settings');
    $mp_options = serialize($default_options['default_h_map_options']);
    $name = 'horizontal map ' . getMapsNextMaxId();
    $mp_data = array('mp_name' => $name, 'mp_type' => 'horizontal', 'mp_options' => $mp_options);
    if ($wpdb->insert($table_prefix . 'wm_maps', $mp_data, array('%s', '%s', '%s')) !== false) {
        $mp_id = $wpdb->insert_id;
        /* end saving map */
        /* start saving tree root */
        $link = home_url();
        $title = parse_url($link, PHP_URL_HOST);
        $rootValues = array(
            'mp_id' => $mp_id,
            'tr_title' => $title,
            'tr_link' => $link,
        );
        $insertRoot = $wpdb->insert($table_prefix . 'wm_trees', $rootValues, array('%d', '%s', '%s'));
        if ($insertRoot !== false) {
            $root_id = $wpdb->insert_id;
            /* end saving tree root */
            /* start saving pages */
            $allPages = wm_read_hierarchical_posts('page');
            $pages = wm_arrange_hierarchical_posts($allPages);

            $vals = array(
                'mp_id' => $mp_id,
                'tr_parent_id' => $root_id,
                'tr_active_link' => '0',
                'tr_title' => 'Pages',
                'tr_link' => '',
            );
            $frms = array('%d', '%d', '%d', '%s', '%s');
            if ($wpdb->insert($table_prefix . 'wm_trees', $vals, $frms) !== false) {
                $pgsId = $wpdb->insert_id;
                wm_save_hierarchical_posts('page', $pages, $mp_id, $pgsId);
            }
            /* end saving pages */
            /* start saving other hierarchical posts */
            $hierarchical_cus_pst = wm_read_custom_post_types(true);
            foreach ($hierarchical_cus_pst as $key => $cust_pst) {
                $vals = array(
                    'mp_id' => $mp_id,
                    'tr_parent_id' => $root_id,
                    'tr_active_link' => '0',
                    'tr_title' => $cust_pst->labels->name,
                    'tr_title' => '',
                );
                $frms = array('%d', '%d', '%d', '%s', '%s');

                $allPosts = wm_read_hierarchical_posts($key);
                $posts = wm_arrange_hierarchical_posts($allPosts);
                if ($wpdb->insert($table_prefix . 'wm_trees', $vals, $frms) !== false) {
                    $pId = $wpdb->insert_id;
                    wm_save_hierarchical_posts($key, $posts, $mp_id, $pId);
                }
            }
            /* end saving other hierarchical posts */
            /* start saving posts taxonomies' terms */
            $vals = array(
                'mp_id' => $mp_id,
                'tr_parent_id' => $root_id,
                'tr_active_link' => '1',
                'tr_title' => 'Posts',
                'tr_link' => home_url('?post_type=post'),
            );
            $frms = array('%d', '%d', '%d', '%s', '%s');

            if ($wpdb->insert($table_prefix . 'wm_trees', $vals, $frms) !== false) {
                $pId = $wpdb->insert_id;

                $taxs = wm_get_all_taxonomies_terms('post');
                foreach ($taxs as $key => $tax) {
                    $vals = array('mp_id' => $mp_id, 'tr_parent_id' => $pId, 'tr_active_link' => '0', 'tr_title' => $tax['name']);
                    $frms = array('%d', '%d', '%d', '%s');
                    if ($wpdb->insert($table_prefix . 'wm_trees', $vals, $frms) !== false) {
                        $taxId = $wpdb->insert_id;
                        wm_save_terms($tax['terms'], $key, $mp_id, $taxId);
                    }
                }
            }
            /* end saving posts taxonomies' terms */
            /* start saving custom posts type taxonomies' terms */
            $non_hierarchical_cus_pst = wm_read_custom_post_types(false);
            foreach ($non_hierarchical_cus_pst as $key => $cust_pst) {
                $vals = array(
                    'mp_id' => $mp_id,
                    'tr_parent_id' => $root_id,
                    'tr_active_link' => '1',
                    'tr_title' => $cust_pst->labels->name,
                    'tr_link' => home_url('?post_type=' . $key),
                );
                $frms = array('%d', '%d', '%d', '%s', '%s');

                if ($wpdb->insert($table_prefix . 'wm_trees', $vals, $frms) !== false) {
                    $pId = $wpdb->insert_id;

                    $taxs = wm_get_all_taxonomies_terms($key);
                    foreach ($taxs as $key => $tax) {
                        $vals = array('mp_id' => $mp_id, 'tr_parent_id' => $pId, 'tr_active_link' => '0', 'tr_title' => $tax['name']);
                        $frms = array('%d', '%d', '%d', '%s');
                        if ($wpdb->insert($table_prefix . 'wm_trees', $vals, $frms) !== false) {
                            $taxId = $wpdb->insert_id;
                            wm_save_terms($tax['terms'], $key, $mp_id, $taxId);
                        }
                    }
                }
            }
            /* end saving custom posts type taxonomies' terms */
        }
        return $mp_id;
    }
    return false;
}

//---------------------------------------------------
/**
 * saves horizontal tree pages
 *
 * @uses wpdb::insert()
 * @uses wpdb::show_errors()
 *
 * @return void
 */
function wm_save_hierarchical_posts($post_type, $pages, $mp_id, $tr_parent_id, $gen_thumb = false)
{
    global $wpdb;

    $order = 0;
    foreach ($pages as $p) {
        $tr_thumbnail_url = NULL;
        if ($gen_thumb && post_type_supports($post_type, 'thumbnail')) {
            if (has_post_thumbnail($p['id'])) {
                $thumb_id = get_post_thumbnail_id($p['id']);
                $tr_thumbnail_url = wm_create_ver_node_thumbnail($thumb_id);
            }
        }

        $values = array(
            'mp_id' => $mp_id,
            'tr_parent_id' => $tr_parent_id,
            'tr_title' => $p['title'],
            'tr_link' => get_permalink($p['id']),
            'tr_thumbnail_url' => $tr_thumbnail_url,
            'tr_type' => 'hierarchical_post',
            'tr_order' => $order,
        );
        $formats = array('%d', '%d', '%s', '%s', '%s', '%s', '%d');
        if ($wpdb->insert($wpdb->prefix . 'wm_trees', $values, $formats) !== false) {
            $insertedId = $wpdb->insert_id;
            if (!empty($p['children'])) {
                wm_save_hierarchical_posts($post_type, $p['children'], $mp_id, $insertedId);
            }
        } else {
            $wpdb->show_errors();
        }
        $order++;
    }
}

//---------------------------------------------------
/**
 * saves horizontal tree terms
 *
 * @uses wpdb::insert()
 * @uses wpdb::show_errors()
 *
 * @return void
 */
function wm_save_terms($terms, $taxonomy, $mp_id, $tr_parent_id)
{
    global $wpdb;
    foreach ($terms as $t) {
        $term = get_term($t['id'], $taxonomy, OBJECT);
        $link = get_term_link($term->name, $taxonomy);
        $link = (is_wp_error($link)) ? '' : $link;
        $values = array(
            'mp_id' => $mp_id,
            'tr_parent_id' => $tr_parent_id,
            'tr_active_link' => '1',
            'tr_title' => $t['name'],
            'tr_link' => ($link instanceof WP_Error) ? '' : $link,
        );
        $formats = array('%d', '%d', '%d', '%s', '%s');
        if ($wpdb->insert($wpdb->prefix . 'wm_trees', $values, $formats) !== false) {
            $insertedId = $wpdb->insert_id;

            if (!empty($t['children'])) {
                wm_save_terms($t['children'], $taxonomy, $mp_id, $insertedId);
            }
        } else {
            $wpdb->show_errors();
        }
    }
}

//---------------------------------------------------
/**
 * saves tree terms
 *
 * @uses wpdb::insert()
 * @uses wpdb::show_errors()
 * @uses is_wp_error()
 *
 * @return void
 */
function wm_save_terms_and_posts($post_type, $terms, $taxonomy, $mp_id, $tr_parent_id, $term_order = 0)
{
    global $wpdb;
    foreach ($terms as $t) {
        $term = get_term($t['id'], $taxonomy, OBJECT);
        $link = get_term_link($term->name, $taxonomy);
        $link = (is_wp_error($link)) ? '' : $link;
        $values = array(
            'mp_id' => $mp_id,
            'tr_parent_id' => $tr_parent_id,
            'tr_active_link' => '1',
            'tr_title' => $t['name'],
            'tr_link' => ($link instanceof WP_Error) ? '' : $link,
            'tr_type' => 'term',
            'tr_order' => $term_order++,
        );
        $formats = array('%d', '%d', '%d', '%s', '%s', '%s', '%d');
        if ($wpdb->insert($wpdb->prefix . 'wm_trees', $values, $formats) !== false) {
            $insertedId = $wpdb->insert_id;
            $next_order = wm_save_non_hierarchical_term_posts($mp_id, $post_type, $term, $insertedId);
            if (!empty($t['children'])) {
                wm_save_terms_and_posts($post_type, $t['children'], $taxonomy, $mp_id, $insertedId, $next_order);
            }
        } else {
            $wpdb->show_errors();
        }
    }
}

//---------------------------------------------------
function wm_save_non_hierarchical_term_posts($mp_id, $post_type, $term, $parent_id)
{
    global $wpdb;
    $posts = wm_read_non_hierarchical_posts($post_type, $term->term_id);
    $post_order = 0;
    foreach ($posts as $p) {
        $tr_thumbnail_url = NULL;
        if (post_type_supports($post_type, 'thumbnail')) {
            if (has_post_thumbnail($p->ID)) {
                $thumb_id = get_post_thumbnail_id($p->ID);
                $tr_thumbnail_url = wm_create_ver_node_thumbnail($thumb_id);
            }
        }

        $values = array(
            'mp_id' => $mp_id,
            'tr_parent_id' => $parent_id,
            'tr_title' => $p->post_title,
            'tr_link' => get_permalink($p->ID),
            'tr_thumbnail_url' => $tr_thumbnail_url,
            'tr_type' => 'post',
            'tr_order' => $post_order++,
        );
        $formats = array('%d', '%d', '%s', '%s', '%s', '%s', '%d');
        $wpdb->insert($wpdb->prefix . 'wm_trees', $values, $formats);
    }
    return $post_order;
}

//---------------------------------------------------
function wm_create_ver_node_thumbnail($thumb_id)
{
    $thumb_url = wp_get_attachment_url($thumb_id);
    $file_ext = wm_get_file_extension(basename($thumb_url));
    $file_name = basename($thumb_url, '.' . $file_ext);
    $url = dirname($thumb_url);
    $editor = wp_get_image_editor(get_attached_file($thumb_id));
    if ($editor instanceof WP_Image_Editor) {
        $editor->resize(22, 22, true);
        $editor->set_quality(100);
        $result = $editor->save($editor->generate_filename());
        $attach_url = $url . '/' . $result['file'];
        return $attach_url;
    }
    return NULL;
}

//---------------------------------------------------
/**
 * retrieves horizontal map tree
 *
 * @return array
 */
function wm_get_map_tree($mp_id)
{
    $mp_id = (int)$mp_id;
    $tree = wm_read_map_tree($mp_id);
    return wm_arrange_tree($tree);
}

//---------------------------------------------------
/**
 * reads horizontal tree from database
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return array
 */
function wm_read_map_tree($mp_id)
{
    global $wpdb;
    $mp_id = (int)$mp_id;
    $sql = "SELECT tr_id, tr_parent_id, tr_title, tr_link, tr_active_link, tr_hide, tr_thumbnail_url 
			FROM {$wpdb->prefix}wm_trees 
			WHERE mp_id = %d";
    return $wpdb->get_results($wpdb->prepare($sql, $mp_id));
}

//---------------------------------------------------
/**
 * reads tree nodes by parent id from database
 *
 * @uses wpdb::get_results()
 * @uses wpdb::prepare()
 *
 * @return array
 */
function wm_read_map_tree_by_parent($mp_id, $tr_parent_id = NULL, $posts_list = NULL, $mp_order_by= NULL, $mp_order_type = NULL)
{
    global $wpdb;
    $mp_id = (int)$mp_id;
    $sql = "SELECT tr_id, tr_parent_id, tr_title, tr_link, tr_active_link, tr_hide, tr_thumbnail_url, tr_type, (select count(trp.tr_id) from {$wpdb->prefix}wm_trees trp where trp.tr_parent_id = tr.tr_id and tr_title <> '') as c 
			FROM {$wpdb->prefix}wm_trees tr
			WHERE tr_title <> '' and mp_id = %d";
    $sql .= (is_null($tr_parent_id)) ? " AND tr_parent_id IS NULL" : " AND tr_parent_id = %d";
	
	
	if($mp_order_by != 'DragDrop')
	{
		if($mp_order_by == 'Title' && !empty($mp_order_type))
		{
			
			$sql .= " ORDER BY tr_title ".$mp_order_type;
		}else if($mp_order_by == 'Date' && !empty($mp_order_type))
		{
			
			$sql .= " ORDER BY tr_id ".$mp_order_type;
		}
	}else{
		$sql .= " ORDER BY tr_order ASC";
	}
	

    $sql .= (is_null($posts_list)) ? "" : " LIMIT %d OFFSET 0";
	//echo $wpdb->prepare($sql, $mp_id, $tr_parent_id, $posts_list);
    return $wpdb->get_results($wpdb->prepare($sql, $mp_id, $tr_parent_id, $posts_list));
}

//---------------------------------------------------
/**
 * counts nodes by parent id
 *
 * @uses wpdb::get_var()
 * @uses wpdb::prepare()
 *
 * @return array
 */
function wm_count_tree_node_children($mp_id, $tr_parent_id)
{
    global $wpdb;
    $sql = "SELECT COUNT(tr_id) c 
			FROM {$wpdb->prefix}wm_trees 
			WHERE mp_id = %d";
    $sql .= (is_null($tr_parent_id)) ? " AND tr_parent_id IS NULL" : " AND tr_parent_id = %d";
    return $wpdb->get_var($wpdb->prepare($sql, $mp_id, $tr_parent_id));
}

//---------------------------------------------------
/**
 * arranges horizontal tree
 *
 * @return array
 */
function wm_arrange_tree($tree, $parent = NULL)
{
    $res = array();
    if (is_array($tree)) {
        for ($i = 0; $i < count($tree); $i++) {
            if ($parent == $tree[$i]->tr_parent_id) {
                $res[$i]['tr_id'] = $tree[$i]->tr_id;
                $res[$i]['tr_title'] = $tree[$i]->tr_title;
                $res[$i]['tr_parent_id'] = $tree[$i]->tr_parent_id;
                $res[$i]['tr_link'] = $tree[$i]->tr_link;
                $res[$i]['tr_active_link'] = $tree[$i]->tr_active_link;
                $res[$i]['tr_hide'] = $tree[$i]->tr_hide;
                $res[$i]['tr_thumbnail_url'] = $tree[$i]->tr_thumbnail_url;
                $res[$i]['children'] = wm_arrange_tree($tree, $tree[$i]->tr_id);
            }
        }
    }
    return $res;
}

//---------------------------------------------------
/**
 * reads column from certain table, using where condition
 *
 * @uses wpdb::get_var()
 * @uses wpdb::prepare()
 *
 * @return array
 */
function wm_read_column_value($table, $reqColumn, $colName, $colValue)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare("SELECT `" . $reqColumn . "` FROM `" . $table . "` WHERE `" . $colName . "` = %s", $colValue));
}

//---------------------------------------------------
/**
 * deletes a horizontal tree node
 *
 * @uses wpdb::query()
 * @uses wpdb::prepare()
 *
 * @return void
 */
function wm_delete_tree_node()
{
    global $wpdb;
    $ids = (isset($_POST['id'])) ? implode(', ', $_POST['id']) : '';
    $ids = (empty($ids)) ? '0' : $ids;
    echo ($wpdb->query($wpdb->prepare("DELETE FROM `" . $wpdb->prefix . "wm_trees` WHERE tr_id IN (%s) AND tr_parent_id IS NOT NULL", $ids)) !== false) ? '1' : '0';
    exit();
}

//---------------------------------------------------
/**
 * Retrieves attachment id from url
 *
 * @uses wp_upload_dir()
 * @uses wpdb::get_var()
 * @uses wpdb::prepare()
 *
 * @return integer|boolean
 */
function wm_get_attachment_id_from_url($attachment_url)
{
    global $wpdb;
    $upload_dir_paths = wp_upload_dir();
    if (strpos($attachment_url, $upload_dir_paths['baseurl']) !== false) {
        $attachment_url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url);

        $attachment_url = str_replace($upload_dir_paths['baseurl'] . '/', '', $attachment_url);

        $sql = "SELECT ID 
				FROM {$wpdb->posts} wposts, {$wpdb->postmeta} wpostmeta 
				WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' 
				AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'";

        $attachment_id = $wpdb->get_var($wpdb->prepare($sql, $attachment_url));

        return $attachment_id;
    }
    return false;
}

//---------------------------------------------------
/**
 * render a horizontal tree node form
 *
 * @uses plugins_url()
 * @uses checked()
 *
 * @return void
 */
function wm_get_node_form()
{
    $action = (isset($_POST['action'])) ? $_POST['action'] : '';
    $edit_ele_id = (isset($_POST['edit_ele_id'])) ? $_POST['edit_ele_id'] : 0;
    $tr_parent_id = (isset($_POST['tr_parent_id'])) ? $_POST['tr_parent_id'] : NULL;

    $tr_title = $tr_link = $tr_hide = $tr_hide_checked = NULL;
    $tr_active_link = '1';
    $formClass = 'add_data';
    $buttonClass = 'submit';

    if ('wm_node_editform' == $action && $edit_ele_id > 0) {
        if ($node = wm_read_tree_node($edit_ele_id)) {
            $tr_parent_id = $node->tr_parent_id;
            $tr_title = $node->tr_title;
            $tr_link = $node->tr_link;
            $tr_active_link = $node->tr_active_link;
            $tr_hide = $node->tr_hide;
            $formClass = 'edit_data';
            $buttonClass = 'edit';
        }
    }

    ?>
    <form class="<?php echo $formClass ?>" method="post" action="">
        <img class="close"
             src="<?php echo plugins_url('lib/horizontal-tree/images/close.png', WM_PLUGIN_MAIN_FILE) ?>"/>
        <br/>
        <input type="hidden" name="thumbnail_id" value="">
        <input type="hidden" name="tr_parent_id" value="<?php echo $tr_parent_id ?>">
        <input type="text" class="first_name" name="first_name" value="<?php echo $tr_title ?>"
               placeholder="first name">
        <input type="text" name="tr_link" value="<?php echo $tr_link ?>" placeholder="url">
        <input type="checkbox" <?php checked($tr_active_link, '1'); ?> value="1" name="tr_active_link"
               id="tr_active_link"/>
        <label for="tr_active_link">Active Link</label>
        <br/>
        <input type="checkbox" <?php checked($tr_hide, '1'); ?> value="1" name="showhideval" id="hide"/>
        <label for="hide">Hide Child Nodes</label>
        <input id="wm_thumb_selector" type="button" style="margin-top: 5px;" class="button-secondary"
               onclick="wm_addImage(this)" value="<?php _e('Add image', 'websitemap') ?>">
        <input type="submit" class="btn btn-success <?php echo $buttonClass ?>" style="margin-top: 10px;" name="submit"
               value="<?php _e('Submit', 'websitemap') ?>">
    </form>
    <?php
    exit();
}

//---------------------------------------------------
/**
 * read horizontal tree node
 *
 * @uses wpdb::prepare()
 * @uses wpdb::get_row()
 *
 * @return object|boolean
 */
function wm_read_tree_node($tr_id)
{
    global $wpdb;
    $node = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}wm_trees` WHERE `tr_id` = %d", $tr_id));
    return (!is_wp_error($node) && !empty($node)) ? $node : false;
}

//---------------------------------------------------
/**
 * saves horizontal tree node
 *
 * @uses wpdb::update()
 * @uses wpdb::insert()
 * @uses wpdb::prepare()
 * @uses wpdb::get_var()
 * @uses wp_get_attachment_url()
 * @uses get_attached_file()
 * @uses wp_get_image_editor()
 * @uses WP_Image_Editor::resize()
 * @uses WP_Image_Editor::set_quality()
 * @uses WP_Image_Editor::save()
 * @uses WP_Image_Editor::generate_filename()
 *
 * @return void
 */
function wm_save_tree_node()
{
    global $wpdb;
    $response = array();
    $data = array();
    $tr_id = (isset($_POST['id'])) ? $_POST['id'] : 0;
    $data['tr_title'] = (isset($_POST['first_name'])) ? $_POST['first_name'] : '';
    $data['tr_link'] = (isset($_POST['tr_link'])) ? $_POST['tr_link'] : '';
    $tr_parent_id = (isset($_POST['tr_parent_id'])) ? $_POST['tr_parent_id'] : NULL;
    $data['tr_active_link'] = (isset($_POST['tr_active_link'])) ? 1 : 0;
    $data['tr_hide'] = (isset($_POST['showhideval'])) ? 1 : 0;
    $dataFormats = array('tr_title' => '%s', 'tr_link' => '%s', 'tr_active_link' => '%d', 'tr_hide' => '%d');

    if ($tr_id > 0) {
        $where = array('tr_id' => $tr_id);
        $response['success'] = (false !== $wpdb->update($wpdb->prefix . 'wm_trees', $data, $where, $dataFormats, array('%d')));
    } else {
        $mp_id = wm_read_column_value($wpdb->prefix . 'wm_trees', 'mp_id', 'tr_id', $tr_parent_id);
        $data = array_merge($data, array('mp_id' => $mp_id, 'tr_parent_id' => $tr_parent_id));
        $dataFormats = array_merge($dataFormats, array('tr_parent_id' => '%d', 'mp_id' => '%d'));
        $response['success'] = (false !== $wpdb->insert($wpdb->prefix . 'wm_trees', $data, $dataFormats));
        $tr_id = $wpdb->insert_id;
    }

    $response['tr_title'] = ($data['tr_active_link'] && !empty($data['tr_link'])) ?
        '<a href="' . $data['tr_link'] . '" target="_parent">' . $data['tr_title'] . '</a>' : $data['tr_title'];

    /* start handling thumbnail image */
    $thumbnail_id = (isset($_POST['thumbnail_id'])) ? $_POST['thumbnail_id'] : '';

    if (!empty($thumbnail_id)) {
        $attch_id = wm_get_attachment_id_from_url(urldecode($thumbnail_id));
        if ($attch_id) {
            $url = dirname(wp_get_attachment_url($attch_id));

            $editor = wp_get_image_editor(get_attached_file($attch_id));
            if ($editor instanceof WP_Image_Editor) {
                $editor->resize(128, 128, true);
                $editor->set_quality(100);
                $result = $editor->save($editor->generate_filename());
                $attach_url = $url . '/' . $result['file'];
                $response['success'] = (false !== $wpdb->update($wpdb->prefix . 'wm_trees',
                        array('tr_thumbnail_url' => $attach_url),
                        array('tr_id' => $tr_id),
                        array('tr_thumbnail_url' => '%s'),
                        array('%d')
                    )
                );
                $response['tr_thumbnail_url'] = $attach_url;
            }
        }
    } else {
        $tr_thumbnail_url = $wpdb->get_var($wpdb->prepare("SELECT tr_thumbnail_url FROM `{$wpdb->prefix}wm_trees` WHERE tr_id = %d", $tr_id));
        $response['tr_thumbnail_url'] = (!empty($tr_thumbnail_url)) ? $tr_thumbnail_url : plugins_url('images/no_image_128.png', WM_PLUGIN_MAIN_FILE);
    }
    /* end handling thumbnail image */

    header("Content-Type: application/json");
    header("Content-Type: text/json");
    echo json_encode($response);
    exit();
}

//---------------------------------------------------
/**
 * change horizontal tree node parent
 *
 * @uses wpdb::update()
 *
 * @return void
 */
function wm_change_parent_node()
{
    global $wpdb;
    $tr_id = (isset($_POST['tr_id'])) ? $_POST['tr_id'] : 0;
    $tr_parent_id = (isset($_POST['tr_parent_id'])) ? $_POST['tr_parent_id'] : 0;

    $res = $wpdb->update(
        $wpdb->prefix . 'wm_trees',
        array('tr_parent_id' => $tr_parent_id),
        array('tr_id' => $tr_id),
        array('%d'),
        array('%d')
    );
    echo (false !== $res) ? '1' : '0';
    exit();
}

//---------------------------------------------------
/**
 * updates horizontal map
 *
 * @uses get_option()
 * @uses wp_parse_args()
 * @uses wpdb::update()
 *
 * @return void
 */
function wm_edit_horizontal_map()
{
    global $wpdb;
    $mp_id = (isset($_POST['mp_id'])) ? $_POST['mp_id'] : 0;
    $mp_name = (isset($_POST['mp_name'])) ? $_POST['mp_name'] : '';
    $mp_options = array();
    $mp_options['mp_enable_thumbnail'] = (isset($_POST['mp_enable_thumbnail'])) ? $_POST['mp_enable_thumbnail'] : 0;
    $mp_options['mp_thumbnail_size'] = (isset($_POST['mp_thumbnail_size'])) ? $_POST['mp_thumbnail_size'] : 0;
    $mp_options['mp_thumbnail_shape'] = (isset($_POST['mp_thumbnail_shape'])) ? $_POST['mp_thumbnail_shape'] : NULL;
    $mp_options['mp_thumbnail_place'] = (isset($_POST['mp_thumbnail_place'])) ? $_POST['mp_thumbnail_place'] : NULL;
    $mp_options['mp_container_bgc'] = (isset($_POST['mp_container_bgc'])) ? $_POST['mp_container_bgc'] : NULL;
    $mp_options['mp_title_color'] = (isset($_POST['mp_title_color'])) ? $_POST['mp_title_color'] : NULL;
    $mp_options['mp_bgcolor'] = (isset($_POST['mp_bgcolor'])) ? $_POST['mp_bgcolor'] : NULL;
    $mp_options['mp_current_bgcolor'] = (isset($_POST['mp_current_bgcolor'])) ? $_POST['mp_current_bgcolor'] : NULL;
    $mp_options['mp_parent_bgcolor'] = (isset($_POST['mp_parent_bgcolor'])) ? $_POST['mp_parent_bgcolor'] : NULL;
    $mp_options['mp_child_bgcolor'] = (isset($_POST['mp_child_bgcolor'])) ? $_POST['mp_child_bgcolor'] : NULL;
    $mp_options['mp_container_width'] = (isset($_POST['mp_container_width'])) ? $_POST['mp_container_width'] : NULL;
    $mp_options['mp_container_height'] = (isset($_POST['mp_container_height'])) ? $_POST['mp_container_height'] : NULL;

    $default_options = get_option('wm_website_map_settings');
    $mp_options = wp_parse_args($mp_options, $default_options['default_h_map_options']);
    $mp_options = serialize($mp_options);

    $res = $wpdb->update(
        $wpdb->prefix . 'wm_maps',
        array('mp_name' => $mp_name, 'mp_options' => $mp_options),
        array('mp_id' => $mp_id),
        array('%s', '%s'),
        array('%d')
    );

    echo (false !== $res) ? '1' : '0';
    exit();
}

//---------------------------------------------------
/**
 * updates vertical map
 *
 * @uses wpdb::update()
 *
 * @return void
 */
function wm_edit_vertical_map()
{
    global $wpdb;
    $mp_id = (isset($_POST['mp_id'])) ? $_POST['mp_id'] : 0;
    $mp_name = (isset($_POST['mp_name'])) ? $_POST['mp_name'] : '';
    $mp_options = array();
    $mp_options['mp_enable_thumbnail'] = (isset($_POST['mp_enable_thumbnail'])) ? $_POST['mp_enable_thumbnail'] : 0;
    $mp_options['mp_tab_link'] = (isset($_POST['mp_tab_link'])) ? $_POST['mp_tab_link'] : 0;
    $mp_options['mp_enable_link'] = (isset($_POST['mp_enable_link'])) ? $_POST['mp_enable_link'] : 0;
    $mp_options['mp_enable_totals'] = (isset($_POST['mp_enable_totals'])) ? $_POST['mp_enable_totals'] : 0;
    $mp_options['mp_list_posts'] = (isset($_POST['mp_list_posts'])) ? $_POST['mp_list_posts'] : 10;
    $mp_options['mp_container_width'] = (isset($_POST['mp_container_width'])) ? $_POST['mp_container_width'] : 'auto';
    $mp_options['mp_container_height'] = (isset($_POST['mp_container_height'])) ? $_POST['mp_container_height'] :
        'auto';
    $mp_options['state_data'] = (isset($_POST['stats'])) ? $_POST['stats'] : '';
    $mp_options['mp_font_size'] = (isset($_POST['mp_font_size'])) ? $_POST['mp_font_size'] : '22';
    $mp_options['mp_order_by'] = (isset($_POST['mp_order_by'])) ? $_POST['mp_order_by'] : 'Date';
    $mp_options['mp_order_type'] = (isset($_POST['mp_order_type'])) ? $_POST['mp_order_type'] : 'DESC';
    $default_options = get_option('wm_website_map_settings');
    $mp_options = wp_parse_args($mp_options, $default_options['default_v_map_options']);
    $mp_options = serialize($mp_options);

    $res = $wpdb->update(
        $wpdb->prefix . 'wm_maps',
        array('mp_name' => $mp_name, 'mp_options' => $mp_options),
        array('mp_id' => $mp_id),
        array('%s', '%s'),
        array('%d')
    );
    echo (false !== $res) ? '1' : '0';
    exit();
}

//---------------------------------------------------
/**
 * creates vertical map
 *
 * @uses get_option()
 * @uses wpdb::insert()
 * @uses home_url()
 *
 * @return integer|boolean
 */
function wm_create_new_vertical_map()
{
    global $wpdb;
    // types: root | post_type | hierarchical_post | taxonomy | term | post | custom
    /* start saving map */
    $default_options = get_option('wm_website_map_settings');
    $mp_options = $default_options['default_v_map_options'];
    $name = 'vertical map ' . getMapsNextMaxId();
    $data = array('mp_name' => $name, 'mp_type' => 'vertical', 'mp_options' => serialize($mp_options));
    $mp_id = NULL;
    if ($wpdb->insert($wpdb->prefix . 'wm_maps', $data, array('%s', '%s', '%s')) !== false) {
        $mp_id = $wpdb->insert_id;
    }
    /* end saving map */

    /* start saving tree root */
    $root_id = NULL;
    if (!empty($mp_id)) {
        $link = home_url();
        $title = parse_url($link, PHP_URL_HOST);
        $rootValues = array(
            'mp_id' => $mp_id,
            'tr_title' => $title,
            'tr_link' => $link,
            'tr_type' => 'root',
            'tr_order' => 0,
        );

        if ($wpdb->insert($wpdb->prefix . 'wm_trees', $rootValues, array('%d', '%s', '%s', '%s', '%d'))) {
            $root_id = $wpdb->insert_id;
        }
    }
    /* end saving tree root */

    /* start saving pages root */
    $post_type_order = 0;
    $pageRoot = NULL;
    if (!empty($root_id)) {
        $data = array(
            'mp_id' => $mp_id,
            'tr_title' => 'Pages',
            'tr_link' => '',
            'tr_active_link' => '0',
            'tr_parent_id' => $root_id,
            'tr_type' => 'post_type',
            'tr_order' => $post_type_order++,
        );
        if ($wpdb->insert($wpdb->prefix . 'wm_trees', $data, array('%d', '%s', '%s', '%d', '%d', '%s', '%d'))) {
            $pagesRoot = $wpdb->insert_id;
        }
    }
    /* end saving pages root */

    /* start saving pages */
    if (!empty($root_id)) {
        $allPages = wm_read_hierarchical_posts('page');
        $pages = wm_arrange_hierarchical_posts($allPages);

        wm_save_hierarchical_posts('page', $pages, $mp_id, $pagesRoot, true);
    }
    /* end saving pages */

    /* start saving other hierarchical posts */
    if (!empty($root_id)) {
        $hierarchical_cus_pst = wm_read_custom_post_types(true);
        foreach ($hierarchical_cus_pst as $key => $cust_pst) {
            $vals = array(
                'mp_id' => $mp_id,
                'tr_parent_id' => $root_id,
                'tr_active_link' => '0',
                'tr_title' => $cust_pst->labels->name,
                'tr_link' => '',
                'tr_type' => 'post_type',
                'tr_order' => $post_type_order++,
            );
            $frms = array('%d', '%d', '%d', '%s', '%s', '%s', '%d');

            $allPosts = wm_read_hierarchical_posts($key);
            $posts = wm_arrange_hierarchical_posts($allPosts);
            if ($wpdb->insert($wpdb->prefix . 'wm_trees', $vals, $frms) !== false) {
                $pId = $wpdb->insert_id;
                wm_save_hierarchical_posts($key, $posts, $mp_id, $pId, true);
            }
        }
    }
    /* end saving other hierarchical posts */

    /* start saving posts taxonomies' terms */
    $taxonomy_order = 0;
    $vals = array(
        'mp_id' => $mp_id,
        'tr_parent_id' => $root_id,
        'tr_active_link' => '1',
        'tr_title' => 'Posts',
        'tr_link' => home_url('?post_type=post'),
        'tr_type' => 'post_type',
        'tr_order' => $post_type_order++,
    );
    $frms = array('%d', '%d', '%d', '%s', '%s', '%s', '%d');

    if ($wpdb->insert($wpdb->prefix . 'wm_trees', $vals, $frms) !== false) {
        $pId = $wpdb->insert_id;

        $taxs = wm_get_all_taxonomies_terms('post');
        foreach ($taxs as $key => $tax) {
            $vals = array(
                'mp_id' => $mp_id,
                'tr_parent_id' => $pId,
                'tr_active_link' => '0',
                'tr_title' => $tax['name'],
                'tr_type' => 'taxonomy',
                'tr_order' => $taxonomy_order++,
            );
            $frms = array('%d', '%d', '%d', '%s', '%s', '%d');
            if ($wpdb->insert($wpdb->prefix . 'wm_trees', $vals, $frms) !== false) {
                $taxId = $wpdb->insert_id;
                wm_save_terms_and_posts('post', $tax['terms'], $key, $mp_id, $taxId);#####
            }
        }
    }
    /* end saving posts taxonomies' terms */

    /* start saving custom posts type taxonomies' terms */
    $non_hierarchical_cus_pst = wm_read_custom_post_types(false);
    foreach ($non_hierarchical_cus_pst as $post_type => $cust_pst) {
        $vals = array(
            'mp_id' => $mp_id,
            'tr_parent_id' => $root_id,
            'tr_active_link' => '1',
            'tr_title' => $cust_pst->labels->name,
            'tr_link' => home_url('?post_type=' . $post_type),
            'tr_type' => 'post_type',
            'tr_order' => $post_type_order++,
        );
        $frms = array('%d', '%d', '%d', '%s', '%s', '%s', '%d');

        if ($wpdb->insert($wpdb->prefix . 'wm_trees', $vals, $frms) !== false) {
            $pId = $wpdb->insert_id;

            $taxs = wm_get_all_taxonomies_terms($post_type);
            foreach ($taxs as $key => $tax) {
                $vals = array(
                    'mp_id' => $mp_id,
                    'tr_parent_id' => $pId,
                    'tr_active_link' => '0',
                    'tr_title' => $tax['name'],
                    'tr_type' => 'taxonomy',
                    'tr_order' => $taxonomy_order++,
                );
                $frms = array('%d', '%d', '%d', '%s', '%s', '%d');
                if ($wpdb->insert($wpdb->prefix . 'wm_trees', $vals, $frms) !== false) {
                    $taxId = $wpdb->insert_id;
                    wm_save_terms_and_posts($post_type, $tax['terms'], $key, $mp_id, $taxId);
                }
            }
        }
    }
    /* end saving custom posts type taxonomies' terms */

    return $mp_id;
}

//---------------------------------------------------
function wm_get_small_size_image($attch_url)
{
    $attch_id = wm_get_attachment_id_from_url($attch_url);
    if ($attch_id) {
        $img = wp_get_attachment_image_src($attch_id, 'small');
        if (empty($img[0])) {
            $img = wp_get_attachment_image_src($attch_id, 'medium');
        }
        return $img[0];
    }
    return NULL;
}

//---------------------------------------------------
function wm_get_selected_image_small_size()
{
    $thumb_url = (isset($_POST['thumb_url'])) ? urldecode($_POST['thumb_url']) : '';
    $thumb_id = wm_get_attachment_id_from_url($thumb_url);
    echo wm_create_ver_node_thumbnail($thumb_id);
    exit;
}

//---------------------------------------------------
/**
 * retrieves vertical tree nodes by parent id
 *
 * @uses is_user_logged_in()
 * @uses current_user_can()
 *
 * @return void
 */
function wm_get_vertical_tree_nodes()
{
    global $wpdb;
    $html = true;
    $mp_id = (isset($_GET['mp_id'])) ? $_GET['mp_id'] : 0;
    $parent = (isset($_GET['parent'])) ? $_GET['parent'] : NULL;
    $check_admin = (isset($_GET['check_admin'])) ? $_GET['check_admin'] : NULL;
    $control_panel = (isset($_GET['control_panel']) && is_user_logged_in() && current_user_can('manage_options'));
    $map = wm_read_map($mp_id);
	
    if (isset($map['mp_id'])) {
        if ('#' === $parent) {
            $map_nodes = wm_read_map_tree_by_parent($mp_id, NULL, NULL, $map['mp_order_by'], $map['mp_order_type']);
        } else {
            $posts_list = NULL;
            if (!empty($map['mp_list_posts'])) {
                $parent_type = wm_read_column_value($wpdb->prefix . 'wm_trees', 'tr_type', 'tr_id', $parent);
                if ('term' === $parent_type) {
                    $posts_list = $map['mp_list_posts'];
                }
            }

            $map_nodes = wm_read_map_tree_by_parent($mp_id, $parent, $posts_list, $map['mp_order_by'], $map['mp_order_type']);
        }
        // ELECT tr_id, tr_parent_id, tr_title, tr_link, tr_active_link, tr_hide, tr_thumbnail_url
        $nodes = array();
        $length = count($map_nodes);
        for ($i = 0; $i < $length; $i++) {
			$tr_title = ($map_nodes[$i]->c >0 && $map['mp_enable_totals'] == 1) ? $map_nodes[$i]->tr_title.' <small><b>('.$map_nodes[$i]->c.')</b></small>' : $map_nodes[$i]->tr_title;
            $nodes[] = array(
                "id" => $map_nodes[$i]->tr_id,
                "text" => $tr_title,
                "icon" => ($map['mp_enable_thumbnail']) ?
                    (empty($map_nodes[$i]->tr_thumbnail_url) ? wm_get_icon($map_nodes[$i]->tr_type) : $map_nodes[$i]->tr_thumbnail_url) : '',
               // "children" => (wm_count_tree_node_children($mp_id, $map_nodes[$i]->tr_id) > 0),
			    "children" => $map_nodes[$i]->c,
                "link" => $map_nodes[$i]->tr_link,
                "hide" => $map_nodes[$i]->tr_hide
            );
			
        }
        $out = '';
        if ($html) {
            $out = '<ul>';
            foreach ($nodes as $n) {
				
				$mp_tab_link = ($map['mp_tab_link'] == 1) ? 'target="_blank"' : 'target="_parent"';
				
				
				
                $text = ($map['mp_enable_link'] && !empty($n['link']) && !$control_panel) ? '<a href="' . $n['link'] . '" '.$mp_tab_link.' onclick="wm_toLink(this);" >
														<span>' . $n['text'] . '</span>
													</a>' :
                    '<span>' . $n['text'] . '</span>';

                $text .= ($control_panel) ? '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #AFA03F;" onclick="wm_editVerNode(' . $n['id'] . ')"><i class="fa fa-pencil-square-o"></i></span>' : '';
                $text .= ($control_panel) ? '&nbsp;&nbsp;<span style="color: #41A34B;" onclick="wm_add_ver_node(' . $n['id'] . ')"><i class="fa fa-plus-square"></i></span>' : '';

                $color = $n['hide'] == 1 ? 'red' : '#000';
                $class = $n['hide'] == 1 ? 'hidden-node' : '';

                $text .= ($control_panel) ? '&nbsp;&nbsp;<span class="' . $class . '" onclick="wm_hide_ver_node(' . $n['id'] . ',this)"><i class="fa fa-eye-slash"></i></span>' : '';
                if ($n['hide'] == 0 && !$check_admin) {
                    $out .= '<li id="' . $n['id'] . '" class="wm-li-node ' . (($n['children']) ? ' jstree-closed' : '') . '"
						data-jstree=\'{"icon":"' . $n['icon'] . '"}\'>&nbsp;' . $text;

                    $out .= '</li>';
                }
                if ($check_admin) {
                    $out .= '<li id="' . $n['id'] . '" class="wm-li-node ' . (($n['children']) ? ' jstree-closed' : '') . '"
						data-jstree=\'{"icon":"' . $n['icon'] . '"}\'>&nbsp;' . $text;

                    $out .= '</li>';
                }
            }
            $out .= '</ul>';
        }

        if (!$html) {
            header('Content-type: text/json');
            header('Content-type: application/json');
        }
        echo ($html) ? $out : json_encode($nodes);
    }
    exit();
}

//---------------------------------------------------
/**
 * retrieves vertical excluded nodes
 *
 * @uses wpdb::prepare()
 * @uses wpdb::get_results()
 *
 * @return array
 */
function wm_read_excluded_nodes($mp_id)
{
    global $wpdb, $table_prefix;

    $sql = "SELECT ex_node_id 
			FROM " . $table_prefix . "wm_excludeds 
			WHERE mp_id = %d";

    $results = $wpdb->get_results($wpdb->prepare($sql, $mp_id));
    $excludeds = array();
    if (is_array($results)) {
        foreach ($results as $node) {
            $excludeds[] = $node->ex_node_id;
        }
    }
    return $excludeds;
}

//---------------------------------------------------
/**
 * renders website map (for shortcode)
 *
 * @uses wp_enqueue_style()
 * @uses wp_enqueue_script()
 * @uses plugins_url()
 * @uses admin_url()
 *
 * @return string
 */
function wm_render_website_map($atts)
{
    $mp_id = (isset($atts['id'])) ? $atts['id'] : 0;
    $map = wm_read_map($mp_id);
    $uniqid = uniqid();
    $out = '';
    if ('horizontal' == $map['mp_type']) {
        /* start ifram */
        $out = '<iframe src="' . home_url('?wmpageid=wm_h_map&mp_id=' . $mp_id) . '" 
				style="border: none; width: ' . $map['mp_container_width'] . 'px; height: ' . $map['mp_container_height'] . 'px; 
				background-color: #' . $map['mp_container_bgc'] . '; padding: 0; margin: 0;" scrolling="no" ></iframe>';
        /* end iframe */

        /* start default */
        /*wp_enqueue_script( 'hor-tree-js', plugins_url( 'lib/horizontal-tree/js/jquery.tree.js', WM_PLUGIN_MAIN_FILE ) );

		$maxWidth = 120;
		if( $map['mp_enable_thumbnail'] && $map['mp_thumbnail_size'] > 64 ){
			$maxWidth = 160;
		}

		$out = '<style type="text/css">
			.first_name{color: #' . $map['mp_title_color'] . ';}
			.first_name a:link{color: #' . $map['mp_title_color'] . '; text-decoration: none;}
			.first_name a:hover{color: #' . $map['mp_title_color'] . '; text-decoration: none;}
			ul.tree li > div { background:#' . $map['mp_bgcolor'] . '; max-width: <?php echo $maxWidth ?>px !important;}
			ul.tree li div.current { background:#' . $map['mp_current_bgcolor'] . '; }
			ul.tree li div.children { background:#' . $map['mp_child_bgcolor'] . '; }
			ul.tree li div.parent { background:#' . $map['mp_parent_bgcolor'] . '; }
		</style>
		<div class="wm-tree-container" style="padding: 0px; margin: 0px;" id="treeContainer_' . $uniqid . '">' . wm_horizontal_tree( $mp_id, false ) . '</div>
		<script type="text/javascript">
			if(!(typeof wm_options == "object" && typeof wm_options.admin_url == "string")){
				var wm_options = {
					admin_url: "' . admin_url() . '",
					images_url: "' . plugins_url( 'lib/horizontal-tree/images/', WM_PLUGIN_MAIN_FILE ) . '",
					plug_images_url: "' . plugins_url( 'images/', WM_PLUGIN_MAIN_FILE ) . '"
				};
			}
			if(typeof wm_options.mp_enable_thumbnail == "undefined"){
				wm_options.mp_enable_thumbnail = ' . ( ( $map['mp_enable_thumbnail'] )? '1' : '0' ) . ';
				wm_options.mp_thumbnail_size = ' . ( ( $map['mp_thumbnail_size'] > 0 )? $map['mp_thumbnail_size'] : '0') . ';
				wm_options.mp_thumbnail_shape = "' . $map['mp_thumbnail_shape'] . '";
				wm_options.mp_thumbnail_place = "' . $map['mp_thumbnail_place'] . '";
			}
		</script>';*/
        /* end default */

    } else if ('vertical' == $map['mp_type']) {
        wp_enqueue_script('jstree-js', plugins_url('lib/jstree/dist/jstree.min.js', WM_PLUGIN_MAIN_FILE));
        $css_over = 'auto';
        $map['mp_container_height'] = isset($map['mp_container_height']) ? $map['mp_container_height'] . 'px' : 0;
        $map['mp_container_width'] = isset($map['mp_container_width']) ? $map['mp_container_width'] . 'px' : 0;
        $map['state_data'] = isset($map['state_data']) ? $map['state_data'] : '';
        $map['mp_font_size'] = isset($map['mp_font_size']) ? $map['mp_font_size'].'px': '22px';
        $map['mp_order_by'] = isset($map['mp_order_by']) ? $map['mp_order_by']: 'Date';
        $map['mp_order_type'] = isset($map['mp_order_type']) ? $map['mp_order_type']: 'DESC';
		
        if ($map['mp_container_width'] == 0 || $map['mp_container_height'] == 0) {
            $css_over = 'hidden';
            $map['mp_container_height'] = 'auto';
            $map['mp_container_width'] = 'auto';
        }
		$state_rec = stripslashes($map['state_data']);
        $out ='<script type="application/javascript">
            var state_record = \''.$state_rec.'\';
          
            if (state_record) {
				
                localStorage.setItem("demo_'.$mp_id.'", state_record);
            }
        </script>';
        
        $out .= '<div class="wm-tree-container" style="font-size:'.$map['mp_font_size'].';max-height:' . $map['mp_container_height'] . '; width:' . $map['mp_container_width'] . ';overflow:' . $css_over . ';" data-height="' . $map['mp_container_height'] . '">
			<div id="tree_' . $uniqid . '" class="wm-vertical-map"></div>
		</div>
		<script type="text/javascript">
		    
			if(!(typeof wm_options == "object" && typeof wm_options.admin_url == "string")){
				var wm_options = {
					admin_url: "' . admin_url() . '",
					images_url: "' . plugins_url('lib/horizontal-tree/images/', WM_PLUGIN_MAIN_FILE) . '",
					plug_images_url: "' . plugins_url('images/', WM_PLUGIN_MAIN_FILE) . '"
				};
			}
			
			jQuery(document).ready(function(){

				jQuery("#tree_' . $uniqid . '").jstree({
					
					"core" : {
						"themes" : {
							"name": "proton",
							"responsive": true,
							"icons": ' . (($map['mp_enable_thumbnail']) ? 'true' : 'false') . ',
						},
						"check_callback" : true,
						"data" : {
							"state" : {"opened" : true },
							"url" : function (node) {
							  return "' . admin_url() . 'admin-ajax.php";
							},
							"data" : function (node) {
							  return {
								  "action": "wm_get_vertical_tree_nodes",
								  "mp_id": ' . $mp_id . ',
								  "parent" : node.id
							  };
							}
						}
					},"dnd" : {
                    "is_draggable" : false
                },
					"types" : {
						"default" : {
							
							"icon" : "fa fa-folder icon-state-warning icon-lg"
						},
						"file" : {
							"icon" : "fa fa-file icon-state-warning icon-lg"
						},
						"f-open" : {
							"icon" : "fa fa-folder-open fa-fw"
						},
						"f-closed" : {
							"icon" : "fa fa-folder fa-fw"
						}
					},
					"state" : { "key" : "demo_' . $mp_id . '" },
					"plugins" : [ "dnd", "state", "types" ]
				});
				
				
			});
			
			


		</script>';
    }
    return $out;
}

//---------------------------------------------------
function wm_mce_plugin($plgs)
{
    if (get_bloginfo('version') < 3.9) {
        $plgs['wmtreeshortcode'] = plugins_url('lib/mceplugin/mceplugin.js', WM_PLUGIN_MAIN_FILE);
    } else {
        $plgs['wmtreeshortcode'] = plugins_url('lib/mceplugin/mceplugin_x4.js', WM_PLUGIN_MAIN_FILE);
    }
    return $plgs;
}

//---------------------------------------------------
function wm_mce_button($btns)
{
    array_push($btns, 'separator', 'wmsrtcdcombo');
    return $btns;
}

//---------------------------------------------------
/**
 * deletes a vertical tree node
 *
 * @uses wpdb::query()
 *
 * @return void
 */
function wm_delete_vertical_tree_node()
{
    global $wpdb;

    $data = array();
    $mp_id = (isset($_POST['mp_id'])) ? (int)$_POST['mp_id'] : 0;
    $nodes = (isset($_POST['nodes'])) ? $_POST['nodes'] : array();

    if (is_array($nodes) && !empty($nodes)) {
        $nodes = implode(',', $nodes);
        $sql = "DELETE FROM `" . $wpdb->prefix . "wm_trees` WHERE tr_id IN (" . $nodes . ") AND tr_parent_id IS NOT NULL";
        echo ($wpdb->query($sql) !== false) ? '1' : '0';
    }
    exit();
}

//---------------------------------------------------
/**
 * restores a vertical tree node
 *
 * @uses wpdb::get_results()
 * @uses wpdb::insert()
 *
 * @return void
 */
function wm_restore_vertical_tree_node()
{
    global $wpdb;

    $where = array();
    $mp_id = (isset($_POST['mp_id'])) ? (int)$_POST['mp_id'] : 0;
    $nodes = (isset($_POST['nodes'])) ? $_POST['nodes'] : array();
    if (is_array($nodes)) {
        foreach ($nodes as $node) {
            $where = array('mp_id' => $mp_id, 'ex_node_id' => $node);
            if (!$wpdb->delete($wpdb->prefix . 'wm_excludeds', $where, array('%d', '%s'))) {
                echo '0';
                break;
            }
        }
        echo '1';
    }
    exit();
}

//---------------------------------------------------
/**
 * deletes a map
 *
 * @uses wpdb::delete()
 *
 * @return void
 */
function wm_delete_map()
{
    global $wpdb;
    $mp_id = (isset($_POST['mp_id'])) ? (int)$_POST['mp_id'] : 0;

    echo ($wpdb->delete($wpdb->prefix . 'wm_maps', array('mp_id' => $mp_id), array('%d')) !== false) ? '1' : '0';
    exit();
}

//---------------------------------------------------
/**
 * returns an object taxonomies
 *
 * @uses get_object_taxonomies()
 *
 * @return array
 */
function wm_get_object_taxonomies($post_type)
{
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $res = array();
    if (!empty($taxonomies)) {
        foreach ($taxonomies as $key => $tax) {
            if ($tax->public && $tax->hierarchical) {
                $res[$key] = $tax;
            }
        }
    }
    return $res;
}

//---------------------------------------------------
/**
 * returns the suitable icon
 *
 * @uses plugins_url()
 * @uses post_type_supports()
 * @uses has_post_thumbnail()
 * @uses get_post_thumbnail_id()
 * @uses wp_get_attachment_image_src()
 * @uses home_url()
 *
 * @return string
 */
function wm_get_icon($tr_type)
{
    switch ($tr_type) {
        case 'root':
            return plugins_url('images/home_icon_22.png', WM_PLUGIN_MAIN_FILE);
            break;

        case 'post_type':
            return plugins_url('images/148953.svg', WM_PLUGIN_MAIN_FILE);
            break;

        case 'hierarchical_post':
            return plugins_url('images/paper_icon_22.png', WM_PLUGIN_MAIN_FILE);
            break;

        case 'post':
        case 'custom':
            return plugins_url('images/post_icon_22.png', WM_PLUGIN_MAIN_FILE);
            break;

        case 'taxonomy':
            return plugins_url('images/archives_icon_22.png', WM_PLUGIN_MAIN_FILE);
            break;

        case 'term':
            return plugins_url('images/148953.svg', WM_PLUGIN_MAIN_FILE);
            break;
    }
}

//---------------------------------------------------
/**
 * streams an image with 22x22 size
 *
 * @uses wp_get_attachment_image_src()
 * @uses get_attached_file()
 * @uses wp_get_image_editor()
 * @uses WP_Image_Editor::resize()
 * @uses WP_Image_Editor::set_quality()
 * @uses WP_Image_Editor::stream()
 *
 * @return void
 */
function wm_get_custom_image_size()
{
    ob_clean();
    $attach_id = (isset($_GET['atchid'])) ? $_GET['atchid'] : NULL;
    $attach = wp_get_attachment_image_src($attach_id, 'wm_vtree_thumbnail');
    $ext = wm_get_file_extension($attach[0]);
    if (!empty($attach_id)) {
        $editor = wp_get_image_editor(get_attached_file($attach_id));
        if ($editor instanceof WP_Image_Editor) {
            $editor->resize(22, 22, true);
            $editor->set_quality(100);
            $editor->stream('image/' . $ext);
        }
    }
    exit();
}

//---------------------------------------------------
/**
 * returns a file extension
 *
 * @return string
 */
function wm_get_file_extension($fileName)
{
    return substr($fileName, strrpos($fileName, '.') + 1);
}

//---------------------------------------------------
function wm_render_shortcodes()
{
    global $pagenow;
    if (('post.php' == $pagenow || 'post-new.php' == $pagenow) && current_user_can('edit_posts') && current_user_can('edit_pages')) {
        $maps = wm_get_all_maps();
        $arr = array();
        if (get_bloginfo('version') >= 3.9) {
            for ($i = 0; $i < count($maps['data']); $i++) {
                $arr[$i]['name'] = $maps['data'][$i]['mp_name'];
                $arr[$i]['shortcode'] = '[wm_website_map id="' . $maps['data'][$i]['mp_id'] . '"]';
            }
        } else {
            for ($i = 0; $i < count($maps['data']); $i++) {
                $arr[] = '[wm_website_map id="' . $maps['data'][$i]['mp_id'] . '"]';
            }
        }
        if (!empty($arr)) {
            ?>
            <script type="text/javascript">
                var wm_maps_shortcode = <?php echo json_encode($arr) ?>;
            </script>
            <?php
        }
    }
    ?>
    <input type="hidden" id="is_admin_side" value="true">
    <?php
}

//---------------------------------------------------
function wm_render_horiontal_map()
{
    $mp_id = (isset($_POST['mp_id'])) ? $_POST['mp_id'] : 0;
    echo wm_horizontal_tree($mp_id, false);
    exit();
}

//---------------------------------------------------
function wm_render_h_map_to_frame()
{
    wm_enqueue_for_frame();
    $mp_id = (isset($_GET['mp_id'])) ? $_GET['mp_id'] : NULL;
    include(WM_PLUGIN_ROOT_DIR . WM_DS . 'views' . WM_DS . 'front' . WM_DS . 'horizontal_map.php');
    exit();
}

//---------------------------------------------------
function wm_enqueue_for_frame()
{
    ?>
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Cabin:400,700,600"/>
    <link rel="stylesheet" type="text/css"
          href="<?php echo plugins_url('lib/horizontal-tree/style.css', WM_PLUGIN_MAIN_FILE) ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('css/style_css.css', WM_PLUGIN_MAIN_FILE) ?>"/>
	

    <script type="text/javascript"
            src="<?php echo plugins_url('lib/horizontal-tree/js/jquery-1.11.1.min.js', WM_PLUGIN_MAIN_FILE) ?>"></script>
    <script type="text/javascript"
            src="<?php echo plugins_url('lib/horizontal-tree/js/jquery-migrate-1.2.1.min.js', WM_PLUGIN_MAIN_FILE) ?>"></script>
    <script type="text/javascript"
            src="<?php echo plugins_url('lib/horizontal-tree/js/jquery-ui.js', WM_PLUGIN_MAIN_FILE) ?>"></script>
    <script type="text/javascript"
            src="<?php echo plugins_url('lib/horizontal-tree/js/jquery-ui.js', WM_PLUGIN_MAIN_FILE) ?>"></script>
    <script type="text/javascript"
            src="<?php echo plugins_url('lib/horizontal-tree/js/jquery.tree.js', WM_PLUGIN_MAIN_FILE) ?>"></script>
    <script type="text/javascript" src="<?php echo plugins_url('js/js.js', WM_PLUGIN_MAIN_FILE) ?>"></script>
    <?php
}

//---------------------------------------------------
/**
 * retrieve maximum id
 *
 * @uses wpdb::get_var()
 *
 * @return integer
 */
function getMapsNextMaxId()
{
    global $wpdb;
    $sql = "SELECT IFNULL(MAX(mp_id), 0) + 1 m FROM {$wpdb->prefix}wm_maps";
    return $wpdb->get_var($sql);
}

//---------------------------------------------------
/**
 * initializes
 *
 * @uses load_theme_textdomain()
 *
 * @return void
 */
function wm_plugin_ini()
{
    wm_add_shortcode_to_editor();
    load_theme_textdomain('websitemap', WM_PLUGIN_ROOT_DIR . WM_DS . 'languages');
}

//---------------------------------------------------
function wm_get_node_props()
{
    $tr_id = (isset($_POST['tr_id'])) ? $_POST['tr_id'] : NULL;
    $node = wm_read_tree_node($tr_id);
    if ($node) {
        $thumb_id = wm_get_attachment_id_from_url($node->tr_thumbnail_url);
        $org_thumbnail_url = wp_get_attachment_url($thumb_id);
        $arr = array('data' => array(
            'tr_id' => $node->tr_id,
            'tr_title' => $node->tr_title,
            'tr_link' => $node->tr_link,
            'tr_thumbnail_url' => $node->tr_thumbnail_url,
            'org_thumbnail_url' => $org_thumbnail_url,
        ));
        echo json_encode($arr);
    }
    exit;
}

//---------------------------------------------------
function wm_get_max_order($tr_parent_id)
{
    global $wpdb;
    $sql = "SELECT IFNULL(MAX(tr_order), -1) + 1 FROM {$wpdb->prefix}wm_trees WHERE tr_parent_id = %d";
    return $wpdb->get_var($wpdb->prepare($sql, $tr_parent_id));
}

//---------------------------------------------------
function wm_save_ver_node()
{
    global $wpdb;
    $mp_id = (isset($_POST['mp_id'])) ? $_POST['mp_id'] : NULL;
    $tr_id = (isset($_POST['tr_id'])) ? $_POST['tr_id'] : NULL;
    $tr_parent_id = (isset($_POST['tr_parent_id'])) ? $_POST['tr_parent_id'] : NULL;
    $tr_title = (isset($_POST['tr_title'])) ? trim($_POST['tr_title']) : NULL;
    $tr_link = (isset($_POST['tr_link'])) ? trim($_POST['tr_link']) : NULL;
    $tr_thumbnail_url = (isset($_POST['tr_thumbnail_url'])) ? trim($_POST['tr_thumbnail_url']) : NULL;

    $response = array('success' => false);
    if (!empty($tr_id)) {
        $data = array(
            'tr_title' => stripslashes($tr_title),
            'tr_link' => $tr_link,
            'tr_thumbnail_url' => $tr_thumbnail_url,
        );
        $dataFormats = array('%s', '%s', '%s');
        if (false !== $wpdb->update($wpdb->prefix . 'wm_trees', $data, array('tr_id' => $tr_id), $dataFormats, array('%d'))) {
            $response['success'] = true;
            $response['data'] = array(
                'new_node' => false,
                'node_id' => $tr_id,
                'node_text' => wm_get_node_text($tr_id),
            );
        }
    } else if (!empty($tr_parent_id)) {
        $data = array(
            'mp_id' => $mp_id,
            'tr_parent_id' => $tr_parent_id,
            'tr_title' => stripslashes($tr_title),
            'tr_link' => $tr_link,
            'tr_thumbnail_url' => $tr_thumbnail_url,
            'tr_type' => 'custom',
            'tr_order' => wm_get_max_order($tr_parent_id),
        );
        $dataFormats = array('%d', '%d', '%s', '%s', '%s', '%s');
        if (false !== $wpdb->insert($wpdb->prefix . 'wm_trees', $data, $dataFormats)) {
            $id = $wpdb->insert_id;
            $response['success'] = true;
            $response['data'] = array(
                'new_node' => true,
                'node_parent' => $tr_parent_id,
                'node_text' => wm_get_node_text($id),
            );
        }
    }

    echo json_encode($response);
    exit;
}

//---------------------------------------------------
function wm_get_node_text($tr_id)
{
    global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}wm_trees WHERE tr_id = %d";
    $node = $wpdb->get_row($wpdb->prepare($sql, $tr_id), OBJECT);
    $mp_id = wm_read_column_value($wpdb->prefix . 'wm_trees', 'mp_id', 'tr_id', $tr_id);
    $map = wm_read_map($mp_id);

    $out = '';
    if (false !== $node && !is_wp_error($node)) {

        $out = array(
            "id" => $node->tr_id,
            "text" => '<span>' . $node->tr_title . '</span>
				&nbsp;&nbsp;-&nbsp;&nbsp;
				<span class="wm-edit-ver-node" onclick="wm_editVerNode(' . $node->tr_id . ')">' . __('edit') . '</span>
				&nbsp;&nbsp;-&nbsp;&nbsp;
				<span class="wm-edit-ver-node" onclick="wm_add_ver_node(' . $node->tr_id . ')">' . __('add child node') . '</span>',
            "icon" => ($map['mp_enable_thumbnail']) ? $node->tr_thumbnail_url : '',
            "link" => '',
        );

        /*
		$text = '<span>' . $node->tr_title . '</span>
				&nbsp;&nbsp;-&nbsp;&nbsp;
				<span class="wm-edit-ver-node" onclick="wm_editVerNode(' . $node->tr_id . ')">' . __( 'edit' ) . '</span>
				&nbsp;&nbsp;-&nbsp;&nbsp;
				<span class="wm-edit-ver-node" onclick="wm_add_ver_node(' . $node->tr_id . ')">' . __( 'add child node' ) . '</span>';

		$out .= '<li id="' . $node->tr_id . '" class="wm-li-node " data-jstree=\'{"icon":"' . $n['icon'] . '"}\'>&nbsp;' . $text;

		$out .= '</li>';
		*/
    }

    return $out;
}

//---------------------------------------------------
function wm_move_ver_node()
{
    global $wpdb;
    $tr_id = (isset($_POST['tr_id'])) ? (int)$_POST['tr_id'] : NULL;
    $tr_parent_id_old = (isset($_POST['tr_parent_id_old'])) ? (int)$_POST['tr_parent_id_old'] : NULL;
    $tr_parent_id = (isset($_POST['tr_parent_id'])) ? (int)$_POST['tr_parent_id'] : NULL;
    $tr_order_old = (isset($_POST['tr_order_old'])) ? (int)$_POST['tr_order_old'] : NULL;
    $tr_order = (isset($_POST['tr_order'])) ? (int)$_POST['tr_order'] : NULL;

    $mp_id = (isset($_POST['mp_id'])) ? $_POST['mp_id'] : 0;
    $records_update = (isset($_POST['record_update'])) ? $_POST['record_update'] : array();
    $records_update = stripslashes($records_update);
    $records = json_decode($records_update);

    $response = array('success' => false);

    if ($mp_id != 0 && !empty($records) && ($tr_parent_id_old == $tr_parent_id)) {
        foreach ($records as $order => $ids) {
            $tree_tbl = $wpdb->prefix . 'wm_trees';
            $updated = $wpdb->update(
                $tree_tbl,
                array('tr_order' => $order),
                array(
                    'mp_id' => $mp_id,
                    'tr_id' => $ids
                )
            );
        }
        $response['success'] = true;
    } else {
        if (!empty($tr_id) && !empty($tr_parent_id_old) && !empty($tr_parent_id)) {
            $wpdb->query("START TRANSACTION");
            $wpdb->query($wpdb->prepare($sql, $tr_id));

            if ($tr_parent_id_old == $tr_parent_id) {
                if ($tr_order > $tr_order_old) {
                    $sql2 = "UPDATE {$wpdb->prefix}wm_trees SET tr_order = tr_order - 1 
						WHERE tr_parent_id = %d AND tr_id <> %d 
						AND tr_order BETWEEN %d AND %d";
                    if (false !== $wpdb->query($wpdb->prepare($sql2, $tr_parent_id, $tr_id, $tr_order_old, $tr_order))) {
                        $sql3 = "UPDATE {$wpdb->prefix}wm_trees SET tr_order = %d WHERE tr_id = %d";
                        if (false !== $wpdb->query($wpdb->prepare($sql3, $tr_order, $tr_id))) {
                            $wpdb->query("COMMIT");
                            $response['success'] = true;
                        }
                    }
                } else if ($tr_order < $tr_order_old) {
                    $sql4 = "UPDATE {$wpdb->prefix}wm_trees SET tr_order = tr_order + 1 
						WHERE tr_parent_id = %d AND tr_id <> %d  
						AND tr_order BETWEEN %d AND %d";
                    if (false !== $wpdb->query($wpdb->prepare($sql4, $tr_parent_id, $tr_id, $tr_order, $tr_order_old))) {
                        $sql5 = "UPDATE {$wpdb->prefix}wm_trees SET tr_order = %d WHERE tr_id = %d";
                        if (false !== $wpdb->query($wpdb->prepare($sql5, $tr_order, $tr_id))) {
                            $wpdb->query("COMMIT");
                            $response['success'] = true;
                        }
                    }
                }
            } else {
                $sql6 = "UPDATE {$wpdb->prefix}wm_trees SET tr_order = tr_order + 1 
					WHERE tr_parent_id = %d AND tr_order >= %d";
                if (false !== $wpdb->query($wpdb->prepare($sql6, $tr_parent_id, $tr_order))) {
                    $sql7 = "UPDATE {$wpdb->prefix}wm_trees SET tr_parent_id = %d, tr_order = %d WHERE tr_id = %d";
                    if (false !== $wpdb->query($wpdb->prepare($sql7, $tr_parent_id, $tr_order, $tr_id))) {
                        $sql8 = "UPDATE {$wpdb->prefix}wm_trees SET tr_order = tr_order - 1 
							WHERE tr_parent_id = %d AND tr_order > %d";
                        if (false !== $wpdb->query($wpdb->prepare($sql8, $tr_parent_id_old, $tr_order_old))) {
                            $wpdb->query("COMMIT");
                            $response['success'] = true;
                        }
                    }
                }
            }
        }
    }


    echo json_encode($response);
    exit;
}

//---------------------------------------------------
function wm_hide_ver_node_callback()
{
    global $wpdb;
    if ($_POST['check_admin'] == 1 && isset($_POST['mp_id']) && isset($_POST['tr_id'])) {
        $mp_id = $_POST['mp_id'];
        $tr_id = $_POST['tr_id'];
        $status = false;
        $color = '#000';
        $tbl_tree = $wpdb->prefix . 'wm_trees';
        $hide_res = $wpdb->get_var("select tr_hide from $tbl_tree where mp_id = $mp_id and tr_id = $tr_id");
        if ($hide_res) {
            $res = $wpdb->update(
                $tbl_tree,
                array('tr_hide' => 0),
                array('mp_id' => $mp_id,
                    'tr_id' => $tr_id
                )
            );

            if ($res) {
                $status = true;
            }
        } else {
            $res = $wpdb->update(
                $tbl_tree,
                array('tr_hide' => 1),
                array('mp_id' => $mp_id,
                    'tr_id' => $tr_id
                )
            );
            if ($res) {
                $status = true;
                $color = 'red';
            }
        }


    }
    echo json_encode(array('status' => $status, 'color' => $color));
    die;
}

//---------------------------------------------------


function wm_wptree_insert_records_postdata($post_id)
{
	
    $data_check = get_post_meta($post_id, 'wm_record_added', true);

    $date_create = strtotime(get_the_date('M j, Y @ G:i', $post_id));
    $modify_date = strtotime(get_the_modified_date('M j, Y @ G:i', $post_id));

    if (isset($_POST['post_status']) && $_POST['post_status'] == 'publish' && $data_check != 'added' && $modify_date == $date_create)
	{
		
        global $wpdb;
		
		$category_detail=get_the_category($post_id);
		
		if(sizeof($category_detail) > 0)
		{
					foreach($category_detail as $cd)
					{
						
						
							
						$tbl_tree = $wpdb->prefix . 'wm_trees';
						$tbl_maps = $wpdb->prefix . 'wm_maps';
						$tr_title_ = ($_POST['post_type'] == 'post') ? 'Posts' : 'Pages'; 
						
						$get_results = $wpdb->get_results("select tree.tr_id,tree.mp_id from $tbl_tree as tree left join $tbl_maps as map on map.mp_id = tree.mp_id where tree.tr_type = 'term' and tree.tr_title = '".$cd->cat_name."' AND map.mp_type='vertical' ");

						if ($get_results) {
							foreach ($get_results as $vals) {

								$last_ord = $wpdb->get_var("select MAX(tr_order) from $tbl_tree where tr_type = 'hierarchical_post' AND mp_id = " . $vals->mp_id);

								$data = array();
								$data['mp_id'] = $vals->mp_id;
								$data['tr_parent_id'] = $vals->tr_id;
								$data['tr_title'] = get_the_title($post_id);
								$data['tr_link'] = get_permalink($post_id);
								$data['tr_active_link'] = 1;
								$data['tr_type'] = 'hierarchical_post';
								$data['tr_order'] = $last_ord + 1;
								if (false !== $wpdb->insert($tbl_tree, $data)) {
									$id = $wpdb->insert_id;
								}
							}
						}
					}
		}else{
			
			
						$tbl_tree = $wpdb->prefix . 'wm_trees';
						$tbl_maps = $wpdb->prefix . 'wm_maps';
						$tr_title_ = 'Pages'; 
						
						$get_results = $wpdb->get_results("SELECT maps.mp_id, trees.tr_id FROM $tbl_tree trees, $tbl_maps  maps where tr_type = 'post_type' and trees.mp_id = maps.mp_id and tr_order=0");

						if ($get_results) {
							foreach ($get_results as $vals)
							{

								$last_ord = $wpdb->get_var("select MAX(tr_order) from $tbl_tree where tr_type = 'hierarchical_post' AND mp_id = " . $vals->mp_id);

								$data = array();
								$data['mp_id'] = $vals->mp_id;
								$data['tr_parent_id'] = $vals->tr_id;
								$data['tr_title'] = get_the_title($post_id);
								$data['tr_link'] = get_permalink($post_id);
								$data['tr_active_link'] = 1;
								$data['tr_type'] = 'hierarchical_post';
								$data['tr_order'] = $last_ord + 1;
								if (false !== $wpdb->insert($tbl_tree, $data)) {
									$id = $wpdb->insert_id;
								}
							}
						}
						
						
		}
        update_post_meta($post_id, 'wm_record_added', 'added');
    }
}



function wm_wptree_update_records_postdata($post_id)
{
	global $wpdb;
	$permalink = get_permalink($post_id);
	
	if($permalink !='')
	{
		$wpdb->query("update `{$wpdb->prefix}wm_trees` set tr_title = '".get_the_title($post_id)."' where tr_link = '".$permalink."'");

	}
}

function wm_wptree_delete_records_postdata($post_id)
{
	global $wpdb;
	$permalink = get_permalink($post_id);
	
	if($permalink !='')
	{
		$wpdb->query("delete from `{$wpdb->prefix}wm_trees` where tr_link = '".$permalink."'");

	}
}

add_action('draft_to_publish', 'wm_wptree_insert_records_postdata', 10, 1);
add_action('post_updated', 'wm_wptree_update_records_postdata', 10, 1);
add_action('wp_trash_post', 'wm_wptree_delete_records_postdata', 10, 1);

function wm_show_hide_vertical_tree_node()
{
    global $wpdb;
    $tbl_tree = $wpdb->prefix . 'wm_trees';
    $status = false;
    $nodes = isset($_POST['nodes']) ? $_POST['nodes'] : array();
    if (isset($_POST['opration']) && isset($_POST['mp_id']) && !empty($nodes) && is_array($nodes)) {
        $mp_id = $_POST['mp_id'];

        if ($_POST['opration'] == 'hide') {
            foreach ($nodes as $ids) {
                $res = $wpdb->update(
                    $tbl_tree,
                    array('tr_hide' => 1),
                    array('mp_id' => $mp_id,
                        'tr_id' => $ids
                    )
                );
            }
            $status = true;
        } elseif ($_POST['opration'] == 'show') {
            foreach ($nodes as $ids) {
                $res = $wpdb->update(
                    $tbl_tree,
                    array('tr_hide' => 0),
                    array('mp_id' => $mp_id,
                        'tr_id' => $ids
                    )
                );
            }
            $status = true;
        } else {
            $status = false;
        }
    }
    echo json_encode(array('status' => $status, 'nodes' => $nodes));
    die;
}


//---------------------------------------------------

function pr($data = '', $die = false)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    if ($die) {
        die;
    }
}

?>