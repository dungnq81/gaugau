<?php

namespace Addons\Optimizer\Lazy_Load;

\defined('ABSPATH') || die;

class Lazy_Load_Videos extends Abstract_Lazy_Load
{
    /**
     * Regex parts for checking content.
     *
     * @var string
     */
    public string $regexp = '/(?:<video[^>]*)(?:(?:\/>)|(?:>.*?<\/video>))/is';

    /**
     * Regex for already replaced items.
     *
     * @var string
     */
    public string $regex_replaced = "/class=['\"][\w\s]*(lazy)+[\w\s]*['\"]/is";

    /**
     * Search patterns.
     *
     * @var array
     */
    public array $patterns = [
        '/(<video[^>]+)(src)=["|\']((?!data).*?)["|\']/i',
    ];

    /**
     * Replace patterns.
     *
     * @var array
     */
    public array $replacements = [
        '$1data-$2="$3"',
    ];

    /**
     * @param $element
     *
     * @return string|array
     */
    public function add_lazyload_class($element): string|array
    {
        return str_replace('<video', '<video class="lazy"', $element);
    }
}
