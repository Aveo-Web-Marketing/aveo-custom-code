console.log('Code editor init - js file');


jQuery(document).ready(function($) {

    console.log('select change event');

    var typeSelect = $('[name="aveo_snippet_type"]');
    var conditionSelect = $('[name="aveo_snippet_condition"]');

    var optionsForTypes = {
        php: [
            { value: "everywhere", text: "Everywhere" },
            { value: "only_frontend", text: "Only in the Frontend" },
            { value: "only_backend", text: "Only in the WP backend" }
        ],
        css: [
            { value: "everywhere", text: "Everywhere" },
            { value: "only_frontend", text: "Only in the Frontend" },
            { value: "only_backend", text: "Only in the WP backend" }
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

jQuery(document).ready(function($) {
    var editorInstance; // Hold the editor instance

    // Encapsulate the shared logic in a function
    function initializeCodeEditor() {
        if (typeof cm_settings !== 'undefined' && cm_settings.codeEditor) {
            // Check if the editor has already been initialized
            if (!editorInstance) {
                // Initialize the editor if it hasn't been initialized yet
                editorInstance = wp.codeEditor.initialize($('#aveo-code-editor'), cm_settings.codeEditor).codemirror;
            } else {
                // Update the editor mode directly if it's already been initialized
                editorInstance.setOption("mode", cm_settings.codeEditor.codemirror.mode);
            }
        }
        console.log('cm_settings', cm_settings);
    }

    // Call the function on document ready to run it on initialization
    initializeCodeEditor();

    // Attach a change event listener to the select element
    $('.aveo_snippet_type').on('change', function() {
        // Getting the selected option's value
        var selectedOptionValue = $(this).val();

        // Updating the mode based on the selected option's value
        if (selectedOptionValue === 'php') {
            cm_settings.codeEditor.codemirror.mode = 'text/x-php';
        } else if (selectedOptionValue === 'css') {
            cm_settings.codeEditor.codemirror.mode = 'text/css';
        } else if (selectedOptionValue === 'js') {
            cm_settings.codeEditor.codemirror.mode = 'text/javascript';
        }

        // Re-initialize or update the code editor
        initializeCodeEditor();
    });
});

jQuery(function($) {
    // Listen for changes on the select element with the name 'aveo_snippet_type'
    $('select[name="aveo_snippet_type"]').change(function() {
        var selectedValue = $(this).val();
        // Check if the selected value is 'php'
        if (selectedValue === 'php') {
            $('.aveo-custom-code-snippet-info').addClass('code-editor-before');
        } else {
            $('.aveo-custom-code-snippet-info').removeClass('code-editor-before');
        }
    });
});

