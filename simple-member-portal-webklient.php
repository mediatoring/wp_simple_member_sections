<?php
/**
 * File: simple-member-portal-webklient.php
 * Path: wp-content/plugins/wp_simple_member_sections/simple-member-portal-webklient.php
 *
 * Plugin Name: Simple Member Sections – Webklient
 * Plugin URI: https://github.com/mediatoring/wp_simple_member_sections
 * Description: Uzamčená členská sekce WordPressu s přístupem přes e-mail a PIN. Pro neveřejné kurzy, kvízy a materiály.
 * Version: 1.0.0
 * Author: Michal Kubíček
 * Author URI: https://webklient.cz
 * License: GPL2+
 * Text Domain: smsw
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SMSW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMSW_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once SMSW_PLUGIN_DIR . 'includes/config.php';
require_once SMSW_PLUGIN_DIR . 'includes/auth.php';
require_once SMSW_PLUGIN_DIR . 'includes/user-access.php';
require_once SMSW_PLUGIN_DIR . 'includes/shortcodes.php';
require_once SMSW_PLUGIN_DIR . 'includes/admin-page.php';

add_action('plugins_loaded', function () {
    load_plugin_textdomain('smsw', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('init', function () {
    if (!session_id()) {
        session_start();
    }

    // Nastavení jazyka
    if (isset($_GET['lang']) && isset(SMSW_SUPPORTED_LANGUAGES[$_GET['lang']])) {
        $_SESSION['smsw_lang'] = sanitize_text_field($_GET['lang']);
        // Zachování URL parametrů při změně jazyka
        $current_url = remove_query_arg('lang', $_SERVER['REQUEST_URI']);
        wp_redirect($current_url);
        exit;
    }

    if (!isset($_SESSION['smsw_lang'])) {
        $_SESSION['smsw_lang'] = SMSW_DEFAULT_LANGUAGE;
    }

    switch_to_locale($_SESSION['smsw_lang']);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('smsw-style', SMSW_PLUGIN_URL . 'assets/css/style.css');
});