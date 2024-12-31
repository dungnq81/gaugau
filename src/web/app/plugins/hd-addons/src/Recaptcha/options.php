<?php

$recaptcha_options = get_option('recaptcha__options');

$recaptcha_v2_site_key   = $recaptcha_options['recaptcha_v2_site_key']   ?? '';
$recaptcha_v2_secret_key = $recaptcha_options['recaptcha_v2_secret_key'] ?? '';

$recaptcha_v3_site_key   = $recaptcha_options['recaptcha_v3_site_key']   ?? '';
$recaptcha_v3_secret_key = $recaptcha_options['recaptcha_v3_secret_key'] ?? '';
$recaptcha_v3_score      = $recaptcha_options['recaptcha_v3_score']      ?? '0.5';

$recaptcha_global        = $recaptcha_options['recaptcha_global']        ?? false;
$recaptcha_allowlist_ips = $recaptcha_options['recaptcha_allowlist_ips'] ?? [];

?>
<h2><?php _e('reCAPTCHA v2 Settings', ADDONS_TEXT_DOMAIN); ?></h2>
<div class="section section-text" id="section_recaptcha_v2_site_key">
    <label class="heading" for="recaptcha_v2_site_key"><?php _e('reCAPTCHA v2 Site Key', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr($recaptcha_v2_site_key); ?>" class="input" type="text" id="recaptcha_v2_site_key" name="recaptcha_v2_site_key">
        </div>
    </div>
</div>

<div class="section section-text" id="section_recaptcha_v2_secret_key">
    <label class="heading inline-heading" for="recaptcha_v2_secret_key"><?php _e('reCAPTCHA v2 Secret', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr($recaptcha_v2_secret_key); ?>" class="input" type="text" id="recaptcha_v2_secret_key" name="recaptcha_v2_secret_key">
        </div>
    </div>
</div>

<h2><?php _e('reCAPTCHA v3 Settings', ADDONS_TEXT_DOMAIN); ?></h2>
<div class="section section-text" id="section_recaptcha_v3_site_key">
    <label class="heading" for="recaptcha_v3_site_key"><?php _e('reCAPTCHA v3 Site Key', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc">Use this site key in the HTML code your site serves to users. <a target="_blank" href="https://developers.google.com/recaptcha/docs/v3">See client side integration</a></div>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr($recaptcha_v3_site_key); ?>" class="input" type="text" id="recaptcha_v3_site_key" name="recaptcha_v3_site_key">
        </div>
    </div>
</div>

<div class="section section-text" id="section_recaptcha_v3_secret_key">
    <label class="heading inline-heading" for="recaptcha_v3_secret_key"><?php _e('reCAPTCHA v3 Secret', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc">Use this secret key for communication between your site and reCAPTCHA. <a target="_blank" href="https://developers.google.com/recaptcha/docs/verify">See server side integration</a></div>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr($recaptcha_v3_secret_key); ?>" class="input" type="text" id="recaptcha_v3_secret_key" name="recaptcha_v3_secret_key">
        </div>
    </div>
</div>

<div class="section section-text" id="section_recaptcha_v3_score">
    <label class="heading inline-heading" for="recaptcha_v3_score"><?php _e('reCAPTCHA human/bot threshold score', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc">reCAPTCHA v3 returns a score (1.0 most likely a good interaction, 0.0 most likely a bot). By default, you can use a threshold of 0.5.</div>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr($recaptcha_v3_score); ?>" class="input !w-200" type="number" min="0.0" max="1.0" step="0.01" id="recaptcha_v3_score" name="recaptcha_v3_score">
        </div>
    </div>
</div>

<div class="section section-checkbox" id="section_recaptcha_global">
	<label class="heading !block" for="recaptcha_global"><?php _e('reCAPTCHA globally', ADDONS_TEXT_DOMAIN); ?></label>
	<div class="desc">Use 'www.recaptcha.net' in your code instead of 'www.google.com'.</div>
	<div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="recaptcha_global" id="recaptcha_global" <?php echo checked($recaptcha_global, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Check to activate', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>

<div class="section section-select" id="section_recaptcha_allowlist_ids">
	<label class="heading" for="recaptcha_allowlist_ips"><?php _e('Allowlist IP Addresses', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('The allowlist IPs can ignore reCAPTCHA.', ADDONS_TEXT_DOMAIN); ?></div>
	<div class="option">
		<div class="controls">
			<div class="select_wrapper">
				<select multiple class="select !w[100%] select2-ips" name="recaptcha_allowlist_ips[]" id="recaptcha_allowlist_ips">
					<?php
                    if ($recaptcha_allowlist_ips) :
                        foreach ((array) $recaptcha_allowlist_ips as $ip) :
                            ?>
                    <option selected value="<?=esc_attr($ip)?>"><?=$ip?></option>
                    <?php endforeach; endif; ?>
				</select>
			</div>
		</div>
	</div>
</div>
