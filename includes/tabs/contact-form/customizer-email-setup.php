<?php
/**
 * Customizer settings for the Contact Form Email Appearance.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Register Customizer settings & controls for the Contact Form Email appearance.
 *
 * @param WP_Customize_Manager $wp_customize Customize Manager instance.
 * @return void
 */
function sbwscf_contactform_customize_email_register( WP_Customize_Manager $wp_customize ) {

	// Section for email appearance.
	$wp_customize->add_section(
		'sbwscf_email_appearance',
		array(
			'title'    => esc_html__( 'SMiLE Basic Web Email Appearance', 'smile-basic-web' ),
			'priority' => 170,
		)
	);

	$email_settings = array(
		'sbwscf_email_logo_bg'              => array(
			'default' => '#FFFFFF',
			'label'   => esc_html__( 'Logo Background', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_logo_padding'         => array(
			'default' => '2rem',
			'label'   => esc_html__( 'Logo Padding', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_msg_bg'               => array(
			'default' => '#FFFFFF',
			'label'   => esc_html__( 'Message Background', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_msg_padding'          => array(
			'default' => '1rem',
			'label'   => esc_html__( 'Message Container Padding', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_copy_message_size'    => array(
			'default' => '1.4rem',
			'label'   => esc_html__( 'Copy Message Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_copy_message_color'   => array(
			'default' => '#333333',
			'label'   => esc_html__( 'Copy Message Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_usertext_size'        => array(
			'default' => 'medium',
			'label'   => esc_html__( 'User Text Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_usertext_color'       => array(
			'default' => '#333333',
			'label'   => esc_html__( 'User Text Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_usertext_line_height' => array(
			'default' => '2',
			'label'   => esc_html__( 'User Text Line Height', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_label_size'           => array(
			'default' => 'medium',
			'label'   => esc_html__( 'Label Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_label_color'          => array(
			'default' => '#000000',
			'label'   => esc_html__( 'Label Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_notice_bg'            => array(
			'default' => '#FFFFFF',
			'label'   => esc_html__( 'Notice Background', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_notice_size'          => array(
			'default' => 'x-small',
			'label'   => esc_html__( 'Notice Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_notice_color'         => array(
			'default' => '#999999',
			'label'   => esc_html__( 'Notice Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_email_notice_padding'       => array(
			'default' => '2rem',
			'label'   => esc_html__( 'Notice Padding', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_email_notice_line_height'   => array(
			'default' => '1.2',
			'label'   => esc_html__( 'Notice Line Height', 'smile-basic-web' ),
			'type'    => 'text',
		),
	);

	// Register settings & controls.
	foreach ( $email_settings as $setting_id => $args ) {

		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $args['default'],
				'sanitize_callback' => ( 'color' === $args['type'] )
				? 'sanitize_hex_color'
				: 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			$setting_id,
			array(
				'label'    => $args['label'],
				'section'  => 'sbwscf_email_appearance',
				'settings' => $setting_id,
				'type'     => $args['type'],
			)
		);
	}
}

/**
 * Enqueue live-preview script for email in the Customizer (vanilla JS).
 *
 * @return void
 */
function sbwscf_contactform_email_customizer_live_preview() {

	// 1. Get or recover the preview page ID.
	$page_id = (int) get_option( 'sbwscf_email_preview_page_id', 0 );
	if ( 0 === $page_id ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'page',
				'title'          => 'sbwscf-customizer-email-preview',
				'posts_per_page' => 1,
				'fields'         => 'ids', // Only retrieve the ID for performance.
			)
		);

		if ( $query->have_posts() ) {
			$page_id = absint( $query->posts[0] );
			update_option( 'sbwscf_email_preview_page_id', $page_id );
		} else {
			return; // No preview page available.
		}
	}

	// 2. Get the preview URL.
	$preview_url = get_permalink( $page_id );
	if ( false === $preview_url ) {
		return; // Invalid page / no URL.
	}

	// 3. Enqueue the customizer script from the contact-form module.
	$js_rel_path = 'includes/tabs/contact-form/assets/js/smile-email-preview-customizer.js';
	wp_enqueue_script(
		'sbwscf-email-preview-customizer',
		plugins_url( $js_rel_path, SMILE_BASIC_WEB_PLUGIN_FILE ),
		array( 'customize-controls' ),
		SMILE_BASIC_WEB_VERSION,
		true
	);

	// 4. Pass the permalink to the script.
	wp_localize_script(
		'sbwscf-email-preview-customizer',
		'sbwscfEmailPreviewData',
		array(
			'preview_url' => esc_url_raw( $preview_url ),
		)
	);
}

// Hook only the live-preview loader.
add_action( 'customize_controls_enqueue_scripts', 'sbwscf_contactform_email_customizer_live_preview' );
