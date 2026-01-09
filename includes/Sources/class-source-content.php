<?php
/**
 * Source content.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources;

defined( 'ABSPATH' ) || exit;

use WP_Post;
use DOMDocument;
use RS_Featured_Image\Options;
use RS_Featured_Image\Utils\Has_Instance;

use function RS_Featured_Image\set_featured_image_from_existing_image;
use function RS_Featured_Image\set_featured_image_from_url;
use function RS_Featured_Image\get_supported_image_extensions;

/**
 * Class Source_Content
 */
class Source_Content {
	use Has_Instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'save_post', array( $this, 'check_content_for_images' ), 10, 2 );
		add_action( 'deleted_post_meta', array( $this, 'handle_deleted_thumbnail' ), 20, 3 );
		add_action( 'rs_featured_image_setting_featured_image_from_content', array( __CLASS__, 'set_featured_image_from_content' ), 10, 2 );
	}

	/**
	 * Check post content for images to set as featured image.
	 *
	 * @param int|WP_Post $post_id Post ID/Post object.
	 * @param WP_Post     $post Post object.
	 */
	public function check_content_for_images( int|WP_Post $post_id, WP_Post $post ) {
		// Check if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if the option to use content images is enabled.
		$options = Options::get_instance();

		$has_source_set = $options->has( 'default_source' );
		$source_set     = $options->get( 'default_source' );

		// If source is set and it's not 'content-image', return early.
		if ( $has_source_set && 'content-image' !== $source_set ) {
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

		// Extract image URLs from content.
		$image_urls = $this->get_image_urls_from_content( $content );

		if ( empty( $image_urls ) ) {
			return;
		}

		// Trigger actions before, during, and after setting the featured image.
		do_action( 'rs_featured_image_before_setting_featured_image_from_content', $post_id, $image_urls );

		// Triggers the actual setting of the featured image.
		do_action( 'rs_featured_image_setting_featured_image_from_content', $post_id, $image_urls );

		// Action after setting the featured image.
		do_action( 'rs_featured_image_after_setting_featured_image_from_content', $post_id, $image_urls );
	}

	/**
	 * Extract all image URLs from content.
	 *
	 * @param string $content Post content.
	 *
	 * @return array
	 */
	public function get_image_urls_from_content( $content ) {
		$urls = array();

		if ( empty( $content ) ) {
			return $urls;
		}

		// 1. DOM parsing (img, source, picture, data-src)
		if ( class_exists( 'DOMDocument' ) ) {
			libxml_use_internal_errors( true );

			$dom = new DOMDocument();
			$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content );

			$tags = array( 'img', 'source' );

			foreach ( $tags as $tag ) {
				foreach ( $dom->getElementsByTagName( $tag ) as $node ) {
					foreach ( array( 'src', 'data-src', 'data-lazy-src' ) as $attr ) {
						if ( $node->hasAttribute( $attr ) ) {
							$urls[] = array(
								'url'  => $node->getAttribute( $attr ),
								'type' => 'dom',
							);
						}
					}

					// srcset support.
					if ( $node->hasAttribute( 'srcset' ) ) {
						$srcset = explode( ',', $node->getAttribute( 'srcset' ) );
						foreach ( $srcset as $item ) {
							$urls[] = array(
								'url'  => preg_replace( '/\s+\d+w$/', '', trim( $item ) ),
								'type' => 'regex',
							);
						}
					}
				}
			}

			libxml_clear_errors();
		}

		$supported_image_extensions         = get_supported_image_extensions();
		$supported_image_extensions_pattern = implode( '|', $supported_image_extensions );

		// 2. Inline CSS background images (url())
		preg_match_all(
			'/url\(\s*[\'"]?([^\'")]+?\.(?:' . $supported_image_extensions_pattern . ')[^\'")]*?)[\'"]?\s*\)/i',
			$content,
			$css_matches
		);

		if ( ! empty( $css_matches[1] ) ) {
			foreach ( $css_matches[1] as $raw_url ) {
				$existing_urls = array_column( $urls, 'url' );
				if ( ! in_array( $raw_url, $existing_urls, true ) ) {
					$urls[] = array(
						'url'  => $raw_url,
						'type' => 'regex',
					);
				}
			}
		}

		// 3. Raw image URLs (JSON, text, builders configs)
		preg_match_all(
			'/(?:https?:\/\/|\/)[^\s"\'>]+?\.(?:' . $supported_image_extensions_pattern . ')(?:\?[^\s"\'>]*)?/i',
			$content,
			$raw_matches
		);

		if ( ! empty( $raw_matches[0] ) ) {
			foreach ( $raw_matches[0] as $raw_url ) {
				$existing_urls = array_column( $urls, 'url' );
				if ( ! in_array( $raw_url, $existing_urls, true ) ) {
					$urls[] = array(
						'url'  => $raw_url,
						'type' => 'regex',
					);
				}
			}
		}

		// 4. Normalize URLs (relative → absolute)
		$normalized = array();
		$site_url   = site_url();

		foreach ( $urls as $url ) {
			$raw_url = html_entity_decode( trim( $url['url'] ) );

			// Skip empty URLs.
			if ( empty( $raw_url ) ) {
				continue;
			}

			// Protocol-relative.
			if ( strpos( $raw_url, '//' ) === 0 ) {
				$raw_url = ( is_ssl() ? 'https:' : 'http:' ) . $raw_url;
			}

			// Root-relative.
			if ( strpos( $raw_url, '/' ) === 0 && strpos( $raw_url, '//' ) !== 0 ) {
				$raw_url = untrailingslashit( $site_url ) . $raw_url;
			}

			// Skip non-image garbage.
			if (
			'regex' === $url['type'] &&
			! preg_match( '/\.(?:' . $supported_image_extensions_pattern . ')(\?|$)/i', $raw_url )
			) {
				continue;
			}

			$normalized[] = esc_url_raw( $raw_url );
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Handle deleted thumbnail post meta.
	 *
	 * @param int    $meta_id    Meta ID.
	 * @param int    $post_id    Post ID.
	 * @param string $meta_key   Meta key.
	 */
	public function handle_deleted_thumbnail( $meta_id, $post_id, $meta_key ) {
		if ( '_thumbnail_id' !== $meta_key ) {
			return;
		}

		// Thumbnail was explicitly removed by user.
		$this->check_content_for_images( $post_id, get_post( $post_id ) );
	}

	/**
	 * Set featured image from content image URLs.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $image_urls Array of image URLs.
	 */
	public static function set_featured_image_from_content( $post_id, $image_urls ) {
		// If no image URLs, return early.
		if ( empty( $image_urls ) ) {
			return;
		}

		$options = Options::get_instance();

		$image_content_position = $options->get( 'image_content_position', 'first' );

		$attachment_id = '';

		if ( 'first' === $image_content_position ) {
			// Use the first found image URL.
			foreach ( $image_urls as $image_url ) {
				// Try to set featured image from existing attachment first.
				$attachment_id = set_featured_image_from_existing_image( $post_id, $image_url );

				// If no attachment found, try to set from URL.
				if ( ! $attachment_id ) {
					$attachment_id = set_featured_image_from_url( $post_id, $image_url );
				}

				// Break loop if we have successfully set a featured image.
				if ( ! empty( $attachment_id ) ) {
					break;
				}
			}
		} elseif ( 'second' === $image_content_position ) {
			$image_url = $image_urls[1] ?? '';

			if ( empty( $image_url ) ) {
				return;
			}

			// Use the second image URL.
			$attachment_id = set_featured_image_from_url( $post_id, $image_url );
		} elseif ( 'last-second' === $image_content_position ) {
			$image_url = $image_urls[ count( $image_urls ) - 2 ];

			if ( empty( $image_url ) ) {
				return;
			}

			// Use the last second image URL.
			$attachment_id = set_featured_image_from_url( $post_id, $image_url );
		} elseif ( 'last' === $image_content_position ) {
			// Use the last image URL.
			$attachment_id = set_featured_image_from_url( $post_id, end( $image_urls ) );
		}
	}
}

// Initialize the Source_Content singleton.
Source_Content::get_instance();
