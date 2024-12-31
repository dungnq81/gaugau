<?php

/**
 * Themes functions
 *
 * @author Gau
 */

use Cores\Helper;

\defined('ABSPATH') || die;

// --------------------------------------------------
// Menu location
// --------------------------------------------------

add_action('after_setup_theme', '__after_setup_theme_action', 11);

function __after_setup_theme_action(): void
{
    register_nav_menus(
        [
            'main-nav'   => __('Primary Menu', TEXT_DOMAIN),
            'mobile-nav' => __('Handheld Menu', TEXT_DOMAIN),
            //'policy-nav' => __( 'Term menu', TEXT_DOMAIN ),
        ]
    );
}

// --------------------------------------------------
// Widget sidebar
// --------------------------------------------------

add_action('widgets_init', '__register_sidebars_action', 11);

function __register_sidebars_action(): void
{
    //----------------------------------------------------------
    // Homepage
    //----------------------------------------------------------

    register_sidebar(
        [
            'container'     => false,
            'id'            => 'home-sidebar',
            'name'          => __('Homepage', TEXT_DOMAIN),
            'description'   => __('Widgets added here will appear in homepage.', TEXT_DOMAIN),
            'before_widget' => '<div class="%2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span>',
            'after_title'   => '</span>',
        ]
    );

    //----------------------------------------------------------
    // Header
    //----------------------------------------------------------

    $top_header_cols    = (int) Helper::getThemeMod('top_header_setting');
    $header_cols        = (int) Helper::getThemeMod('header_setting');
    $bottom_header_cols = (int) Helper::getThemeMod('bottom_header_setting');

    if ($top_header_cols > 0) {
        for ($i = 1; $i <= $top_header_cols; $i++) {
            $_name = sprintf(__('Top-Header %d', TEXT_DOMAIN), $i);
            register_sidebar(
                [
                    'container'     => false,
                    'id'            => 'top-header-' . $i . '-sidebar',
                    'name'          => $_name,
                    'description'   => __('Widgets added here will appear in top header.', TEXT_DOMAIN),
                    'before_widget' => '<div class="header-widgets %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<span>',
                    'after_title'   => '</span>',
                ]
            );
        }
    }

    if ($header_cols > 0) {
        for ($i = 1; $i <= $header_cols; $i++) {
            $_name = sprintf(__('Header %d', TEXT_DOMAIN), $i);
            register_sidebar(
                [
                    'container'     => false,
                    'id'            => 'header-' . $i . '-sidebar',
                    'name'          => $_name,
                    'description'   => __('Widgets added here will appear in header.', TEXT_DOMAIN),
                    'before_widget' => '<div class="header-widgets %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<span>',
                    'after_title'   => '</span>',
                ]
            );
        }
    }

    if ($bottom_header_cols > 0) {
        for ($i = 1; $i <= $bottom_header_cols; $i++) {
            $_name = sprintf(__('Bottom-Header %d', TEXT_DOMAIN), $i);
            register_sidebar(
                [
                    'container'     => false,
                    'id'            => 'bottom-header-' . $i . '-sidebar',
                    'name'          => $_name,
                    'description'   => __('Widgets added here will appear in bottom header.', TEXT_DOMAIN),
                    'before_widget' => '<div class="header-widgets %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<span>',
                    'after_title'   => '</span>',
                ]
            );
        }
    }

    //----------------------------------------------------------
    // Footer
    //----------------------------------------------------------

    $footer_args = [];

    $rows    = (int) Helper::getThemeMod('footer_row_setting');
    $regions = (int) Helper::getThemeMod('footer_col_setting');

    for ($row = 1; $row <= $rows; $row++) {
        for ($region = 1; $region <= $regions; $region++) {
            $footer_n = $region + $regions * ($row - 1); // Defines footer sidebar ID.
            $footer   = sprintf('footer_%d', $footer_n);

            if (1 === $rows) {
                $footer_region_name        = sprintf(__('Footer-Column %1$d', TEXT_DOMAIN), $region);
                $footer_region_description = sprintf(__('Widgets added here will appear in column %1$d of the footer.', TEXT_DOMAIN), $region);
            } else {
                $footer_region_name        = sprintf(__('Footer-Row %1$d - Column %2$d', TEXT_DOMAIN), $row, $region);
                $footer_region_description = sprintf(__('Widgets added here will appear in column %1$d of footer row %2$d.', TEXT_DOMAIN), $region, $row);
            }

            $footer_args[$footer] = [
                'name'        => $footer_region_name,
                'id'          => sprintf('footer-%d-sidebar', $footer_n),
                'description' => $footer_region_description,
            ];
        }
    }

    foreach ($footer_args as $args) {
        $footer_tags = [
            'container'     => false,
            'before_widget' => '<div class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<p class="widget-title">',
            'after_title'   => '</p>',
        ];

        register_sidebar($args + $footer_tags);
    }

    //----------------------------------------------------------
    // Other ...
    //----------------------------------------------------------

    // Footer Credit
    register_sidebar(
        [
            'container'     => false,
            'id'            => 'footer-credit-sidebar',
            'name'          => __('Footer Credit', TEXT_DOMAIN),
            'description'   => __('Widgets added here will appear in footer.', TEXT_DOMAIN),
            'before_widget' => '<div class="%2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span>',
            'after_title'   => '</span>',
        ]
    );
}

// --------------------------------------------------
// Hook default scripts
// --------------------------------------------------

add_action('wp_default_scripts', '__wp_default_scripts');

function __wp_default_scripts($scripts): void
{
    if (isset($scripts->registered['jquery']) && ! is_admin()) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            // Check whether the script has any dependencies

            // remove jquery-migrate
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
}

// --------------------------------------------------
// Hook body_class
// --------------------------------------------------

add_filter('body_class', '__body_classes_filter', 11, 1);

function __body_classes_filter(array $classes): array
{
    // Check whether we're in the customizer preview.
    if (is_customize_preview()) {
        $classes[] = 'customizer-preview';
    }

    foreach ($classes as $class) {
        if (
            str_contains($class, 'wp-custom-logo')          ||
            str_contains($class, 'page-template-templates') ||
            str_contains($class, 'page-template-default')   ||
            str_contains($class, 'no-customize-support')    ||
            str_contains($class, 'page-id-')
        ) {
            $classes = array_diff($classes, [$class]);
        }
    }

    if (Helper::isHomeOrFrontPage() && Helper::isWoocommerceActive()) {
        $classes[] = 'woocommerce';
    }

    // ...
    $classes[] = 'default-mode';

    return $classes;
}

// --------------------------------------------------
// Hook post_class
// --------------------------------------------------

add_filter('post_class', '__post_classes_filter', 11, 1);

function __post_classes_filter(array $classes): array
{
    // remove_sticky_class
    if (in_array('sticky', $classes, false)) {
        $classes   = array_diff($classes, ['sticky']);
        $classes[] = 'wp-sticky';
    }

    // remove 'tag-', 'category-' classes
    foreach ($classes as $class) {
        if (
            str_contains($class, 'tag-') ||
            str_contains($class, 'category-')
        ) {
            $classes = array_diff($classes, [$class]);
        }
    }

    return $classes;
}

// --------------------------------------------------
// Filter nav_menu_css_class
// --------------------------------------------------

add_filter('nav_menu_css_class', '__nav_menu_css_classes_filter', 999, 4);

function __nav_menu_css_classes_filter($classes, $menu_item, $args, $depth): array
{
    if (! is_array($classes)) {
        $classes = [];
    }

    // Remove 'menu-item-type-', 'menu-item-object-' classes
    foreach ($classes as $class) {
        if (
            str_contains($class, 'menu-item-type-')   ||
            str_contains($class, 'menu-item-object-') ||
            str_contains($class, 'menu-item')         ||
            str_contains($class, 'menu-item-')
        ) {
            $classes = array_diff($classes, [$class]);
        }
    }

    if (1 === $menu_item->current || $menu_item->current_item_ancestor || $menu_item->current_item_parent) {
        $classes[] = 'active';
    }

    // li_class
    // li_depth_class

    if ($depth === 0) {
        if (! empty($args->li_class)) {
            $classes[] = $args->li_class;
        }

        return $classes;
    }

    if (! empty($args->li_depth_class)) {
        $classes[] = $args->li_depth_class;
    }

    return $classes;
}

// --------------------------------------------------
// Filter nav_menu_link_attributes
// --------------------------------------------------

add_filter('nav_menu_link_attributes', '__nav_menu_link_attributes_filter', 999, 4);

function __nav_menu_link_attributes_filter($atts, $menu_item, $args, $depth): array
{
    // link_class
    // link_depth_class

    if ($depth === 0) {
        if (property_exists($args, 'link_class')) {
            $atts['class'] = esc_attr($args->link_class);
        }
    } elseif (property_exists($args, 'link_depth_class')) {
        $atts['class'] = esc_attr($args->link_depth_class);
    }

    // menu_link_class
    if (! empty($menu_item->menu_link_class)) {
        //		if ( ! empty( $atts['class'] ) ) {
        //			$atts['class'] .= ' ' . esc_attr( $menu_item->menu_link_class );
        //		} else {
        //			$atts['class'] = esc_attr( $menu_item->menu_link_class );
        //		}

        $atts['class'] = esc_attr($menu_item->menu_link_class);
    }

    return $atts;
}

// --------------------------------------------------
// Filter nav_menu_item_title
// --------------------------------------------------

add_filter('nav_menu_item_title', '__nav_menu_item_title_filter', 999, 4);

function __nav_menu_item_title_filter($title, $item, $args, $depth)
{
    //	if ($args->theme_location === 'main-nav') {
    //		$title = '<span>' . $title . '</span>';
    //	}

    return $title;
}

// --------------------------------------------------
// Filter widget_tag_cloud_args
// --------------------------------------------------

add_filter('widget_tag_cloud_args', '__widget_tag_cloud_args_filter', 99);

function __widget_tag_cloud_args_filter($args): array
{
    $args['smallest'] = '10';
    $args['largest']  = '19';
    $args['unit']     = 'px';
    $args['number']   = 12;

    return $args;
}

// --------------------------------------------------
// query_vars
// --------------------------------------------------

add_filter('query_vars', '__query_vars', 99);

function __query_vars($vars): array
{
    $vars[] = 'page';
    $vars[] = 'paged';

    return $vars;
}

// --------------------------------------------------
// custom filter
// --------------------------------------------------

add_filter('addon_menu_options_page_filter', '__menu_options_page', 99);

function __menu_options_page(): array
{
    return [
        'aspect_ratio'   => __('Aspect Ratio', TEXT_DOMAIN),
        'smtp'           => __('SMTP', TEXT_DOMAIN),
        //'contact_info'   => __( 'Contact Info', TEXT_DOMAIN ),
        'contact_button' => __('Contact Button', TEXT_DOMAIN),
        'editor'         => __('Editor', TEXT_DOMAIN),
        'optimizer'      => __('Optimizer', TEXT_DOMAIN),
        'security'       => __('Security', TEXT_DOMAIN),
        'login_security' => __('Login Security', TEXT_DOMAIN),
        'social'         => __('Social', TEXT_DOMAIN),
        'file'           => __('File', TEXT_DOMAIN),
        'base_slug'      => __('Remove Base Slug', TEXT_DOMAIN),
        //'custom_email_from' => __( 'Custom Email From', TEXT_DOMAIN ),
        'custom_email'   => __('Custom Email To', TEXT_DOMAIN),
        'custom_sorting' => __('Custom Sorting', TEXT_DOMAIN),
        'recaptcha'      => __('reCAPTCHA', TEXT_DOMAIN),
        'woocommerce'    => __('WooCommerce', TEXT_DOMAIN),
        'custom_script'  => __('Custom Script', TEXT_DOMAIN),
        'custom_css'     => __('Custom CSS', TEXT_DOMAIN),
    ];
}

// --------------------------------------------------

add_filter('addon_theme_setting_options_filter', '__theme_setting_options', 99);

/**
 * @param array $arr
 *
 * @return array
 */
function __theme_setting_options(array $arr): array
{
    $arr_new = [
        // hide admin menu
        'admin_hide_menu'                     => [
            //'edit.php',
        ],

        // hide admin submenu
        'admin_hide_submenu'                  => [
            //			'options-general.php' => [
            //				'options-discussion.php',
            //				'options-privacy.php'
            //			]
        ],

        // ignore user
        'admin_hide_menu_ignore_user'         => [1],

        // defer, delay script - default 5s.
        'defer_script'                        => [
            // defer.
            'contact-form-7' => 'defer',

            // delay.
            'comment-reply'  => 'delay',
            'wp-embed'       => 'delay',
        ],

        // defer style.
        'defer_style'                         => [
            'dashicons',
            'contact-form-7',
        ],

        // Aspect Ratio - custom post-type and term.
        'aspect_ratio_post_type_term'         => [
            'post',
        ],

        // Aspect Ratio default.
        'aspect_ratio_default'                => [
            '1-1',
            '2-1',
            '3-2',
            '4-3',
            '16-9',
            '21-9',
        ],

        // Add ID to the admin category page.
        'term_row_actions'                    => [
            'category',
            'post_tag',
        ],

        // Add ID to the admin post-page.
        'post_row_actions'                    => [
            'user',
            'post',
            'page',
        ],

        // Terms thumbnail (term_thumb).
        'term_thumb_columns'                  => [
            'category',
            'post_tag',
        ],

        // Exclude thumb post_type columns.
        'post_type_exclude_thumb_columns'     => [],

        // ACF attributes in menu locations.
        'acf_menu_items_locations'            => [
            'main-nav',
        ],

        // ACF attributes 'mega menu' locations.
        'acf_mega_menu_locations'             => [],

        // Custom post_per_page.
        'posts_num_per_page'                  => [],

        // Custom post-type & taxonomy.
        'post_type_terms'                     => [],

        // smtp_plugins_support.
        'smtp_plugins_support'                => [
            'wp_mail_smtp'     => 'wp-mail-smtp/wp_mail_smtp.php',
            'wp_mail_smtp_pro' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
            'smtp_mailer'      => 'smtp-mailer/main.php',
            'gmail_smtp'       => 'gmail-smtp/main.php',
            'fluent-smtp'      => 'fluent-smtp/fluent-smtp.php',
        ],

        // language_plugins_support
        'language_plugins_support'            => [
            'polylang'     => 'polylang/polylang.php',
            'polylang_pro' => 'polylang-pro/polylang.php',
            'wpglobus'     => 'wpglobus/wpglobus.php',
        ],

        // Custom Email list (to).
        'custom_emails'                       => [],

        // lazy_load_exclude.
        'lazy_load_exclude_css_class'         => [
            'no-lazy',
            'skip-lazy',
        ],

        // The urls where a lazy load is excluded.
        'lazy_load_exclude_urls'              => [
            'no-lazy',
            'skip-lazy',
        ],

        // List of admin IDs allowed to install plugins.
        'allowed_users_ids_install_plugins'   => [1],

        // List of user IDs that are not allowed to be deleted.
        'disallowed_users_ids_delete_account' => [1],

        // Login security
        'login_security'                      => [
            'custom_login_uri'            => '', // Custom admin-login URI.
            'enable_custom_login_options' => false, // Allows customization of the Login URL in the admin options.
            'allowlist_ips_login_access'  => [], // Allowlist IPs Login Access
            'blocked_ips_login_access'    => [], // Blocked IPs Access
        ],

        // Links social.
        'social_follows_links'                => [
            'facebook'  => [
                'name'  => 'Facebook',
                'icon'  => 'fa-brands fa-facebook',
                'color' => '#0866FF',
                'url'   => '',
            ],
            'instagram' => [
                'name'  => 'Instagram',
                'icon'  => 'fa-brands fa-instagram',
                'color' => 'rgb(224, 241, 255)',
                'url'   => '',
            ],
            'youtube'   => [
                'name'  => 'Youtube',
                'icon'  => 'fa-brands fa-youtube',
                'color' => 'rgb(255, 0, 0)',
                'url'   => '',
            ],
            'twitter'   => [
                'name'  => 'X (Twitter)',
                'icon'  => 'fa-brands fa-x-twitter',
                'color' => 'rgb(239, 243, 244)',
                'url'   => '',
            ],
            'tiktok'    => [
                'name'  => 'Tiktok',
                'icon'  => 'fa-brands fa-tiktok',
                'color' => 'rgba(255, 255, 255, 0.9)',
                'url'   => '',
            ],
            'telegram'  => [
                'name'  => 'Telegram',
                'icon'  => 'fa-brands fa-telegram',
                'color' => '#2BA0E5',
                'url'   => '',
            ],
            'linkedin'  => [
                'name'  => 'Linkedin',
                'icon'  => 'fa-brands fa-linkedin',
                'color' => '#0a66c2',
                'url'   => '',
            ],
            'zalo'      => [
                'name'  => 'Zalo',
                'icon'  => THEME_URL . 'assets/img/zlogo.png',
                'color' => '#0068FF',
                'url'   => 'https://chat.zalo.me/?phone=xxx',
            ],
            //			'skype'     => [
            //				'name'  => 'Skype',
            //				'icon'  => 'fa-brands fa-skype',
            //				'color' => '#0092E0',
            //				'url'   => '',
            //			],
            'hotline'   => [
                'name'  => 'Hotline',
                'icon'  => 'fa-solid fa-phone',
                'color' => '',
                'url'   => '',
            ],
            'email'     => [
                'name'  => 'Email',
                'icon'  => 'fa-solid fa-envelope',
                'color' => '',
                'url'   => '',
            ],
        ],

        // Other setting ( file upload, v.v... )
        'file_settings'                       => [
            'upload_size_limit' => [
                'name'  => 'Maximum upload file size',
                'value' => 2, // MB
            ],
        ],

        //----------------------------------------------------------
        // Custom ...
        //----------------------------------------------------------
    ];

    // --------------------------------------------------

    if (Helper::isWoocommerceActive()) {
        $arr_new['aspect_ratio_post_type_term'][]     = 'product';
        $arr_new['aspect_ratio_post_type_term'][]     = 'product_cat';
        $arr_new['term_row_actions'][]                = 'product_cat';
        $arr_new['post_type_exclude_thumb_columns'][] = 'product';
        $arr_new['post_type_terms'][]                 = ['product' => 'product_cat'];
    }

    if (Helper::isCf7Active()) {
        $arr_new['post_type_exclude_thumb_columns'][] = 'wpcf7_contact_form';
    }

    return array_merge($arr, $arr_new);
}
