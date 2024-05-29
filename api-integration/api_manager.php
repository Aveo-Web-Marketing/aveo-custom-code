<?php

function callAPI($method, $url, $data) {
    $args = array(
        'body'        => json_encode($data),
        'headers'     => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'     => 60,
        'redirection' => 5,
        'blocking'    => true,
        'httpversion' => '1.0',
        'sslverify'   => true,
        'data_format' => 'body',
    );

    if ('POST' === $method) {
        $response = wp_remote_post($url, $args);
    } else {
        // Add other methods as needed
        $response = wp_remote_request($url, $args);
    }

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "Something went wrong: $error_message";
    }

    return wp_remote_retrieve_body($response);
}

// Fetch the current URL
function get_url() {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return $url;
}

function get_domain_as_folder_name($url) {
    // Parse the URL to get the host part
    $parsed_url = parse_url($url);
    $host = $parsed_url['host'] ?? ''; // Use null coalescence to avoid errors if host is not found

    // Replace dots with underscores to make it a valid folder name
    $folder_name = str_replace('.', '_', $host);

    // Further sanitize to remove any non-alphanumeric characters, if necessary
    $folder_name = preg_replace('/[^A-Za-z0-9_]/', '', $folder_name);

    return $folder_name;
}

function manage_snippet_api($snippet_name, $snippet_code, $document_type) {

    // Replace spaces with underscores in the snippet name
    $snippet_name = str_replace(' ', '_', $snippet_name);

    // Example of calling the API function
    $api_url = 'http://acc-api.aveo21.dk/index.php';

    // Current URL
    $current_url = get_url();
    $domain_folder_name = get_domain_as_folder_name($current_url);

    $data_array =  array(
        "folder_name" => $domain_folder_name,
        "file_name" => $snippet_name,
        "file_content" => $snippet_code,
        "file_type" => $document_type,
    );

    $response = callAPI('POST', $api_url, $data_array);
    return $response;
}


