<?php
/**
 * Class SBWSCF_Tab_Manager
 *
 * Discovers, registers and renders every admin-tab that implements
 * SBWSCF_Tab_Interface.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Handles the tab life-cycle (discovery → register_settings → render).
 */
class SBWSCF_Tab_Manager {

	/**
	 * Fully-qualified class names of discovered tabs.
	 *
	 * @var string[]
	 */
	private static $tabs = array();

	/**
	 * Boots the tab manager.
	 *
	 * Must be called before the 'admin_menu' hook fires.
	 *
	 * @return void
	 */
	public static function init() {
		self::discover_tabs( SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/' );

		// Menu principal y Settings API.
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_all_settings' ) );

		// Customizer: invoca register_customizer() de cada módulo.
		add_action( 'customize_register', array( __CLASS__, 'customize_all_tabs' ) );
	}

	/**
	 * Recursively carga cada 'class-*.php' y almacena clases que implementen la interfaz.
	 *
	 * @param string $dir Ruta absoluta a escanear.
	 * @return void
	 */
	private static function discover_tabs( $dir ) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( trailingslashit( $dir ) )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && 0 === strpos( $file->getFilename(), 'class-' ) ) {
				require_once $file->getPathname();
			}
		}

		foreach ( get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, 'SBWSCF_Tab_Interface' ) ) {
				self::$tabs[] = $class;
			}
		}

		// Orden alfabético por etiqueta para UI predecible.
		usort(
			self::$tabs,
			static function ( $a, $b ) {
				return strcmp(
					call_user_func( array( $a, 'get_label' ) ),
					call_user_func( array( $b, 'get_label' ) )
				);
			}
		);
		/**
		 * Allows filtering / re-ordering of the final tab list.
		 *
		 * @param string[] $tabs Ordered list of FQCNs implementing the tab.
		 */
		self::$tabs = apply_filters( 'sbwscf_tabs_order', self::$tabs );
	}

	/**
	 * Invokes register_settings() on every discovered tab.
	 *
	 * @return void
	 */
	public static function register_all_settings() {
		foreach ( self::$tabs as $tab ) {
			call_user_func( array( $tab, 'register_settings' ) );
		}
	}

	/**
	 * Registers the top-level “SMiLE Basic Web” admin menu and sub-tabs.
	 *
	 * @return void
	 */
	public static function add_menu_page() {
		add_menu_page(
			esc_html__( 'SMiLE Basic Web', 'smile-basic-web' ),
			esc_html__( 'SMiLE Basic Web', 'smile-basic-web' ),
			'manage_options',
			'smile_basic_web',
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-generic',
			80
		);
	}

	/**
	 * Outputs the page header, tabs and the currently selected tab content.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smile-basic-web' ) );
		}

		$requested_raw = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		/**
		 * Slug de la primera pestaña (alfabéticamente), antes de aplicar filtro.
		 */
		$default = call_user_func( array( self::$tabs[0], 'get_slug' ) );

		/**
		 * ¿Qué pestaña cargar si no hay ?tab=…?
		 * Permitimos sobreescribirlo con sbwscf_default_tab.
		 *
		 * @param string $default_slug Slug por defecto (primera pestaña).
		 */
		$current = $requested_raw
			? sanitize_key( $requested_raw )
			: apply_filters( 'sbwscf_default_tab', $default );

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SMiLE Basic Web', 'smile-basic-web' ) . '</h1>';
		echo '<h2 class="nav-tab-wrapper">';

		foreach ( self::$tabs as $tab ) {
			$slug   = call_user_func( array( $tab, 'get_slug' ) );
			$label  = call_user_func( array( $tab, 'get_label' ) );
			$url    = esc_url( admin_url( 'admin.php?page=smile_basic_web&tab=' . $slug ) );
			$active = ( $slug === $current ) ? ' nav-tab-active' : '';

			printf(
				'<a class="nav-tab%s" href="%s">%s</a>',
				esc_attr( $active ),
				esc_url( $url ),
				esc_html( $label )
			);
		}

		echo '</h2><div class="tab-content">';

		foreach ( self::$tabs as $tab ) {
			if ( call_user_func( array( $tab, 'get_slug' ) ) === $current ) {
				call_user_func( array( $tab, 'render' ) );
				break;
			}
		}

		echo '</div></div>';
	}

	/**
	 * Recorrer todos los tabs y, si implementan register_customizer(), invocarlo.
	 *
	 * @param WP_Customize_Manager $wp_customize Instancia de Customizer.
	 * @return void
	 */
	public static function customize_all_tabs( WP_Customize_Manager $wp_customize ) {
		foreach ( self::$tabs as $tab ) {
			if ( method_exists( $tab, 'register_customizer' ) ) {
				call_user_func( array( $tab, 'register_customizer' ), $wp_customize );
			}
		}
	}
}

// Boot the Tab-Manager after init (priority > 0 so textdomain esté listo).
add_action( 'init', array( 'SBWSCF_Tab_Manager', 'init' ), 20 );
