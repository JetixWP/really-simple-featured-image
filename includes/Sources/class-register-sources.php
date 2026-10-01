<?php
/**
 * Register and initialize Sources.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources;

defined( 'ABSPATH' ) || exit;

use RS_Featured_Image\Options;
use RS_Featured_Image\Utils\Has_Instance;

use const RS_Featured_Image\SKIP_META_KEY;

/**
 * Class Register_Sources
 */
class Register_Sources {
	use Has_Instance;

	/**
	 * IDs of posts being permanently deleted right now.
	 *
	 * @var int[]
	 */
	protected $deleting_posts = array();

	/**
	 * Get source options.
	 *
	 * @return array
	 */
	public static function get_source_options() {
		return array(
			'content-image' => esc_html__( 'Image in Post Content', 'really-simple-featured-image' ),
			'content-video' => esc_html__( 'Video in Post Content', 'really-simple-featured-image' ),
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Remember removed featured images even while automatic images are off.
		add_action( 'before_delete_post', array( $this, 'mark_post_deleting' ) );
		add_action( 'deleted_post', array( $this, 'unmark_post_deleting' ) );
		add_action( 'deleted_post_meta', array( $this, 'remember_removed_thumbnail' ), 10, 3 );

		$options = Options::get_instance();

		$has_enable_automatic_featured_images = $options->has( 'enable_automatic_featured_images' );
		$enable_automatic_featured_images     = $options->get( 'enable_automatic_featured_images' );

		// If the option is not set or is disabled, return early.
		if ( $has_enable_automatic_featured_images && ! $enable_automatic_featured_images ) {
			return;
		}

		$this->init_sources();
	}

	/**
	 * Initialize all Sources.
	 */
	public function init_sources() {
		require_once __DIR__ . '/class-source-content.php';
		require_once __DIR__ . '/class-source-video.php';

		do_action( 'rs_featured_image_after_sources_initialize' );
	}

	/**
	 * Track a post that is being permanently deleted.
	 *
	 * @param int $post_id Post ID.
	 */
	public function mark_post_deleting( $post_id ) {
		$this->deleting_posts[] = (int) $post_id;
	}

	/**
	 * Stop tracking a post once it has been deleted.
	 *
	 * @param int $post_id Post ID.
	 */
	public function unmark_post_deleting( $post_id ) {
		$this->deleting_posts = array_diff( $this->deleting_posts, array( (int) $post_id ) );
	}

	/**
	 * When a person removes a featured image, do not set one automatically again for that post.
	 *
	 * @param string[] $meta_ids  Deleted meta IDs.
	 * @param int      $post_id   Post ID.
	 * @param string   $meta_key  Meta key.
	 */
	public function remember_removed_thumbnail( $meta_ids, $post_id, $meta_key ) {
		$post_id = (int) $post_id;

		// Object ID is 0 when an attachment is deleted and removed from every post using it.
		if ( '_thumbnail_id' !== $meta_key || ! $post_id || in_array( $post_id, $this->deleting_posts, true ) ) {
			return;
		}

		if ( ! get_post( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, SKIP_META_KEY, 1 );
	}
}
