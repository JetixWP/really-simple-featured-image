<?php
/**
 * Vimeo source.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources\Video;

/**
 * Class Vimeo_Video
 */
class Vimeo_Video {

	/**
	 * Get video data for thumbnail from Vimeo video ID.
	 *
	 * @param string $video_id Vimeo video ID.
	 *
	 * @return array Video data.
	 */
	public static function get_data_by_id( string $video_id ) {
		$video_data = array();

		$request = wp_remote_get( 'https://vimeo.com/api/v2/video/' . intval( $video_id ) . '.json' );

		if ( 200 === wp_remote_retrieve_response_code( $request ) ) {
			$response = json_decode( $request['body'] );

			$thumbnail = $response[0]->thumbnail_large ?? '';

			if ( $thumbnail ) {
				$thumbnail_test = explode( '_', $thumbnail );

				$check_request = wp_remote_head(
					$thumbnail_test[0]
				);

				if ( 200 === wp_remote_retrieve_response_code( $check_request ) ) {
					$thumbnail = $thumbnail_test[0];
				}
			}

			$video_data['thumbnail_url'] = isset( $thumbnail ) ? $thumbnail . '.jpg' : false;
			$video_data['title']         = $response[0]->title;
		}

		return $video_data;
	}
}
