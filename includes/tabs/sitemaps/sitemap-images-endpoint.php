<?php
/**
 * Endpoint: sitemap-images.xml generator.
 *
 * Generates a Google-compatible **image** sitemap.
 * Each <url> represents a public post or page and includes every
 * <image:image> found (featured image, images in Gutenberg blocks,
 * classic <img> tags that point to the same domain, etc.).
 *
 * @package smile-basic-web
 */

defined( 'ABSPATH' ) || exit; // No direct access.

/*
* ------------------------------------------------------------------
* Constants
* ------------------------------------------------------------------
*/
const SBWSCF_IMAGES_SITEMAP_CACHE_KEY = 'sbwscf_sitemap_images_xml';

/*
* ------------------------------------------------------------------
* Helper – ISO-8601 UTC formatter
* ------------------------------------------------------------------
*/
/**
 * Return a UTC ISO-8601 date-time string (YYYY-MM-DDThh:mm:ss+00:00).
 *
 * @param int|null $timestamp Unix epoch. Current time when null.
 * @return string  ISO-8601 in UTC.
 */
function sbwscf_iso_utc( ?int $timestamp = null ): string {
	return gmdate( 'c', $timestamp ?? time() );
}

/*
* ------------------------------------------------------------------
* Main renderer (1 h object-cache)
* ------------------------------------------------------------------
*/
/**
 * Echo the cached or freshly built sitemap and stop execution.
 *
 * @return void
 */
function sbwscf_render_sitemap_images_xml(): void {

	$xml = wp_cache_get( SBWSCF_IMAGES_SITEMAP_CACHE_KEY, 'sbwscf' );

	if ( false === $xml ) { // Yoda.
		$xml = sbwscf_build_sitemap_images_xml();
		wp_cache_set( SBWSCF_IMAGES_SITEMAP_CACHE_KEY, $xml, 'sbwscf', HOUR_IN_SECONDS );
	}

	// Ensure nothing is printed before the XML declaration.
	while ( ob_get_level() ) {
		ob_end_clean();
	}

	nocache_headers();
	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, follow', true );

	echo ltrim( $xml ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/*
* ------------------------------------------------------------------
* Builder – loops through posts/pages and discovers images
* ------------------------------------------------------------------
*/
/**
 * Build the <urlset> by batching published posts/pages and parsing images.
 *
 * @return string XML markup.
 */
function sbwscf_build_sitemap_images_xml(): string {

	$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<!--  created by https://SMiLEcomunicacion.com  -->' . "\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
	$xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

	/* -- Content-types enabled in plugin settings ------------------- */
	$enabled_types = (array) get_option( 'sbwscf_content_types', array( 'post' ) );
	$post_types    = array_intersect( $enabled_types, array( 'post', 'page' ) );
	if ( empty( $post_types ) ) {
		$post_types = array( 'post' ); // Fallback.
	}

	$batch   = (int) apply_filters( 'sbwscf_images_batch_size', 50 );
	$paged   = 1;
	$siteurl = site_url();

	do {
		$query = new WP_Query(
			array(
				'post_type'              => $post_types,
				'post_status'            => 'publish',
				'posts_per_page'         => $batch,
				'paged'                  => $paged,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			)
		);

		if ( ! $query->have_posts() ) {
			break;
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			$post_id   = get_the_ID();
			$permalink = get_permalink( $post_id );
			$lastmod   = sbwscf_iso_utc( (int) get_post_modified_time( 'U', true ) );

			$image_ids = array();

			/* -- Featured image ------------------------------------ */
			$thumb_id = get_post_thumbnail_id( $post_id );
			if ( 0 !== $thumb_id ) { // Yoda.
				$image_ids[] = $thumb_id;
			}

			/* -- wp-image-123 in Gutenberg blocks ------------------ */
			$content = get_post_field( 'post_content', $post_id );
			if ( preg_match_all( '/wp-image-(\\d+)/', $content, $m_wp ) ) {
				$image_ids = array_merge( $image_ids, array_map( 'intval', $m_wp[1] ) );
			}

			/* -- <img src=\"…\"> classic tags (same domain) -------- */
			if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m_img ) ) {
				foreach ( $m_img[1] as $src ) {

					// Skip external URLs.
					if ( 0 !== strpos( $src, $siteurl ) ) {
						continue;
					}

					$maybe_id = attachment_url_to_postid( $src );
					if ( false !== $maybe_id && 0 !== $maybe_id ) { // Yoda.
						$image_ids[] = $maybe_id;
					}
				}
			}

			$image_ids = array_values( array_unique( array_filter( $image_ids ) ) );
			if ( empty( $image_ids ) ) {
				continue; // No images → skip URL entry.
			}

			$xml .= "  <url>\n";
			$xml .= '    <loc>' . esc_url( $permalink ) . "</loc>\n";
			$xml .= '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";

			foreach ( $image_ids as $img_id ) {
				$img_url = wp_get_attachment_url( $img_id );
				if ( ! $img_url ) {
					continue;
				}

				$xml .= "    <image:image>\n";
				$xml .= '      <image:loc>' . esc_url( $img_url ) . "</image:loc>\n";
				$xml .= '      <image:title>' .
					esc_html( get_the_title( $img_id ) ) . "</image:title>\n";
				$xml .= "    </image:image>\n";
			}

			$xml .= "  </url>\n";
		}

		++$paged;
		wp_reset_postdata();
	} while ( true );

	$xml .= '</urlset>';

	return $xml;
}

/*
* ------------------------------------------------------------------
* Kick-off immediately (included only on /sitemap-images.xml)
* ------------------------------------------------------------------
*/
sbwscf_render_sitemap_images_xml();
