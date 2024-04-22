<?php

// Function to display the create snippet page
function aveo_custom_code_edit_snippet_page() {

    global $wpdb;

    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if ($message = get_transient('aveo_snippet_success_message')) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        delete_transient('aveo_snippet_success_message');
    }

    // Get the snippet ID from the URL
    $snippet_id = isset($_GET['snippet_id']) ? intval($_GET['snippet_id']) : 0;

    $snippet_name = '';
    $snippet_code = '';
    $snippet_active = '';
    $snippet_description = '';

    // If a valid snippet ID is provided, attempt to retrieve the snippet from the database
    if ($snippet_id > 0) {
        $table_name = $wpdb->prefix . 'aveo_custom_code';
        $snippet = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $snippet_id));

        if ($snippet) {
            // Assign the retrieved values to variables
            $snippet_name = esc_attr($snippet->name); 
            $snippet_active = checked($snippet->is_active, 1, false);
            $snippet_description = esc_textarea($snippet->description);
            $snippet_type = esc_attr($snippet->type);
            $snippet_condition = esc_attr($snippet->display_condition);
            $snippet_priority = esc_attr($snippet->priority);
            $snippet_page_specific_condition = esc_attr($snippet->specific_page_condition);
            $snippet_code = $snippet->code;
            if (strpos($snippet_code, '<?php') === 0) {
                $snippet_code = substr($snippet_code, strlen('<?php'));
            }
            $snippet_code = trim($snippet_code);
            $snippet_code = esc_textarea($snippet_code);

            // query to get the id of all pages and posts
            $pages = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish'");
            $posts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'");
            $all_pages = array_merge($pages, $posts);

            $aveo_page_search_results = '';
            foreach ($all_pages as $page) {
                $aveo_page_search_results .= '<div style="display: none;" class="aveo-page-search-result" data-page_id="' . $page->ID . '">' . $page->post_title . '</div>';
            }

            $snippet_page_specific_condition_text = '';
            if (isset($snippet_page_specific_condition)) {
                // match the id from the $snippet_page_specific_condition with the id of the page
                $snippet_page_specific_condition_text = get_the_title($snippet_page_specific_condition);
            }

            $snippet_page_specific_condition_option = '';
            if (!isset($snippet_page_specific_condition)) {
                $snippet_page_specific_condition_option = '
                    <option value="all">All Pages</option>
                    <option value="specific">Specific Pages</option>
                ';
            } else {
                $snippet_page_specific_condition_option = '
                    <option value="specific">Specific Pages</option>
                    <option value="all">All Pages</option>
                ';
            }

            $snippet_type_option = '';
            if ($snippet_type == 'php') {
                $snippet_type_option = '
                    <option value="php">PHP</option>
                    <option value="css">CSS</option>
                    <option value="js">JavaScript</option>
                ';
            } elseif ($snippet_type == 'css') {
                $snippet_type_option = '
                    <option value="css">CSS</option>
                    <option value="php">PHP</option>
                    <option value="js">JavaScript</option>
                ';
            } elseif ($snippet_type == 'js') {
                $snippet_type_option = '
                    <option value="js">JavaScript</option>
                    <option value="php">PHP</option>
                    <option value="css">CSS</option>
                ';
            }

            $snippet_condition_option = '';
            if ($snippet_type == 'php' && $snippet_condition == 'everywhere') {
                $snippet_condition_option = '
                    <option value="everywhere">Everywhere</option>
                    <option value="only_frontend">Only in the Frontend</option>
                    <option value="only_backend">Only in the WP backend</option>
                ';
            } elseif ($snippet_type == 'php' && $snippet_condition == 'only_frontend') {
                $snippet_condition_option = '
                    <option value="only_frontend">Only in the Frontend</option>
                    <option value="everywhere">Everywhere</option>
                    <option value="only_backend">Only in the WP backend</option>
                ';
            } elseif ($snippet_type == 'php' && $snippet_condition == 'only_backend') {
                $snippet_condition_option = '
                    <option value="only_backend">Only in the WP backend</option>
                    <option value="everywhere">Everywhere</option>
                    <option value="only_frontend">Only in the Frontend</option>
                ';
            } elseif ($snippet_type == 'css' && $snippet_condition == 'everywhere') {
                $snippet_condition_option = '
                    <option value="everywhere">Everywhere</option>
                    <option value="only_frontend">Only in the Frontend</option>
                    <option value="only_backend">Only in the WP backend</option>
                ';
            } elseif ($snippet_type == 'css' && $snippet_condition == 'only_frontend') {
                $snippet_condition_option = '
                    <option value="only_frontend">Only in the Frontend</option>
                    <option value="everywhere">Everywhere</option>
                    <option value="only_backend">Only in the WP backend</option>
                ';
            } elseif ($snippet_type == 'css' && $snippet_condition == 'only_backend') {
                $snippet_condition_option = '
                    <option value="only_backend">Only in the WP backend</option>
                    <option value="everywhere">Everywhere</option>
                    <option value="only_frontend">Only in the Frontend</option>
                ';
            } elseif ($snippet_type == 'js' && $snippet_condition == 'header') {
                $snippet_condition_option = '
                    <option value="header">In the header</option>
                    <option value="body_end">In the body-end</option>

                ';
            }  elseif ($snippet_type == 'js' && $snippet_condition == 'body_end') {
                $snippet_condition_option = '
                    <option value="body_end">In the body-end</option>
                    <option value="header">In the header</option>
                ';
            }
        }
    }

    $html_output = '
        <div class="aveo-custom-code-wrap">
        <h1 class="custom-code-special-heading">Edit Snippet</h1>
            <form action="" method="post" id="aveo-custom-code-form">
                <div class="aveo-custom-code-snippet-info '. ($snippet_type === 'php' ?'code-editor-before' : '') .'">
                    <input type="hidden" name="snippet_id" value="' . $snippet_id . '">
                    <input type="text" name="aveo_snippet_name" value="' . $snippet_name . '" placeholder="Snippet Name">
                    <div>
                        <label class="snippet-discription-label" for="Snippet Description">Description</label>
                        <textarea name="aveo_snippet_description" placeholder="Write  the description of you custom code here.">'. $snippet_description .'</textarea>
                    </div>
                    <textarea id="aveo-code-editor" name="aveo_code_editor">' . $snippet_code . '</textarea>
                    ' . wp_nonce_field('aveo_custom_code_action', 'aveo_custom_code_nonce', true, false) . '
                </div>
                <div class="aveo-custom-code-snippet-condition-wrap">
                    <div>
                        <label for="Snippet type">Document Type</label>
                        <select class="aveo_snippet_type" name="aveo_snippet_type" data-current_type="'. $snippet_type .'">
                            ' . $snippet_type_option . '
                        </select>
                        <span>This should match the code you write</span>
                    </div>
                    <div>
                        <label for="Snippet Condition">Where to run code</label>
                        <select name="aveo_snippet_condition">
                            ' . $snippet_condition_option . '
                        </select>
                    </div>
                    <div class="snippet_page_specific_condition" style="' . ($snippet_type === 'php' ? 'display: none;' : '') . '">
                        <label for="Snippet page specific condition">Page Specific Condition</label>
                        <select name="aveo_snippet_page_specific_condition">
                            ' . $snippet_page_specific_condition_option . '
                        </select>
                        <div class="snippet_page_specific_condition_search" style="' . (!isset($snippet_page_specific_condition) ? 'display: none;' : '') . '">
                            <label for="Snippet page specific condition">Search for Page(s)</label>
                            <input class="snippet_page_specific_condition_search_input" type="text" name="aveo_snippet_page_specific_condition_search" placeholder="Search for pages" value="' .  $snippet_page_specific_condition_text . '">
                            <input type="hidden" name="selected_con_id" id="selected_con_id" value="'. $snippet_page_specific_condition .'">
                            <div class="aveo-page-search-results">
                                ' . $aveo_page_search_results . '
                            </div>
                            <div class="empty-text" style="display: none;">No pages found.</div>
                        </div>
                    </div>
                    <div>
                        <label for="aveo_snippet_priority">Snippet Priority</label>
                        <input type="number" name="aveo_snippet_priority" value="'. $snippet_priority .'">
                    </div>
                    <div>
                        <label for="Snippet activation">Activate snippet on save</label>
                        <input type="checkbox" name="aveo_snippet_active" value="1" ' . $snippet_active . '>
                    </div>
                    <input type="submit" name="aveo_submit_snippet" value="Save Snippet" class="button button-primary">
                </div>
            </form>
        </div>
    ';

    echo $html_output;
}




