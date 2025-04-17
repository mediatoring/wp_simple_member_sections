<?php
/**
 * File: user-portal.php
 * Path: wp-content/plugins/wp_simple_member_sections/templates/user-portal.php
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($_SESSION['smsw_user']) || empty($_SESSION['smsw_user']['access_pages'])) {
    echo '<p>' . esc_html__('Nemáte přiřazeny žádné stránky.', 'smsw') . '</p>';
    return;
}

echo '<h2>' . esc_html__('Vaše kurzy a obsah', 'smsw') . '</h2>';
echo '<ul class="smsw-course-list">';

foreach ($_SESSION['smsw_user']['access_pages'] as $slug) {
    $page = get_page_by_path($slug);
    if ($page) {
        echo '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . esc_html(get_the_title($page->ID)) . '</a></li>';
    } else {
        echo '<li><em>' . esc_html($slug) . '</em> (stránka nenalezena)</li>';
    }
}

echo '</ul>';