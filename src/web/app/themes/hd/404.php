<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package HD
 */

\defined( 'ABSPATH' ) || die;

// redirect to home
wp_redirect( \Cores\Helper::home() );
