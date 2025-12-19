<?php
/**
 * Main plugin class.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image;

use RS_Featured_Image\Settings\Register_Settings;

/**
 * Class RS_Featured_Image\Plugin.
 */
final class Plugin {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Options manager.
	 *
	 * @var Options
	 */
	public $options_manager;

	/**
	 * Settings Manager.
	 *
	 * @var Register_Settings;
	 */
	public $settings_manager;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->register();
	}

	/**
	 * Get a class instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			/**
			 * ReallySimpleFeaturedImage loaded.
			 *
			 * Fires when ReallySimpleFeaturedImage is fully loaded and instantiated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'rs_featured_image_loaded' );
		}

		return self::$instance;
	}

	/**
	 * Registers plugin classes & translation.
	 *
	 * @return void
	 */
	public function register() {
		// Register Settings.
		// $this->settings_manager = new Register_Settings();

		// Register action links.
		add_filter( 'network_admin_plugin_action_links_' . RS_FEATURED_IMAGE_PLUGIN_BASE, array( $this, 'filter_plugin_action_links' ) );
		add_filter( 'plugin_action_links_' . RS_FEATURED_IMAGE_PLUGIN_BASE, array( $this, 'filter_plugin_action_links' ) );
	}

	/**
	 * Include plugin files.
	 *
	 * @return void
	 */
	public function includes() {
		require_once RS_FEATURED_IMAGE_PLUGIN_DIR . 'includes/helpers.php';
		require_once RS_FEATURED_IMAGE_PLUGIN_DIR . 'includes/class-options.php';
		// require_once RS_FEATURED_IMAGE_PLUGIN_DIR . 'includes/Settings/class-register-settings.php';
	}

	/**
	 * Add settings link at plugins page action links.
	 *
	 * @param array $actions Action links.
	 *
	 * @return array
	 */
	public function filter_plugin_action_links( array $actions ) {
		$settings_url = admin_url( 'admin.php?page=rs-featured-image-settings' );

		return array_merge(
			array(
				'settings' => "<a href='{$settings_url}'>" . esc_html__( 'Settings', 'really-simple-featured-image' ) . '</a>',
			),
			$actions
		);
	}
}
