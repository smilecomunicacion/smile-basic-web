<?php
/**
 * Conditional SVG / SVGZ upload support for SMiLE Basic Web.
 *
 * Only loaded when the General-setting “Enable SVG uploads” is checked.
 *
 * @package smile-basic-web
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Main plugin class.
 */
final class SBWSCF_SVG_Upload {
	/*
	* ------------------------------------------------------------------
	* Bootstrapping
	* ------------------------------------------------------------------
	*/

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public static function init() {

		/* Mime type & extension. */
		add_filter( 'upload_mimes', array( __CLASS__, 'allow_svg_mime' ) );
		add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'fix_svg_filetype' ), 10, 5 );

		/* Prevent bitmap processing. */
		add_filter( 'wp_attachment_is_image', array( __CLASS__, 'is_not_bitmap' ), 10, 2 );
		add_filter( 'file_is_displayable_image', array( __CLASS__, 'file_is_not_displayable' ), 10, 2 );

		/* Skip big-image and metadata. */
		add_filter( 'big_image_size_threshold', '__return_false' );
		add_filter( 'wp_read_image_metadata', array( __CLASS__, 'fake_metadata' ), 10, 3 );

		/* Bypass WP 6.8 unsupported-mime blocker for SVG. */
		add_filter( 'wp_prevent_unsupported_mime_type_uploads', array( __CLASS__, 'unblock_svg_mime' ), 10, 2 );

		/* Sanitize SVG on upload. */
		add_filter( 'wp_handle_upload_prefilter', array( __CLASS__, 'sanitize_svg_on_upload' ) );

		/* Thumbnail in Media Library. */
		add_filter( 'wp_prepare_attachment_for_js', array( __CLASS__, 'svg_preview' ), 10, 3 );
	}

	/*
	* ------------------------------------------------------------------
	* 1. Mime type & extension
	* ------------------------------------------------------------------
	*/

	/**
	 * Allow SVG and SVGZ mime types.
	 *
	 * @param array $mimes Allowed mime types.
	 * @return array       Filtered mime types.
	 */
	public static function allow_svg_mime( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Force WordPress to recognise .svg/.svgz as image/svg+xml.
	 *
	 * @param array        $data       Filetype data.
	 * @param string       $file       Path to file.
	 * @param string       $filename   Original filename.
	 * @param array        $mimes      Allowed mime types.
	 * @param string|false $real_mime Real mime type from PHP.
	 * @return array                  Filtered filetype data.
	 */
	public static function fix_svg_filetype( $data, $file, $filename, $mimes, $real_mime ) {
		unset( $mimes, $real_mime );
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( ! $data['type'] && in_array( $ext, array( 'svg', 'svgz' ), true ) ) {
			$data = array(
				'ext'             => $ext,
				'type'            => 'image/svg+xml',
				'proper_filename' => $filename,
			);
		}

		return $data;
	}

	/*
	* ------------------------------------------------------------------
	* 2. Prevent bitmap processing
	* ------------------------------------------------------------------
	*/

	/**
	 * Tell WP an SVG is not a bitmap.
	 *
	 * @param bool       $is_image   Original result.
	 * @param int|string $id_or_path Attachment ID or path.
	 * @return bool                  Filtered result.
	 */
	public static function is_not_bitmap( $is_image, $id_or_path ) {

		if ( is_string( $id_or_path ) && preg_match( '/\.svgz?$/i', $id_or_path ) ) {
			return false;
		}

		if ( is_numeric( $id_or_path ) && 'image/svg+xml' === get_post_mime_type( (int) $id_or_path ) ) {
			return false;
		}

		return $is_image;
	}

	/**
	 * Return false in file_is_displayable_image() for SVG paths.
	 *
	 * @param bool   $result Original value.
	 * @param string $path   File path.
	 * @return bool          Filtered value.
	 */
	public static function file_is_not_displayable( $result, $path ) {
		return preg_match( '/\.svgz?$/i', $path ) ? false : $result;
	}


	/*
	* ------------------------------------------------------------------
	* 3. Metadata & big-image
	* ------------------------------------------------------------------
	*/

	/**
	 * Provide minimal metadata so WP skips size generation.
	 *
	 * @param array  $metadata          Existing metadata.
	 * @param string $file              File path.
	 * @param string $source_image_type Mime type.
	 * @return array                    Filtered metadata.
	 */
	public static function fake_metadata( $metadata, $file, $source_image_type ) {
		if ( 'image/svg+xml' === $source_image_type ) {
			return array(
				'width'      => 0,
				'height'     => 0,
				'file'       => basename( $file ),
				'image_meta' => array(),
			);
		}

		return $metadata;
	}


	/*
	* ------------------------------------------------------------------
	* 4. WP 6.8 unsupported-mime blocker
	* ------------------------------------------------------------------
	*/

	/**
	 * Disable the “unsupported mime type” block for SVG only.
	 *
	 * @param bool        $block Block decision.
	 * @param string|null $mime  Mime type.
	 * @return bool              Filtered decision.
	 */
	public static function unblock_svg_mime( bool $block, $mime = null ): bool {
		return ( null !== $mime && 'image/svg+xml' === $mime ) ? false : $block; // Yoda.
	}

	/*
	* ------------------------------------------------------------------
	* 5. Upload sanitisation
	* ------------------------------------------------------------------
	*/

	/**
	 * Light SVG sanitiser: remove <script> tags and on*= attrs.
	 *
	 * @param string $svg Raw SVG markup.
	 * @return string     Sanitised markup.
	 */
	private static function sanitize_svg_markup( $svg ) {
		$svg = preg_replace( '/<script.*?<\/script>/is', '', $svg );
		$svg = preg_replace( '/\s+on\w+="[^"]*"/i', '', $svg );
		$svg = preg_replace( '/<\?xml.*?\?>/i', '', $svg );
		return $svg;
	}

	/**
	 * Sanitize SVG file using WP_Filesystem instead of direct file_* calls.
	 *
	 * @param array $file File array.
	 * @return array      Possibly modified array.
	 */
	public static function sanitize_svg_on_upload( $file ) {
		if ( isset( $file['type'] ) && 'image/svg+xml' === $file['type'] ) {

			require_once ABSPATH . 'wp-admin/includes/file.php';
			global $wp_filesystem;

			if ( ! is_object( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			if ( is_object( $wp_filesystem ) && $wp_filesystem->exists( $file['tmp_name'] ) ) {
				$raw_svg = $wp_filesystem->get_contents( $file['tmp_name'] );

				if ( false !== $raw_svg ) { // Yoda.
					$clean_svg = self::sanitize_svg_markup( $raw_svg );
					$wp_filesystem->put_contents( $file['tmp_name'], $clean_svg, FS_CHMOD_FILE );
				}
			}
		}
		return $file;
	}

	/*
	* ------------------------------------------------------------------
	* 6. Media Library thumbnail
	* ------------------------------------------------------------------
	*/

        /**
         * Provide thumbnail for SVGs.
         *
         * @param array       $response   Prepared data.
         * @param WP_Post     $attachment Attachment post.
         * @param array|false $meta       Metadata.
         * @return array                  Modified data.
         */
        public static function svg_preview( array $response, WP_Post $attachment, $meta ): array {
                if ( ! is_array( $meta ) ) {
                        $meta = array();
                }
                unset( $meta );
                if ( isset( $response['mime'] ) && 'image/svg+xml' === $response['mime'] ) { // Yoda.
                        $response['thumb']  = esc_url( wp_get_attachment_url( $attachment->ID ) );
                        $response['width']  = 0;
			$response['height'] = 0;
		}
		return $response;
	}
}
