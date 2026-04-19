<?php
/**
 * Lattice License Manager — Uninstall
 *
 * Fires when the plugin is deleted via WP Admin.
 * Cleans up all options and scheduled events.
 *
 * @package Lattice_License
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove options
$options = [
    'lattice_license_key',
    'lattice_license_api_secret',
    'lattice_license_activated',
    'lattice_license_valid',
    'lattice_license_last_check',
];

foreach ($options as $option) {
    delete_option($option);
}

// Clear transients (keyed, so we can't enumerate — flush any with our prefix)
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_lattice_license_%'
        OR option_name LIKE '_transient_timeout_lattice_license_%'"
);

// Clear scheduled cron
$timestamp = wp_next_scheduled('lattice_license_check');
if ($timestamp) {
    wp_unschedule_event($timestamp, 'lattice_license_check');
}
