<?php
/**
 * Comments
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Count the number of spam comments
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_spam_comments() {
	global $wpdb;

	$query  = "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam';";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Count the number of trashed comments
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_trash_comments() {
	global $wpdb;

	$query  = "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'trash';";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Delete spam comments
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_spam_comments() {
	global $wpdb;

	$query  = "DELETE c, cm FROM $wpdb->comments c LEFT JOIN $wpdb->commentmeta cm ON c.comment_ID = cm.comment_id WHERE c.comment_approved = 'spam';";
	$result = $wpdb->query( $query );

	return $result;
}

/**
 * Delete trashed comments
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_trash_comments() {
	global $wpdb;

	$query  = "DELETE c, cm FROM $wpdb->comments c LEFT JOIN $wpdb->commentmeta cm ON c.comment_ID = cm.comment_id WHERE c.comment_approved = 'trash';";
	$result = $wpdb->query( $query );

	return $result;
}

/**
 * Ajax CB
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_delete_comments() {
	check_ajax_referer( 'ng_optimize_delete_comments', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No permission', 'ng-optimize' ) );
		exit;
	}

	$result_1 = ng_optimize_delete_spam_comments();
	$result_2 = ng_optimize_delete_trash_comments();

	if ( false === $result_1 || false === $result_2 ) {
		wp_send_json_error( __( 'Error', 'ng-optimize' ) );
	}

	wp_send_json_success( $result_1 + $result_2 );
	exit;
}

add_action( 'wp_ajax_ng_optimize_delete_comments', 'ng_optimize_delete_comments' );