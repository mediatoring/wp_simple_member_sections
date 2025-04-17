<?php
/**
 * File: config.php
 * Path: wp-content/plugins/wp_simple_member_sections/includes/config.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Základní konfigurace
define('SMSW_SUPPORTED_LANGUAGES', [
    'cs_CZ' => [
        'code' => 'cs_CZ',
        'name' => 'Čeština',
        'flag' => '🇨🇿'
    ],
    'en_US' => [
        'code' => 'en_US',
        'name' => 'English',
        'flag' => '🇬🇧'
    ],
    'it_IT' => [
        'code' => 'it_IT',
        'name' => 'Italiano',
        'flag' => '🇮🇹'
    ]
]);

// Výchozí hodnoty konfigurace
$default_config = [
    'default_language' => 'cs_CZ',
    'login_page' => '/portal/',
    'list_page' => '/portal/list/',
    'pin_length' => 4,
    'pin_pattern' => '\d{4}',
    'session_lifetime' => 3600, // 1 hodina
    'max_login_attempts' => 3,
    'lockout_time' => 900, // 15 minut
    'enable_cookies' => true,
    'remember_me' => true,
    'custom_css' => '',
    'custom_js' => ''
];

// Načtení uložené konfigurace
$config = get_option('smsw_config', $default_config);

// Definice konstant z konfigurace
define('SMSW_DEFAULT_LANGUAGE', $config['default_language']);
define('SMSW_LOGIN_PAGE', $config['login_page']);
define('SMSW_LIST_PAGE', $config['list_page']);
define('SMSW_PIN_LENGTH', $config['pin_length']);
define('SMSW_PIN_PATTERN', $config['pin_pattern']);
define('SMSW_SESSION_LIFETIME', $config['session_lifetime']);
define('SMSW_MAX_LOGIN_ATTEMPTS', $config['max_login_attempts']);
define('SMSW_LOCKOUT_TIME', $config['lockout_time']);
define('SMSW_ENABLE_COOKIES', $config['enable_cookies']);
define('SMSW_REMEMBER_ME', $config['remember_me']);

// Funkce pro získání konfigurace
function smsw_get_config($key = null) {
    global $config;
    if ($key === null) {
        return $config;
    }
    return isset($config[$key]) ? $config[$key] : null;
}

// Funkce pro uložení konfigurace
function smsw_save_config($new_config) {
    global $default_config;
    
    // Validace a sanitizace vstupních dat
    $sanitized_config = [];
    foreach ($default_config as $key => $default_value) {
        if (isset($new_config[$key])) {
            switch ($key) {
                case 'default_language':
                    $sanitized_config[$key] = isset(SMSW_SUPPORTED_LANGUAGES[$new_config[$key]]) 
                        ? $new_config[$key] 
                        : $default_value;
                    break;
                case 'pin_length':
                case 'session_lifetime':
                case 'max_login_attempts':
                case 'lockout_time':
                    $sanitized_config[$key] = absint($new_config[$key]);
                    break;
                case 'enable_cookies':
                case 'remember_me':
                    $sanitized_config[$key] = (bool)$new_config[$key];
                    break;
                case 'custom_css':
                case 'custom_js':
                    $sanitized_config[$key] = wp_kses_post($new_config[$key]);
                    break;
                default:
                    $sanitized_config[$key] = sanitize_text_field($new_config[$key]);
            }
        } else {
            $sanitized_config[$key] = $default_value;
        }
    }
    
    update_option('smsw_config', $sanitized_config);
    return $sanitized_config;
} 