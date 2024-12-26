<?php

namespace Themes;

use Cores\Traits\Singleton;

\defined( 'ABSPATH' ) || die;

/**
 * Customizer Class
 *
 * @author Gau
 */
final class Customizer {
	use Singleton;

	// --------------------------------------------------

	/**
	 * @return void
	 */
	private function init(): void {

		// Theme Customizer settings and controls.
		add_action( 'customize_register', [ $this, 'customize_register' ], 30 );
	}

	// --------------------------------------------------

	/**
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @return void
	 */
	private function _logo_and_title( \WP_Customize_Manager $wp_customize ): void {

		// Logo mobile
		$wp_customize->add_setting( 'alt_logo', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_image',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'alt_logo',
				[
					'label'    => __( 'Alternative Logo', TEXT_DOMAIN ),
					'section'  => 'title_tagline',
					'settings' => 'alt_logo',
					'priority' => 8,
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'logo_title_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'logo_title_control',
			[
				'label'    => __( 'Title of logo', TEXT_DOMAIN ),
				'section'  => 'title_tagline',
				'settings' => 'logo_title_setting',
				'type'     => 'text',
				'priority' => 9,
			]
		);

		// Add control
		$wp_customize->add_setting( 'home_heading_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'home_heading_control',
			[
				'label'    => __( 'H1 on the homepage', TEXT_DOMAIN ),
				'section'  => 'title_tagline',
				'settings' => 'home_heading_setting',
				'type'     => 'text',
				'priority' => 9,
			]
		);
	}

	// --------------------------------------------------

	/**
	 * Register customizer options.
	 *
	 * @param \WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customize_register( \WP_Customize_Manager $wp_customize ): void {
		$this->_logo_and_title( $wp_customize );

		// -------------------------------------------------------------

		// Create custom panels
		$wp_customize->add_panel(
			'addon_menu_panel',
			[
				'priority'       => 140,
				'theme_supports' => '',
				'title'          => __( 'Addons', TEXT_DOMAIN ),
				'description'    => __( 'Controls the add-on menu', TEXT_DOMAIN ),
			]
		);

		// -------------------------------------------------------------
		// Login page
		// -------------------------------------------------------------

		$wp_customize->add_section(
			'login_page_section',
			[
				'title'    => __( 'Login page', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 999,
			]
		);

		$wp_customize->add_setting( 'login_page_bgcolor_setting', [
			'sanitize_callback' => 'sanitize_hex_color',
			'capability'        => 'edit_theme_options',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'login_page_bgcolor_control',
				[
					'label'    => __( 'Background color', TEXT_DOMAIN ),
					'section'  => 'login_page_section',
					'settings' => 'login_page_bgcolor_setting',
					'priority' => 8,
				]
			)
		);

		$wp_customize->add_setting( 'login_page_bgimage_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_image',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'login_page_bgimage_control',
				[
					'label'    => __( 'Background image', TEXT_DOMAIN ),
					'section'  => 'login_page_section',
					'settings' => 'login_page_bgimage_setting',
					'priority' => 9,
				]
			)
		);

		$wp_customize->add_setting( 'login_page_logo_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_image',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'login_page_logo_control',
				[
					'label'    => __( 'Logo', TEXT_DOMAIN ),
					'section'  => 'login_page_section',
					'settings' => 'login_page_logo_setting',
					'priority' => 10,
				]
			)
		);

		$wp_customize->add_setting( 'login_page_headertext_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'login_page_headertext_control',
			[
				'label'       => __( 'Header text', TEXT_DOMAIN ),
				'section'     => 'login_page_section',
				'settings'    => 'login_page_headertext_setting',
				'type'        => 'text',
				'priority'    => 11,
				'description' => __( 'Changing the alt text', TEXT_DOMAIN ),
			]
		);

		$wp_customize->add_setting( 'login_page_headerurl_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'login_page_headerurl_control',
			[
				'label'       => __( 'Header url', TEXT_DOMAIN ),
				'section'     => 'login_page_section',
				'settings'    => 'login_page_headerurl_setting',
				'type'        => 'url',
				'priority'    => 12,
				'description' => __( 'Changing the logo link', TEXT_DOMAIN ),
			]
		);

		// -------------------------------------------------------------
		// OffCanvas Menu
		// -------------------------------------------------------------

		$wp_customize->add_section(
			'offcanvas_menu_section',
			[
				'title'    => __( 'OffCanvas', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 1000,
			]
		);

		// Add offcanvas control
		$wp_customize->add_setting( 'offcanvas_menu_setting', [
			'default'           => 'default',
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'edit_theme_options',
		] );
		$wp_customize->add_control(
			'offcanvas_menu_control',
			[
				'label'    => __( 'offCanvas position', TEXT_DOMAIN ),
				'type'     => 'radio',
				'section'  => 'offcanvas_menu_section',
				'settings' => 'offcanvas_menu_setting',
				'choices'  => [
					'left'    => __( 'Left', TEXT_DOMAIN ),
					'right'   => __( 'Right', TEXT_DOMAIN ),
					'top'     => __( 'Top', TEXT_DOMAIN ),
					'bottom'  => __( 'Bottom', TEXT_DOMAIN ),
					'default' => __( 'Default (Right)', TEXT_DOMAIN ),
				],
			]
		);

		// -------------------------------------------------------------
		// Breadcrumbs
		// -------------------------------------------------------------

		$wp_customize->add_section(
			'breadcrumb_section',
			[
				'title'    => __( 'Breadcrumb', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 1007,
			]
		);

		// Min height control
		$wp_customize->add_setting( 'breadcrumb_min_height_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		] );
		$wp_customize->add_control(
			'breadcrumb_min_height_control',
			[
				'label'       => __( 'Breadcrumb min-height', TEXT_DOMAIN ),
				'section'     => 'breadcrumb_section',
				'settings'    => 'breadcrumb_min_height_setting',
				'type'        => 'number',
				'description' => __( 'Min-height of breadcrumb section', TEXT_DOMAIN ),
			]
		);

		// Max height control
		$wp_customize->add_setting( 'breadcrumb_max_height_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		] );
		$wp_customize->add_control(
			'breadcrumb_max_height_control',
			[
				'label'       => __( 'Breadcrumb max-height', TEXT_DOMAIN ),
				'section'     => 'breadcrumb_section',
				'settings'    => 'breadcrumb_max_height_setting',
				'type'        => 'number',
				'description' => __( 'Max-height of breadcrumb section', TEXT_DOMAIN ),
			]
		);

		// Add control
		$wp_customize->add_setting( 'breadcrumb_bg_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_customize_image_control_id',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'breadcrumb_bg_control',
				[
					'label'    => __( 'Background image', TEXT_DOMAIN ),
					'section'  => 'breadcrumb_section',
					'settings' => 'breadcrumb_bg_setting',
					'priority' => 9,
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'breadcrumb_bgcolor_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'breadcrumb_bgcolor_control',
				[
					'label'    => __( 'Background color', TEXT_DOMAIN ),
					'section'  => 'breadcrumb_section',
					'settings' => 'breadcrumb_bgcolor_setting',
					'priority' => 9,
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'breadcrumb_color_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'breadcrumb_color_control',
				[
					'label'    => __( 'Text color', TEXT_DOMAIN ),
					'section'  => 'breadcrumb_section',
					'settings' => 'breadcrumb_color_setting',
					'priority' => 9,
				]
			)
		);

		// -------------------------------------------------------------
		// Social
		// -------------------------------------------------------------

		$wp_customize->add_section(
			'social_section',
			[
				'title'    => __( 'Social', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 1008,
			]
		);

		// Add options for facebook appid
		$wp_customize->add_setting( 'social_fb_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'social_fb_control',
			[
				'label'       => __( 'Facebook AppID', TEXT_DOMAIN ),
				'section'     => 'social_section',
				'settings'    => 'social_fb_setting',
				'type'        => 'text',
				'description' => __( "You can do this at <a target='_blank' href='https://developers.facebook.com/apps/'>developers.facebook.com/apps</a>", TEXT_DOMAIN ),
			]
		);

		// Add options for facebook page_id
		$wp_customize->add_setting( 'social_fb_page_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'social_fb_page_control',
			[
				'label'       => __( 'Facebook PageID', TEXT_DOMAIN ),
				'section'     => 'social_section',
				'settings'    => 'social_fb_page_setting',
				'type'        => 'text',
				'description' => __( "How do I find my Facebook Page ID? <a target='_blank' href='https://www.facebook.com/help/1503421039731588'>facebook.com/help/1503421039731588</a>", TEXT_DOMAIN ),
			]
		);

		// Fb Chat
		$wp_customize->add_setting( 'social_fb_chat_setting', [
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_checkbox',
		] );
		$wp_customize->add_control(
			'social_fb_chat_control',
			[
				'type'     => 'checkbox',
				'settings' => 'social_fb_chat_setting',
				'section'  => 'social_section',
				'label'    => __( 'Facebook Live Chat', TEXT_DOMAIN ),
			]
		);

		// Zalo Appid
		$wp_customize->add_setting( 'social_zalo_setting', [
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'edit_theme_options',
		] );
		$wp_customize->add_control(
			'social_zalo_control',
			[
				'label'       => __( 'Zalo AppID', TEXT_DOMAIN ),
				'section'     => 'social_section',
				'settings'    => 'social_zalo_setting',
				'type'        => 'text',
				'description' => __( "You can do this at <a target='_blank' href='https://developers.zalo.me/docs/'>developers.zalo.me/docs/</a>", TEXT_DOMAIN ),
			]
		);

		// Zalo OAID
		$wp_customize->add_setting( 'social_zalo_oa_setting', [
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'edit_theme_options',
		] );
		$wp_customize->add_control(
			'social_zalo_oa_control',
			[
				'label'       => __( 'Zalo OAID', TEXT_DOMAIN ),
				'section'     => 'social_section',
				'settings'    => 'social_zalo_oa_setting',
				'type'        => 'text',
				'description' => __( "You can do this at <a target='_blank' href='https://oa.zalo.me/manage/oa?option=create'>oa.zalo.me/manage/oa?option=create</a>", TEXT_DOMAIN ),
			]
		);

		// Zalo Chat
		$wp_customize->add_setting( 'social_zalo_chat_setting', [
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_checkbox',
		] );
		$wp_customize->add_control(
			'social_zalo_chat_control',
			[
				'type'     => 'checkbox',
				'section'  => 'social_section',
				'settings' => 'social_zalo_chat_setting',
				'label'    => __( 'Zalo Live Chat', TEXT_DOMAIN ),
			]
		);

		// -------------------------------------------------------------
		// Header
		// -------------------------------------------------------------

		// Create a footer section
		$wp_customize->add_section(
			'header_section',
			[
				'title'    => __( 'Header', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 1008,
			]
		);

		// Add control
		$wp_customize->add_setting( 'header_bgcolor_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'header_bgcolor_control',
				[
					'label'    => __( 'Background color', TEXT_DOMAIN ),
					'section'  => 'header_section',
					'settings' => 'header_bgcolor_setting',
					'priority' => 9,
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'header_bg_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_image',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'header_bg_control',
				[
					'label'    => __( 'Background image', TEXT_DOMAIN ),
					'section'  => 'header_section',
					'settings' => 'header_bg_setting',
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'top_header_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		] );
		$wp_customize->add_control(
			'top_header_control',
			[
				'label'       => __( 'Top-Header columns', TEXT_DOMAIN ),
				'section'     => 'header_section',
				'settings'    => 'top_header_setting',
				'type'        => 'number',
				'description' => __( 'Top Header columns number', TEXT_DOMAIN ),
			]
		);

		// add control
		$wp_customize->add_setting( 'top_header_container_setting', [
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_checkbox',
		] );
		$wp_customize->add_control(
			'top_header_container_control',
			[
				'type'     => 'checkbox',
				'settings' => 'top_header_container_setting',
				'section'  => 'header_section',
				'label'    => __( 'Top Header Container', TEXT_DOMAIN ),
			]
		);

		// Add control
		$wp_customize->add_setting( 'header_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'header_control',
			[
				'label'       => __( 'Header columns', TEXT_DOMAIN ),
				'section'     => 'header_section',
				'settings'    => 'header_setting',
				'type'        => 'number',
				'description' => __( 'Header columns number', TEXT_DOMAIN ),
			]
		);

		// add control
		$wp_customize->add_setting( 'header_container_setting', [
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_checkbox',
		] );
		$wp_customize->add_control(
			'header_container_control',
			[
				'type'     => 'checkbox',
				'settings' => 'header_container_setting',
				'section'  => 'header_section',
				'label'    => __( 'Header Container', TEXT_DOMAIN ),
			]
		);

		// Add control
		$wp_customize->add_setting( 'bottom_header_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
		] );
		$wp_customize->add_control(
			'bottom_header_control',
			[
				'label'       => __( 'Bottom Header columns', TEXT_DOMAIN ),
				'section'     => 'header_section',
				'settings'    => 'bottom_header_setting',
				'type'        => 'number',
				'description' => __( 'Bottom Header columns number', TEXT_DOMAIN ),
			]
		);

		// add control
		$wp_customize->add_setting( 'bottom_header_container_setting', [
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_checkbox',
		] );
		$wp_customize->add_control(
			'bottom_header_container_control',
			[
				'type'     => 'checkbox',
				'settings' => 'bottom_header_container_setting',
				'section'  => 'header_section',
				'label'    => __( 'Bottom Header Container', TEXT_DOMAIN ),
			]
		);

		// -------------------------------------------------------------
		// Footer
		// -------------------------------------------------------------

		// Create a footer section
		$wp_customize->add_section(
			'footer_section',
			[
				'title'    => __( 'Footer', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 1008,
			]
		);

		// Add control
		$wp_customize->add_setting( 'footer_bgcolor_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'footer_bgcolor_control',
				[
					'label'    => __( 'Background color', TEXT_DOMAIN ),
					'section'  => 'footer_section',
					'settings' => 'footer_bgcolor_setting',
					'priority' => 9,
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'footer_color_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color'
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'footer_color_control',
				[
					'label'    => __( 'Text color', TEXT_DOMAIN ),
					'section'  => 'footer_section',
					'settings' => 'footer_color_setting',
					'priority' => 9,
				]
			)
		);

		// Add a control Footer background
		$wp_customize->add_setting( 'footer_bg_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_image',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'footer_bg_control',
				[
					'label'    => __( 'Background image', TEXT_DOMAIN ),
					'section'  => 'footer_section',
					'settings' => 'footer_bg_setting',
				]
			)
		);

		// Add control
		$wp_customize->add_setting( 'footer_row_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'footer_row_control',
			[
				'label'       => __( 'Footer rows', TEXT_DOMAIN ),
				'section'     => 'footer_section',
				'settings'    => 'footer_row_setting',
				'type'        => 'number',
				'description' => __( 'Footer rows number', TEXT_DOMAIN ),
			]
		);

		// Add control
		$wp_customize->add_setting( 'footer_col_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$wp_customize->add_control(
			'footer_col_control',
			[
				'label'       => __( 'Footer columns', TEXT_DOMAIN ),
				'section'     => 'footer_section',
				'settings'    => 'footer_col_setting',
				'type'        => 'number',
				'description' => __( 'Footer columns number', TEXT_DOMAIN ),
			]
		);

		// add control
		$wp_customize->add_setting( 'footer_container_setting', [
			'default'           => false,
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_checkbox',
		] );
		$wp_customize->add_control(
			'footer_container_control',
			[
				'type'     => 'checkbox',
				'settings' => 'footer_container_setting',
				'section'  => 'footer_section',
				'label'    => __( 'Footer Container', TEXT_DOMAIN ),
			]
		);

		// -------------------------------------------------------------
		// Others
		// -------------------------------------------------------------

		$wp_customize->add_section(
			'other_section',
			[
				'title'    => __( 'Other', TEXT_DOMAIN ),
				'panel'    => 'addon_menu_panel',
				'priority' => 1011,
			]
		);

		// Meta theme-color
		$wp_customize->add_setting( 'theme_color_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_hex_color',
		] );
		$wp_customize->add_control(
			new \WP_Customize_Color_Control( $wp_customize,
				'theme_color_control',
				[
					'label'    => __( 'Theme Color', TEXT_DOMAIN ),
					'section'  => 'other_section',
					'settings' => 'theme_color_setting',
				]
			)
		);

		// Hide menu
		$wp_customize->add_setting( 'remove_menu_setting', [
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_textarea_field',
		] );
		$wp_customize->add_control(
			'remove_menu_control',
			[
				'type'        => 'textarea',
				'section'     => 'other_section',
				'settings'    => 'remove_menu_setting',
				'label'       => __( 'Remove Menu', TEXT_DOMAIN ),
				'description' => __( 'The menu list will be hidden', TEXT_DOMAIN ),
			]
		);
	}
}
