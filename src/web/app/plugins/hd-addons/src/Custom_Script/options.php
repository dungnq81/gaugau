<?php

\defined( 'ABSPATH' ) || die;

$html_header      = get_custom_post_option_content( 'html_header', true );
$html_footer      = get_custom_post_option_content( 'html_footer', true );
$html_body_top    = get_custom_post_option_content( 'html_body_top', true );
$html_body_bottom = get_custom_post_option_content( 'html_body_bottom', true );

?>
<h2><?php _e( 'Custom Scripts Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-textarea" id="section_html_header">
    <label class="heading" for="html_header"><?php _e('Header scripts', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc">Add custom scripts inside HEAD tag. You need to have a SCRIPT tag around scripts.</div>
    <div class="option">
        <div class="controls">
            <textarea class="textarea codemirror_html" name="html_header" id="html_header" rows="4"><?php echo $html_header; ?></textarea>
        </div>
    </div>
</div>
<div class="section section-textarea" id="section_html_footer">
    <label class="heading" for="html_footer"><?php _e('Footer scripts', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc">Add custom scripts you might want to be loaded in the footer of your website. You need to have a SCRIPT tag around scripts.</div>
    <div class="option">
        <div class="controls">
            <textarea class="textarea codemirror_html" name="html_footer" id="html_footer" rows="4"><?php echo $html_footer; ?></textarea>
        </div>
    </div>
</div>
<div class="section section-textarea" id="section_html_body_top">
    <label class="heading" for="html_body_top"><?php _e('Body scripts - TOP', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc">Add custom scripts just after the BODY tag opened. You need to have a SCRIPT tag around scripts.</div>
    <div class="option">
        <div class="controls">
            <textarea class="textarea codemirror_html" name="html_body_top" id="html_body_top" rows="4"><?php echo $html_body_top; ?></textarea>
        </div>
    </div>
</div>
<div class="section section-textarea" id="section_html_body_bottom">
    <label class="heading" for="html_body_bottom"><?php _e('Body scripts - BOTTOM', ADDONS_TEXT_DOMAIN) ?></label>
    <div class="desc">Add custom scripts just before the BODY tag closed. You need to have a SCRIPT tag around scripts.</div>
    <div class="option">
        <div class="controls">
            <textarea class="textarea codemirror_html" name="html_body_bottom" id="html_body_bottom" rows="4"><?php echo $html_body_bottom; ?></textarea>
        </div>
    </div>
</div>
