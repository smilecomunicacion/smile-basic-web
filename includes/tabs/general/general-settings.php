<?php
/**
 * Settings for the General tab.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra secciones y campos de ajustes
 * para la pestaña General.
 *
 * @return void
 */
function sbwscf_general_register_settings(): void {

	/* ---------- Grupo de ajustes -------------------------------------- */
	register_setting(
		'sbwscf_general',
		'sbwscf_general_settings',
		array(
			'sanitize_callback' => 'sbwscf_general_sanitize',
		)
	);

	/* ---------- Sección ------------------------------------------------ */
	add_settings_section(
		'sbwscf_general_section',
		esc_html__( 'General Settings', 'smile-basic-web' ),
		'__return_false',
		'sbwscf_general'
	);

	/* ---------- Campo: habilitar SVG ---------------------------------- */
	add_settings_field(
		'sbwscf_enable_svg',
		esc_html__( 'Enable SVG / SVGZ uploads', 'smile-basic-web' ),
		'sbwscf_enable_svg_cb',
		'sbwscf_general',
		'sbwscf_general_section'
	);

	/* ---------- Campo: auto Alt-Text ---------------------------------- */
	add_settings_field(
		'sbwscf_enable_alt',
		esc_html__( 'Auto-populate image Alt-Text from EXIF metadata', 'smile-basic-web' ),
		'sbwscf_enable_alt_cb',
		'sbwscf_general',
		'sbwscf_general_section'
	);
}

/**
 * Sanitiza todo el array de opciones de la pestaña General.
 *
 * @param array|null $input Raw values (puede venir null si no hay checkbox marcada).
 * @return array           Sanitised values.
 */
function sbwscf_general_sanitize( $input = null ): array {
	$input = is_array( $input ) ? $input : array();

	return array(
		'enable_svg' => isset( $input['enable_svg'] ) ? 1 : 0,
		'enable_alt' => isset( $input['enable_alt'] ) ? 1 : 0,
	);
}

	/**
	 * Callback para imprimir la casilla de "Enable SVG".
	 *
	 * @return void
	 */
function sbwscf_enable_svg_cb(): void {
	$options    = get_option( 'sbwscf_general_settings', array() );
	$checked    = ! empty( $options['enable_svg'] );
	$field_id   = 'sbwscf_enable_svg';
	$field_name = 'sbwscf_general_settings[enable_svg]';

	printf(
		'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
		esc_attr( $field_id ),
		esc_attr( $field_name ),
		checked( true, $checked, false ),
		esc_html__( 'Allow uploading SVG and SVGZ files safely to media library.', 'smile-basic-web' )
	);
}

/**
 * Print checkbox for “Auto Alt-Text”.
 *
 * @return void
 */
function sbwscf_enable_alt_cb(): void {
	$options    = get_option( 'sbwscf_general_settings', array() );
	$checked    = ! empty( $options['enable_alt'] );
	$field_id   = 'sbwscf_enable_alt';
	$field_name = 'sbwscf_general_settings[enable_alt]';

	printf(
		'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
		esc_attr( $field_id ),
		esc_attr( $field_name ),
		checked( true, $checked, false ),
		esc_html__( 'Automatically copy the Alt Text Accessibility tag (or Title) from the EXIF metadata into the ALT field.', 'smile-basic-web' )
	);
}
