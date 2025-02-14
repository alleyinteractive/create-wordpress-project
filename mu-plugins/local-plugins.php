<?php
/**
 * Local Development Plugins
 * Activates plugins only in the local environment.
 *
 * @package create-wordpress-project
 */

if ( wp_get_environment_type() === 'local' ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	// Maybe activate Create Block Theme plugin.
	// @todo: check that it was installed.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$create_block_theme_plugin = 'create-block-theme/create-block-theme.php';
	if ( ! is_plugin_active( $create_block_theme_plugin ) ) {
		activate_plugin( $create_block_theme_plugin );
	}
}
