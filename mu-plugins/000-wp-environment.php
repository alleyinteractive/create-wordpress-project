<?php
/**
 * Configuration for wp_get_environment_type().
 *
 * phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 *
 * @package create-wordpress-project
 */

// Negotiate environment source based on Pantheon/WordPress VIP hosting environment.
$environment_source = '';
if ( defined( 'VIP_GO_APP_ENVIRONMENT' ) ) {
	$environment_source = VIP_GO_APP_ENVIRONMENT;
} elseif ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) ) {
	$environment_source = $_ENV['PANTHEON_ENVIRONMENT'];
}

// Set the environment type to one of the wp_get_environment_type allowed values based on environment config.
$environment_type = match ( $environment_source ) {
	'dev', 'preprod', 'test' => 'staging',
	'develop' => 'development',
	'live', 'production' => 'production',
	default => 'local',
};
define( 'WP_ENVIRONMENT_TYPE', $environment_type );

// Don't pollute global scope.
unset( $environment_source, $environment_type );
