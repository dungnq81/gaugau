<?php

/**
 * The template for displaying the footer.
 * Contains the body & HTML closing tags.
 */

\defined('ABSPATH') || die;

/**
 * After Site Content
 */
do_action('after_site_content_action');

?>
</div><!-- #site-content -->
<?php

/**
 * Before Footer
 */
do_action('before_footer_action');

?>
<footer id="footer" class="site-footer" <?php echo \Cores\Helper::microdata('footer'); ?>>
    <?php

    /**
     * Footer
     *
     * @see __construct_footer - 10
     */
    do_action('footer_action');

?>
</footer><!-- #footer -->
<?php

/**
 * After Footer
 */
do_action('after_footer_action');

/**
 * Footer
 *
 * @see __wp_footer - 98
 */
wp_footer();

?>
</body>

</html>