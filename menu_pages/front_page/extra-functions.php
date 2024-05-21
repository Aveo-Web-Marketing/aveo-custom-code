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
        // If the deletion was successful, check if the corresponding file exists
        if (file_exists($file_path)) {
            // Attempt to delete the file
            if (unlink($file_path)) {
                // File deleted successfully
                return array('success' => true, 'message' => 'Snippet and associated file deleted successfully.');
            } else {
                // File could not be deleted
                return array('success' => false, 'message' => 'Snippet deleted, but the file could not be deleted.');
            }
        } else {
            // File does not exist
            return array('success' => true, 'message' => 'Snippet deleted. No file found to delete.');
        }
    } else {
        // Database deletion failed
        return array('success' => false, 'message' => 'Failed to delete snippet from the database.');
    }

    wp_die();
}

// Add the ajax action
add_action('wp_ajax_aveo_custom_code_delete_snippet', 'aveo_custom_code_delete_snippet');


// Ajax function to clone a snippet
function aveo_custom_code_clone_snippet() {

    ob_start(); // Start buffering output
    // Check user capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to clone snippets.');
        wp_die();
    }

    // Get snippet id
    $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;

    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';

    // Retrieve the original snippet
    $snippet = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $snippet_id));
    if (!$snippet) {
        wp_send_json_error('Snippet not found.');
        wp_die();
    }

    // Modify the snippet name to append "(CLONE)"
    $new_snippet_name = $snippet->name . " (CLONE)";

    // Prepare the data for the new snippet entry
    $new_snippet_data = array(
        'name' => $new_snippet_name,
        'type' => $snippet->type,
        'file' => $snippet->file,
        'is_active' => 0,
        'display_condition' => $snippet->display_condition,
        'description' => $snippet->description,
        'code' => $snippet->code,  // Here the code is treated just as text
    );

    // Insert the cloned snippet into the database
    $inserted = $wpdb->insert($table_name, $new_snippet_data);
    
    // Creste a new file for the cloned snippet
    if ($inserted) {
        $new_snippet_id = $wpdb->insert_id;
        $new_file_path = aveo_custom_code_create_snippet_file($snippet->name, $new_snippet_id, $snippet->type, $snippet->file);
        if ($new_file_path) {
            $wpdb->update($table_name, array('file' => $new_file_path), array('id' => $new_snippet_id));
        }
    }
    
    // if inserted array message
    if ($inserted) {
        return array('success' => true, 'message' => 'Snippet cloned successfully.');
    } else {
        return array('success' => false, 'message' => 'Failed to clone snippet.');
    }

    ob_clean(); // Discard the contents of the output buffer

    wp_die();
}

// Add the ajax action
add_action('wp_ajax_aveo_custom_code_clone_snippet', 'aveo_custom_code_clone_snippet');


// Function to create a new file for a snippet
function aveo_custom_code_create_snippet_file($name, $id, $type, $original_file_path) {
    // Assume the name already includes "(CLONE)" as per your previous logic
    $new_file_path = dirname($original_file_path) . "/{$name} (CLONE).{$type}";

    if (copy($original_file_path, $new_file_path)) {
        return $new_file_path;
    } else {
        return false;
    }
}

// Ajax function to export a snippet
function aveo_download_snippet_file() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to download files.');
        wp_die();
    }

    // Fetching ID and type from the AJAX request
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';
    $snippet = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id));

    if (!$snippet) {
        wp_send_json_error('Snippet not found.');
        wp_die();
    }

    $file_path = $snippet->file;

    if (!file_exists($file_path)) {
        wp_send_json_error('File does not exist.');
        wp_die();
    }

    // Determining the MIME type based on the type from the database
    $mime_type = 'text/plain'; // Default MIME type
    if ($type === 'js') {
        $mime_type = 'application/javascript';
    } elseif ($type === 'css') {
        $mime_type = 'text/css';
    } elseif ($type === 'php') {
        $mime_type = 'application/x-httpd-php';
    }

    // Set headers to force download
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);

    wp_die();
}
add_action('wp_ajax_aveo_download_snippet_file', 'aveo_download_snippet_file');




