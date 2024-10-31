<?php

add_action( 'wp_ajax_prepear_admin_prepear_it_save', 'prepear_ajax_admin_prepear_it_save' );

function prepear_ajax_admin_prepear_it_save()
{
    if (!current_user_can('administrator')) {
        die();
    }

    if (isset($_POST['settings'])) {
        $jsonData = stripslashes(html_entity_decode($_POST['settings']));
        $settings = json_decode($jsonData, true);
        $buttons = isset($settings['buttons']) ? $settings['buttons'] : [];
        $inline_css = isset($settings['inlineCss']) ? $settings['inlineCss'] : '';
        $mobile_preview = isset($settings['mobilePreview']) ? $settings['mobilePreview'] : false;

        prepear_admin_update_prepear_settings([
            'buttons' => $buttons,
            'inlineCss' => $inline_css,
            'mobilePreview' => $mobile_preview,
        ]);
    }
}

function prepear_admin_update_prepear_settings($settings)
{
    update_option('prepear_settings', $settings);
}

function prepear_admin_prepear_it()
{
    global $prepear_settings;
    $default_css = prepear_default_css();
    if (!$prepear_settings) {
        $prepear_settings = [
            'buttons' => [],
            'inlineCss' => $default_css,
        ];
        prepear_admin_update_prepear_settings($prepear_settings);
    }

    $plugin_data = Prepear_Recipe_Plugin_Util::get_data();
 
    $prepear_admin_js = [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'prepear' ),
        'defaultInlineCss' => $default_css,
        'pluginData' => $plugin_data,
    ];


    wp_localize_script( 'prepear-main-js', 'prepearSettings', ['settings' => $prepear_settings] );
    wp_localize_script( 'prepear-main-js', 'prepearAdmin', $prepear_admin_js );

    if ($plugin_data['enabled']) {
        echo '<div id="prepear-root" class="wp-clearfix"></div>';
    }
    else {
        echo '<p>Compatible Recipe plugin not detected. We plan to add support for other recipe plugins in the future.</p>';
    }
}

function prepear_default_css()
{
    return ".prepear-it {
    background-color: #749B3A;
    border-color: #749B3A;
    color: white !important;
    text-decoration: none;
    display: inline-block;
    padding: 11px 14px;
    position: relative;
    box-shadow: none !important;
}

.prepear-it-link {
    color: #749B3A !important;
    box-shadow: none !important;
}";
}

?>