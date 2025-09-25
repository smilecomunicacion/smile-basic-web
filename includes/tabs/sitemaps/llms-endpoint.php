<?php
/**
 * Endpoint: llms.txt | llms.json
 *
 * Genera un feed legible por LLM con metadatos y contenidos.
 *
 * @package smile-basic-web
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// -----------------------------------------------------------------------------
// Helper: UTC date formatter.
// -----------------------------------------------------------------------------
if ( ! function_exists( 'sbwscf_format_date_utc' ) ) :
	/**
	 * Devuelve una fecha UTC en YYYY‑MM‑DD.
	 *
	 * @param int|null $timestamp Epoch; usa time() cuando es null.
	 * @return string Fecha.
	 */
	function sbwscf_format_date_utc( ?int $timestamp = null ): string {
		return gmdate( 'Y-m-d', null === $timestamp ? time() : $timestamp );
	}
endif;

// -----------------------------------------------------------------------------
// Render principal.
// -----------------------------------------------------------------------------
if ( ! function_exists( 'sbwscf_llms_render_endpoint' ) ) :
	/**
	 * Construye y envía la salida TXT o JSON (cache 1 h).
	 *
	 * @return void
	 */
	function sbwscf_llms_render_endpoint(): void {
		/*
		---------------------------------------------------------------------
		 * 1. Opciones de usuario.
		 * ------------------------------------------------------------------
		 */
		$format_opt = get_option( 'sbwscf_output_format', 'txt' );
		$format     = 'json' === $format_opt ? 'json' : 'txt';

		$word_limit = absint( get_option( 'sbwscf_word_limit', 40 ) );

		$title_raw = get_option( 'sbwscf_title', '' );
		$title     = '' !== $title_raw ? $title_raw : get_bloginfo( 'name' );

		$desc_raw   = get_option( 'sbwscf_description', '' );
		$desc_final = '' !== $desc_raw ? $desc_raw : get_bloginfo( 'description' );

		$author_raw = get_option( 'sbwscf_author', '' );
		$author     = '' !== $author_raw ? $author_raw : get_bloginfo( 'name' );

		$base_url = site_url();

		$content_types     = (array) get_option( 'sbwscf_content_types', array( 'post' ) );
		$priority_category = sanitize_text_field( get_option( 'sbwscf_priority_category', '' ) );

		/*
		---------------------------------------------------------------------
		 * 2. Consulta de posts/páginas en lotes.
		 * ------------------------------------------------------------------
		 */
		$batch_size = (int) apply_filters( 'sbwscf_llms_batch_size', 100 );
		$paged      = 1;
		$items      = array();

		$post_types = array();
		if ( in_array( 'post', $content_types, true ) ) {
			$post_types[] = 'post';
		}
		if ( in_array( 'page', $content_types, true ) ) {
			$post_types[] = 'page';
		}

		do {
			$args = array(
				'post_type'              => $post_types,
				'post_status'            => 'publish',
				'posts_per_page'         => $batch_size,
				'paged'                  => $paged,
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			);

			$cache_key = 'sbwscf_llms_posts_' . md5( wp_json_encode( $args ) );
			$batch     = wp_cache_get( $cache_key, 'sbwscf_llms' );

			if ( false === $batch ) { // Yoda.
				$query = new WP_Query( $args );
				$batch = $query->posts;
				wp_reset_postdata();
				wp_cache_set( $cache_key, $batch, 'sbwscf_llms', HOUR_IN_SECONDS );
			}

			if ( empty( $batch ) ) {
				break;
			}

			foreach ( $batch as $post ) {
				$post_id       = $post->ID;
				$post_type     = ucfirst( $post->post_type );
				$post_title    = get_the_title( $post_id );
				$post_url      = get_permalink( $post_id );
				$summary       = wp_trim_words( wp_strip_all_tags( $post->post_content ), $word_limit, '' );
				$publish_date  = sbwscf_format_date_utc( (int) get_post_time( 'U', true, $post_id ) );
				$modified_date = sbwscf_format_date_utc( (int) get_post_modified_time( 'U', true, $post_id ) );
				$priority      = 0;
				$sections      = array();

				if ( 'Post' === $post_type ) {
					$cats = get_the_category( $post_id );
					if ( ! empty( $cats ) ) {
						foreach ( $cats as $cat ) {
							$sections[] = $cat->name;
							if ( '' !== $priority_category && $priority_category === $cat->slug ) {
								$priority = 1;
							}
						}
					} else {
						$sections[] = 'Uncategorized';
					}
				} else {
					$sections[] = $post_type;
				}

				foreach ( $sections as $section ) {
					$items[ $section ][] = array(
						'title'         => $post_title,
						'url'           => $post_url,
						'description'   => $summary,
						'publish_date'  => $publish_date,
						'modified_date' => $modified_date,
						'priority'      => $priority,
					);
				}
			}

			++$paged;

		} while ( true );

		/*
		---------------------------------------------------------------------
		 * 3. Añadir term groups (categorías) si se pidió.
		 * ------------------------------------------------------------------
		 */
		if ( in_array( 'category', $content_types, true ) ) {
			$cat_cache = wp_cache_get( 'sbwscf_llms_categories', 'sbwscf_llms' );

			if ( false === $cat_cache ) {
				$cat_cache = array();
				$terms_cat = get_terms(
					array(
						'taxonomy'   => 'category',
						'hide_empty' => true,
					)
				);

				foreach ( $terms_cat as $term ) {
					$cat_cache[ 'Category: ' . $term->name ][] = array(
						'title'         => $term->name,
						'url'           => esc_url( get_term_link( $term ) ),
						'description'   => wp_trim_words(
							wp_strip_all_tags( term_description( $term ) ),
							$word_limit,
							''
						),
						'publish_date'  => sbwscf_format_date_utc(),
						'modified_date' => sbwscf_format_date_utc(),
						'priority'      => 0,
					);
				}

				wp_cache_set( 'sbwscf_llms_categories', $cat_cache, 'sbwscf_llms', HOUR_IN_SECONDS );
			}

			$items = array_merge_recursive( $items, $cat_cache );
		}

		/*
		---------------------------------------------------------------------
		 * 4. Añadir tags si está habilitado.
		 * ------------------------------------------------------------------
		 */
		if ( in_array( 'tag', $content_types, true ) ) {
			$tag_cache = wp_cache_get( 'sbwscf_llms_tags', 'sbwscf_llms' );

			if ( false === $tag_cache ) {
				$tag_cache = array();
				$terms_tag = get_terms(
					array(
						'taxonomy'   => 'post_tag',
						'hide_empty' => true,
					)
				);

				foreach ( $terms_tag as $term ) {
					$tag_cache[ 'Tag: ' . $term->name ][] = array(
						'title'         => $term->name,
						'url'           => esc_url( get_term_link( $term ) ),
						'description'   => wp_trim_words(
							wp_strip_all_tags( term_description( $term ) ),
							$word_limit,
							''
						),
						'publish_date'  => sbwscf_format_date_utc(),
						'modified_date' => sbwscf_format_date_utc(),
						'priority'      => 0,
					);
				}

				wp_cache_set( 'sbwscf_llms_tags', $tag_cache, 'sbwscf_llms', HOUR_IN_SECONDS );
			}

			$items = array_merge_recursive( $items, $tag_cache );
		}

		/*
		---------------------------------------------------------------------
		 * 5. Salida final.
		 * ------------------------------------------------------------------
		 */
		if ( 'json' === $format ) {
			header( 'Content-Type: application/json; charset=utf-8' );

			$output = array(
				'generator'   => 'SMiLE Basic Web',
				'title'       => $title,
				'description' => $desc_final,
				'author'      => $author,
				'url'         => esc_url( $base_url ),
				'sections'    => array(),
			);

			foreach ( $items as $section => $section_items ) {
				$output['sections'][] = array(
					'category' => $section,
					'items'    => $section_items,
				);
			}

			echo wp_json_encode( $output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		} else {
			header( 'Content-Type: text/plain; charset=utf-8' );

			echo "# llms.txt – generated by SMiLE Basic Web\n";
			echo 'title: "' . esc_html( $title ) . "\"\n";
			echo 'description: "' . esc_html( $desc_final ) . "\"\n";
			echo 'author: "' . esc_html( $author ) . "\"\n";
			echo 'url: "' . esc_url( $base_url ) . "\"\n";
			echo "sections:\n";

			foreach ( $items as $section => $section_items ) {
				echo '  - category: "' . esc_html( $section ) . "\"\n";
				foreach ( $section_items as $item ) {
					echo '    - title: "' . esc_html( $item['title'] ) . "\"\n";
					echo '      url: "' . esc_url( $item['url'] ) . "\"\n";
					echo '      description: "' . esc_html( $item['description'] ) . "\"\n";
					echo '      publish_date: "' . esc_html( $item['publish_date'] ) . "\"\n";
					echo '      modified_date: "' . esc_html( $item['modified_date'] ) . "\"\n";
					echo '      priority: "' . esc_html( $item['priority'] ) . "\"\n";
				}
			}
		}
	}
endif;

// -----------------------------------------------------------------------------
// Ejecutar al incluir y terminar.
// -----------------------------------------------------------------------------
sbwscf_llms_render_endpoint();
exit;
