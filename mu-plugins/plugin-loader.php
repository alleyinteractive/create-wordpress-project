<?php
/**
 * Must-use plugin loader for create-wordpress-project.
 *
 * @package create-wordpress-project
 */

use Alley\WP\WP_Plugin_Loader;

// Load Pantheon's mu-plugin.
require_once __DIR__ . '/pantheon-mu-plugin/pantheon.php';

new WP_Plugin_Loader(
	[
		'create-wordpress-plugin/create-wordpress-plugin.php',
	],
);
