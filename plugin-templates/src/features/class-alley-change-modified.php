<?php
/**
 * Create WordPress Plugin Features: Alley_Change_Modified class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use DateTimeInterface;

/**
 * Feature: Allow a post's modified dates to be set at insert time.
 */
final readonly class Alley_Change_Modified implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'wp_insert_post_data', $this->filter_wp_insert_post_data( ... ), 100, 2 );
	}

	/**
	 * Filters slashed post data just before it is inserted into the database.
	 *
	 * Allows a post's modified dates to be set in `wp_insert_post()` by passing a
	 * custom 'alley_change_modified' parameter.
	 *
	 * 'alley_change_modified' may be one of:
	 *
	 * - `true` to update the modified date as usual.
	 * - `false` to preserve the existing modified dates.
	 * - A "MySQL date," like '2020-01-02 03:04:05' to be used as 'post_modified',
	 *   with 'post_modified_gmt' set accordingly for the site's timezone.
	 * - A `\DateTimeInterface` or '@timestamp' to be used as 'post_modified_gmt',
	 *   with 'post_modified' set accordingly for the site's timezone.
	 *
	 * @param mixed[] $data    An array of slashed post data.
	 * @param mixed[] $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return mixed[] Updated post data.
	 */
	public function filter_wp_insert_post_data( $data, $postarr ) {
		if ( ! isset( $postarr['alley_change_modified'] ) ) {
			return $data;
		}

		$change_post_modified = $postarr['alley_change_modified'];

		if ( true === $change_post_modified ) {
			return $data;
		}

		if ( false === $change_post_modified ) {
			$data['post_modified']     = $postarr['post_modified'] ?? $data['post_modified'];
			$data['post_modified_gmt'] = $postarr['post_modified_gmt'] ?? $data['post_modified_gmt'];

			return $data;
		}

		if ( is_string( $change_post_modified ) && $change_post_modified !== '' && '@' === $change_post_modified[0] ) {
			$change_post_modified = date_create( $change_post_modified );
		}

		if ( $change_post_modified instanceof DateTimeInterface ) {
			$timestamp = $change_post_modified->getTimestamp();

			$data['post_modified']     = wp_date( 'Y-m-d H:i:s', $timestamp );
			$data['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', $timestamp );

			return $data;
		}

		if ( is_string( $change_post_modified ) && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $change_post_modified ) ) {
			$dt = date_create( $change_post_modified, wp_timezone() );

			if ( $dt instanceof DateTimeInterface ) {
				$data['post_modified']     = $change_post_modified;
				$data['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', $dt->getTimestamp() );
			}

			return $data;
		}

		return $data;
	}
}
