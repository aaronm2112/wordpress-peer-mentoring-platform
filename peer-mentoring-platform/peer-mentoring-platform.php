<?php
/**
 * Plugin Name: Peer Mentoring Platform Showcase
 * Description: A sanitized WordPress portfolio plugin demonstrating session workflows, activity navigation, progress tracking, and accessible forms.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Aaron Miller
 * License: GPL-2.0-or-later
 * Text Domain: peer-mentoring-platform-showcase
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PEER_MENTORING_SHOWCASE_VERSION', '1.0.0');
define('PEER_MENTORING_SHOWCASE_PATH', plugin_dir_path(__FILE__));
define('PEER_MENTORING_SHOWCASE_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/includes/class-template-registry.php';
require_once __DIR__ . '/includes/class-session-endpoints.php';
require_once __DIR__ . '/includes/class-activity-progress.php';

PeerMentoringShowcaseTemplateRegistry::init();
PeerMentoringShowcaseSessionEndpoints::init();
PeerMentoringShowcaseActivityProgress::init();