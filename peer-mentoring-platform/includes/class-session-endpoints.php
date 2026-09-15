<?php

if (!defined('ABSPATH')) {
    exit;
}

class PeerMentoringShowcaseSessionEndpoints {
    public static function init() {
        add_action('init', array(__CLASS__, 'register_session_type'));
        add_action('wp_ajax_peer_mentoring_create_session', array(__CLASS__, 'create_session'));
    }

    public static function register_session_type() {
        register_post_type('peer_session', array(
            'label' => __('Peer Sessions', 'peer-mentoring-platform-showcase'),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title', 'custom-fields'),
        ));
    }

    public static function create_session() {
        if (!self::request_has_valid_nonce()) {
            wp_send_json_error(array('message' => __('Your session has expired. Refresh and try again.', 'peer-mentoring-platform-showcase')), 403);
        }

        $facilitator_id = get_current_user_id();
        $participant_id = isset($_POST['participant_id']) ? absint($_POST['participant_id']) : 0;
        $duration = isset($_POST['duration']) ? absint($_POST['duration']) : 0;

        if (!$facilitator_id || !current_user_can('manage_peer_sessions')) {
            wp_send_json_error(array('message' => __('You are not allowed to create peer sessions.', 'peer-mentoring-platform-showcase')), 403);
        }

        if (!$participant_id || !get_userdata($participant_id)) {
            wp_send_json_error(array('message' => __('Choose a valid participant.', 'peer-mentoring-platform-showcase')), 400);
        }

        if ($duration < 15 || $duration > 180) {
            wp_send_json_error(array('message' => __('Choose a session length between 15 and 180 minutes.', 'peer-mentoring-platform-showcase')), 400);
        }

        if (!self::facilitator_can_support_participant($facilitator_id, $participant_id)) {
            wp_send_json_error(array('message' => __('This participant is outside your assigned group.', 'peer-mentoring-platform-showcase')), 403);
        }

        $session_id = wp_insert_post(array(
            'post_type' => 'peer_session',
            'post_status' => 'private',
            'post_title' => sprintf(__('Peer session: %1$s', 'peer-mentoring-platform-showcase'), current_time('mysql')),
            'meta_input' => array(
                'peer_facilitator_id' => $facilitator_id,
                'peer_participant_id' => $participant_id,
                'peer_session_duration' => $duration,
                'peer_session_status' => 'scheduled',
            ),
        ), true);

        if (is_wp_error($session_id)) {
            wp_send_json_error(array('message' => __('The session could not be created.', 'peer-mentoring-platform-showcase')), 500);
        }

        wp_send_json_success(array(
            'session_id' => $session_id,
            'status' => 'scheduled',
            'message' => __('Peer session created.', 'peer-mentoring-platform-showcase'),
        ), 201);
    }

    private static function request_has_valid_nonce() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        return (bool) wp_verify_nonce($nonce, 'peer_mentoring_session');
    }

    private static function facilitator_can_support_participant($facilitator_id, $participant_id) {
        $assigned_participants = (array) get_user_meta($facilitator_id, 'peer_assigned_participants', true);
        $is_assigned = in_array($participant_id, array_map('absint', $assigned_participants), true);

        return (bool) apply_filters(
            'peer_mentoring_showcase_facilitator_can_support',
            $is_assigned,
            $facilitator_id,
            $participant_id
        );
    }
}