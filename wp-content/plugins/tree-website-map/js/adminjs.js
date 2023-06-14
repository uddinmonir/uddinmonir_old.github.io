
//-----------------------------------------------------
$( window ).load(function() {
	//setTimeout(function(){jQuery('.hidden_node').parents('a').next('ul').hide();}, 1000);
});

function wm_edit_horizontal_map(refreshAfter){
	refreshAfter = (typeof refreshAfter != 'undefined' && refreshAfter != null)? refreshAfter : false;
	formChanged = false;
	showProcessStatusMessage(true, 'waiting', 'Editing map in progress..');
	jQuery.ajax({
		method: 'POST',
		url: wm_options.admin_url + 'admin-ajax.php',
		data: "action=wm_edit_horizontal_map&" + jQuery('#wm_horizontal_tree_form').serialize(),
		success: function(data){
			if(Number(data) == 1){
				showProcessResultMessage('success', 'Map updated successfully!');
				if(refreshAfter){
					window.location.href = wm_options.admin_url + 'admin.php?page=wm_edit_website_map&mpid=' + jQuery('#mp_id').val();
				}
			}
		}
	});
}
//-----------------------------------------------------
function wm_edit_vertical_map(refreshAfter){
	var state_data = localStorage.getItem('demo_'+jQuery('#mp_id').val());
	refreshAfter = (typeof refreshAfter != 'undefined' && refreshAfter != null)? refreshAfter : false;
	formChanged = false;
	showProcessStatusMessage(true, 'waiting', 'Editing map in progress..');
	jQuery.ajax({
		method: 'POST',
		url: wm_options.admin_url + 'admin-ajax.php',
		data: "action=wm_edit_vertical_map&" + jQuery('#wm_vertical_tree_form').serialize() + '&stats='+ state_data,
		success: function(data){
			if(Number(data) == 1){
				showProcessResultMessage('success', 'Map updated successfully!');
				if(refreshAfter){
					window.location.href = wm_options.admin_url + 'admin.php?page=wm_edit_website_map&mpid=' + jQuery('#mp_id').val();
				}
			}
		}
	});
}
//-----------------------------------------------------
function wm_delVerTreeNodes(){
	var selec_ids = jQuery('#wm_vertical_tree').jstree('get_checked', false);
	var selec_objs = jQuery('#wm_vertical_tree').jstree('get_checked', true);
	
	if(selec_ids.length > 0 && confirm('Remove node(s) ?')){
		showProcessStatusMessage(true, 'waiting', 'Deleting tree node in progress..');
		jQuery.ajax({
			method: 'POST',
			url: wm_options.admin_url + 'admin-ajax.php',
			data: {
				action: 'wm_del_vertical_tree_node',
				mp_id: jQuery('#mp_id').val(),
				nodes: selec_ids
			},
			success: function(data) {
				if(Number(data) == 1){
					showProcessResultMessage('success', 'Tree node is deleted successfully!');
					jQuery('#wm_vertical_tree').jstree('delete_node', selec_objs);
				}
			}
		});
	}
}

//-----------------------------------------------------
function wm_hideVerTreeNodes(){
	var selec_ids = jQuery('#wm_vertical_tree').jstree('get_checked', false);
	var selec_objs = jQuery('#wm_vertical_tree').jstree('get_checked', true);

	if(selec_ids.length > 0 && confirm('Hide node(s) ?')){
		showProcessStatusMessage(true, 'waiting', 'Hiding tree nodes in progress..');
		jQuery.ajax({
			method: 'POST',
			url: wm_options.admin_url + 'admin-ajax.php',
			data: {
				action: 'wm_show_hide_vertical_tree_node',
				mp_id: jQuery('#mp_id').val(),
				nodes: selec_ids,
				'opration':'hide'
			},
			success: function(data) {
				var jsondata = jQuery.parseJSON(data);
				if(jsondata.status)
				{
					showProcessResultMessage('success', 'Seected nodes is now hidden!');
					for(var i = 0; i < jsondata.nodes.length; i++) {
						jQuery('#'+jsondata.nodes[i]).find('.fa.fa-eye-slash').parent().addClass('hidden-node');
					}
					$('#wm_vertical_tree').jstree(true).refresh();
					//setTimeout(function (){window.location.reload();},1000);
				}
			}
		});
	}
}
//-----------------------------------------------------
function wm_showVerTreeNodes(){
	var selec_ids = jQuery('#wm_vertical_tree').jstree('get_checked', false);
	var selec_objs = jQuery('#wm_vertical_tree').jstree('get_checked', true);

	if(selec_ids.length > 0 && confirm('Show node(s) ?')){
		showProcessStatusMessage(true, 'waiting', 'Showing tree node in progress..');
		jQuery.ajax({
			method: 'POST',
			url: wm_options.admin_url + 'admin-ajax.php',
			data: {
				action: 'wm_show_hide_vertical_tree_node',
				mp_id: jQuery('#mp_id').val(),
				nodes: selec_ids,
				'opration':'show'
			},
			success: function(data) {
				var jsondata = jQuery.parseJSON(data);
				if(jsondata.status)
				{
					showProcessResultMessage('success', 'Tree node shown successfully!');
					for(var i = 0; i < jsondata.nodes.length; i++) {
						jQuery('#'+jsondata.nodes[i]).find('.fa.fa-eye-slash').parent().removeClass('hidden-node');
					}
					$('#wm_vertical_tree').jstree(true).refresh();
					//setTimeout(function (){window.location.reload();},1000);
				}
			}
		});
	}
}
//-----------------------------------------------------
function wm_deleteMap(mp_id){
	if(confirm('Are sure you want to delete this map?')){
		showProcessStatusMessage(true, 'waiting', 'Deleting map in progress..');
		jQuery.ajax({
			method: 'POST',
			url: wm_options.admin_url + 'admin-ajax.php',
			data: {
				action: 'wm_delete_map',
				mp_id: mp_id
			},
			success: function(data){
				if(Number(data) == 1){
					showProcessResultMessage('success', 'Map is deleted successfully!');
					jQuery('#tr-mp_id-' + mp_id).remove();
				}
			}
		});
	}
}

//---------------------------------------------------------
function wm_DuplicateMap(mp_id){
	if(confirm('Are sure you want to duplicate this map?')){
		showProcessStatusMessage(true, 'waiting', 'Duplicating map in progress..');
		jQuery.ajax({
			method: 'POST',
			url: wm_options.admin_url + 'admin-ajax.php',
			data: {
				action: 'wm_duplicate_tree',
				mp_id: mp_id
			},
			success: function(data){
				if(Number(data) == 1){
					showProcessResultMessage('success', 'Map is duplicated successfully!');
					location.reload();
				}
			}
		});
	}
}
//-----------------------------------------------------
function showProcessStatusMessage(show, processStatus, message){
	message = (typeof message != 'undefined')? message : '';
	processStatus = (typeof processStatus != 'undefined')? processStatus : '';
	jQuery('#processMessage').removeClass('waitingStatus');
	jQuery('#processMessage').removeClass('successStatus');
	jQuery('#processMessage').removeClass('failureStatus');
	jQuery('#processMessage').removeClass('alertStatus');
	
	switch(processStatus){
		case 'waiting':
		jQuery('#processMessage').addClass('waitingStatus');
		break;
		
		case 'success':
		jQuery('#processMessage').addClass('successStatus');
		break;
		
		case 'failure':
		jQuery('#processMessage').addClass('failureStatus');
		break;
		
		case 'alert':
		jQuery('#processMessage').addClass('alertStatus');
		break;
		
		default:
	}
	
	jQuery('#processMessage span').html(message);
	
	if(show){
		jQuery('#processMessageContainer').css('display', 'inline-block');
	} else{
		jQuery('#processMessageContainer').css('display', 'none');
	}
}
//-----------------------------------------------------
function showProcessResultMessage(status, message, timeout){
	timeout = (typeof timeout != 'undefined')? timeout : 5000;
	
	showProcessStatusMessage(true, status, message);
	
	var t = setTimeout(function(){
		showProcessStatusMessage(false, null, '');
		clearTimeout(t);
	}, timeout);
}
//-----------------------------------------------------
function monitorFormChanges(fieldNames){
	for(var i = 0; i < fieldNames.length; i++){
		var name = fieldNames[i].replace(/(\[|\])/g, '\\$1');
		var element = jQuery('[name=' + name + ']');
		
		var tagName = element.prop('tagName');
		if(tagName == 'SELECT'){
			element.change(function(){
				formChanged = true;
			});
		}
		else if(tagName == 'INPUT'){
			var type = element.prop('type');
			switch(type){
				case 'text':
				case 'password':
				element.keypress(function(){
					formChanged = true;
				});
				break;
				
				case 'radio':
				case 'checkbox':
				element.click(function(){
					formChanged = true;
				});
				break;
				
				default:
				element.keypress(function(){
					formChanged = true;
				});
			}
		}
		else if(tagName == 'TEXTAREA'){
			element.keypress(function(){
				formChanged = true;
			});
		}
		else{
			element.keypress(function(){
				formChanged = true;
			});
		}
	}
}
//-----------------------------------------------------
var addImageButton = null;
window.original_send_to_editor = window.send_to_editor;

window.send_to_editor = function(html){
	var fileurl = '';
	fileurl = jQuery('img', html).attr('src');
	if(!fileurl){
		var regex = /src="(.+?)"/;
		var rslt = html.match(regex);
		fileurl = rslt[1];
		
		addImageButton.parentNode.thumbnail_id.value = encodeURIComponent(fileurl);
		jQuery('#' + addImageButton.id).after('<img src="' + wm_options.plug_images_url + 'check_16.png" width="16" height="16" style="margin: 5px;" />');
	}
	
	tb_remove();
	jQuery('html').removeClass('Image');
}
//-----------------------------------------------------
function wm_addImage(btn){
	addImageButton = btn;
	
	jQuery('html').addClass('Image');
	
	var frame;
	if (WORDPRESS_VER >= "3.5") {
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media();
		frame.on("select", function(){
			var attachment = frame.state().get("selection").first();
			var fileurl = attachment.attributes.url;
			frame.close();
			
			addImageButton.parentNode.thumbnail_id.value = encodeURIComponent(fileurl);
			jQuery('#' + addImageButton.id).after('<img src="' + wm_options.plug_images_url + 'check_16.png" width="16" height="16" style="margin: 5px;" />');
		});
		frame.open();
	}
	else {
		tb_show("", "media-upload.php?type=image&amp;TB_iframe=true&amp;tab=library");
		return false;
	}
	
}
//-----------------------------------------------------
function disableElements(elementNames, disable, value, criticalValue){
	disable = (value == criticalValue)? disable : !disable;
	for(var i = 0; i < elementNames.length; i++){
		var field = jQuery('[name="' + elementNames[i].replace(/(\[|\])/g, '\\$1') + '"]');
		field.prop('disabled', disable);
	}
}
//-----------------------------------------------------
function wm_changeButtonsStatus(){
	var arr = jQuery('#wm_vertical_tree').jstree('get_checked', false);
	var disabled = true;
	if(arr.length > 0){
		disabled = false;
	}
	jQuery('#wm_deleteNodeBtn').prop('disabled', disabled);
	jQuery('#wm_restoreNodeBtn').prop('disabled', disabled);
}
//-----------------------------------------------------
function wm_setContainerDimentions(){
	jQuery('[name="mp_container_width"]').val(jQuery('#treeContainer').width());
	jQuery('[name="mp_container_height"]').val(jQuery('#treeContainer').height() + 200);
	showProcessResultMessage('alert', 'Container dimensions are changed', 4000);
	formChanged = true;
}
//-----------------------------------------------------
function wm_editVerNode(tr_id){
	showProcessStatusMessage(true, 'waiting', 'Retreiving node data...');
	jQuery.ajax({
		url: wm_options.admin_url + 'admin-ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {
			action: 'wm_get_node_props',
			tr_id: tr_id
		},
		success: function(data){
			if(typeof data.data != 'undefined'){
				showProcessStatusMessage(false);
				jQuery('#tr_id').val(data.data.tr_id);
				jQuery('#tr_title').val(data.data.tr_title);
				jQuery('#tr_link').val(data.data.tr_link);
				if(data.data.tr_thumbnail_url != '' && data.data.tr_thumbnail_url != null){
					wm_set_ver_tree_node_thumbnail(data.data.org_thumbnail_url, data.data.tr_thumbnail_url);
				}
				wm_show_node_modal();
			} else{
				showProcessResultMessage('failure', 'Failed to get node\' data');
			}
		},
		error: function(){
			showProcessResultMessage('failure', 'Failed to connect to the server');
		}
	});
}
//-----------------------------------------------------
function wm_add_ver_node(tr_parent_id){
	jQuery('#tr_parent_id').val(tr_parent_id);
	wm_show_node_modal();
}
//-----------------------------------------------------
function wm_show_node_modal(){
	jQuery('#wm_node_form_modal').modal({
		backdrop: 'static'
	}).on('hidden.bs.modal', function(){
		wm_reset_node_form();
	});
}
//-----------------------------------------------------
var iscf = null;
var iscfp = null;
window.original_send_to_editor = window.send_to_editor;

window.send_to_editor = function(html){
	var fileurl = '';
	fileurl = jQuery('img', html).attr('src');
	if(!fileurl){
		var regex = /src="(.+?)"/;
		var rslt = html.match(regex);
		fileurl = rslt[1];
		
		window[iscf](fileurl, iscfp);
	}
	
	tb_remove();
	jQuery('html').removeClass('Image');
}
//-----------------------------------------------------
function wm_add_image(callback, params){
	iscf = callback;
	iscfp = params;
	
	jQuery('html').addClass('Image');
	
	var frame;
	if (wm_options.wp_ver >= "3.5") {
		if (frame) {
			frame.open();
			return;
		}
		frame = wp.media();
		frame.on("select", function(){
			var attachment = frame.state().get("selection").first();
			var fileurl = attachment.attributes.url;
			frame.close();
			
			//var url = encodeURIComponent(fileurl);
			window[iscf](fileurl, iscfp);
		});
		frame.open();
	}
	else {
		tb_show("", "media-upload.php?type=image&amp;TB_iframe=true&amp;tab=library");
		return false;
	}
	
}
//-----------------------------------------------------
function wm_set_ver_tree_node_small_thumb(url){
	jQuery.ajax({
		url: wm_options.admin_url + 'admin-ajax.php',
		method: 'POST',
		data: {
			action: 'wm_get_small_thumb',
			thumb_url: encodeURIComponent(url)
		},
		success: function(data){
			if(data){
				wm_set_ver_tree_node_thumbnail(url, data);
			} else{
				wm_set_ver_tree_node_thumbnail(url, url);
			}
		},
		error: function(){
			wm_set_ver_tree_node_thumbnail(url, url);
		}
	})
}
//-----------------------------------------------------
function wm_set_ver_tree_node_thumbnail(org_url, thumb_url){
	jQuery('#tr_thumbnail_url').val(thumb_url);
	jQuery('#wm_thumbnail_wrapper').css('border', '2.5px solid #CCCCCC');
	jQuery('#wm_thumbnail_wrapper').css('padding', '5px');
	jQuery('#wm_thumbnail_wrapper').html('<img src="' + org_url + '"  style="width: 100%; height: 128px;" />');
	jQuery('#wm_add_thumb_btn').css('display', 'none');
	jQuery('#wm_remove_thumb_btn').css('display', 'block');
}
//-----------------------------------------------------
function wm_del_ver_tree_node_thumbnail(){
	jQuery('#tr_thumbnail_url').val('');
	jQuery('#wm_thumbnail_wrapper').html('');
	jQuery('#wm_thumbnail_wrapper').css('border', 'none');
	jQuery('#wm_thumbnail_wrapper').css('padding', '0px');
	jQuery('#wm_add_thumb_btn').css('display', 'block');
	jQuery('#wm_remove_thumb_btn').css('display', 'none');
}
//-----------------------------------------------------
function wm_save_node(){
	showProcessStatusMessage(true, 'waiting', 'Saving node...');
	var tr_id = jQuery('#tr_id').val();
	var tr_parent_id = jQuery('#tr_parent_id').val();
	jQuery.ajax({
		url: wm_options.admin_url + 'admin-ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {
			action: 'wm_save_ver_node',
			mp_id: jQuery('#mp_id').val(),
			tr_id: jQuery('#tr_id').val(),
			tr_parent_id: jQuery('#tr_parent_id').val(),
			tr_title: jQuery('#tr_title').val(),
			tr_link: jQuery('#tr_link').val(),
			tr_thumbnail_url: jQuery('#tr_thumbnail_url').val()
		},
		success: function(data){
			if(data.success){
				showProcessResultMessage('success', 'Successfully saved!');
				jQuery('#wm_node_form_modal').modal('hide');
				if(data.data.new_node){
					var parent_node = jQuery("#wm_vertical_tree").jstree('get_node', data.data.node_parent);
					jQuery("#wm_vertical_tree").jstree('create_node', parent_node, data.data.node_text);
				} else{
					var node = jQuery("#wm_vertical_tree").jstree('get_node', data.data.node_id);
					jQuery("#wm_vertical_tree").jstree('rename_node', node, data.data.node_text.text);
					jQuery("#wm_vertical_tree").jstree('set_icon', node, data.data.node_text.icon);
				}
			} else{
				showProcessResultMessage('failure', 'Failed to save the node');
			}
		},
		error: function(){
			showProcessResultMessage('failure', 'Failed to connect to the server');
		}
	});
}
//-----------------------------------------------------
function wm_move_ver_node(tr_id, tr_parent_id_old, tr_parent_id, tr_order_old, tr_order,mp_id = 0,record_update = {}){
	showProcessStatusMessage(true, 'waiting', 'Moving node...');
	jQuery.ajax({
		url: wm_options.admin_url + 'admin-ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {
			action: 'wm_move_ver_node',
			tr_id: tr_id,
			tr_parent_id_old: tr_parent_id_old,
			tr_parent_id: tr_parent_id,
			tr_order_old: tr_order_old,
			tr_order: tr_order,
			mp_id: mp_id,
			record_update: JSON.stringify(record_update)
		},
		success: function(data){
			if(data.success){
				showProcessResultMessage('success', 'Successfully moved!');
			} else{
				showProcessResultMessage('failure', 'Failed to move the node');
			}
		},
		error: function(){
			showProcessResultMessage('failure', 'Failed to connect to the server');
		}
	});
}
//-----------------------------------------------------
function wm_reset_node_form(){
	jQuery('#tr_id').val('');
	jQuery('#tr_parent_id').val('');
	jQuery('#tr_title').val('');
	jQuery('#tr_link').val('');
	wm_del_ver_tree_node_thumbnail();
}
//-----------------------------------------------------

function wm_hide_ver_node(tr_parent_id,ele){

	var check_admin = jQuery('#is_admin_side').length;
	jQuery.ajax({
		url: wm_options.admin_url + 'admin-ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {
			action: 'wm_hide_ver_node',
			mp_id: jQuery('#mp_id').val(),
			tr_id: tr_parent_id,
			'check_admin':check_admin,
		},
		success: function (data) {

			if(data.status)
			{
				jQuery(ele).css('color',data.color);


				if(data.color == 'red')
				{
					jQuery(ele).addClass('hidden_node');
					//jQuery(ele).parents('a').next('ul').hide();
				}else{
					jQuery(ele).removeClass('hidden_node');
					//jQuery(ele).parents('a').next('ul').show();


				}

			}

		}
	});
}
