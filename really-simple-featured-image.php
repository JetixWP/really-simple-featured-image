<?php
/**
 * Plugin Name: Really Simple Featured Image
 * Plugin URI:  https://github.com/JetixWP/really-simple-featured-image
 * Description: Automatically set the featured image for any CPT from the image or Youtube, Vimeo and Dailymotion video in content.
 * Version:     1.0.0
 * Author:      JetixWP Plugins
 * Author URI:  https://jetixwp.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: really-simple-featured-image
 * Domain Path: /languages/
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package ReallySimpleFeaturedImage
 */

defined( 'ABSPATH' ) || exit;

define( 'RS_FEATURED_IMAGE_VERSION', '1.0.0' );
define( 'RS_FEATURED_IMAGE_PLUGIN_FILE', __FILE__ );
define( 'RS_FEATURED_IMAGE_PLUGIN_URL', plugin_dir_url( RS_FEATURED_IMAGE_PLUGIN_FILE ) );
define( 'RS_FEATURED_IMAGE_PLUGIN_DIR', plugin_dir_path( RS_FEATURED_IMAGE_PLUGIN_FILE ) );
define( 'RS_FEATURED_IMAGE_PLUGIN_BASE', plugin_basename( RS_FEATURED_IMAGE_PLUGIN_FILE ) );
define( 'RS_FEATURED_IMAGE_PLUGIN_PRO_URL', '' );

// Third party dependencies.
$vendor_file = __DIR__ . '/vendor/autoload.php';

if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}

/**
 * Include and initialize the updater.
 */
require_once RS_FEATURED_IMAGE_PLUGIN_DIR . 'includes/class-updater.php';
\RS_Featured_Image\Updater::init_updater();

/**
 * Initialize Freemius SDK.
 */
if ( ! function_exists( 'rsfi_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function rsfi_fs() {
		global $rsfi_fs;

		if ( ! function_exists( 'fs_dynamic_init' ) && file_exists( __DIR__ . '/vendor/freemius/wordpress-sdk/start.php' ) ) {
			require_once __DIR__ . '/vendor/freemius/wordpress-sdk/start.php';
		}

		if ( ! isset( $rsfi_fs ) && function_exists( 'fs_dynamic_init' ) ) {
			$rsfi_fs = fs_dynamic_init(
				array(
					'id'             => '22490',
					'slug'           => 'really-simple-featured-image',
					'type'           => 'plugin',
					'public_key'     => 'pk_cdc4b578d06509291b1e11d9339cf',
					'is_premium'     => false,
					'has_addons'     => true,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'       => 'rs-featured-image-settings',
						'first-path' => 'admin.php?page=rs-featured-image-settings',
						'support'    => false,
						'account'    => false,
						'contact'    => false,
						'parent'     => array(
							'slug' => 'jetixwp',
						),
					),
				)
			);
		}

		return $rsfi_fs;
	}

	// Init Freemius.
	rsfi_fs();
	// Signal that SDK was initiated.
	do_action( 'rsfi_fs_loaded' );
}

/**
 * Fire up plugin instance.
 */
add_action(
	'plugins_loaded',
	static function () {
		require_once RS_FEATURED_IMAGE_PLUGIN_DIR . 'includes/class-plugin.php';

		// Main instance.
		\RS_Featured_Image\Plugin::get_instance();
	}
);
