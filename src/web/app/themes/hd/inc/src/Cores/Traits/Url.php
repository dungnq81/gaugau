<?php

namespace Cores\Traits;

use Vectorface\Whip\Whip;

\defined('ABSPATH') || die;

trait Url
{
    // --------------------------------------------------

    /**
     * Redirect to a specified URI with a given HTTP status code.
     *
     * This method handles both PHP header redirects and JavaScript/HTML
     * redirects when headers have already been sent.
     *
     * @param string $uri The URI to redirect to. Default is an empty string.
     * @param int $status The HTTP status code to use for the redirect. Default is 301.
     *
     * @return true|void
     */
    public static function redirect(string $uri = '', int $status = 301)
    {
        if (! headers_sent()) {
            wp_redirect($uri, $status);
            exit;
        }

        echo '<script>window.location.href="' . $uri . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $uri . '" /></noscript>';

        return true;
    }

    // --------------------------------------------------

    /**
     * @param $ip
     * @param $range
     *
     * @return bool
     */
    public static function ipInRange($ip, $range): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $ipPattern    = '/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})$/';
        $rangePattern = '/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})-(\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])$/';
        $cidrPattern  = '/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\/(\d|[1-2]\d|3[0-2])$/';

        // Check if it's a single IP address
        if (preg_match($ipPattern, $range)) {
            return (string) $ip === (string) $range;
        }

        // Check if it's an IP range
        if (preg_match($rangePattern, $range, $matches)) {
            $startIP = "{$matches[1]}.{$matches[2]}.{$matches[3]}.{$matches[4]}";
            $endIP   = "{$matches[1]}.{$matches[2]}.{$matches[3]}.{$matches[5]}";

            return self::_compareIPs($startIP, $endIP) < 0 && self::_compareIPs($startIP, $ip) <= 0 && self::_compareIPs($ip, $endIP) <= 0;
        }

        // Check if it's a CIDR notation
        if (preg_match($cidrPattern, $range)) {
            [$subnet, $maskLength] = explode('/', $range);

            return self::_ipCIDRCheck($ip, $subnet, $maskLength);
        }

        return false;
    }

    // --------------------------------------------------

    /**
     * @param $ip
     * @param $subnet
     * @param $maskLength
     *
     * @return bool
     */
    private static function _ipCIDRCheck($ip, $subnet, $maskLength): bool
    {
        $ip     = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask   = -1 << (32 - $maskLength);
        $subnet &= $mask; // Align the subnet to the mask

        return ($ip & $mask) === $subnet;
    }

    // --------------------------------------------------

    /**
     * @param $range
     *
     * @return bool
     */
    public static function isValidIPRange($range): bool
    {
        $ipPattern    = '/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})$/';
        $rangePattern = '/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})-(\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])$/';
        $cidrPattern  = '/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})\/(\d|[1-2]\d|3[0-2])$/';

        if (preg_match($ipPattern, $range)) {
            return true;
        }

        if (preg_match($rangePattern, $range, $matches)) {
            $startIP = "{$matches[1]}.{$matches[2]}.{$matches[3]}.{$matches[4]}";
            $endIP   = "{$matches[1]}.{$matches[2]}.{$matches[3]}.{$matches[5]}";

            return self::_compareIPs($startIP, $endIP) < 0;
        }

        if (preg_match($cidrPattern, $range)) {
            return true;
        }

        return false;
    }

    // --------------------------------------------------

    /**
     * @param $ip1
     * @param $ip2
     *
     * @return int
     */
    private static function _compareIPs($ip1, $ip2): int
    {
        $ip1Long = (int) ip2long($ip1);
        $ip2Long = (int) ip2long($ip2);

        if ($ip1Long < $ip2Long) {
            return -1;
        }

        if ($ip1Long > $ip2Long) {
            return 1;
        }

        return 0;
    }

    // --------------------------------------------------

    /**
     * Retrieve the server's IP address.
     *
     * This method checks common server environment variables and performs
     * DNS resolution to determine the server's IP address. It prioritizes
     * IPv4 over IPv6. If no valid IP is found, it returns '127.0.0.1'.
     *
     * @return string The server's IP address.
     */
    public static function serverIpAddress(): string
    {
        // Check for SERVER_ADDR first
        if (! empty($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        $hostname = gethostname();
        $ipv4     = gethostbyname($hostname);

        // Validate and return the IPv4 address
        if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ipv4;
        }

        // Get the IPv6 address using dns_get_record
        $dnsRecords = dns_get_record($hostname, DNS_AAAA);
        if (! empty($dnsRecords)) {
            foreach ($dnsRecords as $record) {
                if (isset($record['ipv6']) && filter_var($record['ipv6'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return $record['ipv6'];
                }
            }
        }

        // Return a default IP address if none found
        return '127.0.0.1';
    }

    // --------------------------------------------------

    /**
     * Get the IP address from which the user is viewing the current page.
     *
     * This method checks various server variables and headers to determine
     * the user's real IP address, handling cases like proxies and CloudFlare.
     *
     * @return string The user's IP address.
     */
    public static function getIpAddress(): string
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

            // Fallback to remote address
            if (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
                return $_SERVER['REMOTE_ADDR'];
            }
        }

        // Fallback to localhost IP
        return '127.0.0.1';
    }

    // --------------------------------------------------

    /**
     * Convert a URL to a file system path.
     *
     * @param string $url The URL to convert.
     *
     * @return string The corresponding file system path.
     */
    public static function urlToPath(string $url): string
    {
        // Ensure the URL is absolute before converting
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        // Get the home path and make the URL relative
        $homePath     = rtrim(get_home_path(), '/');
        $relativePath = wp_make_link_relative($url);

        // Combine the home path and relative path
        return $homePath . $relativePath;
    }

    // --------------------------------------------------

    /**
     * Convert a file system path to a URL.
     *
     * @param string $dir The directory path to convert.
     *
     * @return string The corresponding URL.
     */
    public static function pathToUrl(string $dir): string
    {
        $dirs = wp_upload_dir();

        // Ensure the directory path starts with the base directory
        if (! str_starts_with($dir, $dirs['basedir'])) {
            return '';
        }

        // Replace basedir with baseurl and ABSPATH with home URL
        return str_replace(
            [$dirs['basedir'], ABSPATH],
            [$dirs['baseurl'], self::home()],
            $dir
        );
    }

    // --------------------------------------------------

    /**
     * @param string $path
     *
     * @return string
     */
    public static function home(string $path = ''): string
    {
        return apply_filters('home_url_filter', esc_url(home_url($path)), $path);
    }

    // --------------------------------------------------

    /**
     * @param string $path
     *
     * @return string|null
     */
    public static function adminCurrentUrl(string $path = 'admin.php'): ?string
    {
        $parsed_url  = parse_url(wp_unslash($_SERVER['REQUEST_URI']));
        $current_url = admin_url($path);
        if ($parsed_url) {
            $current_url .= '?' . $parsed_url['query'];
        }

        return $current_url;
    }

    // --------------------------------------------------

    /**
     * @param bool $nopaging
     * @param bool $get_vars
     *
     * @return string
     */
    public static function current(bool $nopaging = true, bool $get_vars = true): string
    {
        global $wp;

        $current_url = self::home($wp->request);

        // get the position where '/page. ' text start.
        $pos = strpos($current_url, '/page');

        // remove string from the specific position
        if ($nopaging && $pos) {
            $current_url = trailingslashit(substr($current_url, 0, $pos));
        }

        if ($get_vars) {
            $queryString = http_build_query($_GET);

            if ($queryString && mb_strpos($current_url, '?')) {
                $current_url .= '&' . $queryString;
            } elseif ($queryString) {
                $current_url .= '?' . $queryString;
            }
        }

        return $current_url;
    }

    // --------------------------------------------------

    /**
     * Normalize the given path. On Windows servers backslash will be replaced
     * with slash. Removes unnecessary double slashes and double dots. Removes
     * last slash if it exists.
     *
     * Examples:
     * path::normalize("C:\\any\\path\\") returns "C:/any/path"
     * path::normalize("/your/path/..//home/") returns "/your/home"
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        $parts = explode('/', $path);
        $stack = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                // Ignore empty parts and current directory parts (.)
                continue;
            }
            if ($part === '..') {
                // Pop from the stack if part is '.' and the stack is not empty
                if (! empty($stack)) {
                    array_pop($stack);
                }
            } else {
                // Add the part to the stack
                $stack[] = $part;
            }
        }

        // Rebuild the path
        return '/' . implode('/', $stack);
    }

    // --------------------------------------------------

    /**
     * @param string $url
     *
     * @return array
     */
    public static function urlQueries(string $url): array
    {
        $queries = [];
        parse_str(wp_parse_url($url, PHP_URL_QUERY), $queries);

        return $queries;
    }

    // --------------------------------------------------

    /**
     * @param string $url
     * @param $param
     * @param null $fallback
     *
     * @return mixed|null
     */
    public static function urlQuery(string $url, $param, $fallback = null): mixed
    {
        $queries = self::urlQueries($url);

        return $queries[$param] ?? $fallback;
    }

    // --------------------------------------------------

    /**
     * Check the HTTP status of a remote URL.
     *
     * @param string $url The URL to check.
     *
     * @return int|false The HTTP response code on success, false on error.
     */
    public static function remoteStatusCheck(string $url): int|false
    {
        // Make a HEAD request to the remote URL
        $response = wp_safe_remote_head($url, [
            'timeout'   => 5,
            'sslverify' => false,
        ]);

        // Check for errors in the response
        if (is_wp_error($response)) {
            return false;
        }

        // Return the HTTP response code
        return (int) $response['response']['code'];
    }
}
