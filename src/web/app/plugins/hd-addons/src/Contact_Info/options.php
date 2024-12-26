<?php

$contact_info_options = get_option( 'contact_info__options' );

$hotline = $contact_info_options['hotline'] ?? '';
$address = ! empty( $contact_info_options['address'] ) ? wp_unslash( $contact_info_options['address'] ) : '';
$phones  = $contact_info_options['phones'] ?? '';
$emails  = $contact_info_options['emails'] ?? '';

$contact_info_others = get_custom_post_option_content( 'html_others', false );

?>
<h2><?php _e( 'Contact Info Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-text" id="section_hotline">
	<label class="heading" for="contact_info_hotline"><?php _e( 'Hotline', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Hotline number, support easier interaction on the phone.', ADDONS_TEXT_DOMAIN ) ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $hotline ); ?>" class="input" type="text" id="contact_info_hotline" name="contact_info_hotline">
		</div>
	</div>
</div>
<div class="section section-textarea" id="section_address">
	<label class="heading" for="contact_info_address"><?php _e( 'Address', ADDONS_TEXT_DOMAIN ) ?></label>
	<div class="option">
		<div class="controls">
			<textarea class="textarea" name="contact_info_address" id="contact_info_address" rows="4"><?php echo $address; ?></textarea>
		</div>
	</div>
</div>
<div class="section section-text" id="section_phones">
	<label class="heading" for="contact_info_phones"><?php _e( 'Phones', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'The contact phone number, you may input multiple numbers, separated by <b>"comma"</b>.', ADDONS_TEXT_DOMAIN ) ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $phones ); ?>" class="input" type="text" id="contact_info_phones" name="contact_info_phones">
		</div>
	</div>
</div>
<div class="section section-text" id="section_emails">
	<label class="heading" for="contact_info_emails"><?php _e( 'Emails', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Email contact address, with the ability to enter multiple addresses, with each address separated by a <b>"comma"</b>.', ADDONS_TEXT_DOMAIN ) ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $emails ); ?>" class="input" type="text" id="contact_info_emails" name="contact_info_emails">
		</div>
	</div>
</div>
<div class="section section-text" id="section_others">
	<label class="heading" for="contact_info_others"><?php _e( 'Others', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Other supplementary materials', ADDONS_TEXT_DOMAIN ) ?></div>
	<div class="option">
		<div class="controls">
			<textarea class="textarea codemirror_html" name="contact_info_others" id="contact_info_others" rows="4"><?php echo $contact_info_others; ?></textarea>
		</div>
	</div>
</div>
