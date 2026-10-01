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

/**
 * Remove this plugin's data from the current site.
 *
 * @return void
 */
function rs_featured_image_uninstall_site() {
	global $wpdb;

	delete_option( 'rs_featured_image_options' );
	delete_option( 'rs_featured_image_db_version' );
	delete_option( 'rs_featured_image_previous_db_version' );
	delete_option( 'rs_featured_image_queue_flush_rewrite_rules' );

	// Per-post "removed by a person" flags and remembered source URLs on attachments.
	delete_metadata( 'post', 0, '_rs_featured_image_skip', '', true );
	delete_metadata( 'post', 0, '_rs_featured_image_source_url', '', true );

	// Cached video lookups and rollback version lists.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Removes this plugin's transients on uninstall.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_rs_featured_image_video_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_rs_featured_image_video_' ) . '%',
			$wpdb->esc_like( '_transient_rs_featured_image_rollback_versions_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_rs_featured_image_rollback_versions_' ) . '%'
		)
	);
}

if ( is_multisite() ) {
	$rs_featured_image_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $rs_featured_image_site_ids as $rs_featured_image_site_id ) {
		switch_to_blog( $rs_featured_image_site_id );
		rs_featured_image_uninstall_site();
		restore_current_blog();
	}
} else {
	rs_featured_image_uninstall_site();
}
