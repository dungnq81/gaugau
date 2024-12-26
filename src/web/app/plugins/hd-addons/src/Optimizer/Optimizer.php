<?php

namespace Addons\Optimizer;

use Addons\Base\Singleton;

use Addons\Optimizer\Attached_Media_Cleaner\Attached_Media_Cleaner;
use Addons\Optimizer\Font\Font;
use Addons\Optimizer\Heartbeat\Heartbeat;
use Addons\Optimizer\Lazy_Load\Lazy_Load;
use Addons\Optimizer\Minifier\Minify_Html;

\defined( 'ABSPATH' ) || die;

/**
 * Optimizer Class
 *
 * @author Gaudev
 */
final class Optimizer {
	use Singleton;

	public mixed $optimizer_options;

	// ------------------------------------------------------

	private function init(): void {

		( Attached_Media_Cleaner::get_instance() );
		( Heartbeat::get_instance() );
		( Lazy_Load::get_instance() );

		// optimizer
		$this->_optimizer();

		// Parser
		$this->optimizer_options = get_option( 'optimizer__options', [] );
		$this->_output_parser();
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	private function _optimizer(): void {

		// Filters the rel values that are added to links with the `target` attribute.
		add_filter( 'wp_targeted_link_rel', static function ( $rel, $link_target ) {
			$rel .= ' nofollow';

			return $rel;
		}, 999, 2 );

		// excerpt_more
		add_filter( 'excerpt_more', static function () {
			return ' ' . '&hellip;';
		} );

		// Remove logo admin bar
		add_action( 'wp_before_admin_bar_render', static function () {
			if ( is_admin_bar_showing() ) {
				global $wp_admin_bar;
				$wp_admin_bar->remove_menu( 'wp-logo' );
			}
		} );

		// Normalize upload filename
		add_filter( 'sanitize_file_name', static function ( $filename ) {
			return remove_accents( $filename );
		}, 10, 1 );

		// Remove archive title prefix
		add_filter( 'get_the_archive_title_prefix', static function ( $prefix ) {
			return __return_empty_string();
		} );
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	private function _output_parser(): void {
		if ( defined( 'WP_CLI' ) || is_admin() ) {
			return;
		}

		$minify_html   = $this->optimizer_options['minify_html'] ?? 0;
		$font_optimize = $this->optimizer_options['font_optimize'] ?? 0;
		$font_preload  = isset( $this->optimizer_options['font_preload'] ) ? implode( PHP_EOL, $this->optimizer_options['font_preload'] ) : '';
		$dns_prefetch  = isset( $this->optimizer_options['dns_prefetch'] ) ? implode( PHP_EOL, $this->optimizer_options['dns_prefetch'] ) : '';

		if ( ! empty( $minify_html ) ||
		     ! empty( $font_optimize ) ||
		     ! empty( $font_preload ) ||
		     ! empty( $dns_prefetch )
		) {
			add_action( 'wp_loaded', [ $this, 'start_bufffer' ] );
			add_action( 'shutdown', [ $this, 'end_buffer' ] );
		}
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function start_bufffer(): void {
		ob_start( [ $this, 'run' ] );
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function end_buffer(): void {
		if ( ob_get_length() ) {
			ob_end_flush();
		}
	}

	// ------------------------------------------------------

	/**
	 * @param string $html
	 *
	 * @return string
	 */
	public function run( string $html ): string {
		if ( ! preg_match( '/<\/html>/i', $html ) ) {
			return $html;
		}

		// Do not run optimization if amp is active, the page is an xml or feed.
		if ( \is_amp_enabled( $html ) ||
		     \is_xml( $html ) ||
		     is_feed()
		) {
			return $html;
		}

		return $this->_optimize_for_visitors( $html );
	}

	// ------------------------------------------------------

	/**
	 * @param $html
	 *
	 * @return string
	 */
	private function _optimize_for_visitors( $html ): string {

		$html = ( Font::get_instance() )->run( $html );
		$html = $this->_dns_prefetch( $html );

		$minify_html = $this->optimizer_options['minify_html'] ?? 0;
		if ( ! empty( $minify_html ) ) {
			$html = Minify_Html::minify( $html );
		}

		return $html;
	}

	// ------------------------------------------------------

	/**
	 * @param $html
	 *
	 * @return array|mixed|string|string[]
	 */
	private function _dns_prefetch( $html ): mixed {

		// Check if there are any urls inserted by the user.
		$urls = $this->optimizer_options['dns_prefetch'] ?? false;

		// Return if no url's are set by the user.
		if ( empty( $urls ) ) {
			return $html;
		}

		$new_html = '';
		foreach ( $urls as $url ) {

			// Replace the protocol with //.
			$url_without_protocol = preg_replace( '~(?:(?:https?:)?(?:\/\/)(?:www\.|(?!www)))?((?:.*?)\.(?:.*))~', '//$1', $url );
			$new_html             .= '<link rel="dns-prefetch" href="' . $url_without_protocol . '" />';
		}

		return str_replace( '</head>', $new_html . '</head>', $html );
	}

	// ------------------------------------------------------
}
