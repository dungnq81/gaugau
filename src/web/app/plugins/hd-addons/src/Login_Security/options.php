<?php

use Addons\Login_Security\Login_Attempts;

\defined('ABSPATH') || die;

$login_security_options = get_option('login_security__options');

$custom_login_uri          = $login_security_options['custom_login_uri'] ?? '';

$login_ips_access          = $login_security_options['login_ips_access']          ?? '';
$disable_ips_access        = $login_security_options['disable_ips_access']        ?? '';
$two_factor_authentication = $login_security_options['two_factor_authentication'] ?? '';
$limit_login_attempts      = $login_security_options['limit_login_attempts']      ?? 0;
$illegal_users             = $login_security_options['illegal_users']             ?? '';

echo '<h2>' . __('Login Security Settings', ADDONS_TEXT_DOMAIN) . '</h2>';

$login_security_default = \filter_setting_options('login_security', false);
if ($login_security_default['enable_custom_login_options']) :

    ?>
<div class="section section-text" id="section_custom_login_uri">
	<label class="heading" for="custom_login_uri"><?php _e('Custom Login URL', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('Attackers frequently target <b>/wp-admin</b> or <b>/wp-login.php</b> as the default login URL for WordPress. Changing it can help prevent these attacks and provide a more memorable login URL.', ADDONS_TEXT_DOMAIN); ?></div>
	<div class="option">
		<div class="controls control-prefix" style="height: unset;">
            <div class="prefix">
                <span class="input-txt" title="<?= esc_attr(esc_url(network_home_url('/')))?>"><?=esc_url(network_home_url('/'))?></span>
            </div>
            <?php

                // Default URI
                if (! $custom_login_uri) {
                    $custom_login_uri = 'wp-login.php';

                    if (! empty($login_security_default['custom_login_uri'])) {
                        $custom_login_uri = $login_security_default['custom_login_uri'];
                    }
                }

    ?>
			<input value="<?php echo esc_attr($custom_login_uri); ?>" class="input" type="text" id="custom_login_uri" name="custom_login_uri" placeholder="<?=esc_attr($custom_login_uri)?>">
		</div>
	</div>
</div>
<?php endif; ?>

<div class="section section-select" id="section_login_ips_access">
	<label class="heading" for="login_ips_access"><?php _e('Allowlist IPs Login Access', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('By default, your WordPress login page is accessible from any IP address. You can use this feature to restrict login access to specific IPs or ranges of IPs to prevent brute-force attacks or malicious login attempts.<br><b>Ex:</b> 192.168.0.1, 192.168.0.1-100, 192.168.0.1/4', ADDONS_TEXT_DOMAIN); ?></div>
	<div class="option">
		<div class="controls">
			<div class="select_wrapper">
				<select multiple placeholder="Enter IP addresses" class="select select2-ips !w[100%]" name="login_ips_access" id="login_ips_access">
                    <?php
            if ($login_ips_access) :
                foreach ((array) $login_ips_access as $ip) :
                    ?>
                    <option selected value="<?=esc_attr($ip)?>"><?=$ip?></option>
                    <?php endforeach; endif; ?>
                </select>
			</div>
		</div>
	</div>
</div>

<div class="section section-select" id="section_disable_ips_access">
	<label class="heading" for="disable_ips_access"><?php _e('Blocked IPs Access', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('List of IP addresses or ranges of IPs blocked from accessing the login page.<br><b>Ex:</b> 192.168.0.1, 192.168.0.1-100, 192.168.0.1/4', ADDONS_TEXT_DOMAIN); ?></div>
	<div class="option">
		<div class="controls">
			<div class="select_wrapper">
				<select multiple placeholder="Enter IP addresses" class="select select2-ips !w[100%]" name="disable_ips_access" id="disable_ips_access">
                    <?php
                    if ($disable_ips_access) :
                        foreach ((array) $disable_ips_access as $ip) :
                            ?>
                    <option selected value="<?=esc_attr($ip)?>"><?=$ip?></option>
                    <?php endforeach; endif; ?>
                </select>
			</div>
		</div>
	</div>
</div>

<div class="section section-checkbox" id="section_illegal_users">
    <label class="heading" for="illegal_users"><?php _e('Disable Common Usernames', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('Using common usernames like <b>\'admin\'</b> is a security threat that often results in unauthorised access. By enabling this option we will disable the creation of common usernames and if you already have one or more users with a weak username, we\'ll ask you to provide new one(s).', ADDONS_TEXT_DOMAIN)?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="illegal_users" id="illegal_users" <?php echo checked($illegal_users, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Check to activate', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>

<div class="section section-select" id="section_limit_login_attempts">
    <label class="heading" for="limit_login_attempts"><?php _e('Limit Login Attempts', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('Limit the number of times a given user can attempt to log in to your wp-admin with incorrect credentials. Once the login attempt limit is reached, the IP from which the attempts have originated will be blocked first for 1 hour. If the attempts continue after the first hour, the limit will then be triggered for 24 hours and then for 7 days.', ADDONS_TEXT_DOMAIN)?></div>
    <div class="option">
        <div class="controls">
            <div class="select_wrapper">
                <select class="select" name="limit_login_attempts" id="limit_login_attempts">
                    <?php foreach (Login_Attempts::$login_attempts_data as $key => $value) : ?>
                    <option value="<?=$key?>"<?= selected($limit_login_attempts, $key, false) ?>><?=$value?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="section section-checkbox !hidden" id="section_two_factor_authentication">
    <label class="heading" for="two_factor_authentication"><?php _e('Two-factor Authentication for Admin & Editors Users', ADDONS_TEXT_DOMAIN); ?></label>
    <div class="desc"><?php _e('Two-factor authentication forces admin users to login only after providing a token, generated from the Google Authenticator application. When you enable this option, all admin & editor users will be asked to configure their two-factor authentication in the Authenticator app on their next login.', ADDONS_TEXT_DOMAIN)?></div>
    <div class="option">
        <div class="controls">
            <input disabled type="checkbox" class="checkbox" name="two_factor_authentication" id="two_factor_authentication" <?php echo checked($two_factor_authentication, 1); ?> value="1">
        </div>
        <div class="explain"><?php _e('Enable Two-factor authentication', ADDONS_TEXT_DOMAIN); ?></div>
    </div>
</div>
