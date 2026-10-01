<?php
/**
 * Vimeo source.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources\Video;

defined( 'ABSPATH' ) || exit;

/**
 * Class Vimeo_Video
 */
class Vimeo_Video {

	/**
	 * Get video data for thumbnail from Vimeo video ID.
	 *
	 * Uses Vimeo's oEmbed endpoint; the old api/v2 endpoint is retired.
	 *
	 * @param string $video_id Vimeo video ID.
	 *
	 * @return array Video data.
	 */
	public static function get_data_by_id( string $video_id ) {
		$video_data = array();

		if ( ! preg_match( '/^\d+$/', $video_id ) ) {
			return $video_data;
		}

		$request = wp_safe_remote_get(
			add_query_arg(
				array(
					'url'   => rawurlencode( 'https://vimeo.com/' . $video_id ),
					'width' => 1280,
				),
				'https://vimeo.com/api/oembed.json'
			),
			array( 'timeout' => 10 )
		);

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return $video_data;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		if ( empty( $response->thumbnail_url ) ) {
			return $video_data;
		}

		$video_data['thumbnail_url'] = (string) $response->thumbnail_url;
		$video_data['title']         = isset( $response->title ) ? (string) $response->title : '';

		return $video_data;
	}
}
