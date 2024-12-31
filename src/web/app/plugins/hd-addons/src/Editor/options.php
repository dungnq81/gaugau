<?php

\defined('ABSPATH') || die;

$block_editor_options = get_option('editor__options');

$use_widgets_block_editor_off           = $block_editor_options['use_widgets_block_editor_off']           ?? '';
$gutenberg_use_widgets_block_editor_off = $block_editor_options['gutenberg_use_widgets_block_editor_off'] ?? '';
$use_block_editor_for_post_type_off     = $block_editor_options['use_block_editor_for_post_type_off']     ?? '';
$block_style_off                        = $block_editor_options['block_style_off']                        ?? '';

?>
<h2><?php _e('Editor Settings', ADDONS_TEXT_DOMAIN); ?></h2>
<div class="section section-checkbox" id="section_use_widgets_block_editor_off">
    <label class="heading" for="use_widgets_block_editor_off"><?php _e('Disable widgets block editor', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="use_widgets_block_editor_off" id="use_widgets_block_editor_off" <?php echo checked($use_widgets_block_editor_off, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Disables the block editor from managing widgets.', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_gutenberg_use_widgets_block_editor_off">
    <label class="heading" for="gutenberg_use_widgets_block_editor_off"><?php _e('Disable Gutenberg widgets', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="gutenberg_use_widgets_block_editor_off" id="gutenberg_use_widgets_block_editor_off" <?php echo checked($gutenberg_use_widgets_block_editor_off, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Disables the block editor from managing widgets in the Gutenberg.', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_use_block_editor_for_post_type_off">
    <label class="heading" for="use_block_editor_for_post_type_off"><?php _e('Disable Block Editor', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="use_block_editor_for_post_type_off" id="use_block_editor_for_post_type_off" <?php echo checked($use_block_editor_for_post_type_off, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Use Classic Editor - Disable Block Editor.', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_block_style_off">
    <label class="heading" for="block_style_off"><?php _e('Remove block CSS', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="block_style_off" id="block_style_off" <?php echo checked($block_style_off, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Remove block library styles.', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>
