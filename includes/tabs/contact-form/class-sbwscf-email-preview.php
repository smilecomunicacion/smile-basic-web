<?php
/**
 * Shortcode para previsualizar en vivo el email dentro del front-end
 * y que sea accesible desde el Customizer.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase SBWSCF_Email_Preview
 *
 * Representa la funcionalidad para la vista previa de correos electrónicos.
 */
class SBWSCF_Email_Preview {

	/**
	 * Constructor: agrega el shortcode para la vista previa.
	 */
	public function __construct() {
		add_shortcode( 'sbwscf_email_preview', array( $this, 'render_preview' ) );
	}

	/**
	 * Genera el HTML que simula la copia del correo utilizando la configuración almacenada.
	 *
	 * @return string HTML simulado del correo electrónico.
	 */
	public function render_preview() {
		// Recuperar configuraciones de email desde el Customizer.
		$email_logo_bg        = get_theme_mod( 'sbwscf_email_logo_bg', '#FFFFFF' );
		$email_logo_padding   = get_theme_mod( 'sbwscf_email_logo_padding', '2rem' );
		$email_msg_bg         = get_theme_mod( 'sbwscf_email_msg_bg', '#FFFFFF' );
		$email_msg_padding    = get_theme_mod( 'sbwscf_email_msg_padding', '1rem' );
		$email_usertext_size  = get_theme_mod( 'sbwscf_email_usertext_size', 'medium' );
		$email_usertext_color = get_theme_mod( 'sbwscf_email_usertext_color', '#333333' );
		$email_label_size     = get_theme_mod( 'sbwscf_email_label_size', 'medium' );
		$email_label_color    = get_theme_mod( 'sbwscf_email_label_color', '#000000' );
		$email_notice_bg      = get_theme_mod( 'sbwscf_email_notice_bg', '#FFFFFF' );
		$email_notice_size    = get_theme_mod( 'sbwscf_email_notice_size', 'x-small' );
		$email_notice_color   = get_theme_mod( 'sbwscf_email_notice_color', '#999999' );
		$email_notice_padding = get_theme_mod( 'sbwscf_email_notice_padding', '2rem' );

		// Nuevos ajustes para "Copy message to user".
		$email_copy_message_size    = get_theme_mod( 'sbwscf_email_copy_message_size', '1.4rem' );
		$email_copy_message_color   = get_theme_mod( 'sbwscf_email_copy_message_color', '#333333' );
		$email_usertext_line_height = get_theme_mod( 'sbwscf_email_usertext_line_height', '2' );
		$email_notice_line_height   = get_theme_mod( 'sbwscf_email_notice_line_height', '1.2' );

		// Recuperar configuraciones y campos personalizados guardados.
		$settings      = get_option( 'sbwscf_settings', array() );
		$custom_fields = get_option( 'sbwscf_custom_fields', array() );

		// Simular datos del formulario para la vista previa.
		$sample_data = array();
		foreach ( $custom_fields as $field ) {
			$name = isset( $field['name'] ) ? $field['name'] : '';
			$type = isset( $field['type'] ) ? $field['type'] : 'text';

			switch ( $type ) {
				case 'textarea':
					$sample_data[ $name ] = 'Sample user message.';
					break;
				case 'email':
					$sample_data[ $name ] = 'user@example.com';
					break;
				case 'number':
					$sample_data[ $name ] = '123456789';
					break;
				case 'select_single':
				case 'select_multiple':
					$options              = ! empty( $field['options'] ) ? explode( ',', $field['options'] ) : array();
					$sample_data[ $name ] = trim( reset( $options ) );
					break;
				default:
					$sample_data[ $name ] = 'Example value';
			}
		}

		// Construir el contenido (simulado) con etiquetas <p>.
		$message_content = '';
		foreach ( $sample_data as $key => $value ) {
			$field_label = '';
			foreach ( $custom_fields as $field ) {
				if ( isset( $field['name'] ) && $field['name'] === $key ) {
					$field_label = isset( $field['label'] ) ? $field['label'] : ucfirst( $key );
					break;
				}
			}
			$message_content .= '<p><strong>' . esc_html( $field_label ) . ':</strong> ' . esc_html( $value ) . '</p>';
		}

		// Logo cargado por el usuario (desde la biblioteca de medios).
		$logo_html = '';
		if ( ! empty( $settings['logo_id'] ) ) {
			$logo_id     = absint( $settings['logo_id'] );
			$company_url = isset( $settings['company_url'] ) ? esc_url( $settings['company_url'] ) : '';

			$logo = wp_get_attachment_image(
				$logo_id,
				'medium',
				false,
				array( 'style' => 'max-width:100%; height:auto;' )
			);

			if ( $logo ) {
				$logo_html = $company_url
					? '<a href="' . $company_url . '" target="_blank" rel="noopener noreferrer">' . $logo . '</a>'
					: $logo;
			}
		}

		// Estructura principal del correo simulado.
		$html = '<div style="max-width:800px; margin:0 auto; background-color:whitesmoke;">';

		// Sección del "logo" superior (si existe).
		$html .= '<div style="text-align:center; font-size:larger; background:' . esc_attr( $email_logo_bg ) . '; padding:' . esc_attr( $email_logo_padding ) . ';">';
		// Si no hay logo subido, no se muestra un placeholder remoto para evitar el error.
		// Dejas el espacio en blanco o podrías poner texto si lo deseas.
		$html .= $logo_html ? $logo_html : '';
		$html .= '</div>';

		// Contenedor principal del mensaje.
		$html .= '<div style="font-family:Arial,sans-serif; color:' . esc_attr( $email_usertext_color ) . '; background:' . esc_attr( $email_msg_bg ) . '; padding:' . esc_attr( $email_msg_padding ) . ';">';

		// Mensaje de copia: "Thank you for contacting us..." .
		$message_para = '<p style="text-align:center; margin-top:0; margin-bottom:1.5rem; line-height: normal !important; font-size:' . esc_attr( $email_copy_message_size ) . '; color:' . esc_attr( $email_copy_message_color ) . '; line-height:' . esc_attr( $email_usertext_line_height ) . ';">' .
		nl2br( esc_html( $settings['copy_message'] ?? 'Thank you for contacting us. Here is a copy of your message:' ) ) .
		'</p>';
		$html        .= $message_para;

		$html .= '<div style="text-align:left;">';

		// Ajustar estilos inline al <p> y <strong> dentro del contenido.
		$styled_message = str_replace(
			'<strong>',
			'<strong style="margin:0px; font-size:' . esc_attr( $email_label_size ) . '; color:' . esc_attr( $email_label_color ) . ';">',
			$message_content
		);

		$styled_message = str_replace(
			'<p>',
			'<p style="margin:0px; font-size:' . esc_attr( $email_usertext_size ) . '; color:' . esc_attr( $email_usertext_color ) . '; line-height:' . esc_attr( $email_usertext_line_height ) . ';">',
			$styled_message
		);

		$html .= $styled_message;
		$html .= '</div></div>';

		// Sección del aviso (notice).
		if ( ! empty( $settings['footer_notice'] ) ) {
			$html .= '<div style="text-align:center; font-size:' . esc_attr( $email_notice_size ) . '; color:' . esc_attr( $email_notice_color ) . '; background:' . esc_attr( $email_notice_bg ) . '; padding:' . esc_attr( $email_notice_padding ) . '; line-height:' . esc_attr( $email_notice_line_height ) . ';">';
			$html .= esc_html( $settings['footer_notice'] );
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}

// Instanciar la clase para que el shortcode esté disponible.
new SBWSCF_Email_Preview();
