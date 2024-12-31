<?php

namespace Addons\Optimizer;

use Addons\Base\Abstract_Htaccess;

\defined('ABSPATH') || die;

final class Ssl extends Abstract_Htaccess
{
    /**
     * The path to the htaccess template.
     *
     * @var string|null
     */
    public ?string $template = 'ssl.tpl';

    /**
     * Regular expressions to check if the rules are enabled.
     *
     * @var array
     */
    public array $rules = [
        'enabled'     => '/\#\s+Https/si',
        'disabled'    => '/\#\s+Https(.+?)\#\s+Https\s+END(\n)?/ims',
        'disable_all' => '/\#\s+Https(.+?)\#\s+Https\s+END(\n)?/ims',
    ];

    // --------------------------------------------------

    /**
     * Check if the current domain has a valid ssl certificate.
     *
     * @return bool True is the domain has a certificate, false otherwise.
     */
    public function has_certificate(): bool
    {
        $site_url = get_option('siteurl');

        // Change 'siteurl' protocol.
        if (str_starts_with($site_url, 'http:')) {
            $site_url = str_replace('http', 'https', $site_url);
        }

        // Create a streams context.
        $stream = stream_context_create(
            [
                'ssl' => [
                    'capture_peer_cert' => true,
                ],
            ]
        );

        // Parse the url.
        $parse_url = parse_url($site_url, PHP_URL_HOST);

        // Create the stream socket client.
        $read = @stream_socket_client(
            'ssl://' . $parse_url . ':443',
            $errno,
            $errstr,
            3,
            STREAM_CLIENT_CONNECT,
            $stream
        );

        // Bail if the stream failed.
        if (false === $read) {
            return false;
        }

        // Get the params we are checking.
        $cont = @stream_context_get_params($read);

        return ! is_null($cont['options']['ssl']['peer_certificate']);
    }

    // --------------------------------------------------

    /**
     * Disable the rule and remove it from the htaccess.
     *
     * @return bool
     */
    public function disable(): bool
    {
        // Switch the protocol in a database.
        $protocol_switched     = $this->switch_protocol(false);
        $disable_from_htaccess = true;

        // Remove the rule from htaccess for single sites.
        if (! is_multisite()) {
            $disable_from_htaccess = parent::disable();
        }

        return ! (! $protocol_switched || ! $disable_from_htaccess);
    }

    // --------------------------------------------------

    /**
     *  Add rule to htaccess and enable it.
     *
     * @return bool
     */
    public function enable(): bool
    {
        // Bail if the domain doesn't have a certificate.
        if (! $this->has_certificate()) {
            return false;
        }

        // Switch the protocol in a database.
        $protocol_switched    = $this->switch_protocol(true);
        $enable_from_htaccess = true;

        // Add rule to htaccess for single sites.
        if (! is_multisite()) {
            $enable_from_htaccess = parent::enable();
        }

        return ! (! $protocol_switched || ! $enable_from_htaccess);
    }

    // --------------------------------------------------

    /**
     * Change the url protocol.
     *
     * @param bool $ssl
     *
     * @return bool The result.
     */
    private function switch_protocol(bool $ssl = false): bool
    {
        $from = (true === $ssl) ? 'http' : 'https';
        $to   = (true === $ssl) ? 'https' : 'http';

        // Strip the protocol from site url.
        $site_url_without_protocol = preg_replace('#^https?#', '', get_option('siteurl'));

        // Build the command.
        $command = sprintf(
            "wp search-replace '%s' '%s' --all-tables",
            $from . $site_url_without_protocol,
            $to . $site_url_without_protocol
        );

        // Execute the command.
        exec($command, $output, $status);

        // Check for errors during the import.
        return empty($status);
    }

    // --------------------------------------------------

    /**
     * Creates an array of insecure links that should be https and an array of secure links to replace with
     *
     * @return array
     */
    public function get_url_list(): array
    {
        // Build the search links.
        $search = [
            str_replace('https://', 'http://', trailingslashit(esc_url(network_home_url()))),
            "src='http://",
            'src="http://',
        ];

        return [
            'search'  => $search, // The search links.
            'replace' => str_replace('http://', 'https://', $search), // The replace links.
        ];
    }

    // --------------------------------------------------

    /**
     * Replace all insecure links before the page is sent to the visitor's browser.
     *
     * @param string $content The page content.
     *
     * @return string Modified content.
     */
    public function replace_insecure_links(string $content): string
    {
        // Get the url list.
        $urls = $this->get_url_list();

        // now replace these links.
        $content = str_replace($urls['search'], $urls['replace'], $content);

        // Replace all http links except hyperlinks
        // All tags with src attr are already fixed by str_replace.
        $pattern = [
            '/url\([\'"]?\K(http:\/\/)(?=[^)]+)/i',
            '/<link([^>]*?)href=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
            '/<meta property="og:image" .*?content=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
            '/<form [^>]*?action=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
        ];

        // Return modified content.
        return preg_replace($pattern, 'https://', $content);
    }
}
