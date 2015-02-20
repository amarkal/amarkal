/**
 * Implements a popup form for the tinymce popup window.
 */
Amarkal.Editor.Form = function() {};

/**
 * Initiate the popup form.
 * This is called once the popup iframe has been loaded.
 * @see Outro.js
 */
Amarkal.Editor.Form.init = function()
{
    // Only initiate if a popup for exists
    var form = $('.afw-editor-popup-form');
    
    if( 0 !== form.length ) 
    {
        var args = top.tinymce.activeEditor.windowManager.getParams()
        
        this.setValues();
        args.oninit();
    }       
};

/**
 * Set UI components values using the values passed to the windowManager
 */
Amarkal.Editor.Form.setValues = function()
{
    var values = top.tinymce.activeEditor.windowManager.getParams().values;
    
    if( typeof values !== 'undefined' )
    {
        $('.afw-editor-popup-form').find('.afw-ui-component').each(function()
        {
            var name = $(this).attr('data-name');
            if( values.hasOwnProperty( name ) )
            {
                Amarkal.UI.setValue( this, values[name] );
            }
        });
    }
};

/**
 * This function opens an ajax popup with a form based on the Amarkal UI framework.
 * The param.url parameter should specify an ajax script registered by wordpress
 * 'wp_ajax_*' action. The script should have the rendered UI.
 * 
 * @see Amarkal\Extensions\WordPress\Editor\Plugins\Form
 * 
 * @param {type} editor The TinyMCE editor from which the popup will be called.
 * @param {object} params
 * 
 * <b>Parameters</b>
 * <ul>
 * <li><b>title</b> <i>string</i> The popup's title.</li>
 * <li><b>url</b> <i>string</i> The Popup's iframe url.</li>
 * <li><b>width</b> <i>string</i> The popup's width.</li>
 * <li><b>height</b> <i>string</i> The popup's height.</li>
 * <li><b>template</b> <i>string</i> The template (will be inserted to the current active editor)</li>
 * <li><b>values</b> <i>object</i> A list of initial values for the form UI components (overrides the default values)</li>
 * <li><b>callback</b> <i>function</i> A callback function to be called once the insert button is clicked.</li>
 * </ul>
 */
Amarkal.Editor.Form.open = function( editor, p )
{   
    // Merge with defaults
    var params = Amarkal.Utility.extend( Amarkal.Editor.Form.defaults(), p);
    
    editor.windowManager.open({
        title: params.title,
        url: params.url,
        width: params.width,
        height: params.height,
        buttons: [{
            text: 'Insert',
            classes: 'widget btn primary first abs-layout-item',
            disabled: false,
            onclick: function(e){
                var iframe = $('iframe[src$="'+params.url+'"]').contents();
                var values = {};
                iframe.find('.afw-ui-component').each(function(){
                    var name  = $(this).attr('data-name');
                    var value = $(this).attr('data-value') == 'undefined' ? '' : $(this).attr('data-value');
                    values[name] = value;
                });
                
                // Call the callback
                params.callback( editor, values );
                
                editor.windowManager.close();
            }
        }, 
        {
            text: 'Close',
            onclick: function() {editor.windowManager.close();}
        }]
    }, { /* parameters. see http://www.tinymce.com/wiki.php/Tutorials:Creating_custom_dialogs */
        template: params.template,
        values: params.values,
        oninit: params.oninit
    });
};

Amarkal.Editor.Form.defaults = function()
{
    return {
        title:      '',
        url:        '',
        width:      500,
        height:     500,
        template:   '',
        oninit:     function() {},
        callback:   function( editor, values ) {
            // Default function
            var args = editor.windowManager.getParams();
            editor.insertContent( Amarkal.Editor.parseTemplate( args.template, values ) );
        },
        values:     {}
    };
};