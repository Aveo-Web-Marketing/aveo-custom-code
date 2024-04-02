<?php

// Function to display the create snippet page
function aveo_custom_code_edit_snippet_page() {

    global $wpdb;

    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if ($message = get_transient('aveo_snippet_success_message')) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        delete_transient('aveo_snippet_success_message');
    }

    // Get the snippet ID from the URL
    $snippet_id = isset($_GET['snippet_id']) ? intval($_GET['snippet_id']) : 0;

    $snippet_name = '';
    $snippet_code = '';
    $snippet_active = '';

    // If a valid snippet ID is provided, attempt to retrieve the snippet from the database
    if ($snippet_id > 0) {
        $table_name = $wpdb->prefix . 'aveo_custom_code';
        $snippet = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $snippet_id));

        if ($snippet) {
            // Assign the retrieved values to variables
            $snippet_name = esc_attr($snippet->name);
            $snippet_code = esc_textarea($snippet->code); 
            $snippet_active = checked($snippet->is_active, 1, false);
        }
    }

    $html_output = '
        <div class="aveo-custom-code-wrap">
            <h1>Edit snippet</h1>
            <form action="" method="post" id="aveo-custom-code-form">
                <input type="hidden" name="snippet_id" value="' . $snippet_id . '">
                <input type="text" name="aveo_snippet_name" value="' . $snippet_name . '" placeholder="Snippet Name" style="width:100%; margin-bottom:10px;">
                <textarea id="aveo-code-editor" name="aveo_code_editor" style="width:100%; height:300px;">' . $snippet_code . '</textarea>
                ' . wp_nonce_field('aveo_custom_code_action', 'aveo_custom_code_nonce', true, false) . '
                <input type="submit" name="aveo_submit_snippet" value="Save Snippet" class="button button-primary">
                <input type="checkbox" name="aveo_snippet_active" value="1" ' . $snippet_active . '> <span>Activate Snippet</span>
            </form>
        </div>
    ';

    echo $html_output;
}




