<?php

defined( 'ABSPATH' ) || die;

$file_settings_options = get_option( 'file_setting__options' );
$file_settings         = \filter_setting_options( 'file_settings', [] );

$svgs = $file_settings_options['svgs'] ?? 'disable';

?>
<h2><?php _e( 'Files Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<?php

if ( ! empty( $file_settings ) ) :
	$upload_max_filesize = ini_get( 'upload_max_filesize' );
	$upload_max_filesize = (int) filter_var( $upload_max_filesize, FILTER_SANITIZE_NUMBER_INT );

	foreach ( $file_settings as $key => $setting ) :
		if ( empty( $setting['name'] ) || empty( $setting['value'] ) ) {
			continue;
		}

		$name  = $setting['name'];
		$value = $setting['value'];
		$value = $file_settings_options[ $key ]['value'] ?? $value;
?>
<div class="section section-text" id="section_file_setting">
    <label class="heading !block" for="<?=esc_attr( $key ) ?>"><?php echo $name ?></label>
    <div class="desc">
        The "Maximum Upload File Size" setting allows administrators to set a limit on the size of uploaded files, measured in MB.
        <cite>Maximum <?=$upload_max_filesize?> MB</cite>
    </div>
    <div class="option">
        <div class="controls">
            <input value="<?= esc_attr( $value ) ?>" class="input !w-200" type="number" min="0" step="1" max="<?=$upload_max_filesize?>" id="<?=esc_attr( $key ) ?>" name="<?= esc_attr( $key ) ?>">
        </div>
    </div>
</div>
<?php endforeach; endif;

include __DIR__ . '/SVG/options.php';
