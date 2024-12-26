<?php

\defined( 'ABSPATH' ) || die;

$heartbeat = $heartbeat ?? 0;

?>
<div class="section section-checkbox" id="section_heartbeat">
    <label class="heading" for="heartbeat"><?php _e( 'Heartbeat Optimization', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Disable \'WordPress Admin Pages\' and \'Site Frontend\', adjust to 120s for \'Posts and Pages\'.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="heartbeat" id="heartbeat" <?php echo checked( $heartbeat, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
