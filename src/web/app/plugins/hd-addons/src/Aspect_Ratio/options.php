<?php

defined('ABSPATH') || die;

$aspect_ratio = \filter_setting_options('aspect_ratio_post_type_term', []);

?>
<h2><?php _e('Aspect Ratio Settings', ADDONS_TEXT_DOMAIN); ?></h2>
<?php
foreach ($aspect_ratio as $ar) :
    $title = \mb_ucfirst($ar);

    if (! $title) {
        break;
    }

    $aspect_ratio_options = get_option('aspect_ratio__options');
    $width                = $aspect_ratio_options[ 'ar-' . $ar . '-width' ]  ?? '';
    $height               = $aspect_ratio_options[ 'ar-' . $ar . '-height' ] ?? '';

    ?>
	<div class="section section-text" id="section_aspect_ratio">
		<span class="heading"><?php _e($title, ADDONS_TEXT_DOMAIN); ?></span>
		<div class="desc"><?php echo $title?> images will be viewed at a custom aspect ratio.</div>
		<div class="option inline-option">
			<div class="controls">
				<div class="inline-group">
					<label>
						Width:
						<input class="input" name="<?=$ar?>-width" type="number" pattern="\d*" size="3" min="0" value="<?php echo esc_attr($width); ?>">
					</label>
					<span>x</span>
					<label>
						Height:
						<input class="input" name="<?=$ar?>-height" type="number" pattern="\d*" size="3" min="0" value="<?php echo esc_attr($height); ?>">
					</label>
				</div>
			</div>
		</div>
	</div>
<?php
endforeach;
