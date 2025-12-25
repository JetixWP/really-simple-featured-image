<?php
/**
 * General Settings
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Settings;

defined( 'ABSPATH' ) || exit;

use RS_Featured_Image\Plugin;

/**
 * Image.
 */
class Image extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'image';
		$this->label = __( 'Image in Content', 'really-simple-featured-image' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @param string $rs_featured_image_settings_current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $rs_featured_image_settings_current_section = '' ) {

		$settings = array(
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Scan Images in Content', 'really-simple-featured-image' ),
				'desc'  => esc_html__( 'Settings responsible for scanning images within post content to set as featured images.', 'really-simple-featured-image' ),
			),
			array(
				'title'   => esc_html__( 'Scan Content Length (Characters)', 'really-simple-featured-image' ),
				'desc'    => __( 'Set the maximum number of characters to scan/read from post content when searching for images to set as the featured image. Increasing this value may impact performance on large posts.', 'really-simple-featured-image' ),
				'id'      => 'image_content_length',
				'type'    => 'number',
				'default' => 6000,
			),
			array(
				'title'   => esc_html__( 'Image Position in Content', 'really-simple-featured-image' ),
				'desc'    => __( 'Set which image position in the content to use as the featured image. For example, setting this to Second Image will set the 2nd image found in the content as the featured image.', 'really-simple-featured-image' ),
				'id'      => 'image_content_position',
				'type'    => 'select',
				'default' => 'first',
				'options' => array(
					'first'       => esc_html__( 'First Image', 'really-simple-featured-image' ),
					'second'      => esc_html__( 'Second Image', 'really-simple-featured-image' ),
					'last-second' => esc_html__( 'Second Last Image', 'really-simple-featured-image' ),
					'last'        => esc_html__( 'Last Image', 'really-simple-featured-image' ),
				),
			),
		);

		$settings = array_merge(
			$settings,
			array(
				array(
					'type' => 'sectionend',
				),
			)
		);

		$settings = apply_filters(
			'rs_featured_image_' . $this->id . '_settings',
			$settings,
		);

		return apply_filters( 'rs_featured_image_get_settings_' . $this->id, $settings );
	}
}

return new Image();
