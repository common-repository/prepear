<?php

class Prepear_Recipe_Plugin_Util {

    public static function get_data()
    {
        $data = ['enabled' => self::is_enabled()];
        if ($data['enabled'] == 'wp-recipe-maker') {
            $data['preview_url'] = self::get_wprm_preview_url();
            $data['action_buttons'] = self::get_wprm_action_buttons();
        } elseif ($data['enabled'] == 'mediavine-create') {
            $data['preview_url'] = self::get_mediavine_preview_url();
            $data['action_buttons'] = self::get_mediavine_action_buttons();
            $data['cardStyle'] = self::get_mediavine_card_style();
        } else {
            $data['enabled'] = '';
        }
        return $data;
    }

    public static function is_enabled()
    {
        $active_plugins = get_option('active_plugins');
        // $all_plugins = get_plugins();
        // $wp_recipe_maker_version = null;
        foreach ($active_plugins as $plugin) {
            if ($plugin == 'wp-recipe-maker/wp-recipe-maker.php') {
                // $wp_recipe_maker_version = $all_plugins[$plugin]['Version'];
                return 'wp-recipe-maker';
            } elseif ($plugin == 'mediavine-create/mediavine-create.php') {
                return 'mediavine-create';
            }
        }
    }

    private static function get_wprm_action_buttons()
    {
        $buttons = [];

        if( class_exists( 'WPRM_Template_Manager' ) ) {
            $template = WPRM_Template_Manager::get_template_by_type();
            if ($template) {
                $shortcodes = Prepear_ShortcodeParser::parse_shortcodes($template['html']);

                foreach ($shortcodes['order'] as $code) {
                    if (in_array($code, ['wprm-recipe-print', 'wprm-recipe-pin', 'wprm-recipe-add-to-collection', 'wprm-recipe-jump-to-comments'])) {
                        if (isset($shortcodes[$code])) {
                            $codeInfo = $shortcodes[$code];
                            $atts = self::get_atts_for_shortcode($codeInfo);
                            $buttons[] = [
                                'code' => $code,
                                'text' => $atts['text'],
                                'class' => $atts['class'],
                                'style' => $atts['style'],
                                'iconColor' => $atts['icon_color'],
                                'btnClass' => 'wprm-recipe-icon'
                            ];
                        }
                    }
                }
            }
        }

        return $buttons;
    }

    private static function get_mediavine_card_style()
    {
        if (class_exists('Mediavine\Create\Plugin')) {
            return \Mediavine\Settings::get_setting('mv_create_card_style');
        }
    }

    private static function get_mediavine_action_buttons()
    {
        $buttons = [];
        $cardStyle = self::get_mediavine_card_style();
        if ($cardStyle) {
            $style = '';
            $styleBefore = '';
            $defaultIncludeWrapper = false;
            if ($cardStyle == 'centered' || $cardStyle == 'centered-dark') {
                $style = 'display: block; text-align: center; margin-top: -2.5em;';
                $styleBefore = 'display: block; text-align: center; margin-bottom: 1.5em;';
            } else if ($cardStyle == 'big-image') {
                $defaultIncludeWrapper = true;
                $style = 'margin-top: 10px;';
                $styleBefore = 'margin-bottom: 10px;';
            } else {
                $style = 'margin-top: 10px;';
                $styleBefore = 'margin-bottom: 10px;';
            }
            $buttons[] = [
                'code' => 'mv-create-print-button',
                'text' => 'Print',
                'class' => 'mv-create-button mv-create-print-button button',
                'style' => $style,
                'styleBefore' => $styleBefore,
                'iconColor' => '',
                'btnClass' => 'button',
                'defaultIncludeWrapper' => $defaultIncludeWrapper,
            ];
        }

        return $buttons;
    }

    private static function get_wprm_preview_url()
    {
        if ( defined( 'WPRM_POST_TYPE' ) && class_exists( 'WPRM_Recipe_Manager' ) ) {
            $posts = get_posts( array(
                'post_type' => WPRM_POST_TYPE,
                'posts_per_page' => 1,
                'orderby' => 'rand',
            ) );

            $recipe_id = isset( $posts[0] ) ? $posts[0]->ID : 0;
            $recipe = WPRM_Recipe_Manager::get_recipe( $recipe_id );

            if ($recipe) {
                $post_id = $recipe->parent_post_id();
                return get_preview_post_link($post_id, ['prepear_preview' => '1']);
            }
        }

        return '';
    }

    private static function get_mediavine_preview_url()
    {
        if ( class_exists( 'Mediavine\Create\Plugin' ) ) {

            $posts = get_posts( array(
                'post_type' => 'post',
                'orderby' => 'rand',
            ) );

            foreach ($posts as $post) {
                $content = $post->post_content;
                if(strpos($content, 'mv_create') !== false && strpos($content, 'type="recipe"') !== false ){
                    $post_id = $post->ID;
                    return get_preview_post_link($post_id, ['prepear_preview' => '1']);
                }
            }
        }

        return '';
    }

    private static function get_atts_for_shortcode($codeInfo)
    {
        $atts = isset($codeInfo['attrs']) ? $codeInfo['attrs'] : [];
        // add in missing default attributes
        $atts = shortcode_atts(WPRM_Template_Shortcodes::get_defaults($codeInfo['name']), $atts, str_replace('-', '_', $codeInfo['name']));
        if (!$atts['text_style']) {
            return $atts;
        }
        $classes = [
            'wprm-recipe-link',
            'wprm-block-text-' . $atts['text_style'],
            'wprm-recipe-icon',
        ];
        $style = 'color: ' . $atts['text_color'] . ' !important;';
        if ( 'text' !== $atts['style'] ) {
            $classes[] = 'wprm-recipe-link-' . $atts['style'];
            $classes[] = 'wprm-color-accent';
            $style .= 'background-color: ' . $atts['button_color'] . ';';
            $style .= 'border-color: ' . $atts['border_color'] . ';';
            $style .= 'border-radius: ' . $atts['border_radius'] . ';';
            $style .= 'padding: ' . $atts['vertical_padding'] . ' ' . $atts['horizontal_padding'] . ';';
        }
        else {
            $style .= 'background-color: initial; padding: 0;';
        }
        $atts['class'] = implode( ' ', $classes );
        $atts['style'] = $style;

        return $atts;
    }
}

?>