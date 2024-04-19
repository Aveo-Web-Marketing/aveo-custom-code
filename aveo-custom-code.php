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

// Register the activation hook - whichs runs the function for the first time setup
register_activation_hook( __FILE__, 'aveo_custom_code_install' );

// Function to setup the plugin for the first time - including the sql table
function aveo_custom_code_install() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'aveo_custom_code';

    // Check if the table already exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text NOT NULL,
            code text NOT NULL,
            type varchar(100) NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            file varchar(255) NOT NULL,
            display_condition varchar(255) NOT NULL,
            specific_page_condition JSON NOT NULL,
            modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            priority int(11) NOT NULL DEFAULT 10,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Aditionel code to first time setup...
    }
}

// Action to include the custom code menu - main and submenus below
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

    // Submenu for editing a snippet
    if (isset($_REQUEST['page']) && $_REQUEST['page'] === 'aveo-custom-code-edit-snippet') {
        $edit_snippet_hook_suffix = add_submenu_page(
            'aveo-custom-code',
            'Edit Snippet',
            'Edit Snippet',
            'manage_options',
            'aveo-custom-code-edit-snippet',
            'aveo_custom_code_edit_snippet_page'
        );
    }

    // Enqueue script for initializing the CodeMirror editor. This script will only be loaded on the create- and edit snippet page. It uses the $create_snippet_hook_suffix variable to check if it should be loaded.
    add_action('admin_enqueue_scripts', function ($hook) {
        
        global $wpdb;

        $pages = array(
            'aveo-custom-code-create-snippet',
            'aveo-custom-code-edit-snippet',
        );

        $currentPage = $_GET['page'] ?? '';

        if (!in_array($currentPage, $pages)) {
            return;
        }
    
        $snippet_id = isset($_GET['snippet_id']) ? intval($_GET['snippet_id']) : 0;

        $language_type = 'text/x-php'; // A default value in case no specific type is found

        wp_enqueue_script('aveo-custom-code-create-snippets-page', plugin_dir_url(__FILE__) . 'menu_pages/create_snippets_page/code_mirror_innit.js', array('jquery'), '1.0', true);
    
        if ($snippet_id > 0) {
            // Fetch the language type directly based on the snippet ID
            $table_name = $wpdb->prefix . 'aveo_custom_code';
            $query = $wpdb->prepare("SELECT type FROM $table_name WHERE id = %d", $snippet_id);
            $snippet_type = $wpdb->get_var($query);
    
            // Map the snippet_type to the corresponding CodeMirror MIME type
            if (!is_null($snippet_type)) {
                switch ($snippet_type) {
                    case 'php':
                        $language_type = 'text/x-php'; 
                        break;
                    case 'css':
                        $language_type = 'text/css'; 
                        break;
                    case 'js':
                        $language_type = 'text/javascript'; 
                        break;
                    // Add other cases as needed
                }
            }
        }

        $settings = wp_enqueue_code_editor(array('type' => $language_type));

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

}


// Include the menu page manager file
require_once plugin_dir_path(__FILE__) . 'menu_pages/menu_page_manager.php';


// function for handling the form submission
function aveo_process_snippet_submission() {
    global $wpdb; // Global WordPress database class

    // Check if our form is submitted, nonce is valid, and the user has appropriate capability
    // $_POST['aveo_code_editor'] - saved for later use
    if (isset($_POST['aveo_submit_snippet'], $_POST['aveo_custom_code_nonce']) && wp_verify_nonce($_POST['aveo_custom_code_nonce'], 'aveo_custom_code_action')) {
        if (current_user_can('manage_options')) {
            $snippet_id = isset($_POST['snippet_id']) ? intval($_POST['snippet_id']) : 0;
            $snippet_name = sanitize_text_field($_POST['aveo_snippet_name']);

            // Decode entities for the code editor content
            $snippet_code_raw = stripslashes($_POST['aveo_code_editor']);
            $snippet_code = html_entity_decode($snippet_code_raw, ENT_QUOTES | ENT_HTML5);

            if (isset($_FILES['import-snippet-file'])) {
                $uploaded_file = $_FILES['import-snippet-file'];
                error_log('File Upload Attempt: ' . print_r($uploaded_file, true));
                
                if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
                    error_log('File Upload Error: ' . $uploaded_file['error']);
                }
            }

            $snippet_description = sanitize_textarea_field($_POST['aveo_snippet_description']);
            $is_active = isset($_POST['aveo_snippet_active']) ? 1 : 0;
            $document_type = isset($_POST['aveo_snippet_type']) ? $_POST['aveo_snippet_type'] : null;
            $current_document_type = $snippet_id > 0 ? $wpdb->get_var($wpdb->prepare("SELECT type FROM {$wpdb->prefix}aveo_custom_code WHERE id = %d", $snippet_id)) : '';
            $display_condition = isset($_POST['aveo_snippet_condition']) ? $_POST['aveo_snippet_condition'] : null;
            $modified = current_time('mysql');
            $priority = isset($_POST['aveo_snippet_priority']) ? intval($_POST['aveo_snippet_priority']) : 10;

            // Check if snippet name already exists in the database (exclude current snippet if updating)
            $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}aveo_custom_code WHERE name = %s AND id != %d", $snippet_name, $snippet_id);
            $existing_id = $wpdb->get_var($query);

            // HERE THERE SHOULD BE A CONDITION TO CHECK IF THE SNIPPET IS BEING EDITED OR CREATED. IF EDITED, IT SHOULD NOT CHECK FOR EXISTING ID AND EXIT; BEUCAUSE THE SNIPPET NAME WILL BE THE SAME AS THE CURRENT SNIPPET NAME.
            if ($existing_id) {
                
                set_transient('aveo_snippet_error_message', 'Snippet name already exists. Please choose a different name.', 30); // 30 seconds 

                $redirect_url = add_query_arg([
                    'page' => 'aveo-custom-code-create-snippet',
                ], admin_url('admin.php'));

                wp_redirect($redirect_url);
                echo '<div class="notice notice-error is-dismissible"><p>Snippet name already exists. Please choose a different name.</p></div>';
                exit;
            }

            // Check if the code is imported from a file, and if so, use the contet of the file as the snippet code, and the file name as the snippet name
            if ($uploaded_file['error'] === 0) {
                $file_name = $uploaded_file['name'];
                $file_path = $uploaded_file['tmp_name'];
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

                if ($file_extension === 'php' || $file_extension === 'js' || $file_extension === 'css') {
                    $snippet_code = file_get_contents($file_path);
                    $snippet_name = pathinfo($file_name, PATHINFO_FILENAME);
                    // $document_type = $file_extension; - Can be added if needed
                } else {
                    set_transient('aveo_snippet_error_message', 'Invalid file type. Please upload a PHP, JS, or CSS file.', 30); // 30 seconds expiration

                    $redirect_url = add_query_arg([
                        'page' => 'aveo-custom-code-create-snippet',
                    ], admin_url('admin.php'));

                    wp_redirect($redirect_url);
                    echo '<div class="notice notice-error is-dismissible"><p>Invalid file type. Please upload a PHP, JS, or CSS file.</p></div>';
                    exit;
                }
            }

            $current_file_path = '';
            if ($snippet_id > 0) {
                $current_file_path = $wpdb->get_var($wpdb->prepare("SELECT file FROM {$wpdb->prefix}aveo_custom_code WHERE id = %d", $snippet_id));
            }

            // Proceed with file creation and database insertion/update
            $snippets_dir = plugin_dir_path(__FILE__) . 'aveo-stored-snippets/';
            if (!file_exists($snippets_dir)) {
                wp_mkdir_p($snippets_dir);
            }

            $new_file_path = $snippets_dir . $snippet_name . '.' . $document_type;

            // Determine if renaming is necessary
            if (!empty($current_file_path) && $current_file_path !== $new_file_path) {
                // Attempt to rename the file, check if old file exists
                if (file_exists($current_file_path)) {
                    rename($current_file_path, $new_file_path);
                }
            } else {
                // Create or overwrite the file with new content if not a rename operation

                // Check if the document type is PHP
                if ($document_type === 'php') {
                    // Trim whitespace and check if the code does not start with <?php
                    if (strpos(trim($snippet_code), '<?php') !== 0) {
                        // If it doesn't, prepend <?php to the code
                        $snippet_code = '<?php ' . "\n" . $snippet_code;
                    }
                }

                file_put_contents($new_file_path, $snippet_code);
            }

            $data = [
                'name' => $snippet_name,
                'code' => $snippet_code,
                'type' => $document_type,
                'is_active' => $is_active,
                'file' => $file_path,
                'description' => $snippet_description,
                'display_condition' => $display_condition,
                'modified' => $modified,
                'priority' => $priority
            ];

            $data['file'] = $new_file_path;

            if ($snippet_id > 0) {
                // Update existing snippet
                $wpdb->update("{$wpdb->prefix}aveo_custom_code", $data, ['id' => $snippet_id]);
            } else {
                // Insert new snippet
                $wpdb->insert("{$wpdb->prefix}aveo_custom_code", $data);
                $snippet_id = $wpdb->insert_id;
            }

            set_transient('aveo_snippet_success_message', 'Snippet successfully saved and activated.', 30); // 30 seconds expiration

            // Redirect after successful insert/update
            wp_redirect(add_query_arg([
                'page' => 'aveo-custom-code-edit-snippet',
                'snippet_id' => $snippet_id,
                'aveo_action' => 'snippet_saved'
            ], admin_url('admin.php')));
            exit;
        }
    }
}
add_action('admin_init', 'aveo_process_snippet_submission');


// Function to execute the custom code snippets
function aveo_execute_custom_code_snippets() {
    if (!current_user_can('manage_options')) return; // Security check for admin-only execution

    global $wpdb;
    $table_name = $wpdb->prefix . 'aveo_custom_code';
    $all_snippets = $wpdb->get_results("SELECT * FROM {$table_name} WHERE is_active = 1", OBJECT);

    foreach ($all_snippets as $snippet) {
        $should_execute = false;

        // Determine where to run the snippet based on display_condition and type
        switch ($snippet->type) {
            case 'php':
                if ($snippet->display_condition === 'everywhere' ||
                    ($snippet->display_condition === 'only_frontend' && !is_admin()) ||
                    ($snippet->display_condition === 'only_backend' && is_admin())) {
                    $should_execute = true;
                }
                break;
            case 'js':
                if (($snippet->display_condition === 'header' || $snippet->display_condition === 'body_end') &&
                    !is_admin()) { // JS and CSS are typically only included on the front end
                    $should_execute = true;
                }
                break;
            case 'css':
                if ($snippet->display_condition === 'everywhere' ||
                    ($snippet->display_condition === 'only_frontend' && !is_admin()) ||
                    ($snippet->display_condition === 'only_backend' && is_admin())) {
                    $should_execute = true;
                }
                break;
        }

        if ($should_execute) {
            switch ($snippet->type) {
                
                case 'php':
                    include $snippet->file; // Execute PHP code
                    break;
                case 'js':
                    // Enqueue JavaScript
                    add_action('wp_head', function() use ($snippet) {
                        if ($snippet->display_condition === 'header') {
                            echo "<script>" . file_get_contents($snippet->file) . "</script>";
                        }
                    });
                    add_action('wp_footer', function() use ($snippet) {
                        if ($snippet->display_condition === 'body_end') {
                            echo "<script>" . file_get_contents($snippet->file) . "</script>";
                        }
                    });
                    break;
                case 'css':
                    // Enqueue CSS
                    add_action('wp_head', function() use ($snippet) {
                        echo "<style>" . file_get_contents($snippet->file) . "</style>";
                    });
                    break;
            }
        }
    }
}
add_action('init', 'aveo_execute_custom_code_snippets', 1);
