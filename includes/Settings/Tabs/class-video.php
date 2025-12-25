<?php
/**
 * Video Settings
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Settings;

defined( 'ABSPATH' ) || exit;

use RS_Featured_Image\Plugin;

/**
 * Video.
 */
class Video extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'video';
		$this->label = __( 'Video in Content', 'really-simple-featured-image' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$settings = array(
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Scan Videos in Content', 'really-simple-featured-image' ),
				'desc'  => esc_html__( 'Settings responsible for scanning videos within post content to use to generate and set featured images.', 'really-simple-featured-image' ),
			),
			array(
				'title'   => esc_html__( 'Scan Content Length (Characters)', 'really-simple-featured-image' ),
				'desc'    => __( 'Set the maximum number of characters to scan/read from post content when searching for videos to set as the featured image. Increasing this value may impact performance on large posts.', 'really-simple-featured-image' ),
				'id'      => 'video_content_length',
				'type'    => 'number',
				'default' => 6000,
			),
			array(
				'title'   => esc_html__( 'Video Position in Content', 'really-simple-featured-image' ),
				'desc'    => __( 'Set which video position in the content to use as the featured image. For example, setting this to Second Video will use the 2nd video found in the content to generate thumbnail and set as the featured image.', 'really-simple-featured-image' ),
				'id'      => 'video_content_position',
				'type'    => 'select',
				'default' => 'first',
				'options' => array(
					'first'       => esc_html__( 'First Video', 'really-simple-featured-image' ),
					'second'      => esc_html__( 'Second Video', 'really-simple-featured-image' ),
					'last-second' => esc_html__( 'Second Last Video', 'really-simple-featured-image' ),
					'last'        => esc_html__( 'Last Video', 'really-simple-featured-image' ),
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
			$settings
		);

		return apply_filters( 'rs_featured_image_get_settings_' . $this->id, $settings );
	}
}

return new Video();
