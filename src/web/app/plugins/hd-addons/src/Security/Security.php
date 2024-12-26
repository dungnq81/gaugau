<?php

namespace Addons\Security;

use Addons\Base\Singleton;

\defined( 'ABSPATH' ) || die;

/**
 * Security Class
 *
 * @author HD
 */
final class Security {
	use Singleton;

	// ------------------------------------------------------

	/**
	 * @var array|false|mixed
	 */
	public mixed $security_options = [];

	// ------------------------------------------------------

	private function init(): void {
		$this->security_options = get_option( 'security__options' );

		$this->_hide_wp_version();
		$this->_disable_xmlrpc();
		$this->_disable_comments();
		$this->_disable_opml();
		$this->_remove_readme();
		$this->_disable_rssfeed();
		$this->_xss_protection();
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	private function _disable_comments(): void {

		if ( $this->security_options['comments_off'] ?? 0 ) {
			$comments = new Comment();

			add_action( 'admin_init', [ &$comments, 'disable_comments_post_types_support' ] );
			add_action( 'admin_init', [ &$comments, 'disable_comments_admin_menu_redirect' ] );

			add_filter( 'comments_open', '__return_false', 20, 2 );
			add_filter( 'pings_open', '__return_false', 20, 2 );

			add_action( 'admin_menu', [ &$comments, 'disable_comments_admin_menu' ] );
			add_action( 'wp_dashboard_setup', [ &$comments, 'disable_comments_dashboard' ] );
			add_action( 'wp_before_admin_bar_render', [ &$comments, 'remove_comments_admin_bar' ], 60 );
		}
	}

	// ------------------------------------------------------

	/**
	 * Add headers_service hooks.
	 *
	 * @return void
	 */
	private function _xss_protection(): void {

		if ( $this->security_options['advanced_xss_protection'] ?? 0 ) {
			$headers = new Headers();

			// Add security headers.
			add_action( 'wp_headers', [ &$headers, 'set_security_headers' ] );

			// Add security headers for rest.
			add_filter( 'rest_post_dispatch', [ &$headers, 'set_rest_security_headers' ] );
		}
	}

	// ------------------------------------------------------

	/**
	 * Remove the WordPress version meta-tag and parameter.
	 *
	 * @return void
	 */
	private function _hide_wp_version(): void {
		if ( $this->security_options['hide_wp_version'] ?? 0 ) {

			// Remove an admin wp version
			add_filter( 'update_footer', '__return_empty_string', 11 );

			// Remove WP version from RSS.
			add_filter( 'the_generator', '__return_empty_string' );

			add_filter( 'style_loader_src', [ $this, 'remove_version_scripts_styles' ], PHP_INT_MAX );
			add_filter( 'script_loader_src', [ $this, 'remove_version_scripts_styles' ], PHP_INT_MAX );
		}
	}

	// ------------------------------------------------------

	/**
	 * Remove a version from scripts and styles
	 *
	 * @param $src
	 *
	 * @return false|mixed|string
	 */
	public function remove_version_scripts_styles( $src ): mixed {
		if ( $src && str_contains( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	// ------------------------------------------------------

	/**
	 * Disable the WordPress feed.
	 *
	 * @return void
	 */
	private function _disable_rssfeed(): void {

		// If the option is already enabled.
		if ( $this->security_options['rss_feed_off'] ?? 0 ) {

			add_action( 'do_feed', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rdf', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rss', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rss2', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_atom', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_rss2_comments', [ $this, 'disable_feed' ], 1 );
			add_action( 'do_feed_atom_comments', [ $this, 'disable_feed' ], 1 );

			remove_action( 'wp_head', 'feed_links_extra', 3 ); // remove comments feed.
			remove_action( 'wp_head', 'feed_links', 2 );
		}
	}

	// ------------------------------------------------------

	/**
	 * Disables the WordPress feed.
	 *
	 * @return void
	 */
	public function disable_feed(): void {
		redirect( trailingslashit( esc_url( network_home_url() ) ) );
	}

	// ------------------------------------------------------

	/**
	 * Add readme hooks.
	 *
	 * @return void
	 */
	private function _remove_readme(): void {
		if ( $this->security_options['remove_readme'] ?? 0 ) {

			// Add action to delete the README on WP core update if the option is set.
			$readme = new Readme();
			add_action( '_core_updated_successfully', [ &$readme, 'delete_readme' ] );
		}
	}

	// ------------------------------------------------------

	/**
	 * Opml
	 *
	 * @return void
	 */
	private function _disable_opml(): void {
		if ( $this->security_options['wp_links_opml_off'] ?? 0 ) {
			add_action( 'init', static function () {

				// Check if the request matches wp-links-opml.php
				if ( str_contains( $_SERVER['REQUEST_URI'], 'wp-links-opml.php' ) ) {

					// If matched, send a 403 Forbidden response and exit
					status_header( 403 );
					exit;
				}
			} );
		}
	}

	// ------------------------------------------------------

	/**
	 * XML-RPC
	 *
	 * @return void
	 */
	private function _disable_xmlrpc(): void {
		if ( $this->security_options['xml_rpc_off'] ?? 0 ) {

			// Disable XML-RPC authentication and related functions
			if ( is_admin() ) {
				update_option( 'default_ping_status', 'closed' );
			}

			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'pre_update_option_enable_xmlrpc', '__return_false' );
			add_filter( 'pre_option_enable_xmlrpc', '__return_zero' );

			// Unset XML-RPC headers
			add_filter( 'wp_headers', static function ( $headers ) {
				if ( isset( $headers['X-Pingback'] ) ) {
					unset( $headers['X-Pingback'] );
				}

				return $headers;
			}, 10, 1 );

			// Unset XML-RPC methods for ping-backs
			add_filter( 'xmlrpc_methods', static function ( $methods ) {
				unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );

				return $methods;
			}, 10, 1 );
		}
	}
}
