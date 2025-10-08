<?php
/**
 * File: class-sbwscf-contactform-page.php
 *
 * This file is part of the Smile Basic Web plugin and handles the contact form page functionality.
 *
 * @package Smile_Basic_Web
 */

if ( ! function_exists( 'sbwscf_contactform_ensure_email_preview_page' ) ) {
	require_once __DIR__ . '/email-preview-setup.php';
}

/**
 * Contact-Form admin tab.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/contact-form/contact-form.php';

// shortcode + customizer helpers.
require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/contact-form/class-sbwscf-email-preview.php';

// Contact Form module.
$cf_dir = SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/contact-form/';
require_once $cf_dir . 'contact-form-settings.php';
require_once $cf_dir . 'customizer.php';
require_once $cf_dir . 'customizer-email-setup.php';



/**
 * Adds the “Contact Form” settings page as a modular tab.
 *
 * @package smile-basic-web
 */
final class SBWSCF_ContactForm_Page implements SBWSCF_Tab_Interface {

	/**
	 * Tab slug used in the URL (`?tab=`).
	 *
	 * @return string Slug.
	 */
	public static function get_slug(): string {
		return 'contact_form';
	}

	/**
	 * Text shown in the tab navigation.
	 *
	 * @return string Label.
	 */
	public static function get_label(): string {
		return esc_html__( 'Contact Form', 'smile-basic-web' );
	}

	/**
	 * Unique settings page slug for WP Settings API.
	 *
	 * @return string
	 */
	private static function page_slug(): string {
		return 'sbwscf_page_contact_form';
	}

	/**
	 * Activation callback: add default options for Contact Form.
	 *
	 * @return void
	 */
	public static function activate(): void {

		// Default SMTP and form settings.
		$default_settings = array(
			'host'                     => '',
			'port'                     => 587,
			'username'                 => '',
			'password'                 => '',
			'encryption'               => 'tls',
			'from_email'               => '',
			'from_name'                => get_bloginfo( 'name' ),
			'send_copy_to_user'        => false,
			'copy_message'             => __( 'Thank you for contacting us. Here is a copy of your message:', 'smile-basic-web' ),
			'logo_id'                  => 0,
			'company_url'              => '',
			'privacy_policy_page'      => 0,
			// translators: %s is the placeholder for the privacy policy page link.
			'privacy_policy_text'      => __( 'I have read and accept the %s', 'smile-basic-web' ),
			'legal_notice_page'        => 0,
			// translators: %s is the placeholder for the legal notice page link.
			'legal_notice_text'        => __( 'I accept the %s', 'smile-basic-web' ),
			'terms_page'               => 0,
			// translators: %s is the placeholder for the terms and conditions page link.
			'terms_text'               => __( 'I agree to the %s', 'smile-basic-web' ),
			'marketing_opt_in_enabled' => false,
			'marketing_text'           => '',
			'form_explanation'         => '',
			'consent_instructions'     => '',
			'footer_notice'            => __( 'Notice: The content of this email and attachments is strictly confidential. If you are not the intended recipient and have received this message in error, please notify the sender immediately without disseminating, storing, or copying its content.', 'smile-basic-web' ),
			'recaptcha_enabled'        => false,
			'recaptcha_site_key'       => '',
			'recaptcha_secret_key'     => '',
		);

		if ( false === get_option( 'sbwscf_settings' ) ) {
			add_option( 'sbwscf_settings', $default_settings );
		}

		// Default custom fields.
		$default_fields = array(
			array(
				'label'       => __( 'Name', 'smile-basic-web' ),
				'name'        => 'name',
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'Enter your name', 'smile-basic-web' ),
			),
			array(
				'label'       => __( 'Email', 'smile-basic-web' ),
				'name'        => 'email',
				'type'        => 'email usuario',
				'required'    => true,
				'placeholder' => __( 'Enter your email address', 'smile-basic-web' ),
			),
			array(
				'label'       => __( 'Message', 'smile-basic-web' ),
				'name'        => 'message',
				'type'        => 'textarea',
				'required'    => true,
				'placeholder' => __( 'Type your message here', 'smile-basic-web' ),
			),
		);

		if ( false === get_option( 'sbwscf_custom_fields' ) ) {
			add_option( 'sbwscf_custom_fields', $default_fields );
		}
	}

	/*
	-------------------------------------------------------------------------
	 * Settings registration
	 * -------------------------------------------------------------------------
	 */

	/**
	 * Registers all settings, sections and fields for this tab.
	 *
	 * @return void
	 */
	public static function register_settings(): void {

		$group = self::page_slug();             // Group slug for Settings API.
		$page  = self::page_slug();            // Page unique to this tab.

		register_setting( $group, 'sbwscf_settings', SBWSCF_SETTINGS_SANITIZE_ARGS );
		register_setting( $group, 'sbwscf_custom_fields', SBWSCF_CUSTOM_FIELDS_SANITIZE_ARGS );

		/* ----------  Sections ---------- */
		add_settings_section(
			'sbwscf_smtp_section',
			esc_html__( 'SMTP Configuration', 'smile-basic-web' ),
			'__return_false',
			$page
		);

		add_settings_section(
			'sbwscf_custom_fields_section',
			esc_html__( 'Custom Fields', 'smile-basic-web' ),
			'__return_false',
			$page
		);

		/* ----------  Fields ---------- */
		$smtp_fields = array(
			'host'                     => 'smile_basic_web_render_smtp_host_field',
			'port'                     => 'smile_basic_web_render_smtp_port_field',
			'username'                 => 'smile_basic_web_render_smtp_username_field',
			'password'                 => 'smile_basic_web_render_smtp_password_field',
			'encryption'               => 'smile_basic_web_render_smtp_encryption_field',
			'from_email'               => 'smile_basic_web_render_smtp_from_email_field',
			'from_name'                => 'smile_basic_web_render_smtp_from_name_field',
			'send_copy_to_user'        => 'smile_basic_web_render_send_copy_field',
			'copy_message'             => 'smile_basic_web_render_copy_message_field',
			'logo_id'                  => 'smile_basic_web_render_logo_field',
			'company_url'              => 'smile_basic_web_render_company_url_field',
			'consent_instructions'     => 'smile_basic_web_render_consent_instructions_field',
			'privacy_policy_page'      => 'smile_basic_web_render_privacy_policy_page_field',
			'privacy_policy_text'      => 'smile_basic_web_render_privacy_policy_text_field',
			'legal_notice_page'        => 'smile_basic_web_render_legal_notice_page_field',
			'legal_notice_text'        => 'smile_basic_web_render_legal_notice_text_field',
			'terms_page'               => 'smile_basic_web_render_terms_page_field',
			'terms_text'               => 'smile_basic_web_render_terms_text_field',
			'marketing_opt_in_enabled' => 'smile_basic_web_render_marketing_opt_in_field',
			'marketing_text'           => 'smile_basic_web_render_marketing_text_field',
			'form_explanation'         => 'smile_basic_web_render_form_explanation_field',
			'footer_notice'            => 'smile_basic_web_render_footer_notice_field',
			'recaptcha_enabled'        => 'smile_basic_web_render_recaptcha_enabled_field',
			'recaptcha_site_key'       => 'smile_basic_web_render_recaptcha_site_key_field',
			'recaptcha_secret_key'     => 'smile_basic_web_render_recaptcha_secret_key_field',
		);

		/**
		 * Map option → label (translatable).
		 */
		$field_labels = array(
			'host'                     => esc_html__( 'Host', 'smile-basic-web' ),
			'port'                     => esc_html__( 'Port', 'smile-basic-web' ),
			'username'                 => esc_html__( 'Username', 'smile-basic-web' ),
			'password'                 => esc_html__( 'Password', 'smile-basic-web' ),
			'encryption'               => esc_html__( 'Encryption', 'smile-basic-web' ),
			'from_email'               => esc_html__( 'From Email', 'smile-basic-web' ),
			'from_name'                => esc_html__( 'From Name', 'smile-basic-web' ),
			'send_copy_to_user'        => esc_html__( 'Send Copy To User', 'smile-basic-web' ),
			'copy_message'             => esc_html__( 'Copy Message', 'smile-basic-web' ),
			'logo_id'                  => esc_html__( 'Logo Id', 'smile-basic-web' ),
			'company_url'              => esc_html__( 'Company URL', 'smile-basic-web' ),
			'consent_instructions'     => esc_html__( 'Consent Instructions', 'smile-basic-web' ),
			'privacy_policy_page'      => esc_html__( 'Privacy Policy Page', 'smile-basic-web' ),
			'privacy_policy_text'      => esc_html__( 'Privacy Policy Text', 'smile-basic-web' ),
			'legal_notice_page'        => esc_html__( 'Legal Notice Page', 'smile-basic-web' ),
			'legal_notice_text'        => esc_html__( 'Legal Notice Text', 'smile-basic-web' ),
			'terms_page'               => esc_html__( 'Terms & Conditions Page', 'smile-basic-web' ),
			'terms_text'               => esc_html__( 'Terms & Conditions Text', 'smile-basic-web' ),
			'marketing_opt_in_enabled' => esc_html__( 'Enable Marketing Opt-in', 'smile-basic-web' ),
			'marketing_text'           => esc_html__( 'Marketing Opt-in Text', 'smile-basic-web' ),
			'form_explanation'         => esc_html__( 'Form Explanation', 'smile-basic-web' ),
			'footer_notice'            => esc_html__( 'Footer Notice', 'smile-basic-web' ),
			'recaptcha_enabled'        => esc_html__( 'Enable reCAPTCHA v3', 'smile-basic-web' ),
			'recaptcha_site_key'       => esc_html__( 'reCAPTCHA Site Key', 'smile-basic-web' ),
			'recaptcha_secret_key'     => esc_html__( 'reCAPTCHA Secret Key', 'smile-basic-web' ),
		);

		foreach ( $smtp_fields as $option => $callback ) {
			add_settings_field(
				"sbwscf_$option",
				$field_labels[ $option ],
				$callback,
				$page,
				'sbwscf_smtp_section'
			);
		}

		/* ----------  Manage custom-fields button ---------- */
		add_settings_field(
			'sbwscf_manage_custom_fields',            // Field ID.
			esc_html__( 'Manage Custom Fields', 'smile-basic-web' ),
			'smile_basic_web_render_manage_custom_fields_field',
			$page,
			'sbwscf_custom_fields_section'
		);
	}

	/*
	-------------------------------------------------------------------------
	 * Assets
	 * -------------------------------------------------------------------------
	 */

	/**
	 * Enqueues scripts and styles only when esta pestaña está activa.
	 *
	 * @param string $hook_suffix Current admin-page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook_suffix ): void {

		// Only run on our top-level plugin page.
		if ( false === strpos( $hook_suffix, 'smile_basic_web' ) ) {
			return;
		}

		$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		/*
		 * If no ?tab= param, default to contact_form when coming from main menu.
		 */
		if ( empty( $current_tab ) ) {
			$current_tab = 'contact_form';
		}

		if ( 'contact_form' !== $current_tab ) {
			return;
		}

		/*
		-------------------------------------------------
		*  3.  JavaScript
		* -------------------------------------------------
		*/
		wp_enqueue_media();

		$base_js  = 'includes/tabs/contact-form/assets/js/';
		$js_files = array(
			'sbwscf-cf-admin'        => 'smile-contact-form-admin.js',
			'sbwscf-cf-admin-fields' => 'smile-contact-form-admin-fields.js',
			'sbwscf-cf-multi-select' => 'smile-contact-form-multi-select.js',
		);
		foreach ( $js_files as $handle => $file ) {
			wp_enqueue_script(
				$handle,
				plugins_url( $base_js . $file, SMILE_BASIC_WEB_PLUGIN_FILE ),
				array( 'wp-i18n' ), // i18n dependency.
				SMILE_BASIC_WEB_VERSION,
				true
			);
			// Attach translations for each script.
			wp_set_script_translations( $handle, 'smile-basic-web', plugin_dir_path( __DIR__ ) . '../../../languages' );
		}

		wp_localize_script(
			'sbwscf-cf-admin-fields',
			'sbwscfAdminFields',
			array(
				'type_text'           => esc_html__( 'Text', 'smile-basic-web' ),
				'type_email'          => esc_html__( 'Email', 'smile-basic-web' ),
				'type_textarea'       => esc_html__( 'Textarea', 'smile-basic-web' ),
				'type_number'         => esc_html__( 'Number', 'smile-basic-web' ),
				'type_url'            => esc_html__( 'URL', 'smile-basic-web' ),
				'type_tel'            => esc_html__( 'Telephone', 'smile-basic-web' ),
				'remove_button_text'  => esc_html__( 'Delete', 'smile-basic-web' ),
				'move_up_title'       => esc_attr__( 'Move Up', 'smile-basic-web' ),
				'move_down_title'     => esc_attr__( 'Move Down', 'smile-basic-web' ),
				'options_description' => esc_html__( 'Enter your options separated by the vertical bar (|).', 'smile-basic-web' ),
			)
		);

		wp_enqueue_style(
			'sbwscf-cf-admin-style',
			esc_url( plugins_url( 'assets/css/admin-style.css', __FILE__ ) ),
			array(),
			SMILE_BASIC_WEB_VERSION
		);
	}

	/*
	-------------------------------------------------------------------------
	 * Render
	 * -------------------------------------------------------------------------
	 */

	/**
	 * Prints the whole UI for this tab.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Contact Form Settings', 'smile-basic-web' ); ?></h2>
			<?php settings_errors(); ?>
			<p><?php esc_html_e( 'Use the shortcode [smile_contact_form] to embed the form.', 'smile-basic-web' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
				settings_fields( self::page_slug() );
				do_settings_sections( self::page_slug() );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register Customizer settings & controls for this tab.
	 *
	 * Includes both the form‐appearance panel (customizer.php)
	 * and the email‐preview panel (customizer-email-setup.php).
	 *
	 * @param WP_Customize_Manager $wp_customize Customize Manager instance.
	 * @return void
	 */
	public static function register_customizer( WP_Customize_Manager $wp_customize ): void {
		// 1. Include form‐appearance definitions.
		require_once SMILE_BASIC_WEB_PLUGIN_PATH
			. 'includes/tabs/contact-form/customizer.php';

		// 2. Include email‐preview appearance definitions.
		require_once SMILE_BASIC_WEB_PLUGIN_PATH
			. 'includes/tabs/contact-form/customizer-email-setup.php';

		// 3. Register form appearance.
		if ( function_exists( 'sbwscf_contactform_customize_register' ) ) {
			sbwscf_contactform_customize_register( $wp_customize );
		}

		// 4. Register email appearance.
		if ( function_exists( 'sbwscf_contactform_customize_email_register' ) ) {
			sbwscf_contactform_customize_email_register( $wp_customize );
		}
	}

	/**
	 * Register update routines for Contact Form module.
	 *
	 * @since 1.2.1
	 */
	public static function register_update_hooks(): void {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_run_updates' ) );
	}

	/**
	 * Runs one–time update tasks when plugin version changes.
	 *
	 * @since 1.2.1
	 * @return void
	 */
	public static function maybe_run_updates(): void {
		$old = get_option( 'sbwscf_plugin_version', '0' );
		if ( version_compare( $old, SMILE_BASIC_WEB_VERSION, '<' ) ) {
			sbwscf_contactform_ensure_email_preview_page();
			update_option( 'sbwscf_plugin_version', SMILE_BASIC_WEB_VERSION );
		}
	}
}

// Hook de actualización para Contact Form.
SBWSCF_ContactForm_Page::register_update_hooks();

// Settings & assets.
add_action( 'admin_init', array( 'SBWSCF_ContactForm_Page', 'register_settings' ) );
add_action( 'admin_enqueue_scripts', array( 'SBWSCF_ContactForm_Page', 'enqueue_assets' ) );

// Admin renderer (Tab Manager calls this action).
add_action( 'smile_basic_web_render_tab_contact_form', array( 'SBWSCF_ContactForm_Page', 'render' ) );

// This hook makes sure the two appearance panels are registered.
add_action( 'customize_register', array( 'SBWSCF_ContactForm_Page', 'register_customizer' ) );
