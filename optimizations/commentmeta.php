<?php
/**
 * Orphaned Comment Meta
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Count the number of orphaned comment meta rows
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_orphaned_commentmeta() {
	global $wpdb;

	$query  = "SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments);";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Delete orphaned comment meta rows
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_orphaned_commentmeta() {
	global $wpdb;

	$query  = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments);";
	$result = $wpdb->query( $query );

	return $result;
}

/**
 * Ajax CB
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_delete_commentmeta() {
	check_ajax_referer( 'ng_optimize_delete_commentmeta', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No permission', 'ng-optimize' ) );
		exit;
	}

	$result = ng_optimize_delete_orphaned_commentmeta();

	if ( false === $result ) {
		wp_send_json_error( __( 'Error', 'ng-optimize' ) );
	}

	wp_send_json_success( $result );
	exit;
}

add_action( 'wp_ajax_ng_optimize_delete_commentmeta', 'ng_optimize_delete_commentmeta' );