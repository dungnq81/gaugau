<?php

$contact_options = get_option( 'contact_button__options' );

$contact_title        = $contact_options['contact_title'] ?? '';
$contact_url          = $contact_options['contact_url'] ?? '';
$contact_window       = $contact_options['contact_window'] ?? '';
$contact_waiting_time = $contact_options['contact_waiting_time'] ?? '';
$contact_show_repeat  = $contact_options['contact_show_repeat'] ?? '';

$contact_popup_content = get_custom_post_option_content( 'html_contact', false );

?>
<h2><?php _e( 'Contact Button Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-text" id="section_contact_button_title">
    <label class="heading" for="contact_title"><?php _e( 'Contact Button Title', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr( $contact_title ); ?>" class="input" type="text" id="contact_title" name="contact_title">
        </div>
    </div>
</div>
<div class="section section-text" id="section_contact_url">
    <label class="heading" for="contact_url"><?php _e( 'Contact Button URL', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr( $contact_url ); ?>" class="input" type="url" id="contact_url" name="contact_url" placeholder="https://">
        </div>
    </div>
</div>
<div class="section section-checkbox" id="section_contact_button_window">
    <div class="option" style="padding-top: 15px;">
        <div class="controls">
            <label><input type="checkbox" class="checkbox" name="contact_window" id="contact_window" <?php echo checked( $contact_window, 1 ); ?> value="1"></label>
        </div>
        <div class="explain"><?php _e( 'Open link in a new window', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
<div class="section section-textarea" id="section_contact_popup_content">
    <label class="heading" for="contact_popup_content"><?php _e( 'Popup Content', ADDONS_TEXT_DOMAIN ) ?></label>
    <div class="desc"><?php _e( 'The content of the popup, usually the content of a shortcode or image', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <textarea class="textarea" name="contact_popup_content" id="contact_popup_content" rows="4"><?php echo $contact_popup_content; ?></textarea>
        </div>
    </div>
</div>
<div class="section section-text" id="section_contact_button_waiting_time">
    <label class="heading" for="contact_waiting_time"><?php _e( 'Popup display waiting time', ADDONS_TEXT_DOMAIN ) ?></label>
    <div class="desc"><?php _e( 'The waiting time to display the popup, calculated in seconds.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr( $contact_waiting_time ); ?>" class="input" type="number" min="0" id="contact_waiting_time" name="contact_waiting_time">
        </div>
    </div>
</div>
<div class="section section-text" id="section_contact_show_repeat">
    <label class="heading" for="contact_show_repeat"><?php _e( 'Repeat Displays', ADDONS_TEXT_DOMAIN ) ?></label>
    <div class="desc"><?php _e( 'Number of repeat displays of the popup.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input value="<?php echo esc_attr( $contact_show_repeat ); ?>" class="input" type="number" min="0" id="contact_show_repeat" name="contact_show_repeat">
        </div>
    </div>
</div>
