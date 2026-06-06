<?php
/**
 * ID Row Action script registration and enqueue.
 *
 * This file will be copied to the assets build directory.
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin;

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_id_row_action_assets' );

/**
 * Enqueue the ID row action script on post and term list-table screens.
 *
 * @param string $hook_suffix The current admin page.
 */
function enqueue_id_row_action_assets( string $hook_suffix ): void {
	if ( ! in_array( $hook_suffix, [ 'edit.php', 'edit-tags.php' ], true ) ) {
		return;
	}

	// Automatically load dependencies and version.
	$asset_file = include __DIR__ . '/index.asset.php';

	if ( ! is_array( $asset_file ) ) {
		return;
	}

	// Validate and sanitize dependencies.
	$dependencies = is_array( $asset_file['dependencies'] )
		? array_map( fn( $item ) => is_scalar( $item ) ? (string) $item : '', $asset_file['dependencies'] )
		: [];

	// Validate and sanitize version.
	$version = is_string( $asset_file['version'] ) || is_numeric( $asset_file['version'] )
		? (string) $asset_file['version']
		: null;

	wp_enqueue_script(
		'create-wordpress-plugin_id-row-action',
		plugins_url( 'index.js', __FILE__ ),
		$dependencies,
		$version,
		true
	);
}
