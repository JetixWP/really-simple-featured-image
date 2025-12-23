<?php
/**
 * Rollback Feature
 *
 * @package RS_Featured_Image
 */

namespace RS_Featured_Image\Featuresets\Rollback;

defined( 'ABSPATH' ) || exit;

/**
 * Init Class.
 */
class Init {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Get a class instance.
	 *
	 * @return Init
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
	}
}

new Init();
