<?php
/**
 * Uninstall script for the SMiLE Basic Web plug‑in.
 *
 * Removes every option, transient, object‑cache key and scheduled task
 * registered by the plug‑in on each site of the network.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Performs the data clean‑up for the current site.
 *
 * @return void
 */
function sbwscf_uninstall_site_cleanup() {

	// ---------------------------------------------------------------------
	// Options created by the Contact‑Form and Sitemaps modules.
	// ---------------------------------------------------------------------
	$options = array(
		'sbwscf_settings',
		'sbwscf_custom_fields',
		'sbwscf_generate',
		'sbwscf_content_types',
		'sbwscf_output_format',
		'sbwscf_word_limit',
		'sbwscf_title',
		'sbwscf_description',
		'sbwscf_author',
		'sbwscf_priority_category',
		'sbwscf_email_preview_page_id',
		'sbwscf_general_settings', // <-- NUEVA entrada
		'sbwscf_plugin_version',
		// Cookies module options.
		'sbwscf_enable_cookies_notice',
		'sbwscf_cookie_message',
		'sbwscf_cookie_panel_size',
		'sbwscf_cookie_minimized_position',
		'sbwscf_show_manage_minified_label',
		'sbwscf_cookie_policy_page',
		'sbwscf_privacy_policy_page',
		'sbwscf_legal_notice_page',
		'sbwscf_cookie_color_background',
		'sbwscf_cookie_color_titles',
		'sbwscf_cookie_color_text',
		'sbwscf_cookie_color_accept',
                'sbwscf_cookie_color_accept_text',
                'sbwscf_cookie_color_reject',
                'sbwscf_cookie_color_reject_text',
                'sbwscf_tracking_scripts',
                'sbwscf_cookie_settings_revision',
        );

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	/*
	* ------------------------------------------------------------------
	* Delete the draft page created for the Email Preview.
	* ------------------------------------------------------------------
	*/
	$page_id = get_option( 'sbwscf_email_preview_page_id', 0 );
	if ( 0 !== (int) $page_id && 'trash' !== get_post_status( $page_id ) ) {
		wp_delete_post( (int) $page_id, true );
	}

	// ---------------------------------------------------------------------
	// Transients (site and network scope).
	// ---------------------------------------------------------------------
	delete_transient( 'sbwscf_temp_data' );
	delete_site_transient( 'sbwscf_temp_data' ); // Safety in Multisite.

	// ---------------------------------------------------------------------
	// Object‑cache keys created via wp_cache_set().
	// ---------------------------------------------------------------------
	wp_cache_delete(
		'sbwscf_page_by_title_' . md5( 'sbwscf-customizer-email-preview' ),
		'sbwscf'
	);

	// Network-wide cache key (object cache may be global).
	wp_cache_delete(
		'sbwscf_page_by_title_' . md5( 'sbwscf-customizer-email-preview' ),
		''
	);

	// Optional: flush entire object cache if desired.
	wp_cache_flush();

	// ---------------------------------------------------------------------
	// Scheduled tasks (if the plug‑in ever registers cron events).
	// ---------------------------------------------------------------------
	$cron_hook = 'sbwscf_cron_hook';
	if ( false !== wp_next_scheduled( $cron_hook ) ) { // Yoda condition.
		wp_clear_scheduled_hook( $cron_hook );
	}

	/*
	 * WordPress already flushes rewrite rules once a plug‑in with custom
	 * endpoints is removed. Calling flush_rewrite_rules() again would be
	 * redundant on large sites, so we skip it deliberately.
	 */
}

/*
-------------------------------------------------------------------------
 * Run the clean‑up on the current site or across every site in Multisite.
 * ----------------------------------------------------------------------
 */
if ( is_multisite() ) { // Run for every site regardless of current context.
	$site_ids = get_sites(
		array(
			'fields' => 'ids',
		)
	);

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		sbwscf_uninstall_site_cleanup();
		restore_current_blog();
	}
} else {
	sbwscf_uninstall_site_cleanup();
}
