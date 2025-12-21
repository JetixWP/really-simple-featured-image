<?php
/**
 * Helper functions for the plugin.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image;

use function media_handle_sideload;

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
 * @return bool True on success, false otherwise.
 */
function set_featured_image_from_attachment( int $post_id, int $attachment_id ) {
	if ( empty( $post_id ) || empty( $attachment_id ) ) {
		return false;
	}

	// Set as featured image.
	set_post_thumbnail( $post_id, $attachment_id );

	return true;
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

	if ( ! function_exists( 'media_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$tmp = download_url( $url );

	if ( is_wp_error( $tmp ) ) {
		return false;
	}

	$file_array             = array();
	$file_array['tmp_name'] = $tmp;
	$file_array['name']     = basename( wp_parse_url( $url, PHP_URL_PATH ) );

	if ( empty( pathinfo( $file_array['name'], PATHINFO_EXTENSION ) ) ) {
		$file_array['name'] .= '.jpg'; // Force extension.
	}

	$post_data = array(
		'post_title'  => get_the_title( $post_id ),
		'post_parent' => $post_id,
	);

	$attachment_id = media_handle_sideload( $file_array, $post_id, null, $post_data );

	if ( is_wp_error( $attachment_id ) ) {
		wp_delete_file( $tmp );
		return false;
	}

	set_post_thumbnail( $post_id, $attachment_id );

	return true;
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
	if ( get_post_type( $attachment_id ) !== 'attachment' ) {
		return false;
	}

	return set_featured_image_from_attachment( $post_id, $attachment_id );
}
