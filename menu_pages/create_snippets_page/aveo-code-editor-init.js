console.log('Code editor init - js file');

jQuery(document).ready(function($) {

    console.log('Code editor init');

    if (typeof cm_settings !== 'undefined') {

        console.log('Code editor settings found');

        wp.codeEditor.initialize($('#aveo-code-editor'), cm_settings.codeEditor);
    }
});
