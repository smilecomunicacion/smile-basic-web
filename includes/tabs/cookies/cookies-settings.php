<?php
/**
 * Settings for the Cookies tab.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
* ------------------------------------------------------------------
* Seeder: Default Cookie Color Options
* ------------------------------------------------------------------
*/
/**
 * Seed default cookie color options on admin_init.
 *
 * @return void
 */
function sbwscf_seed_default_cookie_colors(): void {
	$defaults = array(
		'background'        => '#ffffff',
		'titles'            => '#000000',
		'text'              => '#000000',
		'accept'            => '#4caf50',
		'accept_text'       => '#ffffff',
		'reject'            => '#f44336',
		'reject_text'       => '#ffffff',
		'preferences'       => '#e0e0e0',
		'preferences_text'  => '#000000',
		'link'              => '#0000ee',
		'manage_background' => '#ffffff',
		'manage_text'       => '#000000',
	);

	foreach ( $defaults as $key => $color ) {
		$option_name = 'sbwscf_cookie_color_' . $key;
		// Yoda condition para comprobar si existe.
		if ( false === get_option( $option_name ) ) {
			add_option( $option_name, $color );
		}
	}
}
add_action( 'admin_init', 'sbwscf_seed_default_cookie_colors' );

/**
 * Register settings, sections, and fields for the Cookies tab.
 *
 * @return void
 */
function sbwscf_cookies_register_settings(): void {
	/*
	------------------------------------------------------------------
	 * Section 1: Enable Cookies Notice
	 * ------------------------------------------------------------------
	 */
	register_setting(
		'sbwscf_cookies',
		'sbwscf_enable_cookies_notice',
		array(
			'sanitize_callback' => 'sbwscf_sanitize_checkbox',
		)
	);

	add_settings_section(
		'sbwscf_cookies_enable_section',
		esc_html__( 'Enable Cookies Notice', 'smile-basic-web' ),
		'sbwscf_cookies_enable_section_cb',
		'sbwscf_cookies'
	);

	add_settings_field(
		'sbwscf_enable_cookies_notice',
		esc_html__( 'Show cookies notice', 'smile-basic-web' ),
		'sbwscf_enable_cookies_notice_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_enable_section'
	);

	/*
	------------------------------------------------------------------
	 * Section 2: Cookie Panel Settings
	 * ------------------------------------------------------------------
	 */
	$settings_to_register = array(
		'sbwscf_cookie_panel_title'             => 'sbwscf_sanitize_cookie_title',
		'sbwscf_cookie_message'                 => 'sbwscf_sanitize_cookie_message',
		'sbwscf_cookie_functional_title'        => 'sbwscf_sanitize_cookie_inline_html',
		'sbwscf_cookie_functional_description'  => 'sbwscf_sanitize_cookie_message',
		'sbwscf_cookie_label_accept_initial'    => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_label_deny_initial'      => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_label_accept_all'        => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_label_deny_all'          => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_label_preferences'       => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_label_accept_prefs'      => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_label_manage'            => 'sbwscf_sanitize_cookie_label',
		'sbwscf_cookie_panel_size'              => 'sanitize_text_field',
		'sbwscf_cookie_minimized_position'      => 'sanitize_text_field',
		'sbwscf_show_manage_minified_label'     => 'sbwscf_sanitize_checkbox',
		'sbwscf_cookie_policy_page'             => 'absint',
		'sbwscf_privacy_policy_page'            => 'absint',
		'sbwscf_legal_notice_page'              => 'absint',
		'sbwscf_cookie_color_background'        => 'sanitize_hex_color',
		'sbwscf_cookie_color_titles'            => 'sanitize_hex_color',
		'sbwscf_cookie_color_text'              => 'sanitize_hex_color',
		'sbwscf_cookie_color_accept'            => 'sanitize_hex_color',
		'sbwscf_cookie_color_accept_text'       => 'sanitize_hex_color',
		'sbwscf_cookie_color_reject'            => 'sanitize_hex_color',
		'sbwscf_cookie_color_reject_text'       => 'sanitize_hex_color',
		'sbwscf_cookie_color_preferences'       => 'sanitize_hex_color',
		'sbwscf_cookie_color_preferences_text'  => 'sanitize_hex_color',
		'sbwscf_cookie_color_link'              => 'sanitize_hex_color',
		'sbwscf_cookie_color_manage_background' => 'sanitize_hex_color',
		'sbwscf_cookie_color_manage_text'       => 'sanitize_hex_color',
	);

	foreach ( $settings_to_register as $option => $callback ) {
		register_setting(
			'sbwscf_cookies',
			$option,
			array(
				'sanitize_callback' => $callback,
			)
		);
	}

	add_settings_section(
		'sbwscf_cookies_panel_section',
		esc_html__( 'Cookie Panel Settings', 'smile-basic-web' ),
		'sbwscf_cookies_panel_section_cb',
		'sbwscf_cookies'
	);

	add_settings_field(
		'sbwscf_cookie_panel_title',
		esc_html__( 'Panel Title', 'smile-basic-web' ),
		'sbwscf_cookie_panel_title_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	add_settings_field(
		'sbwscf_cookie_message',
		esc_html__( 'Message Text', 'smile-basic-web' ),
		'sbwscf_cookie_message_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	add_settings_field(
		'sbwscf_cookie_functional_title',
		esc_html__( 'Functional Cookies Title', 'smile-basic-web' ),
		'sbwscf_cookie_functional_title_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	add_settings_field(
		'sbwscf_cookie_functional_description',
		esc_html__( 'Functional Cookies Description', 'smile-basic-web' ),
		'sbwscf_cookie_functional_description_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	add_settings_field(
		'sbwscf_cookie_label_accept_initial',
		esc_html__( 'Accept button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_accept_initial',
			'default'     => __( 'Accept', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the "Accept" button when the notice first appears.', 'smile-basic-web' ),
		)
	);

	add_settings_field(
		'sbwscf_cookie_label_deny_initial',
		esc_html__( 'Deny button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_deny_initial',
			'default'     => __( 'Deny', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the "Deny" button when the notice first appears.', 'smile-basic-web' ),
		)
	);

	add_settings_field(
		'sbwscf_cookie_label_accept_all',
		esc_html__( 'Accept All button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_accept_all',
			'default'     => __( 'Accept All', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the "Accept All" button.', 'smile-basic-web' ),
		)
	);

	add_settings_field(
		'sbwscf_cookie_label_deny_all',
		esc_html__( 'Deny All button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_deny_all',
			'default'     => __( 'Deny All', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the "Deny All" button.', 'smile-basic-web' ),
		)
	);

	add_settings_field(
		'sbwscf_cookie_label_preferences',
		esc_html__( 'Preferences button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_preferences',
			'default'     => __( 'Preferences', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the "Preferences" button when collapsed.', 'smile-basic-web' ),
		)
	);

	add_settings_field(
		'sbwscf_cookie_label_accept_prefs',
		esc_html__( 'Accept Preferences button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_accept_prefs',
			'default'     => __( 'Accept Preferences', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the "Preferences" button after expanding the categories.', 'smile-basic-web' ),
		)
	);

	add_settings_field(
		'sbwscf_privacy_policy_page',
		esc_html__( 'Privacy Policy Page', 'smile-basic-web' ),
		'sbwscf_page_selector_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'name' => 'sbwscf_privacy_policy_page',
		)
	);

	add_settings_field(
		'sbwscf_cookie_policy_page',
		esc_html__( 'Cookies Policy Page', 'smile-basic-web' ),
		'sbwscf_page_selector_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'name' => 'sbwscf_cookie_policy_page',
		)
	);

	add_settings_field(
		'sbwscf_legal_notice_page',
		esc_html__( 'Legal Notice Page', 'smile-basic-web' ),
		'sbwscf_page_selector_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'name' => 'sbwscf_legal_notice_page',
		)
	);

	add_settings_field(
		'sbwscf_show_manage_minified_label',
		esc_html__( 'Show label title on minimized tab', 'smile-basic-web' ),
		'sbwscf_show_manage_minified_label_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	add_settings_field(
		'sbwscf_cookie_label_manage',
		esc_html__( 'Manage Consent button label', 'smile-basic-web' ),
		'sbwscf_cookie_label_field_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section',
		array(
			'option'      => 'sbwscf_cookie_label_manage',
			'default'     => __( 'Manage Consent', 'smile-basic-web' ),
			'description' => esc_html__( 'Text displayed on the floating button that reopens the cookies panel.', 'smile-basic-web' ),
		)
	);

	$colors = array(
		'background'        => __( 'Background Color', 'smile-basic-web' ),
		'titles'            => __( 'Title Color', 'smile-basic-web' ),
		'text'              => __( 'Text Color', 'smile-basic-web' ),
		'accept'            => __( 'Accept Button Color', 'smile-basic-web' ),
		'accept_text'       => __( 'Accept Text Color', 'smile-basic-web' ),
		'reject'            => __( 'Reject Button Color', 'smile-basic-web' ),
		'reject_text'       => __( 'Reject Text Color', 'smile-basic-web' ),
		'preferences'       => __( 'Preferences Button Color', 'smile-basic-web' ),
		'preferences_text'  => __( 'Preferences Text Color', 'smile-basic-web' ),
		'link'              => __( 'Link Color', 'smile-basic-web' ),
		'manage_background' => __( 'Manage Consent Button Background', 'smile-basic-web' ),
		'manage_text'       => __( 'Manage Consent Button Text Color', 'smile-basic-web' ),
	);

	foreach ( $colors as $key => $label ) {
		add_settings_field(
			"sbwscf_cookie_color_{$key}",
			$label,
			'sbwscf_color_field_cb',
			'sbwscf_cookies',
			'sbwscf_cookies_panel_section',
			array(
				'key' => $key,
			)
		);
	}

	add_settings_field(
		'sbwscf_cookie_panel_size',
		esc_html__( 'Panel Size', 'smile-basic-web' ),
		'sbwscf_cookie_panel_size_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	add_settings_field(
		'sbwscf_cookie_minimized_position',
		esc_html__( 'Minimized Label Position', 'smile-basic-web' ),
		'sbwscf_cookie_minimized_position_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
	);

	/*
	------------------------------------------------------------------
	 * Section 3: Tracking Scripts
	 * ------------------------------------------------------------------
	 */
	register_setting(
		'sbwscf_cookies',
		'sbwscf_tracking_scripts',
		array(
			'sanitize_callback' => 'sbwscf_sanitize_tracking_scripts',
		)
	);

	add_settings_section(
		'sbwscf_cookies_tracking_section',
		esc_html__( 'Tracking Scripts', 'smile-basic-web' ),
		'sbwscf_cookies_tracking_section_cb',
		'sbwscf_cookies'
	);

	add_settings_field(
		'sbwscf_tracking_scripts',
		'',
		'sbwscf_tracking_scripts_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_tracking_section'
	);
}
add_action( 'admin_init', 'sbwscf_cookies_register_settings' );

/**
 * Determine whether an option belongs to the Cookies tab configuration.
 *
 * @param string $option Option name being modified.
 * @return bool Whether the option impacts cookie consent output.
 */
function sbwscf_is_cookie_setting_option( string $option ): bool {
	if ( 'sbwscf_cookie_settings_revision' === $option ) {
			return false;
	}

	if ( 0 === strpos( $option, 'sbwscf_cookie_' ) ) {
			return true;
	}

	$additional = array(
		'sbwscf_enable_cookies_notice',
		'sbwscf_tracking_scripts',
		'sbwscf_show_manage_minified_label',
		'sbwscf_privacy_policy_page',
		'sbwscf_legal_notice_page',
	);

	return in_array( $option, $additional, true );
}

/**
 * Increment the cookie settings revision counter.
 *
 * This value is embedded in the localized script configuration so that
 * previously stored consents are invalidated whenever administrators adjust
 * cookie-related settings.
 *
 * @return void
 */
function sbwscf_increment_cookie_settings_revision(): void {
	static $bumped = false;

	if ( $bumped ) {
			return;
	}

	$bumped  = true;
	$current = (int) get_option( 'sbwscf_cookie_settings_revision', 0 );
	$next    = $current + 1;

	update_option( 'sbwscf_cookie_settings_revision', (string) $next );
}

/**
 * Maybe bump the cookie settings revision when updating an option.
 *
 * @param string $option    Option name.
 * @param mixed  $old_value Previous value stored in the database.
 * @param mixed  $value     New value being saved.
 * @return void
 */
function sbwscf_maybe_bump_cookie_settings_revision( string $option, $old_value, $value ): void {
	if ( ! sbwscf_is_cookie_setting_option( $option ) ) {
			return;
	}

	if ( $value === $old_value ) {
			return;
	}

	sbwscf_increment_cookie_settings_revision();
}
add_action( 'update_option', 'sbwscf_maybe_bump_cookie_settings_revision', 10, 3 );

/**
 * Maybe bump the cookie settings revision when adding a new option.
 *
 * @param string $option Option name.
 * @param mixed  $value  Value being stored.
 * @return void
 */
function sbwscf_maybe_bump_cookie_settings_revision_on_add( string $option, $value ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	if ( ! sbwscf_is_cookie_setting_option( $option ) ) {
			return;
	}

	sbwscf_increment_cookie_settings_revision();
}
add_action( 'add_option', 'sbwscf_maybe_bump_cookie_settings_revision_on_add', 10, 2 );

/**
 * Section callback: Enable cookies notice.
 *
 * @return void
 */
function sbwscf_cookies_enable_section_cb(): void {
	echo '<p>' . esc_html__( 'Enable or disable the cookie notification for your website visitors.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Enable cookies notice checkbox.
 *
 * @return void
 */
function sbwscf_enable_cookies_notice_cb(): void {
	$enabled = get_option( 'sbwscf_enable_cookies_notice', '' );
	printf(
		'<label><input type="checkbox" name="sbwscf_enable_cookies_notice" value="1" %s /> %s</label>',
		checked( '1', $enabled, false ),
		esc_html__( 'Display cookie notification on the website', 'smile-basic-web' )
	);
}

/**
 * Section callback: Cookie panel settings.
 *
 * @return void
 */
function sbwscf_cookies_panel_section_cb(): void {
	echo '<p>' . esc_html__( 'Customize the appearance and behavior of the cookie notification panel.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Cookie panel title input.
 *
 * @return void
 */
function sbwscf_cookie_panel_title_cb(): void {
	$value = get_option( 'sbwscf_cookie_panel_title', '' );
	printf(
		'<input type="text" name="sbwscf_cookie_panel_title" value="%s" class="regular-text" />',
		esc_attr( $value )
	);
	echo '<p class="description">' . esc_html__( 'Define the heading shown at the top of the cookie consent panel.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Cookie message editor.
 *
 * @return void
 */
function sbwscf_cookie_message_cb(): void {
	$value = get_option( 'sbwscf_cookie_message', '' );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
	}

	wp_editor(
		wp_kses_post( $value ),
		'sbwscf_cookie_message_editor',
		array(
			'textarea_name' => 'sbwscf_cookie_message',
			'textarea_rows' => 8,
			'teeny'         => true,
			'media_buttons' => false,
		)
	);

	echo '<p class="description">' . esc_html__( 'Use bold text, lists, or links to craft the cookie notice shown to visitors.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Functional cookies title editor.
 *
 * @return void
 */
function sbwscf_cookie_functional_title_cb(): void {
	$value = get_option( 'sbwscf_cookie_functional_title', '' );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
	}

	wp_editor(
		sbwscf_sanitize_cookie_inline_html( $value ),
		'sbwscf_cookie_functional_title_editor',
		array(
			'textarea_name' => 'sbwscf_cookie_functional_title',
			'textarea_rows' => 4,
			'media_buttons' => false,
			'teeny'         => false,
			'tinymce'       => array(
				'toolbar1'          => 'bold italic underline | superscript subscript | link unlink | removeformat | undo redo',
				'toolbar2'          => '',
				'forced_root_block' => '',
				'force_br_newlines' => true,
				'force_p_newlines'  => false,
			),
			'quicktags'     => array(
				'buttons' => 'strong,em,link,del,ins,code,close',
			),
		)
	);

	echo '<p class="description">' . esc_html__( 'Use line breaks or inline formatting to refine how the Functional cookies title appears in the preferences panel.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Functional cookies description editor.
 *
 * @return void
 */
function sbwscf_cookie_functional_description_cb(): void {
	$value = get_option( 'sbwscf_cookie_functional_description', '' );

	if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
	}

	wp_editor(
		wp_kses_post( $value ),
		'sbwscf_cookie_functional_description_editor',
		array(
			'textarea_name' => 'sbwscf_cookie_functional_description',
			'textarea_rows' => 6,
			'teeny'         => true,
			'media_buttons' => false,
		)
	);

	echo '<p class="description">' . esc_html__( 'Define the explanatory text shown under the Functional cookies category.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Cookie button label input.
 *
 * @param array $args {
 *     Field arguments.
 *
 *     @type string $option      Option name.
 *     @type string $default     Default label value.
 *     @type string $description Field description.
 * }
 * @return void
 */
function sbwscf_cookie_label_field_cb( $args ): void {
	$option      = $args['option'];
	$default     = $args['default'];
	$description = $args['description'];
	$value       = sbwscf_get_cookie_label( $option, $default );

	printf(
		'<input type="text" name="%1$s" id="%1$s" value="%2$s" class="regular-text" />',
		esc_attr( $option ),
		esc_attr( $value )
	);

	if ( '' !== $description ) {
			echo '<p class="description">' . esc_html( $description ) . '</p>';
	}
}

/**
 * Field callback: Page selector dropdown.
 *
 * @param array $args {
 *     Args.
 *
 *     @type string $name Option name.
 * }
 * @return void
 */
function sbwscf_page_selector_cb( $args ): void {
	$name       = $args['name'];
	$current_id = (int) get_option( $name, 0 );
	$pages      = get_pages();

	echo '<select name="' . esc_attr( $name ) . '">';
	echo '<option value="0">' . esc_html__( 'None', 'smile-basic-web' ) . '</option>';

	foreach ( $pages as $page ) {
		echo '<option value="' . esc_attr( $page->ID ) . '" ' . selected( $page->ID, $current_id, false ) . '>' . esc_html( $page->post_title ) . '</option>';
	}

	echo '</select>';
}

/**
 * Field callback: Show manage label checkbox.
 *
 * @return void
 */
function sbwscf_show_manage_minified_label_cb(): void {
	$enabled = get_option( 'sbwscf_show_manage_minified_label', '' );
	printf(
		'<label><input type="checkbox" name="sbwscf_show_manage_minified_label" value="1" %s /> %s</label>',
		checked( '1', $enabled, false ),
		esc_html__( 'Always show label title (disable hover effect)', 'smile-basic-web' )
	);
}

/**
 * Prints a colour picker (visual) + HEX text box (editable).
 *
 * @since 1.0.1
 * @package smile-basic-web
 *
 * @param array $args {.
 *     @type string $key Colour option suffix.
 * }
 * @return void
 */
function sbwscf_color_field_cb( $args ): void {
	$key   = $args['key'];
	$value = get_option( "sbwscf_cookie_color_{$key}", '#000000' );
	?>
	<div class="sbwscf-color-wrapper">
		<input
			type="color"
			id="sbwscf_color_preview_<?php echo esc_attr( $key ); ?>"
			class="sbwscf-color-preview"
			value="<?php echo esc_attr( $value ); ?>"
			aria-label="<?php esc_attr_e( 'Pick colour', 'smile-basic-web' ); ?>"
		/>

		<input
			type="text"
			name="sbwscf_cookie_color_<?php echo esc_attr( $key ); ?>"
			id="sbwscf_cookie_color_<?php echo esc_attr( $key ); ?>"
			class="regular-text sbwscf-color-hex"
			value="<?php echo esc_attr( $value ); ?>"
			pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
			placeholder="#ffffff"
		/>
	</div>
	<?php
}

/**
 * Prints the “Panel Size” <select>.
 *
 * @since 1.0.0
 * @package smile-basic-web
 *
 * @return void
 */
function sbwscf_cookie_panel_size_cb(): void {
	$value   = get_option( 'sbwscf_cookie_panel_size', 'small' );
	$options = array(
		'fullscreen' => __( 'Fullscreen', 'smile-basic-web' ),
		'large'      => __( 'Large', 'smile-basic-web' ),
		'small'      => __( 'Small', 'smile-basic-web' ),
	);

	echo '<select name="sbwscf_cookie_panel_size" id="sbwscf_cookie_panel_size">';
	foreach ( $options as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}


/**
 * Prints the “Minimized label position” <select>.
 *
 * @package smile-basic-web
 *
 * @return void
 */
function sbwscf_cookie_minimized_position_cb(): void {
	$value   = get_option( 'sbwscf_cookie_minimized_position', 'right' );
	$options = array(
		'left'   => __( 'Left', 'smile-basic-web' ),
		'center' => __( 'Center', 'smile-basic-web' ),
		'right'  => __( 'Right', 'smile-basic-web' ),
	);

	echo '<select name="sbwscf_cookie_minimized_position" id="sbwscf_cookie_minimized_position">';
	foreach ( $options as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}


/**
 * Section callback: Tracking scripts.
 *
 * @return void
 */
function sbwscf_cookies_tracking_section_cb(): void {
	echo '<p>' . esc_html__( 'Add any third-party tracking scripts you want to load after cookie acceptance.', 'smile-basic-web' ) . '</p>';
}

/**
 * Field callback: Tracking scripts table.
 *
 * @return void
 */
function sbwscf_tracking_scripts_cb(): void {
	if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
	}

	$scripts                     = get_option( 'sbwscf_tracking_scripts', array() );
	$name_editor_base_settings   = array(
		'media_buttons' => false,
		'teeny'         => false,
		'textarea_rows' => 2,
		'tinymce'       => array(
			'toolbar1'          => 'bold italic underline | superscript subscript | link unlink | removeformat | undo redo',
			'toolbar2'          => '',
			'forced_root_block' => '',
			'force_br_newlines' => true,
			'force_p_newlines'  => false,
		),
		'quicktags'     => array(
			'buttons' => 'strong,em,link,del,ins,code,close',
		),
	);
	$description_editor_settings = array(
		'media_buttons' => false,
		'teeny'         => true,
		'textarea_rows' => 6,
		'tinymce'       => array(
			'toolbar1'          => 'bold italic underline | bullist numlist | link unlink | removeformat | undo redo',
			'toolbar2'          => '',
			'forced_root_block' => 'p',
			'force_br_newlines' => false,
			'force_p_newlines'  => true,
		),
		'quicktags'     => array(
			'buttons' => 'strong,em,link,del,ins,code,close',
		),
	);

	wp_localize_script(
		'sbwscf-cookies-admin',
		'sbwscfTrackingEditorSettings',
		array(
			'name'        => $name_editor_base_settings,
			'description' => $description_editor_settings,
		)
	);
	?>
	<div id="sbwscf-tracking-scripts-table">
	<table class="widefat fixed">
			<thead>
					<tr>
							<th><?php esc_html_e( 'Name', 'smile-basic-web' ); ?></th>
							<th><?php esc_html_e( 'Description', 'smile-basic-web' ); ?></th>
							<th><?php esc_html_e( 'Script Code', 'smile-basic-web' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'smile-basic-web' ); ?></th>
					</tr>
			</thead>
			<tbody>
			<?php
			if ( is_array( $scripts ) ) :
				foreach ( $scripts as $i => $script ) :
						$name_editor_id        = 'sbwscf_tracking_scripts_' . $i . '_name';
						$description_editor_id = 'sbwscf_tracking_scripts_' . $i . '_description';
					?>
							<tr data-row-index="<?php echo esc_attr( $i ); ?>" data-name-editor="<?php echo esc_attr( $name_editor_id ); ?>" data-description-editor="<?php echo esc_attr( $description_editor_id ); ?>">
									<td>
										<?php
										wp_editor(
											sbwscf_sanitize_cookie_inline_html( (string) ( $script['name'] ?? '' ) ),
											$name_editor_id,
											array_merge(
												$name_editor_base_settings,
												array(
													'textarea_name' => 'sbwscf_tracking_scripts[' . $i . '][name]',
												)
											)
										);
										?>
									</td>
									<td>
										<?php
										wp_editor(
											wp_kses_post( (string) ( $script['description'] ?? '' ) ),
											$description_editor_id,
											array_merge(
												$description_editor_settings,
												array(
													'textarea_name' => 'sbwscf_tracking_scripts[' . $i . '][description]',
												)
											)
										);
										?>
									</td>
									<td>
											<textarea id="sbwscf_tracking_scripts_<?php echo esc_attr( $i ); ?>_code" name="sbwscf_tracking_scripts[<?php echo esc_attr( $i ); ?>][code]" rows="4" class="regular-text code"><?php echo esc_textarea( $script['code'] ?? '' ); ?></textarea>
									</td>
									<td><button type="button" class="button sbwscf-remove-script"><?php esc_html_e( 'Delete', 'smile-basic-web' ); ?></button></td>
							</tr>
							<?php
					endforeach;
			endif;
			?>
			</tbody>
	</table>
	</div>
	<button type="button" class="button button-primary" id="sbwscf-add-script"><?php esc_html_e( 'Add Script', 'smile-basic-web' ); ?></button>
	<?php
}

/**
 * Sanitize checkbox input.
 *
 * @param mixed $value Raw value.
 * @return string Sanitized value.
 */
function sbwscf_sanitize_checkbox( $value ): string {
	return ( isset( $value ) && '1' === $value ) ? '1' : '';
}

/**
 * Sanitize cookie panel title input.
 *
 * @param string $value Raw value.
 * @return string Sanitized value.
 */
function sbwscf_sanitize_cookie_title( $value ): string {
	return sanitize_text_field( $value );
}

/**
 * Sanitize cookie message content allowing basic formatting.
 *
 * @param string $value Raw value.
 * @return string Sanitized value.
 */
function sbwscf_sanitize_cookie_message( $value ): string {
	return wp_kses_post( $value );
}

/**
 * Returns allowed inline HTML tags for cookie titles.
 *
 * @return array Allowed tags for inline cookie text.
 */
function sbwscf_get_cookie_inline_allowed_tags(): array {
	return array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
		),
		'abbr'   => array(
			'title' => true,
		),
		'strong' => array(),
		'em'     => array(),
		'b'      => array(),
		'i'      => array(),
		'u'      => array(),
		'sup'    => array(),
		'sub'    => array(),
		'code'   => array(),
		'mark'   => array(),
		'small'  => array(),
		'del'    => array(),
		'ins'    => array(),
		'span'   => array(
			'class' => true,
		),
		'br'     => array(),
	);
}

/**
 * Generate a normalized slug for tracking script categories.
 *
 * Ensures that HTML tags or entities present in the stored name do not alter
 * the slug used across PHP and JavaScript, preventing mismatches that would
 * bypass the user's consent preferences.
 *
 * @param string $value Raw script name value.
 * @return string Normalized slug or an empty string when it cannot be generated.
 */
function sbwscf_get_cookie_script_slug( string $value ): string {
	$stripped = wp_strip_all_tags( $value );
	$stripped = trim( $stripped );

	if ( '' === $stripped ) {
		return '';
	}

	return sanitize_title( $stripped );
}

/**
 * Sanitize cookie titles that support inline formatting.
 *
 * @param string $value Raw value.
 * @return string Sanitized value.
 */
function sbwscf_sanitize_cookie_inline_html( $value ): string {
	return wp_kses( (string) $value, sbwscf_get_cookie_inline_allowed_tags() );
}

/**
 * Normalize line breaks for cookie inline HTML values.
 *
 * Converts plain newline characters into `<br />` tags so that manually
 * entered line breaks appear correctly inside the cookies preferences
 * summary, which only supports phrasing content.
 *
 * @param string $value Sanitized value.
 * @return string Value with normalized line breaks.
 */
function sbwscf_format_cookie_inline_html( string $value ): string {
	if ( '' === $value ) {
			return '';
	}

	return preg_replace( '/(?:\r\n|\r|\n)/', "<br />\n", $value );
}

/**
 * Sanitize cookie button label input.
 *
 * @param string $value Raw value.
 * @return string Sanitized value.
 */
function sbwscf_sanitize_cookie_label( $value ): string {
	return sanitize_text_field( $value );
}

/**
 * Sanitize tracking scripts array.
 *
 * @param array|string $input Raw input.
 * @return array Sanitized scripts.
 */
function sbwscf_sanitize_tracking_scripts( $input ): array {
	$sanitized = array();

	if ( is_array( $input ) ) {
		foreach ( $input as $entry ) {
				$raw_name = isset( $entry['name'] ) ? wp_unslash( (string) $entry['name'] ) : '';
				$name     = sbwscf_sanitize_cookie_inline_html( $raw_name );
				$slug     = sbwscf_get_cookie_script_slug( $name );
				$desc     = isset( $entry['description'] ) ? wp_kses_post( wp_unslash( (string) $entry['description'] ) ) : '';
				$code     = isset( $entry['code'] ) ? wp_kses_post( wp_unslash( (string) $entry['code'] ) ) : '';
				$code     = trim( $code );

			if ( '' !== $slug && '' !== $code ) {
					$sanitized[] = array(
						'name'        => $name,
						'description' => $desc,
						'code'        => $code,
					);
			}
		}
	}

	return $sanitized;
}

/**
 * Retrieve a cookie label with a default fallback.
 *
 * @param string $option  Option name.
 * @param string $default Default label value.
 * @return string Label to display.
 */
function sbwscf_get_cookie_label( string $option, string $default ): string {
	$value = sanitize_text_field( (string) get_option( $option, '' ) );

	if ( '' === trim( $value ) ) {
			return $default;
	}

	return $value;
}
