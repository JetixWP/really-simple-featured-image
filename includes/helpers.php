<?php
/**
 * Helper functions for the plugin.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image;

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
