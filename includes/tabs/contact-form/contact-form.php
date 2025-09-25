<?php
/**
 * Contact Form front-end rendering and submission handling.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SMiLE_Contact_Form' ) ) :

	/**
	 * Handles contact form rendering, AJAX submission, and email sending.
	 */
	class SMiLE_Contact_Form {
		/*
		* ------------------------------------------------------------------
		*  Static helpers
		* ------------------------------------------------------------------
		*/

		/**
		 * Replace every %s in a template with a given link.
		 * If no %s placeholders are present, the link is appended.
		 *
		 * @param string $template Text written by the admin (may include %s tokens).
		 * @param string $link     HTML `<a>` tag that must appear in the text.
		 * @return string Formatted text ready for output.
		 */
		protected static function format_text_with_link( string $template, string $link ): string {
			$placeholders = preg_match_all( '/(?<!%)%s/', $template, $dummy );

			// No placeholders → append link.
			if ( 0 === $placeholders ) {
				return trim( $template ) . ' ' . $link;
			}

			// Fill every placeholder with the same link (1 or more).
			$args = array_fill( 0, $placeholders, $link );

			return vsprintf( $template, $args );
		}

		/*
		* ------------------------------------------------------------------
		*  Bootstrap
		* ------------------------------------------------------------------
		*/

		/**
		 * Constructor: registers shortcode and AJAX handlers.
		 */
		public function __construct() {
			add_shortcode( 'smile_contact_form', array( $this, 'render_contact_form' ) );
			add_action( 'wp_ajax_submit_smile_contact_form', array( $this, 'handle_form_submission' ) );
			add_action( 'wp_ajax_nopriv_submit_smile_contact_form', array( $this, 'handle_form_submission' ) );
			add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ) );
		}

		/*
		* ------------------------------------------------------------------
		*  Shortcode renderer
		* ------------------------------------------------------------------
		*/

		/**
		 * Renders the contact form and enqueues necessary scripts.
		 *
		 * @return string HTML of the form.
		 */
		public function render_contact_form() {
			$settings = get_option( 'sbwscf_settings', array() );
			$base     = 'includes/tabs/contact-form/assets/js/';

			// Enqueue main form script.
			wp_enqueue_script(
				'sbwscf-form',
				plugins_url( $base . 'smile-contact-form.js', SMILE_BASIC_WEB_PLUGIN_FILE ),
				array( 'wp-i18n' ),
				SMILE_BASIC_WEB_VERSION,
				true
			);
			wp_set_script_translations(
				'sbwscf-form',
				'smile-basic-web',
				plugin_dir_path( SMILE_BASIC_WEB_PLUGIN_FILE ) . 'languages'
			);

			// Enqueue multi-select helper.
			wp_enqueue_script(
				'sbwscf-multi-select',
				plugins_url( $base . 'smile-contact-form-multi-select.js', SMILE_BASIC_WEB_PLUGIN_FILE ),
				array( 'wp-i18n' ),
				SMILE_BASIC_WEB_VERSION,
				true
			);
			wp_set_script_translations(
				'sbwscf-multi-select',
				'smile-basic-web',
				plugin_dir_path( SMILE_BASIC_WEB_PLUGIN_FILE ) . 'languages'
			);

			// Enqueue reCAPTCHA if enabled.
			if ( ! empty( $settings['recaptcha_enabled'] ) && ! empty( $settings['recaptcha_site_key'] ) ) {
				wp_enqueue_script(
					'sbwscf-recaptcha',
					'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $settings['recaptcha_site_key'] ),
					array(),
					'v3',
					false
				);
			}

			// Localize AJAX parameters and messages.
			wp_localize_script(
				'sbwscf-form',
				'sbwscfAjaxObject',
				array(
					'ajax_url'                => admin_url( 'admin-ajax.php' ),
					'nonce'                   => wp_create_nonce( 'sbwscf_form_submit' ),
					'error_message'           => esc_html__( 'An error occurred while processing the request. Please try again later.', 'smile-basic-web' ),
					'recaptcha_enabled'       => (bool) $settings['recaptcha_enabled'],
					'recaptcha_site_key'      => $settings['recaptcha_site_key'] ?? '',
					'recaptcha_execute_error' => esc_html__( 'Error verifying reCAPTCHA. Please try again later.', 'smile-basic-web' ),
					'recaptcha_unavailable'   => esc_html__( 'reCAPTCHA is not available. Please try again later.', 'smile-basic-web' ),
				)
			);

			/*
			* --------------------------------------------------------------
			*  Build dynamic links
			* --------------------------------------------------------------
			*/
			$policy_page_id = (int) ( $settings['privacy_policy_page'] ?? 0 );
			$legal_page_id  = (int) ( $settings['legal_notice_page'] ?? 0 );
			$terms_page_id  = (int) ( $settings['terms_page'] ?? 0 );

			$policy_link = ( 0 !== $policy_page_id )
				? '<a href="' . esc_url( get_permalink( $policy_page_id ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Privacy Policy', 'smile-basic-web' ) . '</a>'
				: '';
			$legal_link  = ( 0 !== $legal_page_id )
				? '<a href="' . esc_url( get_permalink( $legal_page_id ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Legal Notice', 'smile-basic-web' ) . '</a>'
				: '';
			$terms_link  = ( 0 !== $terms_page_id )
				? '<a href="' . esc_url( get_permalink( $terms_page_id ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Terms and Conditions', 'smile-basic-web' ) . '</a>'
				: '';

			ob_start();
			?>
<div class="sbwscf-contact-form">
	<form id="sbwscf-form" method="post" action="">
			<?php wp_nonce_field( 'sbwscf_form_submit', 'sbwscf_form_nonce' ); ?>

			<?php
			$custom_fields = get_option( 'sbwscf_custom_fields', array() );
			if ( ! empty( $custom_fields ) ) :
				foreach ( $custom_fields as $field ) :
					$label        = isset( $field['label'] ) ? esc_html( $field['label'] ) : '';
					$name         = isset( $field['name'] ) ? esc_attr( $field['name'] ) : '';
					$type         = isset( $field['type'] ) ? esc_attr( $field['type'] ) : 'text';
					$required     = ! empty( $field['required'] );
					$placeholder  = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
					$options_list = isset( $field['options'] ) ? array_map( 'trim', explode( '|', $field['options'] ) ) : array();
					$label_suffix = $required ? ' *' : '';
					?>
		<p>
			<label for="<?php echo esc_attr( 'sbwscf-' . $name ); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- concatenating suffix.
					echo $label . $label_suffix;
					?>
			</label>

					<?php if ( 'textarea' === $type ) : ?>
			<textarea id="<?php echo esc_attr( 'sbwscf-' . $name ); ?>"
				name="<?php echo esc_attr( 'sbwscf_' . $name ); ?>"
						<?php echo $required ? 'required="required"' : ''; ?>
				placeholder="<?php echo esc_attr( $placeholder ); ?>" maxlength="1000"></textarea>
			<span class="sbwscf-char-counter" data-max="1000" id="sbwscf-<?php echo esc_attr( $name ); ?>-counter">
				0 / 1000
			</span>
			</p>
			<?php elseif ( 'select_single' === $type ) : ?>
				<div class="sbwscf-select-single">
					<button type="button" id="<?php echo esc_attr( 'sbwscf-' . $name ); ?>" class="sbwscf-select-single-button"
						aria-haspopup="listbox" aria-expanded="false" data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
						<?php echo esc_html( $placeholder ); ?>
					</button>
					<div class="sbwscf-select-single-menu" style="display: none;">
						<?php foreach ( $options_list as $option ) : ?>
						<label class="sbwscf-select-single-option">
							<input type="radio" name="<?php echo esc_attr( 'sbwscf_' . $name ); ?>"
								value="<?php echo esc_attr( $option ); ?>" />
							<?php echo esc_html( $option ); ?>
						</label>
						<?php endforeach; ?>
					</div>
				</div>

				<?php elseif ( 'select_multiple' === $type ) : ?>
				<div class="sbwscf-multiselect-container">
					<button type="button" id="<?php echo esc_attr( 'sbwscf-' . $name ); ?>" class="sbwscf-multiselect-button"
						aria-expanded="false" data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
						<?php
							echo '' !== $placeholder
								? esc_html( $placeholder )
								: esc_html__( 'Select options', 'smile-basic-web' );
						?>
					</button>
					<div class="sbwscf-multiselect-menu" style="display: none;">
						<?php foreach ( $options_list as $option ) : ?>
						<label class="sbwscf-multiselect-option">
							<input type="checkbox" name="<?php echo esc_attr( 'sbwscf_' . $name ); ?>[]"
								value="<?php echo esc_attr( $option ); ?>" />
							<?php echo esc_html( $option ); ?>
						</label>
						<?php endforeach; ?>
					</div>
				</div>

			<?php else : ?>
				<?php
				$input_type = ( 'email usuario' === $type ) ? 'email' : $type;
				?>
			<input type="<?php echo esc_attr( $input_type ); ?>" id="<?php echo esc_attr( 'sbwscf-' . $name ); ?>"
				name="<?php echo esc_attr( 'sbwscf_' . $name ); ?>"
				<?php echo $required ? 'required="required"' : ''; ?>
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
										<?php
										if ( 'text' === $type ) {
											echo 'maxlength="250"';
										} elseif ( 'email' === $type || 'email usuario' === $type ) {
											echo 'maxlength="70"';
										} elseif ( 'number' === $type ) {
											echo 'maxlength="100"';
										} elseif ( 'tel' === $type ) {
											echo 'maxlength="30"';
										} elseif ( 'url' === $type ) {
											echo 'maxlength="250"';
										}
										?>
					>
			<?php endif; ?>

					<?php
				endforeach;
		endif;
			?>

			<?php if ( 0 !== $policy_page_id ) : ?>
		<p>
			<label>
				<input type="checkbox" name="sbwscf_privacy_check" value="1" required>
				<?php
				$policy_text = $settings['privacy_policy_text']
					/* translators: %s is the placeholder for the privacy policy page link. */
					?? __( 'I have read and accept the %s', 'smile-basic-web' );
				echo wp_kses_post(
					nl2br( self::format_text_with_link( $policy_text, $policy_link ) )
				);
				?>
			</label>
		</p>
		<?php endif; ?>

			<?php if ( 0 !== $legal_page_id ) : ?>
		<p>
			<label>
				<input type="checkbox" name="sbwscf_legal_check" value="1" required>
				<?php
				$legal_text = $settings['legal_notice_text']
					/* translators: %s is the placeholder for the legal notice page link. */
					?? __( 'I accept the %s', 'smile-basic-web' );
				echo wp_kses_post(
					nl2br( self::format_text_with_link( $legal_text, $legal_link ) )
				);
				?>
			</label>
		</p>
		<?php endif; ?>

			<?php if ( 0 !== $terms_page_id ) : ?>
		<p>
			<label>
				<input type="checkbox" name="sbwscf_terms_check" value="1" required>
				<?php
				$terms_text = $settings['terms_text']
					/* translators: %s is the placeholder for the terms and conditions page link. */
					?? __( 'I agree to the %s', 'smile-basic-web' );
				echo wp_kses_post(
					nl2br( self::format_text_with_link( $terms_text, $terms_link ) )
				);
				?>
			</label>
		</p>
		<?php endif; ?>

			<?php if ( ! empty( $settings['marketing_opt_in_enabled'] ) ) : ?>
		<p>
			<label>
				<input type="checkbox" name="sbwscf_marketing_opt_in" value="1">
				<?php echo wp_kses_post( nl2br( esc_html( $settings['marketing_text'] ) ) ); ?>
			</label>
		</p>
		<?php endif; ?>

			<?php if ( ! empty( $settings['form_explanation'] ) ) : ?>
		<p class="sbwscf-form-explanation">
			<label>
				<?php echo wp_kses_post( nl2br( $settings['form_explanation'] ) ); ?>
			</label>
		</p>
		<?php endif; ?>

		<div id="sbwscf-form-messages"></div>

		<div class="sbwscf-submit-wrapper">
			<button type="submit"><?php esc_html_e( 'Submit', 'smile-basic-web' ); ?></button>
		</div>
	</form>
</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Handles AJAX form submission, validation, and email sending.
		 *
		 * @return void
		 */
		public function handle_form_submission() {
			$nonce = isset( $_POST['sbwscf_form_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['sbwscf_form_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'sbwscf_form_submit' ) ) {
				wp_send_json_error( esc_html__( 'Form security not verified.', 'smile-basic-web' ) );
				wp_die();
			}

			$settings      = get_option( 'sbwscf_settings', array() );
			$custom_fields = get_option( 'sbwscf_custom_fields', array() );
			$data          = array();
			$errors        = array();

			// reCAPTCHA v3.
			if ( ! empty( $settings['recaptcha_enabled'] ) && ! empty( $settings['recaptcha_secret_key'] ) ) {
				$recaptcha_token = isset( $_POST['g-recaptcha-response'] )
					? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) )
					: '';

				if ( empty( $recaptcha_token ) ) {
					$errors[] = esc_html__( 'reCAPTCHA token not received.', 'smile-basic-web' );
				} else {
					$secret    = $settings['recaptcha_secret_key'];
					$remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
						? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
						: '';
					$response  = wp_remote_post(
						'https://www.google.com/recaptcha/api/siteverify',
						array(
							'body' => array(
								'secret'   => $secret,
								'response' => $recaptcha_token,
								'remoteip' => $remote_ip,
							),
						)
					);

					if ( is_wp_error( $response ) ) {
						$errors[] = esc_html__( 'Error verifying reCAPTCHA. Please try again later.', 'smile-basic-web' );
					} else {
						$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
						if ( empty( $decoded['success'] ) ) {
							$errors[] = esc_html__( 'Could not verify reCAPTCHA. Please try again.', 'smile-basic-web' );
						} elseif ( isset( $decoded['score'] ) && (float) $decoded['score'] < 0.5 ) {
							$errors[] = esc_html__( 'Suspicious activity detected (low score). reCAPTCHA failed.', 'smile-basic-web' );
						}
					}
				}
			}

			// Privacy.
			if ( $policy_page_id && empty( $_POST['sbwscf_privacy_check'] ) ) {
				$errors[] = esc_html__( 'You must accept the privacy policy.', 'smile-basic-web' );
			}

			// Legal.
			if ( $legal_page_id && empty( $_POST['sbwscf_legal_check'] ) ) {
				$errors[] = esc_html__( 'You must accept the legal notice.', 'smile-basic-web' );
			}

			// Terms & Conditions.
			$terms_page_id = (int) ( $settings['terms_page'] ?? 0 );
			if ( $terms_page_id && empty( $_POST['sbwscf_terms_check'] ) ) {
				$errors[] = esc_html__( 'You must accept the terms and conditions.', 'smile-basic-web' );
			}

			// Custom fields validation.
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $field ) {
					$label    = isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';
					$name     = isset( $field['name'] ) ? $field['name'] : '';
					$type     = isset( $field['type'] ) ? $field['type'] : 'text';
					$required = ! empty( $field['required'] );
					$form_key = 'sbwscf_' . $name;

					if ( 'select_multiple' === $type ) {
						if ( true === $required && empty( $_POST[ $form_key ] ) ) {
							/* translators: %s: placeholder for the human-readable label of the multiple selection field. */
							$errors[] = sprintf( esc_html__( 'You must select at least one option in the field %s.', 'smile-basic-web' ), $label );
							continue;
						}
						if ( ! empty( $_POST[ $form_key ] ) ) {
							$data[ $form_key ] = array_map(
								'sanitize_text_field',
								wp_unslash( (array) $_POST[ $form_key ] )
							);
						}
						continue;
					}

					if ( 'select_single' === $type ) {
						if ( true === $required && empty( $_POST[ $form_key ] ) ) {
							/* translators: %s: the label of the single selection field that is required. */
							$errors[] = sprintf( esc_html__( 'You must select an option in the field %s.', 'smile-basic-web' ), $label );
							continue;
						}
						if ( ! empty( $_POST[ $form_key ] ) ) {
							$data[ $form_key ] = sanitize_text_field( wp_unslash( $_POST[ $form_key ] ) );
						}
						continue;
					}

					if ( 'textarea' === $type ) {
						if ( true === $required && empty( $_POST[ $form_key ] ) ) {
							/* translators: %s is the label of the textarea field that is required.*/
							$errors[] = sprintf( esc_html__( 'The textarea field %s is required.', 'smile-basic-web' ), $label );
							continue;
						}
						if ( isset( $_POST[ $form_key ] ) && '' !== $_POST[ $form_key ] ) {
							$data[ $form_key ] = sanitize_textarea_field( wp_unslash( $_POST[ $form_key ] ) );
						}
						continue;
					}

					// CAMPOS GENERALES.
					if ( true === $required && empty( $_POST[ $form_key ] ) ) {
						/* translators: %s is a placeholder for the field label. For instance, if the field is "Email", the placeholder will be replaced with "Email". */
						$errors[] = sprintf( esc_html__( 'The field %s is required.', 'smile-basic-web' ), $label );
						continue;
					}

					if ( isset( $_POST[ $form_key ] ) && ! empty( $_POST[ $form_key ] ) ) {
						$value             = sanitize_text_field( wp_unslash( $_POST[ $form_key ] ) );
						$data[ $form_key ] = $value;

						if ( 'email' === $type || 'email usuario' === $type ) {
							if ( ! is_email( $value ) ) {
								/* translators: %s is the field label. */
								$errors[] = sprintf( esc_html__( 'The field %s must be a valid email address.', 'smile-basic-web' ), $label );
							}
						} elseif ( 'url' === $type && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
							/* translators: %s is replaced with the field label that must be a valid URL. */
							$errors[] = sprintf( esc_html__( 'The field %s must be a valid URL.', 'smile-basic-web' ), $label );
						}
					}
				}
			}

			if ( ! empty( $errors ) ) {
				wp_send_json_error( implode( '<br>', $errors ) );
				wp_die();
			}

			// Map field keys to labels.
			$field_labels = array();
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $field ) {
					if ( ! empty( $field['name'] ) && ! empty( $field['label'] ) ) {
						$field_labels[ 'sbwscf_' . $field['name'] ] = $field['label'];
					}
				}
			}
			// Add marketing label.
			if ( ! empty( $settings['marketing_opt_in_enabled'] ) ) {
				$field_labels['sbwscf_marketing_opt_in'] = $settings['marketing_text'];
			}

			// Add marketing value.
			if ( ! empty( $settings['marketing_opt_in_enabled'] ) ) {
				$marketing                       = ! empty( $_POST['sbwscf_marketing_opt_in'] )
					? esc_html__( 'Yes', 'smile-basic-web' )
					: esc_html__( 'No', 'smile-basic-web' );
				$data['sbwscf_marketing_opt_in'] = $marketing;
			}

			// Build email.
			$to      = ! empty( $settings['from_email'] ) ? $settings['from_email'] : get_option( 'admin_email' );
			$subject = esc_html__( 'New contact message', 'smile-basic-web' );
			$message = '';
			foreach ( $data as $key => $value ) {
				$label = $field_labels[ $key ] ?? ucfirst( str_replace( 'sbwscf_', '', $key ) );
				if ( is_array( $value ) ) {
					$message .= '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( implode( ', ', $value ) ) . '</p>';
				} else {
					$message .= '<p><strong>' . esc_html( $label ) . ':</strong> ' . nl2br( esc_html( $value ) ) . '</p>';
				}
			}

			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			if ( isset( $data['sbwscf_email'] ) && is_email( $data['sbwscf_email'] ) ) {
				$headers[] = 'Reply-To: ' . $data['sbwscf_email'];
			}

			$sent = wp_mail( $to, $subject, $message, $headers );

			if ( $sent ) {
				// Send copy to user if enabled.
				if (
					! empty( $settings['send_copy_to_user'] ) &&
				isset( $data['sbwscf_email'] ) &&
					is_email( $data['sbwscf_email'] )
					) {
					$this->send_copy_to_user( $settings, $message, $data['sbwscf_email'] );
				}
				wp_send_json_success( esc_html__( 'Message sent successfully!', 'smile-basic-web' ) );
			} else {
				wp_send_json_error( esc_html__( 'There was an error sending the message. Please try again later.', 'smile-basic-web' ) );
			}

			wp_die();
		}

			/**
			 * Sends a styled copy of the message to the user.
			 *
			 * @param array  $settings The plugin settings.
			 * @param string $message  The HTML message body.
			 * @param string $to       Recipient email address (already sanitized upstream).
			 * @return void
			 */
		protected function send_copy_to_user( $settings, $message, $to ) {
			// Sanitize and validate the passed email.
			$to = sanitize_email( $to );

			// Abort if no valid email address.
			if ( ! is_email( $to ) ) {
				return;
			}
			// Retrieve style customizations from Customizer.
			$email_copy_message_size    = get_theme_mod( 'sbwscf_email_copy_message_size', '1.4rem' );
			$email_copy_message_color   = get_theme_mod( 'sbwscf_email_copy_message_color', '#333333' );
			$email_usertext_line_height = get_theme_mod( 'sbwscf_email_usertext_line_height', '2' );
			$email_notice_line_height   = get_theme_mod( 'sbwscf_email_notice_line_height', '1.2' );

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

			$copy_subject = esc_html__( 'Copy of your contact message', 'smile-basic-web' );

			// Start wrapper.
			$copy_message = '<div style="max-width:800px; margin:0 auto; background-color:whitesmoke;">';

			// Logo header.
			$copy_message .= '<div style="text-align:center; font-size:larger; background:'
			. esc_attr( $email_logo_bg ) . '; padding:' . esc_attr( $email_logo_padding ) . ';">';
			if ( ! empty( $settings['logo_id'] ) ) {
				$logo_id     = absint( $settings['logo_id'] );
				$company_url = isset( $settings['company_url'] ) ? esc_url( $settings['company_url'] ) : '';
				$logo_html   = wp_get_attachment_image(
					$logo_id,
					'medium',
					false,
					array( 'style' => 'max-width:100%; height:auto;' )
				);
				if ( $logo_html && $company_url ) {
					$copy_message .= '<a href="' . esc_url( $company_url ) . '" target="_blank" rel="noopener noreferrer">'
						. $logo_html . '</a>';
				} elseif ( $logo_html ) {
					$copy_message .= $logo_html;
				}
			}
			$copy_message .= '</div>';

			// Main message block.
			$copy_message .= '<div style="font-family:Arial,sans-serif; color:' . esc_attr( $email_usertext_color )
			. '; background:' . esc_attr( $email_msg_bg ) . '; padding:' . esc_attr( $email_msg_padding ) . ';">';

			// Introductory text.
			$copy_message .= '<p style="text-align:center; margin-top:0; margin-bottom:1.5rem; line-height:'
			. esc_attr( $email_usertext_line_height ) . '; font-size:' . esc_attr( $email_copy_message_size )
			. '; color:' . esc_attr( $email_copy_message_color ) . ';">'
			. nl2br( esc_html( $settings['copy_message'] ) ) . '</p>';

			$copy_message .= '<div style="text-align:left;">';

			// Style the original message.
			$styled_message = str_replace(
				'<strong>',
				'<strong style="margin:0px; font-size:' . esc_attr( $email_label_size ) . '; color:'
				. esc_attr( $email_label_color ) . ';">',
				$message
			);
			$styled_message = str_replace(
				'<p>',
				'<p style="margin:0px; font-size:' . esc_attr( $email_usertext_size ) . '; color:'
				. esc_attr( $email_usertext_color ) . '; line-height:' . esc_attr( $email_usertext_line_height ) . ';">',
				$styled_message
			);
			$copy_message  .= $styled_message;

			// Close main block.
			$copy_message .= '</div></div>';

			// Footer notice.
			if ( ! empty( $settings['footer_notice'] ) ) {
				$copy_message .= '<div style="text-align:center; font-size:' . esc_attr( $email_notice_size )
					. '; color:' . esc_attr( $email_notice_color ) . '; background:' . esc_attr( $email_notice_bg )
					. '; padding:' . esc_attr( $email_notice_padding ) . '; line-height:' . esc_attr( $email_notice_line_height )
					. ';">' . esc_html( $settings['footer_notice'] ) . '</div>';
			}

			// Attribution.
			$mensaje_html  = '<hr style="border:0; height:1px; background-color:#ccc; margin:0px">';
			$mensaje_html .= '<p>' . esc_html__( 'Anti-spam form customized by', 'smile-basic-web' )
			. ' <a href="https://smilecomunicacion.com/">SMiLE comunicación</a>.</p><br>';
			$copy_message .= '<div style="text-align:center; font-size:x-small; color:#999; background-color:#fff;">'
			. wp_kses_post( $mensaje_html ) . '</div>';

			// Close wrapper.
			$copy_message .= '</div>';

			$copy_headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'Reply-To: ' . sanitize_email( $settings['from_email'] ),
			);

			wp_mail( $to, $copy_subject, $copy_message, $copy_headers );
		}


		/**
		 * Configures PHPMailer with SMTP settings.
		 *
		 * @param PHPMailer $phpmailer PHPMailer instance.
		 * @return void
		 */
		public function configure_phpmailer( $phpmailer ) {
			$settings = get_option( 'sbwscf_settings', array() );
			if ( ! empty( $settings['host'] ) && ! empty( $settings['username'] ) && ! empty( $settings['password'] ) ) {
				$phpmailer->isSMTP();
				// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
				$phpmailer->Host       = $settings['host'];
				$phpmailer->SMTPAuth   = true;
				$phpmailer->Port       = $settings['port'];
				$phpmailer->Username   = $settings['username'];
				$phpmailer->Password   = $settings['password'];
				$phpmailer->SMTPSecure = $settings['encryption'];
				$phpmailer->From       = $settings['from_email'];
				$phpmailer->FromName   = $settings['from_name'];
				// phpcs:enable
			}
		}
	}

	new SMiLE_Contact_Form();

endif;
