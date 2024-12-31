<?php

namespace Addons\File;

use Addons\Base\Singleton;
use Addons\File\SVG\SVG;

\defined('ABSPATH') || die;

final class File
{
    use Singleton;

    /** ----------------------------------------------- */
    private function init(): void
    {
        (SVG::get_instance());

        add_action('init', [$this, 'custom_init'], 99);
    }

    /** ----------------------------------------------- */

    /**
     * @return void
     */
    public function custom_init(): void
    {
        add_filter('upload_size_limit', [$this, 'custom_upload_size_limit']);
    }

    /** ----------------------------------------------- */

    /**
     * @param $size
     *
     * @return float|int
     */
    public function custom_upload_size_limit($size): float|int
    {
        $current_size = $size / (1024 * 1024);

        return $this->_upload_max_filesize($current_size);
    }

    /** ----------------------------------------------- */

    /**
     * @param int $default
     *
     * @return int
     */
    private function _upload_max_filesize(int $default = 2): int
    {
        $file_settings_options = get_option('file_setting__options');
        $upload_size_limit     = $file_settings_options['upload_size_limit'] ?? [];
        $value                 = $upload_size_limit['value']                 ?? 0;

        if ((int) $value > 0) {
            $upload_max_filesize = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);

            return $upload_max_filesize * 1024 * 1024;
        }

        return $default * 1024 * 1024;
    }
}
