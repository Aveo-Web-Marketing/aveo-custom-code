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
            $('.snippet_page_specific_condition').show();
        }
    });
});

jQuery(function($) {
    // Listen for changes on the select element with the name 'aveo_snippet_page_specific_condition'
    $('select[name="aveo_snippet_page_specific_condition"]').change(function() {
        var selectedValue = $(this).val();
        // Check if the selected value is 'specific'
        if (selectedValue === 'specific') {
            $('.snippet_page_specific_condition_search').show();
        }
    });
});


// Function to create search functionality for specific page condition
jQuery(function($) {
    // Function to show or hide pages based on search criteria
    function filterPages() {
        var searchText = $('.snippet_page_specific_condition_search_input').val().toLowerCase();
        $('.aveo-page-search-result').hide();
        $('.empty-text').hide();

        // Exit the function early if the input is empty
        if (searchText === '') {
            return;
        }

        // Loop through all page results
        let count = false;
        $('.aveo-page-search-result').each(function() {
            var title = $(this).text().toLowerCase();

            // Check if the search text matches the title
            if (title.includes(searchText)) {
                count = true;
                $(this).show(); // Show the div if the search text is contained in the title
            }
        });

        // If no pages are found, show the 'No pages found' message
        if (!count) {
            $('.empty-text').show();
        }
    }

    // Event listener for the search input
    $('.snippet_page_specific_condition_search_input').on('input', filterPages);

    // Event listener for clicking on a page result
    $('.aveo-page-search-results').on('click', '.aveo-page-search-result', function() {
        var pageId = $(this).data('page_id');
        var title = $(this).text();

        $('.snippet_page_specific_condition_search_input')
            .val(title) // Set the input value to the text of the clicked page
            .data('selected_con_id', pageId); // Set a data attribute with the page ID

        // Also update the hidden input's value
        $('#selected_con_id').val(pageId);

        // Console log to verify the data attribute
        console.log('#selected_con_id', $('#selected_con_id').val());

        // Set display none to the search result, to hide all pages again
        $('.aveo-page-search-result').hide();
    });
});





