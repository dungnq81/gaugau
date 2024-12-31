<?php

namespace Themes;

use Cores\CSS;
use Cores\Helper;
use Cores\Traits\Singleton;
use Plugins\ACF;
use Plugins\CF7;
use Plugins\PLL;
use Plugins\WooCommerce;

\defined('ABSPATH') || die;

/**
 * Theme Class
 *
 * @author Gau
 */
final class Theme
{
    use Singleton;

    // --------------------------------------------------

    private function init(): void
    {
        // plugins_loaded -> after_setup_theme -> init -> rest_api_init -> widgets_init -> wp_loaded -> admin_menu -> admin_init ...

        /** Login page */
        $this->_admin_login();

        add_action('after_setup_theme', [$this, 'i18n'], 10);
        add_action('after_setup_theme', [$this, 'after_setup_theme'], 11);
        add_action('after_setup_theme', [$this, 'setup'], 12);
        add_action('after_setup_theme', [$this, 'plugins_setup'], 13);

        /** Enqueue Scripts */
        add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts'], 14);

        /** Restrict admin install plugin */
        add_filter('user_has_cap', [$this, 'restrict_admin_plugin_install'], 10, 3);

        /** Prevent the deletion of admin accounts */
        add_filter('user_has_cap', [$this, 'prevent_deletion_admin_accounts'], 10, 3);
        add_action('delete_user', [$this, 'prevent_deletion_user'], 10);

        /** Widgets WordPress */
        add_action('widgets_init', [$this, 'unregister_widgets'], 16);
        add_action('widgets_init', [$this, 'register_widgets'], 17);
    }

    // --------------------------------------------------

    /**
     * Load localization file
     *
     * @return void
     */
    public function i18n(): void
    {
        /**
         * Make the theme available for translation.
         * Translations can be filed at WordPress.org.
         */
        load_theme_textdomain(TEXT_DOMAIN, trailingslashit(WP_LANG_DIR) . 'themes/');
        load_theme_textdomain(TEXT_DOMAIN, get_template_directory() . '/languages');
        load_theme_textdomain(TEXT_DOMAIN, get_stylesheet_directory() . '/languages');
    }

    // --------------------------------------------------

    /**
     * Sets up theme defaults and register support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which runs before the init hook.
     * The init hook is too late for some features, such as indicating support for post-thumbnails.
     */
    public function after_setup_theme(): void
    {
        /** Add theme support for various features. */
        add_theme_support('automatic-feed-links');
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support('html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'script',
        ]);

        add_theme_support('customize-selective-refresh-widgets');
        add_theme_support('align-wide');
        add_theme_support('wp-block-styles');

        /** This theme styles the visual editor to resemble the theme style. */
        add_editor_style();

        /** Remove Template Editor support until WP 5.9 since more Theme Blocks are going to be introduced. */
        remove_theme_support('block-templates');

        /** Enable excerpt to page, page-attributes to post */
        add_post_type_support('page', ['excerpt']);
        add_post_type_support('post', ['page-attributes']);

        /** Set default values for the upload media box */
        update_option('image_default_align', 'center');
        update_option('image_default_size', 'large');

        /**
         * Add support for the core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        $logo_height = 240;
        $logo_width  = 240;

        add_theme_support(
            'custom-logo',
            apply_filters(
                'custom_logo_args_filter',
                [
                    'height'               => $logo_height,
                    'width'                => $logo_width,
                    'flex-height'          => true,
                    'flex-width'           => true,
                    'header-text'          => '',
                    'unlink-homepage-logo' => true,
                ]
            )
        );
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    public function setup(): void
    {
        if (is_admin()) {
            (Admin::get_instance());
        }

        (Customizer::get_instance());
        (Optimizer::get_instance());
        (Shortcode::get_instance());

        /** template hooks */
        $this->_hooks();

        // folders
        $dirs = [
            'template_structures' => THEME_PATH . 'template-structures',
            'templates'           => THEME_PATH . 'templates',
            'template_parts'      => THEME_PATH . 'template-parts',
            'storage'             => THEME_PATH . 'storage',
            'languages'           => THEME_PATH . 'languages',

            'inc_ajax'   => INC_PATH . 'ajax',
            'inc_blocks' => INC_PATH . 'blocks',
        ];

        foreach ($dirs as $dir => $path) {
            Helper::createDirectory($path);

            // autoload template_structures & ajax files
            if (in_array($dir, ['template_structures', 'inc_ajax'])) {
                Helper::FQNLoad($path, true);
            }
        }
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    public function plugins_setup(): void
    {
        Helper::isWoocommerceActive() && WooCommerce\WooCommerce::get_instance();
        Helper::isAcfActive()         && ACF\ACF::get_instance();
        Helper::isCf7Active()         && CF7::get_instance();
        Helper::isPolylangActive()    && PLL::get_instance();
    }

    // --------------------------------------------------

    /**
     * Registers a WP_Widget widget
     *
     * @return void
     */
    public function register_widgets(): void
    {
        $widgets_dir = INC_PATH . 'src/Widgets';
        $FQN         = '\\Widgets\\';

        Helper::createDirectory($widgets_dir);
        Helper::FQNLoad($widgets_dir, false, true, $FQN, true);
    }

    // --------------------------------------------------

    /**
     * Unregister a WP_Widget widget
     *
     * @return void
     */
    public function unregister_widgets(): void
    {
        unregister_widget('WP_Widget_Search');
        unregister_widget('WP_Widget_Recent_Posts');

        // Removes the styling added to the header for recent comments
        global $wp_widget_factory;
        remove_action('wp_head', [
            $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
            'recent_comments_style',
        ]);
    }

    // --------------------------------------------------

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function wp_enqueue_scripts(): void
    {
        $version = THEME_VERSION;
        if (WP_DEBUG) {
            $version = date('YmdHis', current_time('U', 0));
        }

        /** Stylesheet */
        wp_enqueue_style('app-style', ASSETS_URL . 'css/app.css', [], $version);
        wp_enqueue_style('fonts-style', ASSETS_URL . 'css/fonts.css', [], $version);

        /** Scripts */
        wp_enqueue_script('modulepreload', ASSETS_URL . 'js/modulepreload-polyfill.js', [], $version, true);
        wp_enqueue_script('app', ASSETS_URL . 'js/app2.js', ['jquery-core'], $version, true);

        wp_script_add_data('modulepreload', 'module', true);
        wp_script_add_data('app', 'extra', ['module', 'defer']);

        wp_add_inline_script('jquery-core', 'Object.assign(window, { $: jQuery, jQuery });', 'after');

        /** Inline Js */
        $recaptcha_options     = Helper::getOption('recaptcha__options');
        $recaptcha_v2_site_key = $recaptcha_options['recaptcha_v2_site_key'] ?? '';
        $recaptcha_v3_site_key = $recaptcha_options['recaptcha_v3_site_key'] ?? '';

        $l10n = [
            'ajaxUrl'     => esc_js(admin_url('admin-ajax.php', 'relative')),
            'baseUrl'     => esc_js(untrailingslashit(site_url()) . '/'),
            'themeUrl'    => esc_js(THEME_URL),
            '_csrf_token' => wp_create_nonce('wp_csrf_token'),
            '_wpnonce'    => wp_create_nonce('wp_rest'),
            'locale'      => esc_js(get_locale()),
            'lang'        => esc_js(Helper::getLang()),
        ];

        if ($recaptcha_v2_site_key) {
            $l10n['recaptcha_v2_site_key'] = esc_js($recaptcha_v2_site_key);
        }

        if ($recaptcha_v3_site_key) {
            $l10n['recaptcha_v3_site_key'] = esc_js($recaptcha_v3_site_key);
        }

        wp_localize_script('jquery-core', Helper::snakeCase(TEXT_DOMAIN), $l10n);

        /** Comments */
        if (is_singular() && comments_open() && Helper::getOption('thread_comments')) {
            wp_enqueue_script('comment-reply');
        } else {
            wp_dequeue_script('comment-reply');
        }
    }

    // --------------------------------------------------

    /**
     * @param $allcaps
     * @param $caps
     * @param $args
     *
     * @return mixed
     */
    public function restrict_admin_plugin_install($allcaps, $caps, $args): mixed
    {
        $allowed_users_ids_install_plugins = Helper::filterSettingOptions('allowed_users_ids_install_plugins', []);

        // Get the current user ID
        $user_id = get_current_user_id();

        // Check if the current user is in the allowed users list
        if ($user_id && in_array($user_id, $allowed_users_ids_install_plugins, false)) {
            return $allcaps;
        }

        // If a user is not allowed, remove the ability to install plugins
        if (isset($allcaps['activate_plugins'])) {
            unset($allcaps['install_plugins'], $allcaps['delete_plugins']);
        }

        if (isset($allcaps['install_themes'])) {
            unset($allcaps['install_themes']);
        }

        return $allcaps;
    }

    // --------------------------------------------------

    /**
     * @param $allcaps
     * @param $cap
     * @param $args
     *
     * @return mixed
     */
    public function prevent_deletion_admin_accounts($allcaps, $cap, $args): mixed
    {
        $disallowed_users_ids_delete_account = Helper::filterSettingOptions('disallowed_users_ids_delete_account', []);
        if (isset($cap[0]) && $cap[0] === 'delete_users') {
            $user_id_to_delete = $args[2] ?? 0;

            if ($user_id_to_delete && in_array($user_id_to_delete, $disallowed_users_ids_delete_account, true)) {
                unset($allcaps['delete_users']);
            }
        }

        return $allcaps;
    }

    // --------------------------------------------------

    /**
     * @param $user_id
     *
     * @return void
     */
    public function prevent_deletion_user($user_id): void
    {
        $disallowed_users_ids_delete_account = Helper::filterSettingOptions('disallowed_users_ids_delete_account', []);
        if (in_array($user_id, $disallowed_users_ids_delete_account, false)) {
            Helper::wpDie(
                __('You cannot delete this admin account.', TEXT_DOMAIN),
                __('Error', TEXT_DOMAIN),
                ['response' => 403]
            );
        }
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    private function _hooks(): void
    {
        // -------------------------------------------------------------
        // images sizes
        // -------------------------------------------------------------

        /**
         * thumbnail (540x0)
         * medium (768x0)
         * large (1024x0)
         *
         * small-thumbnail (150x150)
         * widescreen (1920x9999)
         * post-thumbnail (1280x9999)
         */

        /** Custom thumb */
        add_image_size('small-thumbnail', 150, 150, true);
        add_image_size('widescreen', 1920, 9999, false);
        add_image_size('post-thumbnail', 1200, 9999, false);

        /** Disable unwanted image sizes */
        add_filter('intermediate_image_sizes_advanced', static function ($sizes) {
            unset($sizes['medium_large'], $sizes['1536x1536'], $sizes['2048x2048']);

            // disable 2x medium-large size
            // disable 2x large size

            return $sizes;
        });

        /** Disable scaled */
        //add_filter( 'big_image_size_threshold', '__return_false' );

        /** Disable other sizes */
        add_action('init', static function () {
            remove_image_size('1536x1536'); // disable 2x medium-large size
            remove_image_size('2048x2048'); // disable 2x large size
        });

        // ------------------------------------------

        add_filter('post_thumbnail_html', static function ($html) {
            return preg_replace('/(<img[^>]+)(style=\"[^\"]+\")([^>]+)(>)/', '${1}${3}${4}', $html);
        }, 10, 1);

        //		add_filter( 'image_send_to_editor', function ( $html ) {
        //			return preg_replace( '/(<img[^>]+)(style=\"[^\"]+\")([^>]+)(>)/', '${1}${3}${4}', $html );
        //		}, 10, 1 );

        add_filter('the_content', static function ($html) {
            return preg_replace('/(<img[^>]+)(style=\"[^\"]+\")([^>]+)(>)/', '${1}${3}${4}', $html);
        }, 10, 1);
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    private function _admin_login(): void
    {
        add_action('login_enqueue_scripts', [$this, 'login_enqueue_script'], 31);

        // Changing the alt text on the logo to show your site name
        add_filter('login_headertext', static function () {
            $headertext = Helper::getThemeMod('login_page_headertext_setting');

            return $headertext ?: get_bloginfo('name');
        });

        // Changing the logo link from WordPress.org to your site
        add_filter('login_headerurl', static function () {
            $headerurl = Helper::getThemeMod('login_page_headerurl_setting');

            return $headerurl ?: Helper::home('/');
        });
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    public function login_enqueue_script(): void
    {
        wp_enqueue_style('login-style', THEME_URL . 'assets/css/admin.css', [], THEME_VERSION);
        wp_enqueue_script('login', THEME_URL . 'assets/js/login.js', ['jquery'], THEME_VERSION, true);
        wp_script_add_data('login', 'module', true);

        // $default_logo    = THEME_URL . "storage/img/logo.png";
        // $default_logo_bg = THEME_URL . "storage/img/login-bg.jpg";

        $default_logo    = '';
        $default_logo_bg = '';

        // script / style
        $logo          = ! empty($logo = Helper::getThemeMod('login_page_logo_setting')) ? $logo : $default_logo;
        $logo_bg       = ! empty($logo_bg = Helper::getThemeMod('login_page_bgimage_setting')) ? $logo_bg : $default_logo_bg;
        $logo_bg_color = Helper::getThemeMod('login_page_bgcolor_setting');

        $css = CSS::get_instance();

        if ($logo_bg) {
            $css->set_selector('body.login');
            $css->add_property('background-image', 'url(' . $logo_bg . ')');
        }

        if ($logo_bg_color) {
            $css->set_selector('body.login');
            $css->add_property('background-color', $logo_bg_color);

            $css->set_selector('body.login:before');
            $css->add_property('background', 'none');
            $css->add_property('opacity', 1);
        }

        $css->set_selector('body.login #login h1 a');
        if ($logo) {
            $css->add_property('background-image', 'url(' . $logo . ')');
        }

        $css_output = $css->css_output();
        if ($css_output) {
            wp_add_inline_style('login-style', $css_output);
        }
    }
}
