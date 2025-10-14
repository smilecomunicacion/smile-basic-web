<?php
/**
 * Admin tab: Cookies.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
* ------------------------------------------------------------------
* Includes del módulo Cookies
* ------------------------------------------------------------------
*/
require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/cookies/cookies-settings.php';
require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/cookies/cookies-endpoint.php';

/**
 * Defines the “Cookies” tab in the SMiLE Basic Web plugin.
 */
final class SBWSCF_Cookies_Page implements SBWSCF_Tab_Interface {

	/**
	 * Slug para la URL de admin.
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return 'cookies';
	}

	/**
	 * Etiqueta visible en la UI.
	 *
	 * @return string
	 */
	public static function get_label(): string {
		return esc_html__( 'Cookies', 'smile-basic-web' );
	}

	/**
	 * Registra settings, secciones y campos vía Settings API.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		sbwscf_cookies_register_settings();
	}

	/**
	 * Registra los controles en el Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Instancia de Customizer.
	 * @return void
	 */
	public static function register_customizer( WP_Customize_Manager $wp_customize ): void {
		// No se usan controles de Customizer en esta pestaña.
	}

	/**
	 * Hook de carga: registra assets y acciones propias.
	 *
	 * @return void
	 */
	public static function load(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Class for handling cookies page functionality.
	 *
	 * This class manages the cookies settings page, including rendering the page content,
	 * handling form submissions, and managing cookie-related configurations.
	 *
	 * @param string $hook_suffix The hook suffix for the current admin page.
	 *
	 * @return void
	 */
	public static function enqueue_assets( string $hook_suffix ): void {
		$screen = get_current_screen();

		/*
		 * ------------------------------------------------------------------
		 * Asegura coincidencia con sub-pantallas como
		 * “toplevel_page_smile-basic-web_page_cookies”.
		 * ------------------------------------------------------------------
		 */
		if ( false === strpos( $hook_suffix, 'smile_basic_web' ) ) {
			return;
		}

                wp_enqueue_script(
                        'sbwscf-cookies-admin',
                        esc_url( plugins_url( 'assets/js/sbwscf-cookies-admin.js', __FILE__ ) ),
                        array( 'wp-i18n', 'wp-editor' ),
                        SMILE_BASIC_WEB_VERSION,
                        true
                );

		wp_enqueue_style(
			'sbwscf-cookies-admin-style',
			esc_url( plugins_url( 'assets/css/admin-style.css', __FILE__ ) ),
			array( 'wp-color-picker' ),
			SMILE_BASIC_WEB_VERSION
		);

		wp_enqueue_script(
			'sbwscf-color-sync',
			esc_url( plugins_url( 'assets/js/sbwscf-color-sync.js', __FILE__ ) ),
			array(),
			SMILE_BASIC_WEB_VERSION,
			true
		);

		/*
		* ------------------------------------------------------------------
		* Traducciones para el script JS.
		* ------------------------------------------------------------------
		*/
		wp_set_script_translations(
			'sbwscf-cookies-admin',
			'smile-basic-web',
			plugin_dir_path( __DIR__ ) . '/../../../../languages'
		);
	}

	/**
	 * Encola el CSS para el aviso de cookies en el frontend si está habilitado.
	 *
	 * @return void
	 */
	public static function enqueue_frontend_assets(): void {
		$enabled = get_option( 'sbwscf_enable_cookies_notice' );

		if ( '1' === $enabled ) {
			wp_enqueue_style(
				'sbwscf-cookies-style',
				esc_url( plugins_url( 'assets/css/smile-cookies.css', __FILE__ ) ),
				array(),
				SMILE_BASIC_WEB_VERSION
			);

			wp_enqueue_script(
				'sbwscf-cookies-panel',
				plugins_url( 'assets/js/sbwscf-cookies-panel.js', __FILE__ ),
				array( 'wp-i18n' ),
				SMILE_BASIC_WEB_VERSION,
				false // Importante: cargar antes de </head>.
			);

			/*
			* ------------------------------------------------------------------
			* Traducciones para el script JS.
			* ------------------------------------------------------------------
			*/
			wp_set_script_translations(
				'sbwscf-cookies-panel',
				'smile-basic-web',
				plugin_dir_path( __DIR__ ) . '/../../../../languages'
			);

			// Scripts de preferencias configurados en backend.
			$raw_scripts = get_option( 'sbwscf_tracking_scripts', array() );
			$prepared    = array();

			if ( is_array( $raw_scripts ) ) {
				foreach ( $raw_scripts as $script ) {
					if ( '' !== $script['name'] && '' !== $script['code'] ) {
						$prepared[] = array(
							'category' => sanitize_title( $script['name'] ),
							'code'     => $script['code'],
						);
					}
				}
			}

			wp_localize_script(
				'sbwscf-cookies-panel',
				'sbwscfCookieScripts',
				array( 'scripts' => $prepared )
			);
		}
	}


	/**
	 * Renderiza el contenido de la pestaña.
	 *
	 * @return void
	 */
	public static function render(): void {
		?>
<div class="wrap sbwscf-cookies-container">
	<h1><?php esc_html_e( 'Cookies Settings', 'smile-basic-web' ); ?></h1>
	<form action="options.php" method="post">
		<?php
				settings_fields( 'sbwscf_cookies' );
				do_settings_sections( 'sbwscf_cookies' );
				submit_button();
		?>
	</form>
</div>
		<?php
	}
}

// Hook para inicializar el tab (backend y frontend).
add_action( 'init', array( 'SBWSCF_Cookies_Page', 'load' ) );
