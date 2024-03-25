<?php
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
        echo '<tr>';
        echo '<td><input type="checkbox" class="snippet-checkbox" data-id="' . $snippet->id . '"></td>';
        echo '<td>' . $snippet->name . '</td>';
        echo '<td>' . $snippet->type . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}