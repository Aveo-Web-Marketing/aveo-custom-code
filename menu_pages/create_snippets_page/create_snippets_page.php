<?php
// Function to display the create snippet page
function aveo_custom_code_create_snippet_page() {
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
    echo '</form>';

}


// enqueuing the code editor with CodeMirror
function aveo_enqueue_code_editor_assets($hook) {
    // Assuming 'aveo-custom-code-create-snippet' is the menu slug for your plugin's page
    if ($hook !== 'toplevel_page_aveo-custom-code' && $hook !== 'aveo-custom-code_page_aveo-custom-code-create-snippet') {
        return;
    }

    // Correct path assuming your-plugin-main-file.php is in the root of your plugin directory
    wp_enqueue_script('aveo-code-editor-init', plugin_dir_url(__FILE__) . 'code-editor-init.js', array('wp-code-editor'), null, true);

    // No need to enqueue these here if you're using wp_enqueue_code_editor(), it does it for you
    // wp_enqueue_script('wp-theme-plugin-editor');
    // wp_enqueue_style('wp-codemirror');
}
add_action('admin_enqueue_scripts', 'aveo_enqueue_code_editor_assets');


