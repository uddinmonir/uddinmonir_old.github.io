(function() {
    tinymce.create('tinymce.plugins.wmtreeshortcode', {
		createControl : function(btn, cm) {
            if(btn == 'wmsrtcdcombo'){
                var cmbo = cm.createListBox('wmsrtcdcombo', {
                     title : 'drop',
                     onselect : function(v) {
                     	if(tinyMCE.activeEditor.selection.getContent() == ''){
                            tinyMCE.activeEditor.selection.setContent( v );
                        }
                     }
                });

                for(var i in wm_maps_shortcode){
                	cmbo.add(wm_maps_shortcode[i], wm_maps_shortcode[i]);
				}
                return cmbo;
            }
            return null;
        }
	});
	
    tinymce.PluginManager.add('wmtreeshortcode', tinymce.plugins.wmtreeshortcode);
})();