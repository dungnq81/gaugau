<?php

namespace Addons\Login_Security;

use Addons\Base\Singleton;

\defined('ABSPATH') || die;

final class Custom_Login
{
    use Singleton;

    // --------------------------------------------------

    private function init(): void
    {
        add_action('login_init', [$this, 'restrict_login_to_ips'], PHP_INT_MIN);
    }

    // --------------------------------------------------

    /**
     * Check blocked and restrict login
     *
     * @return true|void
     */
    public function restrict_login_to_ips()
    {
        $_login_security          = \filter_setting_options('login_security', false);
        $_custom_security_options = get_option('login_security__options');

        $custom_restrict_ips = $_custom_security_options['login_ips_access']   ?? [];
        $custom_blocked_ips  = $_custom_security_options['disable_ips_access'] ?? [];

        $allowed_ips = (! empty($_login_security['allowlist_ips_login_access'])) ? array_filter(array_merge($_login_security['allowlist_ips_login_access'], (array) $custom_restrict_ips)) : array_filter((array) $custom_restrict_ips);
        $blocked_ips = (! empty($_login_security['blocked_ips_login_access'])) ? array_filter(array_merge($_login_security['blocked_ips_login_access'], (array) $custom_blocked_ips)) : array_filter((array) $custom_blocked_ips);

        unset($custom_restrict_ips, $custom_blocked_ips);

        // Bail if the allowed ip list is empty.
        if (empty($allowed_ips) && empty($blocked_ips)) {
            return true;
        }

        // Check if the current IP is in the allowed list, block all other IPs not in the list.
        if (! empty($allowed_ips)) {
            foreach ($allowed_ips as $allowed_ip) {
                if ($this->_ipInRange(\ip_address(), $allowed_ip)) {
                    return true;
                }
            }

            // Update the total blocked logins counter.
            update_option('_security_total_blocked_logins', get_option('_security_total_blocked_logins', 0) + 1);

            error_log('Restricted login page: access currently not permitted - ' . \ip_address());
            wp_die(
                esc_html__('You don’t have access to this page. Please contact the administrator of this website for further assistance.', ADDONS_TEXT_DOMAIN),
                esc_html__('Restricted access', ADDONS_TEXT_DOMAIN),
                [
                    'addon_error'      => true,
                    'response'         => 403,
                    'blocked_login'    => true,
                ]
            );
        }

        // Block all IPs in the list.
        if (! empty($blocked_ips)) {
            foreach ($blocked_ips as $blocked_ip) {
                if ($this->_ipInRange(\ip_address(), $blocked_ip)) {
                    // Update the total blocked logins counter.
                    update_option('_security_total_blocked_logins', get_option('_security_total_blocked_logins', 0) + 1);

                    error_log('Restricted login page: access currently not permitted - ' . \ip_address());
                    wp_die(
                        esc_html__('You don’t have access to this page. Please contact the administrator of this website for further assistance.', ADDONS_TEXT_DOMAIN),
                        esc_html__('Restricted access', ADDONS_TEXT_DOMAIN),
                        [
                            'addon_error'      => true,
                            'response'         => 403,
                            'blocked_login'    => true,
                        ]
                    );
                }
            }
        }
    }

    // --------------------------------------------------

    /**
     * @param $ip
     * @param $range
     *
     * @return bool
     */
    private function _ipInRange($ip, $range): bool
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

            return $this->_compareIPs($startIP, $endIP) < 0 && $this->_compareIPs($startIP, $ip) <= 0 && $this->_compareIPs($ip, $endIP) <= 0;
        }

        // Check if it's a CIDR notation
        if (preg_match($cidrPattern, $range)) {
            [$subnet, $maskLength] = explode('/', $range);

            return $this->_ipCIDRCheck($ip, $subnet, $maskLength);
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
    private function _compareIPs($ip1, $ip2): int
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
     * @param $ip
     * @param $subnet
     * @param $maskLength
     *
     * @return bool
     */
    private function _ipCIDRCheck($ip, $subnet, $maskLength): bool
    {
        $ip     = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask   = -1 << (32 - $maskLength);
        $subnet &= $mask; // Align the subnet to the mask

        return ($ip & $mask) === $subnet;
    }
}
