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
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            file varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Aditionel code to first time setup...
    }
}



// Hook for adding admin menus
add_action('admin_menu', 'aveo_custom_code_menu');

// Adjusted function to include submenu registration
function aveo_custom_code_menu() {
    // Main menu
    add_menu_page(
        'All snippets', // Page title
        'Aveo Custom Code', // Menu title
        'manage_options', // Capability
        'aveo-custom-code', // Menu slug
        'aveo_custom_code_front_page', // Function to display the menu page
        'dashicons-editor-code', // Icon URL
        4 // Position
    );

    // Renaming the main menu
    add_submenu_page(
        'aveo-custom-code', // Parent slug
        'All Snippets', // Page title (used for the browser title when this page is opened)
        'All Snippets', // Menu title (name of the submenu item)
        'manage_options', // Capability
        'aveo-custom-code', // Menu slug (should match the slug of the main menu page to replace it)
        'aveo_custom_code_front_page' // Function to display the menu page
    );

    // Submenu for creating a snippet
    $create_snippet_hook_suffix = add_submenu_page(
        'aveo-custom-code',
        'Create new',
        'Create new',
        'manage_options',
        'aveo-custom-code-create-snippet',
        'aveo_custom_code_create_snippet_page'
    );

    // Enqueue script for initializing the CodeMirror editor. This script will only be loaded on the create snippet page. It uses the $create_snippet_hook_suffix variable to check if it should be loaded.
    add_action('admin_enqueue_scripts', function ($hook) use ($create_snippet_hook_suffix) {
        if ($hook !== $create_snippet_hook_suffix) {
            return;
        }
    
        // Enqueue the front_page.js file
        wp_enqueue_script('aveo-custom-code-create-snippets-page', plugin_dir_url(__FILE__) . 'menu_pages/create_snippets_page/code_mirror_innit.js', array('jquery'), '1.0', true);
    
        // Prepare CodeMirror for code editing, assuming PHP for this example
        $language_type = 'text/x-php'; // This could be dynamic
        $settings = wp_enqueue_code_editor(array('type' => $language_type));

        // Now, adjust settings based on the language type
        if (is_array($settings) && isset($settings['codemirror'])) {
            switch ($language_type) {
                case 'text/x-php':
                    $settings['codemirror'] = array_merge(
                        $settings['codemirror'],
                        array(
                            'mode' => $language_type,
                            'autoCloseBrackets' => true,
                            'autoCloseTags' => true,
                            'matchBrackets' => true,
                            'matchTags' => array('bothTags' => true),
                            'extraKeys'        => array(
                                'Alt-Space' => 'autocomplete',
                                'Ctrl-/'     => 'toggleComment',
                                'Cmd-/'      => 'toggleComment',
                                'Alt-F'      => 'findPersistent',
                                'Ctrl-F'     => 'findPersistent',
                                'Cmd-F'      => 'findPersistent',
                            ),
                            'gutters' => array('CodeMirror-lint-markers'),
                            'indentWithTabs' => true,
                            'lineWrapping' => true,
                            'highlightSelectionMatches' => array('showToken' => '/\w/', 'annotateScrollbar' => true)
                        )
                    );
                    break;
                // Add cases for other languages as needed
                case 'text/css':
                    $settings['codemirror'] = array_merge(
                        $settings['codemirror'],
                        array(
                            'mode' => $language_type,
                            // Other CSS-specific settings here
                        )
                    );
                    break;
                // Default case if needed
                default:
                    // Default settings or log an error
                    break;
            }
        }

        if (false !== $settings) {
            wp_localize_script('aveo-custom-code-create-snippets-page', 'cm_settings', array('codeEditor' => $settings));
        }
    
        // Enqueue CodeMirror scripts and styles
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
    });

    // Submenu for editing a snippet
    add_submenu_page(
        'aveo-custom-code',
        'Edit Snippet',
        'Edit Snippet',
        'manage_options',
        'aveo-custom-code-edit-snippet',
        'aveo_custom_code_edit_snippet_page'
    );

    // Submenu for importing snippets
    add_submenu_page(
        'aveo-custom-code',
        'Import Snippet',
        'Import Snippet',
        'manage_options',
        'aveo-custom-code-import',
        'aveo_custom_code_import_page'
    );
}

// Include the menu page manager file
require_once plugin_dir_path(__FILE__) . 'menu_pages/menu_page_manager.php';



// function for handling the form submission
function aveo_process_snippet_submission() {
    global $wpdb; // Global WordPress database class

    // Check if our form is submitted, nonce is valid, and the user has appropriate capability
    if (isset($_POST['aveo_submit_snippet'], $_POST['aveo_code_editor'], $_POST['aveo_custom_code_nonce']) && wp_verify_nonce($_POST['aveo_custom_code_nonce'], 'aveo_custom_code_action')) {
        if (current_user_can('manage_options')) {
            $snippet_code = stripslashes($_POST['aveo_code_editor']);

            // Sanitize the input
            $snippet_code_sanitized = sanitize_textarea_field($snippet_code);
            $snippet_name_sanitized = sanitize_text_field($_POST['aveo_snippet_name']);

            // Define the base directory for the snippets
            $snippets_dir = plugin_dir_path(__FILE__) . 'aveo-stored-snippets/';

            // Check if the directory exists, and if not, create it
            if (!file_exists($snippets_dir)) {
                wp_mkdir_p($snippets_dir); // WordPress function to create a directory if it doesn't exist
            }

            // Now adjust the file path to include this directory
            $file = $snippets_dir . $snippet_name_sanitized . '.php';
    
            // Check if file already exists to avoid unnecessary operations
            if (!file_exists($file)) {
                $handle = fopen($file, 'w') or die('Cannot open file:  '.$file); // implicitly creates file

                // Define the content of your PHP file
                $content = "<?php\n" . $snippet_code_sanitized;
                
                fwrite($handle, $content);
                fclose($handle);
            }

            // Prepare data for insertion
            $data = array(
                'name' => $snippet_name_sanitized,
                'code' => $snippet_code_sanitized,
                'type' => 'php', // Assuming you're storing PHP snippets. Adjust as necessary.
                'is_active' => isset($_POST['aveo_snippet_active']) ? 1 : 0, // Check if the snippet should be active or not
                'file' => $file,
            );

            // Define data format for insertion
            $format = array('%s', '%s', '%s', '%d', '%s');

            // Insert data into the database
            $wpdb->insert(
                $wpdb->prefix . 'aveo_custom_code', // Table name
                $data,
                $format
            );

            // After processing, redirect to avoid form resubmission issues
            wp_redirect(add_query_arg('aveo_action', 'snippet_saved', admin_url('admin.php?page=aveo-custom-code-create-snippet')));
            exit;
        }
    }
}
add_action('admin_init', 'aveo_process_snippet_submission');


// Function to run snippets saved in the database
function aveo_execute_custom_php_snippets() {
    if (!current_user_can('manage_options')) return; // Security check for admin-only execution

    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';
    $php_snippets = $wpdb->get_results("SELECT * FROM {$table_name} WHERE type = 'php'", OBJECT);

    foreach ($php_snippets as $snippet) {
        // Check if the snippet is active
        if ($snippet->is_active == 1) {
            // Include the file
            include $snippet->file;
        }
    }
}
add_action('init', 'aveo_execute_custom_php_snippets', 1);









