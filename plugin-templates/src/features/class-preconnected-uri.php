<?php
/**
 * Create WordPress Plugin Features: Preconnected_URI class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Psr\Http\Message\UriInterface;

/**
 * Feature: Adds a `preconnect` resource hint for a single URI.
 */
final readonly class Preconnected_URI implements Feature {
	/**
	 * Constructor.
	 *
	 * @param UriInterface $uri The URI to preconnect to.
	 */
	public function __construct(
		private UriInterface $uri,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'wp_resource_hints', $this->filter_wp_resource_hints( ... ), 10, 2 );
	}

	/**
	 * Filters domains and URLs for resource hints of the given relation type.
	 *
	 * @phpstan-param string[]|array{href: string}[] $urls
	 * @phpstan-return string[]|array{href: string}[]
	 *
	 * @param array  $urls          Array of resources and their attributes, or URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 * @return array
	 */
	public function filter_wp_resource_hints( $urls, $relation_type ) {
		if ( 'preconnect' === $relation_type ) {
			if ( ! is_array( $urls ) ) {
				$urls = [];
			}

			$urls = array_merge(
				$urls,
				[
					[
						'href' => (string) $this->uri,
					],
				],
			);
		}

		return $urls;
	}
}
