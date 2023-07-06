<?php
/**
* Plugin Name: Event API
* Plugin URI: <your-plugin-uri>
* Description: Provides a simple CRUD API for managing events.
* Version: 1.0.0
* Author: <your-name>
* Author URI: <your-author-uri>
*/

add_action( 'rest_api_init', 'event_api_register_routes' );

function event_api_register_routes() {
    register_rest_route( 'event-api/v1', '/events', array(
        'methods' => 'POST',
        'callback' => 'event_api_create_event',
        'permission_callback' => 'event_api_check_permission',
    ) );

    register_rest_route( 'event-api/v1', '/events/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'event_api_update_event',
        'permission_callback' => 'event_api_check_permission',
    ) );

    register_rest_route( 'event-api/v1', '/events/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'event_api_delete_event',
        'permission_callback' => 'event_api_check_permission',
    ) );

    register_rest_route( 'event-api/v1', '/events', array(
        'methods' => 'GET',
        'callback' => 'event_api_list_events',
        'permission_callback' => 'event_api_check_permission',
    ) );
}

// Create Event
add_action('init', 'event_api_create_event');
function event_api_create_event() {
    if (isset($_GET['action']) && $_GET['action'] === 'create_event') {
        $title = sanitize_text_field($_POST['title']);
		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date = sanitize_text_field($_POST['end_date']);
		$description = sanitize_text_field($_POST['description']);
		$category = sanitize_text_field($_POST['category']);

		// Perform necessary validation and security checks

		// Create new event
		$event_id = wp_insert_post(array(
			'post_title' => $title,
			'post_type' => 'event',
			'post_status' => 'publish',
			'post_content' => $description
		));

		// Add custom fields for start date, end date, and category
		update_post_meta($event_id, 'start_date', $start_date);
		update_post_meta($event_id, 'end_date', $end_date);
		update_post_meta($event_id, 'category', $category);

		// Return success response
		wp_send_json_success('Event created successfully.');
    }
}

// Update Event
add_action('init', 'event_api_update_event');
function event_api_update_event() {
    if (isset($_GET['action']) && $_GET['action'] === 'update_event') {
		$event_id = intval($_POST['event_id']);

		// Retrieve existing event
		$event = get_post($event_id);

		// Check if event exists
		if (!$event) {
			// Return error response if event not found
			wp_send_json_error('Event not found.');
		}

		$title = sanitize_text_field($_POST['title']);
		$start_date = sanitize_text_field($_POST['start_date']);
		$end_date = sanitize_text_field($_POST['end_date']);
		$description = sanitize_text_field($_POST['description']);
		$category = sanitize_text_field($_POST['category']);

		// Perform necessary validation and security checks

		// Update event
		wp_update_post(array(
			'ID' => $event_id,
			'post_title' => $title,
			'post_content' => $description
		));

		// Update custom fields for start date, end date, and category
		update_post_meta($event_id, 'start_date', $start_date);
		update_post_meta($event_id, 'end_date', $end_date);
		update_post_meta($event_id, 'category', $category);

		// Return success response
		wp_send_json_success('Event updated successfully.');
    }
}

// List Events
add_action('init', 'event_api_list_events');
function event_api_list_events() {
    if (isset($_GET['action']) && $_GET['action'] === 'list_events') {
		$date = sanitize_text_field($_GET['date']);

		// Perform necessary validation and security checks

		// Query events based on the provided date
		$args = array(
			'post_type' => 'event',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'start_date',
					'value' => $date,
					'compare' => '='
				)
			)
		);

		$events = get_posts($args);

		// Format events data as needed
		$formatted_events = array();
		foreach ($events as $event) {
			$formatted_events[] = array(
				'id' => $event->ID,
				'title' => $event->post_title,
				'start_date' => get_post_meta($event->ID, 'start_date', true),
				'end_date' => get_post_meta($event->ID, 'end_date', true),
				'description' => $event->post_content,
				'category' => get_post_meta($event->ID, 'category', true)
			);
		}

		// Return events data
		wp_send_json_success($formatted_events);
    }
}

// Delete Event
add_action('init', 'event_api_delete_event');
function event_api_delete_event() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete_event') {
        $event_id = intval($_POST['event_id']);

		// Delete event
		$result = wp_delete_post($event_id, true);

		if ($result) {
			// Return success response
			wp_send_json_success('Event deleted successfully.');
		} else {
			// Return error response if event deletion failed
			wp_send_json_error('Failed to delete event.');
		}
    }
}

add_action('init', 'event_api_check_permission');
function event_api_check_permission() {
    return current_user_can( 'administrator' );
}
?>