<?php
/**
 * Admin tab: Sitemaps (robots.txt, sitemap.xml, sitemap-images.xml & llms.txt).
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/*
 * -------------------------------------------------------------------------
 * Boot helpers for this module **once per request**.
 * -------------------------------------------------------------------------
 */
require_once SMILE_BASIC_WEB_PLUGIN_PATH . 'includes/tabs/sitemaps/sitemaps.php';

/*
 * -------------------------------------------------------------------------
 * Constants (guarded to avoid redefining the one set in sitemaps.php).
 * -------------------------------------------------------------------------
 */
if ( ! defined( 'SBWSCF_LLMS_SANITIZE_ARGS' ) ) {
	define(
		'SBWSCF_LLMS_SANITIZE_ARGS',
		array(
			'sanitize_callback' => 'sbwscf_llms_sanitize_array',
		)
	);
}

/**
 * Admin tab: Sitemaps.
 */
final class SBWSCF_Sitemaps_Page implements SBWSCF_Tab_Interface {

	/**
	 * Returns the slug used in the admin URL.
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return 'sitemaps';
	}

	/**
	 * Returns the human‑readable label.
	 *
	 * @return string
	 */
	public static function get_label(): string {
		return esc_html__( 'Sitemaps', 'smile-basic-web' );
	}

	/**
	 * Unique settings-page slug for WP Settings API.
	 *
	 * @return string
	 */
	private static function page_slug(): string {
		return 'sbwscf_page_sitemaps';
	}

	/**
	 * Registers settings, sections and fields for this tab.
	 *
	 * @return void
	 */
	public static function register_settings(): void {

		$group = self::page_slug();

		// Main options.
		register_setting( $group, 'sbwscf_generate', SBWSCF_LLMS_SANITIZE_ARGS );
		register_setting( $group, 'sbwscf_content_types', SBWSCF_LLMS_SANITIZE_ARGS );
		register_setting( $group, 'sbwscf_output_format', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_word_limit', 'absint' );
		register_setting( $group, 'sbwscf_title', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_description', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_author', 'sanitize_text_field' );
		register_setting( $group, 'sbwscf_priority_category', 'sanitize_text_field' );

		// Dummy section (all fields are printed manually).
		add_settings_section(
			'sbwscf_sitemaps_section',
			'',
			'__return_false',
			$group
		);
	}

	/**
	 * Prints the whole UI for the Sitemaps tab.
	 *
	 * @return void
	 */
	public static function render(): void {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// ------------------------------------------------------------------
		// • Current option values.
		// ------------------------------------------------------------------
		$selected_outputs     = (array) get_option( 'sbwscf_generate', array( 'llms' ) );
		$output_format        = get_option( 'sbwscf_output_format', 'txt' );
		$word_limit           = absint( get_option( 'sbwscf_word_limit', 40 ) );
		$title_override       = get_option( 'sbwscf_title', '' );
		$description_override = get_option( 'sbwscf_description', '' );
		$author_override      = get_option( 'sbwscf_author', '' );
		$content_types        = (array) get_option( 'sbwscf_content_types', array( 'post' ) );
		$priority_category    = get_option( 'sbwscf_priority_category', '' );
		$base_url             = esc_url( site_url() );

		// Labels for the selectable files.
		$output_labels = array(
			'robots'  => 'robots.txt',
			'sitemap' => 'sitemap.xml',
			'images'  => 'sitemap-images.xml',
			'llms'    => 'llms.txt',
		);
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Sitemaps Settings', 'smile-basic-web' ); ?></h2>
			<?php settings_errors(); ?>
			<p><?php esc_html_e( 'Configure the output of your AI-friendly llms.txt, sitemaps, and robots.txt files.', 'smile-basic-web' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
				// Use same group and page slug as in register_settings().
				settings_fields( self::page_slug() );
				do_settings_sections( self::page_slug() );
				?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Files to generate', 'smile-basic-web' ); ?></th>
							<td>
								<?php foreach ( $output_labels as $key => $label ) : ?>
									<label>
										<input
											type="checkbox"
											name="sbwscf_generate[]"
											value="<?php echo esc_attr( $key ); ?>"
											<?php checked( in_array( $key, $selected_outputs, true ) ); ?>
										/>
										<?php echo esc_html( $label ); ?>
									</label><br />
								<?php endforeach; ?>
								<p class="description">
									<?php esc_html_e( 'Select which files will be generated dynamically.', 'smile-basic-web' ); ?>
								</p>
							</td>
						</tr>

						<?php
						// Helper to print a simple text input.
						$print_text_row = static function ( $label, $option_name, $value, $desc = '' ) {
							?>
							<tr>
								<th scope="row"><?php echo esc_html( $label ); ?></th>
								<td>
									<input
										type="text"
										name="<?php echo esc_attr( $option_name ); ?>"
										value="<?php echo esc_attr( $value ); ?>"
										class="regular-text"
									/>
									<?php if ( '' !== $desc ) : ?>
										<p class="description"><?php echo esc_html( $desc ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<?php
						};

						$print_text_row(
							__( 'Title override', 'smile-basic-web' ),
							'sbwscf_title',
							$title_override,
							__( 'Override site title inside llms.txt.', 'smile-basic-web' )
						);
						$print_text_row(
							__( 'Description override', 'smile-basic-web' ),
							'sbwscf_description',
							$description_override,
							__( 'Override site description inside llms.txt.', 'smile-basic-web' )
						);
						$print_text_row(
							__( 'Author override', 'smile-basic-web' ),
							'sbwscf_author',
							$author_override,
							__( 'Override author inside llms.txt.', 'smile-basic-web' )
						);
		?>

						<tr>
							<th scope="row"><?php esc_html_e( 'Content types to include', 'smile-basic-web' ); ?></th>
							<td>
								<?php
								$types = array(
									'post'     => esc_html__( 'Posts', 'smile-basic-web' ),
									'page'     => esc_html__( 'Pages', 'smile-basic-web' ),
									'category' => esc_html__( 'Categories', 'smile-basic-web' ),
									'tag'      => esc_html__( 'Tags', 'smile-basic-web' ),
								);
								foreach ( $types as $type_key => $type_label ) :
									?>
									<label>
										<input
											type="checkbox"
											name="sbwscf_content_types[]"
											value="<?php echo esc_attr( $type_key ); ?>"
											<?php checked( in_array( $type_key, $content_types, true ) ); ?>
										/>
										<?php echo esc_html( $type_label ); ?>
									</label><br />
								<?php endforeach; ?>
								<p class="description">
									<?php esc_html_e( 'Choose which content types appear in the feeds.', 'smile-basic-web' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Word limit per item', 'smile-basic-web' ); ?></th>
							<td>
								<input
									type="number"
									name="sbwscf_word_limit"
									value="<?php echo esc_attr( $word_limit ); ?>"
									class="small-text"
								/>
								<p class="description">
									<?php esc_html_e( 'Maximum words per summary (default 40).', 'smile-basic-web' ); ?>
								</p>
							</td>
						</tr>

						<?php
						$print_text_row(
							__( 'Priority category (slug)', 'smile-basic-web' ),
							'sbwscf_priority_category',
							$priority_category,
							__( 'Posts in this category are given priority 1.', 'smile-basic-web' )
						);
						?>

						<tr>
							<th scope="row"><?php esc_html_e( 'Output format', 'smile-basic-web' ); ?></th>
							<td>
								<label>
									<input
										type="radio"
										name="sbwscf_output_format"
										value="txt"
										<?php checked( 'txt', $output_format ); ?>
									/>
									<?php esc_html_e( 'TXT', 'smile-basic-web' ); ?>
								</label><br />
								<label>
									<input
										type="radio"
										name="sbwscf_output_format"
										value="json"
										<?php checked( 'json', $output_format ); ?>
									/>
									<?php esc_html_e( 'JSON', 'smile-basic-web' ); ?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>

			</form>

			<h3><?php esc_html_e( 'Generated URLs', 'smile-basic-web' ); ?></h3>
			<ul>
				<?php foreach ( $output_labels as $key => $label ) : ?>
					<?php if ( in_array( $key, $selected_outputs, true ) ) : ?>
						<li>
							<a
								href="<?php echo esc_url( $base_url . '/' . $label ); ?>"
								target="_blank"
								rel="noopener noreferrer"
							>
								<?php echo esc_html( $base_url . '/' . $label ); ?>
							</a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Register Customizer settings & controls.
	 *
	 * Empty implementation because Sitemaps module has no Customizer UI.
	 *
	 * @param WP_Customize_Manager $wp_customize Customize Manager instance.
	 * @return void
	 */
	public static function register_customizer( WP_Customize_Manager $wp_customize ): void {
		// No Customizer sections for Sitemaps.
	}
}

/*
 * -------------------------------------------------------------------------
 * Hooks
 * -------------------------------------------------------------------------
 */
add_action( 'admin_init', array( 'SBWSCF_Sitemaps_Page', 'register_settings' ) );
add_action( 'smile_basic_web_render_tab_sitemaps', array( 'SBWSCF_Sitemaps_Page', 'render' ) );
