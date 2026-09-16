<?php
// Demonstrates: registering a nested page template and resolving it with a child-theme-first fallback.

if (!defined('ABSPATH')) {
    exit;
}

add_filter('theme_page_templates', 'peer_register_session_template', 10, 4);
add_filter('template_include', 'peer_resolve_session_template');

function peer_register_session_template($page_templates, $theme, $post, $post_type) {
    if ($post_type === 'page') {
        $page_templates['templates/peer-mentoring/page-session-dashboard.php'] = __('Peer Session Dashboard', 'peer-mentoring-showcase');
    }

    return $page_templates;
}

function peer_resolve_session_template($template) {
    $slug = get_page_template_slug();

    if (!$slug) {
        return $template;
    }

    // Child theme wins if it overrides the template; otherwise fall back to the parent theme copy.
    $child_path = trailingslashit(get_stylesheet_directory()) . ltrim($slug, '/');
    $parent_path = trailingslashit(get_template_directory()) . ltrim($slug, '/');

    if (file_exists($child_path)) {
        return $child_path;
    }

    if (file_exists($parent_path)) {
        return $parent_path;
    }

    return $template;
}
