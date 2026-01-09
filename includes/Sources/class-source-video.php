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
		add_action( 'save_post', array( $this, 'check_content_for_videos' ), 10, 2 );

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
		// Check if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if the option to use content videos is enabled.
		$options = Options::get_instance();

		$source_set = $options->get( 'default_source' );

		// If source is set and it's not 'content-video', return early.
		if ( 'content-video' !== $source_set ) {
			return;
		}

		// If this is a revision, switch to parent.
		if ( wp_is_post_revision( $post_id ) && is_object( $post ) ) {
			$post_id = $post->post_parent;
		}

		// Prevent trying to assign when trashing or untrashing posts in the list screen.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'trash', 'untrash', 'add-menu-item' ), true ) ) {
			return;
		}

		// Ensure post is an object or if post already has a featured image.
		if ( has_post_thumbnail( $post_id ) || ! $post ) {
			return;
		}

		$content = $post->post_content ?? '';

		// If content is empty return early.
		if ( empty( $content ) ) {
			return;
		}

		$content_length = apply_filters( 'rs_featured_image_read_content_length_limit', 6000, $source_set, $post_id );

		// Limit content length to 6000 characters to improve performance.
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
			libxml_use_internal_errors( true );

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
			'youtube'     => '#(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{6,})#i',
			'vimeo'       => '#(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com\/|player\.vimeo\.com\/video\/)(\d+)#i',
			'dailymotion' => '#(?:https?:\/\/)?(?:www\.)?(?:dailymotion\.com\/video\/|dai\.ly\/)([a-zA-Z0-9]+)#i',
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
		switch ( $host ) {
			case 'youtube':
				return Youtube_Video::get_data_by_id( $video_id );
			case 'vimeo':
				return Vimeo_Video::get_data_by_id( $video_id );
			case 'dailymotion':
				return Dailymotion_Video::get_data_by_id( $video_id );
			default:
				return array();
		}
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

		$thumbnail_url = '';

		if ( 'first' === $video_content_position ) {
			// Use the first found video URL.
			foreach ( $video_urls as $video_url ) {
				$video_id = $video_url['id'];

				$video_data = self::get_video_data_by_host_and_id( $video_url['host'], $video_id );

				// If no video data, continue to next.
				$thumbnail_url = $video_data['thumbnail_url'] ?? '';

				if ( empty( $thumbnail_url ) ) {
					continue;
				}

				$video_title = $video_data['title'] ?? '';

				// Try to set from URL.
				$attachment_id = set_featured_image_from_url( $post_id, $thumbnail_url, $video_title );

				if ( ! empty( $attachment_id ) ) {
					break;
				}
			}
		} elseif ( 'second' === $video_content_position ) {
			$video_url = $video_urls[1] ?? '';

			if ( empty( $video_url ) ) {
				return;
			}

			$video_id = $video_url['id'];

			$video_data    = self::get_video_data_by_host_and_id( $video_url['host'], $video_id );
			$thumbnail_url = $video_data['thumbnail_url'] ?? '';
			$video_title   = $video_data['title'] ?? '';

			// Try to set from URL.
			$attachment_id = set_featured_image_from_url( $post_id, $thumbnail_url, $video_title );
		} elseif ( 'last-second' === $video_content_position ) {
			$video_url = $video_urls[ count( $video_urls ) - 2 ] ?? '';

			if ( empty( $video_url ) ) {
				return;
			}

			$video_id = $video_url['id'];

			$video_data    = self::get_video_data_by_host_and_id( $video_url['host'], $video_id );
			$thumbnail_url = $video_data['thumbnail_url'] ?? '';
			$video_title   = $video_data['title'] ?? '';

			// Try to set from URL.
			$attachment_id = set_featured_image_from_url( $post_id, $thumbnail_url, $video_title );
		} elseif ( 'last' === $video_content_position ) {
			$video_url = end( $video_urls );

			if ( empty( $video_url ) ) {
				return;
			}

			$video_id = $video_url['id'];

			$video_data    = self::get_video_data_by_host_and_id( $video_url['host'], $video_id );
			$thumbnail_url = $video_data['thumbnail_url'] ?? '';
			$video_title   = $video_data['title'] ?? '';

			// Try to set from URL.
			$attachment_id = set_featured_image_from_url( $post_id, $thumbnail_url, $video_title );
		}
	}
}

// Initialize the source.
Source_Video::get_instance();
