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
        $user_email = $_SESSION['smsw_user']['email'];
        $users = get_option('smsw_users', []);
        $user = null;

        // Najít uživatele podle e-mailu
        foreach ($users as $u) {
            if ($u['email'] === $user_email) {
                $user = $u;
                break;
            }
        }

        $output = '<div class="smsw-member-sections-list">';
        $output .= sprintf(
            '<h2 class="smsw-list-title">%s %s</h2>',
            esc_html__('Jste přihlášen jako', 'smsw'),
            esc_html($user_email)
        );

        if (!$user || empty($user['access_pages'])) {
            $output .= '<div class="smsw-notice">' . esc_html__('Nemáte přístup k žádným stránkám.', 'smsw') . '</div>';
        } else {
            $output .= '<div class="smsw-page-list-container">';
            $output .= '<ul class="smsw-page-list">';

            // Přidání stránek do seznamu
            foreach ($user['access_pages'] as $page_path) {
                $page = get_page_by_path($page_path);
                if ($page) {
                    $page_url = get_permalink($page->ID);
                    $output .= sprintf(
                        '<li class="smsw-page-item"><a href="%s" class="smsw-page-link">%s</a></li>',
                        esc_url($page_url),
                        esc_html($page->post_title)
                    );
                }
            }

            $output .= '</ul>';
            $output .= '</div>';
        }

        // Přidáme odkaz na odhlášení
        $current_url = remove_query_arg(['lang', 'smsw_logout'], $_SERVER['REQUEST_URI']);
        if (empty($current_url)) {
            $current_url = '/';
        }
        
        // Zachováme aktuální jazyk v URL
        if (isset($_SESSION['smsw_lang'])) {
            $current_url = add_query_arg('lang', $_SESSION['smsw_lang'], $current_url);
        }
        
        $output .= sprintf(
            '<p class="smsw-logout"><a href="%s" class="smsw-logout-link">%s</a></p>',
            esc_url(add_query_arg('smsw_logout', '1', $current_url)),
            esc_html__('Odhlásit se', 'smsw')
        );

        $output .= '</div>';
        return $output;
    }

    ob_start();
    include SMSW_PLUGIN_DIR . 'templates/login-form.php';
    return ob_get_clean();
});

add_shortcode('logout', function () {
    if (isset($_SESSION['smsw_user']) && is_array($_SESSION['smsw_user'])) {
        $current_url = remove_query_arg(['lang', 'smsw_logout'], $_SERVER['REQUEST_URI']);
        if (empty($current_url)) {
            $current_url = '/';
        }
        
        // Zachováme aktuální jazyk v URL
        if (isset($_SESSION['smsw_lang'])) {
            $current_url = add_query_arg('lang', $_SESSION['smsw_lang'], $current_url);
        }
        
        return sprintf(
            '<a href="%s" class="smsw-logout-link">%s</a>',
            esc_url(add_query_arg('smsw_logout', '1', $current_url)),
            esc_html__('Odhlásit se', 'smsw')
        );
    }
    return '';
});

// Shortcode pro zobrazení seznamu přístupných stránek
function smsw_member_sections_list_shortcode($atts) {
    // Kontrola přihlášení
    if (!isset($_SESSION['smsw_user']) || !is_array($_SESSION['smsw_user'])) {
        return '<div class="smsw-notice">' . esc_html__('Pro zobrazení seznamu stránek se musíte přihlásit.', 'smsw') . '</div>';
    }

    $user_email = $_SESSION['smsw_user']['email'];
    $users = get_option('smsw_users', []);
    $user = null;

    // Najít uživatele podle e-mailu
    foreach ($users as $u) {
        if ($u['email'] === $user_email) {
            $user = $u;
            break;
        }
    }

    if (!$user || empty($user['access_pages'])) {
        return '<div class="smsw-notice">' . esc_html__('Nemáte přístup k žádným stránkám.', 'smsw') . '</div>';
    }

    // Začátek výstupu
    $output = '<div class="smsw-member-sections-list">';
    $output .= '<h2 class="smsw-list-title">' . esc_html__('Vaše přístupné stránky', 'smsw') . '</h2>';
    $output .= '<div class="smsw-page-list-container">';
    $output .= '<ul class="smsw-page-list">';

    // Přidání stránek do seznamu
    foreach ($user['access_pages'] as $page_path) {
        $page = get_page_by_path($page_path);
        if ($page) {
            $page_url = get_permalink($page->ID);
            $output .= sprintf(
                '<li class="smsw-page-item"><a href="%s" class="smsw-page-link">%s</a></li>',
                esc_url($page_url),
                esc_html($page->post_title)
            );
        }
    }

    // Konec výstupu
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('smsw_member_sections_list', 'smsw_member_sections_list_shortcode');