<?php

$woocommerce__options = get_option( 'woocommerce__options' );

$remove_legacy_coupon    = $woocommerce__options['remove_legacy_coupon'] ?? '';
$woocommerce_jsonld      = $woocommerce__options['woocommerce_jsonld'] ?? '';
$woocommerce_default_css = $woocommerce__options['woocommerce_default_css'] ?? '';

?>
<h2><?php _e( 'Woocommerce Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-checkbox" id="remove_legacy_coupon">
	<label class="heading" for="remove_legacy_coupon"><?php _e( 'Remove legacy coupon menu', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Fixed WooCommerce Admin notice for removing legacy coupon menu does not disappear.', ADDONS_TEXT_DOMAIN )?></div>
	<div class="option">
		<div class="controls">
			<input type="checkbox" class="checkbox" name="remove_legacy_coupon" id="remove_legacy_coupon" <?php echo checked( $remove_legacy_coupon, 1 ); ?> value="1">
		</div>
		<div class="explain"><?php _e( 'Remove legacy coupon', ADDONS_TEXT_DOMAIN ); ?></div>
	</div>
</div>

<div class="section section-checkbox" id="woocommerce_jsonld">
    <label class="heading" for="woocommerce_jsonld"><?php _e( 'WooCommerce 3 JSON/LD', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Remove the default WooCommerce 3 JSON/LD structured data format', ADDONS_TEXT_DOMAIN )?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="woocommerce_jsonld" id="woocommerce_jsonld" <?php echo checked( $woocommerce_jsonld, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Remove WooCommerce 3 JSON/LD', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>

<div class="section section-checkbox" id="woocommerce_default_css">
    <label class="heading" for="woocommerce_default_css"><?php _e( 'Remove WooCommerce CSS', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc">Remove all default CSS of WooCommerce.</div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="woocommerce_default_css" id="woocommerce_default_css" <?php echo checked( $woocommerce_default_css, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Remove all', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
