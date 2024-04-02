<?php

// Function to display the import page
function aveo_custom_code_import_page() {

    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }
    
    

    $html_output = '
        <div class="aveo-custom-code-wrap">
            <h1>Import Snippets</h1>
            <form action="" method="post" id="aveo-custom-code-import-form" enctype="multipart/form-data">
                <input type="file" name="aveo_import_file" style="width:100%; margin-bottom:10px;">
                ' . wp_nonce_field('aveo_custom_code_action', 'aveo_custom_code_nonce', true, false) . '
                <input type="submit" name="aveo_submit_import" value="Import Snippets" class="button button-primary">
            </form>
        </div>
    ';

    echo $html_output;
}