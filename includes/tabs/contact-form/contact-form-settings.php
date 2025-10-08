<?php
/**
 * Registers and manages settings for the Contact Form module, including SMTP configuration and custom fields.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/*
* ------------------------------------------------------------------
* Constants
* ------------------------------------------------------------------
*/
const SBWSCF_SETTINGS_SANITIZE_ARGS      = array(
	'sanitize_callback' => 'smile_basic_web_sanitize_settings',
);
const SBWSCF_CUSTOM_FIELDS_SANITIZE_ARGS = array(
	'sanitize_callback' => 'smile_basic_web_sanitize_custom_fields',
);

/*
* ------------------------------------------------------------------
* Sanitizers
* ------------------------------------------------------------------
*/

/**
 * Sanitize SMTP and general settings.
 *
 * @param array $input Input array.
 * @return array Sanitized data.
 */
function smile_basic_web_sanitize_settings( $input ): array {
	$sanitized = array();

	// Hosts and ports.
	$sanitized['host'] = isset( $input['host'] ) ? sanitize_text_field( $input['host'] ) : '';
	$sanitized['port'] = isset( $input['port'] ) ? absint( $input['port'] ) : 587;
	// Credentials.
	$sanitized['username'] = isset( $input['username'] ) ? sanitize_text_field( $input['username'] ) : '';
	$sanitized['password'] = isset( $input['password'] ) ? sanitize_text_field( $input['password'] ) : '';
	// Encryption.
	$sanitized['encryption'] = isset( $input['encryption'] ) ? sanitize_text_field( $input['encryption'] ) : 'tls';
	// Sender.
	$sanitized['from_email'] = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '';
	$sanitized['from_name']  = isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : get_bloginfo( 'name' );
	// Copy to user.
	$sanitized['send_copy_to_user'] = ! empty( $input['send_copy_to_user'] );
	$sanitized['copy_message']      = isset( $input['copy_message'] )
	? sanitize_textarea_field( $input['copy_message'] )
	: __( 'Thank you for contacting us. Here is a copy of your message:', 'smile-basic-web' );
	// Logo & company.
	$sanitized['logo_id']     = isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;
	$sanitized['company_url'] = isset( $input['company_url'] ) ? esc_url_raw( $input['company_url'] ) : '';
	// Pages.
	$sanitized['privacy_policy_page'] = isset( $input['privacy_policy_page'] )
		? absint( $input['privacy_policy_page'] )
		: 0;
	$sanitized['privacy_policy_text'] = isset( $input['privacy_policy_text'] )
		? sanitize_textarea_field( $input['privacy_policy_text'] )
		: '';
	$sanitized['legal_notice_page']   = isset( $input['legal_notice_page'] )
		? absint( $input['legal_notice_page'] )
		: 0;
	$sanitized['legal_notice_text']   = isset( $input['legal_notice_text'] )
		? sanitize_textarea_field( $input['legal_notice_text'] )
		: '';
	$sanitized['terms_page']          = isset( $input['terms_page'] )
		? absint( $input['terms_page'] )
		: 0;
	$sanitized['terms_text']          = isset( $input['terms_text'] )
		? sanitize_textarea_field( $input['terms_text'] )
		: '';
	// Marketing.
	$sanitized['marketing_opt_in_enabled'] = ! empty( $input['marketing_opt_in_enabled'] );
	$sanitized['marketing_text']           = isset( $input['marketing_text'] )
	? sanitize_textarea_field( $input['marketing_text'] )
	: '';
	// Explanation & footer.
        $sanitized['form_explanation']     = isset( $input['form_explanation'] )
                ? wp_kses_post( $input['form_explanation'] )
                : '';
        $sanitized['consent_instructions'] = isset( $input['consent_instructions'] ) ? sanitize_textarea_field( $input['consent_instructions'] ) : '';
        $sanitized['footer_notice']        = isset( $input['footer_notice'] ) ? sanitize_textarea_field( $input['footer_notice'] ) : '';
	// reCAPTCHA.
	$sanitized['recaptcha_enabled']    = ! empty( $input['recaptcha_enabled'] );
	$sanitized['recaptcha_site_key']   = isset( $input['recaptcha_site_key'] ) ? sanitize_text_field( $input['recaptcha_site_key'] ) : '';
	$sanitized['recaptcha_secret_key'] = isset( $input['recaptcha_secret_key'] ) ? sanitize_text_field( $input['recaptcha_secret_key'] ) : '';

	// If reCAPTCHA enabled, require keys.
	if ( $sanitized['recaptcha_enabled'] ) {
		if ( empty( $sanitized['recaptcha_site_key'] ) || empty( $sanitized['recaptcha_secret_key'] ) ) {
			add_settings_error(
				'sbwscf_settings',
				'recaptcha_error',
				__( 'reCAPTCHA is enabled. Both Site Key and Secret Key are required.', 'smile-basic-web' ),
				'error'
			);
			$sanitized['recaptcha_enabled'] = false;
		}
	}

	return $sanitized;
}

/**
 * Sanitiza los campos personalizados.
 *
 * @param array $input Datos de entrada.
 * @return array Datos sanitizados.
 */
function smile_basic_web_sanitize_custom_fields( $input ) {
	/**
	 * Se almacenan los campos válidos en $sanitized.
	 * Si hay un duplicado, se añade un error y se retorna la config antigua,
	 * para no guardar el cambio en la base de datos.
	 */
	$old_config = get_option( 'sbwscf_custom_fields', array() );
	$sanitized  = array();
	$names      = array();
	$has_error  = false;

	if ( is_array( $input ) ) {
		foreach ( $input as $field ) {
			$temp_field          = array();
			$temp_field['label'] = isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';
			$type                = isset( $field['type'] ) ? sanitize_text_field( $field['type'] ) : 'text';
			$temp_field['type']  = $type;

			if ( 'email usuario' === $type ) {
				$temp_field['name'] = 'email';
			} else {
				$temp_field['name'] = isset( $field['name'] ) ? sanitize_key( $field['name'] ) : '';
			}

			$temp_field['required']    = ! empty( $field['required'] ) ? true : false;
			$temp_field['placeholder'] = isset( $field['placeholder'] ) ? sanitize_text_field( $field['placeholder'] ) : '';
			$temp_field['options']     = isset( $field['options'] ) ? sanitize_text_field( $field['options'] ) : '';

			// Validar que label y name no estén vacíos y que el tipo sea uno permitido.
			$valid_types = array(
				'text',
				'email',
				'email usuario',
				'textarea',
				'number',
				'url',
				'tel',
				'select_single',
				'select_multiple',
			);

			if (
				! empty( $temp_field['label'] )
				&& ! empty( $temp_field['name'] )
				&& in_array( $temp_field['type'], $valid_types, true )
			) {
				if ( in_array( $temp_field['name'], $names, true ) ) {
					// translators: %s is a field name which has been duplicated in the form.
					add_settings_error(
						'sbwscf_custom_fields',
						'duplicate_name',
						sprintf(
							// Translators: %s is a field name which has been duplicated in the form.
							esc_html__( 'Duplicate field name "%s" is not allowed. Please change it.', 'smile-basic-web' ),
							$temp_field['name']
						),
						'error'
					);
					$has_error = true;
				} else {
					$names[] = $temp_field['name'];
				}
				// Lo añadimos a $sanitized (temporal). Si hay error, no se guardará.
				$sanitized[] = $temp_field;
			}
		}
	}

	if ( $has_error ) {
		// Almacenas la versión que el usuario intentó enviar.
		set_transient( 'sbwscf_temp_data', $input, 60 * 5 ); // 5 minutos
		return $old_config; // Para no guardar en DB.
	}

	return $sanitized;
}

/**
 * Render the Contact Form Settings page.
 *
 * @return void
 */
function smile_basic_web_contact_form_settings_callback() {
	?>
<div class="wrap">
	<h2><?php esc_html_e( 'Contact Form Settings', 'smile-basic-web' ); ?></h2>
	<p><?php esc_html_e( 'Use the shortcode [smile_contact_form] to insert the form.', 'smile-basic-web' ); ?></p>

	<?php settings_errors(); ?>

	<form action="options.php" method="post">
		<?php
				// Use unified group and page slug.
				settings_fields( 'sbwscf_page_contact_form' );
				do_settings_sections( 'sbwscf_page_contact_form' );
				submit_button();
		?>
	</form>
</div>
	<?php
}


/**
 * Renderiza el campo SMTP Server.
 */
function smile_basic_web_render_smtp_host_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<input type="text" name="sbwscf_settings[host]" value="<?php echo esc_attr( $options['host'] ?? '' ); ?>"
	class="regular-text" required>
<p class="description"><?php esc_html_e( 'SMTP server for sending emails.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo SMTP Port.
 */
function smile_basic_web_render_smtp_port_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<input type="number" name="sbwscf_settings[port]" value="<?php echo esc_attr( $options['port'] ?? 587 ); ?>"
	class="small-text" min="1" required>
<p class="description"><?php esc_html_e( 'SMTP port.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo SMTP Username.
 */
function smile_basic_web_render_smtp_username_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<input type="text" name="sbwscf_settings[username]" value="<?php echo esc_attr( $options['username'] ?? '' ); ?>"
	class="regular-text" required>
<p class="description"><?php esc_html_e( 'SMTP username.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo SMTP Password.
 */
function smile_basic_web_render_smtp_password_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<input type="password" name="sbwscf_settings[password]" value="<?php echo esc_attr( $options['password'] ?? '' ); ?>"
	class="regular-text" required>
<p class="description"><?php esc_html_e( 'SMTP password.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo SMTP Encryption.
 */
function smile_basic_web_render_smtp_encryption_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<select name="sbwscf_settings[encryption]" required>
	<option value="none" <?php selected( $options['encryption'] ?? '', 'none' ); ?>>
		<?php esc_html_e( 'None', 'smile-basic-web' ); ?>
	</option>
	<option value="tls" <?php selected( $options['encryption'] ?? '', 'tls' ); ?>>
		<?php esc_html_e( 'TLS', 'smile-basic-web' ); ?>
	</option>
	<option value="ssl" <?php selected( $options['encryption'] ?? '', 'ssl' ); ?>>
		<?php esc_html_e( 'SSL', 'smile-basic-web' ); ?>
	</option>
</select>
<p class="description"><?php esc_html_e( 'SMTP encryption type.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo Sender Email.
 */
function smile_basic_web_render_smtp_from_email_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<input type="email" name="sbwscf_settings[from_email]" value="<?php echo esc_attr( $options['from_email'] ?? '' ); ?>"
	class="regular-text" required>
<p class="description"><?php esc_html_e( 'Sender email address.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo Sender Name.
 */
function smile_basic_web_render_smtp_from_name_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<input type="text" name="sbwscf_settings[from_name]"
	value="<?php echo esc_attr( $options['from_name'] ?? get_bloginfo( 'name' ) ); ?>" class="regular-text" required>
<p class="description"><?php esc_html_e( 'Sender name.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo Send copy to user.
 */
function smile_basic_web_render_send_copy_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<label>
	<input type="checkbox" name="sbwscf_settings[send_copy_to_user]" value="1"
		<?php checked( $options['send_copy_to_user'] ?? false, true ); ?>>
	<?php esc_html_e( 'Send a copy of the email to the user who filled out the form.', 'smile-basic-web' ); ?>
</label>
<p class="description">
	<?php esc_html_e( 'If selected, the user will receive a copy of their message.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo Copy message to user.
 */
function smile_basic_web_render_copy_message_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<textarea name="sbwscf_settings[copy_message]" rows="4" cols="50"
	class="large-text"><?php echo esc_textarea( $options['copy_message'] ?? __( 'Thank you for contacting us. Here is a copy of your message:', 'smile-basic-web' ) ); ?></textarea>
<p class="description">
	<?php esc_html_e( 'Message that will be sent to the user along with a copy of their message.', 'smile-basic-web' ); ?>
</p>
	<?php
}

/**
 * Renderiza el campo Company Logo.
 */
function smile_basic_web_render_logo_field() {
	$options = get_option( 'sbwscf_settings', array() );
	$logo_id = isset( $options['logo_id'] ) ? absint( $options['logo_id'] ) : 0;
	?>
<div id="sbwscf-logo-container">
	<?php
	if ( $logo_id ) {
		echo wp_get_attachment_image(
			$logo_id,
			'medium',
			false,
			array(
				'id'    => 'sbwscf-logo-preview',
				'style' => 'max-width:100%; height:auto;',
			)
		);
	} else {
		echo '<img id="sbwscf-logo-preview" style="max-width:200px;display:none;" alt="' . esc_attr__( 'Company Logo', 'smile-basic-web' ) . '">';
	}
	?>
</div>
<input type="hidden" id="sbwscf_logo_id" name="sbwscf_settings[logo_id]" value="<?php echo esc_attr( $logo_id ); ?>">
<button type="button" class="button" id="sbwscf_upload_logo">
	<?php esc_html_e( 'Select Logo', 'smile-basic-web' ); ?>
</button>
<button type="button" class="button button-secondary" id="sbwscf_remove_logo"
	style="<?php echo $logo_id ? '' : 'display:none;'; ?>">
	<?php esc_html_e( 'Remove Logo', 'smile-basic-web' ); ?>
</button>
<p class="description">
	<?php esc_html_e( 'Select a logo from the media library to include in the user copy email.', 'smile-basic-web' ); ?>
</p>
	<?php
}

/**
 * Renderiza el campo Company URL.
 */
function smile_basic_web_render_company_url_field() {
	$options = get_option( 'sbwscf_settings', array() );
	$value   = isset( $options['company_url'] ) ? esc_url( $options['company_url'] ) : '';
	?>
<input type="url" name="sbwscf_settings[company_url]" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
<p class="description">
	<?php esc_html_e( 'Enter the URL of your company. The logo in the user copy email will link to this URL.', 'smile-basic-web' ); ?>
</p>
	<?php
}

/**
 * Renderiza el campo Privacy Policy Page.
 */
function smile_basic_web_render_privacy_policy_page_field() {
	$options         = get_option( 'sbwscf_settings', array() );
	$current_page_id = isset( $options['privacy_policy_page'] ) ? (int) $options['privacy_policy_page'] : 0;

	$pages = get_pages();
	echo '<select name="sbwscf_settings[privacy_policy_page]" required>';
	echo '<option value="0" ' . selected( 0, $current_page_id, false ) . '>';
	esc_html_e( 'None', 'smile-basic-web' );
	echo '</option>';
	foreach ( $pages as $p ) {
		echo '<option value="' . esc_attr( $p->ID ) . '" ' . selected( $p->ID, $current_page_id, false ) . '>';
		echo esc_html( $p->post_title );
		echo '</option>';
	}
	echo '</select>';
	echo '<p class="description">';
	esc_html_e( 'Select the page for your privacy policy.', 'smile-basic-web' );
	echo '</p>';
}
/**
 * Render “Privacy Policy text” textarea.
 */
function smile_basic_web_render_privacy_policy_text_field() {
	$options = get_option( 'sbwscf_settings', array() );
	// translators: %s is the placeholder for the privacy policy page link.
	$value = $options['privacy_policy_text'] ?? __( 'I have read and accept the %s', 'smile-basic-web' );
	?>
<textarea name="sbwscf_settings[privacy_policy_text]" rows="3"
	class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
<p class="description">
	<?php
		// translators: %s is the placeholder where the privacy policy page link will appear.
		esc_html_e( 'Text displayed next to the checkbox. Use %s where the privacy policy page link should appear.', 'smile-basic-web' );
	?>
</p>
	<?php
}
/**
 * Renderiza el campo Legal Notice Page.
 */
function smile_basic_web_render_legal_notice_page_field() {
	$options         = get_option( 'sbwscf_settings', array() );
	$current_page_id = isset( $options['legal_notice_page'] ) ? (int) $options['legal_notice_page'] : 0;

	$pages = get_pages();
	echo '<select name="sbwscf_settings[legal_notice_page]" required>';
	echo '<option value="0" ' . selected( 0, $current_page_id, false ) . '>';
	esc_html_e( 'None', 'smile-basic-web' );
	echo '</option>';
	foreach ( $pages as $p ) {
		echo '<option value="' . esc_attr( $p->ID ) . '" ' . selected( $p->ID, $current_page_id, false ) . '>';
		echo esc_html( $p->post_title );
		echo '</option>';
	}
	echo '</select>';
	echo '<p class="description">';
	esc_html_e( 'Select the page for your legal notice.', 'smile-basic-web' );
	echo '</p>';
}
/**
 * Render “Legal Notice text” textarea.
 */
function smile_basic_web_render_legal_notice_text_field() {
	$options = get_option( 'sbwscf_settings', array() );
	// translators: %s is the placeholder for the legal notice page link.
	$value = $options['legal_notice_text'] ?? __( 'I accept the %s', 'smile-basic-web' );
	?>
<textarea name="sbwscf_settings[legal_notice_text]" rows="3"
	class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
<p class="description">
	<?php
		// translators: %s is the placeholder where the legal notice page link should appear in the checkbox text.
		esc_html_e( 'Text displayed next to the checkbox. Use %s where the legal notice page link should appear.', 'smile-basic-web' );
	?>
</p>
	<?php
}

/**
 * Render “Terms & Conditions page” selector.
 */
function smile_basic_web_render_terms_page_field() {
	$options         = get_option( 'sbwscf_settings', array() );
	$current_page_id = isset( $options['terms_page'] ) ? (int) $options['terms_page'] : 0;

	$pages = get_pages();
	echo '<select name="sbwscf_settings[terms_page]">';
	echo '<option value="0" ' . selected( 0, $current_page_id, false ) . '>';
	esc_html_e( 'None', 'smile-basic-web' );
	echo '</option>';
	foreach ( $pages as $p ) {
		echo '<option value="' . esc_attr( $p->ID ) . '" ' . selected( $p->ID, $current_page_id, false ) . '>';
		echo esc_html( $p->post_title );
		echo '</option>';
	}
	echo '</select>';
	echo '<p class="description">';
	esc_html_e( 'Select the page for your Terms & Conditions.', 'smile-basic-web' );
	echo '</p>';
}

/**
 * Render “Terms & Conditions text” textarea.
 */
function smile_basic_web_render_terms_text_field() {
	$options = get_option( 'sbwscf_settings', array() );
	// translators: %s is the placeholder for the terms and conditions page link.
	$value = $options['terms_text'] ?? __( 'I agree to the %s', 'smile-basic-web' );
	?>
<textarea name="sbwscf_settings[terms_text]" rows="3"
	class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
<p class="description">
	<?php
		// translators: %s is the placeholder where the terms and conditions page link will appear in the checkbox text.
		esc_html_e( 'Text displayed next to the checkbox. Use %s where the terms and conditions page link should appear.', 'smile-basic-web' );
	?>
</p>
	<?php
}
/**
 * Renderiza el campo Marketing Opt-In Field.
 */
function smile_basic_web_render_marketing_opt_in_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<label>
	<input type="checkbox" name="sbwscf_settings[marketing_opt_in_enabled]" value="1"
		<?php checked( $options['marketing_opt_in_enabled'] ?? false, true ); ?>>
	<?php esc_html_e( 'Activate Marketing Opt-In field', 'smile-basic-web' ); ?>
</label>
<p class="description"><?php esc_html_e( 'Show a marketing opt-in checkbox below Legal Notice.', 'smile-basic-web' ); ?>
</p>
	<?php
}

/**
 * Renderiza el campo Marketing Opt-In Text.
 */
function smile_basic_web_render_marketing_text_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<textarea name="sbwscf_settings[marketing_text]" rows="4" cols="50"
	class="large-text"><?php echo esc_textarea( $options['marketing_text'] ?? '' ); ?></textarea>
<p class="description">
	<?php
	esc_html_e( 'Text displayed next to the marketing opt-in checkbox.', 'smile-basic-web' );
	?>
</p>
	<?php
}

/**
 * Renderiza el campo para el texto explicativo de los fines del formulario.
 */
function smile_basic_web_render_form_explanation_field() {
        $options = get_option( 'sbwscf_settings', array() );
        $content = isset( $options['form_explanation'] ) ? wp_kses_post( $options['form_explanation'] ) : '';

        wp_editor(
                $content,
                'sbwscf_form_explanation',
                array(
                        'textarea_name' => 'sbwscf_settings[form_explanation]',
                        'textarea_rows' => 8,
                        'teeny'         => true,
                        'media_buttons' => false,
                )
        );
        ?>
<p class="description"><?php esc_html_e( 'Use formatting to explain the purpose of the contact form.', 'smile-basic-web' ); ?></p>
        <?php
}

/**
 * Renderiza el campo Consent Instructions.
 */
function smile_basic_web_render_consent_instructions_field() {
        $options = get_option( 'sbwscf_settings', array() );
        ?>
<textarea name="sbwscf_settings[consent_instructions]" rows="4" cols="50"
        class="large-text"><?php echo esc_textarea( $options['consent_instructions'] ?? '' ); ?></textarea>
<p class="description"><?php esc_html_e( 'Text displayed before the privacy and legal consent checkboxes.', 'smile-basic-web' ); ?></p>
        <?php
}

/**
 * Renderiza el campo Email Footer Notice.
 */
function smile_basic_web_render_footer_notice_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<textarea name="sbwscf_settings[footer_notice]" rows="4" cols="50"
	class="large-text"><?php echo esc_textarea( $options['footer_notice'] ?? '' ); ?></textarea>
<p class="description">
	<?php esc_html_e( 'Enter the footer notice to be appended at the bottom of the email.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo Enable reCAPTCHA v3.
 */
function smile_basic_web_render_recaptcha_enabled_field() {
	$options = get_option( 'sbwscf_settings', array() );
	?>
<label>
	<input type="checkbox" name="sbwscf_settings[recaptcha_enabled]" value="1"
		<?php checked( $options['recaptcha_enabled'] ?? false, true ); ?>>
	<?php esc_html_e( 'Enable reCAPTCHA v3 on the form', 'smile-basic-web' ); ?>
</label>
	<?php
}

/**
 * Renderiza el campo reCAPTCHA Site Key.
 */
function smile_basic_web_render_recaptcha_site_key_field() {
	$options = get_option( 'sbwscf_settings', array() );
	$value   = isset( $options['recaptcha_site_key'] ) ? $options['recaptcha_site_key'] : '';
	?>
<input type="text" name="sbwscf_settings[recaptcha_site_key]" value="<?php echo esc_attr( $value ); ?>"
	class="regular-text">
<p class="description"><?php esc_html_e( 'Enter the reCAPTCHA v3 Site Key.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo reCAPTCHA Secret Key.
 */
function smile_basic_web_render_recaptcha_secret_key_field() {
	$options = get_option( 'sbwscf_settings', array() );
	$value   = isset( $options['recaptcha_secret_key'] ) ? $options['recaptcha_secret_key'] : '';
	?>
<input type="text" name="sbwscf_settings[recaptcha_secret_key]" value="<?php echo esc_attr( $value ); ?>"
	class="regular-text">
<p class="description"><?php esc_html_e( 'Enter the reCAPTCHA v3 Secret Key.', 'smile-basic-web' ); ?></p>
	<?php
}

/**
 * Renderiza el campo para gestionar campos personalizados.
 *
 * @return void
 */
function smile_basic_web_render_manage_custom_fields_field() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	/*
	* Always verify the Settings API nonce on POST, even if the user tried
	* to bypass the custom‑fields array. Uses the slug generated by
	* settings_fields( 'sbwscf_page_contact_form' ).
	*/
	if ( 'POST' === filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) ) { // Yoda.
		check_admin_referer( 'sbwscf_page_contact_form-options' );
	}

	$errors    = get_settings_errors( 'sbwscf_custom_fields' );
	$found_dup = false;

	if ( ! empty( $errors ) ) {
		foreach ( $errors as $err ) {
			if ( 'duplicate_name' === $err['code'] ) {
				$found_dup = true;
				break;
			}
		}
	}

	if ( true === $found_dup ) {
		// Recuperar el transient.
		$custom_fields = get_transient( 'sbwscf_temp_data' );
		if ( ! is_array( $custom_fields ) ) {
			$custom_fields = get_option( 'sbwscf_custom_fields', array() );
		}
	} else {
		$custom_fields = get_option( 'sbwscf_custom_fields', array() );
	}
	?>
<style>
	#sbwscf-custom-fields-container table.widefat th,
	#sbwscf-custom-fields-container table.widefat td {
		vertical-align: top;
	}
</style>

<div id="sbwscf-custom-fields-container">
	<table class="widefat fixed" cellspacing="0" style="table-layout:auto; width:auto;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Label', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Name', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Type', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Required', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Placeholder', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Options', 'smile-basic-web' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'smile-basic-web' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$index = 0;
			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) :
				foreach ( $custom_fields as $field ) :
					// Asegurar que sea array para evitar warnings.
					if ( ! is_array( $field ) ) {
						continue;
					}
					$label       = isset( $field['label'] ) ? esc_attr( $field['label'] ) : '';
					$type        = isset( $field['type'] ) ? esc_attr( $field['type'] ) : 'text';
					$name        = isset( $field['name'] ) ? esc_attr( $field['name'] ) : '';
					$required    = ! empty( $field['required'] );
					$placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
					$options     = isset( $field['options'] ) ? esc_attr( $field['options'] ) : '';
					?>
			<tr>
				<td>
					<button type="button" class="button button-secondary sbwscf-move-up"
						title="<?php esc_attr_e( 'Move Up', 'smile-basic-web' ); ?>">
						&#x25B2;
					</button>
					<button type="button" class="button button-secondary sbwscf-move-down"
						title="<?php esc_attr_e( 'Move Down', 'smile-basic-web' ); ?>">
						&#x25BC;
					</button>
				</td>
				<!-- 2. Label. -->
				<td>
					<input type="text" name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][label]"
						value="<?php echo esc_attr( $label ); ?>" class="regular-text" required>
				</td>
				<td>
					<?php if ( 'email usuario' === $type ) : ?>
					<input type="text" name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][name]"
						value="email" class="regular-text" readonly>
					<?php else : ?>
					<input type="text" name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][name]"
						value="<?php echo esc_attr( $name ); ?>" class="regular-text" required>
					<?php endif; ?>
				</td>
				<!-- 4. Type. -->
				<td>
					<select name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][type]" required>
						<option value="text" <?php selected( $type, 'text' ); ?>>
						<?php esc_html_e( 'Text', 'smile-basic-web' ); ?>
						</option>
						<option value="email" <?php selected( $type, 'email' ); ?>>
						<?php esc_html_e( 'Email', 'smile-basic-web' ); ?>
						</option>
						<option value="email usuario" <?php selected( $type, 'email usuario' ); ?>>
						<?php esc_html_e( 'User Email', 'smile-basic-web' ); ?>
						</option>
						<option value="textarea" <?php selected( $type, 'textarea' ); ?>>
						<?php esc_html_e( 'Textarea', 'smile-basic-web' ); ?>
						</option>
						<option value="number" <?php selected( $type, 'number' ); ?>>
						<?php esc_html_e( 'Number', 'smile-basic-web' ); ?>
						</option>
						<option value="url" <?php selected( $type, 'url' ); ?>>
						<?php esc_html_e( 'URL', 'smile-basic-web' ); ?>
						</option>
						<option value="tel" <?php selected( $type, 'tel' ); ?>>
						<?php esc_html_e( 'Telephone', 'smile-basic-web' ); ?>
						</option>
						<option value="select_single" <?php selected( $type, 'select_single' ); ?>>
						<?php esc_html_e( 'Select (Single)', 'smile-basic-web' ); ?>
						</option>
						<option value="select_multiple" <?php selected( $type, 'select_multiple' ); ?>>
						<?php esc_html_e( 'Select (Multiple)', 'smile-basic-web' ); ?>
						</option>
					</select>
				</td>
				<!-- 5. Required. -->
				<td>
					<input type="checkbox" name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][required]"
						value="1" <?php checked( $required, true ); ?>>
				</td>
				<!-- 6. Placeholder. -->
				<td>
					<input type="text" name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][placeholder]"
						value="<?php echo esc_attr( $placeholder ); ?>" class="regular-text">
				</td>
				<!-- 7. Options. -->
				<td>
					<input type="text" name="sbwscf_custom_fields[<?php echo esc_attr( $index ); ?>][options]"
						value="<?php echo esc_attr( $options ); ?>" class="regular-text">
					<p class="description" style="margin:0;">
						<?php esc_html_e( 'Enter options separated by a vertical bar (|).', 'smile-basic-web' ); ?>
					</p>
				</td>
				<!-- 8. Actions con botón Delete. -->
				<td>
					<button type="button" class="button button-secondary sbwscf-remove-field">
						<?php esc_html_e( 'Delete', 'smile-basic-web' ); ?>
					</button>
				</td>
			</tr>
					<?php
					++$index;
					endforeach;
			endif;
			?>
		</tbody>
	</table>
	<button type="button" class="button button-primary" id="sbwscf-add-field">
		<?php esc_html_e( 'Add Field', 'smile-basic-web' ); ?>
	</button>
</div>
	<?php
}
