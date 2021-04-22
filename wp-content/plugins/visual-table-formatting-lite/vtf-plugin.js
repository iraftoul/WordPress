if (typeof W3Ex === "undefined") {
    var W3Ex = {};
}

W3Ex.vtfModuleOut = (function (tinymce) {
	var _bookmark = null;
	var _bremove = false;
	jQuery(document).on('keyup', function(evt) {
		//jquery closes the thick frame and keeps the dialog
		if(_bremove)
		{
			 if (evt.keyCode === 27) {
				 jQuery("div.w3exvtfdel").remove();   
				 jQuery("div.sp-container").remove();         		 
				 jQuery("div[aria-describedby='w3exvtf-dialogstyles']").remove();
				 _bremove = false;
       		 } 
		}
     });       
	return { 
		FillText: function (text) {
		    if (tinymce.isIE) 
		    {
		        tinymce.activeEditor.selection.moveToBookmark(_bookmark);
		        tinymce.activeEditor.execCommand('mceInsertRawHTML', 0, text);
		    }else
		        tinymce.activeEditor.execCommand('mceInsertContent', 0, text);
		    tb_remove();
			jQuery("div[aria-describedby='w3exvtf-dialogstyles']").remove();
			jQuery("div.sp-container").remove(); 
		},
		SetBookmark:function(book){ _bookmark = book;},
		SetRemove:function(bremove){ _bremove = bremove;}
	};
})(tinymce);

(function($) {
    
    tinymce.create('tinymce.plugins.W3ExVTables', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.addCommand('vtables', function() {
    			var H = $(window).height();
    			if ( $( 'body.admin-bar' ).length )
    				H -= 28;
                W3Ex.vtfModuleOut.SetBookmark(ed.selection.getBookmark());
                tb_show( 'Visual Table Formatting',  'admin-ajax.php?height=' + ( H - 75 ) + '&width=870&action=vft_show_tables', false );
            });

            ed.addButton('vtables', {
                title : 'Visual Tables',
                cmd : 'vtables',
                image : url + '/images/vtables.png'
            });

        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                    longname : 'Visual Table Formatting Lite',
                    author : 'George Iron',
                    authorurl : 'http://www.w3examples.com',
                    infourl : 'http://www.w3examples.com',
                    version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('w3vtables', tinymce.plugins.W3ExVTables);
})(jQuery);