<?php
// Ajax function to activate or deactivate a snippet
function aveo_custom_code_activate_snippet() {
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get snippet id
    $snippet_id = $_POST['snippet_id'];

    // Get snippet status
    $status = $_POST['activation_status'];

    // Update snippet status
    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';
    $wpdb->update(
        $table_name,
        array('is_active' => $status),
        array('id' => $snippet_id)
    );

    
    // Don't forget to stop execution afterward
    wp_die();
}

// Add the ajax action
add_action('wp_ajax_aveo_custom_code_activate_snippet', 'aveo_custom_code_activate_snippet');

// nopriv
add_action('wp_ajax_nopriv_aveo_custom_code_activate_snippet', 'aveo_custom_code_activate_snippet');
