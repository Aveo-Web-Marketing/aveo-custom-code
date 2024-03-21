
// JS file to initialize the code editor. This is rooted in the 'aveo' namespace, to avoid conflicts with other plugins.

// Check if the code editor exists and initialize the codeMirror editor
jQuery(document).ready(function($) {
    if ($('#aveo-code-editor').length > 0) {
        wp.codeEditor.initialize($('#aveo-code-editor'), {
            type: 'text/x-php',
            codemirror: { indentUnit: 2, tabSize: 2 }
        });
    }
});
