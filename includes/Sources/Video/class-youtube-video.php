<?php
/**
 * Youtube source.
 *
 * @package ReallySimpleFeaturedImage
 */

namespace RS_Featured_Image\Sources\Video;

defined( 'ABSPATH' ) || exit;

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

		if ( ! preg_match( '/^[a-zA-Z0-9_-]{11}$/', $video_id ) ) {
			return $video_data;
		}

		$thumb_url = 'https://img.youtube.com/vi/%s/%s.jpg';

		// Highest resolution first; YouTube answers 404 when a video has no maxres thumbnail.
		$maxres = wp_safe_remote_head( sprintf( $thumb_url, $video_id, 'maxresdefault' ), array( 'timeout' => 10 ) );

		$video_data['thumbnail_url'] = 200 === wp_remote_retrieve_response_code( $maxres )
			? sprintf( $thumb_url, $video_id, 'maxresdefault' )
			: sprintf( $thumb_url, $video_id, 'hqdefault' );

		// The title is optional; oEmbed fails for videos that do not allow embedding.
		$request = wp_safe_remote_get(
			add_query_arg(
				array(
					'format' => 'json',
					'url'    => rawurlencode( 'https://www.youtube.com/watch?v=' . $video_id ),
				),
				'https://www.youtube.com/oembed'
			),
			array( 'timeout' => 10 )
		);

		if ( 200 === wp_remote_retrieve_response_code( $request ) ) {
			$response = json_decode( wp_remote_retrieve_body( $request ) );

			$video_data['title'] = isset( $response->title ) ? (string) $response->title : '';
		}

		return $video_data;
	}
}
