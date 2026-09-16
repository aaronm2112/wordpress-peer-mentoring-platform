<?php
// Demonstrates: a hierarchical "guide" CPT containing ordered "activity" posts, linked without a custom table.

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'peer_register_guide_post_type');
add_action('init', 'peer_register_activity_post_type');

function peer_register_guide_post_type() {
    register_post_type('peer_guide', array(
        'label' => __('Guides', 'peer-mentoring-showcase'),
        'public' => true,
        'hierarchical' => true, // allows a guide to contain child guides/sections
        'supports' => array('title', 'editor', 'page-attributes'),
    ));
}

function peer_register_activity_post_type() {
    register_post_type('peer_activity', array(
        'label' => __('Activities', 'peer-mentoring-showcase'),
        'public' => true,
        'supports' => array('title', 'editor', 'page-attributes'), // page-attributes enables menu_order for sequencing
    ));
}

// An activity belongs to exactly one guide via this meta field, not post_parent, so guides stay free to nest.
function peer_link_activity_to_guide($activity_id, $guide_id) {
    return update_post_meta($activity_id, 'guide_id', absint($guide_id));
}

// Returns a guide's activities in author-defined sequence.
function get_guide_activities($guide_id) {
    return get_posts(array(
        'post_type' => 'peer_activity',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_key' => 'guide_id',
        'meta_value' => absint($guide_id),
    ));
}

// Walks the ordered activity list to find what comes after $current_activity_id.
function get_next_activity($current_activity_id, $guide_id) {
    $activities = get_guide_activities($guide_id);

    foreach ($activities as $index => $activity) {
        if ($activity->ID === (int) $current_activity_id) {
            return $activities[$index + 1] ?? null;
        }
    }

    return null;
}
