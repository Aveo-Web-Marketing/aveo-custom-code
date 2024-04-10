// Event listener for the activation / deactivaiton of a snippet
console.log('Front page JS file  loaded');


jQuery(function($) {
    // Correctly attaching the event handler
    $(document).on('click', '.snippet-activate-switch', function() {
        let id = $(this).data('snippet_id'); // Correctly getting the ID
        let activation_status = $(this).is(':checked'); // Getting the checkbox status
        snippet_activation_status_update(id, activation_status);
    });
});

// Function to run on ajax call atempt
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

// Filter snippets by type
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
    });
}); 


// Function to create a drag and drop area for the import of snippets
$(document).ready(function() {
    // Highlight the label on drag over
    $('#file-input-label').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    // Remove highlight from the label on drag leave
    $('#file-input-label').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    // Handle file drop
    $('#file-input-label').on('drop', function(e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files; // Get the files that were dropped
        $('#import-snippet-file').prop('files', files); // Assign dropped files to file input

        // Optional: Update text
        $('.file-input-text').text('File Selected');
    });
});
