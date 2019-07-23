<?php
/**
 * Orphaned Post Meta
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Count the number of orphaned post meta rows
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_orphaned_postmeta() {
	global $wpdb;

	$query  = "SELECT COUNT(*) FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p on p.ID = pm.post_id WHERE p.ID IS NULL;";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Delete orphaned post meta rows
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_orphaned_postmeta() {
	global $wpdb;

	$query  = "DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts p on p.ID = pm.post_id WHERE p.ID IS NULL;";
	$result = $wpdb->query( $query );

	return $result;
}

/**
 * Ajax CB
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_delete_postmeta() {
	check_ajax_referer( 'ng_optimize_delete_postmeta', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No permission', 'ng-optimize' ) );
		exit;
	}

	$result = ng_optimize_delete_orphaned_postmeta();

	if ( false === $result ) {
		wp_send_json_error( __( 'Error', 'ng-optimize' ) );
	}

	wp_send_json_success( $result );
	exit;
}

add_action( 'wp_ajax_ng_optimize_delete_postmeta', 'ng_optimize_delete_postmeta' );