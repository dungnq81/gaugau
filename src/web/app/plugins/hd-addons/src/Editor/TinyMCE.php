<?php

namespace Addons\Editor;

\defined('ABSPATH') || die;

/**
 * TinyMCE Plugin
 *
 * @author Gaudev
 */
class TinyMCE
{
    // --------------------------------------------------

    public function __construct()
    {
        add_filter('mce_buttons', [$this, 'mce_buttons']);
        add_filter('mce_external_plugins', [$this, 'mce_external_plugins']);
    }

    // --------------------------------------------------

    /**
     * @param $buttons
     *
     * @return mixed
     */
    public function mce_buttons($buttons): mixed
    {
        array_push($buttons, 'separator', 'unlink');
        array_push($buttons, 'separator', 'alignjustify');
        array_push($buttons, 'separator', 'table');
        array_push($buttons, 'separator', 'charmap');
        array_push($buttons, 'separator', 'backcolor');
        array_push($buttons, 'separator', 'superscript');
        array_push($buttons, 'separator', 'subscript');
        array_push($buttons, 'separator', 'codesample');
        array_push($buttons, 'separator', 'toc');

        return $buttons;
    }

    // --------------------------------------------------

    /**
     * @param $plugins
     *
     * @return mixed
     */
    public function mce_external_plugins($plugins): mixed
    {
        $plugins['table']      = ADDONS_URL . 'src/Editor/tinymce/table/plugin.min.js';
        $plugins['codesample'] = ADDONS_URL . 'src/Editor/tinymce/codesample/plugin.min.js';
        $plugins['toc']        = ADDONS_URL . 'src/Editor/tinymce/toc/plugin.min.js';
        $plugins['wordcount']  = ADDONS_URL . 'src/Editor/tinymce/wordcount/plugin.min.js';
        $plugins['charcount']  = ADDONS_URL . 'src/Editor/tinymce/charcount/plugin.min.js';

        return $plugins;
    }
}
