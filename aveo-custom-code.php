<?php
/*
* Plugin Name: Aveo Custom Code
* Plugin URI: https://aveo.dk
* Description: Plugin som indeholder en kodeeditor til wordspress, hvori man kan skrive php, js og css kode.
* Version: 1.0
* Author: Aveo
* Update URI: https://aveo.dk/
* Text Domain: aveo-custom-code
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// Register the the sql table for the plugin on activation
register_activation_hook( __FILE__, 'aveo_custom_code_install' );

// Function to create the sql table for the plugin, which runs on activation
function aveo_custom_code_install() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'aveo_custom_code';

    // Check if the table already exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            code text NOT NULL,
            type varchar(100) NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Aditionel code to first time setup...
    }
}


// Include the menu page file
require_once plugin_dir_path(__FILE__) . 'menu_pages/menu_frontpage.php';

// Hook for adding admin menus
add_action('admin_menu', 'aveo_custom_code_menu');

// This function might now be inside your included file
function aveo_custom_code_menu() {
    add_menu_page(
        'Aveo Custom Code Settings', // Page title
        'Aveo Custom Code', // Menu title
        'manage_options', // Capability
        'aveo-custom-code', // Menu slug
        'aveo_custom_code_page', // Function to display the menu page
        'dashicons-editor-code', // Icon URL
        4 // Position
    );
}