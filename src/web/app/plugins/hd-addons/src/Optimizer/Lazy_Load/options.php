<?php

\defined('ABSPATH') || die;

$lazy_load        = $lazy_load        ?? 0;
$lazy_load_mobile = $lazy_load_mobile ?? 0;
$exclude_lazyload = $exclude_lazyload ?? [];
$exclude_lazyload = implode(PHP_EOL, $exclude_lazyload);

?>
<div class="section section-checkbox" id="section_lazyload">
	<label class="heading !block" for="lazy_load"><?php _e('Lazy-Load Media', ADDONS_TEXT_DOMAIN); ?></label>
	<div class="desc"><?php _e('Speed up your web application by deferring the loading of below-the-fold images, animated SVGs, videos, and iframes until they enter the viewport.', ADDONS_TEXT_DOMAIN) ?></div>
	<div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="lazy_load" id="lazy_load" <?php echo checked($lazy_load, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Check to activate', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>

<div class="section section-checkbox !hide" id="section_lazyload_mobile">
	<label class="heading inline-heading" for="lazy_load_mobile"><?php _e('Lazy-load on Mobile', ADDONS_TEXT_DOMAIN); ?></label>
	<div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="lazy_load_mobile" id="lazy_load_mobile" <?php echo checked($lazy_load_mobile, 1); ?> value="1" />
        </div>
        <div class="explain"><?php _e('Check to activate', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>

<div class="section section-textarea" id="section_exclude_lazyload">
	<label class="heading inline-heading" for="exclude_lazyload"><?php _e('Excluded images or iframes', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc"><?php _e('The keywords include <b>file-name</b>, <b>CSS classes</b> of images or <b>iframe</b> codes that will be excluded.', ADDONS_TEXT_DOMAIN); ?></div>
	<div class="option">
		<div class="controls">
			<textarea class="textarea" name="exclude_lazyload" id="exclude_lazyload" rows="4"><?php echo $exclude_lazyload; ?></textarea>
		</div>
	</div>
</div>
