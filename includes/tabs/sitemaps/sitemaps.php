<?php
/**
 * Core logic for llms.txt, sitemaps and robots endpoints.
 *
 * @package smile-basic-web
 */

defined( 'ABSPATH' ) || exit; // Do not access directly.

/*
* ------------------------------------------------------------------
*   Early-exit for AJAX / REST requests
* ------------------------------------------------------------------
*
*   Media uploads usan async-upload.php (define DOING_AJAX)
*   y el editor de bloques usa la REST API (define REST_REQUEST).
*   Estas peticiones no necesitan cargar reglas ni ajustes
*   — evita sobre-carga y el error “The server cannot process…”.
*/
if ( ( defined( 'DOING_AJAX' ) && true === DOING_AJAX )
	|| ( defined( 'REST_REQUEST' ) && true === REST_REQUEST ) ) {
	return;
}

/*
* ------------------------------------------------------------------
*   Constantes y sanitizadores
* ------------------------------------------------------------------
*/
/**
 * Sanitize callback para arrays vía register_setting().
 *
 * @param mixed $value Valor entrante.
 * @return array Valor saneado.
 */
function sbwscf_llms_sanitize_array( $value ): array {
	return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
}
// Alias para mantener compatibilidad con código existente.
if ( ! defined( 'SBWSCF_LLMS_SANITIZE_ARGS' ) ) {
	define( 'SBWSCF_LLMS_SANITIZE_ARGS', 'sbwscf_llms_sanitize_array' );
}

/*
* ------------------------------------------------------------------
*   Registro de ajustes (solo en pantallas admin normales)
* ------------------------------------------------------------------
*/
if ( is_admin() ) {

	/**
	 * Registra opciones de la pestaña “Sitemaps”.
	 *
	 * @return void
	 */
	function sbwscf_llms_register_settings(): void {

		$group = 'sbwscf_sitemaps_group';

		register_setting( $group, 'sbwscf_generate', SBWSCF_LLMS_SANITIZE_ARGS );
		register_setting( $group, 'sbwscf_output_format', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_word_limit', 'absint' );
		register_setting( $group, 'sbwscf_title', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_description', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_author', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_content_types', SBWSCF_LLMS_SANITIZE_ARGS );
		register_setting( $group, 'sbwscf_priority_category', 'sanitize_text_field' );
	}
	add_action( 'admin_init', 'sbwscf_llms_register_settings' );
}

/*
* ------------------------------------------------------------------
*   Rewrite rules y query vars
* ------------------------------------------------------------------
*/

/**
 * Brief description of the function's purpose.
 *
 * Detailed explanation of the function if needed.
 *
 * @param array $vars An associative array containing the necessary variables.
 *
 * @return mixed Description of the return value.
 */
function sbwscf_llms_query_vars( array $vars ): array {
	$extra = array( 'sbwscf_llms', 'sbwscf_sitemap', 'sbwscf_images', 'sbwscf_robots' );
	return array_merge( $vars, $extra );
}
add_filter( 'query_vars', 'sbwscf_llms_query_vars' );

/**
 * Registra las reglas de reescritura.
 *
 * @return void
 */
function sbwscf_llms_add_rewrite_rules(): void {
	add_rewrite_rule( '^llms\.(txt|json)$', 'index.php?sbwscf_llms=1', 'top' );
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?sbwscf_sitemap=1', 'top' );
	add_rewrite_rule( '^sitemap-images\.xml$', 'index.php?sbwscf_images=1', 'top' );
	add_rewrite_rule( '^robots\.txt$', 'index.php?sbwscf_robots=1', 'top' );
}
add_action( 'init', 'sbwscf_llms_add_rewrite_rules' );

/**
 * Flush reglas al activar el plugin.
 *
 * @return void
 */
function sbwscf_llms_rewrite_flush(): void {
	sbwscf_llms_add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( SMILE_BASIC_WEB_PLUGIN_FILE, 'sbwscf_llms_rewrite_flush' );

/*
* ------------------------------------------------------------------
*   Endpoints (front-end) – solo template_redirect
* ------------------------------------------------------------------
*/
/**
 * Brief description of what the function does.
 *
 * This function performs its task by processing the input parameters and
 * returning the desired output. You can elaborate on the overall behavior,
 * expected usage, and any specific notes or details relevant to its implementation.
 *
 * @return mixed Description of the return value.
 */
function sbwscf_llms_template_redirect(): void {

	$enabled = (array) get_option( 'sbwscf_generate', array( 'llms' ) );

	if ( 1 === (int) get_query_var( 'sbwscf_llms', 0 ) && in_array( 'llms', $enabled, true ) ) {
		include SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/llms-endpoint.php';
		exit;
	}

	if ( 1 === (int) get_query_var( 'sbwscf_sitemap', 0 ) && in_array( 'sitemap', $enabled, true ) ) {
		include SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/sitemap-endpoint.php';
		exit;
	}

	if ( 1 === (int) get_query_var( 'sbwscf_images', 0 ) && in_array( 'images', $enabled, true ) ) {
		include SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/sitemap-images-endpoint.php';
		exit;
	}

	if ( 1 === (int) get_query_var( 'sbwscf_robots', 0 ) && in_array( 'robots', $enabled, true ) ) {
		include SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/robots-endpoint.php';
		exit;
	}
}
add_action( 'template_redirect', 'sbwscf_llms_template_redirect' );

/*
* ------------------------------------------------------------------
*   Invalida caché del sitemap-de-imágenes al crear / borrar adjuntos
* ------------------------------------------------------------------
*/
/**
 * Elimina la entrada de caché cuando cambia la biblioteca.
 *
 * @return void
 */
function sbwscf_invalidate_image_sitemap_cache(): void {
	wp_cache_delete( 'sbwscf_sitemap_images_xml_v1', 'sbwscf' );
}
add_action( 'add_attachment', 'sbwscf_invalidate_image_sitemap_cache' );
add_action( 'delete_attachment', 'sbwscf_invalidate_image_sitemap_cache' );
add_action( 'save_post', 'sbwscf_invalidate_image_sitemap_cache' );
