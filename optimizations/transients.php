<?php
/**
 * Expired Transients
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Count the number of expired transients
 *
 * @since 1.0
 * @return int
 */
function ng_optimize_count_expired_transients() {
	global $wpdb;

	$query  = "
	SELECT
		COUNT(*)
	FROM
		$wpdb->options a,$wpdb->options b
	WHERE
		a.option_name LIKE '_transient_%' AND
		a.option_name NOT LIKE '_transient_timeout_%' AND
		b.option_name = CONCAT(
			'_transient_timeout_',
			SUBSTRING(
				a.option_name,
				CHAR_LENGTH('_transient_') + 1
			)
		)
	AND b.option_value < UNIX_TIMESTAMP()
	";
	$number = $wpdb->get_var( $query );

	return (int) $number;
}

/**
 * Delete expired transients
 *
 * @since 1.0
 * @return false|int Number of rows affected or false on failure.
 */
function ng_optimize_delete_expired_transients() {
	global $wpdb;

	// Clean transient rows.
	$query  = "
	DELETE
		a
	FROM
		$wpdb->options a, $wpdb->options b
	WHERE
		a.option_name LIKE '_transient_%' AND
		b.option_name = CONCAT(
			'_transient_timeout_',
			SUBSTRING(
				a.option_name,
				CHAR_LENGTH('_transient_') + 1
			)
		)
	AND b.option_value < UNIX_TIMESTAMP()
	";
	$result = $wpdb->query( $query );

	// Clean transient timeout rows.
	$query = "
	DELETE 
		b
	FROM 
	   $wpdb->options b
	 WHERE
	    b.option_name LIKE '_transient_timeout_%' AND
	    b.option_value < UNIX_TIMESTAMP()
	";
	$wpdb->query( $query );

	return $result;
}

/**
 * Ajax CB
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_delete_transients() {
	check_ajax_referer( 'ng_optimize_delete_transients', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No permission', 'ng-optimize' ) );
		exit;
	}

	$result = ng_optimize_delete_expired_transients();

	if ( false === $result ) {
		wp_send_json_error( __( 'Error', 'ng-optimize' ) );
	}

	wp_send_json_success( $result );
	exit;
}

add_action( 'wp_ajax_ng_optimize_delete_transients', 'ng_optimize_delete_transients' );