<?php

namespace Themes;

use Cores\Helper;
use Cores\Traits\Singleton;

\defined('ABSPATH') || die;

/**
 * Shortcode Class
 *
 * @author Gau
 */
final class Shortcode
{
    use Singleton;

    // --------------------------------------------------

    /**
     * @return void
     */
    private function init(): void
    {
        $shortcodes = [
            'safe_mail'         => [$this, 'safe_mail'],
            'site_logo'         => [$this, 'site_logo'],
            'menu_logo'         => [$this, 'menu_logo'],
            'inline_search'     => [$this, 'inline_search'],
            'dropdown_search'   => [$this, 'dropdown_search'],
            'off_canvas_button' => [$this, 'off_canvas_button'],
            'horizontal_menu'   => [$this, 'horizontal_menu'],
            'vertical_menu'     => [$this, 'vertical_menu'],
            'social_menu'       => [$this, 'social_menu'],
            'posts'             => [$this, 'posts'],
        ];

        foreach ($shortcodes as $shortcode => $function) {
            add_shortcode(apply_filters("{$shortcode}_shortcode_tag", $shortcode), $function);
        }
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function safe_mail(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'title' => '',
                'email' => '',
                'class' => '',
                'id'    => Helper::escAttr(uniqid('mail-', false)),
            ],
            $atts,
            'safe_mail'
        );

        $attributes['title'] = $atts['title'] ? Helper::escAttr($atts['title']) : Helper::escAttr($atts['email']);
        $attributes['id']    = $atts['id'] ? Helper::escAttr($atts['id']) : Helper::escAttr(uniqid('mail-', false));

        if ($atts['class']) {
            $attributes['class'] = Helper::escAttr($atts['class']);
        }

        return Helper::safeMailTo($atts['email'], $atts['title'], $attributes);
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function social_menu(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'class' => 'social-menu',
                'id'    => Helper::escAttr(uniqid('menu-', false)),
            ],
            $atts,
            'social_menu'
        );

        $class = $atts['class'] ? ' ' . Helper::escAttr($atts['class']) : ' social-menu';

        ob_start();

        $social_options       = Helper::getOption('social__options');
        $social_follows_links = Helper::filterSettingOptions('social_follows_links', []);

        if ($social_options) {
            foreach ($social_options as $key => $social_option) {
                $data = [
                    'url'        => $social_option['url'] ?? '',
                    'name'       => $key,
                    'color'      => $social_follows_links[$key]['color']      ?? '',
                    'background' => $social_follows_links[$key]['background'] ?? '',
                    'icon'       => $social_follows_links[$key]['icon']       ?? '',
                ];

                $thumb = '';
                if (Helper::isUrl($data['icon']) || str_starts_with($data['icon'], 'data:')) :
                    $thumb = '<img src="' . $data['icon'] . '" alt="' . Helper::escAttr($data['name']) . '">';
                elseif (str_starts_with($data['icon'], '<svg')) :
                    $thumb = $data['icon'];
                elseif (is_string($data['icon'])) :
                    $thumb = '<i class="' . $data['icon'] . '"></i>';
                endif;

                if (! empty($data['url'])) :
                    ?>
					<li>
						<a href="<?= $data['url'] ?>" title="<?= Helper::escAttr($data['name']) ?>" target="_blank">
							<?= $thumb ?>
							<span class="sr-only"><?= $data['name'] ?></span>
						</a>
					</li>
		<?php
                endif;
            }
        }

        return '<ul class="menu' . $class . '">' . ob_get_clean() . '</ul>';
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return false|string|null
     */
    public function posts(array $atts = []): false|string|null
    {
        $default_atts = [
            'post_type'        => 'post',
            'term_ids'         => '',
            'taxonomy'         => 'category',
            'include_children' => false,
            'posts_per_page'   => 12,

            'limit_time'    => '',
            'wrapper_tag'   => '',
            'wrapper_class' => '',

            'show' => [
                'title_tag'      => 'p',
                'thumbnail'      => true,
                'thumbnail_size' => 'medium',
                'scale'          => false,
                'time'           => true,
                'term'           => true,
                'desc'           => true,
                'view_more'      => true,
            ],
        ];

        $atts = shortcode_atts(
            $default_atts,
            $atts,
            'posts'
        );

        $term_ids         = $atts['term_ids'] ?: [];
        $posts_per_page   = $atts['posts_per_page'] ? absint($atts['posts_per_page']) : Helper::getOption('posts_per_page');
        $include_children = Helper::toBool($atts['include_children']);
        $orderby          = ['date' => 'DESC'];
        $strtotime_str    = $atts['limit_time'] ? Helper::toString($atts['limit_time']) : false;

        $r = Helper::queryByTerms(
            $term_ids,
            $atts['post_type'],
            $atts['taxonomy'],
            $include_children,
            $posts_per_page,
            $orderby,
            [],
            $strtotime_str
        );

        if (! $r) {
            return null;
        }

        // ok !
        $wrapper_open  = $atts['wrapper'] ? '<' . $atts['wrapper'] . ' class="' . $atts['wrapper_class'] . '">' : '';
        $wrapper_close = $atts['wrapper'] ? '</' . $atts['wrapper'] . '>' : '';

        ob_start();
        $i = 0;

        // Load slides loop.
        while ($r->have_posts() && $i < $posts_per_page) :
            $r->the_post();

            echo $wrapper_open;
            get_template_part('template-parts/posts/loop', null, $atts['show']);
            echo $wrapper_close;

            ++$i;
        endwhile;
        wp_reset_postdata();

        return ob_get_clean();
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function vertical_menu(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'location'         => 'mobile-nav',
                'class'            => 'menu vertical vertical-menu mobile-menu',
                'id'               => Helper::escAttr(uniqid('menu-', false)),
                'depth'            => 4,
                'li_class'         => '',
                'li_depth_class'   => '',
                'link_class'       => '',
                'link_depth_class' => '',
            ],
            $atts,
            'vertical_menu'
        );

        $location = $atts['location'] ?: 'mobile-nav';
        $class    = $atts['class'] ? Helper::escAttr($atts['class']) : '';
        $depth    = $atts['depth'] ? absint($atts['depth']) : 1;
        $id       = $atts['id'] ?: Helper::escAttr(uniqid('menu-', false));

        $li_class         = ! empty($atts['li_class']) ? Helper::escAttr($atts['li_class']) : '';
        $li_depth_class   = ! empty($atts['li_depth_class']) ? Helper::escAttr($atts['li_depth_class']) : '';
        $link_class       = ! empty($atts['link_class']) ? Helper::escAttr($atts['link_class']) : '';
        $link_depth_class = ! empty($atts['link_depth_class']) ? Helper::escAttr($atts['link_depth_class']) : '';

        return Helper::verticalNav([
            'menu_id'          => $id,
            'menu_class'       => $class,
            'theme_location'   => $location,
            'depth'            => $depth,
            'li_class'         => $li_class,
            'li_depth_class'   => $li_depth_class,
            'link_class'       => $link_class,
            'link_depth_class' => $link_depth_class,
            'echo'             => false,
        ]);
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function horizontal_menu(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'location'         => 'main-nav',
                'class'            => 'dropdown menu horizontal horizontal-menu desktop-menu',
                'id'               => Helper::escAttr(uniqid('menu-', false)),
                'depth'            => 4,
                'li_class'         => '',
                'li_depth_class'   => '',
                'link_class'       => '',
                'link_depth_class' => '',
            ],
            $atts,
            'horizontal_menu'
        );

        $location = $atts['location'] ?: 'main-nav';
        $class    = $atts['class'] ? Helper::escAttr($atts['class']) : '';
        $depth    = $atts['depth'] ? absint($atts['depth']) : 1;
        $id       = $atts['id'] ?: Helper::escAttr(uniqid('menu-', false));

        $li_class         = ! empty($atts['li_class']) ? Helper::escAttr($atts['li_class']) : '';
        $li_depth_class   = ! empty($atts['li_depth_class']) ? Helper::escAttr($atts['li_depth_class']) : '';
        $link_class       = ! empty($atts['link_class']) ? Helper::escAttr($atts['link_class']) : '';
        $link_depth_class = ! empty($atts['link_depth_class']) ? Helper::escAttr($atts['link_depth_class']) : '';

        return Helper::horizontalNav([
            'menu_id'          => $id,
            'menu_class'       => $class,
            'theme_location'   => $location,
            'depth'            => $depth,
            'li_class'         => $li_class,
            'li_depth_class'   => $li_depth_class,
            'link_class'       => $link_class,
            'link_depth_class' => $link_depth_class,
            'echo'             => false,
        ]);
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function off_canvas_button(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'title'           => '',
                'hide_if_desktop' => 1,
                'class'           => '',
            ],
            $atts,
            'off_canvas_button'
        );

        $title = $atts['title'] ?: __('Menu', TEXT_DOMAIN);
        $class = ! empty($atts['hide_if_desktop']) ? ' !lg:hidden' : '';
        $class .= $atts['class'] ? ' ' . Helper::escAttr($atts['class']) . $class : '';

        ob_start();

        ?>
		<button class="menu-lines" type="button" data-open="offCanvasMenu" aria-label="button">
			<span class="menu-txt"><?= $title ?></span>
			<span class="line">
				<span class="line-1"></span>
				<span class="line-2"></span>
				<span class="line-3"></span>
			</span>
		</button>
	<?php
        return '<div class="off-canvas-content' . $class . '" data-off-canvas-content>' . ob_get_clean() . '</div>';
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function menu_logo(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'heading' => 'h1',
                'class'   => 'logo',
            ],
            $atts,
            'menu_logo'
        );

        return Helper::siteTitleOrLogo(false, $atts['heading'], $atts['class']);
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function site_logo(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'theme' => 'default',
                'class' => '',
            ],
            $atts,
            'site_logo'
        );

        return Helper::siteLogo($atts['theme'], $atts['class']);
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function inline_search(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'title'       => '',
                'placeholder' => '',
                'class'       => '',
                'id'          => Helper::escAttr(uniqid('search-', false)),
            ],
            $atts,
            'inline_search'
        );

        $title             = $atts['title'] ?: '';
        $title_for         = __('Search', TEXT_DOMAIN);
        $placeholder_title = $atts['placeholder'] ?: __('Search...', TEXT_DOMAIN);
        $id                = $atts['id'] ? Helper::escAttr($atts['id']) : Helper::escAttr(uniqid('search-', false));
        $class             = $atts['class'] ? ' ' . Helper::escAttr($atts['class']) : '';

        ob_start();

        ?>
		<form action="<?= Helper::home() ?>" class="frm-search" method="get" accept-charset="UTF-8" data-abide
			novalidate>
			<label for="<?= $id ?>" class="screen-reader-text"><?= $title_for ?></label>
			<input id="<?= $id ?>" required pattern="^(.*\S+.*)$" type="search" autocomplete="off" name="s"
				value="<?= get_search_query() ?>" placeholder="<?= $placeholder_title; ?>">
			<button type="submit" data-fa=""><span><?= $title ?></span></button>
			<?php
                if (Helper::isWoocommerceActive()) : ?>
				<input type="hidden" name="post_type" value="product">
			<?php
                endif; ?>
		</form>
	<?php
        return '<div class="inline-search' . $class . '">' . ob_get_clean() . '</div>';
    }

    // ------------------------------------------------------

    /**
     * @param array $atts
     *
     * @return string
     */
    public function dropdown_search(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'title' => '',
                'class' => '',
                'id'    => Helper::escAttr(uniqid('search-', false)),
            ],
            $atts,
            'dropdown_search'
        );

        $title             = $atts['title'] ?: __('Search', TEXT_DOMAIN);
        $title_for         = __('Search for', TEXT_DOMAIN);
        $placeholder_title = Helper::escAttr(__('Search ...', TEXT_DOMAIN));
        $close_title       = __('Close', TEXT_DOMAIN);
        $id                = $atts['id'] ? Helper::escAttr($atts['id']) : Helper::escAttr(uniqid('search-', false));
        $class             = $atts['class'] ? ' ' . Helper::escAttr($atts['class']) : '';

        ob_start();

        ?>
		<a class="trigger-s" title="<?= Helper::escAttr($title) ?>" href="javascript:;"
			data-toggle="dropdown-<?= $id ?>" data-fa=""><span><?= $title ?></span>
		</a>
		<div role="search" class="dropdown-pane" id="dropdown-<?= $id ?>" data-dropdown data-auto-focus="true">
			<form action="<?= Helper::home() ?>" class="frm-search" method="get" accept-charset="UTF-8" data-abide novalidate>
				<div class="frm-container">
					<label for="<?= $id ?>" class="screen-reader-text"><?= $title_for ?></label>
					<input id="<?= $id ?>" required pattern="^(.*\S+.*)$" type="search" name="s"
						value="<?= get_search_query() ?>" placeholder="<?= $placeholder_title ?>">
					<button class="btn-s" type="submit" data-fa=""><span><?= $title ?></span></button>
					<button class="trigger-s-close" type="button" data-fa=""><span><?= $close_title ?></span></button>
				</div>
				<?php
                    if (Helper::isWoocommerceActive()) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php
                    endif; ?>
			</form>
		</div>
<?php
        return '<div class="dropdown-search' . $class . '">' . ob_get_clean() . '</div>';
    }

    // ------------------------------------------------------
}
