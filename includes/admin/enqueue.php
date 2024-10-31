<?php

add_action('admin_enqueue_scripts', 'prepear_enqueue_admin');

function prepear_enqueue_admin()
{
    global $prepear_auth;
    $screen = get_current_screen();

    if (('settings_page_prepear' === $screen->id || 'toplevel_page_prepear' === $screen->id) && $prepear_auth && Prepear_Recipe_Plugin_Util::is_enabled()) {
        wp_enqueue_script(
            'prepear-main-js',
            PREPEAR_URL . 'assets/js/main.js',
            ['wp-element', 'wp-components'],
            time(),
            true
        );
        wp_enqueue_style(
            'prepear-main-css',
            PREPEAR_URL . 'assets/css/main.css',
            [],
            time(),
            'all'
        );
    }
}

?>