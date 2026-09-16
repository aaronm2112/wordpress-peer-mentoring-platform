<?php
// Demonstrates: a nonce-verified AJAX endpoint gated by capability and a facilitator/participant relationship check.

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_peer_create_session', 'peer_create_session');

function peer_create_session() {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!wp_verify_nonce($nonce, 'peer_session_action')) {
        wp_send_json_error(array('message' => __('Your session has expired. Refresh and try again.', 'peer-mentoring-showcase')), 403);
    }

    $facilitator_id = get_current_user_id();
    $participant_id = isset($_POST['participant_id']) ? absint($_POST['participant_id']) : 0;
    $guide_id = isset($_POST['guide_id']) ? absint($_POST['guide_id']) : 0;

    if (!$facilitator_id || !current_user_can('manage_peer_sessions')) {
        wp_send_json_error(array('message' => __('You are not allowed to create sessions.', 'peer-mentoring-showcase')), 403);
    }

    if (!$participant_id || !get_userdata($participant_id)) {
        wp_send_json_error(array('message' => __('Choose a valid participant.', 'peer-mentoring-showcase')), 400);
    }

    if (!$guide_id || get_post_type($guide_id) !== 'peer_guide') {
        wp_send_json_error(array('message' => __('Choose a valid guide.', 'peer-mentoring-showcase')), 400);
    }

    if (!peer_facilitator_is_assigned($facilitator_id, $participant_id)) {
        wp_send_json_error(array('message' => __('This participant is not in your assigned group.', 'peer-mentoring-showcase')), 403);
    }

    $session_id = wp_insert_post(array(
        'post_type' => 'peer_session',
        'post_status' => 'private',
        'post_title' => sprintf(__('Session: %s', 'peer-mentoring-showcase'), current_time('mysql')),
        'meta_input' => array(
            'facilitator_id' => $facilitator_id,
            'participant_id' => $participant_id,
            'guide_id' => $guide_id,
            'status' => 'scheduled',
        ),
    ), true);

    if (is_wp_error($session_id)) {
        wp_send_json_error(array('message' => __('The session could not be created.', 'peer-mentoring-showcase')), 500);
    }

    wp_send_json_success(array('session_id' => $session_id, 'status' => 'scheduled'), 201);
}

// Relationship data is stored as user meta here; a real deployment might use a join table instead.
function peer_facilitator_is_assigned($facilitator_id, $participant_id) {
    $assigned = array_map('absint', (array) get_user_meta($facilitator_id, 'assigned_participants', true));

    return in_array((int) $participant_id, $assigned, true);
}
