<?php

namespace Widgets;

use Cores\Abstract_Widget;
use Cores\Helper;

\defined( 'ABSPATH' ) || die;

class Search_Widget extends Abstract_Widget {
	public function __construct() {
		$this->widget_description = __( 'A search form for your site.', TEXT_DOMAIN );
		$this->widget_name        = __( '* Search', TEXT_DOMAIN );
		$this->settings           = [
			'title'             => [
				'type'  => 'text',
				'std'   => __( 'Search', TEXT_DOMAIN ),
				'label' => __( 'Button title', TEXT_DOMAIN ),
			],
			'placeholder_title' => [
				'type'  => 'text',
				'std'   => __( 'Search ...', TEXT_DOMAIN ),
				'label' => __( 'Placeholder title', TEXT_DOMAIN ),
			]
		];

		parent::__construct();
	}

	/**
	 * @param array $args
	 * @param array $instance
	 *
	 * @throws \JsonException
	 */
	public function widget( $args, $instance ): void {
		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		$ACF = $this->acfFields( 'widget_' . $args['widget_id'] );

		$css_class = ! empty( $ACF->css_class ) ? Helper::escAttr( $ACF->css_class ) : '';
		$title     = $this->get_instance_title( $instance );
		$placeholder_title   = ! empty( $instance['placeholder_title'] ) ? Helper::escAttr( $instance['placeholder_title'] ) : __( 'Search', TEXT_DOMAIN );

		$shortcode_content = Helper::doShortcode(
			'inline_search',
			apply_filters(
				'inline_search_widget_shortcode_args',
				[
					'title'       => $title,
					'placeholder' => $placeholder_title,
					'class'       => $css_class,
					'id'          => '',
				]
			)
		);

		echo $this->cache_widget( $args, $shortcode_content );
	}
}
