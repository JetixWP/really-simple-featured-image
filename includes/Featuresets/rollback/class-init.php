<?php
/**
 * Rollback to an earlier version of the free plugin from WordPress.org.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Featuresets\Rollback;

defined( 'ABSPATH' ) || exit;

use RS_Featured_Image\Utils\Has_Instance;

/**
 * Class Init
 */
class Init {
	use Has_Instance;

	/**
	 * Plugin slug on WordPress.org.
	 *
	 * @var string
	 */
	const SLUG = 'really-simple-featured-image';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'rs_featured_image_settings_localized_data', array( $this, 'add_rollback_data' ) );
		add_action( 'admin_post_rs_featured_image_rollback', array( $this, 'handle_rollback' ) );
	}

	/**
	 * Add the rollback request URL to the settings script data.
	 *
	 * @param array $data Localized data.
	 * @return array
	 */
	public function add_rollback_data( $data ) {
		$data['rollback_url']          = wp_nonce_url( admin_url( 'admin-post.php?action=rs_featured_image_rollback' ), 'rs_featured_image_rollback' );
		$data['i18n_rollback_confirm'] = __( 'Reinstall this version of the plugin? Your settings are kept.', 'really-simple-featured-image' );

		return $data;
	}

	/**
	 * Check whether rollbacks are allowed on this site for the current user.
	 *
	 * @return bool
	 */
	public static function can_rollback() {
		// JETIXWP_DEBUG keeps development copies from being replaced.
		return current_user_can( 'update_plugins' ) && ! ( defined( 'JETIXWP_DEBUG' ) && JETIXWP_DEBUG );
	}

	/**
	 * Reinstall the requested version.
	 *
	 * @return void
	 */
	public function handle_rollback() {
		check_admin_referer( 'rs_featured_image_rollback' );

		if ( ! self::can_rollback() ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to rollback Really Simple Featured Image on this site.', 'really-simple-featured-image' ) );
		}

		$version = isset( $_GET['version'] ) ? sanitize_text_field( wp_unslash( $_GET['version'] ) ) : '';

		if ( '' === $version || ! in_array( $version, self::get_rollback_versions(), true ) ) {
			wp_die( esc_html__( 'Error occurred, the version selected is invalid. Try selecting different version.', 'really-simple-featured-image' ) );
		}

		// If WordPress asks for FTP details, the form posts back here and reruns this rollback.
		$skin_url = add_query_arg(
			array(
				'action'  => 'rs_featured_image_rollback',
				'version' => rawurlencode( $version ),
			),
			admin_url( 'admin-post.php' )
		);

		$rollbacker = new Rollbacker(
			array(
				'version'     => $version,
				'plugin_name' => RS_FEATURED_IMAGE_PLUGIN_BASE,
				'plugin_slug' => self::SLUG,
				'package_url' => sprintf( 'https://downloads.wordpress.org/plugin/%s.%s.zip', self::SLUG, rawurlencode( $version ) ),
				'skin_url'    => wp_nonce_url( $skin_url, 'rs_featured_image_rollback' ),
			)
		);

		self::run_in_page( $rollbacker );
	}

	/**
	 * Run a rollback inside a minimal HTML page and stop.
	 *
	 * @param Rollbacker $rollbacker Rollbacker to run.
	 * @return void
	 */
	public static function run_in_page( Rollbacker $rollbacker ) {
		echo '<!DOCTYPE html><html ' . get_language_attributes() . '><head><meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '"><meta name="viewport" content="width=device-width"><title>' . esc_html__( 'Rollback to Previous Version', 'really-simple-featured-image' ) . '</title></head><body>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_language_attributes() returns escaped attributes.

		$rollbacker->run();

		echo '</body></html>';
		exit;
	}

	/**
	 * Earlier versions available on WordPress.org, newest first.
	 *
	 * @return string[]
	 */
	public static function get_rollback_versions() {
		$cache_key = 'rs_featured_image_rollback_versions_' . RS_FEATURED_IMAGE_VERSION;
		$versions  = get_transient( $cache_key );

		if ( is_array( $versions ) ) {
			return $versions;
		}

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		$info = plugins_api(
			'plugin_information',
			array(
				'slug'   => self::SLUG,
				'fields' => array( 'versions' => true ),
			)
		);

		// Do not cache failures, try again next time.
		if ( is_wp_error( $info ) || empty( $info->versions ) || ! is_array( $info->versions ) ) {
			return array();
		}

		$versions = array();

		foreach ( array_keys( $info->versions ) as $version ) {
			$version = (string) $version;

			if ( preg_match( '/^\d+(\.\d+){1,3}$/', $version ) && version_compare( $version, RS_FEATURED_IMAGE_VERSION, '<' ) ) {
				$versions[] = $version;
			}
		}

		usort(
			$versions,
			static function ( $a, $b ) {
				return version_compare( $b, $a );
			}
		);

		$versions = array_slice( $versions, 0, 30 );

		set_transient( $cache_key, $versions, DAY_IN_SECONDS );

		return $versions;
	}
}
