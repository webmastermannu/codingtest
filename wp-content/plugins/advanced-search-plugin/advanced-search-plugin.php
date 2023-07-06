<?php
/*
Plugin Name: Advance Post Search System
Plugin URI: <your-plugin-uri>
Description: Extends the default WordPress search system with advanced search capabilities.
Version: 1.0.0
Author: <your-name>
Author URI: <your-author-uri>
*/

function advanced_search_query($search, $wp_query) {
    if (!is_admin() && $wp_query->is_main_query()) {
        // Check if 's' parameter is present and starts with 'adv' to initiate advanced search
        $search_string = $wp_query->query_vars['s'];
        if (strpos($search_string, 'adv') === 0) {
            $wp_query->set('s', ''); // Clear the default search parameter

            // Get advanced search parameters from the query string
            $title = isset($_GET['title']) ? sanitize_text_field($_GET['title']) : '';
            $author = isset($_GET['author']) ? sanitize_text_field($_GET['author']) : '';
            $meta_fields = array();

            // Loop through query parameters to extract meta fields and values
            foreach ($_GET as $param_key => $param_value) {
                if (strpos($param_key, '_meta_') === 0) {
                    $meta_field_key = sanitize_key(substr($param_key, 6));
                    $meta_fields[$meta_field_key] = sanitize_text_field($param_value);
                }
            }

            // Build the advanced search query
            $meta_query = array('relation' => 'AND');
            if (!empty($title)) {
                $meta_query[] = array(
                    'key' => 'title',
                    'value' => $title,
                    'compare' => 'LIKE',
                );
            }
            if (!empty($author)) {
                $meta_query[] = array(
                    'key' => 'author',
                    'value' => $author,
                    'compare' => 'LIKE',
                );
            }
            foreach ($meta_fields as $meta_key => $meta_value) {
                $meta_query[] = array(
                    'key' => $meta_key,
                    'value' => $meta_value,
                    'compare' => 'LIKE',
                );
            }

            $wp_query->set('meta_query', $meta_query);
        }
    }
    return $search;
}

add_filter('posts_search', 'advanced_search_query', 10, 2);
?>