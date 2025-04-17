<?php
/**
 * File: shortcodes.php
 * Path: wp-content/plugins/wp_simple_member_sections/includes/shortcodes.php
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('smsw_login_form', function () {
    if (isset($_SESSION['smsw_user']) && is_array($_SESSION['smsw_user'])) {
        return sprintf(
            '<p>%s %s</p>',
            esc_html__('Jste přihlášen jako', 'smsw'),
            esc_html($_SESSION['smsw_user']['email'])
        );
    }

    ob_start();
    include SMSW_PLUGIN_DIR . 'templates/login-form.php';
    return ob_get_clean();
});

add_shortcode('smsw_logout', function () {
    if (isset($_SESSION['smsw_user']) && is_array($_SESSION['smsw_user'])) {
        $current_url = remove_query_arg('lang', $_SERVER['REQUEST_URI']);
        return sprintf(
            '<a href="%s" class="smsw-logout-link">%s</a>',
            esc_url(add_query_arg('smsw_logout', '1', $current_url)),
            esc_html__('Odhlásit se', 'smsw')
        );
    }
    return '';
});