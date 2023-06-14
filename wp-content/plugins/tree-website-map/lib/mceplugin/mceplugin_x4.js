(function() {
	var values = [];
	for(var i = 0 in wm_maps_shortcode){
		var name = (typeof wm_maps_shortcode[i].name == 'undefined' || wm_maps_shortcode[i].name == '' || wm_maps_shortcode[i].name == null)? 
					wm_maps_shortcode[i].shortcode : wm_maps_shortcode[i].name;
		values.push({text: name, value: wm_maps_shortcode[i].shortcode});
	}
    tinymce.create('tinymce.plugins.wmtreeshortcode', {
        init : function(ed, url) {
            ed.addButton( 'wmsrtcdcombo', {
                type: 'listbox',
                text: 'Site Maps',
                icon: false,
                onselect: function(e) {
                    tinymce.execCommand('mceInsertContent', false, this.value());
                },
                values: values
            });
        }
	});
	
    tinymce.PluginManager.add('wmtreeshortcode', tinymce.plugins.wmtreeshortcode);
})();