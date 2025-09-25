<?php
/**
 * Endpoint: robots.txt
 *
 * Entrega un robots.txt dinámico con reglas de bloqueo, lista de “bad bots”
 * y enlaces a los distintos sitemaps que genera la pestaña Sitemaps.
 *
 * @package smile-basic-web
 */

defined( 'ABSPATH' ) || exit; // Do not access directly.

/*
 * ------------------------------------------------------------------
 * Helper compartido para formato de fecha UTC.
 * ------------------------------------------------------------------
 */
if ( ! function_exists( 'sbwscf_format_date_utc' ) ) :
	/**
	 * Devuelve una fecha UTC en YYYY‑MM‑DD.
	 *
	 * @param int|null $timestamp Epoch seg; usa time() cuando es null.
	 * @return string Fecha formateada.
	 */
	function sbwscf_format_date_utc( ?int $timestamp = null ): string {
		return gmdate( 'Y-m-d', $timestamp ?? time() );
	}
endif;

/*
 * ------------------------------------------------------------------
 * Renderizado de robots.txt (1 h de caché).
 * ------------------------------------------------------------------
 */
if ( ! function_exists( 'sbwscf_llms_render_robots_endpoint' ) ) :
	/**
	 * Muestra robots.txt, sirviéndolo desde la caché cuando sea posible.
	 *
	 * @return void
	 */
	function sbwscf_llms_render_robots_endpoint(): void {

		$cache_key = 'sbwscf_robots_txt_v2';
		$robots    = wp_cache_get( $cache_key, 'sbwscf' );

		if ( false === $robots ) { // Yoda.
			$robots = sbwscf_build_robots_txt();
			wp_cache_set( $cache_key, $robots, 'sbwscf', HOUR_IN_SECONDS );
		}

		header( 'Content-Type: text/plain; charset=utf-8' );
		/* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
		echo $robots;
	}
endif;

/*
 * ------------------------------------------------------------------
 * Constructor del contenido de robots.txt.
 * ------------------------------------------------------------------
 */

if ( ! function_exists( 'sbwscf_build_robots_txt' ) ) :
	/**
	 * Construye el texto completo de robots.txt.
	 *
	 * @return string Robots.txt.
	 */
	function sbwscf_build_robots_txt(): string {

		$lines = array();

		/*
		 * ------------------------------------------------------------------
		 * 1. Cabecera de generador.
		 * ------------------------------------------------------------------
		 */
		/* translators: %s is the URL used to indicate the generator. */
		$generator_url = 'https://smilecomunicacion.com';
		$lines[]       = '# Created by ' . esc_url( $generator_url );
		$lines[]       = '# ' . sbwscf_format_date_utc();
		$lines[]       = '';

		/*
		 * ------------------------------------------------------------------
		 * 2. Reglas básicas de bloqueo y permiso.
		 * ------------------------------------------------------------------
		 */
		$lines[] = '# Basic blocking for bots and crawlers';
		$lines[] = 'User-agent: *';
		$lines[] = 'Allow: /llms.txt';
		$lines[] = 'Allow: /wp-content/uploads/';
		$lines[] = 'Allow: /*.js$';
		$lines[] = 'Allow: /*.css$';
		$lines[] = 'Disallow: /cgi-bin/';
		$lines[] = 'Disallow: /wp-content/plugins/';
		$lines[] = 'Disallow: /wp-content/themes/';
		$lines[] = 'Disallow: /wp-includes/';
		$lines[] = 'Disallow: /*/attachment/';
		$lines[] = 'Disallow: /author/';
		$lines[] = 'Disallow: /*/page/';
		$lines[] = 'Disallow: /tag/*/page/';
		$lines[] = 'Disallow: /tag/*/feed/';
		$lines[] = 'Disallow: /page/';
		$lines[] = 'Disallow: /comments/';
		$lines[] = 'Disallow: /xmlrpc.php';
		$lines[] = 'Disallow: /?attachment_id';
		$lines[] = 'Disallow: /*?';
		$lines[] = '';

		/*
		 * ------------------------------------------------------------------
		 * 3. Trackbacks.
		 * ------------------------------------------------------------------
		 */
		$lines[] = '# Trackbacks';
		$lines[] = 'Disallow: /trackback';
		$lines[] = 'Disallow: /*trackback';
		$lines[] = 'Disallow: /*trackback*';
		$lines[] = 'Disallow: /*/trackback';
		$lines[] = '';

		/*
		 * ------------------------------------------------------------------
		 * 4. Feeds para crawlers.
		 * ------------------------------------------------------------------
		 */
		$lines[] = '# Feeds for crawlers';
		$lines[] = 'Allow: /feed/$';
		$lines[] = 'Disallow: /feed/';
		$lines[] = 'Disallow: /comments/feed/';
		$lines[] = 'Disallow: /*/feed/$';
		$lines[] = 'Disallow: /*/feed/rss/$';
		$lines[] = 'Disallow: /*/trackback/$';
		$lines[] = 'Disallow: /*/*/feed/$';
		$lines[] = 'Disallow: /*/*/feed/rss/$';
		$lines[] = 'Disallow: /*/*/trackback/$';
		$lines[] = 'Disallow: /*/*/*/feed/$';
		$lines[] = 'Disallow: /*/*/*/feed/rss/$';
		$lines[] = 'Disallow: /*/*/*/trackback/$';
		$lines[] = '';

		/*
		 * ------------------------------------------------------------------
		 * 5. Lista de “bad bots”.
		 * ------------------------------------------------------------------
		 */
		$default_bad_bots = array(
			'MSIECrawler',
			'WebCopier',
			'HTTrack',
			'Microsoft.URL.Control',
			'libwww',
			'Orthogaffe',
			'UbiCrawler',
			'DOC',
			'Zao',
			'sitecheck.internetseer.com',
			'Zealbot',
			'SiteSnagger',
			'WebStripper',
			'Fetch',
			'Offline Explorer',
			'Teleport',
			'TeleportPro',
			'WebZIP',
			'linko',
			'Xenu',
			'larbin',
			'ZyBORG',
			'Download Ninja',
			'wget',
			'grub-client',
			'k2spider',
			'NPBot',
			'WebReaper',
		);

		/**
		 * Permite modificar la lista de bad bots.
		 *
		 * @param string[] $bots Lista de user-agents a bloquear.
		 */
		$bad_bots = apply_filters( 'sbwscf_bad_bots', $default_bad_bots );

		$lines[] = '# Bad bots';
		foreach ( $bad_bots as $bot ) {
			$lines[] = 'User-agent: ' . esc_attr( $bot );
			$lines[] = 'Disallow: /';
		}
		$lines[] = '';

		/*
		 * ------------------------------------------------------------------
		 * 6. Googlebot – recursos permitidos.
		 * ------------------------------------------------------------------
		 */
		$lines[] = '# Googlebot resources';
		$lines[] = 'User-agent: Googlebot';
		$lines[] = 'Allow: /*.css$';
		$lines[] = 'Allow: /*.js$';
		$lines[] = '';

		/*
		 * ------------------------------------------------------------------
		 * 7. Sitemaps dinámicos.
		 * ------------------------------------------------------------------
		 */
		$base_url      = untrailingslashit( site_url() );
		$enabled_files = (array) get_option( 'sbwscf_generate', array( 'llms' ) );

		$lines[] = '# Sitemaps';

		if ( in_array( 'sitemap', $enabled_files, true ) ) {
			$lines[] = 'Sitemap: ' . esc_url( $base_url . '/sitemap.xml' );
		}
		if ( in_array( 'images', $enabled_files, true ) ) {
			$lines[] = 'Sitemap: ' . esc_url( $base_url . '/sitemap-images.xml' );
		}
		if ( in_array( 'llms', $enabled_files, true ) ) {
			$lines[] = 'Sitemap: ' . esc_url( $base_url . '/llms.txt' );
		}

		/*
		 * ------------------------------------------------------------------
		 * 8. Líneas adicionales.
		 * ------------------------------------------------------------------
		 */
		/**
		 * Permite añadir líneas extra al final.
		 *
		 * @param string[] $extra_lines Cada línea adicional.
		 */
		$extra_lines = apply_filters( 'sbwscf_robots_extra_lines', array() );
		if ( ! empty( $extra_lines ) && is_array( $extra_lines ) ) {
			$lines = array_merge( $lines, array_map( 'trim', $extra_lines ) );
		}

		return implode( "\n", $lines ) . "\n";
	}
endif;

// -----------------------------------------------------------------------------
// Ejecutar y terminar petición.
// -----------------------------------------------------------------------------
sbwscf_llms_render_robots_endpoint();
exit;
