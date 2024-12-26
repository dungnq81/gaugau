<?php
/**
 * CSS Output functions
 *
 * @author Gau
 */

\defined( 'ABSPATH' ) || die;

// --------------------------------------------------
// Custom css
// --------------------------------------------------

add_action( 'wp_enqueue_scripts', '__custom_css', 99 );

/**
 * @return void
 */
function __custom_css(): void {
//	$css = \Cores\CSS::get_instance();
//
//	//...
//
//	$css_output = $css->css_output();
//	if ( $css_output ) {
//		wp_add_inline_style( 'app-style', $css_output );
//	}

	//ob_start();

	//...

	//$inline_css = ob_get_clean();
	//$inline_css = \Cores\Helper::CSSMinify( $inline_css, true );
	//wp_add_inline_style( 'app-style', $inline_css );
}
