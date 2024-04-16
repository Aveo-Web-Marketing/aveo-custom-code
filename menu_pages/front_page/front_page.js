// Event listener for the activation / deactivaiton of a snippet
jQuery(function($) {
    // Correctly attaching the event handler
    $(document).on('click', '.snippet-activate-switch', function() {
        let id = $(this).data('snippet_id'); // Correctly getting the ID
        let activation_status = $(this).is(':checked'); // Getting the checkbox status
        snippet_activation_status_update(id, activation_status);
    });
});

// Function to run on ajax call atempt - activate / deactivate snippet
async function snippet_activation_status_update(snippet_id, activation_status) {

    // Convert boolean values to integers for PHP
    let status_to_send = activation_status ? 1 : 0;

    // Send the data to the server
    await jQuery.ajax(
        {
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'aveo_custom_code_activate_snippet',
                snippet_id: snippet_id,
                activation_status: status_to_send
            },
            success: function(response) {
                console.log('success');
            },
            error: function(response) {
                console.log('error');
            }
        }
    );

}

// Event listener for the deletion of a snippet
jQuery(function($) {
    $(document).on('click', '.delete-snippet', function() {
        let id = $(this).data('snippet_id');
        // Confirmation dialog before deletion
        if (confirm("Are you sure you want to delete this snippet?")) {
            snippet_delete(id);
        } else {
            return false;
        }
    });
});

// Function to run on ajax call atempt - delete snippet
async function snippet_delete(snippet_id) {
    await jQuery.ajax(
        {
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'aveo_custom_code_delete_snippet',
                snippet_id: snippet_id
            },
            success: function(response) {
                console.log('success delete');
                location.reload();
            },
            error: function(response) {
                console.log('error');
            }
        }
    );
    // delete the snippet file from the plugin directory

}

// Event listener for the cloning of a snippet
jQuery(function($) {
    $(document).on('click', '.clone-snippet', function() {
        let id = $(this).data('snippet_id');
        snippet_clone(id);
    });
});

// Function to run on ajax call atempt - clone snippet
async function snippet_clone(snippet_id) {
    await jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'aveo_custom_code_clone_snippet',
            snippet_id: snippet_id
        },
        success: function() {
            console.log('success clone');
            location.reload();
        },
        error: function() {
            console.log('error');
        }
    });
}

// Event listener for the export of a snippet
jQuery(function($) {
    $(document).on('click', '.export-snippet', function() {
        var id = $(this).data('id');
        var type = $(this).data('type');
        downloadFile(id, type);
    });
});

// Function to run on ajax call atempt - export snippet
function downloadFile(id, type) {
    window.location.href = `/wp-admin/admin-ajax.php?action=aveo_download_snippet_file&id=${encodeURIComponent(id)}&type=${encodeURIComponent(type)}`;
    console.log("Attempting to download file with ID:", id, "and type:", type);
}


// Function to filter snippets by type
jQuery(function($) {
    $('.snippet-type-filter-wrap div').on('click', function() {
        let category_id = $(this).data('category');
        // Add active class to the clicked item and remove from siblings
        $(this).addClass('active').siblings().removeClass('active');

        // Loop through each snippet row
        $('.aveo-single-snippet').each(function() {
            // Check if we are showing all snippets or if the snippet's type matches the selected category
            if (category_id === 'all_snippets' || $(this).data('snippet_type') === category_id) {
                $(this).show(); // Show the row if it matches the filter
            } else {
                $(this).hide(); // Hide the row if it does not match the filter
            }
        });
    });
});

// Function to hide / show the import wrapper. Using jQuery animation
jQuery(function($) {
    $('.import-snippet-button').on('click', function() {
        $('.import-snippet-animate').slideToggle();
        $(this).toggleClass('active');
    });
}); 

// Function to create a drag and drop area for the import of snippets
jQuery(document).ready(function($) {

    console.log('Document ready');
    // Highlight the label on drag over
    $('#file-input-label').on('dragover', function(e) {
        e.preventDefault();
        console.log('Drag over event triggered');
        $(this).addClass('dragover');
    });

    // Remove highlight from the label on drag leave
    $('#file-input-label').on('dragleave', function(e) {
        e.preventDefault();
        console.log('Drag leave event triggered');
        $(this).removeClass('dragover');
    });

    function updateUIWithFileInfo(files) {
        // Assuming `files` is an array-like object of File objects
        if (files.length > 0) {
            console.log('File input changed or file dropped');
            $('.file-input-text').text('File Uploaded: ' + files[0].name);
            $('#file-input-label').css('backgroundColor', '#D4EDDA'); // Light green background
            $('.file-input-plus').css('color', '#155724'); // Dark green color for the icon
        }
    }

    // Handle file drop
    $('#file-input-label').on('drop', function(e) {
        e.preventDefault();
        console.log('File dropped');
        var files = e.originalEvent.dataTransfer.files; // Get the files that were dropped
        $('#import-snippet-file').prop('files', files); // Assign dropped files to file input
        updateUIWithFileInfo(files); // Update UI
    });

    $('#import-snippet-file').change(function() {
        updateUIWithFileInfo(this.files); // Update UI
    });
});

// Function to handle conditional logic for importet snippets
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


// Function to mark all snippets
jQuery(document).ready(function($) {
    $('#select-all-snippets').on('click', function() {
        $('.snippet-checkbox').prop('checked', $(this).prop('checked'));
    });
});


