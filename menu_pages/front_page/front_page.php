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
    
    // Display page content
    echo '<div class="aveo-custom-code-wrap">';
    echo '<h1>Aveo Custom Code</h1>';
    
    // Foreach loop to display all snippets
    echo '<div class="aveo-snippet-list">';
    echo '<h2>All Snippets</h2>';
    echo '<table class="wp-list-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<input type="checkbox" id="select-all-snippets">';
    echo '<th>Snippet Name</th>';
    echo '<th>type</th>';
    echo '</tr>';

    // Get all snippets from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';
    $snippets = $wpdb->get_results("SELECT * FROM $table_name");
    

    echo '<tbody>';

    foreach ($snippets as $snippet) {

        $snippet_edit_url = admin_url('admin.php?page=aveo-custom-code-edit-snippet&snippet_id=' . $snippet->id);

        echo '<tr>';
        echo '<td><input type="checkbox" class="snippet-checkbox" data-snippet_id="' . $snippet->id . '"></td>';
        echo '<td>  <span>
                        <label for="activation"> Activate / Deactivate </label>
                        <input type="checkbox" ' . ($snippet->is_active == 1 ? 'checked' : '') . ' class="snippet-activate-switch" data-snippet_id="' . $snippet->id . '"> 
                    </span> 
                </td>';
        echo '<td><a href="'. $snippet_edit_url .'"> ' . $snippet->name . '</a></td>';
        echo '<td>' . $snippet->type . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}