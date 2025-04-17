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

// Zpracování formuláře pro správu uživatelů
function smsw_process_user_form() {
    if (!isset($_POST['smsw_user_action']) || !check_admin_referer('smsw_user_management')) {
        return;
    }

    $users = get_option('smsw_users', []);
    if (!is_array($users)) {
        $users = [];
    }

    $action = $_POST['smsw_user_action'];
    $email = isset($_POST['smsw_email']) ? sanitize_email($_POST['smsw_email']) : '';
    $pin = isset($_POST['smsw_pin']) ? sanitize_text_field($_POST['smsw_pin']) : '';
    $access_pages = isset($_POST['smsw_access_pages']) ? (array)$_POST['smsw_access_pages'] : [];

    switch ($action) {
        case 'add':
            if (empty($email) || empty($pin)) {
                add_settings_error('smsw_users', 'invalid_input', __('E-mail a PIN jsou povinné.', 'smsw'));
                return;
            }

            // Kontrola duplicity e-mailu
            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    add_settings_error('smsw_users', 'duplicate_email', __('Uživatel s tímto e-mailem již existuje.', 'smsw'));
                    return;
                }
            }

            $users[] = [
                'email' => $email,
                'pin' => $pin,
                'access_pages' => $access_pages
            ];
            break;

        case 'edit':
            $user_index = isset($_POST['smsw_user_index']) ? absint($_POST['smsw_user_index']) : -1;
            if ($user_index < 0 || !isset($users[$user_index])) {
                add_settings_error('smsw_users', 'invalid_user', __('Neplatný uživatel.', 'smsw'));
                return;
            }

            if (!empty($pin)) {
                $users[$user_index]['pin'] = $pin;
            }
            $users[$user_index]['access_pages'] = $access_pages;
            break;

        case 'delete':
            $user_index = isset($_POST['smsw_user_index']) ? absint($_POST['smsw_user_index']) : -1;
            if ($user_index >= 0 && isset($users[$user_index])) {
                array_splice($users, $user_index, 1);
            }
            break;
    }

    update_option('smsw_users', $users);
    add_settings_error('smsw_users', 'success', __('Změny byly uloženy.', 'smsw'), 'updated');
}

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

// Hlavní administrační stránka
function smsw_render_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění k přístupu na tuto stránku.', 'smsw'));
    }

    // Zpracování formuláře pro správu uživatelů
    smsw_process_user_form();

    // Získání seznamu stránek pro výběr přístupových práv
    $all_pages = get_pages([
        'sort_column' => 'menu_order',
        'sort_order' => 'ASC'
    ]);

    // Filtrování pouze stránek pod /portal/
    $pages = [];
    foreach ($all_pages as $page) {
        $page_path = get_page_uri($page->ID);
        if (strpos($page_path, 'portal/') === 0 && $page_path !== 'portal/list') {
            $pages[] = $page;
        }
    }

    $users = get_option('smsw_users', []);
    if (!is_array($users)) {
        $users = [];
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Správa kurzů a přístupů', 'smsw') . '</h1>';

    // Formulář pro přidání nového uživatele
    echo '<div class="card">';
    echo '<h2>' . esc_html__('Přidat nového uživatele', 'smsw') . '</h2>';
    echo '<form method="post" action="">';
    wp_nonce_field('smsw_user_management');
    echo '<input type="hidden" name="smsw_user_action" value="add">';
    
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="smsw_new_email">' . __('E-mail', 'smsw') . '</label></th>';
    echo '<td><input type="email" name="smsw_email" id="smsw_new_email" class="regular-text" required></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th scope="row"><label for="smsw_new_pin">' . __('PIN', 'smsw') . '</label></th>';
    echo '<td><input type="text" name="smsw_pin" id="smsw_new_pin" pattern="\d{4}" class="regular-text" required></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th scope="row">' . __('Přístup ke stránkám', 'smsw') . '</th>';
    echo '<td>';
    if (!empty($pages)) {
        echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
        foreach ($pages as $page) {
            $page_path = get_page_uri($page->ID);
            echo sprintf(
                '<label style="display: block; margin-bottom: 5px;"><input type="checkbox" name="smsw_access_pages[]" value="%s"> %s</label>',
                esc_attr($page_path),
                esc_html($page->post_title)
            );
        }
        echo '</div>';
    } else {
        echo '<p class="description">' . __('Nejsou k dispozici žádné stránky pod cestou /portal/.', 'smsw') . '</p>';
    }
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    
    submit_button(__('Přidat uživatele', 'smsw'), 'primary');
    echo '</form>';
    echo '</div>';

    // Seznam uživatelů v metaboxu
    echo '<div class="metabox-holder" style="margin-top: 20px;">';
    echo '<div class="postbox" style="width: 100%;">';
    echo '<h2 class="hndle" style="padding: 10px 15px; margin: 0; border-bottom: 1px solid #ddd;">';
    echo '<span>' . esc_html__('Seznam uživatelů', 'smsw') . '</span>';
    echo '</h2>';
    echo '<div class="inside" style="padding: 15px;">';

    if (!empty($users)) {
        echo '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
        echo '<thead><tr>';
        echo '<th style="width: 25%;">' . __('E-mail', 'smsw') . '</th>';
        echo '<th style="width: 15%;">' . __('PIN', 'smsw') . '</th>';
        echo '<th style="width: 40%;">' . __('Přístup ke stránkám', 'smsw') . '</th>';
        echo '<th style="width: 20%;">' . __('Akce', 'smsw') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($users as $index => $user) {
            echo '<tr>';
            echo '<td>' . esc_html($user['email']) . '</td>';
            echo '<td>' . esc_html($user['pin']) . '</td>';
            echo '<td>';
            if (!empty($user['access_pages'])) {
                foreach ($user['access_pages'] as $page_path) {
                    $page = get_page_by_path($page_path);
                    if ($page) {
                        echo '<div><code>' . esc_html($page->post_title) . '</code></div>';
                    } else {
                        echo '<div><code>' . esc_html($page_path) . '</code> <em>(' . __('stránka nenalezena', 'smsw') . ')</em></div>';
                    }
                }
            } else {
                echo '<em>' . __('Žádný přístup', 'smsw') . '</em>';
            }
            echo '</td>';
            echo '<td>';
            echo '<form method="post" action="" style="display: inline;">';
            wp_nonce_field('smsw_user_management');
            echo '<input type="hidden" name="smsw_user_action" value="edit">';
            echo '<input type="hidden" name="smsw_user_index" value="' . esc_attr($index) . '">';
            
            if (!empty($pages)) {
                echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
                foreach ($pages as $page) {
                    $page_path = get_page_uri($page->ID);
                    $checked = in_array($page_path, $user['access_pages'] ?? []) ? ' checked' : '';
                    echo sprintf(
                        '<label style="display: block; margin-bottom: 5px;"><input type="checkbox" name="smsw_access_pages[]" value="%s"%s> %s</label>',
                        esc_attr($page_path),
                        $checked,
                        esc_html($page->post_title)
                    );
                }
                echo '</div>';
            }
            submit_button(__('Upravit přístup', 'smsw'), 'small');
            echo '</form>';

            echo '<form method="post" action="" style="display: inline;">';
            wp_nonce_field('smsw_user_management');
            echo '<input type="hidden" name="smsw_user_action" value="delete">';
            echo '<input type="hidden" name="smsw_user_index" value="' . esc_attr($index) . '">';
            submit_button(__('Smazat', 'smsw'), 'small delete');
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>' . esc_html__('Zatím nejsou definováni žádní uživatelé.', 'smsw') . '</p>';
    }

    echo '</div>'; // .inside
    echo '</div>'; // .postbox
    echo '</div>'; // .metabox-holder
    echo '</div>'; // .wrap
}