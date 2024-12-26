<?php

namespace Addons\Optimizer\Lazy_Load;

use Addons\Base\Singleton;
use Detection\Exception\MobileDetectException;

\defined( 'ABSPATH' ) || die;

final class Lazy_Load {

	use Singleton;

	public Lazy_Load_Iframes $lazyload_iframes;
	public Lazy_Load_Videos $lazyload_videos;
	public Lazy_Load_Images $lazyload_images;

	public array $lazyload_hooks = [
		'lazyload_iframes' => [
			'the_content',
			'widget_text',
		],
		'lazyload_videos'  => [
			'the_content',
			'widget_text',
		],
		'lazyload_images'  => [
			'the_content',
			'widget_text',
			'widget_block_content',
			'wp_get_attachment_image',
			'post_thumbnail_html',
			'get_avatar',
			'woocommerce_product_get_image',
			'woocommerce_single_product_image_thumbnail_html',
		],
	];

	/** ---------------------------------------- */

	/**
	 * @throws MobileDetectException
	 */
	private function init(): void {
		$optimizer_options = get_option( 'optimizer__options' );
		$lazy_load        = $optimizer_options[ 'lazy_load' ] ?? 0;
		$lazy_load_mobile = $optimizer_options[ 'lazy_load_mobile' ] ?? 0;

		if ( empty( $lazy_load ) ) {
			return;
		}

		// Bail if the current browser runs on a mobile device and the lazy-load on mobile is deactivated.
		if ( ! $lazy_load_mobile && \is_mobile() ) {
			return;
		}

		// Disable the native lazy-loading.
		//add_filter( 'wp_lazy_loading_enabled', '__return_false' );

		$this->lazyload_iframes = new Lazy_Load_Iframes();
		$this->lazyload_videos  = new Lazy_Load_Videos();
		$this->lazyload_images  = new Lazy_Load_Images();

		$this->_add_lazy_load_hooks();
	}

	/** ---------------------------------------- */

	/**
	 * @return void
	 */
	private function _add_lazy_load_hooks(): void {
		foreach ( $this->lazyload_hooks as $name => $attributes ) {

			// Loop through all attributes.
			foreach ( $attributes as $hook ) {

				// Add the hooks.
				add_filter( $hook, [ $this->{$name}, 'filter_html' ], 999999 );
			}
		}

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
	}

	/** ---------------------------------------- */

	/**
	 * @return void
	 */
	public function load_scripts(): void {
		wp_enqueue_script( 'lazy-js', ADDONS_URL . 'assets/js/lazyload.js', [], ADDONS_VERSION, true );
		wp_script_add_data( 'lazy-js', 'module', true );
	}
}
