<?php

if (!defined('ABSPATH')) {
    exit;
}

class PeerMentoringShowcaseActivityProgress {
    public static function init() {
        add_action('init', array(__CLASS__, 'register_content_types'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_assets'));
        add_action('wp_ajax_peer_mentoring_load_activity', array(__CLASS__, 'load_activity'));
        add_shortcode('peer_mentoring_showcase', array(__CLASS__, 'render_showcase'));
    }

    public static function register_content_types() {
        register_post_type('peer_program', array('label' => __('Programs', 'peer-mentoring-platform-showcase'), 'public' => false, 'show_ui' => true));
        register_post_type('peer_activity', array('label' => __('Activities', 'peer-mentoring-platform-showcase'), 'public' => true, 'show_ui' => true, 'supports' => array('title', 'editor', 'page-attributes')));
        register_post_type('peer_progress_event', array('label' => __('Progress Events', 'peer-mentoring-platform-showcase'), 'public' => false, 'show_ui' => false, 'supports' => array('custom-fields')));
    }

    public static function register_assets() {
        wp_register_script('peer-mentoring-activity-navigation', PEER_MENTORING_SHOWCASE_URL . 'assets/js/activity-navigation.js', array(), PEER_MENTORING_SHOWCASE_VERSION, true);
        wp_register_script('peer-mentoring-reflection-validation', PEER_MENTORING_SHOWCASE_URL . 'assets/js/reflection-validation.js', array(), PEER_MENTORING_SHOWCASE_VERSION, true);
    }

    public static function render_showcase($attributes) {
        $attributes = shortcode_atts(array('activity_id' => 0), $attributes, 'peer_mentoring_showcase');
        $activity_id = absint($attributes['activity_id']);

        wp_enqueue_script('peer-mentoring-activity-navigation');
        wp_enqueue_script('peer-mentoring-reflection-validation');
        wp_localize_script('peer-mentoring-activity-navigation', 'peerMentoringShowcase', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('peer_mentoring_activity'),
            'messages' => array('loading' => __('Loading activity...', 'peer-mentoring-platform-showcase'), 'error' => __('Unable to load the activity.', 'peer-mentoring-platform-showcase')),
        ));

        ob_start();
        ?>
        <section class="peer-mentoring-showcase" aria-labelledby="peer-showcase-title">
            <h2 id="peer-showcase-title"><?php esc_html_e('Peer Activity Demo', 'peer-mentoring-platform-showcase'); ?></h2>
            <div id="peer-activity-status" role="status" aria-live="polite"></div>
            <div id="peer-activity-content">
                <?php echo $activity_id ? wp_kses_post(self::render_activity_content($activity_id)) : esc_html__('Select an activity ID to demonstrate asynchronous navigation.', 'peer-mentoring-platform-showcase'); ?>
            </div>
            <?php require PEER_MENTORING_SHOWCASE_PATH . 'templates/partials/reflection-form.php'; ?>
        </section>
        <?php
        return ob_get_clean();
    }

    public static function load_activity() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'peer_mentoring_activity')) {
            wp_send_json_error(array('message' => __('Your session has expired. Refresh and try again.', 'peer-mentoring-platform-showcase')), 403);
        }

        $activity_id = isset($_POST['activity_id']) ? absint($_POST['activity_id']) : 0;
        $activity = get_post($activity_id);
        if (!$activity || $activity->post_type !== 'peer_activity') {
            wp_send_json_error(array('message' => __('The requested activity is unavailable.', 'peer-mentoring-platform-showcase')), 404);
        }

        $program_id = absint(get_post_meta($activity_id, 'peer_program_id', true));
        if (!$program_id || !self::user_can_access_program(get_current_user_id(), $program_id)) {
            wp_send_json_error(array('message' => __('You do not have access to this activity.', 'peer-mentoring-platform-showcase')), 403);
        }

        self::transition_activity(get_current_user_id(), $program_id, $activity_id);
        $requires_full_render = (bool) get_post_meta($activity_id, 'peer_requires_full_render', true);

        wp_send_json_success(array(
            'activity_id' => $activity_id,
            'title' => get_the_title($activity_id),
            'content' => self::render_activity_content($activity_id),
            'requires_full_render' => $requires_full_render,
            'full_render_url' => $requires_full_render ? get_permalink($activity_id) : '',
        ));
    }

    private static function user_can_access_program($user_id, $program_id) {
        return (bool) apply_filters('peer_mentoring_showcase_user_can_access_program', $user_id > 0, $user_id, $program_id);
    }

    private static function transition_activity($user_id, $program_id, $activity_id) {
        $current_key = 'peer_current_activity_' . $program_id;
        $previous_activity_id = absint(get_user_meta($user_id, $current_key, true));

        if ($previous_activity_id && $previous_activity_id !== $activity_id) {
            self::complete_open_event($user_id, $program_id, $previous_activity_id);
        }

        update_user_meta($user_id, $current_key, $activity_id);
        self::start_event_once($user_id, $program_id, $activity_id);
    }

    private static function start_event_once($user_id, $program_id, $activity_id) {
        $open_event = get_posts(array(
            'post_type' => 'peer_progress_event',
            'post_status' => 'private',
            'posts_per_page' => 1,
            'meta_query' => array(
                array('key' => 'peer_user_id', 'value' => $user_id),
                array('key' => 'peer_program_id', 'value' => $program_id),
                array('key' => 'peer_activity_id', 'value' => $activity_id),
                array('key' => 'peer_event_status', 'value' => 'open'),
            ),
        ));

        if (!$open_event) {
            wp_insert_post(array(
                'post_type' => 'peer_progress_event',
                'post_status' => 'private',
                'post_title' => __('Activity started', 'peer-mentoring-platform-showcase'),
                'meta_input' => array(
                    'peer_user_id' => $user_id,
                    'peer_program_id' => $program_id,
                    'peer_activity_id' => $activity_id,
                    'peer_event_status' => 'open',
                    'peer_started_at' => current_time('mysql', true),
                ),
            ));
        }
    }

    private static function complete_open_event($user_id, $program_id, $activity_id) {
        $open_events = get_posts(array(
            'post_type' => 'peer_progress_event',
            'post_status' => 'private',
            'posts_per_page' => 1,
            'meta_query' => array(
                array('key' => 'peer_user_id', 'value' => $user_id),
                array('key' => 'peer_program_id', 'value' => $program_id),
                array('key' => 'peer_activity_id', 'value' => $activity_id),
                array('key' => 'peer_event_status', 'value' => 'open'),
            ),
        ));

        if ($open_events) {
            update_post_meta($open_events[0]->ID, 'peer_event_status', 'completed');
            update_post_meta($open_events[0]->ID, 'peer_completed_at', current_time('mysql', true));
        }
    }

    private static function render_activity_content($activity_id) {
        $activity = get_post($activity_id);
        return $activity ? apply_filters('the_content', $activity->post_content) : '';
    }
}