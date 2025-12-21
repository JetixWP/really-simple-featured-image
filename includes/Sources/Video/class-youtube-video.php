<?php
/**
 * Youtube source.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources\Video;

/**
 * Class Youtube_Video
 */
class Youtube_Video {

	/**
	 * Get video data for thumbnail from YouTube video ID.
	 *
	 * @param string $video_id YouTube video ID.
	 *
	 * @return array Video data.
	 */
	public static function get_data_by_id( string $video_id ) {
		$video_data = array();
		$request    = wp_remote_get( 'https://www.youtube.com/oembed?format=json&url=https://www.youtube.com/watch?v=' . $video_id );

		if ( is_array( $request ) && ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
			$request_data = json_decode( wp_remote_retrieve_body( $request ) );

			$youtube_thumb_url_string = 'https://img.youtube.com/vi/%s/%s.jpg';
			$remote_headers           = wp_remote_head(
				sprintf(
					$youtube_thumb_url_string,
					$video_id,
					'maxresdefault'
				)
			);

			$video_data['thumbnail_url'] = ( 404 === wp_remote_retrieve_response_code( $remote_headers ) ) ?
			sprintf(
				$youtube_thumb_url_string,
				$video_id,
				'hqdefault'
			) :
			sprintf(
				$youtube_thumb_url_string,
				$video_id,
				'maxresdefault'
			);

			$video_data['title'] = $request_data->title;
		}

		return $video_data;
	}
}
