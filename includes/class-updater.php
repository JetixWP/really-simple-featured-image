<?php
/**
 * Migrations handler.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image;

defined( 'ABSPATH' ) || exit;

/**
 * Class Updater
 */
class Updater {

	/**
	 * The slug of db option.
	 *
	 * @var string
	 */
	const OPTION = 'rs_featured_image_db_version';

	/**
	 * The slug of previous db option.
	 *
	 * @var string
	 */
	const PREVIOUS_OPTION = 'rs_featured_image_previous_db_version';

	/**
	 * Hooked into admin_init and walks through an array of upgrade methods.
	 *
	 * @return void
	 */
	public function init() {
		$routines = array(
			'1.1.0' => 'upgrade_1_1_0',
		);

		$version = get_option( self::OPTION, '1.0.4' );

		if ( version_compare( RS_FEATURED_IMAGE_VERSION, $version, '=' ) ) {
			return;
		}

		array_walk( $routines, array( $this, 'run_upgrade_routine' ), $version );
		$this->finish_up( $version );
	}

	/**
	 * Runs the upgrade routine.
	 *
	 * @param string $routine The method to call.
	 * @param string $version The new version.
	 * @param string $current_version The current set version.
	 *
	 * @return void
	 */
	protected function run_upgrade_routine( $routine, $version, $current_version ) {
		if ( version_compare( $current_version, $version, '<' ) ) {
			$this->$routine( $current_version );
		}
	}

	/**
	 * Runs the needed cleanup after an update, setting the DB version to latest version.
	 *
	 * @param string $previous_version The previous version.
	 *
	 * @return void
	 */
	protected function finish_up( $previous_version ) {
		update_option( self::PREVIOUS_OPTION, $previous_version, false );
		update_option( self::OPTION, RS_FEATURED_IMAGE_VERSION, false );
	}

	/**
	 * Upgrade to 1.1.0.
	 *
	 * Fixes a Default Source value that is not one of the choices and stores scan lengths as numbers.
	 * Leaves every other setting as it is.
	 *
	 * @return void
	 */
	protected function upgrade_1_1_0() {
		$options = Options::get_instance();

		if ( $options->has( 'default_source' ) && ! in_array( $options->get( 'default_source' ), array( 'content-image', 'content-video' ), true ) ) {
			$options->set( 'default_source', 'content-image' );
		}

		foreach ( array( 'image_content_length', 'video_content_length' ) as $key ) {
			if ( $options->has( $key ) ) {
				$options->set( $key, get_scan_length( str_replace( '_content_length', '', $key ) ) );
			}
		}

		// Written on every settings save before 1.1.0 and never read.
		delete_option( 'rs_featured_image_queue_flush_rewrite_rules' );
	}
}
