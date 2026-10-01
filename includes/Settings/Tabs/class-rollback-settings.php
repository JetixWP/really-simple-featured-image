<?php
/**
 * Rollback Settings
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Settings;

defined( 'ABSPATH' ) || exit;

use RS_Featured_Image\Featuresets\Rollback\Init as Rollback;

/**
 * Rollback tab: reinstall an earlier version. PRO adds its own section through
 * the rs_featured_image_version_control_settings filter.
 */
class Rollback_Settings extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// The id stays version_control so links to the tab keep working.
		$this->id    = 'version_control';
		$this->label = __( 'Rollback', 'really-simple-featured-image' );

		parent::__construct();
	}

	/**
	 * Output the settings. This tab has nothing to save.
	 */
	public function output() {
		$GLOBALS['hide_save_button'] = true;

		parent::output();
	}

	/**
	 * Save settings. Nothing to save on this tab.
	 */
	public function save() {}

	/**
	 * Get settings array.
	 *
	 * @param string $rs_featured_image_settings_current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $rs_featured_image_settings_current_section = '' ) {
		$settings = array();

		if ( current_user_can( 'update_plugins' ) ) {
			$versions = Rollback::get_rollback_versions();

			$settings[] = array(
				'title' => __( 'Rollback Versions', 'really-simple-featured-image' ),
				'desc'  => empty( $versions )
					? __( 'There are no earlier versions of Really Simple Featured Image to reinstall right now.', 'really-simple-featured-image' )
					: __( 'If you are having issues with the current version of Really Simple Featured Image, you can reinstall an earlier stable version from WordPress.org. Your settings are kept.', 'really-simple-featured-image' ),
				'type'  => 'title',
				'id'    => 'rs_featured_image_plugin_rollback_version',
			);

			if ( ! empty( $versions ) ) {
				$settings[] = array(
					'title'     => __( 'Rollback Really Simple Featured Image', 'really-simple-featured-image' ),
					'id'        => 'rs_featured_image_rollback_version_select_option',
					'type'      => 'select',
					'options'   => array_combine( $versions, $versions ),
					'is_option' => false,
				);
				$settings[] = array(
					'id'    => 'rs_featured_image_rollback_version_button',
					'type'  => 'button',
					'class' => 'rs-featured-image-rollback-version-button rs-featured-image-button button-secondary',
					'value' => __( 'Reinstall this version', 'really-simple-featured-image' ),
				);
			}

			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'rs_featured_image_plugin_rollback',
			);
		}

		$settings = apply_filters( 'rs_featured_image_version_control_settings', $settings );

		return apply_filters( 'rs_featured_image_get_settings_' . $this->id, $settings );
	}
}

return new Rollback_Settings();
