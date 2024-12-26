<?php
/**
 * Header hooks
 *
 * @author Gaudev
 */

use Cores\Helper;

\defined( 'ABSPATH' ) || die;

// -----------------------------------------------
// wp_head
// -----------------------------------------------

if ( ! function_exists( '__wp_head' ) ) {
	add_action( 'wp_head', '__wp_head', 1 );

	function __wp_head(): void {
		//$meta_viewport = '<meta name="viewport" content="user-scalable=yes, width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0" />';
		$meta_viewport = '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
		echo apply_filters( 'meta_viewport_filter', $meta_viewport );

		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s" />', esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}
}

// -----------------------------------------------

if ( ! function_exists( '__module_preload' ) ) {
	add_action( 'wp_head', '__module_preload', 10 );

	function __module_preload(): void {
		ob_start();

		?>
        <link rel="modulepreload" crossorigin href="<?php echo ASSETS_URL . 'js/_vendor.js'; ?>">
        <link rel="modulepreload" crossorigin href="<?php echo ASSETS_URL . 'js/back-to-top.js'; ?>">
        <link rel="modulepreload" crossorigin href="<?php echo ASSETS_URL . 'js/lazy-loader.js'; ?>">
        <link rel="modulepreload" crossorigin href="<?php echo ASSETS_URL . 'js/social-share.js'; ?>">
		<?php

		$content = ob_get_clean();
		echo apply_filters( 'module_preload_filter', $content );
	}
}

// -----------------------------------------------

if ( ! function_exists( '__critical_css' ) ) {
	add_action( 'wp_head', '__critical_css', 11 );

	function __critical_css(): void {
		if ( is_front_page() || is_home() ) {

			$critical_css = get_transient( '_transient_index_critical' );
			if ( false === $critical_css ) {
				$critical_css_file = THEME_PATH . 'assets/css/index_critical.min.css';

				if ( is_file( $critical_css_file ) ) {
					$critical_css = file_get_contents( $critical_css_file );
					set_transient( '_transient_index_critical', $critical_css, 2 * HOUR_IN_SECONDS );
				}
			}

			if ( $critical_css ) {
				echo '<style id="index-critical">' . $critical_css . '</style>';
			}
		}
	}
}

// -----------------------------------------------

if ( ! function_exists( '__external_custom' ) ) {
	add_action( 'wp_head', '__external_custom', 98 );

	function __external_custom(): void {
        // Manifest
		$manifest_url = Helper::home( 'manifest.json' );
		if ( file_exists( $manifest_url ) ) {
			echo '<link rel="manifest" href="' . esc_url( $manifest_url ) . '">';
		}

		// Theme color
		$theme_color = Helper::getThemeMod( 'theme_color_setting' );
		if ( $theme_color ) {
			echo '<meta name="theme-color" content="' . esc_attr( $theme_color ) . '" />';
		}

		// Fb app_id
		$fb_appid = Helper::getThemeMod( 'social_fb_setting' );
		if ( $fb_appid ) {
			echo '<meta property="fb:app_id" content="' . esc_attr( $fb_appid ) . '" />';
		}
	}
}

// -----------------------------------------------

if ( ! function_exists( '__external_fonts' ) ) {
	add_action( 'wp_head', '__external_fonts', 99 );

	function __external_fonts(): void {
		ob_start();

		//...

		$content = ob_get_clean();
		echo apply_filters( 'external_fonts_filter', $content );
	}
}

// -----------------------------------------------
// before_header_action
// -----------------------------------------------

if ( ! function_exists( '__skip_to_content_link' ) ) {
	add_action( 'before_header_action', '__skip_to_content_link', 2 );

	/**
	 * Add skip to a content link before the header.
	 *
	 * @return void
	 */
	function __skip_to_content_link(): void {
		printf(
			'<a class="screen-reader-text skip-link" href="#site-content" title="%1$s">%2$s</a>',
			esc_attr__( 'Skip to content', TEXT_DOMAIN ),
			esc_html__( 'Skip to content', TEXT_DOMAIN )
		);
	}
}

// -----------------------------------------------
// header_action
// -----------------------------------------------

if ( ! function_exists( '__construct_header' ) ) {
	add_action( 'header_action', '__construct_header', 10 );

	function __construct_header(): void {

		/**
		 * @see _masthead_home_seo_header - 10
		 * @see _masthead_container_open - 11
		 * @see _masthead_top_header - 12
		 * @see _masthead_header - 13
		 * @see _masthead_bottom_header - 14
		 * @see _masthead_custom - 98
		 * @see _masthead_container_close - 99
		 */
		do_action( 'masthead' );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_home_seo_header' ) ) {
	add_action( 'masthead', '_masthead_home_seo_header', 10 );

	function _masthead_home_seo_header(): void {
		$home_heading = Helper::getThemeMod( 'home_heading_setting' );
		$home_heading = ! empty( $home_heading ) ? esc_html( $home_heading ) : get_bloginfo( 'name' );

		echo apply_filters( 'home_seo_header_filter', '<h1 class="sr-only">' . $home_heading . '</h1>' );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_container_open' ) ) {
	add_action( 'masthead', '_masthead_container_open', 11 );

	function _masthead_container_open(): void {
		echo apply_filters( 'masthead_container_open_filter', null );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_container_close' ) ) {
	add_action( 'masthead', '_masthead_container_close', 99 );

	function _masthead_container_close(): void {
		echo apply_filters( 'masthead_container_close_filter', null );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_top_header' ) ) {
	add_action( 'masthead', '_masthead_top_header', 12 );

	function _masthead_top_header(): void {
		$top_header_cols      = (int) Helper::getThemeMod( 'top_header_setting' );
		$top_header_container = Helper::getThemeMod( 'top_header_container_setting' );

		if ( $top_header_cols > 0 ) :
			?>
            <div id="top-header" class="top-header">
				<?php
				echo \_toggle_container_open( $top_header_container );

				for ( $i = 1; $i <= $top_header_cols; $i ++ ) :
					if ( is_active_sidebar( 'top-header-' . $i . '-sidebar' ) ) :
						echo '<div class="cell cell-' . $i . '">';
						dynamic_sidebar( 'top-header-' . $i . '-sidebar' );
						echo '</div>';
					endif;
				endfor;

				echo \_toggle_container_close( $top_header_container );
				?>
            </div><!-- #top-header -->
		<?php
		endif;
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_header' ) ) {
	add_action( 'masthead', '_masthead_header', 13 );

	function _masthead_header(): void {
		$header_cols      = (int) Helper::getThemeMod( 'header_setting' );
		$header_container = Helper::getThemeMod( 'header_container_setting' );

		if ( $header_cols > 0 ) :
			?>
            <div id="inside-header" class="inside-header">
				<?php
				echo \_toggle_container_open( $header_container );

				for ( $i = 1; $i <= $header_cols; $i ++ ) :
					if ( is_active_sidebar( 'header-' . $i . '-sidebar' ) ) :
						echo '<div class="cell cell-' . $i . '">';
						dynamic_sidebar( 'header-' . $i . '-sidebar' );
						echo '</div>';
					endif;
				endfor;

				echo \_toggle_container_close( $header_container );
				?>
            </div><!-- #inside-header -->
		<?php
		endif;
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_bottom_header' ) ) {
	add_action( 'masthead', '_masthead_bottom_header', 14 );

	function _masthead_bottom_header(): void {
		$bottom_header_cols      = (int) Helper::getThemeMod( 'bottom_header_setting' );
		$bottom_header_container = Helper::getThemeMod( 'bottom_header_container_setting' );

		if ( $bottom_header_cols > 0 ) :
			?>
            <div id="bottom-header" class="bottom-header">
				<?php
				echo \_toggle_container_open( $bottom_header_container );

				for ( $i = 1; $i <= $bottom_header_cols; $i ++ ) :
					if ( is_active_sidebar( 'bottom-header-' . $i . '-sidebar' ) ) :
						echo '<div class="cell cell-' . $i . '">';
						dynamic_sidebar( 'bottom-header-' . $i . '-sidebar' );
						echo '</div>';
					endif;
				endfor;

				echo \_toggle_container_close( $bottom_header_container );
				?>
            </div><!-- #bottom-header -->
		<?php
		endif;
	}
}

// -----------------------------------------------

if ( ! function_exists( '_masthead_custom' ) ) {
	add_action( 'masthead', '_masthead_custom', 98 );

	function _masthead_custom(): void {
		//
	}
}

// -----------------------------------------------
