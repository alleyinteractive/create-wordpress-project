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
 * Returns a list of plugin main file paths (under the plugins directory) to load via code.
 *
 * @return string[]
 */
function create_wordpress_project_core_plugins(): array {
	return [
		'create-wordpress-plugin/create-wordpress-plugin.php',
	];
}

new WP_Plugin_Loader( create_wordpress_project_core_plugins() );
