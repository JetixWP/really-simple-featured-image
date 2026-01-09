<?php
/**
 * Plugin Name: Really Simple Featured Image
 * Plugin URI:  https://github.com/JetixWP/really-simple-featured-image
 * Description: Automatically set the featured image from Image or Youtube, Vimeo, Dailymotion video in content.
 * Version:     1.0.3
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

define( 'RS_FEATURED_IMAGE_VERSION', '1.0.3' );
define( 'RS_FEATURED_IMAGE_PLUGIN_FILE', __FILE__ );
define( 'RS_FEATURED_IMAGE_PLUGIN_URL', plugin_dir_url( RS_FEATURED_IMAGE_PLUGIN_FILE ) );
define( 'RS_FEATURED_IMAGE_PLUGIN_DIR', plugin_dir_path( RS_FEATURED_IMAGE_PLUGIN_FILE ) );
define( 'RS_FEATURED_IMAGE_PLUGIN_BASE', plugin_basename( RS_FEATURED_IMAGE_PLUGIN_FILE ) );
define( 'RS_FEATURED_IMAGE_PLUGIN_PRO_URL', 'https://jetixwp.com/plugins/really-simple-featured-image' );

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
