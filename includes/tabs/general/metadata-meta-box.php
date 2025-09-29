<?php
/**
 * Conditional metadata editor for posts and pages.
 *
 * @package smile-basic-web
 */

defined( 'ABSPATH' ) || exit;

/*
 * ------------------------------------------------------------------
 * Main class
 * ------------------------------------------------------------------
 */

/**
 * Registers meta box and saving logic for SEO metadata fields.
 */
final class SBWSCF_Metadata_Meta_Box {

        /**
         * Tracks whether we are inside wp_head() while a custom description exists.
         *
         * @var bool
         */
        private static bool $is_head_context = false;

        /**
         * Holds the meta description for the current request when available.
         *
         * @var string|null
         */
        private static ?string $current_meta_description = null;

		/**
		 * Bootstraps hooks.
		 *
		 * @return void
		 */
	public static function init(): void {
			add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ), 10, 2 );
			add_action( 'save_post', array( __CLASS__, 'save_metadata' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

		/*
		 * ------------------------------------------------------------------
		 * Meta box registration
		 * ------------------------------------------------------------------
		 */

		/**
		 * Registers the metadata meta box for supported post types.
		 *
		 * @param string  $post_type Post type slug.
		 * @param WP_Post $post      Current post object.
		 * @return void
		 */
	public static function register_meta_box( string $post_type, WP_Post $post ): void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! in_array( $post_type, self::get_supported_post_types(), true ) ) {
				return;
		}

			add_meta_box(
				'sbwscf_metadata_fields',
				esc_html__( 'Search Metadata', 'smile-basic-web' ),
				array( __CLASS__, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
	}

		/**
		 * Returns the list of post types that should display the meta box.
		 *
		 * @return array
		 */
	public static function get_supported_post_types(): array {
			$post_types = get_post_types(
				array(
					'show_ui' => true,
					'public'  => true,
				),
				'names'
			);

			/**
			 * Filters the post types that support the metadata editor.
			 *
			 * @param array $post_types Default supported post types.
			 */
			return apply_filters( 'sbwscf_metadata_post_types', $post_types );
	}

		/**
		 * Boots the front-end rendering hooks when metadata is enabled.
		 *
		 * @return void
		 */
        public static function init_frontend(): void {
                        add_action( 'wp_head', array( __CLASS__, 'begin_head_context' ), 0 );
                        add_filter( 'pre_get_document_title', array( __CLASS__, 'filter_document_title' ), 20 );
                        add_action( 'wp_head', array( __CLASS__, 'output_meta_tags' ), 1 );
                        add_filter( 'bloginfo', array( __CLASS__, 'filter_bloginfo_description' ), 10, 3 );
                        add_filter( 'wp_robots', array( __CLASS__, 'filter_wp_robots' ) );
                        add_action( 'wp_head', array( __CLASS__, 'end_head_context' ), PHP_INT_MAX );
        }

		/*
		 * ------------------------------------------------------------------
		 * Rendering
		 * ------------------------------------------------------------------
		 */

		/**
		 * Renders the metadata meta box fields.
		 *
		 * @param WP_Post $post Post being edited.
		 * @return void
		 */
	public static function render_meta_box( WP_Post $post ): void {
			$meta_title       = get_post_meta( $post->ID, '_sbwscf_meta_title', true );
			$meta_description = get_post_meta( $post->ID, '_sbwscf_meta_description', true );
			$meta_index       = get_post_meta( $post->ID, '_sbwscf_meta_index', true );

			$meta_title       = is_string( $meta_title ) ? $meta_title : '';
			$meta_description = is_string( $meta_description ) ? $meta_description : '';
			$meta_index       = 'noindex' === $meta_index ? 'noindex' : 'index';

			$title_length       = self::get_character_length( $meta_title );
			$description_length = self::get_character_length( $meta_description );

			wp_nonce_field( 'sbwscf_metadata_meta_box', 'sbwscf_metadata_meta_box_nonce' );
		?>
<div class="sbwscf-metadata-field">
	<label for="sbwscf_meta_title"><strong><?php esc_html_e( 'Meta Title', 'smile-basic-web' ); ?></strong></label>
	<input type="text" id="sbwscf_meta_title" name="sbwscf_meta_title" value="<?php echo esc_attr( $meta_title ); ?>" class="widefat" maxlength="160" data-sbwscf-count-field="1" />
	<p class="description">
		<?php esc_html_e( 'Characters:', 'smile-basic-web' ); ?>
		<span class="sbwscf-char-count" data-sbwscf-count-for="sbwscf_meta_title"><?php echo esc_html( (string) $title_length ); ?></span>
		<?php esc_html_e( '(recommended 50–60).', 'smile-basic-web' ); ?>
	</p>
</div>
<div class="sbwscf-metadata-field">
	<label for="sbwscf_meta_description"><strong><?php esc_html_e( 'Meta Description', 'smile-basic-web' ); ?></strong></label>
	<textarea id="sbwscf_meta_description" name="sbwscf_meta_description" class="widefat" rows="4" data-sbwscf-count-field="1"><?php echo esc_textarea( $meta_description ); ?></textarea>
	<p class="description">
		<?php esc_html_e( 'Characters:', 'smile-basic-web' ); ?>
		<span class="sbwscf-char-count" data-sbwscf-count-for="sbwscf_meta_description"><?php echo esc_html( (string) $description_length ); ?></span>
		<?php esc_html_e( '(recommended 120–160).', 'smile-basic-web' ); ?>
	</p>
</div>
<div class="sbwscf-metadata-field">
	<strong><?php esc_html_e( 'Indexing', 'smile-basic-web' ); ?></strong>
	<p>
		<label for="sbwscf_meta_index_yes">
			<input type="radio" id="sbwscf_meta_index_yes" name="sbwscf_meta_index" value="index" <?php checked( 'index', $meta_index ); ?> />
			<?php esc_html_e( 'Index in search engines', 'smile-basic-web' ); ?>
		</label>
	</p>
	<p>
		<label for="sbwscf_meta_index_no">
			<input type="radio" id="sbwscf_meta_index_no" name="sbwscf_meta_index" value="noindex" <?php checked( 'noindex', $meta_index ); ?> />
			<?php esc_html_e( 'Do not index or follow (hide from search engines)', 'smile-basic-web' ); ?>
		</label>
	</p>
</div>
		<?php
	}

		/*
		 * ------------------------------------------------------------------
		 * Saving
		 * ------------------------------------------------------------------
		 */

		/**
		 * Persists metadata values when the post is saved.
		 *
		 * @param int $post_id Post ID.
		 * @return void
		 */
	public static function save_metadata( int $post_id ): void {
		if ( ! isset( $_POST['sbwscf_metadata_meta_box_nonce'] ) ) {
				return;
		}

			$nonce = sanitize_text_field( wp_unslash( $_POST['sbwscf_metadata_meta_box_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'sbwscf_metadata_meta_box' ) ) {
				return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}

			$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! in_array( $post_type, self::get_supported_post_types(), true ) ) {
				return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
		}

			$meta_title = isset( $_POST['sbwscf_meta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['sbwscf_meta_title'] ) ) : '';

			$meta_description = isset( $_POST['sbwscf_meta_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sbwscf_meta_description'] ) ) : '';

			$meta_index = isset( $_POST['sbwscf_meta_index'] ) ? sanitize_key( wp_unslash( $_POST['sbwscf_meta_index'] ) ) : 'index';
			$meta_index = in_array( $meta_index, array( 'index', 'noindex' ), true ) ? $meta_index : 'index';

			self::update_meta_value( $post_id, '_sbwscf_meta_title', $meta_title );
			self::update_meta_value( $post_id, '_sbwscf_meta_description', $meta_description );
			update_post_meta( $post_id, '_sbwscf_meta_index', $meta_index );
	}

		/**
		 * Updates or deletes a meta value based on its contents.
		 *
		 * @param int    $post_id Post ID.
		 * @param string $key     Meta key.
		 * @param string $value   Meta value.
		 * @return void
		 */
	private static function update_meta_value( int $post_id, string $key, string $value ): void {
		if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
				return;
		}

			update_post_meta( $post_id, $key, $value );
	}

		/**
		 * Returns the number of characters in a string, multibyte-safe.
		 *
		 * @param string $value Value to measure.
		 * @return int
		 */
	private static function get_character_length( string $value ): int {
		if ( function_exists( 'mb_strlen' ) ) {
				return (int) mb_strlen( $value );
		}

			return strlen( $value );
	}

		/**
		 * Filters the document title with the custom meta title.
		 *
		 * @param string $title Default title.
		 * @return string
		 */
        public static function filter_document_title( string $title ): string {
                        $post_id = self::get_current_post_id();
                if ( 0 === $post_id ) {
                                return $title;
                }

                        $meta_title = get_post_meta( $post_id, '_sbwscf_meta_title', true );
                if ( ! is_string( $meta_title ) || '' === $meta_title ) {
                                return $title;
                }

                        return $meta_title;
        }

		/**
		 * Outputs the meta description in the document head.
		 *
		 * @return void
		 */
        public static function output_meta_tags(): void {
                        $meta_description = self::get_current_meta_description();
                if ( '' !== $meta_description ) {
                                printf(
                                        "\n<meta name=\"description\" content=\"%s\" />\n",
                                        esc_attr( $meta_description )
                                );
                }
        }

		/**
		 * Adjusts robots directives so they are emitted through wp_robots().
		 *
		 * @param array $robots Current robots directives.
		 * @return array
		 */
        public static function filter_wp_robots( array $robots ): array {
                        $post_id = self::get_current_post_id();
                if ( 0 === $post_id ) {
                                return $robots;
                }

                        $meta_index = get_post_meta( $post_id, '_sbwscf_meta_index', true );
                if ( 'noindex' !== $meta_index ) {
                                return $robots;
                }

			unset( $robots['index'] );
			unset( $robots['follow'] );

			$robots['noindex']  = true;
			$robots['nofollow'] = true;

			return $robots;
	}

		/*
		 * ------------------------------------------------------------------
		 * Assets
         * ------------------------------------------------------------------
         */

        /**
         * Marks the start of wp_head() so description filtering only happens there.
         *
         * @return void
         */
        public static function begin_head_context(): void {
                        $meta_description = self::get_current_meta_description();
                if ( '' === $meta_description ) {
                                self::$is_head_context       = false;
                                self::$current_meta_description = null;
                                return;
                }

                        self::$is_head_context       = true;
                        self::$current_meta_description = $meta_description;
        }

        /**
         * Filters bloginfo() so themes output the custom meta description instead of the tagline.
         *
         * @param string $value  Original value.
         * @param string $show   Requested field.
         * @param string $filter Context filter (raw/display).
         * @return string
         */
        public static function filter_bloginfo_description( string $value, string $show, string $filter ): string { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
                if ( 'description' !== $show ) {
                                return $value;
                }

                if ( ! self::$is_head_context || null === self::$current_meta_description ) {
                                return $value;
                }

                        return self::$current_meta_description;
        }

        /**
         * Resets head-context flags after wp_head() has finished rendering.
         *
         * @return void
         */
        public static function end_head_context(): void {
                        self::$is_head_context       = false;
                        self::$current_meta_description = null;
        }

        /**
         * Enqueues admin assets for the metadata meta box.
         *
         * @param string $hook_suffix Current admin page.
         * @return void
         */
        public static function enqueue_assets( string $hook_suffix ): void {
                if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix ) {
                                return;
                }

                        $screen = get_current_screen();
                if ( ! $screen || ! in_array( $screen->post_type, self::get_supported_post_types(), true ) ) {
                                return;
                }

                        wp_enqueue_script(
                                'sbwscf-metadata-meta-box',
                                SMILE_BASIC_WEB_PLUGIN_URL . 'includes/tabs/general/js/metadata-meta-box.js',
                                array(),
                                SMILE_BASIC_WEB_VERSION,
                                true
                        );
        }

        /**
         * Returns the current post ID when displaying a supported singular post.
         *
         * @return int
         */
        private static function get_current_post_id(): int {
                if ( ! is_singular( self::get_supported_post_types() ) ) {
                                return 0;
                }

                        $post_id = get_queried_object_id();
                if ( ! $post_id ) {
                                return 0;
                }

                        return (int) $post_id;
        }

        /**
         * Retrieves the saved meta description for the current request.
         *
         * @return string
         */
        private static function get_current_meta_description(): string {
                        $post_id = self::get_current_post_id();
                if ( 0 === $post_id ) {
                                return '';
                }

                        $meta_description = get_post_meta( $post_id, '_sbwscf_meta_description', true );
                if ( ! is_string( $meta_description ) ) {
                                return '';
                }

                        return $meta_description;
        }
}
