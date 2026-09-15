<?php

if (!defined('ABSPATH')) {
    exit;
}

class PeerMentoringShowcaseTemplateRegistry {
    private const TEMPLATE_KEY = 'peer-mentoring-showcase/page-peer-session.php';

    public static function init() {
        add_filter('theme_page_templates', array(__CLASS__, 'register_template'), 10, 4);
        add_filter('template_include', array(__CLASS__, 'include_template'));
    }

    public static function register_template($page_templates, $theme, $post, $post_type) {
        if ($post_type === 'page') {
            $page_templates[self::TEMPLATE_KEY] = __('Peer Session Dashboard', 'peer-mentoring-platform-showcase');
        }

        return $page_templates;
    }

    public static function include_template($template) {
        if (get_page_template_slug() !== self::TEMPLATE_KEY) {
            return $template;
        }

        $relative_path = self::TEMPLATE_KEY;
        $candidates = array(
            trailingslashit(get_stylesheet_directory()) . $relative_path,
            trailingslashit(get_template_directory()) . $relative_path,
            PEER_MENTORING_SHOWCASE_PATH . 'templates/page-peer-session.php',
        );

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return $template;
    }
}