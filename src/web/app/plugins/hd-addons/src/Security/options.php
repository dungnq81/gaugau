<?php

\defined( 'ABSPATH' ) || die;

$security_options = get_option( 'security__options' );

$hide_wp_version         = $security_options['hide_wp_version'] ?? '';
$xml_rpc_off             = $security_options['xml_rpc_off'] ?? '';
$comments_off            = $security_options['comments_off'] ?? '';
$wp_links_opml_off       = $security_options['wp_links_opml_off'] ?? '';
$remove_readme           = $security_options['remove_readme'] ?? '';
$rss_feed_off            = $security_options['rss_feed_off'] ?? '';
$lock_protect_system     = $security_options['lock_protect_system'] ?? '';
$advanced_xss_protection = $security_options['advanced_xss_protection'] ?? '';

?>
<h2><?php _e( 'Security Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-checkbox" id="section_hide_wp_version">
    <label class="heading" for="hide_wp_version"><?php _e( 'Hide WordPress Version', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Many attackers scan sites for vulnerable WordPress versions. By hiding the version from your site HTML, you avoid being marked by hackers for mass attacks.', ADDONS_TEXT_DOMAIN )?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="hide_wp_version" id="hide_wp_version" <?php echo checked( $hide_wp_version, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_xml_rpc_off">
	<label class="heading" for="xml_rpc_off"><?php _e( 'Disable XMLRPC', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'XMLRPC was designed as a protocol enabling WordPress to communicate with third-party systems but recently it has been used in a number of exploits. Unless you specifically need to use it, we recommend that XML-RPC is always disabled.', ADDONS_TEXT_DOMAIN )?></div>
	<div class="option">
		<div class="controls">
			<input type="checkbox" class="checkbox" name="xml_rpc_off" id="xml_rpc_off" <?php echo checked( $xml_rpc_off, 1 ); ?> value="1">
		</div>
		<div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
	</div>
</div>

<div class="section section-checkbox" id="section_comments_off">
    <label class="heading" for="comments_off"><?php _e( 'Disable Comments', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'WordPress comments enable user engagement by allowing feedback on posts, with easy management options for administrators to approve or disable comments as needed.', ADDONS_TEXT_DOMAIN )?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="comments_off" id="comments_off" <?php echo checked( $comments_off, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>

<div class="section section-checkbox" id="section_wp_links_opml_off">
	<label class="heading" for="wp_links_opml_off"><?php _e( 'Disable wp-links-opml', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'The wp-links-opml.php file allows users to export their links to an OPML file. This file is rarely used and can be disabled to enhance the security of your WordPress site. Disabling it helps prevent unauthorized access and potential information leakage.', ADDONS_TEXT_DOMAIN )?></div>
	<div class="option">
		<div class="controls">
			<input type="checkbox" class="checkbox" name="wp_links_opml_off" id="wp_links_opml_off" <?php echo checked( $wp_links_opml_off, 1 ); ?> value="1">
		</div>
		<div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
	</div>
</div>

<div class="section section-checkbox" id="section_remove_readme">
	<label class="heading" for="remove_readme"><?php _e( 'Delete the Default Readme.html', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'WordPress comes with a readme.html file containing information about your website. The readme.html is often used by hackers to compile lists of potentially vulnerable sites which can be hacked or attacked.', ADDONS_TEXT_DOMAIN ); ?></div>
	<div class="option">
		<div class="controls">
			<input type="checkbox" class="checkbox" name="remove_readme" id="remove_readme" <?php echo checked( $remove_readme, 1 ); ?> value="1">
		</div>
		<div class="explain"><?php _e( 'Remove the readme.html', ADDONS_TEXT_DOMAIN ); ?></div>
	</div>
</div>
<div class="section section-checkbox" id="section_rss_feed_off">
	<label class="heading" for="rss_feed_off"><?php _e( 'Disable RSS and ATOM Feeds', ADDONS_TEXT_DOMAIN ); ?></label>
	<div class="desc"><?php _e( 'RSS and ATOM feeds are often used to scrape your content and to perform a number of attacks against your site. Only use feeds if you have readers using your site via RSS readers.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
		<div class="controls">
			<input type="checkbox" class="checkbox" name="rss_feed_off" id="rss_feed_off" <?php echo checked( $rss_feed_off, 1 ); ?> value="1">
		</div>
		<div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
	</div>
</div>
<div class="section section-checkbox" id="section_lock_protect_system">
    <label class="heading" for="lock_protect_system"><?php _e( 'Lock and Protect System Folders', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'By enabling this option you are ensuring that no unauthorised or malicious scripts can be executed in your system folders. This is an often exploited back door you can close with a simple toggle.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="lock_protect_system" id="lock_protect_system" <?php echo checked( $lock_protect_system, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_advanced_xss_protection">
    <label class="heading" for="advanced_xss_protection"><?php _e( 'Advanced XSS Protection', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="advanced_xss_protection" id="advanced_xss_protection" <?php echo checked( $advanced_xss_protection, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Enabling this option will add extra headers to your site for protection against XSS attacks.', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
