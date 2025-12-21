<?php
/**
 * Dailymotion source.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources\Video;

/**
 * Class Dailymotion_Video
 */
class Dailymotion_Video {

	/**
	 * Get video data for thumbnail from Dailymotion video ID.
	 *
	 * @param string $video_id Dailymotion video ID.
	 *
	 * @return array Video data.
	 */
	public static function get_data_by_id( string $video_id ) {
		$video_data = array();

		$request = wp_remote_get( 'https://api.dailymotion.com/video/' . intval( $video_id ) . '?fields=thumbnail_url,title' );

		if ( 200 === wp_remote_retrieve_response_code( $request ) ) {
			$response = json_decode( $request['body'] );

			$thumbnail = $response->thumbnail_url ?? '';

			if ( empty( $thumbnail ) ) {
				return $video_data;
			}

			$video_data['thumbnail_url'] = isset( $thumbnail ) ? $thumbnail . '.jpg' : false;
			$video_data['title']         = $response->title ?? '';
		}

		return $video_data;
	}
}
