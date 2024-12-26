<?php

namespace Themes;

use Cores\Helper;
use Cores\Traits\Singleton;

/**
 * Optimizer Class
 *
 * @author Gau
 */

\defined( 'ABSPATH' ) || die;

final class Optimizer {
	use Singleton;

	// ------------------------------------------------------

	private function init(): void {
		$this->_cleanup();
		$this->_optimizer();
	}

	// ------------------------------------------------------

	/**
	 * Launching operation cleanup
	 *
	 * @return void
	 */
	private function _cleanup(): void {
		// wp_head
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

		// All actions related to emojis
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		// Staticize emoji
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

		/**
		 * Remove wp-json header from WordPress
		 * Note that the REST API functionality will still be working as it used to;
		 * this only removes the header code that is being inserted.
		 */
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );

		// Remove id li navigation
		add_filter( 'nav_menu_item_id', '__return_null', 10, 3 );
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	private function _optimizer(): void {
		add_action( 'wp_head', [ $this, 'wp_head' ], 10 );

		add_filter( 'script_loader_tag', [ $this, 'script_loader_tag' ], 12, 3 );
		add_filter( 'style_loader_tag', [ $this, 'style_loader_tag' ], 12, 2 );

		// Adding Shortcode in WordPress Using Custom HTML Widget
		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'widget_text', 'shortcode_unautop' );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 11 );
		add_filter( 'posts_search', [ $this, 'post_search_by_title' ], 500, 2 );
		//add_filter( 'posts_where', [ $this, 'posts_title_filter' ], 499, 2 );

		// if not admin page
		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', [ $this, 'set_posts_per_page' ] );

			// only front-end
			if ( ! Helper::isLogin() ) {
				add_action( 'wp_print_footer_scripts', [ $this, 'print_footer_scripts' ], 999 );
				add_action( 'wp_footer', [ $this, 'deferred_scripts' ], 1000 );
			}
		}
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function wp_head(): void {
		// manifest.json
		//echo '<link rel="manifest" href="' . Helper::home( 'manifest.json' ) . '">';

		// Theme color
		$theme_color = Helper::getThemeMod( 'theme_color_setting' );
		if ( $theme_color ) {
			echo '<meta name="theme-color" content="' . $theme_color . '" />';
		}

		// Fb
		$fb_appid = Helper::getThemeMod( 'social_fb_setting' );
		if ( $fb_appid ) {
			echo '<meta property="fb:app_id" content="' . $fb_appid . '" />';
		}
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function enqueue(): void {
		wp_dequeue_style( 'classic-theme-styles' );
	}

	// ------------------------------------------------------

	/**
	 * Search only in post title or excerpt
	 *
	 * @param $search
	 * @param $wp_query
	 *
	 * @return mixed|string
	 */
	public function post_search_by_title( $search, $wp_query ): mixed {
		global $wpdb;

		if ( empty( $search ) ) {
			return $search; // skip processing – no search term in a query
		}

		$q = $wp_query->query_vars;
		$n = ! empty( $q['exact'] ) ? '' : '%';

		$search = $search_and = '';

		foreach ( (array) $q['search_terms'] as $term ) {
			$term = mb_strtolower( esc_sql( $wpdb->esc_like( $term ) ) );

			$like       = "LIKE CONCAT('{$n}', CONVERT('{$term}', BINARY), '{$n}')";
			$search     .= "{$search_and}(LOWER($wpdb->posts.post_title) {$like} OR LOWER($wpdb->posts.post_excerpt) {$like})";
			$search_and = " AND ";
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;
	}

	// ------------------------------------------------------

	/**
	 * Search only in post-title - wp_query
	 *
	 * @param $where
	 * @param $wp_query
	 *
	 * @return mixed|string
	 */
//	public function posts_title_filter( $where, $wp_query ) {
//		global $wpdb;
//
//		if ( $search_term = $wp_query->get( 'title_filter' ) ) {
//			$term = esc_sql( $wpdb->esc_like( $search_term ) );
//			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $term . '%\'';
//		}
//
//		return $where;
//	}

	// ------------------------------------------------------

	/**
	 * @param $query
	 */
	public function set_posts_per_page( $query ): void {
		if ( ! is_admin() ) {

			// get default value
			$posts_per_page_default = $posts_per_page = Helper::getOption( 'posts_per_page' );
			$posts_num_per_page_arr = Helper::filterSettingOptions( 'posts_num_per_page', [] );

			if ( ! empty( $posts_num_per_page_arr ) ) {
				$posts_per_page = min( $posts_num_per_page_arr );

				if ( isset( $_GET['pagenum'] ) ) {
					$pagenum = esc_sql( $_GET['pagenum'] );

					if ( in_array( $pagenum, $posts_num_per_page_arr, false ) ) {
						$posts_per_page = $pagenum;
					}

					if ( $pagenum > max( $posts_num_per_page_arr ) ) {
						$posts_per_page = max( $posts_num_per_page_arr );
					}
				}
			}

			if ( (int) $posts_per_page_default < (int) $posts_per_page ) {
				$query->set( 'posts_per_page', $posts_per_page );
			}
		}
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function print_footer_scripts(): void { ?>
        <script>document.documentElement.classList.remove( 'no-js' );</script>
		<?php

		if ( is_file( THEME_PATH . 'assets/js/skip-link-focus.js' ) ) {
			echo '<script>';
			include THEME_PATH . 'assets/js/skip-link-focus.js';
			echo '</script>';
		}
	}

	// ------------------------------------------------------

	/**
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string
	 */
	public function script_loader_tag( string $tag, string $handle, string $src ): string {
		$attributes = wp_scripts()->registered[ $handle ]->extra ?? [];

		// Add `type="module"` and `crossorigin` attributes if the script is marked as a module
		if ( ! empty( $attributes['module'] ) ) {
			$tag = preg_replace( '#(?=></script>)#', ' type="module"', $tag, 1 );
		}

		// Handle `async` and `defer` attributes
		foreach ( [ 'async', 'defer' ] as $attr ) {
			if ( ! empty( $attributes[ $attr ] ) && ! preg_match( "#\s$attr(=|>|\s)#", $tag ) ) {
				$tag = preg_replace( '#(?=></script>)#', " $attr", $tag, 1 );
			}
		}

		// Process combined attributes (e.g., `module defer`) from `extra`
		if ( ! empty( $attributes['extra'] ) ) {

			// Convert space-separated string to array if necessary
			$extra_attrs = is_array( $attributes['extra'] )
				? $attributes['extra']
				: explode( ' ', $attributes['extra'] );

			foreach ( $extra_attrs as $attr ) {
				if ( $attr === 'module' ) {
					if ( ! preg_match( '#\stype=(["\'])module\1#', $tag ) ) {
						$tag = preg_replace( '#(?=></script>)#', ' type="module"', $tag, 1 );
					}
				} elseif ( ! preg_match( "#\s$attr(=|>|\s)#", $tag ) ) {
					$tag = preg_replace( '#(?=></script>)#', " $attr", $tag, 1 );
				}
			}
		}

		// Fontawesome kit
		if ( ( 'fontawesome-kit' === $handle ) && ! preg_match( '#\scrossorigin([=>\s])#', $tag ) ) {
			$tag = preg_replace( '#(?=></script>)#', " crossorigin='anonymous'", $tag, 1 );
		}

		// Add script handles to the array
		$str_parsed = Helper::filterSettingOptions( 'defer_script', [] );

		return Helper::lazyScriptTag( $str_parsed, $tag, $handle );
	}

	// ------------------------------------------------------

	/**
	 * Add style handles to the array below
	 *
	 * @param string $html
	 * @param string $handle
	 *
	 * @return string
	 */
	public function style_loader_tag( string $html, string $handle ): string {
		$styles = Helper::filterSettingOptions( 'defer_style', [] );

		return Helper::lazyStyleTag( $styles, $html, $handle );
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function deferred_scripts(): void {
		//...
	}
}
