<?php
/**
 * Register and initialize Sources.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources;

use RS_Featured_Image\Options;
use RS_Featured_Image\Utils\Has_Instance;

/**
 * Class Register_Sources
 */
class Register_Sources {
	use Has_Instance;

	/**
	 * Get source options.
	 *
	 * @return array
	 */
	public static function get_source_options() {
		return array(
			'content-image' => esc_html__( 'Image in Post Content', 'really-simple-featured-image' ),
			'content-video' => esc_html__( 'Video in Post Content', 'really-simple-featured-image' ),
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$options = Options::get_instance();

		$has_enable_automatic_featured_images = $options->has( 'enable_automatic_featured_images' );
		$enable_automatic_featured_images     = $options->get( 'enable_automatic_featured_images' );

		// If the option is not set or is disabled, return early.
		if ( $has_enable_automatic_featured_images && ! $enable_automatic_featured_images ) {
			return;
		}

		$this->init_sources();
	}

	/**
	 * Initialize all Sources.
	 */
	public function init_sources() {
		require_once __DIR__ . '/class-source-content.php';
		require_once __DIR__ . '/class-source-video.php';

		do_action( 'rs_featured_image_after_sources_initialize' );
	}
}
