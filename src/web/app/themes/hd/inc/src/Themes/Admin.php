<?php

namespace Themes;

use Cores\Helper;
use Cores\Traits\Singleton;

\defined( 'ABSPATH' ) || die;

/**
 * Admin Class
 *
 * @author Gau
 */
final class Admin {
	use Singleton;

	// --------------------------------------------------

	private function init(): void {

		// editor-style.css
		add_editor_style( ASSETS_URL . "css/editor-style.css" );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );

		// admin.js, admin.css v.v
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 99999 );

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ], 11 );

		add_action( 'admin_footer', [ $this, 'add_custom_admin_script' ] );
	}

	// --------------------------------------------------

	/**
	 * @return void
	 */
	public function add_custom_admin_script(): void { ?>
        <script>
            jQuery( document ).ready( function ( $ ) {
                $( 'a' ).each( function () {
                    let link = $( this ).attr( 'href' );
                    if ( link && link.indexOf( 'action=trash' ) !== -1 ) {
                        $( this ).on( 'click', function ( e ) {
                            let confirmAction = confirm( "Are you sure you want to move this post to the trash?" );
                            if ( !confirmAction ) {
                                e.preventDefault();
                            }
                        } );
                    }
                } );
            } );
        </script>
		<?php
	}

	// --------------------------------------------------

	/**
	 * Gutenberg editor
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		wp_enqueue_style( 'editor-style', ASSETS_URL . "css/editor-style.css" );
	}

	// --------------------------------------------------

	/**
	 * @return void
	 */
	public function admin_enqueue_scripts(): void {
		$version = THEME_VERSION;
		if ( WP_DEBUG ) {
			$version = date( 'YmdHis', current_time( 'U', 0 ) );
		}

		wp_enqueue_style( 'admin-style', ASSETS_URL . 'css/admin.css', [], $version );

		wp_register_script( 'pace-js', ASSETS_URL . 'js/pace.min.js', [], $version, true );
		$pace_js_inline = 'paceOptions = {startOnPageLoad:!1}';
		wp_add_inline_script( 'pace-js', $pace_js_inline, 'before' );

		wp_enqueue_script( 'admin', ASSETS_URL . 'js/admin2.js', [ 'jquery-core', 'pace-js' ], $version, true );
		wp_script_add_data( 'admin', 'extra', [ 'module', 'defer' ] );
	}

	// --------------------------------------------------

	/**
	 * @return void
	 */
	public function admin_menu(): void {
		global $menu, $submenu;
		//dump($menu);
		//dump($submenu);

		// remove...
		remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );

		$admin_hide_menu             = Helper::filterSettingOptions( 'admin_hide_menu', [] );
		$admin_hide_submenu          = Helper::filterSettingOptions( 'admin_hide_submenu', [] );
		$admin_hide_menu_ignore_user = Helper::filterSettingOptions( 'admin_hide_menu_ignore_user', [] );

		$user_id = get_current_user_id();

		// menu
		if ( $admin_hide_menu && ! in_array( $user_id, $admin_hide_menu_ignore_user, false ) ) {
			foreach ( $admin_hide_menu as $menu_slug ) {
				if ( $menu_slug ) {
					$_a = remove_menu_page( $menu_slug );
				}
			}
		}

		// submenu
		if ( $admin_hide_submenu && ! in_array( $user_id, $admin_hide_menu_ignore_user, false ) ) {
			foreach ( $admin_hide_submenu as $menu_slug => $_submenu ) {
				foreach ( $_submenu as $_submenu_item ) {
					if ( $_submenu_item ) {
						$_b = remove_submenu_page( $menu_slug, $_submenu_item );
					}
				}
			}
		}

        // other settings
		$remove_menu_setting = Helper::getThemeMod( 'remove_menu_setting' );
		if ( $remove_menu_setting ) {
			foreach ( explode( "\n", $remove_menu_setting ) as $menu_slug ) {
				if ( $menu_slug ) {
					$c = remove_menu_page( $menu_slug );
				}
			}
		}
	}

	// --------------------------------------------------

	/**
	 * @return void
	 */
	public function admin_init(): void {
		// https://wordpress.stackexchange.com/questions/77532/how-to-add-the-category-id-to-admin-page
		foreach ( Helper::filterSettingOptions( 'term_row_actions', [] ) as $term ) {
			add_filter( "{$term}_row_actions", [ $this, '_term_row_actions' ], 11, 2 );
		}

		// customize row_actions
		foreach ( Helper::filterSettingOptions( 'post_row_actions', [] ) as $post_type ) {
			add_filter( "{$post_type}_row_actions", [ $this, '_post_type_row_actions' ], 11, 2 );
		}

		// customize post-page
		add_filter( 'manage_posts_columns', [ $this, '_manage_columns_header' ], 11, 1 );
		add_filter( 'manage_posts_custom_column', [ $this, '_manage_columns_content' ], 11, 2 );

		add_filter( 'manage_pages_columns', [ $this, '_manage_columns_header' ], 5, 1 );
		add_filter( 'manage_pages_custom_column', [ $this, '_manage_columns_content' ], 5, 2 );

		// exclude post columns
		foreach ( Helper::filterSettingOptions( 'post_type_exclude_thumb_columns', [] ) as $post ) {
			add_filter( "manage_{$post}_posts_columns", [ $this, '_manage_columns_exclude_header' ], 12, 1 );
		}

		// thumb terms
		foreach ( Helper::filterSettingOptions( 'term_thumb_columns', [] ) as $term ) {
			add_filter( "manage_edit-{$term}_columns", [ $this, '_manage_term_columns_header' ], 11, 1 );
			add_filter( "manage_{$term}_custom_column", [ $this, '_manage_term_columns_content' ], 11, 3 );
		}
	}

	// --------------------------------------------------

	/**
	 * @param $actions
	 * @param $_object
	 *
	 * @return array
	 */
	public function _term_row_actions( $actions, $_object ): array {
		return Helper::prepend( $actions, 'Id: ' . $_object->term_id, 'action_id' );
	}

	// --------------------------------------------------

	/**
	 * @param $actions
	 * @param $_object
	 *
	 * @return mixed
	 */
	public function _post_type_row_actions( $actions, $_object ): mixed {
		if ( ! in_array( $_object->post_type, [ 'product', 'site-review' ] ) ) {
			$actions = Helper::prepend( $actions, 'Id:' . $_object->ID, 'action_id' );
		}

		return $actions;
	}

	// --------------------------------------------------

	/**
	 * @param $columns
	 *
	 * @return array
	 */
	public function _manage_columns_header( $columns ): array {
		$in = [
			"post_thumb" => sprintf( '<span class="wc-image tips">%1$s</span>', __( "Thumb", TEXT_DOMAIN ) ),
		];

		return Helper::insertBefore( 'title', $columns, $in );
	}

	// --------------------------------------------------

	/**
	 * @param $column_name
	 * @param $post_id
	 */
	public function _manage_columns_content( $column_name, $post_id ): void {
		switch ( $column_name ) {
			case 'post_thumb':
				$post_type = get_post_type( $post_id );
				if ( ! in_array( $post_type, [ 'video', 'product' ] ) ) {
					if ( ! $thumbnail = get_the_post_thumbnail( $post_id, 'thumbnail' ) ) {
						$thumbnail = Helper::placeholderSrc();
					}
					echo $thumbnail;
				} else if ( 'video' === $post_type ) {
					if ( has_post_thumbnail( $post_id ) ) {
						echo get_the_post_thumbnail( $post_id, 'thumbnail' );
					} else if ( $url = Helper::getField( 'url', $post_id ) ) {
						$img_src = Helper::youtubeImage( esc_url( $url ), 3 );
						echo "<img loading=\"lazy\" alt=\"video\" src=\"" . $img_src . "\" />";
					}
				}

				break;

			default:
				break;
		}
	}

	// --------------------------------------------------

	/**
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function _manage_columns_exclude_header( $columns ): mixed {
		unset( $columns['post_thumb'] );

		return $columns;
	}

	// --------------------------------------------------

	/**
	 * @param $columns
	 *
	 * @return array|mixed
	 */
	public function _manage_term_columns_header( $columns ): mixed {
		if ( Helper::isAcfActive() ) {
			$thumb   = [
				"term_thumb" => sprintf( '<span class="wc-image tips">%1$s</span>', __( "Thumb", TEXT_DOMAIN ) ),
			];
			$columns = Helper::insertBefore( 'name', $columns, $thumb );
		}

		return $columns;
	}

	// --------------------------------------------------

	/**
	 * @param $out
	 * @param $column
	 * @param $term_id
	 *
	 * @return int|mixed|string|null
	 */
	public function _manage_term_columns_content( $out, $column, $term_id ): mixed {
		switch ( $column ) {
			case 'term_thumb':
				$term_thumb = Helper::acfTermThumb( $term_id, $column, "thumbnail", true );
				if ( ! $term_thumb ) {
					$term_thumb = Helper::placeholderSrc();
				}

				$out = $term_thumb;

				return $out;

			default:
				return $out;
		}
	}
}
