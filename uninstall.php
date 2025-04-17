<?php
/**
 * File: uninstall.php
 * Path: wp-content/plugins/wp_simple_member_sections/uninstall.php
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Smazání všech pluginových dat
delete_option('smsw_users');
delete_option('smsw_config');

// Smazání všech pluginových meta dat
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'smsw_%'");

// Smazání všech pluginových transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_smsw_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_smsw_%'");

// Smazání všech pluginových capabilities
$roles = get_editable_roles();
foreach ($roles as $role_name => $role_info) {
    $role = get_role($role_name);
    if ($role) {
        $role->remove_cap('smsw_access_portal');
    }
}
