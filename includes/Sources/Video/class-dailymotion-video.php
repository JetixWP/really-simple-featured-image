<?php
/**
 * Dailymotion source.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources\Video;

defined( 'ABSPATH' ) || exit;

/**
 * Class Dailymotion_Video
 */
class Dailymotion_Video {

	/**
	 * Get video data for thumbnail from Dailymotion video ID.
	 *
	 * @param string $video_id Dailymotion video ID, for example x8abc12.
	 *
	 * @return array Video data.
	 */
	public static function get_data_by_id( string $video_id ) {
		$video_data = array();

		// Dailymotion IDs are alphanumeric.
		if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $video_id ) ) {
			return $video_data;
		}

		$request = wp_safe_remote_get(
			add_query_arg(
				'fields',
				'thumbnail_1080_url,thumbnail_720_url,thumbnail_url,title',
				'https://api.dailymotion.com/video/' . $video_id
			),
			array( 'timeout' => 10 )
		);

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return $video_data;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		foreach ( array( 'thumbnail_1080_url', 'thumbnail_720_url', 'thumbnail_url' ) as $field ) {
			if ( ! empty( $response->$field ) ) {
				$video_data['thumbnail_url'] = (string) $response->$field;
				break;
			}
		}

		if ( empty( $video_data['thumbnail_url'] ) ) {
			return array();
		}

		$video_data['title'] = isset( $response->title ) ? (string) $response->title : '';

		return $video_data;
	}
}
