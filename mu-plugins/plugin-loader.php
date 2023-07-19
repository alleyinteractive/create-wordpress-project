<?php
/**
 * Must-use plugin loader for create-wordpress-project.
 *
 * @package create-wordpress-project
 */

// Helper function for getting the current environment name.
function create_wordpress_project_environment(): string {
	return $_ENV['PANTHEON_ENVIRONMENT'] ?? 'local';
}

// Load Pantheon's mu-plugin.
require_once __DIR__ . '/pantheon-mu-plugin/pantheon.php';

/**
 * Returns a list of plugin main file paths (under the plugins directory) to load via code.
 *
 * @return string[]
 */
function create_wordpress_project_core_plugins(): array {
	return [
		'create-wordpress-plugin/create-wordpress-plugin.php',
	];
}

// Load as many standard plugins via code as possible.
foreach ( create_wordpress_project_core_plugins() as $plugin ) {
	require_once dirname( __DIR__ ) . '/plugins/' . $plugin;
}

/**
 * Ensure code activated plugins are shown as such on core plugins screens.
 *
 * @link https://github.com/Automattic/vip-go-mu-plugins/blob/2414bba6ec4ed582eb31c27e3990c9e3ed42dbd1/vip-plugins/vip-plugins.php#L153
 *
 * @param array{
 *   activate?: string,
 *   deactivate?: string,
 *   delete?: string,
 *   network_active?: string,
 * } $actions An array of plugin action links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 *
 * @return array{
 *   activate?: string,
 *   deactivate?: string,
 *   delete?: string,
 *   network_active?: string,
 *   create_wordpress_project_code_activated_plugin?: string,
 * } Modified list of plugin action links.
 */
function create_wordpress_project_plugin_action_links( array $actions, string $plugin_file ): array {
	if ( in_array( $plugin_file, create_wordpress_project_core_plugins(), true ) ) {
		unset( $actions['activate'] );
		unset( $actions['deactivate'] );
		unset( $actions['delete'] );
		unset( $actions['network_active'] );
		$actions['create_wordpress_project_code_activated_plugin'] = 'Enabled via code';
	}

	return $actions;
}
add_filter( 'plugin_action_links', 'create_wordpress_project_plugin_action_links', 10, 2 );
add_filter( 'network_admin_plugin_action_links', 'create_wordpress_project_plugin_action_links', 10, 2 );
