<?php
/**
 * New Relic configuration.
 *
 * Sets the New Relic app name based on HTTP_HOST to segment data by site,
 * which is particularly useful for WordPress multisite installations.
 *
 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
 *
 * @package create-wordpress-project
 */

// Set New Relic app name to HTTP_HOST if the function exists and HTTP_HOST is available.
if ( function_exists( 'newrelic_set_appname' ) && ! empty( $_SERVER['HTTP_HOST'] ) ) {
	// Remove port number if present (e.g., example.com:8080 -> example.com).
	$host = $_SERVER['HTTP_HOST'];
	$host = preg_replace( '/:\d+$/', '', $host );

	// Sanitize the hostname to prevent potential security issues.
	// Only allow alphanumeric characters, dots, and hyphens.
	$host = preg_replace( '/[^a-zA-Z0-9.-]/', '', $host );

	// Validate basic hostname structure: must contain at least one character and valid domain format.
	if ( ! empty( $host ) && preg_match( '/^[a-zA-Z0-9]([a-zA-Z0-9.-]*[a-zA-Z0-9])?$/', $host ) ) {
		newrelic_set_appname( $host );
	}
}
