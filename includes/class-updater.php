<?php
/**
 * Plugin updates handler.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image;

defined( 'ABSPATH' ) || exit;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Plugin updater class.
 */
class Updater {
	const REPO_URL    = 'https://github.com/JetixWP/really-simple-featured-image';
	const PLUGIN_SLUG = 'really-simple-featured-image';
	const PLUGIN_FILE = RS_FEATURED_IMAGE_PLUGIN_FILE;

	/**
	 * Initialize the updater.
	 */
	public static function init_updater() {
		$update_checker = PucFactory::buildUpdateChecker(
			self::REPO_URL,
			self::PLUGIN_FILE,
			self::PLUGIN_SLUG,
		);

		$update_vcs_api = $update_checker->getVcsApi();
		if ( method_exists( $update_vcs_api, 'enableReleaseAssets' ) ) {
			$update_vcs_api->enableReleaseAssets();
		}
	}
}
