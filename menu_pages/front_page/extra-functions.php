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



// Ajax function to delete a snippet
function aveo_custom_code_delete_snippet() {
    // Check user capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to delete snippets.');
        wp_die();
    }

    // Get snippet id
    $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;

    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';

    // Retrieve the snippet to get the file path
    $snippet = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $snippet_id));
    if (!$snippet) {
        wp_send_json_error('Snippet not found.');
        wp_die();
    }

    $file_path = $snippet->file;

    // Delete the database entry
    $delete_success = $wpdb->delete($table_name, array('id' => $snippet_id));
    if ($delete_success) {
        // Check if file exists and delete it
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                wp_send_json_success('Snippet and file deleted successfully.');
            } else {
                wp_send_json_error('Failed to delete file, but snippet entry removed from database.');
            }
        } else {
            wp_send_json_success('File does not exist, but snippet entry removed from database.');
        }
    } else {
        wp_send_json_error('Failed to delete snippet from database.');
    }

    wp_die();
}

// Add the ajax action
add_action('wp_ajax_aveo_custom_code_delete_snippet', 'aveo_custom_code_delete_snippet');

// nopriv
add_action('wp_ajax_nopriv_aveo_custom_code_delete_snippet', 'aveo_custom_code_delete_snippet');