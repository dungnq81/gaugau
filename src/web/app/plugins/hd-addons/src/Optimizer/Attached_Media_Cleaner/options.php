<?php

\defined( 'ABSPATH' ) || die;

$attached_media_cleaner = $attached_media_cleaner ?? 0;

?>
<div class="section section-checkbox !hidden" id="section_attached_media_cleaner">
    <label class="heading" for="attached_media_cleaner"><?php _e( 'Attached Media Cleaner', ADDONS_TEXT_DOMAIN ) ?></label>
    <div class="desc"><?php _e( 'Remove all attached media from posts (if enabled). Clear old archives by deleting images associated with posts.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input disabled type="checkbox" class="checkbox" name="attached_media_cleaner" id="attached_media_cleaner" <?php echo checked( $attached_media_cleaner, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
