<?php

use Detection\Exception\MobileDetectException;
use Detection\MobileDetect;
use MatthiasMullie\Minify;
use Vectorface\Whip\Whip;

defined('ABSPATH') || die;

/** ----------------------------------------------- */

if (! function_exists('is_valid_phone')) {
    /**
     * @param $phone
     *
     * @return bool
     */
    function is_valid_phone($phone): bool
    {
        if (! is_string($phone) || trim($phone) === '') {
            return false;
        }

        $pattern = '/^\(?\+?(0|84?)\)?[\s.-]?(3[2-9]|5[689]|7[06-9]|8[0-689]|9[0-4|6-9])(\d{7}|\d[\s.-]?\d{3}[\s.-]?\d{3})$/';

        return preg_match($pattern, $phone) === 1;
    }
}

/** ----------------------------------------------- */

if (! function_exists('is_xml')) {
    /**
     * @param $content
     *
     * @return false|int
     */
    function is_xml($content): false|int
    {
        // Get the first 200 chars of the file to make the preg_match check faster.
        $xml_part = substr($content, 0, 20);

        return preg_match('/<\?xml version="/', $xml_part);
    }
}

/** ----------------------------------------------- */

if (! function_exists('is_amp_enabled')) {
    /**
     * @param $html
     *
     * @return false|int
     */
    function is_amp_enabled($html): false|int
    {
        // Get the first 200 chars of the file to make the preg_match check faster.
        $is_amp = substr($html, 0, 200);

        // Checks if the document is containing the amp tag.
        return preg_match('/<html[^>]+(amp|⚡)[^>]*>/u', $is_amp);
    }
}

/** ----------------------------------------------- */

if (! function_exists('is_mobile')) {
    /**
     * Test if the current browser runs on a mobile device (smartphone, tablet, etc.)
     *
     * @throws MobileDetectException
     *
     * @return boolean
     */
    function is_mobile(): bool
    {
        if (class_exists(MobileDetect::class)) {
            return (new MobileDetect())->isMobile();
        }

        return wp_is_mobile();
    }
}

/** ----------------------------------------------- */

if (! function_exists('get_current_url')) {
    /**
     * Get the current url.
     *
     * @return string The current url.
     */
    function get_current_url(): string
    {
        // Return an empty string if it is not an HTTP request.
        if (! isset($_SERVER['HTTP_HOST'])) {
            return '';
        }

        $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

        // Build the current url.
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

/** ----------------------------------------------- */

if (! function_exists('lighthouse')) {
    /**
     * @return bool
     */
    function lighthouse(): bool
    {
        $header = $_SERVER['HTTP_USER_AGENT'];

        return mb_strpos($header, 'Lighthouse', 0, 'UTF-8') !== false;
    }
}

/** ----------------------------------------------- */

if (! function_exists('extract_js')) {
    /**
     * @param string $content
     *
     * @return string
     */
    function extract_js(string $content = ''): string
    {
        $script_pattern = '/<script\b[^>]*>(.*?)<\/script>/is';
        preg_match_all($script_pattern, $content, $matches);

        // Initialize an array to hold the non-empty <script> tags or those with src attribute
        $valid_scripts = [];

        // Define patterns for detecting potentially malicious code or encoding
        $malicious_patterns = [
            '/eval\(/i',            // Use of eval()
            '/document\.write\(/i', // Use of document.write()
            '/<script.*?src=[\'"]?data:/i', // Inline scripts with data URIs
            '/base64,/i',           // Base64 encoding
        ];

        // Loop through all matched <script> tags
        foreach ($matches[0] as $index => $scriptTag) {
            $scriptContent = trim($matches[1][ $index ]);
            $hasSrc        = preg_match('/\bsrc=["\'].*?["\']/', $scriptTag);

            // Check if the script content is not malicious
            $isMalicious = false;
            foreach ($malicious_patterns as $pattern) {
                if (preg_match($pattern, $scriptContent)) {
                    $isMalicious = true;

                    break;
                }
            }

            if (! $isMalicious && ($scriptContent !== '' || $hasSrc)) {
                $valid_scripts[] = $scriptTag;
            }
        }

        // Replace original <script> tags in the content with the valid ones
        return preg_replace_callback($script_pattern, static function ($match) use ($valid_scripts) {
            static $i = 0;

            return isset($valid_scripts[ $i ]) ? $valid_scripts[ $i++ ] : '';
        }, $content);
    }
}

/** ----------------------------------------------- */

if (! function_exists('explode_multi')) {
    /**
     * @param $delimiters
     * @param $string
     * @param bool $remove_empty
     *
     * @return mixed|string[]
     */
    function explode_multi($delimiters, $string, bool $remove_empty = true): mixed
    {
        if (is_string($delimiters)) {
            return explode($delimiters, $string);
        }

        if (is_array($delimiters)) {
            $ready  = str_replace($delimiters, $delimiters[0], $string);
            $launch = explode($delimiters[0], $ready);
            if ($remove_empty) {
                $launch = array_filter($launch);
            }

            return $launch;
        }

        return $string;
    }
}

/** ----------------------------------------------- */

if (! function_exists('message_success')) {
    /**
     * @param $message
     * @param bool $auto_hide
     *
     * @return void
     */
    function message_success($message, bool $auto_hide = false): void
    {
        $message = $message ?: 'Values saved';
        $message = __($message, ADDONS_TEXT_DOMAIN);

        $class = 'notice notice-success is-dismissible';
        if ($auto_hide) {
            $class .= ' dismissible-auto';
        }

        printf('<div class="%1$s"><p><strong>%2$s</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', esc_attr($class), $message);
    }
}

/** ----------------------------------------------- */

if (! function_exists('message_error')) {
    /**
     * @param $message
     * @param bool $auto_hide
     *
     * @return void
     */
    function message_error($message, bool $auto_hide = false): void
    {
        $message = $message ?: 'Values error';
        $message = __($message, ADDONS_TEXT_DOMAIN);

        $class = 'notice notice-error is-dismissible';
        if ($auto_hide) {
            $class .= ' dismissible-auto';
        }

        printf('<div class="%1$s"><p><strong>%2$s</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', esc_attr($class), $message);
    }
}

/** ----------------------------------------------- */

if (! function_exists('in_array_checked')) {
    /**
     * Conditionally adds an HTML attribute based on array membership.
     *
     * @param array $checked_arr
     * @param $current
     * @param bool $display
     * @param string $type
     *
     * @return string|null
     */
    function in_array_checked(array $checked_arr, $current, bool $display = true, string $type = 'checked'): ?string
    {
        $type   = preg_match('/^[a-zA-Z0-9\-]+$/', $type) ? $type : 'checked';
        $result = in_array($current, $checked_arr, false) ? " $type='$type'" : '';

        // Echo or return the result
        if ($display) {
            echo $result;

            return null;
        }

        return $result;
    }
}

/** ----------------------------------------------- */

if (! function_exists('redirect')) {
    /**
     * @param string $uri
     * @param int $status
     *
     * @return true|void
     */
    function redirect(string $uri = '', int $status = 301)
    {
        if (! headers_sent()) {
            wp_redirect($uri, $status);
        } else {
            echo '<script>window.location.href="' . $uri . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . $uri . '" /></noscript>';

            return true;
        }
    }
}

/** ----------------------------------------------- */

if (! function_exists('js_minify')) {
    /**
     * @param $js
     * @param bool $debug_check
     *
     * @return mixed|string
     */
    function js_minify($js, bool $debug_check = true): mixed
    {
        if (empty($js)) {
            return $js;
        }

        if ($debug_check && WP_DEBUG) {
            return $js;
        }

        if (class_exists(Minify\JS::class)) {
            return (new Minify\JS())->add($js)->minify();
        }

        return $js;
    }
}

/** ----------------------------------------------- */

if (! function_exists('css_minify')) {
    /**
     * @param $css
     * @param bool $debug_check
     *
     * @return string
     */
    function css_minify($css, bool $debug_check = true): string
    {
        if (empty($css)) {
            return $css;
        }

        if ($debug_check && WP_DEBUG) {
            return $css;
        }

        if (class_exists(Minify\CSS::class)) {
            return (new Minify\CSS())->add($css)->minify();
        }

        return $css;
    }
}

/** ----------------------------------------------- */

if (! function_exists('get_custom_post_option_content')) {
    /**
     * @param string $post_type - max 20 characters
     * @param bool $encode
     *
     * @return array|string
     */
    function get_custom_post_option_content(string $post_type, bool $encode = false): array|string
    {
        if (empty($post_type)) {
            return '';
        }

        $post = \get_custom_post_option($post_type);
        if (isset($post->post_content)) {
            $post_content = wp_unslash($post->post_content);
            if ($encode) {
                $post_content = wp_unslash(base64_decode($post->post_content));
            }

            return $post_content;
        }

        return '';
    }
}

/** ----------------------------------------------- */

if (! function_exists('get_custom_post_option')) {
    /**
     * @param string $post_type - max 20 characters
     *
     * @return array|WP_Post|null
     */
    function get_custom_post_option(string $post_type): array|WP_Post|null
    {
        if (empty($post_type)) {
            return null;
        }

        $custom_query_vars = [
            'post_type'              => $post_type,
            'post_status'            => get_post_stati(),
            'posts_per_page'         => 1,
            'no_found_rows'          => true,
            'cache_results'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'lazy_load_term_meta'    => false,
        ];

        $post    = null;
        $post_id = get_theme_mod($post_type . '_option_id');

        if ($post_id > 0 && get_post($post_id)) {
            $post = get_post($post_id);
        }

        // `-1` indicates no post exists; no query necessary.
        if (! $post && -1 !== $post_id) {
            $post = (new \WP_Query($custom_query_vars))->post;

            set_theme_mod($post_type . '_option_id', $post->ID ?? -1);
        }

        return $post;
    }
}

/** ----------------------------------------------- */

if (! function_exists('update_custom_post_option')) {
    /**
     * @param string $mixed
     * @param string $post_type - max 20 characters
     * @param string $code_type
     * @param bool $encode
     * @param string $preprocessed
     *
     * @return array|int|WP_Error|WP_Post|null
     */
    function update_custom_post_option(string $mixed = '', string $post_type = 'addon_css', string $code_type = 'css', bool $encode = false, string $preprocessed = ''): WP_Error|array|int|WP_Post|null
    {
        $post_type = $post_type ?: 'addon_css';
        $code_type = $code_type ?: 'text/css';

        if (in_array($code_type, ['css', 'text/css'])) {
            $mixed = strip_all_tags($mixed, true, false);
        }

        if ($encode) {
            $mixed = base64_encode($mixed);
        }

        $post_data = [
            'post_type'             => $post_type,
            'post_status'           => 'publish',
            'post_content'          => $mixed,
            'post_content_filtered' => $preprocessed,
        ];

        // Update 'post' if it already exists, otherwise create a new one.
        $post = \get_custom_post_option($post_type);
        if ($post) {
            $post_data['ID'] = $post->ID;
            $r               = wp_update_post(wp_slash($post_data), true);
        } else {
            $post_data['post_title'] = $post_type . '_post_title';
            $post_data['post_name']  = wp_generate_uuid4();
            $r                       = wp_insert_post(wp_slash($post_data), true);

            if (! is_wp_error($r)) {
                set_theme_mod($post_type . '_option_id', $r);

                // Trigger creation of a revision. This should be removed once #30854 is resolved.
                $revisions = wp_get_latest_revision_id_and_total_count($r);
                if (! is_wp_error($revisions) && 0 === $revisions['count']) {
                    $revision = wp_save_post_revision($r);
                }
            }
        }

        if (is_wp_error($r)) {
            return $r;
        }

        return get_post($r);
    }
}

/** ----------------------------------------------- */

if (! function_exists('strip_all_tags')) {
    /**
     * @param $string
     * @param bool $remove_js_css
     * @param bool $flatten
     * @param null $allowed_tags
     *
     * @return string
     */
    function strip_all_tags($string, bool $remove_js_css = true, bool $flatten = true, $allowed_tags = null): string
    {
        if (! is_scalar($string)) {
            return '';
        }

        if ($remove_js_css) {
            $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', ' ', $string);
        }

        $string = strip_tags($string, $allowed_tags);

        if ($flatten) {
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        }

        return trim($string);
    }
}

/** ----------------------------------------------- */

if (! function_exists('filter_setting_options')) {
    /**
     * @param $name
     * @param mixed $default
     *
     * @return array|mixed
     */
    function filter_setting_options($name, mixed $default = []): mixed
    {
        $filters = apply_filters('addon_theme_setting_options_filter', []);

        if (isset($filters[ $name ])) {
            return $filters[ $name ] ?: $default;
        }

        return [];
    }
}

/** ----------------------------------------------- */

if (! function_exists('capitalized_slug')) {
    /**
     * @param $slug
     *
     * @return string
     */
    function capitalized_slug($slug): string
    {
        $words            = preg_split('/[_-]/', $slug);
        $capitalizedWords = array_map('ucfirst', $words);

        if (str_contains($slug, '_')) {
            return implode('_', $capitalizedWords);
        }

        return implode('-', $capitalizedWords);
    }
}

/** ----------------------------------------------- */

if (! function_exists('mb_ucfirst')) {
    /**
     * @param string $str
     * @param string|null $encoding
     *
     * @return string
     */
    function mb_ucfirst(string $str, string $encoding = null): string
    {
        if (is_null($encoding)) {
            $encoding = mb_internal_encoding();
        }

        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, null, $encoding);
    }
}

/** ----------------------------------------------- */

if (! function_exists('htaccess')) {
    /**
     * @return bool
     */
    function htaccess(): bool
    {
        global $is_apache;

        if ($is_apache) {
            return true;
        }

        // ?
        if (isset($_SERVER['HTACCESS']) && 'on' === $_SERVER['HTACCESS']) {
            return true;
        }

        return false;
    }
}

/** ----------------------------------------------- */

if (! function_exists('ip_address')) {
    /**
     * Get the IP address from which the user is viewing the current page.
     *
     * @return string
     */
    function ip_address(): string
    {
        if (class_exists('Whip')) {
            // Use a Whip library to get the valid IP address
            $clientAddress = (new Whip(Whip::ALL_METHODS))->getValidIpAddress();
            if (false !== $clientAddress) {
                return preg_replace('/^::1$/', '127.0.0.1', $clientAddress);
            }
        } else {
            // Check for CloudFlare's connecting IP
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                return $_SERVER['HTTP_CF_CONNECTING_IP'];
            }

            // Check for forwarded IP (proxy) and get the first valid IP
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }

            // Check for client IP
            if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                return $_SERVER['HTTP_CLIENT_IP'];
            }

            // Fallback to a remote address
            if (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
                return $_SERVER['REMOTE_ADDR'];
            }
        }

        // Fallback to localhost IP
        return '127.0.0.1';
    }
}

/** ----------------------------------------------- */

if (! function_exists('clear_all_cache')) {
    /**
     * @return void
     */
    function clear_all_cache(): void
    {
        global $wpdb;

        // LiteSpeed cache
        if (class_exists(\LiteSpeed\Purge::class)) {
            \LiteSpeed\Purge::purge_all();
        }

        // WP-Rocket cache
        if (defined('WP_ROCKET_PATH') && function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        // Clearly minified CSS and JavaScript files (WP-Rocket)
        if (function_exists('rocket_clean_minify')) {
            rocket_clean_minify();
        }

        // Jetpack transient cache
        if (\check_plugin_active('jetpack/jetpack.php')) {
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_jetpack_%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_jetpack_%'");

            // Clear Jetpack Photon cache locally
            if (class_exists(\Jetpack_Photon::class)) {
                \Jetpack_Photon::instance()->purge_cache();
            }
        }

        // Clear all WordPress transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%'");

        // Clear object cache (e.g., Redis or Memcached)
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}

/** ----------------------------------------------- */

if (! function_exists('check_plugin_active')) {
    /**
     * Check if the plugin is installed
     *
     * @param $plugin_slug
     *
     * @return bool
     */
    function check_plugin_active($plugin_slug): bool
    {
        return \check_plugin_installed($plugin_slug) &&
               is_plugin_active($plugin_slug);
    }
}

/** ----------------------------------------------- */

if (! function_exists('check_plugin_installed')) {
    /**
     * Check if plugin is installed by getting all plugins from the plugins dir
     *
     * @param $plugin_slug
     *
     * @return bool
     */
    function check_plugin_installed($plugin_slug): bool
    {
        // Check if the necessary functions exist - if not, require them
        if (! function_exists('get_plugins') || ! function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed_plugins = \get_plugins();

        return array_key_exists($plugin_slug, $installed_plugins) || in_array($plugin_slug, $installed_plugins, false);
    }
}

/** ----------------------------------------------- */

if (! function_exists('check_smtp_plugin_active')) {
    /**
     * @return bool
     */
    function check_smtp_plugin_active(): bool
    {
        $smtp_plugins_support = \filter_setting_options('smtp_plugins_support', []);

        $check = true;
        if (! empty($smtp_plugins_support)) {
            foreach ($smtp_plugins_support as $plugin_slug) {
                if (\check_plugin_active($plugin_slug)) {
                    $check = false;

                    break;
                }
            }
        }

        return $check;
    }
}

/** ----------------------------------------------- */
