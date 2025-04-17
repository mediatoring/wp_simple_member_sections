<?php
/**
 * File: login-form.php
 * Path: wp-content/plugins/wp_simple_member_sections/templates/login-form.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$error = '';
if (!empty($_SESSION['smsw_login_error'])) {
    $error = '<div class="smsw-error">' . esc_html($_SESSION['smsw_login_error']) . '</div>';
    unset($_SESSION['smsw_login_error']);
}

// Jazykový přepínač
$current_url = remove_query_arg('lang', $_SERVER['REQUEST_URI']);
echo '<div class="smsw-lang-switcher">';
foreach (SMSW_SUPPORTED_LANGUAGES as $lang_code => $lang) {
    $active_class = ($_SESSION['smsw_lang'] === $lang_code) ? 'active' : '';
    echo sprintf(
        '<a href="%s" class="%s" title="%s">%s %s</a>',
        esc_url(add_query_arg('lang', $lang_code, $current_url)),
        esc_attr($active_class),
        esc_attr($lang['name']),
        esc_html($lang['flag']),
        esc_html($lang['name'])
    );
}
echo '</div>';

echo $error;
?>

<form method="post" class="smsw-login-form">
    <label for="smsw_email"><?php esc_html_e('E-mail', 'smsw'); ?></label>
    <input type="email" id="smsw_email" name="smsw_email" required />

    <label for="smsw_pin"><?php esc_html_e('PIN (4 číslice)', 'smsw'); ?></label>
    <input type="text" id="smsw_pin" name="smsw_pin" pattern="\d{4}" required />

    <button type="submit" name="smsw_login"><?php esc_html_e('Přihlásit se', 'smsw'); ?></button>
</form>
