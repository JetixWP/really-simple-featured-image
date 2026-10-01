<?php
/**
 * Remove plugin data when the plugin is deleted.
 *
 * Featured images and their attachments are kept: they belong to the posts.
 *
 * @package ReallySimpleFeaturedImage
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'rs_featured_image_options' );
delete_option( 'rs_featured_image_db_version' );
delete_option( 'rs_featured_image_previous_db_version' );
delete_option( 'rs_featured_image_queue_flush_rewrite_rules' );

// Per-post "removed by a person" flags and remembered source URLs on attachments.
delete_metadata( 'post', 0, '_rs_featured_image_skip', '', true );
delete_metadata( 'post', 0, '_rs_featured_image_source_url', '', true );
