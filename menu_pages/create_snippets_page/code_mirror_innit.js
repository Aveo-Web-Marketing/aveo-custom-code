console.log('Code editor init - js file');


jQuery(document).ready(function($) {

    console.log('select change event');

    var typeSelect = $('[name="aveo_snippet_type"]');
    var conditionSelect = $('[name="aveo_snippet_condition"]');

    var optionsForTypes = {
        php: [
            { value: "everywhere", text: "Everywhere" },
            { value: "only_backend", text: "Only in the WP backend" }
        ],
        css: [
            { value: "everywhere", text: "Everywhere" },
            { value: "only_frontend", text: "Only in the Frontend" }
        ],
        js: [
            { value: "header", text: "In the Header" },
            { value: "body_end", text: "In the body end" }
        ]
    };

    typeSelect.change(function() {
        var selectedType = $(this).val();
        var options = optionsForTypes[selectedType] || [];

        conditionSelect.empty(); // Clear existing options
        $.each(options, function(index, opt) {
            conditionSelect.append($('<option>').val(opt.value).text(opt.text));
        });
    });
});

// CodeMirror initialization
jQuery(document).ready(function($) {
    if (typeof cm_settings !== 'undefined' && cm_settings.codeEditor) {
        var editor = wp.codeEditor.initialize($('#aveo-code-editor'), cm_settings.codeEditor);
    }

    console.log('cm_settings', cm_settings);
});

