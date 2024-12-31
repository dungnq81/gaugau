<?php

namespace Addons\Woocommerce;

use Addons\Base\Singleton;

\defined('ABSPATH') || die;

final class WooCommerce
{
    use Singleton;

    public mixed $woocommerce_options = [];

    // --------------------------------------------------

    private function init(): void
    {
        $this->woocommerce_options = get_option('woocommerce__options', false);
        $woocommerce_jsonld        = $this->woocommerce_options['woocommerce_jsonld'] ?? '';
        if ($woocommerce_jsonld) {
            // Remove the default WooCommerce 3 JSON/LD structured data format
            add_action('init', [$this, 'remove_woocommerce_jsonld'], 10);
        }

        add_action('after_setup_theme', [$this, 'after_setup_theme'], 33);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 98);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets'], 41);
    }

    // ------------------------------------------------------

    /**
     * @return void
     */
    public function remove_woocommerce_jsonld(): void
    {
        remove_action('wp_footer', [\WC()->structured_data, 'output_structured_data'], 10);
        remove_action('woocommerce_email_order_details', [
            \WC()->structured_data,
            'output_email_structured_data',
        ], 30);
    }

    // ------------------------------------------------------

    /**
     * @return void
     */
    public function after_setup_theme(): void
    {
        // Remove woocommerce default styles
        $woocommerce_default_css = $this->woocommerce_options['woocommerce_default_css'] ?? '';
        if ($woocommerce_default_css) {
            add_filter('woocommerce_enqueue_styles', '__return_false');
        }
    }

    // ------------------------------------------------------

    /**
     * @return void
     */
    public function enqueue_scripts(): void
    {
        // remove 'woocommerce-inline-inline-css'
        $woocommerce_default_css = $this->woocommerce_options['woocommerce_default_css'] ?? '';
        if ($woocommerce_default_css) {
            wp_deregister_style('woocommerce-inline');
        }
    }

    // ------------------------------------------------------

    /**
     * @return void
     */
    public function enqueue_block_assets(): void
    {
        global $wp_styles;

        // Remove woocommerce blocks styles
        $block_editor_options = get_option('editor__options', false);

        if ($block_editor_options['block_style_off'] ?? '') {
            wp_deregister_style('wc-block-editor');

            wp_deregister_style('wc-blocks-style');
            wp_deregister_style('wc-blocks-packages-style');

            $styles_to_remove = [];
            foreach ($wp_styles->registered as $handle => $style) {
                if (str_starts_with($handle, 'wc-blocks-style-')) {
                    $styles_to_remove[] = $handle;
                }
            }

            foreach ($styles_to_remove as $handle) {
                wp_deregister_style($handle);
            }
        }
    }
}
