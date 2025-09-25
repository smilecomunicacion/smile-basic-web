<?php
/**
 * Endpoint: sitemap.xml generator.
 *
 * Outputs an XML sitemap with homepage, posts, pages, products, categories,
 * and tags per settings.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
* ------------------------------------------------------------------
* Date formatter – UTC ISO-8601 with time
* ------------------------------------------------------------------
*/
/**
 * Format a Unix timestamp as a UTC ISO-8601 date-time string.
 *
 * @param int|null $timestamp Unix epoch; current time when null.
 * @return string             ISO-8601 date-time (YYYY-MM-DDThh:mm:ss+00:00).
 */
if ( ! function_exists( 'sbwscf_llms_format_date' ) ) {
	/**
	 * Formats a timestamp into a human-readable date string.
	 *
	 * If no timestamp is provided, the current time is used by default.
	 *
	 * @param int|null $timestamp Optional. The Unix timestamp to format, or null to use the current time.
	 * @return string The formatted date string.
	 */
	function sbwscf_llms_format_date( ?int $timestamp = null ): string {
		return gmdate( 'c', $timestamp ?? time() );
	}
}

/*
* ------------------------------------------------------------------
* Render sitemap.xml endpoint
* ------------------------------------------------------------------
*/
/**
 * Render the sitemap.xml endpoint.
 *
 * @return void
 */
if ( ! function_exists( 'sbwscf_llms_render_sitemap_endpoint' ) ) :
	/**
	 * Render sitemap endpoint.
	 *
	 * This function is responsible for outputting the sitemap endpoint which is used for generating or
	 * displaying the sitemap structure of the website. The resulting output assists in site navigation
	 * and indexing by search engines.
	 *
	 * @return void
	 */
	function sbwscf_llms_render_sitemap_endpoint(): void {

		// Clean any prior output to avoid whitespace before XML.
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: application/xml; charset=utf-8' );

		// XML prologue.
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<!-- created by https://SMiLEcomunicacion.com -->' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		// Track URLs we've already printed, to avoid duplicates.
		$seen_locs = array();

		// Read enabled sections.
		$enabled_types = (array) get_option( 'sbwscf_content_types', array( 'homepage', 'post', 'page' ) );

		// ------------------------------------------------------------------
		// 1. Homepage (if enabled)
		// ------------------------------------------------------------------
		if ( in_array( 'homepage', $enabled_types, true ) ) {
			$home_loc = home_url( '/' );
			$home_mod = sbwscf_llms_format_date( (int) get_option( 'page_on_front' ) ? get_post_modified_time( 'U', true, get_option( 'page_on_front' ) ) : null );
			if ( ! in_array( $home_loc, $seen_locs, true ) ) {
				$seen_locs[] = $home_loc;
				echo '<url>' . "\n";
				echo '  <loc>' . esc_url( $home_loc ) . '</loc>' . "\n";
				echo '  <lastmod>' . esc_html( $home_mod ) . '</lastmod>' . "\n";
				echo '  <changefreq>weekly</changefreq>' . "\n";
				echo '  <priority>1.0</priority>' . "\n";
				echo '</url>' . "\n";
			}
		}

		// ------------------------------------------------------------------
		// 2. Posts, Pages & Products
		// ------------------------------------------------------------------
		$post_types = array( 'post' );
		if ( in_array( 'page', $enabled_types, true ) ) {
			$post_types[] = 'page';
		}
		if ( in_array( 'product', $enabled_types, true ) && post_type_exists( 'product' ) ) {
			$post_types[] = 'product';
		}

		$args      = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);
		$cache_key = 'sbwscf_sitemap_' . md5( wp_json_encode( $args ) );
		$items     = wp_cache_get( $cache_key, 'sbwscf_llms' );

		if ( false === $items ) {
			$query = new WP_Query( $args );
			$items = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$items[] = array(
						'loc'     => get_permalink(),
						'lastmod' => sbwscf_llms_format_date( (int) get_post_modified_time( 'U', true ) ),
					);
				}
				wp_reset_postdata();
			}

			wp_cache_set( $cache_key, $items, 'sbwscf_llms', HOUR_IN_SECONDS );
		}

		foreach ( $items as $item ) {
			if ( in_array( $item['loc'], $seen_locs, true ) ) {
				continue;
			}
			$seen_locs[] = $item['loc'];
			echo '<url>' . "\n";
			echo '  <loc>' . esc_url( $item['loc'] ) . '</loc>' . "\n";
			echo '  <lastmod>' . esc_html( $item['lastmod'] ) . '</lastmod>' . "\n";
			echo '  <changefreq>monthly</changefreq>' . "\n";
			echo '  <priority>0.5</priority>' . "\n";
			echo '</url>' . "\n";
		}

		// ------------------------------------------------------------------
		// 3. Categories
		// ------------------------------------------------------------------
		if ( in_array( 'category', $enabled_types, true ) ) {
			$cats = wp_cache_get( 'sbwscf_sitemap_cats', 'sbwscf_llms' );
			if ( false === $cats ) {
				$cats = array();
				foreach ( get_categories( array( 'hide_empty' => true ) ) as $term ) {
					$cats[] = array(
						'loc'     => get_category_link( $term ),
						'lastmod' => sbwscf_llms_format_date(),
					);
				}
				wp_cache_set( 'sbwscf_sitemap_cats', $cats, 'sbwscf_llms', HOUR_IN_SECONDS );
			}
			foreach ( $cats as $item ) {
				if ( in_array( $item['loc'], $seen_locs, true ) ) {
					continue;
				}
				$seen_locs[] = $item['loc'];
				echo '<url>' . "\n";
				echo '  <loc>' . esc_url( $item['loc'] ) . '</loc>' . "\n";
				echo '  <lastmod>' . esc_html( $item['lastmod'] ) . '</lastmod>' . "\n";
				echo '  <changefreq>monthly</changefreq>' . "\n";
				echo '  <priority>0.4</priority>' . "\n";
				echo '</url>' . "\n";
			}
		}

		// ------------------------------------------------------------------
		// 4. Tags
		// ------------------------------------------------------------------
		if ( in_array( 'tag', $enabled_types, true ) ) {
			$tags = wp_cache_get( 'sbwscf_sitemap_tags', 'sbwscf_llms' );
			if ( false === $tags ) {
				$tags = array();
				foreach ( get_tags( array( 'hide_empty' => true ) ) as $term ) {
					$tags[] = array(
						'loc'     => get_tag_link( $term ),
						'lastmod' => sbwscf_llms_format_date(),
					);
				}
				wp_cache_set( 'sbwscf_sitemap_tags', $tags, 'sbwscf_llms', HOUR_IN_SECONDS );
			}
			foreach ( $tags as $item ) {
				if ( in_array( $item['loc'], $seen_locs, true ) ) {
					continue;
				}
				$seen_locs[] = $item['loc'];
				echo '<url>' . "\n";
				echo '  <loc>' . esc_url( $item['loc'] ) . '</loc>' . "\n";
				echo '  <lastmod>' . esc_html( $item['lastmod'] ) . '</lastmod>' . "\n";
				echo '  <changefreq>monthly</changefreq>' . "\n";
				echo '  <priority>0.3</priority>' . "\n";
				echo '</url>' . "\n";
			}
		}

		// ------------------------------------------------------------------
		// Close urlset
		// ------------------------------------------------------------------
		echo '</urlset>' . "\n";
	}
endif;

// Execute if query var is set.
if ( 1 === (int) get_query_var( 'sbwscf_sitemap', 0 ) ) {
	sbwscf_llms_render_sitemap_endpoint();
	exit;
}
