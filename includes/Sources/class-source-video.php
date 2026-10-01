<?php
/**
 * Source video.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources;

defined( 'ABSPATH' ) || exit;

use WP_Post;
use DOMDocument;
use RS_Featured_Image\Options;
use RS_Featured_Image\Utils\Has_Instance;
use RS_Featured_Image\Sources\Video\Youtube_Video;
use RS_Featured_Image\Sources\Video\Vimeo_Video;
use RS_Featured_Image\Sources\Video\Dailymotion_Video;
use function RS_Featured_Image\set_featured_image_from_url;
use function RS_Featured_Image\get_items_by_position;
use function RS_Featured_Image\get_max_attempts;
use function RS_Featured_Image\should_process_post;
use function RS_Featured_Image\get_scan_length;

/**
 * Class Source_Video
 */
class Source_Video {
	use Has_Instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Include video providers.
		$this->include_video_providers();

		// Hook into save_post to check for videos.
		add_action( 'wp_after_insert_post', array( $this, 'check_content_for_videos' ), 10, 2 );

		// Hook into the featured image setting action.
		add_action( 'rs_featured_image_setting_featured_image_from_content_video', array( __CLASS__, 'set_featured_image_from_videos' ), 10, 2 );
	}

	/**
	 * Include video provider classes.
	 */
	public function include_video_providers() {
		require_once __DIR__ . '/Video/class-youtube-video.php';
		require_once __DIR__ . '/Video/class-vimeo-video.php';
		require_once __DIR__ . '/Video/class-dailymotion-video.php';
	}

	/**
	 * Check post content for images to set as featured image.
	 *
	 * @param int|WP_Post $post_id Post ID/Post object.
	 * @param WP_Post     $post Post object.
	 */
	public function check_content_for_videos( int|WP_Post $post_id, WP_Post $post ) {
		// Check if the option to use content videos is enabled.
		$options = Options::get_instance();

		$source_set = $options->get( 'default_source' );

		// If source is set and it's not 'content-video', return early.
		if ( 'content-video' !== $source_set ) {
			return;
		}

		// Prevent trying to assign when trashing or untrashing posts in the list screen.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'trash', 'untrash', 'add-menu-item' ), true ) ) {
			return;
		}

		// Only enabled post types, real posts and posts without a featured image.
		if ( ! should_process_post( $post ) ) {
			return;
		}

		$post_id = $post->ID;

		$content = $post->post_content ?? '';

		// If content is empty return early.
		if ( empty( $content ) ) {
			return;
		}

		$content_length = absint( apply_filters( 'rs_featured_image_read_content_length_limit', get_scan_length( 'video' ), $source_set, $post_id ) );

		// Limit how much content is read to keep saves fast.
		$content = substr( $content, 0, $content_length );

		// Extract Video URLs from content.
		$video_urls = $this->get_video_data_from_content( $content );

		if ( empty( $video_urls ) ) {
			return;
		}

		// Trigger actions before, during, and after setting the featured image.
		do_action( 'rs_featured_image_before_setting_featured_image_from_content_video', $post_id, $video_urls );

		// Triggers the actual setting of the featured image.
		do_action( 'rs_featured_image_setting_featured_image_from_content_video', $post_id, $video_urls );

		// Action after setting the featured image.
		do_action( 'rs_featured_image_after_setting_featured_image_from_content_video', $post_id, $video_urls );
	}

	/**
	 * Extract YouTube, Vimeo, and Dailymotion videos from content.
	 *
	 * @param string $content Post content.
	 *
	 * @return array[]
	 */
	public function get_video_data_from_content( $content ) {
		$results  = array();
		$patterns = $this->get_video_provider_patterns();

		if ( empty( $content ) ) {
			return $results;
		}

		// 1. DOM-based extraction (iframe, anchor)
		if ( class_exists( 'DOMDocument' ) ) {
			$libxml_errors = libxml_use_internal_errors( true );

			$dom = new DOMDocument();
			$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content );

			foreach ( array( 'iframe', 'a' ) as $tag ) {
				foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
					foreach ( array( 'src', 'href', 'data-src' ) as $attr ) {
						if ( ! $node->hasAttribute( $attr ) ) {
							continue;
						}

						$url = $node->getAttribute( $attr );

						foreach ( $patterns as $host => $regex ) {
							if ( preg_match( $regex, $url, $m ) ) {
								$results[] = array(
									'host' => $host,
									'id'   => $m[1],
								);
								break;
							}
						}
					}
				}
			}

			libxml_clear_errors();
			libxml_use_internal_errors( $libxml_errors );
		}

		// 2. Raw URL scanning (plain text / builders)
		foreach ( $patterns as $host => $regex ) {
			preg_match_all( $regex, $content, $matches );
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $id ) {
					$results[] = array(
						'host' => $host,
						'id'   => $id,
					);
				}
			}
		}

		// 3. De-duplicate (host + id)
		$unique = array();
		foreach ( $results as $item ) {
			$key            = $item['host'] . ':' . $item['id'];
			$unique[ $key ] = $item;
		}

		return array_values( $unique );
	}

	/**
	 * Get video provider regex patterns.
	 *
	 * @return array Video provider patterns.
	 */
	public function get_video_provider_patterns() {
		return array(
			'youtube'     => '#(?:https?:)?(?://)?(?:www\.|m\.)?(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:[^"\'\s<>]*?&(?:amp;)?)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([a-zA-Z0-9_-]{11})#i',
			'vimeo'       => '#(?:https?:)?(?://)?(?:www\.)?(?:vimeo\.com/(?:video/|channels/[\w-]+/|groups/[\w-]+/videos/)?|player\.vimeo\.com/video/)(\d+)#i',
			'dailymotion' => '#(?:https?:)?(?://)?(?:www\.|geo\.)?(?:dailymotion\.com/(?:embed/)?video/|dai\.ly/|dailymotion\.com/player(?:/[\w-]+)?\.html\?video=)([a-zA-Z0-9]+)#i',
		);
	}

	/**
	 * Get video data by host and ID.
	 *
	 * @param string $host Video host (e.g., 'youtube', 'vimeo', 'dailymotion').
	 * @param string $video_id Video ID.
	 *
	 * @return array Video data array.
	 */
	public static function get_video_data_by_host_and_id( string $host, string $video_id ) {
		$cache_key = 'rs_featured_image_video_' . md5( $host . ':' . $video_id );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		switch ( $host ) {
			case 'youtube':
				$video_data = Youtube_Video::get_data_by_id( $video_id );
				break;
			case 'vimeo':
				$video_data = Vimeo_Video::get_data_by_id( $video_id );
				break;
			case 'dailymotion':
				$video_data = Dailymotion_Video::get_data_by_id( $video_id );
				break;
			default:
				$video_data = array();
		}

		// Remember lookups so saving a post again does not call the provider again; retry failures sooner.
		set_transient( $cache_key, $video_data, empty( $video_data['thumbnail_url'] ) ? HOUR_IN_SECONDS : 12 * HOUR_IN_SECONDS );

		return $video_data;
	}

	/**
	 * Set featured image from extracted video data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $video_urls Array of video urls.
	 */
	public static function set_featured_image_from_videos( int $post_id, array $video_urls ) {
		// If no video data, return early.
		if ( empty( $video_urls ) ) {
			return;
		}

		$options = Options::get_instance();

		$video_content_position = $options->get( 'video_content_position', 'first' );

		foreach ( array_slice( get_items_by_position( $video_urls, (string) $video_content_position ), 0, get_max_attempts() ) as $video_url ) {
			$video_data    = self::get_video_data_by_host_and_id( $video_url['host'], $video_url['id'] );
			$thumbnail_url = $video_data['thumbnail_url'] ?? '';

			if ( empty( $thumbnail_url ) ) {
				continue;
			}

			$attachment_id = set_featured_image_from_url( $post_id, $thumbnail_url, $video_data['title'] ?? '' );

			if ( $attachment_id ) {
				break;
			}
		}
	}
}

// Initialize the source.
Source_Video::get_instance();
