<?php
/**
 * File: admin-page.php
 * Path: wp-content/plugins/wp_simple_member_sections/includes/admin-page.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Přidání menu položek
add_action('admin_menu', function () {
    add_menu_page(
        __('Správa kurzů', 'smsw'),
        __('Kurzy', 'smsw'),
        'manage_options',
        'smsw_courses',
        'smsw_render_admin_page',
        'dashicons-welcome-learn-more',
        30
    );

    add_submenu_page(
        'smsw_courses',
        __('Nastavení', 'smsw'),
        __('Nastavení', 'smsw'),
        'manage_options',
        'smsw_settings',
        'smsw_render_settings_page'
    );
});

// Stránka s nastavením
function smsw_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění k přístupu na tuto stránku.', 'smsw'));
    }

    // Zpracování formuláře
    if (isset($_POST['smsw_save_settings']) && check_admin_referer('smsw_settings')) {
        $new_config = [
            'default_language' => $_POST['default_language'] ?? '',
            'login_page' => $_POST['login_page'] ?? '',
            'list_page' => $_POST['list_page'] ?? '',
            'pin_length' => $_POST['pin_length'] ?? 4,
            'session_lifetime' => $_POST['session_lifetime'] ?? 3600,
            'max_login_attempts' => $_POST['max_login_attempts'] ?? 3,
            'lockout_time' => $_POST['lockout_time'] ?? 900,
            'enable_cookies' => isset($_POST['enable_cookies']),
            'remember_me' => isset($_POST['remember_me']),
            'custom_css' => $_POST['custom_css'] ?? '',
            'custom_js' => $_POST['custom_js'] ?? ''
        ];

        smsw_save_config($new_config);
        echo '<div class="notice notice-success"><p>' . __('Nastavení bylo uloženo.', 'smsw') . '</p></div>';
    }

    $config = smsw_get_config();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Nastavení členské sekce', 'smsw') . '</h1>';
    
    echo '<form method="post" action="">';
    wp_nonce_field('smsw_settings');
    
    echo '<table class="form-table">';
    
    // Výchozí jazyk
    echo '<tr>';
    echo '<th scope="row"><label for="default_language">' . __('Výchozí jazyk', 'smsw') . '</label></th>';
    echo '<td><select name="default_language" id="default_language">';
    foreach (SMSW_SUPPORTED_LANGUAGES as $code => $lang) {
        $selected = selected($config['default_language'], $code, false);
        echo sprintf(
            '<option value="%s" %s>%s %s</option>',
            esc_attr($code),
            $selected,
            esc_html($lang['flag']),
            esc_html($lang['name'])
        );
    }
    echo '</select></td>';
    echo '</tr>';
    
    // Stránka přihlášení
    echo '<tr>';
    echo '<th scope="row"><label for="login_page">' . __('Stránka přihlášení', 'smsw') . '</label></th>';
    echo '<td><input type="text" name="login_page" id="login_page" value="' . esc_attr($config['login_page']) . '" class="regular-text"></td>';
    echo '</tr>';
    
    // Stránka se seznamem
    echo '<tr>';
    echo '<th scope="row"><label for="list_page">' . __('Stránka se seznamem', 'smsw') . '</label></th>';
    echo '<td><input type="text" name="list_page" id="list_page" value="' . esc_attr($config['list_page']) . '" class="regular-text"></td>';
    echo '</tr>';
    
    // Délka PIN kódu
    echo '<tr>';
    echo '<th scope="row"><label for="pin_length">' . __('Délka PIN kódu', 'smsw') . '</label></th>';
    echo '<td><input type="number" name="pin_length" id="pin_length" value="' . esc_attr($config['pin_length']) . '" min="4" max="8" class="small-text"></td>';
    echo '</tr>';
    
    // Doba platnosti session
    echo '<tr>';
    echo '<th scope="row"><label for="session_lifetime">' . __('Doba platnosti přihlášení (v sekundách)', 'smsw') . '</label></th>';
    echo '<td><input type="number" name="session_lifetime" id="session_lifetime" value="' . esc_attr($config['session_lifetime']) . '" min="300" step="300" class="small-text"></td>';
    echo '</tr>';
    
    // Maximální počet pokusů
    echo '<tr>';
    echo '<th scope="row"><label for="max_login_attempts">' . __('Maximální počet pokusů o přihlášení', 'smsw') . '</label></th>';
    echo '<td><input type="number" name="max_login_attempts" id="max_login_attempts" value="' . esc_attr($config['max_login_attempts']) . '" min="1" max="10" class="small-text"></td>';
    echo '</tr>';
    
    // Doba uzamčení
    echo '<tr>';
    echo '<th scope="row"><label for="lockout_time">' . __('Doba uzamčení po překročení pokusů (v sekundách)', 'smsw') . '</label></th>';
    echo '<td><input type="number" name="lockout_time" id="lockout_time" value="' . esc_attr($config['lockout_time']) . '" min="60" step="60" class="small-text"></td>';
    echo '</tr>';
    
    // Cookies
    echo '<tr>';
    echo '<th scope="row">' . __('Cookies', 'smsw') . '</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="enable_cookies" value="1" ' . checked($config['enable_cookies'], true, false) . '> ' . __('Povolit cookies', 'smsw') . '</label><br>';
    echo '<label><input type="checkbox" name="remember_me" value="1" ' . checked($config['remember_me'], true, false) . '> ' . __('Povolit "Zapamatovat si mě"', 'smsw') . '</label>';
    echo '</td>';
    echo '</tr>';
    
    // Vlastní CSS
    echo '<tr>';
    echo '<th scope="row"><label for="custom_css">' . __('Vlastní CSS', 'smsw') . '</label></th>';
    echo '<td><textarea name="custom_css" id="custom_css" rows="5" class="large-text code">' . esc_textarea($config['custom_css']) . '</textarea></td>';
    echo '</tr>';
    
    // Vlastní JavaScript
    echo '<tr>';
    echo '<th scope="row"><label for="custom_js">' . __('Vlastní JavaScript', 'smsw') . '</label></th>';
    echo '<td><textarea name="custom_js" id="custom_js" rows="5" class="large-text code">' . esc_textarea($config['custom_js']) . '</textarea></td>';
    echo '</tr>';
    
    echo '</table>';
    
    submit_button(__('Uložit nastavení', 'smsw'), 'primary', 'smsw_save_settings');
    echo '</form>';
    echo '</div>';
}

// Původní funkce pro zobrazení stránky s kurzy
function smsw_render_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění k přístupu na tuto stránku.', 'smsw'));
    }

    $users = get_option('smsw_users', []);
    if (!is_array($users)) {
        $users = [];
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Správa kurzů a přístupů', 'smsw') . '</h1>';

    if (!empty($users)) {
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('E-mail', 'smsw') . '</th>';
        echo '<th>' . esc_html__('PIN', 'smsw') . '</th>';
        echo '<th>' . esc_html__('Přístup ke stránkám', 'smsw') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($users as $user) {
            if (!is_array($user)) {
                continue;
            }

            echo '<tr>';
            echo '<td>' . esc_html($user['email'] ?? '') . '</td>';
            echo '<td>' . esc_html($user['pin'] ?? '') . '</td>';
            echo '<td>';

            if (!empty($user['access_pages']) && is_array($user['access_pages'])) {
                foreach ($user['access_pages'] as $slug) {
                    echo '<div><code>' . esc_html($slug) . '</code></div>';
                }
            } else {
                echo '<em>' . esc_html__('Žádný přístup.', 'smsw') . '</em>';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>' . esc_html__('Zatím nejsou definováni žádní uživatelé.', 'smsw') . '</p>';
    }

    echo '</div>';
}