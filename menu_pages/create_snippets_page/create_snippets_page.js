console.log('Code editor init - js file');


// CodeMirror initialization
jQuery(document).ready(function($) {
    if (typeof cm_settings !== 'undefined' && cm_settings.codeEditor) {
        var editor = wp.codeEditor.initialize($('#aveo-code-editor'), cm_settings.codeEditor);
    }

    console.log('cm_settings', cm_settings);
});

