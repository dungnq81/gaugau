<?php

namespace Cores\Traits;

use Cores\CSS;
use Cores\Horizontal_Nav_Walker;
use Cores\Vertical_Nav_Walker;
use MatthiasMullie\Minify;

\defined('ABSPATH') || die;

trait Wp
{
    use Cast;
    use DateTime;
    use File;
    use Str;
    use Url;
    use Db;
    use Encryption;

    // -------------------------------------------------------------

    /**
     * @param string|null $js
     * @param bool $debug_check
     *
     * @return string|null
     */
    public static function JSMinify(?string $js, bool $debug_check = true): ?string
    {
        if (empty($js)) {
            return null;
        }

        if ($debug_check && defined('WP_DEBUG') && WP_DEBUG) {
            return $js;
        }

        if (class_exists(Minify\JS::class)) {
            return (new Minify\JS())->add($js)->minify();
        }

        return $js;
    }

    // -------------------------------------------------------------

    /**
     * @param string|null $css
     * @param bool $debug_check
     *
     * @return string|null
     */
    public static function CSSMinify(?string $css, bool $debug_check = true): ?string
    {
        if (empty($css)) {
            return null;
        }

        if ($debug_check && defined('WP_DEBUG') && WP_DEBUG) {
            return $css;
        }

        if (class_exists(Minify\CSS::class)) {
            return (new Minify\CSS())->add($css)->minify();
        }

        return $css;
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $action
     * @param string $name
     * @param bool $referer
     * @param bool $display
     *
     * @return string|void
     */
    public static function CSRFToken(string|int $action = -1, string $name = '_csrf_token', bool $referer = false, bool $display = false)
    {
        $name        = esc_attr($name);
        $token       = wp_create_nonce($action);
        $nonce_field = '<input type="hidden" id="' . self::random(10) . '" name="' . $name . '" value="' . esc_attr($token) . '" />';

        if ($referer) {
            $nonce_field .= wp_referer_field(false);
        }

        if ($display) {
            echo $nonce_field;
        } else {
            return $nonce_field;
        }
    }

    // -------------------------------------------------------------

    /**
     * @param ?string $path
     * @param bool $require_path
     * @param bool $init_class
     * @param string $FQN
     * @param bool $is_widget
     *
     * @return void
     */
    public static function FQNLoad(?string $path, bool $require_path = false, bool $init_class = false, string $FQN = '\\', bool $is_widget = false): void
    {
        // Validate $path
        if (empty($path) || ! is_dir($path)) {
            self::errorLog("Invalid or inaccessible path: $path");

            return;
        }

        // Retrieve PHP files in the directory
        $files = glob($path . '/*.php', GLOB_NOSORT);

        // Check if glob() failed
        if ($files === false) {
            self::errorLog("Failed to read files in directory: $path");

            return;
        }

        foreach ($files as $file_path) {
            $filename    = basename($file_path, '.php');
            $filenameFQN = rtrim($FQN, '\\') . '\\' . $filename;

            // Skip unreadable files
            if (! is_readable($file_path)) {
                self::errorLog("Unreadable file skipped: $file_path");

                continue;
            }

            // Include the file if $require_path is true
            if ($require_path) {
                try {
                    require_once $file_path;
                } catch (\Exception $e) {
                    self::errorLog("Error including file $file_path: " . $e->getMessage());

                    continue;
                }
            }

            // Initialize the class or register as widget if $init_class is true
            if ($init_class && class_exists($filenameFQN)) {
                try {
                    if ($is_widget) {
                        register_widget(new $filenameFQN());
                    } else {
                        new $filenameFQN();
                    }
                } catch (\Exception $e) {
                    // Log any error that occurs during class initialization
                    self::errorLog("Error initializing class $filenameFQN: " . $e->getMessage());
                }
            }
        }
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     * @param string|null $extra_title
     *
     * @return string
     */
    public static function ACFLink(mixed $link, ?string $class = '', ?string $label = '', ?string $extra_title = ''): string
    {
        // string
        if (! empty($link) && is_string($link)) {
            $link_return = sprintf(
                '<a class="%3$s" href="%1$s" title="%2$s">',
                esc_url(trim($link)),
                self::escAttr($label),
                self::escAttr($class)
            );

            $link_return .= $label . $extra_title;
            $link_return .= '</a>';

            return $link_return;
        }

        // array
        if (! empty($link) && is_array($link)) {
            $_link_title  = $link['title']  ?? '';
            $_link_url    = $link['url']    ?? '';
            $_link_target = $link['target'] ?? '';

            // force label
            if (! empty($label)) {
                $_link_title = $label;
            }

            if (! empty($_link_url)) {
                $link_return = sprintf(
                    '<a class="%3$s" href="%1$s" title="%2$s"',
                    esc_url($_link_url),
                    self::escAttr($_link_title),
                    self::escAttr($class)
                );

                if (! empty($_link_target)) {
                    $link_return .= ' target="_blank" rel="noopener noreferrer nofollow"';
                }

                $link_return .= '>';
                $link_return .= $_link_title . $extra_title;
                $link_return .= '</a>';

                return $link_return;
            }
        }

        return '';
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     *
     * @return string
     */
    public static function ACFLinkOpen(mixed $link, ?string $class = '', ?string $label = ''): string
    {
        // string
        if (! empty($link) && is_string($link)) {
            return sprintf(
                '<a class="%3$s" href="%1$s" title="%2$s">',
                esc_url(trim($link)),
                self::escAttr($label),
                self::escAttr($class)
            );
        }

        // array
        if (! empty($link) && is_array($link)) {
            $_link_title  = $link['title']  ?? '';
            $_link_url    = $link['url']    ?? '';
            $_link_target = $link['target'] ?? '';

            if (! empty($label)) {
                $_link_title = $label;
            }

            if (! empty($_link_url)) {
                $link_return = sprintf(
                    '<a class="%3$s" href="%1$s" title="%2$s"',
                    esc_url($_link_url),
                    self::escAttr($_link_title),
                    self::escAttr($class)
                );

                if (! empty($_link_target)) {
                    $link_return .= ' target="_blank" rel="noopener noreferrer nofollow"';
                }
                $link_return .= '>';

                return $link_return;
            }
        }

        return '';
    }

    // -------------------------------------------------------------

    /**
     * @param string|null $content
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     * @param string|bool $empty_link_default_tag
     *
     * @return string
     */
    public static function ACFLinkWrap(?string $content, mixed $link, ?string $class = '', ?string $label = '', string|bool $empty_link_default_tag = 'span'): string
    {
        // string
        if (is_string($link) && ! empty($link)) {
            $link_return = sprintf(
                '<a class="%3$s" href="%1$s" title="%2$s">',
                esc_url(trim($link)),
                self::escAttr($label),
                self::escAttr($class)
            );
            $link_return .= $content . '</a>';

            return $link_return;
        }

        // array
        $link = (array) $link;
        if ($link) {
            $_link_title  = $link['title']  ?? '';
            $_link_url    = $link['url']    ?? '';
            $_link_target = $link['target'] ?? '';

            if (! empty($label)) {
                $_link_title = $label;
            }

            if (! empty($_link_url)) {
                $link_return = sprintf(
                    '<a class="%3$s" href="%1$s" title="%2$s"',
                    esc_url($_link_url),
                    self::escAttr($_link_title),
                    self::escAttr($class)
                );

                if (! empty($_link_target)) {
                    $link_return .= ' target="_blank" rel="noopener noreferrer nofollow"';
                }

                $link_return .= '>';
                $link_return .= $content;
                $link_return .= '</a>';

                return $link_return;
            }
        }

        // empty link
        $link_return = $content;
        if ($empty_link_default_tag) {
            $link_return = '<' . $empty_link_default_tag . ' class="' . self::escAttr($class) . '">' . $content . '</' . $empty_link_default_tag . '>';
        }

        return $link_return;
    }

    // -------------------------------------------------------------

    /**
     * @param array $args
     *
     * @return bool|false|string|void
     */
    public static function verticalNav(array $args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'container'      => false, // Remove nav container
                'menu_id'        => '',
                'menu_class'     => 'menu vertical',
                'theme_location' => '',
                'depth'          => 4,
                'fallback_cb'    => false,
                'walker'         => new Vertical_Nav_Walker(),
                'items_wrap'     => '<ul id="%1$s" class="%2$s" data-accordion-menu data-submenu-toggle="true">%3$s</ul>',
                'echo'           => false,
            ]
        );

        if (true === $args['echo']) {
            echo wp_nav_menu($args);
        } else {
            return wp_nav_menu($args);
        }
    }

    // -------------------------------------------------------------

    /**
     * @link http://codex.wordpress.org/Function_Reference/wp_nav_menu
     *
     * @param array $args
     *
     * @return bool|false|string|void
     */
    public static function horizontalNav(array $args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'container'      => false,
                'menu_id'        => '',
                'menu_class'     => 'dropdown menu horizontal',
                'theme_location' => '',
                'depth'          => 4,
                'fallback_cb'    => false,
                'walker'         => new Horizontal_Nav_Walker(),
                'items_wrap'     => '<ul id="%1$s" class="%2$s" data-dropdown-menu>%3$s</ul>',
                'echo'           => false,
            ]
        );

        if (true === $args['echo']) {
            echo wp_nav_menu($args);
        } else {
            return wp_nav_menu($args);
        }
    }

    // -------------------------------------------------------------

    /**
     * Call a shortcode function by its tag name.
     *
     * @param string $tag The shortcode tag to call.
     * @param array $atts Optional. An array of attributes to pass to the shortcode function.
     * @param string|null $content Optional. The content is enclosed by the shortcode. Default is null (no content).
     *
     * @return mixed Returns the result of the shortcode on success, or false if the shortcode does not exist.
     */
    public static function doShortcode(string $tag, array $atts = [], ?string $content = null): mixed
    {
        global $shortcode_tags;

        // Check if the shortcode exists
        if (! isset($shortcode_tags[$tag])) {
            return false;
        }

        // Call the shortcode function and return its output
        return call_user_func($shortcode_tags[$tag], $atts, $content, $tag);
    }

    // -------------------------------------------------------------

    /**
     * Using `rawurlencode` on any variable used as part of the query string, either by using
     * `add_query_arg()` or directly by string concatenation will prevent parameter hijacking.
     *
     * @param array $args An associative array of query parameters to add.
     * @param string $url The base URL to which query parameters will be added.
     *
     * @return string The URL with the encoded query parameters added.
     */
    public static function addQueryArg(array $args, string $url): string
    {
        // Encode each argument to prevent parameter hijacking
        $encodedArgs = array_map('rawurlencode', $args);

        // Use WordPress's add_query_arg function to construct the URL
        return add_query_arg($encodedArgs, $url);
    }

    // -------------------------------------------------------------

    /**
     * Retrieves attachment details by its ID.
     *
     * @param mixed $attachment_id
     * @param bool $return_object Optional. Whether to return the result as an object. Default true.
     *
     * @throws \JsonException If JSON encoding fails.
     *
     * @return object|array|null Attachment details as an object or array, or null if not found.
     */
    public static function getAttachment(mixed $attachment_id, bool $return_object = true): object|array|null
    {
        // Fetch the attachment post object
        $attachment = get_post($attachment_id);

        // Check if the attachment exists
        if (! $attachment) {
            return null;
        }

        // Prepare the attachment details
        $attachment_details = [
            'alt'         => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'caption'     => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'href'        => get_permalink($attachment->ID),
            'src'         => $attachment->guid,
            'title'       => $attachment->post_title,
        ];

        // Convert to object if specified
        if ($return_object) {
            return self::toObject($attachment_details);
        }

        // Return attachment details as an array
        return $attachment_details;
    }

    // -------------------------------------------------------------

    /**
     * @param array|null $arr_parsed
     *
     * @return bool
     */
    public static function hasDelayScriptTag(?array $arr_parsed): bool
    {
        if (is_null($arr_parsed)) {
            return false;
        }

        foreach ($arr_parsed as $str => $value) {
            if ('delay' === $value) {
                return true;
            }
        }

        return false;
    }

    // -------------------------------------------------------------

    /**
     * @param array|null $arr_parsed
     * @param string $tag
     * @param string $handle
     *
     * @return string
     */
    public static function lazyScriptTag(?array $arr_parsed, string $tag, string $handle): string
    {
        if (is_null($arr_parsed)) {
            return $tag;
        }

        foreach ($arr_parsed as $str => $value) {
            if (str_contains($handle, $str)) {
                if ('defer' === $value) {
                    return preg_replace(
                        ['/\s+defer\s+/', '/\s+src=/'],
                        [' ', ' defer src='],
                        $tag
                    );
                }

                if ('delay' === $value && ! self::isAdmin()) {
                    return preg_replace(
                        ['/\s+defer\s+/', '/\s+src=/'],
                        [' ', ' defer data-type=\'lazy\' data-src='],
                        $tag
                    );
                }
            }
        }

        return $tag;
    }

    // -------------------------------------------------------------

    /**
     * @param array|null $arr_styles
     * @param string $html
     * @param string $handle
     *
     * @return string
     */
    public static function lazyStyleTag(?array $arr_styles, string $html, string $handle): string
    {
        if (is_null($arr_styles)) {
            return $html;
        }

        foreach ($arr_styles as $style) {
            if (str_contains($handle, $style)) {
                return preg_replace('/media=\'all\'/', 'media=\'print\' onload=\'this.media="all"\'', $html);
            }
        }

        return $html;
    }

    // -------------------------------------------------------------

    /**
     * Updates a WordPress option with new values.
     *
     * @param string $option_name The name of the option to update.
     * @param mixed $new_options The new options to set, either as a single value or an array.
     * @param bool $merge_arr Optional. Whether to merge new options with existing options. Default false.
     *
     * @return bool True on success, false on failure.
     */
    public static function updateOption(string $option_name, mixed $new_options, bool $merge_arr = false): bool
    {
        // Retrieve current options if merging is requested
        if ($merge_arr) {
            $options = self::getOption($option_name);

            // Ensure both are arrays before merging
            if (is_array($options) && is_array($new_options)) {
                $updated_options = array_merge($options, $new_options);
            } else {
                // If current options are not an array, just set new options
                $updated_options = is_array($new_options) ? $new_options : [$new_options];
            }
        } else {
            // No merging, set updated options directly
            $updated_options = $new_options;
        }

        // Update the option in the appropriate context (multisite or not)
        return is_multisite()
            ? update_site_option($option_name, $updated_options)
            : update_option($option_name, $updated_options);
    }

    // -------------------------------------------------------------

    /**
     * Retrieve a WordPress option value.
     *
     * @param string $option The name of the option to retrieve.
     * @param mixed $default Optional. The default value to return if the option does not exist. Default is false.
     * @param bool $static_cache Optional. Whether to use static caching for the option value. Default is false.
     *
     * @return mixed The option value, or the default if the option does not exist.
     */
    public static function getOption(string $option, mixed $default = false, bool $static_cache = false): mixed
    {
        static $cache = [];

        // Validate the option key
        $option = strtolower(trim($option));
        if (empty($option)) {
            return $default;
        }

        // Return a cached value if static caching is enabled and the value is already cached
        if ($static_cache && isset($cache[$option])) {
            return $cache[$option];
        }

        // Retrieve the option value
        $value = is_multisite() ? get_site_option($option, $default) : get_option($option, $default);

        // Cache the value if static caching is enabled
        if ($static_cache) {
            $cache[$option] = $value;
        }

        return $value;
    }

    // -------------------------------------------------------------

    /**
     * Retrieve a theme modification value.
     *
     * @param string $mod_name The name of the theme modification to retrieve.
     * @param mixed $default Optional. The default value to return if the theme modification does not exist. Default is false.
     *
     * @return mixed The theme modification value, or the default if the theme modification does not exist.
     */
    public static function getThemeMod(string $mod_name, mixed $default = false): mixed
    {
        static $_is_loaded = [];

        // Check if the modification name is provided
        if ($mod_name) {
            $mod_name_lower = strtolower($mod_name);

            // Load the modification if not already loaded
            if (! isset($_is_loaded[$mod_name_lower])) {
                $_mod = get_theme_mod($mod_name, $default);

                // If using SSL, ensure the URL is HTTPS
                $_is_loaded[$mod_name_lower] = is_ssl() ? str_replace('http://', 'https://', $_mod) : $_mod;
            }

            return $_is_loaded[$mod_name_lower];
        }

        return $default;
    }

    // -------------------------------------------------------------

    /**
     * Retrieve a term by ID or slug/name in a specified taxonomy.
     *
     * @param mixed $term_id The term ID (integer) or slug/name (string) of the term to retrieve.
     * @param string $taxonomy The taxonomy to search for the term. Default is 'category'.
     *
     * @return \WP_Term|\WP_Error|bool|null The term object, a WP_Error on failure, false if `term` doesn't exist, or null if term ID is invalid.
     */
    public static function getTerm(mixed $term_id, string $taxonomy = 'category'): \WP_Term|\WP_Error|bool|null
    {
        // Check if the term ID is numeric and retrieve term by ID
        if (is_numeric($term_id)) {
            $term_id = (int) $term_id;
            $term    = get_term($term_id, $taxonomy);
        } else {
            // If term_id is not numeric, attempt to retrieve the term by slug or name
            $term = get_term_by('slug', $term_id, $taxonomy) ?: get_term_by('name', $term_id, $taxonomy);
        }

        return $term;
    }

    // -------------------------------------------------------------

    /**
     * Set the number of posts per page for non-admin pages.
     *
     * @param int $post_limit The maximum number of posts to display per page. Default is -1.
     *
     * @return void
     */
    public static function setPostsPerPage(int $post_limit = -1): void
    {
        // Check if we are not in the admin area and the main query is not being processed
        if (! is_admin() && ! is_main_query()) {
            $limit_default = self::getOption('posts_per_page');

            // Only modify the query if the new limit exceeds the default
            if ($post_limit > (int) $limit_default) {
                add_action('pre_get_posts', static function ($query) use ($post_limit) {
                    $query->set('posts_per_page', $post_limit);
                }, 9999);
            }
        }
    }

    // -------------------------------------------------------------

    /**
     * Query posts by term.
     *
     * @param mixed $term The term to query.
     * @param string $post_type The post-type to query. The default is 'post'.
     * @param bool $include_children Whether to include children of the term. Default is false.
     * @param int $posts_per_page Number of posts to return. Default is -1.
     * @param array|null $orderby Array of orderby parameters. Ex. [ 'date' => 'DESC' ]. Default is null.
     * @param array|null $meta_query Array of meta query parameters. Default is null.
     * @param bool|string $strtotime_recent Timestamp string for recent posts. Default is false.
     *
     * @throws \JsonException
     *
     * @return \WP_Query|bool False on failure or if no posts found, WP_Query object on success.
     */
    public static function queryByTerm(
        mixed $term,
        string $post_type = 'post',
        bool $include_children = false,
        int $posts_per_page = -1,
        ?array $orderby = null,
        ?array $meta_query = null,
        bool|string $strtotime_recent = false
    ): \WP_Query|bool {
        if (! $term) {
            return false;
        }

        $posts_per_page = max($posts_per_page, -1);

        $_args = [
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => $posts_per_page,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query'              => ['relation' => 'AND'],
        ];

        // Convert term to object if it is not already
        if (! is_object($term)) {
            $term = self::toObject($term);
        }

        if (isset($term->taxonomy, $term->term_id)) {
            $_args['tax_query'][] = [
                'taxonomy'         => $term->taxonomy,
                'terms'            => [$term->term_id],
                'include_children' => $include_children,
                'operator'         => 'IN',
            ];
        }

        // Set orderby if provided
        if (! empty($orderby)) {
            $_args['orderby'] = $orderby;
        }

        // Merge meta_query if provided
        if (! empty($meta_query)) {
            $_args = array_merge($_args, $meta_query);
        }

        // Handle date_query for recent posts
        if ($strtotime_recent) {
            $recent = strtotime($strtotime_recent);
            if ($recent) {
                $_args['date_query'] = [
                    'after' => [
                        'year'  => date('Y', $recent),
                        'month' => date('n', $recent),
                        'day'   => date('j', $recent),
                    ],
                ];
            }
        }

        // Handle WooCommerce specific visibility settings
        if (
            'product' === $post_type                                             &&
            'yes'     === self::getOption('woocommerce_hide_out_of_stock_items') &&
            self::checkPluginActive('woocommerce/woocommerce.php')
        ) {
            $product_visibility_term_ids = \wc_get_product_visibility_term_ids();
            $_args['tax_query'][]        = [
                [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $product_visibility_term_ids['outofstock'],
                    'operator' => 'NOT IN',
                ],
            ];
        }

        self::setPostsPerPage($posts_per_page);
        $_query = new \WP_Query($_args);

        return $_query->have_posts() ? $_query : false;
    }

    // -------------------------------------------------------------

    /**
     * Query posts by multiple terms.
     *
     * @param array $term_ids The term IDs to query.
     * @param string $post_type The post-type to query. The default is 'post'.
     * @param string $taxonomy The taxonomy to query. Default is 'category'.
     * @param bool $include_children Whether to include children of the terms. Default is false.
     * @param int $posts_per_page Number of posts to return. Default is -1.
     * @param array|null $orderby Array of orderby parameters.
     * @param array|null $meta_query Array of meta query parameters.
     * @param bool|string $strtotime_str Timestamp string for recent posts. Default is false.
     *
     * @return \WP_Query|false False on failure or if no posts found, WP_Query object on success.
     */
    public static function queryByTerms(
        array $term_ids,
        string $post_type = 'post',
        string $taxonomy = 'category',
        bool $include_children = false,
        int $posts_per_page = -1,
        ?array $orderby = null,
        ?array $meta_query = null,
        bool|string $strtotime_str = false,
    ): \WP_Query|false {
        $posts_per_page = max($posts_per_page, -1);

        $_args = [
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => $posts_per_page,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query'              => ['relation' => 'AND'],
        ];

        // Set taxonomy to default if not provided
        if (! $taxonomy) {
            $taxonomy = 'category';
        }

        // Add terms to the tax query
        if (count($term_ids) > 0) {
            $_args['tax_query'][] = [
                'taxonomy'         => $taxonomy,
                'terms'            => $term_ids,
                'field'            => 'term_id',
                'include_children' => $include_children,
                'operator'         => 'IN',
            ];
        }

        // Set orderby if provided
        if (! empty($orderby)) {
            $_args['orderby'] = $orderby;
        }

        // Merge meta_query if provided
        if (! empty($meta_query)) {
            $_args = array_merge($_args, $meta_query);
        }

        // Handle date_query for recent posts
        if ($strtotime_str) {
            $recent = strtotime($strtotime_str);
            if ($recent) {
                $_args['date_query'] = [
                    'after' => [
                        'year'  => date('Y', $recent),
                        'month' => date('n', $recent),
                        'day'   => date('j', $recent),
                    ],
                ];
            }
        }

        // Handle WooCommerce specific visibility settings
        if (
            'product' === $post_type                                             &&
            'yes'     === self::getOption('woocommerce_hide_out_of_stock_items') &&
            self::checkPluginActive('woocommerce/woocommerce.php')
        ) {
            $product_visibility_term_ids = \wc_get_product_visibility_term_ids();
            $_args['tax_query'][]        = [
                [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $product_visibility_term_ids['outofstock'],
                    'operator' => 'NOT IN',
                ],
            ];
        }

        // Set custom posts_per_page
        self::setPostsPerPage($posts_per_page);

        // Query posts
        $query_result = new \WP_Query($_args);

        return $query_result->have_posts() ? $query_result : false;
    }

    // -------------------------------------------------------------

    /**
     * @param int|null $blog_id
     *
     * @return string
     *
     * Modified from the native get_custom_logo() function
     */
    public static function customSiteLogo(?int $blog_id = 0): string
    {
        $html          = '';
        $switched_blog = false;

        if ($blog_id !== null && is_multisite() && get_current_blog_id() !== $blog_id) {
            switch_to_blog($blog_id);
            $switched_blog = true;
        }

        $custom_logo_id = self::getThemeMod('custom_logo');

        // We have a logo. Logo is go.
        if ($custom_logo_id) {
            $custom_logo_attr = [
                'class'   => 'custom-logo',
                'loading' => false,
            ];

            $unlink_homepage_logo = (bool) get_theme_support('custom-logo', 'unlink-homepage-logo');
            $unlink_logo          = $unlink_homepage_logo;

            if ($unlink_homepage_logo && self::isHomeOrFrontPage() && ! is_paged()) {
                /*
                 * If on the home page, set the logo alt attribute to an empty string,
                 * as the image is decorative and doesn't need its purpose to be described.
                 */
                $custom_logo_attr['alt'] = '';
            } elseif ($unlink_logo) {
                // set the logo alt attribute to an empty string
                $custom_logo_attr['alt'] = '';
            } else {
                /*
                 * If the logo alt attribute is empty, get the site title and explicitly pass it
                 * to the attributes used by wp_get_attachment_image().
                 */
                $image_alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
                if (empty($image_alt)) {
                    $custom_logo_attr['alt'] = get_bloginfo('name', 'display');
                }
            }

            /**
             * Filters the list of custom logo image attributes.
             *
             * @param array $custom_logo_attr Custom logo image attributes.
             * @param int $custom_logo_id Custom logo attachment ID.
             * @param int $blog_id ID of the blog to get the custom logo for.
             *
             * @since 5.5.0
             */
            $custom_logo_attr = apply_filters('get_custom_logo_image_attributes', $custom_logo_attr, $custom_logo_id, $blog_id);

            /**
             * If the alt attribute is not empty, there's no need to explicitly pass it
             * because wp_get_attachment_image() already adds the alt attribute.
             */
            $image = wp_get_attachment_image($custom_logo_id, 'full', false, $custom_logo_attr);

            if ($unlink_homepage_logo && self::isHomeOrFrontPage() && ! is_paged()) {
                // If on the home page, don't link the logo to home.
                $html = sprintf('<span class="custom-logo-link">%1$s</span>', $image);
            } elseif ($unlink_logo) {
                // Remove logo link
                $html = sprintf('<span class="custom-logo-link">%1$s</span>', $image);
            } else {
                $aria_current = self::isHomeOrFrontPage() && ! is_paged() ? ' aria-current="page"' : '';

                $html = sprintf(
                    '<a href="%1$s" class="custom-logo-link" rel="home"%2$s>%3$s</a>',
                    self::home(),
                    $aria_current,
                    $image
                );
            }
        } elseif (is_customize_preview()) {
            // If no logo is set, but we're in the Customizer, leave a placeholder (needed for the live preview).
            $html = sprintf(
                '<a href="%1$s" class="custom-logo-link" style="display:none;">' . esc_html(get_bloginfo('name')) . '</a>',
                self::home(),
            );
        }

        if ($switched_blog) {
            restore_current_blog();
        }

        /**
         * Filters the custom logo output.
         *
         * @param string $html Custom logo HTML output.
         * @param int $blog_id ID of the blog to get the custom logo for.
         */
        return apply_filters('custom_site_logo_filter', $html, $blog_id);
    }

    // -------------------------------------------------------------

    /**
     * @return bool
     */
    public static function isHomeOrFrontPage(): bool
    {
        return is_home() || is_front_page();
    }

    // -------------------------------------------------------------

    /**
     * @param bool $echo
     * @param string|null $home_heading
     * @param string|null $class
     *
     * @return string|void
     */
    public static function siteTitleOrLogo(bool $echo = true, ?string $home_heading = 'h1', ?string $class = 'logo')
    {
        $logo_title = self::getThemeMod('logo_title_setting');
        $logo_title = $logo_title ? '<span class="logo-txt">' . $logo_title . '</span>' : '';
        $logo_class = ! empty($class) ? ' class="' . $class . '"' : '';

        if (function_exists('the_custom_logo') && has_custom_logo()) {
            // replace \get_custom_logo() with self::customSiteLogo()
            $logo = self::customSiteLogo();
            $html = '<a' . $logo_class . ' title="' . esc_attr(get_bloginfo('name')) . '" href="' . self::home('/') . '" rel="home">' . $logo . $logo_title . '</a>';
        } else {
            $html = '<a' . $logo_class . ' title="' . esc_attr(get_bloginfo('name')) . '" href="' . self::home('/') . '" rel="home">' . esc_html(get_bloginfo('name')) . $logo_title . '</a>';
            if ('' !== get_bloginfo('description')) {
                $html .= '<p class="site-description">' . esc_html(get_bloginfo('description', 'display')) . '</p>';
            }
        }

        if (is_string($home_heading) && ! empty($home_heading)) {
            $is_home_or_front_page = self::isHomeOrFrontPage();
            $tag                   = $is_home_or_front_page ? $home_heading : 'div';
            $logo_heading          = self::getThemeMod('home_heading_setting');

            if ($logo_heading && $is_home_or_front_page) {
                $html .= '<' . esc_attr($tag) . ' class="sr-only">' . $logo_heading . '</' . esc_attr($tag) . '>';
            }
        }

        if (! $echo) {
            return $html;
        }

        echo $html;
    }

    // -------------------------------------------------------------

    /**
     * @param string $theme - default|light|dark
     * @param string|null $class
     *
     * @return string
     */
    public static function siteLogo(string $theme = 'default', ?string $class = ''): string
    {
        $html           = '';
        $custom_logo_id = null;

        if ('default' !== $theme && $theme_logo = self::getThemeMod($theme . '_logo')) {
            $custom_logo_id = attachment_url_to_postid($theme_logo);
        } elseif (has_custom_logo()) {
            $custom_logo_id = self::getThemeMod('custom_logo');
        }

        // We have a logo. Logo is go.
        if ($custom_logo_id) {
            $custom_logo_attr = [
                'class'   => $theme . '-logo',
                'loading' => 'lazy',
            ];

            /**
             * If the logo alt attribute is empty, get the site title and explicitly pass it
             * to the attributes used by wp_get_attachment_image().
             */
            $image_alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
            if (empty($image_alt)) {
                $image_alt = get_bloginfo('name', 'display');
            }

            $custom_logo_attr['alt'] = $image_alt;

            $logo_title = self::getThemeMod('logo_title_setting');
            $logo_title = $logo_title ? '<span>' . $logo_title . '</span>' : '';

            /**
             * If the alt attribute is not empty, there's no need to explicitly pass it
             * because wp_get_attachment_image() already adds the alt attribute.
             */
            $logo = wp_get_attachment_image($custom_logo_id, 'full', false, $custom_logo_attr);
            if ($class) {
                $html = '<div class="' . $class . '"><a title="' . $image_alt . '" href="' . self::home() . '">' . $logo . $logo_title . '</a></div>';
            } else {
                $html = '<a title="' . $image_alt . '" href="' . self::home() . '">' . $logo . $logo_title . '</a>';
            }
        }

        return $html;
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string|null $class
     * @param string|null $default_tag
     *
     * @return string|null
     */
    public static function loopExcerpt(mixed $post = null, ?string $class = 'excerpt', ?string $default_tag = 'div'): ?string
    {
        $excerpt = get_the_excerpt($post);
        if (! self::stripSpace($excerpt)) {
            return null;
        }

        $excerpt = strip_tags($excerpt);
        if (! $class) {
            return $excerpt;
        }

        $tag = $default_tag ?? 'p';

        return '<' . $tag . " class=\"$class\">{$excerpt}</" . $tag . '>';
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string|null $class
     * @param string|null $default_tag
     * @param string|null $fa_glyph
     *
     * @return string|null
     */
    public static function postExcerpt(mixed $post = null, ?string $class = 'excerpt', ?string $default_tag = 'div', ?string $fa_glyph = ''): ?string
    {
        $post = get_post($post);
        if (! $post || ! self::stripSpace($post->post_excerpt)) {
            return null;
        }

        $open  = '';
        $close = '';
        $glyph = '';

        if ($fa_glyph) {
            $glyph = ' data-fa="' . $fa_glyph . '"';
        }

        if ($class) {
            $tag = $default_tag ?? 'div';

            $open  = '<' . $tag . ' class="' . $class . '"' . $glyph . '>';
            $close = '</' . $tag . '>';
        }

        return $open . $post->post_excerpt . $close;
    }

    // -------------------------------------------------------------

    /**
     * @param int|null $term
     * @param string|null $class
     * @param string|null $default_tag
     *
     * @return string|null
     */
    public static function termExcerpt(?int $term = 0, ?string $class = 'excerpt', ?string $default_tag = 'p'): ?string
    {
        $description = term_description($term);
        if (! self::stripSpace($description)) {
            return null;
        }

        $description = strip_tags($description);
        if (! $class) {
            return $description;
        }

        $tag = $default_tag ?? 'p';

        return '<' . $tag . " class=\"$class\">$description</" . $tag . '>';
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $post
     * @param string $taxonomy
     *
     * @return mixed
     */
    public static function primaryTerm(mixed $post, string $taxonomy = ''): mixed
    {
        // Ensure $post is a valid post object
        $post = get_post($post);
        if (! $post) {
            return null;
        }

        $post_id = $post->ID;

        // Determine the taxonomy if not explicitly provided
        if (! $taxonomy) {
            $post_type = get_post_type($post);

            // The default taxonomy for 'post' is 'category'
            if ('post' === $post_type) {
                $taxonomy = 'category';
            }

            // Use custom filter to retrieve taxonomy mapping for the post-type
            foreach (self::filterSettingOptions('post_type_terms', []) as $post_type_terms) {
                foreach ($post_type_terms as $_post_type => $_taxonomy) {
                    if ($_post_type === $post_type) {
                        $taxonomy = $_taxonomy;

                        break;
                    }
                }
            }
        }

        // Get all terms associated with the post for the specified taxonomy
        $post_terms = get_the_terms($post, $taxonomy);
        if (! is_array($post_terms) || empty($post_terms)) {
            return null;
        }

        // Extract term IDs for further processing
        $term_ids = wp_list_pluck($post_terms, 'term_id');

        // Support for Rank Math SEO plugin
        $primary_term_id = get_post_meta($post_id, 'rank_math_primary_' . $taxonomy, true);
        if ($primary_term_id && in_array($primary_term_id, $term_ids, false)) {
            $term = get_term($primary_term_id, $taxonomy);
            if ($term) {
                return $term;
            }
        }

        // Support for Yoast SEO plugin
        if (class_exists('\WPSEO_Primary_Term')) {
            $primary_term_id = (new \WPSEO_Primary_Term($taxonomy, $post))?->get_primary_term();
            if ($primary_term_id && in_array($primary_term_id, $term_ids, false)) {
                $term = get_term($primary_term_id, $taxonomy);
                if ($term) {
                    return $term;
                }
            }
        }

        // Support for All in One SEO plugin
        if (function_exists('aioseo')) {
            $aioseo_primary_term_id = get_post_meta($post_id, '_aioseo_primary_' . $taxonomy, true);
            if ($aioseo_primary_term_id && in_array($aioseo_primary_term_id, $term_ids, false)) {
                $term = get_term($aioseo_primary_term_id, $taxonomy);
                if ($term) {
                    return $term;
                }
            }
        }

        // Default: return the first term if no primary term is found
        return $post_terms[0] ?? null;
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string $taxonomy
     * @param string|null $wrapper_open
     * @param string|null $wrapper_close
     *
     * @return string|null
     */
    public static function getPrimaryTerm(mixed $post = null, string $taxonomy = '', ?string $wrapper_open = '<div class="terms">', ?string $wrapper_close = '</div>'): ?string
    {
        $term = self::primaryTerm($post, $taxonomy);
        if (! $term) {
            return null;
        }

        $link = '<a href="' . esc_url(get_term_link($term, $taxonomy)) . '" title="' . esc_attr($term->name) . '">' . $term->name . '</a>';
        if ($wrapper_open && $wrapper_close) {
            $link = $wrapper_open . $link . $wrapper_close;
        }

        return $link;
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $post
     * @param string|null $taxonomy
     * @param string|null $wrapper_open
     * @param string|null $wrapper_close
     *
     * @return string|null
     */
    public static function postTerms(mixed $post, ?string $taxonomy = 'category', ?string $wrapper_open = '<div class="terms">', ?string $wrapper_close = '</div>'): ?string
    {
        if (! $taxonomy) {
            $post_type = get_post_type($post);
            $taxonomy  = $post_type . '_cat';

            if ('post' === $post_type) {
                $taxonomy = 'category';
            }

            $post_type_terms_arr = self::filterSettingOptions('post_type_terms', []);
            if (! empty($post_type_terms_arr)) {
                foreach ($post_type_terms_arr as $_post_type => $_taxonomy) {
                    if ($_post_type === $post_type) {
                        $taxonomy = $_taxonomy;
                    }
                }
            }
        }

        $link       = '';
        $post_terms = get_the_terms($post, $taxonomy);
        if (empty($post_terms)) {
            return null;
        }

        foreach ($post_terms as $term) {
            if ($term->slug) {
                $link .= '<a href="' . esc_url(get_term_link($term)) . '" title="' . esc_attr($term->name) . '">' . $term->name . '</a>';
            }
        }

        if ($wrapper_open && $wrapper_close) {
            $link = $wrapper_open . $link . $wrapper_close;
        }

        return $link;
    }

    // -------------------------------------------------------------

    /**
     * @param string|null $taxonomy
     * @param int $id
     * @param string $sep
     *
     * @return void
     */
    public static function hashTags(?string $taxonomy = 'post_tag', int $id = 0, string $sep = ''): void
    {
        if (! $taxonomy) {
            $taxonomy = 'post_tag';
        }

        // Get Tags for posts.
        $hashtag_list = get_the_term_list($id, $taxonomy, '', $sep);

        // We don't want to output if it is empty, so make sure it's not.
        if ($hashtag_list) {
            echo '<div class="hashtags">';
            printf(
                /* translators: 1: SVG icon. 2: posted in label, only visible to screen readers. 3: list of tags. */
                '<div class="hashtag-links links">%1$s<span class="sr-only">%2$s</span>%3$s</div>',
                '<i data-fa="#"></i>',
                __('Tags', TEXT_DOMAIN),
                $hashtag_list
            ); // WPCS: XSS OK.

            echo '</div>';
        }
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string $size
     *
     * @return string|null
     */
    public static function postImageSrc(mixed $post = null, string $size = 'thumbnail'): ?string
    {
        return get_the_post_thumbnail_url($post, $size);
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string $size
     * @param string|array $attr
     *
     * @return string
     */
    public static function iconPostImage(mixed $post = null, string $size = 'thumbnail', string|array $attr = ''): string
    {
        $post = get_post($post);
        if (! $post) {
            return '';
        }

        $post_thumbnail_id = get_post_thumbnail_id($post);
        if ($post_thumbnail_id) {
            return self::iconImage($post_thumbnail_id, $size, $attr);
        }

        return '';
    }

    // -------------------------------------------------------------

    /**
     * @param int $attachment_id
     * @param string $size
     *
     * @return string|null
     */
    public static function attachmentImageSrc(int $attachment_id, string $size = 'thumbnail'): ?string
    {
        return wp_get_attachment_image_url($attachment_id, $size);
    }

    // -------------------------------------------------------------

    /**
     * @param $attachment_id
     * @param string $size
     * @param string|array $attr
     *
     * @return string
     */
    public static function iconImage($attachment_id, string $size = 'thumbnail', string|array $attr = ''): string
    {
        $html  = '';
        $image = wp_get_attachment_image_src($attachment_id, $size, true);

        if ($image) {
            [$src, $width, $height] = $image;
            $hwstring               = image_hwstring($width, $height);

            $default_attr = [
                'src' => $src,
                'alt' => trim(strip_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true))),
            ];

            $context = apply_filters('wp_get_attachment_image_context', 'wp_get_attachment_image');
            $attr    = wp_parse_args($attr, $default_attr);

            $loading_attr              = $attr;
            $loading_attr['width']     = $width;
            $loading_attr['height']    = $height;
            $loading_optimization_attr = wp_get_loading_optimization_attributes(
                'img',
                $loading_attr,
                $context
            );

            // Add loading optimization attributes if not available.
            $attr = array_merge($attr, $loading_optimization_attr);

            // Omit the `decoding` attribute if the value is invalid, according to the spec.
            if (empty($attr['decoding']) || ! in_array($attr['decoding'], ['async', 'sync', 'auto'], false)) {
                unset($attr['decoding']);
            }

            /**
             * If the default value of `lazy` for the `loading` attribute is overridden
             * to omit the attribute for this image, ensure it is not included.
             */
            if (isset($attr['loading']) && ! $attr['loading']) {
                unset($attr['loading']);
            }

            // If the `fetchpriority` attribute is overridden and set to false or an empty string.
            if (isset($attr['fetchpriority']) && ! $attr['fetchpriority']) {
                unset($attr['fetchpriority']);
            }

            $attr = array_map('esc_attr', $attr);
            $html = rtrim("<img $hwstring");

            foreach ($attr as $name => $value) {
                $html .= " $name=" . '"' . $value . '"';
            }

            $html .= ' />';
        }

        return apply_filters('icon_image_html_filter', $html, $attachment_id, $size, $attr);
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $post_id
     * @param bool $force_object
     * @param bool $format_value
     * @param bool $escape_html
     *
     * @throws \JsonException
     *
     * @return array|bool|object
     */
    public static function getFields(mixed $post_id = false, bool $force_object = false, bool $format_value = true, bool $escape_html = false): object|bool|array
    {
        if (! self::isAcfActive()) {
            return [];
        }

        $fields = \function_exists('get_fields') ? \get_fields($post_id, $format_value, $escape_html) : [];

        return (true === $force_object) ? self::toObject($fields) : $fields;
    }

    // -------------------------------------------------------------

    /**
     * @param string|null $selector
     * @param mixed $post_id
     * @param boolean $format_value
     * @param boolean $escape_html
     *
     * @return mixed
     */
    public static function getField(?string $selector, mixed $post_id = false, bool $format_value = true, bool $escape_html = false): mixed
    {
        if (! $selector || ! self::isAcfActive()) {
            return false;
        }

        return \function_exists('get_field') ? \get_field($selector, $post_id, $format_value, $escape_html) : false;
    }

    // -------------------------------------------------------------

    /**
     * @param \WP_Term|null $term
     * @param null $acf_field_name
     * @param string $size
     * @param bool $img_wrap
     * @param string|array $attr
     *
     * @return string|null
     */
    public static function acfTermThumb(\WP_Term|null $term, $acf_field_name = null, string $size = 'thumbnail', bool $img_wrap = false, string|array $attr = ''): ?string
    {
        if (! $term) {
            return null;
        }

        if (class_exists(\ACF::class)) {
            $attach_id = self::getField($acf_field_name, $term);
            if ($attach_id) {
                $img_src = wp_get_attachment_image_url($attach_id, $size);
                if ($img_wrap) {
                    $img_src = wp_get_attachment_image($attach_id, $size, false, $attr);
                }

                return $img_src;
            }
        }

        return null;
    }

    // -------------------------------------------------------------

    /**
     * @return void
     */
    public static function breadCrumbs(): void
    {
        global $post, $wp_query;

        // If it's the front page, no need to display breadcrumbs
        if (is_front_page()) {
            return;
        }

        $before      = '<li class="current">';
        $after       = '</li>';
        $breadcrumbs = [];

        // Home
        $breadcrumbs[] = '<li><a class="home" href="' . self::home() . '">' . __('Home', TEXT_DOMAIN) . '</a></li>';

        // WooCommerce Shop Page
        if (self::isWoocommerceActive() && \is_shop()) {
            $breadcrumbs[] = $before . get_the_title(self::getOption('woocommerce_shop_page_id')) . $after;
        } // Posts Page
        elseif ($wp_query?->is_posts_page) {
            $breadcrumbs[] = $before . get_the_title(self::getOption('page_for_posts', true)) . $after;
        } // Post-type Archive
        elseif ($wp_query?->is_post_type_archive) {
            $breadcrumbs[] = $before . post_type_archive_title('', false) . $after;
        } // Page or Attachment
        elseif (is_page() || is_attachment()) {
            // Breadcrumb for child pages (Parent page)
            if ($post?->post_parent) {
                $parent_id          = $post->post_parent;
                $parent_breadcrumbs = [];

                while ($parent_id) {
                    $page                 = get_post($parent_id);
                    $parent_breadcrumbs[] = '<li><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
                    $parent_id            = $page->post_parent;
                }

                $parent_breadcrumbs = array_reverse($parent_breadcrumbs);
                $breadcrumbs        = array_merge($breadcrumbs, $parent_breadcrumbs);
            }
            $breadcrumbs[] = $before . get_the_title() . $after;
        } // Single
        elseif (is_single() && ! is_attachment()) {
            $post_type  = get_post_type_object(get_post_type());
            $taxonomies = get_object_taxonomies($post_type?->name, 'names');

            if (empty($taxonomies)) {
                $slug = $post_type?->rewrite;
                if (! is_bool($slug)) {
                    $breadcrumbs[] = '<li><a href="' . self::home() . $slug['slug'] . '/">' . $post_type?->labels?->singular_name . '</a></li>';
                }
            } else {
                // taxonomy (primary term)
                $term = self::primaryTerm($post);
                if ($term) {
                    $cat_code      = get_term_parents_list($term->term_id, $term->taxonomy, ['separator' => '']);
                    $cat_code      = str_replace('<a', '<li><a', $cat_code);
                    $breadcrumbs[] = str_replace('</a>', '</a></li>', $cat_code);
                }
            }

            $breadcrumbs[] = $before . get_the_title() . $after;
        } // Search page
        elseif (is_search()) {
            $breadcrumbs[] = $before . sprintf(__('Search Results for: %s', TEXT_DOMAIN), get_search_query()) . $after;
        } // Tag Archive
        elseif (is_tag()) {
            $breadcrumbs[] = $before . sprintf(__('Tag Archives: %s', TEXT_DOMAIN), single_tag_title('', false)) . $after;
        } // Author
        elseif (is_author()) {
            global $author;
            $userdata      = get_userdata($author);
            $breadcrumbs[] = $before . $userdata?->display_name . $after;
        } // Day, Month, Year Archives
        elseif (is_day() || is_month() || is_year()) {
            if (is_day()) {
                $breadcrumbs[] = '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
                $breadcrumbs[] = '<li><a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a></li>';
                $breadcrumbs[] = $before . get_the_time('d') . $after;
            } elseif (is_month()) {
                $breadcrumbs[] = '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
                $breadcrumbs[] = $before . get_the_time('F') . $after;
            } elseif (is_year()) {
                $breadcrumbs[] = $before . get_the_time('Y') . $after;
            }
        } // Category, Taxonomy
        elseif (is_category() || is_tax()) {
            $cat_obj       = get_queried_object();
            $cat_code      = get_term_parents_list($cat_obj?->term_id, $cat_obj?->taxonomy, ['separator' => '']);
            $cat_code      = str_replace('<a', '<li><a', $cat_code);
            $breadcrumbs[] = str_replace('</a>', '</a></li>', $cat_code) . $before . single_cat_title('', false) . $after;
        } // 404 Page
        elseif (is_404()) {
            $breadcrumbs[] = $before . __('Not Found', TEXT_DOMAIN) . $after;
        }

        // If there is pagination
        if (get_query_var('paged')) {
            $breadcrumbs[] = $before . ' (' . __('page', TEXT_DOMAIN) . ' ' . get_query_var('paged') . ')' . $after;
        }

        // Display Breadcrumbs.
        echo '<ul id="breadcrumbs" class="breadcrumbs" aria-label="Breadcrumbs">';
        echo implode('', $breadcrumbs);
        echo '</ul>';

        // Reset query
        wp_reset_query();
    }

    // -------------------------------------------------------------

    /**
     * @param string $template
     *
     * @return array|\WP_Post|null
     */
    public static function getPageTemplate(string $template): array|\WP_Post|null
    {
        $query = new \WP_Query([
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => $template,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if ($query->have_posts()) {
            $query->the_post();

            $post = get_post();
            wp_reset_postdata();

            return $post;
        }

        wp_reset_postdata();

        return null;
    }

    // -------------------------------------------------------------

    /**
     * @param string $template
     *
     * @return bool|string|null
     */
    public static function getPageLinkTemplate(string $template): bool|string|null
    {
        $query = new \WP_Query([
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => $template,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if ($query->have_posts()) {
            $query->the_post();
            $url = get_permalink();
            wp_reset_postdata();

            return $url;
        }

        wp_reset_postdata();

        return null;
    }

    // -------------------------------------------------------------

    /**
     * @param int|false $user_id
     *
     * @return string
     */
    public static function getUserLink(int|false $user_id = false): string
    {
        if (! $user_id) {
            $user_id = (int) get_the_author_meta('ID');
        }

        return get_author_posts_url($user_id);
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $obj
     * @param mixed $fallback
     *
     * @return mixed
     */
    public static function getPermalink(mixed $obj = null, mixed $fallback = false): mixed
    {
        if (empty($obj) && ! empty($fallback)) {
            return $fallback;
        }
        if (is_numeric($obj) || empty($obj)) {
            return get_permalink($obj);
        }
        if (is_string($obj)) {
            return $obj;
        }

        if (is_array($obj)) {
            if (isset($obj['term_id'])) {
                return get_term_link($obj['term_id']);
            }
            if (isset($obj['user_login'], $obj['ID'])) {
                return self::getUserLink($obj['ID']);
            }
            if (isset($obj['ID'])) {
                return get_permalink($obj['ID']);
            }
        }
        if (is_object($obj)) {
            $val_class = get_class($obj);
            if ($val_class === 'WP_Post') {
                return get_permalink($obj->ID);
            }
            if ($val_class === 'WP_Term') {
                return get_term_link($obj->term_id);
            }
            if ($val_class === 'WP_User') {
                return self::getUserLink($obj->ID);
            }
        }

        return $fallback;
    }

    // -------------------------------------------------------------

    /**
     * @param mixed|null $obj
     * @param mixed $fallback
     *
     * @return mixed
     */
    public static function getId(mixed $obj = null, mixed $fallback = false): mixed
    {
        if (empty($obj) && $fallback) {
            return get_the_ID();
        }
        if (is_numeric($obj)) {
            return (int) $obj;
        }
        if (self::isUrl($obj)) {
            return url_to_postid($obj);
        }
        if (is_string($obj)) {
            return (int) $obj;
        }
        if (is_array($obj)) {
            if (isset($obj['term_id'])) {
                return $obj['term_id'];
            }
            if (isset($obj['ID'])) {
                return $obj['ID'];
            }
        }
        if (is_object($obj)) {
            $val_class = get_class($obj);
            if ($val_class === 'WP_Post') {
                return $obj->ID;
            }
            if ($val_class === 'WP_Term') {
                return $obj->term_id;
            }
            if ($val_class === 'WP_User') {
                return $obj->ID;
            }
        }

        return \false;
    }

    // -------------------------------------------------------------

    /**
     * @param string|null $url
     *
     * @return int
     */
    public static function getPostIdFromUrl(?string $url = ''): int
    {
        if (! $url) {
            global $wp;
            $url = home_url(add_query_arg([], $wp->request));
        }

        return url_to_postid($url);
    }

    // -------------------------------------------------------------

    /**
     * @param string $post_type - max 20 characters
     *
     * @return array|\WP_Post|null
     */
    public static function getCustomPostOption(string $post_type = 'gau_css'): array|\WP_Post|null
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
        $post_id = self::getThemeMod($post_type . '_option_id');

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

    // -------------------------------------------------------------

    /**
     * @param string $post_type - max 20 characters
     * @param bool $encode
     *
     * @return array|string
     */
    public static function getCustomPostContent(string $post_type, bool $encode = false): array|string
    {
        if (empty($post_type)) {
            return '';
        }

        $post = self::getCustomPostOption($post_type);
        if (isset($post->post_content)) {
            $post_content = wp_unslash($post->post_content);
            if ($encode) {
                $post_content = wp_unslash(base64_decode($post->post_content));
            }

            return $post_content;
        }

        return '';
    }

    // -------------------------------------------------------------

    /**
     * @param string $mixed
     * @param string $post_type - max 20 characters
     * @param string $code_type
     * @param bool $encode
     * @param string $preprocessed
     *
     * @return array|int|\WP_Error|\WP_Post|null
     */
    public static function updateCustomPostOption(
        string $mixed = '',
        string $post_type = 'gau_css',
        string $code_type = 'css',
        bool $encode = false,
        string $preprocessed = ''
    ): \WP_Error|array|int|\WP_Post|null {
        $post_type = $post_type ?: 'gau_css';
        $code_type = $code_type ?: 'text/css';

        if (in_array($code_type, ['css', 'text/css'])) {
            $mixed = self::stripAllTags($mixed, true, false);
        }

        if ($encode) {
            $mixed = base64_encode($mixed);
        }

        //		else if ( in_array( $code_type, [ 'html', 'text/html' ] ) ) {
        //			$mixed = base64_encode( $mixed );
        //		}

        $post_data = [
            'post_type'             => $post_type,
            'post_status'           => 'publish',
            'post_content'          => $mixed,
            'post_content_filtered' => $preprocessed,
        ];

        // Update `post` if it already exists, otherwise create a new one.
        $post = self::getCustomPostOption($post_type);
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

    // -------------------------------------------------------------

    /**
     * @param string $css - CSS, stored in `post_content`.
     * @param string $post_type - max 20 characters
     * @param bool $encode
     * @param string $preprocessed - Pre-processed CSS, stored in `post_content_filtered`. Normally empty string.
     *
     * @return array|int|\WP_Error|\WP_Post|null
     */
    public static function updateCustomCssPost(
        string $css,
        string $post_type = 'gau_css',
        bool $encode = false,
        string $preprocessed = ''
    ): \WP_Error|array|int|\WP_Post|null {
        return self::updateCustomPostOption($css, $post_type, 'text/css', $encode, $preprocessed);
    }

    // -------------------------------------------------------------

    /**
     * @param string $post_type
     * @param string|null $option
     *
     * @return string|string[]
     */
    public static function getAspectRatioOption(string $post_type = '', ?string $option = ''): array|string
    {
        $post_type = $post_type ?: 'post';
        $option    = $option ?? 'aspect_ratio__options';

        $aspect_ratio_options = self::getOption($option);
        $width                = $aspect_ratio_options['ar-' . $post_type . '-width']  ?? '';
        $height               = $aspect_ratio_options['ar-' . $post_type . '-height'] ?? '';

        return ($width && $height) ? [$width, $height] : '';
    }

    // -------------------------------------------------------------

    /**
     * @param string $post_type
     * @param string $default
     *
     * @return string
     */
    public static function aspectRatioClass(string $post_type = 'post', string $default = 'ar[3-2]'): string
    {
        $ratio = self::getAspectRatioOption($post_type);

        $ratio_x = $ratio[0] ?? '';
        $ratio_y = $ratio[1] ?? '';
        if (! $ratio_x || ! $ratio_y) {
            return $default;
        }

        return 'ar[' . $ratio_x . '-' . $ratio_y . ']';
    }

    // -------------------------------------------------------------

    /**
     * @param string $post_type
     * @param string|null $option
     * @param string $default
     *
     * @return object
     */
    public static function getAspectRatio(string $post_type = 'post', ?string $option = '', string $default = 'ar[3-2]'): object
    {
        $ratio = self::getAspectRatioOption($post_type, $option);

        $ratio_x = $ratio[0] ?? '';
        $ratio_y = $ratio[1] ?? '';

        $ratio_style = '';
        if (! $ratio_x || ! $ratio_y) {
            $ratio_class = $default;
        } else {
            //$ratio_class         = 'ar-' . $ratio_x . '-' . $ratio_y;
            $ratio_class             = 'ar[' . $ratio_x . '-' . $ratio_y . ']';
            $ar_aspect_ratio_default = self::filterSettingOptions('aspect_ratio_default', []);

            if (is_array($ar_aspect_ratio_default) && ! in_array($ratio_x . '-' . $ratio_y, $ar_aspect_ratio_default, false)) {
                $css = CSS::get_instance();

                $css->set_selector('.' . $ratio_class);
                $css->add_property('height', 0);

                $pb = ($ratio_y / $ratio_x) * 100;
                $css->add_property('padding-bottom', $pb . '%');
                //$css->add_property( 'aspect-ratio', $ratio_x . '/' . $ratio_y );

                $ratio_style = $css->css_output();
            }
        }

        return (object) [
            'class' => $ratio_class,
            'style' => $ratio_style,
        ];
    }

    // -------------------------------------------------------------

    /**
     * Get any necessary microdata.
     *
     * @param string|null $context The element to target.
     *
     * @return string Our final attribute to add to the element.
     *
     * GeneratePress
     */
    public static function microdata(?string $context): string
    {
        $data = false;

        if ('body' === $context) {
            $type = 'WebPage';

            if (is_home() || is_archive() || is_attachment() || is_tax() || is_single()) {
                $type = 'Blog';
            }

            if (is_search()) {
                $type = 'SearchResultsPage';
            }

            if (function_exists('is_shop') && \is_shop()) {
                $type = 'Collection';
            }

            if (function_exists('is_product_category') && \is_product_category()) {
                $type = 'Collection';
            }

            $data = sprintf('itemtype="https://schema.org/%s" itemscope', esc_html($type));
        }

        if ('header' === $context) {
            $data = 'itemtype="https://schema.org/WPHeader" itemscope';
        }

        if ('navigation' === $context) {
            $data = 'itemtype="https://schema.org/SiteNavigationElement" itemscope';
        }

        if ('article' === $context) {
            $type = apply_filters('article_itemtype_filter', 'CreativeWork');
            $data = sprintf('itemtype="https://schema.org/%s" itemscope', esc_html($type));
        }

        if ('product' === $context) {
            $data = 'itemtype="https://schema.org/Product" itemscope';
        }

        if ('post-author' === $context) {
            $data = 'itemprop="author" itemtype="https://schema.org/Person" itemscope';
        }

        if ('comment-body' === $context) {
            $data = 'itemtype="https://schema.org/Comment" itemscope';
        }

        if ('comment-author' === $context) {
            $data = 'itemprop="author" itemtype="https://schema.org/Person" itemscope';
        }

        if ('sidebar' === $context) {
            $data = 'itemtype="https://schema.org/WPSideBar" itemscope';
        }

        if ('footer' === $context) {
            $data = 'itemtype="https://schema.org/WPFooter" itemscope';
        }

        if ('text' === $context) {
            $data = 'itemprop="text"';
        }

        if ('url' === $context) {
            $data = 'itemprop="url"';
        }

        if ('author-name' === $context) {
            $data = 'itemprop="name"';
        }

        if ('breadcrumb' === $context) {
            $data = 'itemtype="https://schema.org/BreadcrumbList" itemscope';
        }

        if ('logo' === $context) {
            $data = 'itemprop="logo" itemtype="https://schema.org/ImageObject" itemscope';
        }

        if ('review' === $context) {
            $data = 'itemtype="https://schema.org/Review" itemscope';
        }

        if ('image' === $context) {
            $data = 'itemprop="image" itemtype="https://schema.org/ImageObject" itemscope';
        }

        if ('video' === $context) {
            $data = 'itemprop="video" itemtype="https://schema.org/VideoObject" itemscope';
        }

        if ('publisher' === $context) {
            $data = 'itemtype="https://schema.org/Organization" itemscope';
        }

        if ('date-published' === $context) {
            $data = 'itemprop="datePublished"';
        }

        if ('date-modified' === $context) {
            $data = 'itemprop="dateModified"';
        }

        if ('rating' === $context) {
            $data = 'itemtype="https://schema.org/Rating" itemscope';
        }

        if ('faq' === $context) {
            $data = 'itemtype="https://schema.org/FAQPage" itemscope';
        }

        if ('question' === $context) {
            $data = 'itemtype="https://schema.org/Question" itemscope';
        }

        if ('answer' === $context) {
            $data = 'itemtype="https://schema.org/Answer" itemscope';
        }

        return apply_filters("{$context}_microdata_filter", $data);
    }

    // -------------------------------------------------------------

    /**
     * @param int|null $term_id
     * @param string $taxonomy
     * @param bool $hide_empty
     *
     * @return int[]|string|string[]|\WP_Error|\WP_Term[]|null
     */
    public static function childTerms(?int $term_id, string $taxonomy, bool $hide_empty = true): array|\WP_Error|string|null
    {
        if (! $term_id || ! taxonomy_exists($taxonomy)) {
            return null;
        }

        $child_terms = get_terms([
            'taxonomy'   => $taxonomy,
            'parent'     => $term_id,
            'hide_empty' => $hide_empty,
        ]);

        if (empty($child_terms) || is_wp_error($child_terms)) {
            return null;
        }

        return $child_terms;
    }

    // -------------------------------------------------------------

    /**
     * @param string|null $taxonomy
     * @param bool $hide_empty
     * @param int|null $parent
     * @param mixed|null $selected_request
     * @param int|null $disabled_parent
     * @param bool $only_parent
     *
     * @return array|null
     */
    public static function hierarchyTerms(
        ?string $taxonomy,
        bool $hide_empty = true,
        ?int $parent = null,
        mixed $selected_request = null,
        ?int $disabled_parent = null,
        bool $only_parent = false
    ): ?array {
        if ($taxonomy === null || ! taxonomy_exists($taxonomy)) {
            return null;
        }

        $args = [
            'taxonomy'     => $taxonomy,
            'hide_empty'   => $hide_empty,
            'hierarchical' => true,
            'parent'       => 0,
        ];

        if (! is_null($parent) && $parent >= 0) {
            $args['parent'] = $parent;
        }

        $terms = get_terms($args);
        if (empty($terms) || is_wp_error($terms)) {
            return null;
        }

        $options = [];
        foreach ($terms as $term) {
            // Append options from the term and its children using the spread operator
            $options = [
                ...$options,
                ...self::_buildTreeTerms($term, $hide_empty, 0, $selected_request, $disabled_parent, $only_parent),
            ];
        }

        return $options;
    }

    // -------------------------------------------------------------

    /**
     * @param mixed $term
     * @param bool $hide_empty
     * @param int $depth
     * @param mixed|null $selected_request
     * @param int|null $disabled_parent
     * @param bool $only_parent
     *
     * @return array
     *
     * @private
     */
    private static function _buildTreeTerms(
        mixed $term,
        bool $hide_empty = true,
        int $depth = 0,
        mixed $selected_request = null,
        ?int $disabled_parent = null,
        bool $only_parent = false
    ): array {
        $options = [];

        if ($term?->term_id) {
            $prefix   = str_repeat('— ', $depth);
            $selected = '';

            if (! is_array($selected_request)) {
                $selected = ' ' . selected($selected_request, $term->term_id, false);
            } elseif (in_array($term->term_id, $selected_request, false)) {
                $selected = ' selected="selected"';
            }

            $disabled = '';
            if (isset($disabled_parent) && $term?->parent === $disabled_parent) {
                $disabled = ' disabled="disabled"';
            }

            $options[] = [
                'value'    => $term?->term_id,
                'label'    => $prefix . $term?->name,
                'selected' => ! empty($selected),
                'disabled' => ! empty($disabled),
            ];

            if (! $only_parent) {
                $child_terms = get_terms([
                    'taxonomy'   => $term?->taxonomy,
                    'hide_empty' => $hide_empty,
                    'parent'     => $term?->term_id,
                ]);

                if (! empty($child_terms) && ! is_wp_error($child_terms)) {
                    foreach ($child_terms as $child_term) {
                        // Append child options directly to the array
                        $options = [
                            ...$options,
                            ...self::_buildTreeTerms($child_term, $hide_empty, $depth + 1, $selected_request, $disabled_parent),
                        ];
                    }
                }
            }
        }

        return $options;
    }

    // -------------------------------------------------------------
}
