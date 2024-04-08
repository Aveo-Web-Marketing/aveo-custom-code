<?php

// Include style and script files
function aveo_custom_code_enqueue_scripts() {
    // Enqueue the create_snippets_page.css file
    wp_enqueue_style('aveo-custom-code-create-snippets-page', plugin_dir_url(__FILE__) . 'create_snippets_page.css');
    
}
add_action('admin_enqueue_scripts', 'aveo_custom_code_enqueue_scripts');

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
                <div class="aveo-custom-code-snippet-info">
                    <input type="text" name="aveo_snippet_name" placeholder="Snippet Name" style="width:100%;">
                    <input type="textarea" name="aveo_snippet_description" placeholder="Write  the description of you custom code here." style="width:100%;">
                    <textarea id="aveo-code-editor" name="aveo_code_editor" style="width:100%;"></textarea>
                    ' . wp_nonce_field('aveo_custom_code_action', 'aveo_custom_code_nonce', true, false) . '
                </div>
                <div class="aveo-custom-code-snippet-condition-wrap">
                    <div>
                        <label for="Snippet type">Document Type</label>
                        <select name="aveo_snippet_type">
                            <option value="php">PHP</option>
                            <option value="css">CSS</option>
                            <option value="js">JavaScript</option>
                        </select>
                        <span>This should match the code you write</span>
                    </div>
                    <div>
                        <label for="Snippet Condition">Where to run code</label>
                        <select name="aveo_snippet_condition">
                            <option value="everywhere">Everywhere</option>
                            <option value="only_frontend">Only in the Frontend</option>
                            <option value="only_backend">Only in the WP backend</option>
                        </select>
                    </div>
                    <div>
                        <label for="Snippet activation">Activate snippet on save</label>
                        <input type="checkbox" name="aveo_snippet_active" value="1" checked>
                    </div>
                    <input type="submit" name="aveo_submit_snippet" value="Save Snippet" class="button button-primary">
                </div>
            </form>
        </div>
    ';

    echo $html_output;
}




