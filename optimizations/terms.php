<?php
/**
 * Orphaned Term Relationships
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Count the number of orphaned term relationship rows
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_orphaned_terms() {
	global $wpdb;

	$query  = "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts);";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Delete orphaned term relationship rows
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_orphaned_terms() {
	global $wpdb;

	$query  = "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)";
	$result = $wpdb->query( $query );

	return $result;
}

/**
 * Ajax CB
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_delete_terms() {
	check_ajax_referer( 'ng_optimize_delete_terms', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No permission', 'ng-optimize' ) );
		exit;
	}

	$result = ng_optimize_delete_orphaned_terms();

	if ( false === $result ) {
		wp_send_json_error( __( 'Error', 'ng-optimize' ) );
	}

	wp_send_json_success( $result );
	exit;
}

add_action( 'wp_ajax_ng_optimize_delete_terms', 'ng_optimize_delete_terms' );