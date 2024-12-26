<?php

namespace Addons\Third_Party;

use Addons\Base\Singleton;

\defined( 'ABSPATH' ) || die;

/**
 * RankMath Plugins
 *
 * @author Gaudev
 */
final class RankMath {
	use Singleton;

	// --------------------------------------------------

	private function init(): void {

		add_filter( 'rank_math/frontend/breadcrumb/args', [ $this, 'breadcrumb_args' ] );
		add_filter( 'rank_math/frontend/show_keywords', '__return_true' );

		/**
		 * Filter if XML sitemap transient cache is enabled.
		 *
		 * @param boolean $unsigned Enable cache or not, defaults to true
		 */
		add_filter( 'rank_math/sitemap/enable_caching', '__return_false' );

		/**
		 * Filter to add plugins to the Rank Math SEO TOC list.
		 */

		/* Tocer */
		if ( check_plugin_active( 'tocer/tocer.php' ) ) {
			add_filter( 'rank_math/researches/toc_plugins', static function ( $toc_plugins ) {
				$toc_plugins['tocer/tocer.php'] = 'Tocer';

				return $toc_plugins;
			} );
		}

		/* Easy Table of Contents */
		if ( check_plugin_active( 'easy-table-of-contents/easy-table-of-contents.php' ) ) {
			add_filter( 'rank_math/researches/toc_plugins', static function ( $toc_plugins ) {
				$toc_plugins['easy-table-of-contents/easy-table-of-contents.php'] = 'Easy Table of Contents';

				return $toc_plugins;
			} );
		}
	}

	// --------------------------------------------------

	/**
	 * @param $args
	 *
	 * @return string[]
	 */
	public function breadcrumb_args( $args ): array {
		return [
			'delimiter'   => '',
			'wrap_before' => '<ul id="breadcrumbs" class="breadcrumbs" aria-label="Breadcrumbs">',
			'wrap_after'  => '</ul>',
			'before'      => '<li><span property="itemListElement" typeof="ListItem">',
			'after'       => '</span></li>',
		];
	}
}
