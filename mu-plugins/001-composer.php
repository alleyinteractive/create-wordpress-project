<?php
/**
 * Composer Autoloader
 *
 * Load's Composer's autoloader if it exists and prompts the user to install it
 * if it doesn't.
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package create-wordpress-project
 */

$composer_autoloader_path = dirname( __DIR__ ) . '/vendor/autoload.php';

// Display a friendly error message if Composer is not installed locally.
if ( 'local' === wp_get_environment_type() ) {
	if ( ! file_exists( $composer_autoloader_path ) ) {
		wp_die(
			'Composer is not installed. Please run <code>composer install</code> locally in the <code>wp-content</code> folder for this project.'
		);
	}

	/**
	 * Composer Configuration
	 *
	 * @var array{
	 *   config?: array{
	 *     platform?: array{
	 *       php?: string,
	 *     }
	 *   }
	 * } $composer Composer configuration from composer.json, which may specify the platform PHP version.
	 */
	$composer         = json_decode( file_get_contents( dirname( __DIR__ ) . '/composer.json' ) ?: '', true );
	$required_version = $composer['config']['platform']['php'] ?? '8.0.0';

	// Display a friendly error message if the wrong PHP version is used locally.
	if ( version_compare( PHP_VERSION, $required_version, '<' ) ) {
		wp_die(
			sprintf(
				'This site requires a PHP version >= %s. You are running %s. Please switch to <code>%s</code> on your machine.',
				esc_html( $required_version ),
				PHP_VERSION,
				esc_html( $required_version ),
			),
		);
	}

	// Unset all variables to prevent any external usage.
	unset( $composer, $required_version );
}

// Load the Composer autoloader or add a notice if it doesn't exist.
if ( file_exists( $composer_autoloader_path ) ) {
	require_once $composer_autoloader_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
} else {
	// Include an error notice.
	add_action(
		'admin_notices',
		function () {
			if ( 'local' === wp_get_environment_type() ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html( 'Composer needs to be installed for the site.' )
				);
			} else {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html( 'Composer is not installed on the site! Please contact the site administrator.' )
				);
			}
		}
	);
}

// Don't pollute global scope.
unset( $composer_autoloader_path );
