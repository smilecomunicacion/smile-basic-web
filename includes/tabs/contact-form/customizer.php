<?php
/**
 * Customizer settings for the Contact Form module.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Register Customizer settings & controls for the Contact Form Appearance.
 *
 * @param WP_Customize_Manager $wp_customize Customize Manager instance.
 * @return void
 */
function sbwscf_contactform_customize_register( WP_Customize_Manager $wp_customize ) {

	// Sección principal para la apariencia del formulario.
	$wp_customize->add_section(
		'sbwscf_appearance',
		array(
			'title'    => esc_html__( 'SMiLE Basic Web Form Appearance', 'smile-basic-web' ),
			'priority' => 160,
		)
	);

	// Definimos un array con todos los ajustes. Agregamos los cuatro nuevos:.
	$settings = array(
		// 1. Etiquetas (label).
		'sbwscf_label_font_size'             => array(
			'default' => '16px',
			'label'   => esc_html__( 'Label Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_label_font_color'            => array(
			'default' => '#666666',
			'label'   => esc_html__( 'Label Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_label_font_weight'           => array(
			'default' => 'normal',
			'label'   => esc_html__( 'Label Font Weight', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'normal'  => esc_html__( 'Normal', 'smile-basic-web' ),
				'bold'    => esc_html__( 'Bold', 'smile-basic-web' ),
				'lighter' => esc_html__( 'Lighter', 'smile-basic-web' ),
				'bolder'  => esc_html__( 'Bolder', 'smile-basic-web' ),
			),
		),
		// 2. Inputs de texto.
		'sbwscf_input_font_size'             => array(
			'default' => '16px',
			'label'   => esc_html__( 'Input Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_input_line_height'           => array(
			'default' => '1.5',
			'label'   => esc_html__( 'Input Line Height', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_input_padding'               => array(
			'default' => '1rem',
			'label'   => esc_html__( 'Input Field Padding', 'smile-basic-web' ),
			'type'    => 'text',
		),
		// 3. Placeholder
		'sbwscf_placeholder_font_size'       => array(
			'default' => '16px',
			'label'   => esc_html__( 'Placeholder Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_placeholder_font_color'      => array(
			'default' => '#7a7a7a',
			'label'   => esc_html__( 'Placeholder Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		// 4. Campos de entrada (borde, radio).
		'sbwscf_input_width'                 => array(
			'default' => '100%',
			'label'   => esc_html__( 'Input Field Width', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_field_border_color'          => array(
			'default' => '#cccccc',
			'label'   => esc_html__( 'Field Border Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_field_border_width'          => array(
			'default' => '1px',
			'label'   => esc_html__( 'Field Border Width', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_field_border_style'          => array(
			'default' => 'solid',
			'label'   => esc_html__( 'Field Border Style', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'solid'  => esc_html__( 'Solid', 'smile-basic-web' ),
				'dashed' => esc_html__( 'Dashed', 'smile-basic-web' ),
				'dotted' => esc_html__( 'Dotted', 'smile-basic-web' ),
				'double' => esc_html__( 'Double', 'smile-basic-web' ),
				'none'   => esc_html__( 'None', 'smile-basic-web' ),
			),
		),
		'sbwscf_field_border_radius'         => array(
			'default' => '4px',
			'label'   => esc_html__( 'Field Border Radius', 'smile-basic-web' ),
			'type'    => 'text',
		),

		// 5. Textarea.
		'sbwscf_textarea_width'              => array(
			'default' => '100%',
			'label'   => esc_html__( 'Textarea Width', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_textarea_height'             => array(
			'default' => '150px',
			'label'   => esc_html__( 'Textarea Height', 'smile-basic-web' ),
			'type'    => 'text',
		),

		// 6. SELECT (Single y Multiple).
		'sbwscf_select_font_size'            => array(
			'default' => '16px',
			'label'   => esc_html__( 'Select Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_select_font_color'           => array(
			'default' => '#000000',
			'label'   => esc_html__( 'Select Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_select_border_color'         => array(
			'default' => '#cccccc',
			'label'   => esc_html__( 'Select Border Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_select_border_width'         => array(
			'default' => '1px',
			'label'   => esc_html__( 'Select Border Width', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_select_border_style'         => array(
			'default' => 'solid',
			'label'   => esc_html__( 'Select Border Style', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'solid'  => esc_html__( 'Solid', 'smile-basic-web' ),
				'dashed' => esc_html__( 'Dashed', 'smile-basic-web' ),
				'dotted' => esc_html__( 'Dotted', 'smile-basic-web' ),
				'double' => esc_html__( 'Double', 'smile-basic-web' ),
				'none'   => esc_html__( 'None', 'smile-basic-web' ),
			),
		),
		'sbwscf_select_border_radius'        => array(
			'default' => '1px',
			'label'   => esc_html__( 'Select Border Radius', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_select_font_weight'          => array(
			'default' => 'normal',
			'label'   => esc_html__( 'Select Font Weight', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'normal'  => esc_html__( 'Normal', 'smile-basic-web' ),
				'bold'    => esc_html__( 'Bold', 'smile-basic-web' ),
				'lighter' => esc_html__( 'Lighter', 'smile-basic-web' ),
				'bolder'  => esc_html__( 'Bolder', 'smile-basic-web' ),
			),
		),
		'sbwscf_select_background'           => array(
			'default' => '#efefef',
			'label'   => esc_html__( 'Select Background (Single/Multi)', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_select_menu_background'      => array(
			'default' => '#ffffff',
			'label'   => esc_html__( 'Select Menu Background (Single/Multi)', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_select_width'                => array(
			'default' => '100%',
			'label'   => esc_html__( 'Select Width (Single/Multi)', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_select_height'               => array(
			'default' => '3rem',
			'label'   => esc_html__( 'Select Height (Single/Multi)', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_select_text_align'           => array(
			'default' => 'left',
			'label'   => esc_html__( 'Select Text Align (Single/Multi)', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'left'   => esc_html__( 'Left', 'smile-basic-web' ),
				'center' => esc_html__( 'Center', 'smile-basic-web' ),
				'right'  => esc_html__( 'Right', 'smile-basic-web' ),
			),
		),
               'sbwscf_consent_instructions_font_color' => array(
                       'default' => '#666666',
                       'label'   => esc_html__( 'Consent Instructions Font Color', 'smile-basic-web' ),
                       'type'    => 'color',
               ),
               'sbwscf_consent_instructions_font_size'  => array(
                       'default' => '14px',
                       'label'   => esc_html__( 'Consent Instructions Font Size', 'smile-basic-web' ),
                       'type'    => 'text',
               ),
                // Nuevo: Tamaño de fuente para el texto explicativo.
                'sbwscf_form_explanation_font_size'   => array(
                        'default' => '14px',
                        'label'   => esc_html__( 'Form Explanation Font Size', 'smile-basic-web' ),
                        'type'    => 'text',
                ),
                'sbwscf_form_explanation_font_color' => array(
                        'default' => '#666666',
                        'label'   => esc_html__( 'Form Explanation Font Color', 'smile-basic-web' ),
                        'type'    => 'color',
                ),
                // 7. Submit button.
                'sbwscf_submit_button_color'         => array(
			'default' => '#2b71f2',
			'label'   => esc_html__( 'Submit Button Background Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_submit_button_hover_color'   => array(
			'default' => '#00a2f9',
			'label'   => esc_html__( 'Submit Button Hover Background Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_submit_button_font_color'    => array(
			'default' => '#ffffff',
			'label'   => esc_html__( 'Submit Button Font Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_submit_button_font_size'     => array(
			'default' => '16px',
			'label'   => esc_html__( 'Submit Button Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_submit_button_border_color'  => array(
			'default' => '#006799',
			'label'   => esc_html__( 'Submit Button Border Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_submit_button_border_width'  => array(
			'default' => '1px',
			'label'   => esc_html__( 'Submit Button Border Width', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_submit_button_border_style'  => array(
			'default' => 'solid',
			'label'   => esc_html__( 'Submit Button Border Style', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'solid'  => esc_html__( 'Solid', 'smile-basic-web' ),
				'dashed' => esc_html__( 'Dashed', 'smile-basic-web' ),
				'dotted' => esc_html__( 'Dotted', 'smile-basic-web' ),
				'double' => esc_html__( 'Double', 'smile-basic-web' ),
				'none'   => esc_html__( 'None', 'smile-basic-web' ),
			),
		),
		'sbwscf_submit_button_font_weight'   => array(
			'default' => 'bold',
			'label'   => esc_html__( 'Submit Button Font Weight', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'normal'  => esc_html__( 'Normal', 'smile-basic-web' ),
				'bold'    => esc_html__( 'Bold', 'smile-basic-web' ),
				'lighter' => esc_html__( 'Lighter', 'smile-basic-web' ),
				'bolder'  => esc_html__( 'Bolder', 'smile-basic-web' ),
			),
		),
		'sbwscf_submit_button_border_radius' => array(
			'default' => '4px',
			'label'   => esc_html__( 'Submit Button Border Radius', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_submit_button_width'         => array(
			'default' => '20rem',
			'label'   => esc_html__( 'Submit Button Width', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_submit_button_height'        => array(
			'default' => '2.5rem',
			'label'   => esc_html__( 'Submit Button Height', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_submit_button_alignment'     => array(
			'default' => 'center',
			'label'   => esc_html__( 'Submit Button Alignment', 'smile-basic-web' ),
			'type'    => 'select',
			'choices' => array(
				'left'   => esc_html__( 'Left', 'smile-basic-web' ),
				'center' => esc_html__( 'Center', 'smile-basic-web' ),
				'right'  => esc_html__( 'Right', 'smile-basic-web' ),
			),
		),

		// 8. Links.
		'sbwscf_links_color'                 => array(
			'default' => '#0073aa',
			'label'   => esc_html__( 'Links Color (Privacy / Legal)', 'smile-basic-web' ),
			'type'    => 'color',
		),

		// 9. Recaptcha margin.
		'sbwscf_recaptcha_margin_bottom'     => array(
			'default' => '20px',
			'label'   => esc_html__( 'Recaptcha Bottom Margin', 'smile-basic-web' ),
			'type'    => 'text',
		),

		// NUEVAS OPCIONES: Mensajes de error y éxito.
		'sbwscf_error_msg_color'             => array(
			'default' => '#ff0044',
			'label'   => esc_html__( 'Error Message Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_error_msg_font_size'         => array(
			'default' => '1.3rem',
			'label'   => esc_html__( 'Error Message Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
		'sbwscf_success_msg_color'           => array(
			'default' => '#00c64f',
			'label'   => esc_html__( 'Success Message Color', 'smile-basic-web' ),
			'type'    => 'color',
		),
		'sbwscf_success_msg_font_size'       => array(
			'default' => '1.3rem',
			'label'   => esc_html__( 'Success Message Font Size', 'smile-basic-web' ),
			'type'    => 'text',
		),
	);

	// Registrar ajustes y controles en el Customizer.
	foreach ( $settings as $setting_id => $args ) {

		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $args['default'],
				'sanitize_callback' => ( 'color' === $args['type'] )
					? 'sanitize_hex_color'
					: 'sanitize_text_field',
			)
		);

		$control_args = array(
			'label'    => $args['label'],
			'section'  => 'sbwscf_appearance',
			'settings' => $setting_id,
			'type'     => $args['type'],
		);

		if ( ! empty( $args['choices'] ) ) {
			$control_args['choices'] = $args['choices'];
		}

		$wp_customize->add_control( $setting_id, $control_args );
	}
}

/**
 * Output dynamic CSS for the Contact Form based on Customizer settings.
 *
 * @return void
 */
function sbwscf_contactform_output_customizer_css() {

       // 1. Etiquetas.
       $label_font_size                     = get_theme_mod( 'sbwscf_label_font_size', '16px' );
       $label_font_color                    = get_theme_mod( 'sbwscf_label_font_color', '#666666' );
       $label_font_weight                   = get_theme_mod( 'sbwscf_label_font_weight', 'normal' );
       $consent_instructions_font_color     = get_theme_mod( 'sbwscf_consent_instructions_font_color', '#666666' );
       $consent_instructions_font_size      = get_theme_mod( 'sbwscf_consent_instructions_font_size', '14px' );
       $form_explanation_font_size          = get_theme_mod( 'sbwscf_form_explanation_font_size', '14px' );
       $form_explanation_font_color         = get_theme_mod( 'sbwscf_form_explanation_font_color', '#666666' );

	// 2. Inputs.
	$input_font_size   = get_theme_mod( 'sbwscf_input_font_size', '16px' );
	$input_line_height = get_theme_mod( 'sbwscf_input_line_height', '1.5' );
	$input_padding     = get_theme_mod( 'sbwscf_input_padding', '1rem' );

	// 3. Placeholder.
	$placeholder_font_size  = get_theme_mod( 'sbwscf_placeholder_font_size', '16px' );
	$placeholder_font_color = get_theme_mod( 'sbwscf_placeholder_font_color', '#7a7a7a' );

	// 4. Campos (borde, etc.).
	$input_width         = get_theme_mod( 'sbwscf_input_width', '100%' );
	$field_border_color  = get_theme_mod( 'sbwscf_field_border_color', '#cccccc' );
	$field_border_width  = get_theme_mod( 'sbwscf_field_border_width', '1px' );
	$field_border_style  = get_theme_mod( 'sbwscf_field_border_style', 'solid' );
	$field_border_radius = get_theme_mod( 'sbwscf_field_border_radius', '4px' );

	// 5. Textarea.
	$textarea_width  = get_theme_mod( 'sbwscf_textarea_width', '100%' );
	$textarea_height = get_theme_mod( 'sbwscf_textarea_height', '150px' );

	// 6. SELECT (unificado).
	$select_font_size       = get_theme_mod( 'sbwscf_select_font_size', '16px' );
	$select_font_color      = get_theme_mod( 'sbwscf_select_font_color', '#000000' );
	$select_border_color    = get_theme_mod( 'sbwscf_select_border_color', '#cccccc' );
	$select_border_width    = get_theme_mod( 'sbwscf_select_border_width', '1px' );
	$select_border_style    = get_theme_mod( 'sbwscf_select_border_style', 'solid' );
	$select_border_radius   = get_theme_mod( 'sbwscf_select_border_radius', '1px' );
	$select_font_weight     = get_theme_mod( 'sbwscf_select_font_weight', 'normal' );
	$select_background      = get_theme_mod( 'sbwscf_select_background', '#efefef' );
	$select_menu_background = get_theme_mod( 'sbwscf_select_menu_background', '#ffffff' );
	$select_width           = get_theme_mod( 'sbwscf_select_width', '100%' );
	$select_height          = get_theme_mod( 'sbwscf_select_height', '3rem' );
	$select_text_align      = get_theme_mod( 'sbwscf_select_text_align', 'left' );

	// 7. Submit button.
	$submit_button_color         = get_theme_mod( 'sbwscf_submit_button_color', '#2b71f2' );
	$submit_button_hover_color   = get_theme_mod( 'sbwscf_submit_button_hover_color', '#00a2f9' );
	$submit_button_font_color    = get_theme_mod( 'sbwscf_submit_button_font_color', '#ffffff' );
	$submit_button_font_size     = get_theme_mod( 'sbwscf_submit_button_font_size', '16px' );
	$submit_button_border_color  = get_theme_mod( 'sbwscf_submit_button_border_color', '#006799' );
	$submit_button_border_width  = get_theme_mod( 'sbwscf_submit_button_border_width', '1px' );
	$submit_button_border_style  = get_theme_mod( 'sbwscf_submit_button_border_style', 'solid' );
	$submit_button_font_weight   = get_theme_mod( 'sbwscf_submit_button_font_weight', 'bold' );
	$submit_button_border_radius = get_theme_mod( 'sbwscf_submit_button_border_radius', '4px' );
	$submit_button_width         = get_theme_mod( 'sbwscf_submit_button_width', '20rem' );
	$submit_button_height        = get_theme_mod( 'sbwscf_submit_button_height', '2.5rem' );
	$submit_button_alignment     = get_theme_mod( 'sbwscf_submit_button_alignment', 'center' );

	// 8. Links.
	$links_color = get_theme_mod( 'sbwscf_links_color', '#0073aa' );

	// 9. Recaptcha margin.
	$recaptcha_margin_bottom = get_theme_mod( 'sbwscf_recaptcha_margin_bottom', '20px' );

	// 10. Mensajes de Error.
	$error_msg_color     = get_theme_mod( 'sbwscf_error_msg_color', '#ff0044' );
	$error_msg_font_size = get_theme_mod( 'sbwscf_error_msg_font_size', '1.3rem' );

	$success_msg_color     = get_theme_mod( 'sbwscf_success_msg_color', '#00c64f' );
	$success_msg_font_size = get_theme_mod( 'sbwscf_success_msg_font_size', '1.3rem' );
	?>
<style type="text/css">
/* ==================== */
/* 1. Etiquetas */
/* ==================== */
.sbwscf-contact-form label {
	font-size: <?php echo esc_attr( $label_font_size ); ?>;
	color: <?php echo esc_attr( $label_font_color ); ?>;
	font-weight: <?php echo esc_attr( $label_font_weight ); ?>;
}

/* Nueva regla para el Texto explicativo de los fines del formulario */
.sbwscf-contact-form .sbwscf-form-explanation {
        font-size: <?php echo esc_attr( $form_explanation_font_size ); ?>;
        color: <?php echo esc_attr( $form_explanation_font_color ); ?>;
}

.sbwscf-contact-form .sbwscf-form-explanation p {
        font-size: inherit;
}

section .sbwscf-contact-form .sbwscf-form-explanation p:not(.text-emphasis):not(.h2) {
        color: <?php echo esc_attr( $form_explanation_font_color ); ?>;
}

section .sbwscf-contact-form p.sbwscf-consent-instructions,
.sbwscf-contact-form .sbwscf-consent-instructions {
       color: <?php echo esc_attr( $consent_instructions_font_color ); ?> !important;
       font-size: <?php echo esc_attr( $consent_instructions_font_size ); ?>;
}

/* ==================== */
/* 2. Inputs (text, email, etc.) */
/* ==================== */
.sbwscf-contact-form input[type="text"],
.sbwscf-contact-form input[type="email"],
.sbwscf-contact-form input[type="number"],
.sbwscf-contact-form input[type="url"],
.sbwscf-contact-form input[type="tel"],
.sbwscf-contact-form textarea {
	font-size: <?php echo esc_attr( $input_font_size ); ?>;
	line-height: <?php echo esc_attr( $input_line_height ); ?>;
	width: <?php echo esc_attr( $input_width ); ?>;
	border-width: <?php echo esc_attr( $field_border_width ); ?>;
	border-style: <?php echo esc_attr( $field_border_style ); ?>;
	border-color: <?php echo esc_attr( $field_border_color ); ?>;
	border-radius: <?php echo esc_attr( $field_border_radius ); ?>;
	outline: none;
}

/* ==================== */
/* 3. Placeholder */
/* ==================== */
.sbwscf-contact-form ::-webkit-input-placeholder {
	font-size: <?php echo esc_attr( $placeholder_font_size ); ?>;
	color: <?php echo esc_attr( $placeholder_font_color ); ?>;
}

.sbwscf-contact-form :-moz-placeholder {
	font-size: <?php echo esc_attr( $placeholder_font_size ); ?>;
	color: <?php echo esc_attr( $placeholder_font_color ); ?>;
}

.sbwscf-contact-form ::-moz-placeholder {
	font-size: <?php echo esc_attr( $placeholder_font_size ); ?>;
	color: <?php echo esc_attr( $placeholder_font_color ); ?>;
}

.sbwscf-contact-form :-ms-input-placeholder {
	font-size: <?php echo esc_attr( $placeholder_font_size ); ?>;
	color: <?php echo esc_attr( $placeholder_font_color ); ?>;
}

/* ==================== */
/* 4. Textarea Size */
/* ==================== */
.sbwscf-contact-form textarea {
	width: <?php echo esc_attr( $textarea_width ); ?>;
	height: <?php echo esc_attr( $textarea_height ); ?>;
}

/* ==================== */
/* 5. SINGLE SELECT */
/* ==================== */
/* Contenedor de select_single */
.sbwscf-select-single {
	position: relative; /* Asegura que el menú se posicione correctamente */
	display: inline-block; /* Mantiene la consistencia con select_multiple */
	width: <?php echo esc_attr( $select_width ); ?>; /* Asegura que el contenedor tenga el ancho definido */
}

/* Botón principal de select_single */
.sbwscf-select-single-button {
	font-size: <?php echo esc_attr( $select_font_size ); ?>;
	color: <?php echo esc_attr( $select_font_color ); ?>;
	border-width: <?php echo esc_attr( $select_border_width ); ?>;
	border-style: <?php echo esc_attr( $select_border_style ); ?>;
	border-color: <?php echo esc_attr( $select_border_color ); ?>;
	border-radius: <?php echo esc_attr( $select_border_radius ); ?>;
	font-weight: <?php echo esc_attr( $select_font_weight ); ?>;
	background: <?php echo esc_attr( $select_background ); ?>;
	position: relative;
	padding-right: 1.5em;
	padding-left: 1em;
	cursor: pointer;
	outline: none;

	/* Ancho/Alto unificado */
	width: 100%; /* Botón ocupa todo el ancho del contenedor */
	height: <?php echo esc_attr( $select_height ); ?>;

	text-align: <?php echo esc_attr( $select_text_align ); ?>;

	/* Prevención de desbordamiento de texto */
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.sbwscf-select-single-button::after {
	content: '▼';
	/* Flecha unificada */
	position: absolute;
	right: 0.5em;
	top: 50%;
	transform: translateY(-50%);
	font-size: 85%;
}

/* Menú emergente de select_single */
.sbwscf-select-single-menu {
	background-color: <?php echo esc_attr( $select_menu_background ); ?>; /* Aplicar background del menú */
	padding: 1rem;
	border: <?php echo esc_attr( $select_border_width ); ?> <?php echo esc_attr( $select_border_style ); ?> <?php echo esc_attr( $select_border_color ); ?>;
	border-radius: <?php echo esc_attr( $select_border_radius ); ?>;
	display: none; /* Inicialmente oculto */
	position: absolute;
	width: 100%; /* Asegurar que el menú coincida con el ancho del botón */
	box-sizing: border-box;
	z-index: 1000;
	top: 100%; /* Posicionar justo debajo del botón */
	left: 0; /* Alinear al inicio del botón */
}

/* Opciones en select_single */
.sbwscf-select-single-option {
	display: block; /* Asegura que cada opción ocupe una línea completa */
	margin-bottom: 5px; /* Espacio entre opciones */
	cursor: pointer;
}

/* ==================== */
/* 6. SELECT MULTIPLE (botón + menú) */
/* ==================== */
/* Contenedor de select_multiple */
.sbwscf-multiselect-container {
	position: relative; /* Asegura que el menú se posicione correctamente */
	display: inline-block; /* Para mantener la consistencia */
	width: <?php echo esc_attr( $select_width ); ?>; /* Asegura que el contenedor tenga el ancho definido */
}

/* Botón principal de select_multiple */
.sbwscf-multiselect-button {
	font-size: <?php echo esc_attr( $select_font_size ); ?>;
	color: <?php echo esc_attr( $select_font_color ); ?>;
	border-width: <?php echo esc_attr( $select_border_width ); ?>;
	border-style: <?php echo esc_attr( $select_border_style ); ?>;
	border-color: <?php echo esc_attr( $select_border_color ); ?>;
	border-radius: <?php echo esc_attr( $select_border_radius ); ?>;
	font-weight: <?php echo esc_attr( $select_font_weight ); ?>;
	background: <?php echo esc_attr( $select_background ); ?>;
	position: relative;
	padding-right: 1.5em;
	padding-left: 1em;
	cursor: pointer;
	outline: none;

	/* Ancho/Alto unificado */
	width: 100%; /* Botón ocupa todo el ancho del contenedor */
	height: <?php echo esc_attr( $select_height ); ?>;

	text-align: <?php echo esc_attr( $select_text_align ); ?>;

	/* Prevención de desbordamiento de texto */
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.sbwscf-multiselect-button::after {
	content: '▼';
	position: absolute;
	right: 0.5em;
	top: 50%;
	transform: translateY(-50%);
	font-size: 85%;
}

/* Menú emergente de select_multiple */
.sbwscf-multiselect-menu {
	background-color: <?php echo esc_attr( $select_menu_background ); ?>; /* Aplicar background del menú */
	padding: 1rem;
	border: <?php echo esc_attr( $select_border_width ); ?> <?php echo esc_attr( $select_border_style ); ?> <?php echo esc_attr( $select_border_color ); ?>;
	border-radius: <?php echo esc_attr( $select_border_radius ); ?>;
	display: none; /* Inicialmente oculto */
	position: absolute;
	width: 100%; /* Asegurar que el menú coincida con el ancho del botón */
	box-sizing: border-box;
	z-index: 1000;
	top: 100%; /* Posicionar justo debajo del botón */
	left: 0; /* Alinear al inicio del botón */
}

/* Opciones en select_multiple */
.sbwscf-multiselect-option {
	display: block; /* Asegura que cada opción ocupe una línea completa */
	margin-bottom: 5px; /* Espacio entre opciones */
	cursor: pointer;
}

/* ==================== */
/* 7. Botón de Envío */
/* ==================== */
.sbwscf-submit-wrapper {
	margin-top: 10px;
	margin-bottom: 10px;
	text-align: <?php echo esc_attr( $submit_button_alignment ); ?>;
}

.sbwscf-submit-wrapper button[type="submit"],
.sbwscf-submit-wrapper input[type="submit"] {
	background-color: <?php echo esc_attr( $submit_button_color ); ?>;
	color: <?php echo esc_attr( $submit_button_font_color ); ?>;
	font-size: <?php echo esc_attr( $submit_button_font_size ); ?>;
	border-width: <?php echo esc_attr( $submit_button_border_width ); ?>;
	border-style: <?php echo esc_attr( $submit_button_border_style ); ?>;
	border-color: <?php echo esc_attr( $submit_button_border_color ); ?>;
	border-radius: <?php echo esc_attr( $submit_button_border_radius ); ?>;
	font-weight: <?php echo esc_attr( $submit_button_font_weight ); ?>;
	width: <?php echo esc_attr( $submit_button_width ); ?>;
	height: <?php echo esc_attr( $submit_button_height ); ?>;
	outline: none;
	cursor: pointer;

	/* Prevención de desbordamiento de texto */
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

/* ==================== */
/* 8. Submit Button Hover */
/* ==================== */
.sbwscf-submit-wrapper button[type="submit"]:hover,
.sbwscf-submit-wrapper input[type="submit"]:hover {
	background-color: <?php echo esc_attr( $submit_button_hover_color ); ?>;
}

/* ==================== */
/* 9. Multiselect Options */
/* ==================== */
/* Ya están cubiertos arriba */

/* ==================== */
/* 10. Input Field Padding */
/* ==================== */
.sbwscf-contact-form input[type="text"],
.sbwscf-contact-form input[type="email"],
.sbwscf-contact-form input[type="number"],
.sbwscf-contact-form input[type="url"],
.sbwscf-contact-form input[type="tel"],
.sbwscf-contact-form textarea {
	padding: <?php echo esc_attr( $input_padding ); ?>;
}

/* ==================== */
/* 11. Links (Privacy/Legal) */
/* ==================== */
.sbwscf-contact-form a {
	color: <?php echo esc_attr( $links_color ); ?>;
}

/* ==================== */
/* 12. Recaptcha margin */
/* ==================== */
.grecaptcha-badge {
	margin-bottom: <?php echo esc_attr( $recaptcha_margin_bottom ); ?>;
}

/* ==================== */
/* 14. Contador de Caracteres */
/* ==================== */
.sbwscf-char-counter {
	display: block;
	font-size: 0.8em;
	color: #666666;
	margin-top: 5px;
}

/* ==================== */
/* Mensajes de Error/Éxito (nuevos) */
/* ==================== */
.sbwscf-message-error {
	color: <?php echo esc_attr( $error_msg_color ); ?> !important;
	font-size: <?php echo esc_attr( $error_msg_font_size ); ?>;
}

.sbwscf-message-success {
	color: <?php echo esc_attr( $success_msg_color ); ?> !important;
	font-size: <?php echo esc_attr( $success_msg_font_size ); ?>;
}
</style>
	<?php
}

// NOTA: El Tab Manager invocará automáticamente
// sbwscf_contactform_customize_register() en 'customize_register'.

// Hook para inyectar el CSS en front-end.
add_action( 'wp_head', 'sbwscf_contactform_output_customizer_css' );
