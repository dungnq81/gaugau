<?php

\defined( 'ABSPATH' ) || die;

$optimizer_options = get_option( 'optimizer__options' );

$https_enforce          = $optimizer_options['https_enforce'] ?? 0;
$gzip                   = $optimizer_options['gzip'] ?? 0;
$browser_caching        = $optimizer_options['browser_caching'] ?? 0;
$heartbeat              = $optimizer_options['heartbeat'] ?? 0;
$attached_media_cleaner = $optimizer_options['attached_media_cleaner'] ?? 0;

$lazy_load        = $optimizer_options['lazy_load'] ?? 0;
$lazy_load_mobile = $optimizer_options['lazy_load'] ?? 0;
$exclude_lazyload = $optimizer_options['exclude_lazyload'] ?? [ 'no-lazy' ];

$font_preload      = $optimizer_options['font_preload'] ?? [];
$font_optimize     = $optimizer_options['font_optimize'] ?? 0;
$font_combined_css = $optimizer_options['font_combined_css'] ?? 0;

$dns_prefetch = $optimizer_options['dns_prefetch'] ?? [];
$minify_html  = $optimizer_options['minify_html'] ?? 0;

?>
<h2><?php _e( 'Optimizer Settings', ADDONS_TEXT_DOMAIN ); ?></h2>
<div class="section section-checkbox" id="section_https_enforce">
    <label class="heading" for="https_enforce"><?php _e( 'HTTPS', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Configures your site to work correctly via HTTPS and forces a secure connection to your site. In order to force HTTPS, we will automatically update your database replacing all insecure links. In addition to that, we will add a rule in your .htaccess file, forcing all requests to go through encrypted connection.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="https_enforce" id="https_enforce" <?php echo checked( $https_enforce, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_gzip">
    <label class="heading" for="gzip"><?php _e( 'GZIP Compression', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Enables compression of the content delivered to visitors\' browsers, improving the network loading times of your site.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="gzip" id="gzip" <?php echo checked( $gzip, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
<div class="section section-checkbox" id="section_browser_caching">
    <label class="heading" for="browser_caching"><?php _e( 'Browser Caching', ADDONS_TEXT_DOMAIN ); ?></label>
    <div class="desc"><?php _e( 'Adds rules to store the static content of your site in visitors\' browser cache for a longer period, improving site performance.', ADDONS_TEXT_DOMAIN ); ?></div>
    <div class="option">
        <div class="controls">
            <input type="checkbox" class="checkbox" name="browser_caching" id="browser_caching" <?php echo checked( $browser_caching, 1 ); ?> value="1">
        </div>
        <div class="explain"><?php _e( 'Check to activate', ADDONS_TEXT_DOMAIN ); ?></div>
    </div>
</div>
<?php

include __DIR__ . '/Heartbeat/options.php';
include __DIR__ . '/Minifier/options.php';
include __DIR__ . '/Font/options.php';
include __DIR__ . '/Lazy_Load/options.php';
include __DIR__ . '/Attached_Media_Cleaner/options.php';
