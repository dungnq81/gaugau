<?php

namespace Addons\Optimizer\Font;

use Addons\Base\Singleton;

\defined('ABSPATH') || die;

const GOOGLE_API_URL       = 'https://fonts.googleapis.com/';
const GOOGLE_FONTS_DISPLAY = 'swap';

/**
 * @author SiteGround
 * Modified by HD Team
 */
final class Font
{
    use Singleton;

    public mixed $optimizer_options = [];

    // ------------------------------------------------------

    public ?string $assets_dir = null;

    public mixed $wp_filesystem;

    /**
     * Google Fonts regular expression
     *
     * @var string[]
     */
    public array $regex_parts = [
        '~', // The php quotes.
        '<link', // Match the opening part of link tags.
        '(?:\s+(?:(?!href\s*=\s*)[^>])+)?', // Negative lookahead asserting the regex does not match href attribute.
        '(?:\s+href\s*=\s*(?P<quotes>[\'|"]))', // Match the href attribute followed by single or double quotes. Create a `quotes` group, so we can use it later.
        '(', // Open the capturing group for the href value.
        '(?:https?:)?', // Match the protocol, which is optional. Sometimes the fons are added. Without protocol i.e. //fonts.googleapi.com/css.
        '\/\/fonts\.googleapis\.com\/', // Match that the href value is a Google font link.
        '(?P<type>css2?)', // The type of the fonts CSS/CSS2.
        '(?:(?!(?P=quotes)).)+', // Match anything in the href attribute until the closing quote.
        ')', // Close the capturing group.
        '(?P=quotes)', // Match the closing quote.
        '(?:\s+.*?)?', // Match anything else after the href tag.
        '[>]', // Until the closing tag if found.
        '~', // The php quotes.
        'ims',
    ];

    // ------------------------------------------------------

    private function init(): void
    {
        $this->optimizer_options = get_option('optimizer__options');

        $this->_set_assets_directory_path();
        $this->_setup_wp_filesystem();
    }

    // ------------------------------------------------------

    /**
     * @return void
     */
    private function _set_assets_directory_path(): void
    {
        // Bail if the assets dir has been set.
        if (null !== $this->assets_dir) {
            return;
        }

        $cache_dir = $this->_get_cache_dir();

        // Build the assets dir name.
        $directory = $cache_dir . '/addons';

        // Check if a directory exists and try to create it if not.
        if (is_dir($directory) || wp_mkdir_p($directory)) {
            $this->assets_dir = trailingslashit($directory);
        }
    }

    // ------------------------------------------------------

    /**
     * @return void
     */
    private function _setup_wp_filesystem(): void
    {
        $this->wp_filesystem = $this->_wp_filesystem();
    }

    // --------------------------------------------------

    /**
     * @return mixed
     */
    private function _wp_filesystem(): mixed
    {
        global $wp_filesystem;

        // Initialize the WP filesystem, no more using 'file-put-contents' function.
        // Front-end only. In the back-end, it's already included
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        return $wp_filesystem;
    }

    // ------------------------------------------------------

    /**
     * @return string
     */
    private function _get_cache_dir(): string
    {
        // Set the main cache dir.
        $dir = WP_CONTENT_DIR . '/cache';

        // Bail if the main directory exists.
        if (is_dir($dir)) { // phpcs:ignore
            return $dir;
        }

        if (! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    // ------------------------------------------------------

    /**
     * @param $html
     *
     * @return array|mixed|string|string[]|void|null
     */
    public function run($html)
    {
        // Get fonts if any.
        $fonts = $this->get_items($html);

        // Insert preload links for local fonts.
        $html = $this->font_preload($html);

        // get font optimize options
        $font_optimize = $this->optimizer_options[ 'font_optimize' ] ?? 0;

        // Bail if there are no fonts, no options or if there is only one font.
        if (empty($font_optimize) || empty($fonts)) {
            return $html;
        }

        $_fonts = $fonts;

        // The methods that should be called to combine the fonts.
        $methods = [
            'parse_fonts',
            'beautify',
            'prepare_urls',
            'get_combined_css',
        ];

        foreach ($methods as $method) {
            $_fonts = $this->$method($_fonts);
        }

        $html = preg_replace('~<\/title>~', '</title>' . $_fonts, $html, 1);

        // Remove old fonts.
        foreach ($fonts as $font) {
            $html = str_replace($font[0], '', $html);
        }

        return preg_replace('~<\/title>~', '</title><link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin/><link rel="preconnect" href="https://fonts.googleapis.com"/>', $html, 1);
    }

    // ------------------------------------------------------

    /**
     * @param $html
     *
     * @return array
     */
    public function get_items($html): array
    {
        // Build the regular expression.
        $regex = implode('', $this->regex_parts);

        // Check for items.
        preg_match_all($regex, $html, $matches, PREG_SET_ORDER);

        return $matches;
    }

    // ------------------------------------------------------

    /**
     * Parse fonts.
     *
     * @param $fonts
     *
     * @return array|void
     */
    public function parse_fonts($fonts)
    {
        $parts = [];
        foreach ($fonts as $font) {
            // Decode the entities.
            $url = html_entity_decode($font[2]);

            // Parse the url and get the query string.
            $query_string = wp_parse_url($url, PHP_URL_QUERY);

            // Bail if the query string is empty.
            if (! isset($query_string)) {
                return;
            }

            // Parse the query args.
            $parsed_font = wp_parse_args($query_string);

            // Assign parsed fonts to the part array.
            $parts[ $font['type'] ]['fonts'][] = $parsed_font['family'];

            // Add a subset to a collection.
            if (isset($parsed_font['subset'])) {
                $parts[ $font['type'] ]['subset'][] = $parsed_font['subset'];
            }
        }

        return $parts;
    }

    // ------------------------------------------------------

    /**
     * Beautify and remove duplicates.
     *
     * @param $parts
     *
     * @return mixed
     */
    public function beautify($parts): mixed
    {
        // URL encode & convert characters to HTML entities.
        foreach ($parts as $key => $type) {
            if ('css2' === (string) $key) {
                continue;
            }

            $type = array_map(static fn ($item) => array_map(
                'rawurlencode',
                array_map(
                    static fn ($value) => htmlentities($value, ENT_QUOTES, 'UTF-8'),
                    $item
                )
            ), $type);

            $parts[ $key ] = $type;
        }

        // Remove duplicates.
        foreach ($parts as $key => $type) {
            if ('css2' === (string) $key) {
                continue;
            }

            $type = array_map(
                'array_filter',
                array_map(
                    'array_unique',
                    $type
                )
            );

            // Assign an array with removed duplicates to the main one.
            $parts[ $key ] = $type;
        }

        return $parts;
    }

    // ------------------------------------------------------

    /**
     * Prepare the combined urls.
     *
     * @param $fonts
     *
     * @return array
     */
    public function prepare_urls($fonts): array
    {
        // Define the display variable.
        $display = GOOGLE_FONTS_DISPLAY ?: 'swap';

        $urls = [];

        // Implode different fonts into one.
        foreach ($fonts as $css_type => $value) {
            $url     = GOOGLE_API_URL . $css_type;
            $subsets = ! empty($value['subset']) ? implode(',', $value['subset']) : '';
            switch ($css_type) {
                case 'css':
                    $url .= '?family=' . implode('%7C', $value['fonts']);

                    break;
                case 'css2':
                    $query_string = '';
                    foreach ($value['fonts'] as $index => $font_family) {
                        $delimiter    = (0 === $index) ? '?' : '&';
                        $query_string .= $delimiter . 'family=' . $font_family;
                    }
                    $url .= $query_string;

                    break;
            }

            $urls[] = $url . '&display=' . $display . '&subset=' . $subsets;
        }

        return $urls;
    }

    // ------------------------------------------------------

    /**
     * Get combined css.
     *
     * @param $urls
     *
     * @return string
     */
    public function get_combined_css($urls): string
    {
        // Gather all the Google fonts and generate the combined tag.
        $combined_tags = [];
        $css           = '';
        foreach ($urls as $url) {
            // Get the font CSS.
            $css             .= $this->get_external_file_content($url, 'css', 'fonts');
            $combined_tags[] = '<link rel="stylesheet" href="' . $url . '" />';
        }

        // Return the combined tag if the CSS is empty.
        if (! $css) {
            return implode('', $combined_tags);
        }

        // Return combined tag if AMP plugin is active.
        if (function_exists('ampforwp_is_amp_endpoint') && \ampforwp_is_amp_endpoint()) {
            return implode('', $combined_tags);
        }

        // Force combined tag
        $font_combined_css = $this->optimizer_options[ 'font_combined_css' ] ?? 0;
        if (! empty($font_combined_css)) {
            return implode('', $combined_tags);
        }

        // Return the inline CSS.
        return '<style>' . $css . '</style>';
    }

    // ------------------------------------------------------

    /**
     * @param $html
     *
     * @return array|mixed|string|string[]|null
     */
    public function font_preload($html): mixed
    {
        // Check if there are any urls inserted by the user.
        $urls = $this->optimizer_options[ 'font_preload' ] ?? false;

        // Return, if no url's are set by the user.
        if (empty($urls)) {
            return $html;
        }

        $new_html = '';

        foreach ($urls as $url) {
            $new_html .= '<link rel="preload" as="font" href="' . $url . '" crossorigin />';
        }

        return preg_replace('~<\/title>~', '</title>' . $new_html, $html, 1);
    }

    // ------------------------------------------------------

    /**
     * @param $url
     * @param $type
     * @param string $add_dir
     *
     * @return false|string
     */
    public function get_external_file_content($url, $type, string $add_dir = ''): false|string
    {
        // Generate a unique hashtag using the url.
        $hash = md5($url);

        // Build the dir.
        $dir = $this->assets_dir . $add_dir;

        // Build the file path.
        $file_path = $dir . '/' . $hash . '.' . $type;

        // Check if a cached version of the file exists.
        if ($this->wp_filesystem->exists($file_path)) {
            // Get the file content.
            $content = $this->wp_filesystem->get_contents($file_path);

            // Return the file content if it's not empty.
            if (! empty($content)) {
                return $content;
            }
        }

        // THE FILE DOESN'T EXIST.

        // Create an additional dir if it doesn't exist.
        if (! $this->wp_filesystem->exists($dir)) {
            $this->wp_filesystem->mkdir($dir);
        }

        // Try to fetch the file.
        $request = wp_remote_get($url);

        // Bail if the request fails.
        if (is_wp_error($request)) {
            return false;
        }

        if (200 !== wp_remote_retrieve_response_code($request)) {
            return false;
        }

        // Try to create the file and bail if for some reason it's not created.
        if (! $this->wp_filesystem->touch($file_path)) {
            return false;
        }

        // Get the file content from the request.
        $file_content = wp_remote_retrieve_body($request);

        // Add the file content in the file, so it can be cached.
        $this->wp_filesystem->put_contents(
            $file_path,
            $file_content
        );

        // Finally, return the file content.
        return $file_content;
    }
}
