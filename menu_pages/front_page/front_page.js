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