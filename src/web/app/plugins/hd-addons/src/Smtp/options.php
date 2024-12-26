<?php

defined( 'ABSPATH' ) || die;

$smtp_options = get_option( 'smtp__options' );

$smtp_host                     = $smtp_options['smtp_host'] ?? '';
$smtp_auth                     = $smtp_options['smtp_auth'] ?? 'false';
$smtp_username                 = $smtp_options['smtp_username'] ?? '';
$smtp_password                 = $smtp_options['smtp_password'] ?? '';
$smtp_encryption               = $smtp_options['smtp_encryption'] ?? 'none';
$smtp_port                     = $smtp_options['smtp_port'] ?? '';
$smtp_from_email               = $smtp_options['smtp_from_email'] ?? '';
$smtp_from_name                = $smtp_options['smtp_from_name'] ?? '';
$smtp_force_from_address       = $smtp_options['smtp_force_from_address'] ?? '';
$smtp_disable_ssl_verification = $smtp_options['smtp_disable_ssl_verification'] ?? '';

?>
<h2><?php _e( 'SMTP Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-text" id="section_smtp_host">
	<label class="heading" for="smtp_host"><?php _e( 'SMTP Host', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'The SMTP server which will be used to send email. For example: <b>smtp.gmail.com</b>', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $smtp_host ); ?>" class="input" type="text" id="smtp_host" name="smtp_host">
		</div>
	</div>
</div>
<div class="section section-select" id="section_smtp_auth">
	<label class="heading" for="smtp_auth"><?php _e( 'SMTP Authentication', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'Whether to use SMTP Authentication when sending an email (recommended: <b>True</b>).', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<div class="select_wrapper">
				<select class="select" name="smtp_auth" id="smtp_auth">
					<option value="true"<?php echo selected( $smtp_auth, 'true', false ); ?>>True</option>
					<option value="false"<?php echo selected( $smtp_auth, 'false', false ); ?>>False</option>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="section section-text" id="section_smtp_username">
	<label class="heading" for="smtp_username"><?php _e( 'SMTP Username', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'Your SMTP Username. For example: <b>abc@gmail.com</b>', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $smtp_username ); ?>" class="input" type="text" id="smtp_username" name="smtp_username">
		</div>
	</div>
</div>
<div class="section section-text" id="section_smtp_password">
	<label class="heading" for="smtp_password"><?php _e( 'SMTP Password', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'Your SMTP Password (The saved password is not shown for security reasons. If you do not want to update the saved password, you can leave this field empty when updating other options).', ADDONS_TEXT_DOMAIN );?></div>
	<div class="option">
		<div class="controls">
			<input value="" class="input" type="password" id="smtp_password" name="smtp_password">
		</div>
	</div>
</div>
<div class="section section-select" id="section_smtp_encryption">
	<label class="heading" for="smtp_encryption"><?php _e( 'Type of Encryption', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'The encryption which will be used when sending an email (recommended: <b>TLS</b>).', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<div class="select_wrapper">
				<select class="select" name="smtp_encryption" id="smtp_encryption">
					<option value="tls"<?php echo selected( $smtp_encryption, 'tls', false );?>>TLS</option>
					<option value="ssl"<?php echo selected( $smtp_encryption, 'ssl', false );?>>SSL</option>
					<option value="none"<?php echo selected( $smtp_encryption, 'none', false );?>>No Encryption</option>
				</select>
			</div>
		</div>
	</div>
</div>
<div class="section section-text" id="section_smtp_port">
	<label class="heading" for="smtp_port"><?php _e( 'SMTP Port', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'The port which will be used when sending an email <b>(587/465/25)</b>. If you choose <b>TLS</b>, it should be set to <b>587</b>. For <b>SSL</b>, use port <b>465</b> instead.', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $smtp_port ); ?>" class="input" type="text" id="smtp_port" name="smtp_port">
		</div>
	</div>
</div>
<div class="section section-text" id="section_smtp_from_email">
	<label class="heading" for="smtp_from_email"><?php _e( 'From Email Address', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'The email address which will be used as the <b>From Address</b> if it is not supplied to the mail function.', ADDONS_TEXT_DOMAIN );?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $smtp_from_email ); ?>" class="input" type="text" id="smtp_from_email" name="smtp_from_email">
		</div>
	</div>
</div>
<div class="section section-text" id="section_smtp_from_name">
	<label class="heading" for="smtp_from_name"><?php _e( 'From Name', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'The name which will be used as the <b>From Name</b> if it is not supplied to the mail function.', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<input value="<?php echo esc_attr( $smtp_from_name ); ?>" class="input" type="text" id="smtp_from_name" name="smtp_from_name">
		</div>
	</div>
</div>

<div class="section section-checkbox" id="section_smtp_force_from_address">
    <label class="heading" for="smtp_force_from_address"><?php _e( 'Force From Address', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="smtp_force_from_address" id="smtp_force_from_address" <?php echo checked( $smtp_force_from_address, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'The From address in the settings will be set for all outgoing email messages.', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>

<div class="section section-checkbox" id="section_smtp_disable_ssl_verification">
	<label class="heading" for="smtp_disable_ssl_verification"><?php _e( 'Disable SSL Certificate Verification', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="option">
		<div class="controls">
			<input type="checkbox" class="checkbox" name="smtp_disable_ssl_verification" id="smtp_disable_ssl_verification" <?php echo checked( $smtp_disable_ssl_verification, 1 ); ?> value="1">
		</div>
		<div class="explain"><?php _e( 'You should get your host to fix the SSL configurations instead of bypassing it.', ADDONS_TEXT_DOMAIN ); ?></div>
	</div>
</div>
