<?php

defined( 'ABSPATH' ) || die;

$emails_options = get_option( 'custom_email__options' );
$filter_custom_emails = filter_setting_options( 'custom_emails', [] );

?>
<h2><?php _e( 'Email to Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<?php

if ( ! empty( $filter_custom_emails ) ) :
	foreach ( $filter_custom_emails as $key => $ar ) :
		$emails_list = $emails_options[$key] ?? '';

		if ( ! $ar ) {
			break;
		}
?>
<div class="section section-text" id="section_emails">
	<label class="heading" for="<?=esc_attr( $key )?>"><?php _e( $ar, ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc">The email addresses are separated by commas "comma".</div>
	<div class="option">
		<div class="controls">
            <select aria-multiselectable="true" multiple placeholder="Email addresses" class="select select2-emails !w[100%]" name="<?=esc_attr( $key )?>_email" id="<?=esc_attr( $key )?>">
				<?php
				if ( $emails_list ) :
					foreach ( (array) $emails_list as $email ) :
                ?>
                <option selected value="<?=esc_attr( $email )?>"><?=$email?></option>
                <?php endforeach; endif; ?>
            </select>
		</div>
	</div>
</div>
<?php endforeach; else : echo '<p style="color:#d63638">' . __( 'Chưa được khởi tạo', ADDONS_TEXT_DOMAIN ) . '</p>'; endif;
