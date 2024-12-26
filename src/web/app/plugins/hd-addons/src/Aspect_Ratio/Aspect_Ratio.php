<?php

namespace Addons\Aspect_Ratio;

use Addons\Base\CSS;
use Addons\Base\Singleton;

\defined( 'ABSPATH' ) || die;

final class Aspect_Ratio {
	use Singleton;

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	private function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'aspect_ratio_enqueue_scripts' ], 99 );
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function aspect_ratio_enqueue_scripts(): void {
		$classes = [];
		$styles  = '';

		$aspect_ratio_post_type_term = \filter_setting_options( 'aspect_ratio_post_type_term', [] );
		foreach ( $aspect_ratio_post_type_term as $ar_post_type ) {

			$ratio_obj   = $this->_get_aspect_ratio( $ar_post_type );
			$ratio_class = $ratio_obj->class ?: '';
			$ratio_style = $ratio_obj->style ?: '';

			if ( $ratio_style && ! in_array( $ratio_class, $classes, false ) ) {
				$classes[] = $ratio_class;
				$styles    .= $ratio_style;
			}
		}

		if ( $styles ) {
			wp_add_inline_style( 'app-style', $styles );
		}
	}

	// ------------------------------------------------------

	/**
	 * @param string $post_type
	 * @param string $option
	 * @param string $default
	 *
	 * @return object
	 */
	private function _get_aspect_ratio( string $post_type = 'post', string $option = '', string $default = 'ar[3-2]' ): object {
		$ratio = $this->_aspect_ratio_option( $post_type, $option );

		$ratio_x = $ratio[0] ?? '';
		$ratio_y = $ratio[1] ?? '';

		$ratio_style = '';
		if ( ! $ratio_x || ! $ratio_y ) {
			$ratio_class = $default;
		} else {
			$ratio_class         = 'ar[' . $ratio_x . '-' . $ratio_y . ']';
			$ar_aspect_ratio_default = \filter_setting_options( 'aspect_ratio_default', [] );

			if ( is_array( $ar_aspect_ratio_default ) && ! in_array( $ratio_x . '-' . $ratio_y, $ar_aspect_ratio_default, false ) ) {
				$css = CSS::get_instance();

				$css->set_selector( '.' . $ratio_class );
				$css->add_property( 'height', 0 );

				$pb = ( $ratio_y / $ratio_x ) * 100;
				$css->add_property( 'padding-bottom', $pb . '%' );
				//$css->add_property( 'aspect-ratio', $ratio_x . '/' . $ratio_y );

				$ratio_style = $css->css_output();
			}
		}

		return (object) [
			'class' => $ratio_class,
			'style' => $ratio_style,
		];
	}

	// -------------------------------------------------------------

	/**
	 * @param string $post_type
	 * @param string $option
	 *
	 * @return string|string[]
	 */
	private function _aspect_ratio_option( string $post_type = '', string $option = '' ): array|string {
		$post_type = $post_type ?: 'post';
		$option    = $option ?: 'aspect_ratio__options';

		$aspect_ratio_options = get_option( $option );
		$width                = $aspect_ratio_options[ 'ar-' . $post_type . '-width' ] ?? '';
		$height               = $aspect_ratio_options[ 'ar-' . $post_type . '-height' ] ?? '';

		return ( $width && $height ) ? [ $width, $height ] : '';
	}
}
