<?php
/**
 * Interfaz para todos los módulos de pestaña en el área de administración.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Interface SBWSCF_Tab_Interface
 */
interface SBWSCF_Tab_Interface {

	/**
	 * Slug único de la pestaña (aparece en la URL ?tab=slug).
	 *
	 * @return string
	 */
	public static function get_slug();

	/**
	 * Etiqueta que se muestra en la pestaña dentro de la UI.
	 *
	 * @return string
	 */
	public static function get_label();

	/**
	 * Registra settings, secciones y campos vía Settings API.
	 *
	 * @return void
	 */
	public static function register_settings();

	/**
	 * Renderiza el contenido completo de la pestaña.
	 *
	 * @return void
	 */
	public static function render();

	/**
	 * Registra los ajustes y controles en el Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Instancia de Customizer.
	 * @return void
	 */
	public static function register_customizer( WP_Customize_Manager $wp_customize );
}
