<?php
/**
 * Footer hooks
 *
 * @author Gaudev
 */

use Cores\Helper;

\defined( 'ABSPATH' ) || die;

// -----------------------------------------------
// wp_footer
// -----------------------------------------------

if ( ! function_exists( '__wp_footer' ) ) {
	add_action( 'wp_footer', '__wp_footer', 98 );

	function __wp_footer(): void {
		if ( apply_filters( 'back_to_top_filter', true ) ) {
			echo apply_filters(
				'back_to_top_output_filter',
				sprintf(
					'<a title="%1$s" aria-label="%1$s" rel="nofollow" href="#" class="back-to-top toTop" data-scroll-speed="%2$s" data-scroll-start="%3$s">%4$s</a>',
					esc_attr__( 'Scroll back to top', TEXT_DOMAIN ),
					absint( apply_filters( 'back_to_top_scroll_speed_filter', 400 ) ),
					absint( apply_filters( 'back_to_top_scroll_start_filter', 300 ) ),
					'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24"><g fill="none"><path d="M8.47 4.22a.75.75 0 0 0 0 1.06L15.19 12l-6.72 6.72a.75.75 0 1 0 1.06 1.06l7.25-7.25a.75.75 0 0 0 0-1.06L9.53 4.22a.75.75 0 0 0-1.06 0z" fill="currentColor"></path></g></svg>'
				)
			);
		}
	}
}

// -----------------------------------------------
// footer_action
// -----------------------------------------------

if ( ! function_exists( '__construct_footer' ) ) {
	add_action( 'footer_action', '__construct_footer', 10 );

	function __construct_footer(): void {

		/**
		 * @see _footer_container_open - 10
		 * @see _footer_widgets - 11
		 * @see _footer_credit - 12
		 * @see _footer_custom - 98
		 * @see _footer_container_close - 99
		 */
		do_action( 'construct_footer' );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_footer_container_open' ) ) {
	add_action( 'construct_footer', '_footer_container_open', 10 );

	function _footer_container_open(): void {
		echo apply_filters( 'footer_container_open_filter', '' );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_footer_container_close' ) ) {
	add_action( 'construct_footer', '_footer_container_close', 99 );

	function _footer_container_close(): void {
		echo apply_filters( 'footer_container_close_filter', '' );
	}
}

// -----------------------------------------------

if ( ! function_exists( '_footer_widgets' ) ) {
	add_action( 'construct_footer', '_footer_widgets', 10 );

	/**
	 * Build our footer widgets
	 *
	 * @return void
	 */
	function _footer_widgets(): void {
		$rows    = (int) Helper::getThemeMod( 'footer_row_setting' );
		$regions = (int) Helper::getThemeMod( 'footer_col_setting' );

		// If no footer widgets exist, we don't need to continue
		if ( 1 > $rows || 1 > $regions ) {
			return;
		}

		?>
		<div id="footer-widgets" class="footer-widgets">
			<?php
			$footer_container = Helper::getThemeMod( 'footer_container_setting' );
			for ( $row = 1; $row <= $rows; $row ++ ) :

				// Defines the number of active columns in this footer row.
				for ( $region = $regions; 0 < $region; $region -- ) {
					if ( is_active_sidebar( 'footer-' . esc_attr( $region + $regions * ( $row - 1 ) ) . '-sidebar' ) ) {
						$columns = $region;
						break;
					}
				}

				if ( isset( $columns ) ) :
					echo '<div class="rows row-' . $row . '">';
					echo \_toggle_container_open( $footer_container );
					echo '<div class="flex-x">';

					for ( $column = 1; $column <= $columns; $column ++ ) :
						$footer_n = $column + $regions * ( $row - 1 );
						if ( is_active_sidebar( 'footer-' . esc_attr( $footer_n ) . '-sidebar' ) ) :

							echo sprintf( '<div class="cell cell-%1$s">', esc_attr( $column ) );
							dynamic_sidebar( 'footer-' . esc_attr( $footer_n ) . '-sidebar' );
							echo "</div>";

						endif;
					endfor;

					echo '</div>';
					echo \_toggle_container_close( $footer_container );
					echo '</div>';

				endif;
			endfor;
			?>
		</div><!-- #footer-widgets-->
		<?php
	}
}

// -----------------------------------------------

if ( ! function_exists( '_footer_credit' ) ) {
	add_action( 'construct_footer', '_footer_credit', 11 );

	function _footer_credit(): void {
		$footer_container = Helper::getThemeMod( 'footer_container_setting' );

		?>
        <div id="footer-credit" class="footer-credit">
			<?php
			echo \_toggle_container_open( $footer_container );

			// footer-copyright
			$copyright = sprintf(
				'<p class="copyright">&copy; %1$s %2$s. %3$s</p>',
				date( 'Y' ),
				get_bloginfo( 'name' ),
				apply_filters( 'copyright_text_filter', __( 'All rights reserved.', TEXT_DOMAIN ) )
			);

			echo apply_filters( 'copyright_filter', $copyright );

			// footer-credit sidebar
			if ( is_active_sidebar( 'footer-credit-sidebar' ) ) :
				echo '<div class="credit-sidebar">';
				dynamic_sidebar( 'footer-credit-sidebar' );
				echo '</div>';
			endif;

			echo \_toggle_container_close( $footer_container );
			?>
        </div><!-- #footer-credit -->
		<?php
	}
}

// -----------------------------------------------

if ( ! function_exists( '_footer_custom' ) ) {
	add_action( 'construct_footer', '_footer_custom', 98 );

	function _footer_custom(): void {
		//
	}
}

// -----------------------------------------------
