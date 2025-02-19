<?php
/**
 * Configuration for wp_get_environment_type().
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
 *
 * @package create-wordpress-project
 */

// Set the environment type to one of the wp_get_environment_type allowed values based on environment config.
if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
	// Negotiate environment source based on Pantheon/WordPress VIP hosting environment.
	$environment_source = '';

	if ( defined( 'VIP_GO_APP_ENVIRONMENT' ) ) {
		$environment_source = VIP_GO_APP_ENVIRONMENT;
	} elseif ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
		$environment_source = $_ENV['PANTHEON_ENVIRONMENT'];
	}

	define(
		'WP_ENVIRONMENT_TYPE',
		match ( $environment_source ) {
			'dev', 'preprod', 'test' => 'staging',
			'develop' => 'development',
			'live', 'production' => 'production',
			default => 'local',
		},
	);

	// Don't pollute global scope.
	unset( $environment_source );
}
