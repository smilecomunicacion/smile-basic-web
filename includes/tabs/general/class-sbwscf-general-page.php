<?php
/**
 * Admin tab: General.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Helpers propios de este módulo.
require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/general/general-settings.php';

/**
 * Defines the “General” tab in the SMiLE Basic Web plugin.
 */
final class SBWSCF_General_Page implements SBWSCF_Tab_Interface {

	/**
	 * Slug para la URL de admin.
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return 'general';
	}

	/**
	 * Etiqueta visible en el menú de pestañas.
	 *
	 * @return string
	 */
	public static function get_label(): string {
		return esc_html__( 'General', 'smile-basic-web' );
	}

	/**
	 * Registra settings, secciones y campos vía Settings API.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		// Llama a la función que ya añade secciones y campos.
		sbwscf_general_register_settings();
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

                $options = get_option( 'sbwscf_general_settings', array() );
                if ( ! empty( $options['enable_svg'] ) ) {
                        // Activa el soporte SVG:.
                        require_once __DIR__ . '/svg-upload.php';
                        SBWSCF_SVG_Upload::init();
                }

                if ( ! empty( $options['enable_alt'] ) ) {
                        require_once __DIR__ . '/alt-text-upload.php';
                        SBWSCF_Auto_Alt_Text::init();
                }

                if ( ! empty( $options['enable_metadata'] ) ) {
                        require_once __DIR__ . '/metadata-meta-box.php';
                        SBWSCF_Metadata_Meta_Box::init();
                }
        }

        /**
         * Boot front-end specific features for the General tab.
         *
         * @return void
         */
        public static function boot_frontend_features(): void {
                if ( is_admin() ) {
                        return;
                }

                $options = get_option( 'sbwscf_general_settings', array() );
                if ( empty( $options['enable_metadata'] ) ) {
                        return;
                }

                require_once __DIR__ . '/metadata-meta-box.php';
                SBWSCF_Metadata_Meta_Box::init_frontend();
        }

	/**
	 * Encola CSS y JS sólo cuando estamos en la pestaña General.
	 *
	 * @return void
	 */
	public static function enqueue_assets(): void {
		$screen = get_current_screen();
		if ( 'toplevel_page_smile-basic-web' !== $screen->base || self::get_slug() !== $screen->id ) {
			return;
		}
	}

	/**
	 * Renderiza el contenido de la pestaña.
	 *
	 * @return void
	 */
	public static function render(): void {
		?>
		<div class="wrap sbwscf-general-container">
			<h1><?php esc_html_e( 'General Settings', 'smile-basic-web' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// Registra nonce y campos.
				settings_fields( 'sbwscf_general' );
				do_settings_sections( 'sbwscf_general' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}


// Hook para invocar load() tras inicializar Tab Manager.
add_action( 'admin_init', array( 'SBWSCF_General_Page', 'load' ) );
add_action( 'init', array( 'SBWSCF_General_Page', 'boot_frontend_features' ) );
