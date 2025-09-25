<?php
/**
 * Email preview page setup functions for the Contact Form module.
 *
 * @package    SMiLE Basic Web
 * @subpackage Contact Form
 */

/**
 * Ensure the Email Preview draft page exists.
 *
 * - Checks a saved option for an existing page.
 * - Falls back to querying by slug.
 * - Creates a new draft if none is found.
 *
 * @return int|false The page ID on success, false on failure.
 */
function sbwscf_contactform_ensure_email_preview_page() {
	$option_key = 'sbwscf_email_preview_page_id';
	$page_id    = (int) get_option( $option_key, 0 );

	// If option exists and post is not in trash, return it.
	if ( 0 !== $page_id && 'trash' !== get_post_status( $page_id ) ) {
		return $page_id;
	}

	// Try to find the page by its slug.
	$slug  = 'sbwscf-customizer-email-preview';
	$query = new WP_Query(
		array(
			'post_type'      => 'page',
			'pagename'       => $slug,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( $query->have_posts() ) {
		$page_id = absint( $query->posts[0] );
		update_option( $option_key, $page_id );
		return $page_id;
	}

	// If still not found, create a new draft page with the preview shortcode.
	$new = wp_insert_post(
		array(
			'post_title'   => $slug,
			'post_name'    => $slug,
			'post_content' => '[sbwscf_email_preview]',
			'post_status'  => 'draft',
			'post_type'    => 'page',
		)
	);

	if ( is_wp_error( $new ) ) {
		return false;
	}

	$page_id = absint( $new );
	update_option( $option_key, $page_id );
	return $page_id;
}

// Hook only in Customizer context to avoid front-end overhead.
add_action( 'customize_register', 'sbwscf_contactform_ensure_email_preview_page' );
