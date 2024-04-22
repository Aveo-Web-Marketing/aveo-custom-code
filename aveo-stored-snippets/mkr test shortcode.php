<?php 
function mkr_test_shortcode() {
    $current_page_id = get_the_ID();  // Fetches the current post/page ID
    if ($current_page_id) {
        return "Current Page ID is: " . $current_page_id;
    } else {
        return "No page ID found";
    }
}

add_shortcode('mkr_test', 'mkr_test_shortcode');