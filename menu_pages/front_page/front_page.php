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
    // Your form or settings go here
    echo '</div>';
}