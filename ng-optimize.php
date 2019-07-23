<?php
/**
 * Plugin Name: NG Optimize
 * Plugin URI: https://www.nosegraze.com
 * Description: Clean up your database.
 * Version: 1.0
 * Author: Ashley Gibson
 * Author URI: https://www.nosegraze.com
 * License: GPL2
 *
 * @package   ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Include required files.
 */
require_once plugin_dir_path( __FILE__ ) . 'optimizations/autodraft.php';
require_once plugin_dir_path( __FILE__ ) . 'optimizations/commentmeta.php';
require_once plugin_dir_path( __FILE__ ) . 'optimizations/comments.php';
require_once plugin_dir_path( __FILE__ ) . 'optimizations/postmeta.php';
require_once plugin_dir_path( __FILE__ ) . 'optimizations/revisions.php';
require_once plugin_dir_path( __FILE__ ) . 'optimizations/terms.php';
require_once plugin_dir_path( __FILE__ ) . 'optimizations/transients.php';

/**
 * Register admin menu under "Tools"
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_menu() {
	add_management_page( __( 'Optimize', 'ng-optimize' ), __( 'Optimize', 'ng-optimize' ), 'manage_options', 'ng-optimize', 'ng_optimize_render_admin_page' );
}

add_action( 'admin_menu', 'ng_optimize_menu' );

/**
 * Load admin JavaScript file
 *
 * @param string $hook
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_admin_js( $hook ) {
	if ( 'tools_page_ng-optimize' != $hook ) {
		return;
	}

	wp_enqueue_script( 'ng-optimize', plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js', array( 'jquery' ), '1.0', true );
}

add_action( 'admin_enqueue_scripts', 'ng_optimize_admin_js' );

/**
 * Render admin page
 *
 * @since 1.0
 * @return void
 */
function ng_optimize_render_admin_page() {
	$number_revisions            = ng_optimize_count_revisions();
	$number_autodrafts           = ng_optimize_count_autodrafts();
	$number_spam_comments        = ng_optimize_count_spam_comments();
	$number_trashed_comments     = ng_optimize_count_trash_comments();
	$number_transients           = ng_optimize_count_expired_transients();
	$number_orphaned_postmeta    = ng_optimize_count_orphaned_postmeta();
	$number_orphaned_commentmeta = ng_optimize_count_orphaned_commentmeta();
	$number_orphaned_terms       = ng_optimize_count_orphaned_terms();
	?>
	<div class="wrap">
		<h1><?php _e( 'Optimize Database', 'ng-optimize' ); ?></h1>
		<table class="widefat">
			<thead>
			<tr>
				<th><?php _e( 'Optimization', 'ng-optimize' ); ?></th>
				<th><?php _e( 'Notes', 'ng-optimize' ); ?></th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php _e( 'Delete revisions', 'ng-optimize' ); ?></td>
				<td><?php printf( _n( '%s post revision in your database', '%s post revisions in your database', $number_revisions, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_revisions . '</span>' ); ?></td>
				<td>
					<button id="ng-optimize-run-revisions" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_revisions" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_revisions' ) ); ?>" type="button" <?php echo 0 === $number_revisions ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Delete auto-draft posts', 'ng-optimize' ); ?></td>
				<td><?php printf( _n( '%s auto-draft post in your database', '%s auto-draft posts in your database', $number_autodrafts, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_autodrafts . '</span>' ); ?></td>
				<td>
					<button id="ng-optimize-run-autodrafts" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_autodrafts" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_autodrafts' ) ); ?>" type="button" <?php echo 0 === $number_autodrafts ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Delete spam and trashed comments', 'ng-optimize' ); ?></td>
				<td>
					<?php printf( _n( '%s spam comment found', '%s spam comments found', $number_spam_comments, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_spam_comments . '</span>' ); ?>
					|
					<a href="<?php echo esc_url( admin_url( 'edit-comments.php?comment_status=spam' ) ); ?>"><?php _e( 'Review', 'ng-optimize' ); ?></a>
					<br>
					<?php printf( _n( '%s trashed comment found', '%s trashed comments found', $number_trashed_comments, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_trashed_comments . '</span>' ); ?>
					|
					<a href="<?php echo esc_url( admin_url( 'edit-comments.php?comment_status=trash' ) ); ?>"><?php _e( 'Review', 'ng-optimize' ); ?></a>
				</td>
				<td>
					<button id="ng-optimize-run-comments" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_comments" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_comments' ) ); ?>" type="button" <?php echo ( 0 === $number_spam_comments + $number_trashed_comments ) ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Delete expired transients', 'ng-optimize' ); ?></td>
				<td><?php printf( _n( '%s expired transient found', '%s expired transients found', $number_transients, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_transients . '</span>' ); ?></td>
				<td>
					<button id="ng-optimize-run-transients" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_transients" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_transients' ) ); ?>" type="button" <?php echo 0 === $number_transients ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Delete orphaned post meta data', 'ng-optimize' ); ?></td>
				<td><?php printf( _n( '%s orphaned post meta row in your database', '%s orphaned post meta rows in your database', $number_orphaned_postmeta, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_orphaned_postmeta . '</span>' ); ?></td>
				<td>
					<button id="ng-optimize-run-postmeta" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_postmeta" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_postmeta' ) ); ?>" type="button" <?php echo 0 === $number_orphaned_postmeta ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Delete orphaned comment meta data', 'ng-optimize' ); ?></td>
				<td><?php printf( _n( '%s orphaned comment meta row in your database', '%s orphaned comment meta rows in your database', $number_orphaned_commentmeta, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_orphaned_commentmeta . '</span>' ); ?></td>
				<td>
					<button id="ng-optimize-run-commentmeta" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_commentmeta" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_commentmeta' ) ); ?>" type="button" <?php echo 0 === $number_orphaned_commentmeta ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Delete orphaned term relationships', 'ng-optimize' ); ?></td>
				<td><?php printf( _n( '%s orphaned term relationship in your database', '%s orphaned term relationships in your database', $number_orphaned_terms, 'ng-optimize' ), '<span class="ng-optimize-number">' . $number_orphaned_terms . '</span>' ); ?></td>
				<td>
					<button id="ng-optimize-run-terms" class="button button-secondary ng-optimize-button" data-action="ng_optimize_delete_terms" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ng_optimize_delete_terms' ) ); ?>" type="button" <?php echo 0 === $number_orphaned_terms ? 'disabled="disabled"' : ''; ?>><?php _e( 'Run Optimization', 'ng-optimize' ); ?></button>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
	<?php
}