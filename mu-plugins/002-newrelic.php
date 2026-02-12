<?php
/**
 * New Relic configuration.
 *
 * Sets the New Relic app name based on HTTP_HOST to segment data by site,
 * which is particularly useful for WordPress multisite installations.
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
 *
 * @package create-wordpress-project
 */

// Set New Relic app name to HTTP_HOST if the function exists and HTTP_HOST is available.
if ( function_exists( 'newrelic_set_appname' ) && ! empty( $_SERVER['HTTP_HOST'] ) ) {
	newrelic_set_appname( $_SERVER['HTTP_HOST'] );
}
