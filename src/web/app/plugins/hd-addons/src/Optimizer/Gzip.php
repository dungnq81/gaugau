<?php

namespace Addons\Optimizer;

use Addons\Base\Abstract_Htaccess;

\defined('ABSPATH') || die;

final class Gzip extends Abstract_Htaccess
{
    /**
     * @var string|null
     */
    public ?string $template = 'gzip.tpl';

    /**
     * @var array|string[]
     */
    public array $rules = [
        'enabled'     => '/\#\s+Gzip/si',
        'disabled'    => '/\#\s+Gzip(.+?)\#\s+Gzip\s+END(\n)?/ims',
        'disable_all' => '/\#\s+Gzip(.+?)\#\s+Gzip\s+END(\n)?/ims',
    ];
}
