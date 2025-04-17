<?php
/**
 * File: user-access.php
 * Path: wp-content/plugins/wp_simple_member_sections/includes/user-access.php
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {
    // Povolení přístupu pro přihlášené uživatele a administrátory
    if (is_user_logged_in() || is_admin()) {
        return;
    }

    $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Povolení veřejného přístupu mimo /portal/ nebo pro hlavní stránky portal a portal/list
    if (stripos($current_path, 'portal') !== 0 || $current_path === 'portal' || $current_path === 'portal/list') {
        return;
    }

    // Kontrola přihlášení a přístupových práv
    if (!isset($_SESSION['smsw_user']) || !is_array($_SESSION['smsw_user'])) {
        wp_redirect(home_url('/portal/'));
        exit;
    }

    if (empty($_SESSION['smsw_user']['access_pages'])) {
        wp_redirect(home_url('/portal/'));
        exit;
    }

    $allowed = false;
    foreach ($_SESSION['smsw_user']['access_pages'] as $allowed_slug) {
        if ($current_path === trim($allowed_slug, '/')) {
            $allowed = true;
            break;
        }
    }

    if (!$allowed) {
        wp_redirect(home_url('/portal/list/'));
        exit;
    }
});