<?php

// include the JS and extra functions files
include 'extra-functions.php';

// enqueue the scripts
function aveo_frontpage_code_enqueue_scripts() {
    // Enqueue the front_page.js file
    wp_enqueue_script('aveo-custom-code-front-page', plugin_dir_url(__FILE__) . 'front_page.js', array('jquery'), '1.0', true);
    // Enqueue the front_page.css file
    wp_enqueue_style('aveo-custom-code-front-page', plugin_dir_url(__FILE__) . 'front_page.css');
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

        $current_date = date('Y-m-d');
        $formatted_modified = date('Y-m-d', strtotime($snippet->modified));

        // Calculate days since last modified
        $date1 = new DateTime($formatted_modified);
        $date2 = new DateTime($current_date);
        $diff = $date1->diff($date2);
        $days_since_modified = $diff->days;

        if ($days_since_modified == 0) {
            $days_since_modified = 'Today';
        } else if ($days_since_modified == 1) {
            $days_since_modified = 'Yesterday';
        } else {
            $days_since_modified = $days_since_modified . ' days ago';
        }

        $table_row_html .= '
            <tr class="aveo-single-snippet" data-snippet_type="'. $snippet->type .'" data-snippet_id="' . $snippet->id . '">
                <td><input type="checkbox" class="snippet-checkbox" data-snippet_id="' . $snippet->id . '" data-snippet_type="'. $snippet->type .'"></td>
                <td>  
                    <span class="snippet-activate-switch-con">
                        <input id="activation-'. $snippet->id .'" type="checkbox" ' . ($snippet->is_active == 1 ? 'checked' : '') . ' class="snippet-activate-switch" data-snippet_id="' . $snippet->id . '">
                        <label for="activation-'. $snippet->id .'" class="switch">
                            <span class="slider round"></span>
                        </label>
                    </span> 
                </td>
                <td class="snippet-name-td">
                    <a class="snippet-name" href="'. $snippet_edit_url .'"> ' . $snippet->name . '</a>
                    <div class="snippet-actions">
                        <a href="' . $snippet_edit_url . '">Edit</a>
                        <a href="#" class="clone-snippet" data-snippet_id="' . $snippet->id . '">Clone</a>
                        <a href="#" class="export-snippet" data-id="' . $snippet->id . '" data-type="'. $snippet->type .'">Export</a>
                        <a href="#" class="delete-snippet" data-snippet_id="' . $snippet->id . '">Delete</a>
                    </div>
                </td>
                <td>' . $snippet->type . '</td>
                <td>' . $snippet->description . '</td>
                <td>' . $days_since_modified . '</td>
                <td>' . $snippet->priority . '</td>
            </tr>
        ';
    }

    $html_output = '
    <div class="aveo-custom-code-wrap">

        <div>
            <h1>Aveo custom code</h1>
            <p>Create a new code snippet, or manually import one.</p>
            <div class="new-snippet-button-wrap">
                <a href="' . admin_url('admin.php?page=aveo-custom-code-create-snippet') . '" class="create-snippet-button">Create</a>
                <div class="import-snippet-button">Import</div>
            </div>
        </div>

        <div class="import-snippet-animate">
            <div class="import-snippet-wrap">
                <p>Upload from this device</p>
                <div class="import-snippet-content">
                    <div class="import-snippet-file-wrap">
                        <input type="file" id="import-snippet-file">
                        <label for="import-snippet-file" id="file-input-label">
                            <div class="file-input-content">
                                <span class="file-input-plus">+</span>
                                <span class="file-input-text">Click or drag file here</span>
                            </div>
                        </label>
                    </div>
                    <div class="aveo-custom-code-snippet-condition-wrap">
                        <div>
                            <label for="Snippet type">Document Type</label>
                            <select class="aveo_snippet_type" name="aveo_snippet_type">
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
                </div>
            </div>
        </div>

        <div class="snippet-type-filter-wrap">
            <div data-category="all_snippets" class="all-snippets active">All your snippets</div>
            <div data-category="php" class="php-snippets">PHP <img class="snippet-category-img" src="' . plugins_url('../../img/php.svg', __FILE__) . '"/></div>
            <div data-category="js" class="js-snippets">JavaScript <img class="snippet-category-img" src="' . plugins_url('../../img/js.svg', __FILE__) . '"/></div>
            <div data-category="css" class="css-snippets">CSS <img class="snippet-category-img" src="' . plugins_url('../../img/css.svg', __FILE__) . '"/></div>
            <div data-category="premade_snippets" class="premade-snippets">Premade snippets</div>
            <span class="num-of-snippets">' . count($snippets) . ' items</span>
        </div>

        <table class="wp-list-table snippet-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all-snippets"></th>
                    <th></th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Modified</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                ' . $table_row_html . '
            </tbody>
        </table>
    </div>';

    echo $html_output;
}