<?php
/**
 * Displays the cookie consent panel on the frontend.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
* ------------------------------------------------------------------
* Mostrar aviso de cookies si está habilitado
* ------------------------------------------------------------------
*/
add_action( 'wp_footer', 'sbwscf_output_cookie_consent_panel', 99 );

/**
 * Outputs the cookie consent panel HTML markup.
 *
 * This function generates and displays the cookie consent panel interface
 * that allows users to manage their cookie preferences. The panel typically
 * includes options to accept, decline, or customize cookie settings.
 *
 * @since 1.0.0
 * @return void
 */
function sbwscf_output_cookie_consent_panel(): void {
	$enabled = get_option( 'sbwscf_enable_cookies_notice' );

	if ( '1' !== $enabled ) {
		return;
	}

	/*
	* ------------------------------------------------------------------
	* Función auxiliar para colores con fallback
	* ------------------------------------------------------------------
	*/
	$color_or_default = static function ( string $option, string $default_color_fallback ): string {
		$value = trim( (string) get_option( $option ) );
		return '' !== $value ? $value : $default_color_fallback;
	};

	/*
	* ------------------------------------------------------------------
	* Obtener opciones de configuración del usuario
	* ------------------------------------------------------------------
	*/
        $message = get_option( 'sbwscf_cookie_message', '' );
        $panel_title = get_option( 'sbwscf_cookie_panel_title', '' );
        $panel_title = '' !== trim( (string) $panel_title ) ? $panel_title : __( 'Manage Consent', 'smile-basic-web' );
        $accept_initial_label = sbwscf_get_cookie_label( 'sbwscf_cookie_label_accept_initial', __( 'Accept', 'smile-basic-web' ) );
        $deny_initial_label   = sbwscf_get_cookie_label( 'sbwscf_cookie_label_deny_initial', __( 'Deny', 'smile-basic-web' ) );
        $accept_all_label     = sbwscf_get_cookie_label( 'sbwscf_cookie_label_accept_all', __( 'Accept All', 'smile-basic-web' ) );
        $deny_all_label       = sbwscf_get_cookie_label( 'sbwscf_cookie_label_deny_all', __( 'Deny All', 'smile-basic-web' ) );
        $preferences_label = sbwscf_get_cookie_label( 'sbwscf_cookie_label_preferences', __( 'Preferences', 'smile-basic-web' ) );
        $accept_preferences_label = sbwscf_get_cookie_label( 'sbwscf_cookie_label_accept_prefs', __( 'Accept Preferences', 'smile-basic-web' ) );
        $manage_button_label = sanitize_text_field( (string) get_option( 'sbwscf_cookie_label_manage', '' ) );
        $functional_title = get_option( 'sbwscf_cookie_functional_title', '' );
        $functional_title = sbwscf_sanitize_cookie_inline_html( $functional_title );
        $functional_title = sbwscf_format_cookie_inline_html( $functional_title );

        if ( '' === trim( wp_strip_all_tags( (string) $functional_title ) ) ) {
                $functional_title = __( 'Functional', 'smile-basic-web' );
        }
        $functional_description = get_option( 'sbwscf_cookie_functional_description', '' );
        $bg_color = $color_or_default( 'sbwscf_cookie_color_background', '#fff' );
        $title_color = $color_or_default( 'sbwscf_cookie_color_titles', '#000000' );
        $text_color = $color_or_default( 'sbwscf_cookie_color_text', '#000000' );
        $accept_bg = $color_or_default( 'sbwscf_cookie_color_accept', '#4caf50' );
        $accept_txt = $color_or_default( 'sbwscf_cookie_color_accept_text', '#fff' );
        $reject_bg = $color_or_default( 'sbwscf_cookie_color_reject', '#f44336' );
        $reject_txt = $color_or_default( 'sbwscf_cookie_color_reject_text', '#fff' );
        $preferences_bg = $color_or_default( 'sbwscf_cookie_color_preferences', '#2196f3' );
        $preferences_txt = $color_or_default( 'sbwscf_cookie_color_preferences_text', '#fff' );
        $link_color = $color_or_default( 'sbwscf_cookie_color_link', '#0000ee' );
        $manage_btn_bg = $color_or_default( 'sbwscf_cookie_color_manage_background', $bg_color );
        $manage_btn_txt = $color_or_default( 'sbwscf_cookie_color_manage_text', $text_color );
        // Tamaño del panel de cookies.
        // Por defecto, se usa 'small' si no se ha configurado.
        $panel_size = get_option( 'sbwscf_cookie_panel_size', 'small' );

        if ( '' === trim( $manage_button_label ) ) {
                $manage_button_label = sanitize_text_field( (string) $panel_title );
        }

	/*
	* ------------------------------------------------------------------
	* Estilos inline dinámicos desde las opciones
	* ------------------------------------------------------------------
	*/
	?>
        <style>
        #sbwscf-smile-cookies-panel {
                --sbwscf-bg-color: <?php echo esc_attr( $bg_color ); ?>;
                --sbwscf-text-color: <?php echo esc_attr( $text_color ); ?>;
                --sbwscf-title-color: <?php echo esc_attr( $title_color ); ?>;
                --sbwscf-link-color: <?php echo esc_attr( $link_color ); ?>;
                color: <?php echo esc_attr( $text_color ); ?>;
        }
        .sbwscf-smile-cookies-box {
                background-color: <?php echo esc_attr( $bg_color ); ?>;
        }
        #sbwscf-smile-cookies-panel .sbwscf-smile-cookies-title,
        #sbwscf-smile-cookies-panel .sbwscf-cookie-title {
                color: <?php echo esc_attr( $title_color ); ?>;
        }
        #sbwscf-smile-cookies-panel .sbwscf-smile-cookies-message,
        #sbwscf-smile-cookies-panel .sbwscf-smile-cookies-message *,
        #sbwscf-smile-cookies-panel .sbwscf-cookie-summary,
        #sbwscf-smile-cookies-panel .sbwscf-cookie-description,
        #sbwscf-smile-cookies-panel .sbwscf-cookie-description *,
        #sbwscf-smile-cookies-panel .sbwscf-cookie-checkbox,
        #sbwscf-smile-cookies-panel .sbwscf-cookie-checkbox label {
                color: <?php echo esc_attr( $text_color ); ?> !important;
        }
	#sbwscf-smile-cookies-panel .sbwscf-smile-cookies-accept {
		background-color: <?php echo esc_attr( $accept_bg ); ?>;
		color:            <?php echo esc_attr( $accept_txt ); ?>;
	}
	#sbwscf-smile-cookies-panel .sbwscf-smile-cookies-deny {
		background-color: <?php echo esc_attr( $reject_bg ); ?>;
		color:            <?php echo esc_attr( $reject_txt ); ?>;
	}
	#sbwscf-smile-cookies-panel .sbwscf-smile-cookies-preferences {
		background-color: <?php echo esc_attr( $preferences_bg ); ?>;
		color:            <?php echo esc_attr( $preferences_txt ); ?>;
	}
	#sbwscf-smile-cookies-panel .sbwscf-smile-cookies-links a {
		color: <?php echo esc_attr( $link_color ); ?>;
	}
        #sbwscf-manage-consent-btn {
                background-color: <?php echo esc_attr( $manage_btn_bg ); ?>;
                color:            <?php echo esc_attr( $manage_btn_txt ); ?>;
        }
	</style>


<div id="sbwscf-smile-cookies-panel" class="sbwscf-smile-cookies-panel sbwscf-smile-size-<?php echo esc_attr( $panel_size ); ?>" role="dialog" aria-live="polite">
	<div class="sbwscf-smile-cookies-box">
		<div class="sbwscf-smile-cookies-header">
                        <strong class="sbwscf-smile-cookies-title"><?php echo esc_html( $panel_title ); ?></strong>
		</div>
		<div class="sbwscf-smile-cookies-message">
			<?php
                        if ( '' !== trim( wp_strip_all_tags( (string) $message ) ) ) {
                                echo wp_kses_post( wpautop( $message ) );
			} else {
				esc_html_e( 'To offer the best experience, we use cookies to store or access device information. Your consent allows us to process browsing behavior or unique IDs on this site. Refusing or withdrawing consent may affect site features.', 'smile-basic-web' );
			}
			?>
		</div>

		<div id="sbwscf-cookie-categories" class="sbwscf-cookie-categories" hidden>
                        <strong class="sbwscf-smile-cookies-title"><?php echo esc_html( $preferences_label ); ?></strong>
			<details class="sbwscf-cookie-category">
				<summary class="sbwscf-cookie-summary">
                                        <span class="sbwscf-cookie-title"><?php echo wp_kses( $functional_title, sbwscf_get_cookie_inline_allowed_tags() ); ?></span>
					<span>
						<label class="sbwscf-cookie-checkbox" for="sbwscf-functional-always-active">
							<input type="checkbox" id="sbwscf-functional-always-active" name="sbwscf_cookie_functional" checked
								disabled aria-disabled="true" />
							<?php esc_html_e( 'Always active', 'smile-basic-web' ); ?>
						</label>
					</span>
				</summary>
                                <div class="sbwscf-cookie-description">
                                        <?php
                                        if ( '' !== trim( wp_strip_all_tags( (string) $functional_description ) ) ) {
                                                echo wp_kses_post( wpautop( $functional_description ) );
                                        } else {
                                                esc_html_e( 'Technical storage or access is strictly necessary to enable the use of a specific service explicitly requested by the user or to carry out the transmission of a communication over an electronic communications network.', 'smile-basic-web' );
                                        }
                                        ?>
                                </div>
			</details>

			<?php
			$scripts = get_option( 'sbwscf_tracking_scripts', array() );

                        if ( is_array( $scripts ) && ! empty( $scripts ) ) :
                                foreach ( $scripts as $i => $script ) :
                                        $raw_name        = $script['name'] ?? '';
                                        $raw_description = $script['description'] ?? '';
                                        $name            = sbwscf_sanitize_cookie_inline_html( (string) $raw_name );
                                        $description     = wp_kses_post( (string) $raw_description );

                                        if ( '' !== trim( wp_strip_all_tags( $name ) ) && '' !== trim( wp_strip_all_tags( $description ) ) ) :
                                                $input_id = 'sbwscf-optin-' . $i;
                                                ?>
                        <details class="sbwscf-cookie-category">
                                <summary class="sbwscf-cookie-summary">
                                        <span class="sbwscf-cookie-title"><?php echo wp_kses( sbwscf_format_cookie_inline_html( $name ), sbwscf_get_cookie_inline_allowed_tags() ); ?></span>
                                        <span class="sbwscf-cookie-checkbox">
                                                <input type="checkbox" id="<?php echo esc_attr( $input_id ); ?>" data-category="<?php echo esc_attr( sanitize_title( wp_strip_all_tags( $name ) ) ); ?>" />
                                                <label for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Enable', 'smile-basic-web' ); ?></label>
                                        </span>
                                </summary>
                                <div class="sbwscf-cookie-description">
                                                <?php echo wp_kses_post( wpautop( $description ) ); ?>
                                </div>
                        </details>
                                                <?php
                                        endif;
                                endforeach;
                        endif;
			?>
		</div>

		<div class="sbwscf-smile-cookies-buttons">
                        <button
                                class="sbwscf-smile-cookies-accept"
                                data-initial-label="<?php echo esc_attr( $accept_initial_label ); ?>"
                                data-all-label="<?php echo esc_attr( $accept_all_label ); ?>"
                        >
                                <?php echo esc_html( $accept_initial_label ); ?>
                        </button>
                        <button
                                class="sbwscf-smile-cookies-deny"
                                data-initial-label="<?php echo esc_attr( $deny_initial_label ); ?>"
                                data-all-label="<?php echo esc_attr( $deny_all_label ); ?>"
                        >
                                <?php echo esc_html( $deny_initial_label ); ?>
                        </button>
                        <button
                                class="sbwscf-smile-cookies-preferences"
                                data-preferences-label="<?php echo esc_attr( $preferences_label ); ?>"
                                data-accept-preferences-label="<?php echo esc_attr( $accept_preferences_label ); ?>"
                        >
                                <?php echo esc_html( $preferences_label ); ?>
                        </button>
		</div>
		<?php
		/*
		* ------------------------------------------------------------------
		* Mostrar sólo enlaces configurados en Cookies Settings
		* ------------------------------------------------------------------
		*/
		// Obtener los IDs (Yoda condition).
		$cookie_page_id  = absint( get_option( 'sbwscf_cookie_policy_page', 0 ) );
		$privacy_page_id = absint( get_option( 'sbwscf_privacy_policy_page', 0 ) );
		$legal_page_id   = absint( get_option( 'sbwscf_legal_notice_page', 0 ) );
		?>
		<div class="sbwscf-smile-cookies-links">
			<?php if ( 0 < $cookie_page_id ) : ?>
				<a href="<?php echo esc_url( get_permalink( $cookie_page_id ) ); ?>"
				target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Cookies Policy', 'smile-basic-web' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( 0 < $privacy_page_id ) : ?>
				<a href="<?php echo esc_url( get_permalink( $privacy_page_id ) ); ?>"
				target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Privacy Policy', 'smile-basic-web' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( 0 < $legal_page_id ) : ?>
				<a href="<?php echo esc_url( get_permalink( $legal_page_id ) ); ?>"
				target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Legal Notice', 'smile-basic-web' ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>
</div>

	<?php
	$minimized_position = get_option( 'sbwscf_cookie_minimized_position', 'center' );
	$show_label_class   = get_option( 'sbwscf_show_manage_minified_label', '' ) === '1' ? 'show-label' : 'hide-label';
	?>
<div id="sbwscf-manage-consent-container" class="sbwscf-manage-consent-container sbwscf-position-<?php echo esc_attr( $minimized_position ); ?>">
        <button id="sbwscf-manage-consent-btn" class="sbwscf-manage-consent-button <?php echo esc_attr( $show_label_class ); ?>">
                <?php echo esc_html( $manage_button_label ); ?>
        </button>
</div>


	<?php
	// Fin del panel de cookies.
}
