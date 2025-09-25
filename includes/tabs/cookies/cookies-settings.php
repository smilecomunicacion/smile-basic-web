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
		'background'       => '#ffffff',
		'text'             => '#000000',
		'accept'           => '#4caf50',
		'accept_text'      => '#ffffff',
		'reject'           => '#f44336',
		'reject_text'      => '#ffffff',
		'preferences'      => '#e0e0e0',
		'preferences_text' => '#000000',
		'link'             => '#0000ee',
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
		'sbwscf_cookie_message'                => 'sanitize_textarea_field',
		'sbwscf_cookie_panel_size'             => 'sanitize_text_field',
		'sbwscf_cookie_minimized_position'     => 'sanitize_text_field',
		'sbwscf_show_manage_minified_label'    => 'sbwscf_sanitize_checkbox',
		'sbwscf_cookie_policy_page'            => 'absint',
		'sbwscf_privacy_policy_page'           => 'absint',
		'sbwscf_legal_notice_page'             => 'absint',
		'sbwscf_cookie_color_background'       => 'sanitize_hex_color',
		'sbwscf_cookie_color_text'             => 'sanitize_hex_color',
		'sbwscf_cookie_color_accept'           => 'sanitize_hex_color',
		'sbwscf_cookie_color_accept_text'      => 'sanitize_hex_color',
		'sbwscf_cookie_color_reject'           => 'sanitize_hex_color',
		'sbwscf_cookie_color_reject_text'      => 'sanitize_hex_color',
		'sbwscf_cookie_color_preferences'      => 'sanitize_hex_color',
		'sbwscf_cookie_color_preferences_text' => 'sanitize_hex_color',
		'sbwscf_cookie_color_link'             => 'sanitize_hex_color',
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
		'sbwscf_cookie_message',
		esc_html__( 'Message Text', 'smile-basic-web' ),
		'sbwscf_cookie_message_cb',
		'sbwscf_cookies',
		'sbwscf_cookies_panel_section'
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

	$colors = array(
		'background'       => __( 'Background Color', 'smile-basic-web' ),
		'text'             => __( 'Text Color', 'smile-basic-web' ),
		'accept'           => __( 'Accept Button Color', 'smile-basic-web' ),
		'accept_text'      => __( 'Accept Text Color', 'smile-basic-web' ),
		'reject'           => __( 'Reject Button Color', 'smile-basic-web' ),
		'reject_text'      => __( 'Reject Text Color', 'smile-basic-web' ),
		'preferences'      => __( 'Preferences Button Color', 'smile-basic-web' ),
		'preferences_text' => __( 'Preferences Text Color', 'smile-basic-web' ),
		'link'             => __( 'Link Color', 'smile-basic-web' ),
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
 * Field callback: Cookie message textarea.
 *
 * @return void
 */
function sbwscf_cookie_message_cb(): void {
	$value = get_option( 'sbwscf_cookie_message', '' );
	printf(
		'<textarea name="sbwscf_cookie_message" rows="4" class="large-text">%s</textarea>',
		esc_textarea( $value )
	);
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
	$scripts = get_option( 'sbwscf_tracking_scripts', array() );
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
				?>
				<tr>
					<td><input type="text" name="sbwscf_tracking_scripts[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $script['name'] ?? '' ); ?>" class="regular-text" /></td>
					<td><input type="text" name="sbwscf_tracking_scripts[<?php echo esc_attr( $i ); ?>][description]" value="<?php echo esc_attr( $script['description'] ?? '' ); ?>" class="regular-text" /></td>
					<td><textarea name="sbwscf_tracking_scripts[<?php echo esc_attr( $i ); ?>][code]" rows="4" class="regular-text code"><?php echo esc_textarea( $script['code'] ?? '' ); ?></textarea></td>
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
 * Sanitize tracking scripts array.
 *
 * @param array|string $input Raw input.
 * @return array Sanitized scripts.
 */
function sbwscf_sanitize_tracking_scripts( $input ): array {
	$sanitized = array();

	if ( is_array( $input ) ) {
		foreach ( $input as $entry ) {
			$name = isset( $entry['name'] ) ? sanitize_text_field( $entry['name'] ) : '';
			$desc = isset( $entry['description'] ) ? sanitize_text_field( $entry['description'] ) : '';
			$code = isset( $entry['code'] ) ? wp_kses_post( $entry['code'] ) : '';

			if ( '' !== $name && '' !== $code ) {
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
