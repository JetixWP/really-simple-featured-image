<?php
/**
 * General Settings
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Settings;

defined( 'ABSPATH' ) || exit;

use RS_Featured_Image\Sources\Register_Sources as Sources;

/**
 * General.
 */
class General extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'Source', 'really-simple-featured-image' );

		parent::__construct();
	}

	/**
	 * Gets available post types.
	 *
	 * @return mixed|array
	 */
	public function get_available_post_types() {
		$post_types                        = array();
		$supported_post_types              = \get_post_types();
		$post_types_with_thumbnail_support = \get_post_types_by_support( 'thumbnail' );

		// Just in case.
		if ( ! is_array( $post_types_with_thumbnail_support ) ) {
			$post_types_with_thumbnail_support = array();
		}

		foreach ( $supported_post_types as $post_type ) {
			if ( ! isset( $post_types[ $post_type ] ) && in_array( $post_type, $post_types_with_thumbnail_support, true ) ) {
				$post_types[ $post_type ] = get_post_type_object( $post_type )->labels->name;
			}
		}

		return $post_types;
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$post_types = $this->get_available_post_types();

		$default_enabled_post_types = apply_filters(
			'rs_featured_image_default_enabled_post_types',
			array(
				'post' => true,
				'page' => true,
			)
		);

		$settings = array(
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Automatic Featured Image Source', 'really-simple-featured-image' ),
				'desc'  => esc_html__( 'Choose the default source for automatic featured images. Once set we will fetch images from this source when no featured image is set and set it for you.', 'really-simple-featured-image' ),
			),
			array(
				'title'   => esc_html__( 'Enable Automatic Images', 'really-simple-featured-image' ),
				'id'      => 'enable_automatic_featured_images',
				'type'    => 'checkbox',
				'default' => true,
				'desc'    => esc_html__( 'Once ticked, automatic featured images will be set for enabled CPTs.', 'really-simple-featured-image' ),
			),
			array(
				'title'   => esc_html__( 'Default Source', 'really-simple-featured-image' ),
				'id'      => 'default_source',
				'type'    => 'select',
				'class'   => 'rs-featured-image-select2',
				'default' => 'content',
				'options' => Sources::get_source_options(),
			),
			array(
				'type' => 'sectionend',
			),
			array(
				'title' => esc_html_x( 'Enable Post Types Support', 'settings title', 'really-simple-featured-image' ),
				'desc'  => __( 'Please select the post types you wish to enable automatic featured image support at.', 'really-simple-featured-image' ),
				'class' => 'rs-featured-image-enable-post-types',
				'type'  => 'content',
				'id'    => 'rs-featured-image-enable-post-types',
			),
			array(
				'type' => 'title',
				'id'   => 'rs-featured-image-post-types-title',
			),
			array(
				'title'   => '',
				'id'      => 'post_types',
				'default' => $default_enabled_post_types,
				'type'    => 'multi-checkbox',
				'options' => $post_types,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rs_featured_image_post_types_title',
			),
		);

		$settings = apply_filters(
			'rs_featured_image_' . $this->id . '_settings',
			$settings
		);

		return apply_filters( 'rs_featured_image_get_settings_' . $this->id, $settings );
	}
}

return new General();
