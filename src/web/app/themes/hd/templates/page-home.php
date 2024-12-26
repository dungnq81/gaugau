<?php
/**
 * The template for displaying `homepage`
 * Template Name: Home
 * Template Post Type: page
 */

\defined( 'ABSPATH' ) || die;

// header
get_header( 'home' );

if ( have_posts() ) {
	the_post();
}

if ( post_password_required() ) :
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
endif;

// homepage widget
if ( is_active_sidebar( 'home-sidebar' ) ) :
	dynamic_sidebar( 'home-sidebar' );
endif;

// custom page
$ACF = \Cores\Helper::getFields( get_the_ID() );

$home_flexible_content = ! empty( $ACF['home_flexible_content'] ) ? (array) $ACF['home_flexible_content'] : false;
if ( $home_flexible_content ) {

	foreach ( $home_flexible_content as $section ) {
		$acf_fc_layout = $section['acf_fc_layout'] ?? '';

		if ( $acf_fc_layout ) {
			get_template_part( 'template-parts/home/' . $acf_fc_layout, null, $section );
		}
	}
}

// footer
get_footer( 'home' );
