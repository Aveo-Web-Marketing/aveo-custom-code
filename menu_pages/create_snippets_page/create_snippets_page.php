<?php

// Function to display the create snippet page
function aveo_custom_code_create_snippet_page() {

    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if ($message = get_transient('aveo_snippet_error_message')) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        delete_transient('aveo_snippet_error_message');
    }

    $html_output = '
        <div class="aveo-custom-code-wrap">
            <h1>Create New Snippet</h1>
            <form action="" method="post" id="aveo-custom-code-form">
                <input type="text" name="aveo_snippet_name" placeholder="Snippet Name" style="width:100%; margin-bottom:10px;">
                <textarea id="aveo-code-editor" name="aveo_code_editor" style="width:100%; height:300px;"></textarea>
                ' . wp_nonce_field('aveo_custom_code_action', 'aveo_custom_code_nonce', true, false) . '
                <input type="submit" name="aveo_submit_snippet" value="Save Snippet" class="button button-primary">
                <input type="checkbox" name="aveo_snippet_active" value="1" checked> <span>Activate Snippet</span>
            </form>
        </div>
    ';

    echo $html_output;
}




