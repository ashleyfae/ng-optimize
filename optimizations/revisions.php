<?php
/**
 * Revisions
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Count the number of revisions
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_revisions() {
	global $wpdb;

	$query  = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision';";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Delete revisions
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_revisions() {
	global $wpdb;

	$query  = "DELETE FROM $wpdb->posts WHERE post_type = 'revision';";
	$result = $wpdb->query( $query );

	return $result;
}

/**
 * Ajax CB
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_ajax_revisions() {
	check_ajax_referer( 'ng_optimize_delete_revisions', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No permission', 'ng-optimize' ) );
		exit;
	}

	$result = ng_optimize_delete_revisions();

	if ( false === $result ) {
		wp_send_json_error( __( 'Error', 'ng-optimize' ) );
	}

	wp_send_json_success( $result );
	exit;
}

add_action( 'wp_ajax_ng_optimize_delete_revisions', 'ng_optimize_ajax_revisions' );