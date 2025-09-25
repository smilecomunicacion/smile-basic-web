<?php
/**
 * Plugin Name: SMiLE Basic Web
 * Description: Modular plugin bundling the Contact-Form and Sitemaps features.
 * Version:     1.3.3
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:      smilecomunicacion
 * Author URI:  https://smilecomunicacion.com/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smile-basic-web
 * Domain Path: /languages
 *
 * @package smile-basic-web
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/** -----------------------------------------------------------------------
 *  Core constants
 * -------------------------------------------------------------------- */
define( 'SMILE_BASIC_WEB_PLUGIN_FILE', __FILE__ );
define( 'SMILE_BASIC_WEB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SMILE_BASIC_WEB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMILE_BASIC_WEB_VERSION', '1.3.3' );

/**
 * Main plugin singleton.
 *
 * @package smile-basic-web
 */
final class SMiLE_Basic_Web {

	/**
	 * Holds the single instance.
	 *
	 * @var SMiLE_Basic_Web|null
	 */
	private static ?SMiLE_Basic_Web $instance = null;

	/**
	 * Returns or creates the instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) { // Yoda.
			self::$instance = new self();
		}
		return self::$instance;
	}

        /**
         * Constructor: includes and hooks.
         */
        private function __construct() {
                $this->includes();
                $this->init_hooks();
        }

	/**
	 * Loads indispensable files.
	 *
	 * @return void
	 */
	private function includes(): void {
                // Core interface & manager.
                require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/core/interface-tab.php';
                require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/core/class-sbwscf-tab-manager.php';
                // General tab class (needed early for front-end hooks).
                require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/general/class-sbwscf-general-page.php';
                // Contact-Form tab class.
                require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/contact-form/class-sbwscf-contactform-page.php';
                // Sitemaps core – always load so rewrite rules persist.
                require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/sitemaps.php';
		// Carga del tab “Cookies”.
		require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/cookies/class-sbwscf-cookies-page.php';
	}

	/**
	 * Hooks: activation + Tab-Manager.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		register_activation_hook(
			SMILE_BASIC_WEB_PLUGIN_FILE,
			array( __CLASS__, 'activate_plugin' )
		);

                // Boot the Tab-Manager.
		add_action( 'init', array( 'SBWSCF_Tab_Manager', 'init' ), 20 );
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate_plugin(): void {

		// 1. Create or locate the Email-Preview draft page.
		$title     = 'sbwscf-customizer-email-preview';
		$cache_key = 'sbwscf_page_by_title_' . md5( $title );
		$page_id   = wp_cache_get( $cache_key, 'sbwscf' );

		if ( false === $page_id ) { // Yoda.
			$page_id = self::find_page_by_exact_title( $title );
		}

		if ( 0 === $page_id ) { // Yoda.
			$new_page_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => '[sbwscf_email_preview]',
					'post_status'  => 'draft',
					'post_type'    => 'page',
				)
			);

			if ( ! is_wp_error( $new_page_id ) ) {
				$page_id = absint( $new_page_id );
			}
		}

		if ( 0 !== $page_id ) { // Yoda.
			update_option( 'sbwscf_email_preview_page_id', $page_id );
			wp_cache_set( $cache_key, $page_id, 'sbwscf', HOUR_IN_SECONDS );
		}

		// 2. Delegate Contact-Form specific defaults.
		if ( class_exists( 'SBWSCF_ContactForm_Page' ) ) {
			SBWSCF_ContactForm_Page::activate();
		}

		// 3. Register & flush Sitemaps rewrite rules.
		self::register_sitemap_rules_on_activation();
	}

	/*
	---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------
	 */

	/**
	 * Finds a page by exact title (avoiding deprecated get_page_by_title()).
	 *
	 * @param string $title Exact page title.
	 * @return int          Page ID or 0.
	 */
	private static function find_page_by_exact_title( string $title ): int {
		if ( '' === $title ) { // Yoda.
			return 0;
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'title'          => $title,
				'posts_per_page' => 1,
				'no_found_rows'  => true,
			)
		);

		if ( $query->have_posts() ) { // Yoda.
			$id = (int) $query->posts[0]->ID;
			wp_reset_postdata();
			return $id;
		}

		wp_reset_postdata();
		return 0;
	}

	/**
	 * Ensure Sitemaps rewrite rules are added and flushed on activation.
	 *
	 * @return void
	 */
	private static function register_sitemap_rules_on_activation(): void {
		// Load only the helper part (rules/functions) of Sitemaps.
		require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/sitemaps.php';

		if ( function_exists( 'sbwscf_llms_add_rewrite_rules' ) ) {
			sbwscf_llms_add_rewrite_rules();
		}

		flush_rewrite_rules();
	}
}

/*
* -------------------------------------------------------------------------
* Boot plugin.
* -------------------------------------------------------------------------
*/
SMiLE_Basic_Web::get_instance();

/*
 * ------------------------------------------------------------------
 * 1) Default tab when no ?tab= is present.
 * ------------------------------------------------------------------
 */
add_filter(
	'sbwscf_default_tab',
	function ( string $default_slug ): string {
		// Use the incoming value to avoid PHPCS “unused” warning.
		return ( 'general' === $default_slug ) ? $default_slug : 'general';
	}
);

/*
 * ------------------------------------------------------------------
 * 2) Move the "General" tab to the first position in the list.
 * ------------------------------------------------------------------
 */
add_filter(
	'sbwscf_tabs_order',
	function ( array $tabs ): array {
		$general_class = 'SBWSCF_General_Page';

		if ( in_array( $general_class, $tabs, true ) ) {
			// Remove the class and prepend it at the beginning.
			$tabs = array_diff( $tabs, array( $general_class ) );
			array_unshift( $tabs, $general_class );
		}

		return $tabs;
	}
);
