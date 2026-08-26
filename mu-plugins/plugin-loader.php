<?php
/**
 * Must-use plugin loader for create-wordpress-project.
 *
 * @package create-wordpress-project
 */

use Alley\WP\WP_Plugin_Loader;

// Load Pantheon's mu-plugin.
require_once __DIR__ . '/pantheon-mu-plugin/pantheon.php';

/**
 * Returns a list of plugin main file paths (under the plugins directory) to
 * load via code in local environment only.
 *
 * @return array<int, string>
 */
function create_wordpress_project_local_plugins(): array {
	if ( wp_get_environment_type() !== 'local' ) {
		return [];
	}

	return [
		'create-block-theme/create-block-theme.php'
	];
}

/**
 * Returns a list of plugin main file paths (under the plugins directory) to load via code.
 *
 * @return array<int, string>
 */
function create_wordpress_project_core_plugins(): array {
	return array_merge(
		[
			'create-wordpress-plugin/create-wordpress-plugin.php',
		],
		create_wordpress_project_local_plugins()
	);
}

new WP_Plugin_Loader( create_wordpress_project_core_plugins() );
