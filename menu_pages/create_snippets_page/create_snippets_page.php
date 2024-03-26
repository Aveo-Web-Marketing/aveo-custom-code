<?php
// Function to display the create snippet page
function aveo_custom_code_create_snippet_page() {

    function aveo_create_snippets_enqueue_scripts() {
        // Enqueue the front_page.js file
        wp_enqueue_script('aveo-custom-code-create-snippets-page', plugin_dir_url(__FILE__) . 'aveo-code-editor-init.js', array('jquery'), '1.0', true);
    }
    add_action('admin_enqueue_scripts', 'aveo_create_snippets_enqueue_scripts');


    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="aveo-custom-code-wrap">';
    echo '<h1>Create New Snippet</h1>';
    echo '<form action="" method="post" id="aveo-custom-code-form">';
    echo '<input type="text" name="aveo_snippet_name" placeholder="Snippet Name" style="width:100%; margin-bottom:10px;">';
    echo '<textarea id="aveo-code-editor" name="aveo_code_editor" style="width:100%; height:300px;"></textarea>';
    wp_nonce_field('aveo_custom_code_action', 'aveo_custom_code_nonce');
    echo '<input type="submit" name="aveo_submit_snippet" value="Save Snippet" class="button button-primary">';
    echo '<input type="checkbox" name="aveo_snippet_active" value="1" checked> <span>Activate Snippet</span>';
    echo '</form>';

}




