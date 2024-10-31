<?php
/**
 * Plugin Name: Prepear Pro Sync
 * Plugin URI: https://wordpress.org/plugins/prepear
 * Description: Link your blog to your Prepear Pro account.
 * Version: 1.4.9
 * Author: The Prepear Team
 * License: GPL2
 *
 * Prepear Wordpress Link is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Prepear Wordpress Link is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Prepear Wordpress Link. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

defined( 'ABSPATH' ) or die( 'No direct access!' );
$prepear_settings = get_option( 'prepear_settings' );
$prepear_auth = get_option( 'prepear_auth' );

define( 'PREPEAR_VERSION', '1.4.9' );
define( 'PREPEAR_DIR', plugin_dir_path( __FILE__ ) );
define( 'PREPEAR_URL', plugin_dir_url( __FILE__ ) );
define( 'PREPEAR_BASE', plugin_basename(__FILE__) );

if ( is_admin() ) {    
    require_once PREPEAR_DIR . 'includes/admin/imports.php';
}
else {
    add_action('wp_enqueue_scripts', 'prepear_enqueue_prepear_it_script');
}

function prepear_enqueue_prepear_it_script()
{
    global $prepear_settings;

    if (!$prepear_settings) {
        $prepear_settings = [];
    }
    if (!isset($prepear_settings['buttons'])) {
        $prepear_settings['buttons'] = [];
    }

    if( !is_user_logged_in() && ( !is_single() || !count($prepear_settings['buttons']) ) ) {
        return;
    }

    if (isset($prepear_settings['inlineCss']) && $prepear_settings['inlineCss']) {
        wp_register_style( 'prpr-custom-style', false );
        wp_enqueue_style( 'prpr-custom-style' );
        wp_add_inline_style( 'prpr-custom-style', $prepear_settings['inlineCss'] );
    }

    unset($prepear_settings['inlineCss']);

    wp_register_script( 'prepear-loader', '' );
    wp_enqueue_script( 'prepear-loader' );

    $btn_json = json_encode($prepear_settings);
    $is_prepear_preview = filter_input(INPUT_GET, 'prepear_preview', FILTER_SANITIZE_STRING);
    $args = $is_prepear_preview ? '&preview_mode=1' : '';

    wp_add_inline_script( 'prepear-loader', "(function(w, d, s, e) {
w.prprItConfig=$btn_json;
var t = d.createElement(s);t.defer = true;t.src='https://app.prepear.com/share/prepear-it.js?page='+e(location.href.replace(/#.*$/, ''))+'${args}&c='+(+new Date);
var f=d.getElementsByTagName(s)[0];f.parentNode.insertBefore(t,f);
})(window, document, 'script', encodeURIComponent);");
}

?>
