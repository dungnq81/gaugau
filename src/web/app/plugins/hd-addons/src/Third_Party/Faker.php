<?php

namespace Addons\Third_Party;

use Addons\Base\Singleton;

\defined('ABSPATH') || die;

final class Faker
{
    use Singleton;

    // -------------------------------------------------------------

    /**
     * @throws \JsonException
     */
    private function init(): void
    {
        $this->_acf_faker();
    }

    // -------------------------------------------------------------

    /**
     * @throws \JsonException
     *
     * @return void
     */
    private function _acf_faker(): void
    {
        if (check_plugin_active('advanced-custom-fields-pro/acf.php')) {
            add_filter('pre_http_request', static function ($preempt, $parsed_args, $url) {
                // Intercept ACF activation request
                if (str_contains($url, 'https://connect.advancedcustomfields.com/v2/plugins/activate?p=pro')) {
                    return [
                        'headers'  => [],
                        'body'     => json_encode([
                            'message'        => 'Licence key activated. Updates are now enabled',
                            'license'        => 'GPL001122334455AA6677BB8899CC000',
                            'license_status' => [
                                'status'            => 'active',
                                'lifetime'          => true,
                                'name'              => 'Agency',
                                'view_licenses_url' => 'https://www.advancedcustomfields.com/my-account/view-licenses/',
                            ],
                            'status'         => 1,
                        ], JSON_THROW_ON_ERROR),
                        'response' => [
                            'code'    => 200,
                            'message' => 'OK',
                        ],
                    ];
                }

                // Intercept ACF validation request
                if (str_contains($url, 'https://connect.advancedcustomfields.com/v2/plugins/validate?p=pro')) {
                    return [
                        'headers'  => [],
                        'body'     => json_encode([
                            'expiration'     => 864000,
                            'license_status' => [
                                'status'            => 'active',
                                'lifetime'          => true,
                                'name'              => 'Agency',
                                'view_licenses_url' => 'https://www.advancedcustomfields.com/my-account/view-licenses/',
                            ],
                            'status'         => 1,
                        ], JSON_THROW_ON_ERROR),
                        'response' => [
                            'code'    => 200,
                            'message' => 'OK',
                        ],
                    ];
                }

                // Intercept ACF get-info request
                if (str_contains($url, 'https://connect.advancedcustomfields.com/v2/plugins/get-info?p=pro')) {
                    return [
                        'headers'  => [],
                        'body'     => json_encode([
                            'name'    => 'Advanced Custom Fields PRO',
                            'slug'    => 'advanced-custom-fields-pro',
                            'version' => '6.x.x',
                        ], JSON_THROW_ON_ERROR),
                        'response' => [
                            'code'    => 200,
                            'message' => 'OK',
                        ],
                    ];
                }

                // Proceed with the original request if the URL doesn't match
                return $preempt;
            }, 10, 3);
        }
    }
}
