<?php

namespace Plugins;

use Cores\Helper;
use Cores\Traits\Singleton;

\defined( 'ABSPATH' ) || die;

/**
 * Polylang
 *
 * @author Gau
 */
final class PLL {
	use Singleton;

	// --------------------------------------------------

	/**
	 * @return void
	 */
	private function init(): void {

		// Woocommerce
		if ( Helper::isWoocommerceActive() ) {
			add_action( 'init', [ $this, 'register_and_translate_wc_attributes' ] );
			add_filter( 'woocommerce_attribute_label', [ $this, 'translate_product_attribute_label' ], 10, 3 );
			add_filter( 'woocommerce_variation_option_name', [ $this, 'translate_product_attribute_option_name' ] );
			add_action( 'wp_loaded', [ $this, 'language_switch_empty_cart' ] );

			add_filter( 'woocommerce_get_shop_url', [ $this, 'woocommerce_get_shop_url' ], 10, 1 );
		}

		// Home Url filter
		add_filter( 'home_url_filter', static function ( $url, $path ) {
			$path = trim( $path, '/' );
			if ( empty( $path ) ) {
				return pll_home_url();
			}

			return pll_home_url() . $path . '/';
		}, 10, 2 );
	}

	// --------------------------------------------------

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public function woocommerce_get_shop_url( $url ): string {
		if ( Helper::checkPluginActive( 'polylang-wc/polylang-wc.php' ) ) {
			return $url;
		}

		$shop_page_id = wc_get_page_id( 'shop' );
		$shop_slug    = get_post_field( 'post_name', $shop_page_id );

		return pll_home_url() . $shop_slug . '/';
	}

	// --------------------------------------------------

	/**
	 * @return array
	 */
	public function languages(): array {
		global $polylang;
		$languages = $polylang->model->get_languages_list();

		$arr = [];
		foreach ( $languages as $language ) {
			$name = $language->name;
			$slug = $language->slug;
			$w3c  = $language->w3c;

			$arr[ $slug ] = [
				'name' => $name,
				'slug' => $slug,
				'w3c'  => $w3c,
			];
		}

		return $arr;
	}

	// --------------------------------------------------

	/**
	 * @param $label
	 * @param $name
	 * @param $product
	 *
	 * @return string
	 */
	public function translate_product_attribute_label( $label, $name, $product ): string {

		// Get the translated string from Polylang
		return \pll__( $label );
	}

	// --------------------------------------------------

	/**
	 * @param $term_name
	 *
	 * @return string
	 */
	public function translate_product_attribute_option_name( $term_name ): string {

		// Get the translated string from Polylang
		return \pll__( $term_name );
	}

	// --------------------------------------------------

	/**
	 * @return void
	 */
	public function register_and_translate_wc_attributes(): void {
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {

			// Register attribute label with Polylang
			pll_register_string( $attribute->attribute_name, $attribute->attribute_label, TEXT_DOMAIN );

			// Get all terms (options) of the attribute
			$terms = get_terms( [
				'taxonomy' => 'pa_' . $attribute->attribute_name,
				'hide_empty' => false
			] );

			foreach ( $terms as $term ) {

				// Register each attribute option with Polylang
				\pll_register_string( $term->name, $term->name, TEXT_DOMAIN );
			}
		}
	}
}
