<?php
/**
 * Helper functions for the plugin.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image;

defined( 'ABSPATH' ) || exit;

use function media_handle_sideload;

/**
 * Post meta flag set when a person removes a featured image, so it is not added back automatically.
 */
const SKIP_META_KEY = '_rs_featured_image_skip';

/**
 * Attachment meta holding the remote URL an image was downloaded from.
 */
const SOURCE_URL_META_KEY = '_rs_featured_image_source_url';

/**
 * Get asset version for cache busting.
 *
 * When JETIXWP_DEBUG is defined, uses the file modification time.
 * Otherwise, uses the plugin version constant.
 *
 * @param string $file_path Full path to the asset file.
 * @return string|int       File modification time if debugging, otherwise plugin version.
 */
function get_asset_version( $file_path = '' ) {
	// If debug mode is enabled and file exists, use file modification time for cache busting.
	if ( defined( 'JETIXWP_DEBUG' ) && JETIXWP_DEBUG && ! empty( $file_path ) && file_exists( $file_path ) ) {
		return filemtime( $file_path );
	}

	// Otherwise, use plugin version constant.
	return RS_FEATURED_IMAGE_VERSION;
}

/**
 * Get supported image extensions.
 *
 * @return array Supported image extensions.
 */
function get_supported_image_extensions() {
	return array( 'jpg', 'jpeg', 'png', 'webp', 'bmp' );
}

/**
 * Pick the items to try for a position setting.
 *
 * "first" tries every item in order until one works; the other positions pick one item.
 *
 * @since 1.1.0
 *
 * @param array  $items    Items found in content, in order.
 * @param string $position One of first, second, last-second, last.
 *
 * @return array Items to try, in order.
 */
function get_items_by_position( array $items, string $position ) {
	$items = array_values( $items );
	$count = count( $items );

	switch ( $position ) {
		case 'second':
			return $count > 1 ? array( $items[1] ) : array();
		case 'last-second':
			return $count > 1 ? array( $items[ $count - 2 ] ) : array();
		case 'last':
			return $count > 0 ? array( $items[ $count - 1 ] ) : array();
		default:
			return $items;
	}
}

/**
 * Get how many characters of post content to scan for a source.
 *
 * @since 1.1.0
 *
 * @param string $type Either image or video.
 *
 * @return int Number of characters, 6000 when not set.
 */
function get_scan_length( string $type ) {
	$length = absint( Options::get_instance()->get( $type . '_content_length', 6000 ) );

	return $length > 0 ? $length : 6000;
}

/**
 * How many found images or videos to try per save.
 *
 * Keeps saves fast and stops a post full of broken links from making many remote requests.
 *
 * @since 1.1.0
 *
 * @return int
 */
function get_max_attempts() {
	/**
	 * Filters how many found images or videos are tried per save.
	 *
	 * @since 1.1.0
	 *
	 * @param int $max_attempts Default 3.
	 */
	return max( 1, (int) apply_filters( 'rs_featured_image_max_attempts', 3 ) );
}

/**
 * Check whether a remote image may be downloaded into the Media Library for a post.
 *
 * The person saving the post (or its author when nobody is logged in, e.g. cron or imports)
 * must be allowed to upload files, the same as adding the image by hand.
 *
 * @since 1.1.0
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function can_download_for_post( int $post_id ) {
	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		$post    = get_post( $post_id );
		$user_id = $post ? (int) $post->post_author : 0;
	}

	$can_download = $user_id && user_can( $user_id, 'upload_files' );

	/**
	 * Filters whether a remote image may be downloaded for a post.
	 *
	 * @since 1.1.0
	 *
	 * @param bool $can_download Whether downloading is allowed.
	 * @param int  $post_id      Post ID.
	 * @param int  $user_id      User the check ran for, 0 when none.
	 */
	return (bool) apply_filters( 'rs_featured_image_can_download', $can_download, $post_id, $user_id );
}

/**
 * Get the post types automatic featured images are enabled for.
 *
 * @return string[] Post type slugs.
 */
function get_enabled_post_types() {
	$defaults = apply_filters(
		'rs_featured_image_default_enabled_post_types',
		array(
			'post' => true,
			'page' => true,
		)
	);

	$post_types = Options::get_instance()->get( 'post_types', $defaults );

	if ( ! is_array( $post_types ) ) {
		$post_types = $defaults;
	}

	return array_keys( array_filter( $post_types ) );
}

/**
 * Check whether a post should get an automatic featured image.
 *
 * @param int|\WP_Post $post Post ID or object.
 *
 * @return bool
 */
function should_process_post( $post ) {
	$post = get_post( $post );

	if ( ! $post instanceof \WP_Post ) {
		return false;
	}

	$should_process = true;

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
		$should_process = false;
	} elseif ( in_array( $post->post_status, array( 'auto-draft', 'inherit', 'trash' ), true ) ) {
		$should_process = false;
	} elseif ( ! in_array( $post->post_type, get_enabled_post_types(), true ) || ! post_type_supports( $post->post_type, 'thumbnail' ) ) {
		$should_process = false;
	} elseif ( has_post_thumbnail( $post ) || get_post_meta( $post->ID, SKIP_META_KEY, true ) ) {
		$should_process = false;
	}

	/**
	 * Filters whether a post should get an automatic featured image.
	 *
	 * @since 1.1.0
	 *
	 * @param bool     $should_process Whether to look for a featured image.
	 * @param \WP_Post $post           Post object.
	 */
	return (bool) apply_filters( 'rs_featured_image_should_process_post', $should_process, $post );
}

/**
 * Check if the given URL corresponds to an existing attachment image.
 *
 * @param string $url Image URL.
 *
 * @return bool True if the URL corresponds to an existing attachment, false otherwise.
 */
function get_attachment_by_url( string $url ) {
	if ( empty( $url ) ) {
		return false;
	}

	$url = esc_url_raw( $url );

	// 1. Direct match
	$attachment_id = attachment_url_to_postid( $url );
	if ( $attachment_id ) {
		return (int) $attachment_id;
	}

	// 2. Remove size suffix (-300x300)
	$base_url = preg_replace( '/-\d+x\d+(?=\.\w+$)/', '', $url );
	if ( $base_url !== $url ) {
		$attachment_id = attachment_url_to_postid( $base_url );
		if ( $attachment_id ) {
			return (int) $attachment_id;
		}
	}

	// 3. Try scaled variant (image.jpg → image-scaled.jpg)
	$scaled_url = preg_replace(
		'/(\.\w+)$/',
		'-scaled$1',
		$base_url
	);

	if ( $scaled_url !== $base_url ) {
		$attachment_id = attachment_url_to_postid( $scaled_url );
		if ( $attachment_id ) {
			return (int) $attachment_id;
		}
	}

	return false;
}

/**
 * Set featured image from attachment ID.
 *
 * @param int $post_id Post ID.
 * @param int $attachment_id Attachment ID.
 *
 * @return int|false Attachment ID on success, false otherwise.
 */
function set_featured_image_from_attachment( int $post_id, int $attachment_id ) {
	if ( empty( $post_id ) || empty( $attachment_id ) ) {
		return false;
	}

	// Set as featured image.
	return set_post_thumbnail( $post_id, $attachment_id ) ? $attachment_id : false;
}

/**
 * Set featured image from image URL.
 *
 * @param int    $post_id Post ID.
 * @param string $image_url Image URL.
 * @param string $title Optional. Image title.
 *
 * @return int|false Attachment ID on success, false otherwise.
 */
function set_featured_image_from_url( int $post_id, string $image_url, string $title = '' ) {
	if ( empty( $post_id ) || empty( $image_url ) ) {
		return false;
	}

	$url = esc_url_raw( $image_url );

	if ( empty( $url ) ) {
		return false;
	}

	// Reuse an image we downloaded from the same URL before.
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_query -- Only runs when a post has no featured image.
			'meta_query'     => array(
				array(
					'key'   => SOURCE_URL_META_KEY,
					'value' => $url,
				),
			),
		)
	);

	if ( ! empty( $existing ) && wp_attachment_is_image( $existing[0] ) ) {
		return set_featured_image_from_attachment( $post_id, (int) $existing[0] );
	}

	if ( ! can_download_for_post( $post_id ) ) {
		return false;
	}

	if ( ! function_exists( 'media_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$download = download_remote_image( $url );

	if ( ! $download ) {
		return false;
	}

	$title = '' !== trim( $title ) ? $title : get_the_title( $post_id );

	$file_array = array(
		'tmp_name' => $download['file'],
		'name'     => $download['name'],
	);

	$post_data = array(
		'post_title'  => $title,
		'post_parent' => $post_id,
	);

	$attachment_id = media_handle_sideload( $file_array, $post_id, null, $post_data );

	if ( is_wp_error( $attachment_id ) ) {
		wp_delete_file( $download['file'] );
		return false;
	}

	update_post_meta( $attachment_id, SOURCE_URL_META_KEY, $url );

	if ( '' !== trim( $title ) ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $title ) );
	}

	set_post_thumbnail( $post_id, $attachment_id );

	return (int) $attachment_id;
}

/**
 * Download a remote image to a temporary file.
 *
 * Uses wp_safe_remote_get() so local and private network addresses are refused, caps the
 * download size and only accepts real image files.
 *
 * @since 1.1.0
 *
 * @param string $url Image URL.
 *
 * @return array{file: string, name: string}|false Temporary file path and file name, or false.
 */
function download_remote_image( string $url ) {
	if ( ! function_exists( 'wp_tempnam' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	/**
	 * Filters the largest remote image we download, in bytes.
	 *
	 * @since 1.1.0
	 *
	 * @param int $max_size Maximum size in bytes. Default 15 MB.
	 */
	$max_size = (int) apply_filters( 'rs_featured_image_max_download_size', 15 * MB_IN_BYTES );

	$path     = (string) wp_parse_url( $url, PHP_URL_PATH );
	$basename = sanitize_file_name( pathinfo( $path, PATHINFO_FILENAME ) );
	$tmp_file = wp_tempnam( $basename ? $basename : 'featured-image' );

	if ( ! $tmp_file ) {
		return false;
	}

	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'             => 20,
			'redirection'         => 3,
			'stream'              => true,
			'filename'            => $tmp_file,
			'limit_response_size' => $max_size,
		)
	);

	$length = (int) wp_remote_retrieve_header( $response, 'content-length' );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) || $length > $max_size || ! file_exists( $tmp_file ) || filesize( $tmp_file ) >= $max_size ) {
		wp_delete_file( $tmp_file );
		return false;
	}

	$extensions = array(
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/gif'  => 'gif',
		'image/webp' => 'webp',
		'image/avif' => 'avif',
		'image/bmp'  => 'bmp',
	);

	$mime = wp_get_image_mime( $tmp_file );

	if ( ! $mime || ! isset( $extensions[ $mime ] ) ) {
		wp_delete_file( $tmp_file );
		return false;
	}

	/**
	 * Filters the largest image we accept, in megapixels.
	 *
	 * A small file can claim huge dimensions and exhaust memory when sizes are generated.
	 *
	 * @since 1.1.0
	 *
	 * @param int $max_megapixels Default 50.
	 */
	$max_pixels = (int) apply_filters( 'rs_featured_image_max_megapixels', 50 ) * 1000000;
	$size       = wp_getimagesize( $tmp_file );

	if ( ! $size || empty( $size[0] ) || empty( $size[1] ) || ( (int) $size[0] * (int) $size[1] ) > $max_pixels ) {
		wp_delete_file( $tmp_file );
		return false;
	}

	return array(
		'file' => $tmp_file,
		'name' => ( $basename ? $basename : 'featured-image' ) . '.' . $extensions[ $mime ],
	);
}

/**
 * Set featured image from image URL.
 *
 * @param int    $post_id Post ID.
 * @param string $image_url Image URL.
 *
 * @return int|false Attachment ID on success, false otherwise
 */
function set_featured_image_from_existing_image( int $post_id, string $image_url ) {
	if ( empty( $post_id ) || empty( $image_url ) ) {
		return false;
	}

	$url = esc_url_raw( $image_url );

	// Return early if URL is empty.
	if ( empty( $url ) ) {
		return false;
	}

	// Try to resolve attachment ID.
	$attachment_id = get_attachment_by_url( $url );

	// If still no attachment ID, skip.
	if ( ! $attachment_id ) {
		return false;
	}

	// Validate attachment type.
	if ( get_post_type( $attachment_id ) !== 'attachment' || ! wp_attachment_is_image( $attachment_id ) ) {
		return false;
	}

	return set_featured_image_from_attachment( $post_id, $attachment_id );
}
