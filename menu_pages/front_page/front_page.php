<?php

// include the JS and extra functions files
include 'extra-functions.php';

// enqueue the scripts
function aveo_frontpage_code_enqueue_scripts() {
    // Enqueue the front_page.js file
    wp_enqueue_script('aveo-custom-code-front-page', plugin_dir_url(__FILE__) . 'front_page.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'aveo_frontpage_code_enqueue_scripts');

// Function to display the frontpage of the plugin
function aveo_custom_code_front_page() {
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get all snippets from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';
    $snippets = $wpdb->get_results("SELECT * FROM $table_name");
    
    $table_row_html = '';

    foreach ($snippets as $snippet) {

        $snippet_edit_url = admin_url('admin.php?page=aveo-custom-code-edit-snippet&snippet_id=' . $snippet->id);

        $table_row_html .= '
            <tr>
                <td><input type="checkbox" class="snippet-checkbox" data-snippet_id="' . $snippet->id . '"></td>
                <td>  
                    <span>
                            <label for="activation"> Activate / Deactivate </label>
                            <input type="checkbox" ' . ($snippet->is_active == 1 ? 'checked' : '') . ' class="snippet-activate-switch" data-snippet_id="' . $snippet->id . '"> 
                    </span> 
                </td>
                <td><a href="'. $snippet_edit_url .'"> ' . $snippet->name . '</a></td>
                <td>' . $snippet->type . '</td>
            </tr>
        ';
    }

    $html_output = '
    <div class="aveo-custom-code-wrap">
        <h2>All Snippets</h2>
        <table class="wp-list-table">
            <thead>
                <tr>
                    <th>Snippet Name</th>
                    <th>type</th>
                </tr>
            </thead>
            <tbody>
                ' . $table_row_html . '
            </tbody>
        </table>
    </div>';

    echo $html_output;
}