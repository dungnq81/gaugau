<?php

\defined('ABSPATH') || die;

$font_optimize     = $font_optimize     ?? 0;
$font_combined_css = $font_combined_css ?? 0;

$font_preload = $font_preload ?? [];
$font_preload = implode(PHP_EOL, $font_preload);

?>
<div class="section section-checkbox" id="section_font_optimize">
    <label class="heading" for="font_optimize"><?php _e('Web Fonts Optimization', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc"><?php _e('Optimize the loading of default <b>Google fonts</b>, as well as all <b>other fonts</b> used on your website.', ADDONS_TEXT_DOMAIN) ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="font_optimize" id="font_optimize" <?php echo checked($font_optimize, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Check to activate', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>

<div class="section section-checkbox !hide" id="section_font_combined_css">
    <label class="heading inline-heading" for="font_combined_css"><?php _e('Combined CSS', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc"><?php _e('Return combined tag instead of using inline CSS.', ADDONS_TEXT_DOMAIN) ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="font_combined_css" id="font_combined_css" <?php echo checked($font_combined_css, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Check to activate', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>

<div class="section section-textarea" id="section_font_preload">
	<label class="heading inline-heading" for="font_preload"><?php _e('Fonts Preloading', ADDONS_TEXT_DOMAIN) ?></label>
	<div class="desc"><?php _e('Preload the fonts you\'re using to improve rendering speed and enhance site performance. <b>Use the full URL to the font.</b>', ADDONS_TEXT_DOMAIN) ?></div>
	<div class="option">
		<div class="controls">
			<textarea class="textarea" name="font_preload" id="font_preload" rows="4"><?php echo $font_preload; ?></textarea>
		</div>
	</div>
</div>
