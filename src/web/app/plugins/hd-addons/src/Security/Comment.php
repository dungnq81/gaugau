<?php

namespace Addons\Security;

\defined('ABSPATH') || die;

final class Comment
{
    // --------------------------------------------------

    /**
     * @return void
     */
    public function disable_comments_post_types_support(): void
    {
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    public function disable_comments_admin_menu_redirect(): void
    {
        global $pagenow;
        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    public function disable_comments_admin_menu(): void
    {
        $removed_menu = remove_menu_page('edit-comments.php');
    }

    // --------------------------------------------------

    /**
     * @return void
     */
    public function disable_comments_dashboard(): void
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    // --------------------------------------------------

    /**
     * @param $wp_admin_bar
     *
     * @return void
     */
    public function remove_comments_admin_bar($wp_admin_bar): void
    {
        if (is_admin_bar_showing()) {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('comments');
        }
    }
}
