<?php
/**
 * File: auth.php
 * Path: wp-content/plugins/wp_simple_member_sections/includes/auth.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Pomocná funkce pro debugování
function smsw_debug($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('SMSW Debug: ' . $message);
        if ($data !== null) {
            error_log('Data: ' . print_r($data, true));
        }
    }
}

// Pomocná funkce pro kontrolu, zda je stránka v sekci portal
function smsw_is_portal_page($page_uri) {
    $page_uri = trim($page_uri, '/');
    return strpos($page_uri, 'portal') === 0;
}

// Pomocná funkce pro získání aktuální stránky
function smsw_get_current_page() {
    global $post;
    if (!$post || !is_page()) {
        return false;
    }
    return trim(get_page_uri($post->ID), '/');
}

// Pomocná funkce pro výpis dat do logu
function print_data($data) {
    ob_start();
    var_export($data);
    return ob_get_clean();
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
            return;
        }

        if (!preg_match('/^\d{4}$/', $pin)) {
            $_SESSION['smsw_login_error'] = __('PIN musí obsahovat přesně 4 číslice.', 'smsw');
            return;
        }

        $users = get_option('smsw_users', []);

        foreach ($users as $user) {
            if ($user['email'] === $email && $user['pin'] === $pin) {
                $_SESSION['smsw_user'] = [
                    'email' => $email,
                    'access_pages' => isset($user['access_pages']) ? (array)$user['access_pages'] : []
                ];

                // Přesměrujeme s parametrem pro obnovení stránky
                $redirect_url = add_query_arg('smsw_refresh', '1', $_SERVER['REQUEST_URI']);
                wp_redirect($redirect_url);
                exit;
            }
        }

        $_SESSION['smsw_login_error'] = __('Neplatný e-mail nebo PIN.', 'smsw');
    }
});

// Přidáme filtr pro obsah stránky
add_filter('template_include', 'smsw_template_include', 999999);

function smsw_template_include($template) {
    $current_page = smsw_get_current_page();
    if (!$current_page || !smsw_is_portal_page($current_page)) {
        return $template;
    }

    smsw_debug('Template Include - Current Page:', $current_page);
    
    // Necháme WordPress načíst šablonu stránky
    $page_template = get_page_template();
    smsw_debug('Using Page Template:', $page_template);
    return $page_template;
}

// Přidáme filtr pro obsah stránky
add_filter('the_content', 'smsw_filter_protected_content', 999999);

function smsw_filter_protected_content($content) {
    $current_page = smsw_get_current_page();
    if (!$current_page || !smsw_is_portal_page($current_page)) {
        return $content;
    }

    smsw_debug('Filter Content - Current Page:', $current_page);
    smsw_debug('Original Content:', $content);

    // Pro /portal/ a /portal/list/ zobrazíme vždy obsah
    if ($current_page === 'portal' || $current_page === 'portal/list') {
        $processed_content = do_shortcode($content);
        smsw_debug('Processed Content:', $processed_content);
        return $processed_content;
    }

    // Pro ostatní stránky kontrolujeme přístup
    if (!isset($_SESSION['smsw_user']) || !is_array($_SESSION['smsw_user'])) {
        wp_redirect(home_url('/portal/'));
        exit;
    }

    $user_email = $_SESSION['smsw_user']['email'];
    $users = get_option('smsw_users', []);
    
    foreach ($users as $user) {
        if ($user['email'] === $user_email) {
            if (in_array($current_page, $user['access_pages'])) {
                $processed_content = do_shortcode($content);
                smsw_debug('Processed Content:', $processed_content);
                return $processed_content;
            }
            break;
        }
    }

    // Pokud nemá přístup, přesměrujeme na seznam
    wp_redirect(home_url('/portal/list/'));
    exit;
}
