<?php

namespace Addons\Security;

use Addons\Base\Abstract_Htaccess;

\defined('ABSPATH') || die;

final class Opml extends Abstract_Htaccess
{
    /**
     * @var string|null
     */
    public ?string $template = 'wp-links-opml.tpl';

    /**
     * Regular expressions to check if the rules are enabled.
     *
     * @var array Regular expressions to check if the rules are enabled.
     */
    public array $rules = [
        'enabled'     => '/\#\s+wp-links-opml\s+Disable/si',
        'disabled'    => '/\#\s+wp-links-opml\s+Disable(.+?)\#\s+wp-links-opml\s+Disable\s+END(\n)?/ims',
        'disable_all' => '/\#\s+wp-links-opml\s+Disable(.+?)\#\s+wp-links-opml\s+Disable\s+END(\n)?/ims',
    ];
}
