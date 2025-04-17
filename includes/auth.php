<?php
/**
 * File: auth.php
 * Path: wp-content/plugins/wp_simple_member_sections/includes/auth.php
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    if (!session_id()) {
        session_start();
    }

    // Odhlášení
    if (isset($_GET['smsw_logout'])) {
        unset($_SESSION['smsw_user']);
        wp_redirect(home_url('/portal/'));
        exit;
    }

    // Přihlášení
    if (isset($_POST['smsw_login'])) {
        $email = isset($_POST['smsw_email']) ? sanitize_email($_POST['smsw_email']) : '';
        $pin = isset($_POST['smsw_pin']) ? sanitize_text_field($_POST['smsw_pin']) : '';

        if (empty($email) || empty($pin)) {
            $_SESSION['smsw_login_error'] = __('Prosím vyplňte e-mail a PIN.', 'smsw');
            wp_redirect(home_url('/portal/'));
            exit;
        }

        if (!preg_match('/^\d{4}$/', $pin)) {
            $_SESSION['smsw_login_error'] = __('PIN musí obsahovat přesně 4 číslice.', 'smsw');
            wp_redirect(home_url('/portal/'));
            exit;
        }

        $users = get_option('smsw_users', []);

        foreach ($users as $user) {
            if ($user['email'] === $email && $user['pin'] === $pin) {
                $_SESSION['smsw_user'] = [
                    'email' => $email,
                    'access_pages' => isset($user['access_pages']) ? (array)$user['access_pages'] : []
                ];

                wp_redirect(home_url('/portal/list/'));
                exit;
            }
        }

        $_SESSION['smsw_login_error'] = __('Neplatný e-mail nebo PIN.', 'smsw');
        wp_redirect(home_url('/portal/'));
        exit;
    }
});
