<?php

\defined('ABSPATH') || die;

$menu_options_page = apply_filters('addon_menu_options_page_filter', []);

?>
<div id="_content" class="tabs-content">
	<h2 class="hidden-text"></h2>

	<?php
    $i = 0;
foreach ($menu_options_page as $slug => $value) :
    $show_class = (0 === $i) ? ' show' : '';

    // WooCommerce
    if ('woocommerce' === (string) $slug && ! \check_plugin_active('woocommerce/woocommerce.php')) {
        continue;
    }

    // SMTP
    if ('smtp' === (string) $slug && ! \check_smtp_plugin_active()) {
        continue;
    }
    ?>
	<div id="<?=$slug?>_settings" class="group tabs-panel<?=$show_class?>">
		<?php

        $option_file = ADDONS_SRC_PATH . \capitalized_slug($slug) . DIRECTORY_SEPARATOR . 'options.php';
    $option_file     = apply_filters('addon_content_option_file', $option_file);

    file_exists($option_file) && include $option_file;

    ?>
	</div>
	<?php $i++; endforeach; ?>

    <div class="save-bar">
        <button type="submit" name="_submit_settings" class="button button-primary"><?php _e('Save Changes', ADDONS_TEXT_DOMAIN); ?></button>
    </div>
</div>
