<?php

namespace Widgets;

use Cores\Abstract_Widget;
use Cores\Helper;

\defined('ABSPATH') || die;

class Repeater_Widget extends Abstract_Widget
{
    public function __construct()
    {
        $this->widget_description = __('List using ACF repeater.', TEXT_DOMAIN);
        $this->widget_name        = __('* Repeater', TEXT_DOMAIN);
        $this->settings           = [
            'title' => [
                'type'  => 'text',
                'std'   => __('Search', TEXT_DOMAIN),
                'label' => __('Title', TEXT_DOMAIN),
            ],
        ];

        parent::__construct();
    }

    /**
     * @param array $args
     * @param array $instance
     *
     * @throws \JsonException
     */
    public function widget($args, $instance): void
    {
        if ($this->get_cached_widget($args)) {
            return;
        }

        $title = $this->get_instance_title($instance);
        $ACF   = $this->acfFields('widget_' . $args['widget_id']);

        $css_class     = ! empty($ACF->css_class) ? ' ' . Helper::escAttr($ACF->css_class) : '';
        $heading_tag   = ! empty($ACF->title_tag) ? Helper::escAttr($ACF->title_tag) : 'span';
        $heading_class = ! empty($ACF->title_classes) ? Helper::escAttr($ACF->title_classes) : 'heading-title';

        ob_start();

        if ($title) {
            echo '<div class="rl-section">';
            $args['before_title'] = '<' . $heading_tag . ' class="' . $heading_class . '">';
            $args['after_title']  = '</' . $heading_tag . '>';

            echo $args['before_title'] . $title . $args['after_title'];
        }

        $repeater = $ACF->repeater ?? [];
        if (! empty($repeater)) :

            ?>
		<ul class="rl<?=$css_class?>">
            <?php
                foreach ($repeater as $re) :
                    $content      = '';
                    $re_icon_svg  = ! empty($re['re_icon_svg']) ? $re['re_icon_svg'] : '';
                    $re_image     = ! empty($re['re_image']) ? $re['re_image'] : '';
                    $re_title     = ! empty($re['re_title']) ? $re['re_title'] : '';
                    $re_url       = $re['re_url'] ?? '';
                    $re_css_class = ! empty($re['re_css_class']) ? ' class="' . $re['re_css_class'] . '"' : '';
                    ?>
			<li<?=$re_css_class?>>
                <?php

                        if ($re_icon_svg) {
                            $content .= $re_icon_svg;
                        }
                    if ($re_image) {
                        $content .= Helper::iconImage($re_image, 'thumbnail');
                    }
                    if ($re_title) {
                        $content .= '<span>' . $re_title . '</span>';
                    }

                    echo Helper::ACFLinkWrap($content, $re_url, '', $re_title);

                    ?>
            </li>
            <?php endforeach; ?>
		</ul>
	    <?php endif;

        if ($title) {
            echo '</div>';
        }

        echo $this->cache_widget($args, ob_get_clean());
    }
}
