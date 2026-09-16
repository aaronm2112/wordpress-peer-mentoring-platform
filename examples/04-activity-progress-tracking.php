<?php
// Demonstrates: idempotent start/complete tracking so re-loading an activity never creates duplicate open events.

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_peer_load_activity', 'peer_load_activity');

function peer_load_activity() {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!wp_verify_nonce($nonce, 'peer_activity_action')) {
        wp_send_json_error(array('message' => __('Your session has expired. Refresh and try again.', 'peer-mentoring-showcase')), 403);
    }

    $user_id = get_current_user_id();
    $activity_id = isset($_POST['activity_id']) ? absint($_POST['activity_id']) : 0;
    $guide_id = absint(get_post_meta($activity_id, 'guide_id', true));

    if (!$user_id || !$activity_id || !$guide_id) {
        wp_send_json_error(array('message' => __('This activity is unavailable.', 'peer-mentoring-showcase')), 404);
    }

    peer_transition_activity($user_id, $guide_id, $activity_id);

    // Some embedded content (forms, third-party widgets) only initializes correctly on a full page load.
    $requires_full_render = (bool) get_post_meta($activity_id, 'requires_full_render', true);

    wp_send_json_success(array(
        'activity_id' => $activity_id,
        'requires_full_render' => $requires_full_render,
        'full_render_url' => $requires_full_render ? get_permalink($activity_id) : '',
    ));
}

function peer_transition_activity($user_id, $guide_id, $activity_id) {
    $current_key = 'current_activity_' . $guide_id;
    $previous_activity_id = absint(get_user_meta($user_id, $current_key, true));

    if ($previous_activity_id && $previous_activity_id !== $activity_id) {
        peer_complete_open_event($user_id, $guide_id, $previous_activity_id);
    }

    update_user_meta($user_id, $current_key, $activity_id);
    peer_start_event_once($user_id, $guide_id, $activity_id);
}

// Checks for an existing open event before inserting, so repeated navigation calls stay idempotent.
function peer_start_event_once($user_id, $guide_id, $activity_id) {
    $existing = peer_find_open_event($user_id, $guide_id, $activity_id);

    if ($existing) {
        return $existing;
    }

    return wp_insert_post(array(
        'post_type' => 'peer_progress_event',
        'post_status' => 'private',
        'post_title' => __('Activity started', 'peer-mentoring-showcase'),
        'meta_input' => array(
            'user_id' => $user_id,
            'guide_id' => $guide_id,
            'activity_id' => $activity_id,
            'event_status' => 'open',
            'started_at' => current_time('mysql', true),
        ),
    ));
}

function peer_complete_open_event($user_id, $guide_id, $activity_id) {
    $event = peer_find_open_event($user_id, $guide_id, $activity_id);

    if (!$event) {
        return false;
    }

    update_post_meta($event->ID, 'event_status', 'completed');
    return update_post_meta($event->ID, 'completed_at', current_time('mysql', true));
}

function peer_find_open_event($user_id, $guide_id, $activity_id) {
    $events = get_posts(array(
        'post_type' => 'peer_progress_event',
        'post_status' => 'private',
        'posts_per_page' => 1,
        'meta_query' => array(
            array('key' => 'user_id', 'value' => $user_id),
            array('key' => 'guide_id', 'value' => $guide_id),
            array('key' => 'activity_id', 'value' => $activity_id),
            array('key' => 'event_status', 'value' => 'open'),
        ),
    ));

    return $events ? $events[0] : null;
}
