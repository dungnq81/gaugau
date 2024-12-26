<?php

defined( 'ABSPATH' ) || die;

$emails_options = get_option( 'custom_email_from__options' );
$filter_custom_emails = filter_setting_options( 'custom_emails', [] );

?>
<h2><?php _e( 'Email from Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<?php

if ( ! empty( $filter_custom_emails ) ) :
foreach ( $filter_custom_emails as $key => $ar ) :
	$email_item = $emails_options[ $key ] ?? '';

	if ( ! $ar ) {
		break;
	}

    $from_name = $email_item[0] ?? get_bloginfo( 'name' );
    $from_email = $email_item[1] ?? '';

?>
<div class="section section-text" id="section_emails_from">
    <span class="heading block"><?php _e( $ar, ADDONS_TEXT_DOMAIN ); ?></span>
    <div class="row">
        <div class="controls">
            <label for="<?=esc_attr( $key )?>_from_name"><?php _e( 'From Name', ADDONS_TEXT_DOMAIN ); ?></label>
            <input value="<?=$from_name?>" type="text" class="!w[100%]" name="<?=esc_attr( $key )?>_from_name" id="<?=esc_attr( $key )?>_from_name">
        </div>
    </div>
    <div class="row">
        <div class="controls">
            <label for="<?=esc_attr( $key )?>_from_email"><?php _e( 'From Email', ADDONS_TEXT_DOMAIN ); ?></label>
            <input value="<?=$from_email?>" type="email" class="!w[100%]" name="<?=esc_attr( $key )?>_from_email" id="<?=esc_attr( $key )?>_from_email" placeholder="e.g. &quot;abc@gmail.com&quot;" aria-describedby="<?=esc_attr( $key )?>_from_label">
        </div>
        <div class="desc" id="<?=esc_attr( $key )?>_from_label">The input only allows a single email address.</div>
    </div>
</div>
<?php endforeach; else : echo '<p style="color:#d63638">' . __( 'Chưa được khởi tạo', ADDONS_TEXT_DOMAIN ) . '</p>'; endif;
