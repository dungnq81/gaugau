<?php

namespace Widgets;

use Cores\Abstract_Widget;
use Cores\Helper;

\defined( 'ABSPATH' ) || die;

class Recent_Posts_Widget extends Abstract_Widget {
	public function __construct() {
		$this->widget_description = __( 'Your site&#8217;s most recent Posts.' );
		$this->widget_name        = __( '* Recent Posts', TEXT_DOMAIN );
		$this->settings           = [
			'title'          => [
				'type'  => 'text',
				'std'   => __( 'Recent posts', TEXT_DOMAIN ),
				'label' => __( 'Title', TEXT_DOMAIN ),
			],
			'number'         => [
				'type'  => 'number',
				'min'   => 0,
				'max'   => 99,
				'std'   => 5,
				'class' => 'tiny-text',
				'label' => __( 'Number of posts to show:', TEXT_DOMAIN ),
			],
			'show_cat'       => [
				'type'  => 'checkbox',
				'std'   => '',
				'class' => 'checkbox',
				'label' => __( 'Show categories', TEXT_DOMAIN ),
			],
			'show_thumbnail' => [
				'type'  => 'checkbox',
				'std'   => '',
				'class' => 'checkbox',
				'label' => __( 'Show thumbnail', TEXT_DOMAIN ),
			],
			'show_date'      => [
				'type'  => 'checkbox',
				'std'   => '',
				'class' => 'checkbox',
				'label' => __( 'Show post date', TEXT_DOMAIN ),
			],
			'show_desc'      => [
				'type'  => 'checkbox',
				'std'   => '',
				'class' => 'checkbox',
				'label' => __( 'Show post description', TEXT_DOMAIN ),
			],
			'limit_time'     => [
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Time limit', TEXT_DOMAIN ),
				'desc'  => sprintf( __( "A date/time string, restrict to only posts within a specific time period. %s", TEXT_DOMAIN ), "\n<a target='_blank' href=\"https://www.php.net/manual/en/function.strtotime.php\">php.net/manual/en/function.strtotime.php</a>" ),
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

		$title = $this->get_instance_title( $instance );

		$number         = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		$show_cat       = ! empty( $instance['show_cat'] );
		$show_thumbnail = ! empty( $instance['show_thumbnail'] );
		$show_date      = ! empty( $instance['show_date'] );
		$show_desc      = ! empty( $instance['show_desc'] );

		$limit_time = $instance['limit_time'] ? trim( $instance['limit_time'] ) : '';

		$query_args = [
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $number,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
		];

		if ( $limit_time ) {

			// constrain to just posts in $limit_time
			$recent = strtotime( $limit_time );
			if ( Helper::isInteger( $recent ) ) {
				$query_args['date_query'] = [
					'after' => [
						'year'  => date( 'Y', $recent ),
						'month' => date( 'n', $recent ),
						'day'   => date( 'j', $recent ),
					],
				];
			}
		}

        Helper::setPostsPerPage( $number );

		$r = new \WP_Query(
			apply_filters(
				'widget_recent_posts_args',
				$query_args,
				$instance
			)
		);

		$ACF       = $this->acfFields( 'widget_' . $args['widget_id'] );
		$css_class = ! empty( $ACF->css_class ) ? Helper::escAttr( $ACF->css_class ) : '';
		$uniqid    = esc_attr( uniqid( $this->widget_classname . '-', false ) );

		if ( ! $r->have_posts() ) {
			return;
		}

		ob_start();

		?>
        <div class="<?php echo $css_class; ?>">
			<?php if ( $title ) : ?>
            <span class="sidebar-title"><?php echo $title; ?></span>
			<?php endif;

			// The title may be filtered: Strip out HTML and make sure the aria-label is never empty.
			$aria_label = $title ? Helper::escAttr( $title ) : __( 'Recent Posts', TEXT_DOMAIN );

			?>
            <nav class="<?= $uniqid ?>" aria-label="<?php echo $aria_label; ?>">
                <ul>
					<?php
					$ratio_class = Helper::aspectRatioClass();
					foreach ( $r->posts as $recent_post ) :
						$post_title = get_the_title( $recent_post->ID );
						$post_title     = ( ! empty( $post_title ) ) ? $post_title : __( '(no title)', TEXT_DOMAIN );

                        $attr_post_title = Helper::escAttr( $post_title );
                        $post_thumbnail = get_the_post_thumbnail( $recent_post, 'medium', [ 'alt' => $attr_post_title ] );

						$aria_current = '';
						if ( get_queried_object_id() === $recent_post->ID ) {
							$aria_current = ' aria-current="page"';
						}
						?>
                        <li>
							<?php if ( $show_thumbnail && $post_thumbnail ) : ?>
                            <a class="block cover" href="<?php the_permalink( $recent_post->ID ); ?>" aria-label="<?php echo $attr_post_title; ?>">
                                <span class="res <?= $ratio_class ?>"><?php echo $post_thumbnail; ?></span>
                            </a>
							<?php endif; ?>
                            <div class="cover-content">
                                <a href="<?php the_permalink( $recent_post->ID ); ?>" title="<?php echo $attr_post_title; ?>"<?php echo $aria_current; ?>><?php echo $post_title; ?></a>
								<?php if ( $show_date || $show_cat ) : ?>
                                <div class="meta">

                                    <?php if ( $show_date ) : ?>
                                    <span class="post-date"><?php echo Helper::humanizeTime( $recent_post ); ?></span>
                                    <?php endif;

                                    if ( $show_cat ) { echo Helper::getPrimaryTerm( $recent_post ); }

                                    ?>
                                </div>
								<?php endif; ?>

								<?php if ( $show_desc ) { echo Helper::loopExcerpt( $recent_post ); } ?>

                            </div>
                        </li>
					<?php endforeach; ?>
                </ul>
            </nav>
        </div>
		<?php

		echo $this->cache_widget( $args, ob_get_clean() );
	}
}
