<?php
/**
 * Create WordPress Plugin Features: Taxonomy_Default_Term_REST_Field class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Create_WordPress_Plugin\Default_Term;

/**
 * Feature: Adds a REST API field for a taxonomy's default term.
 */
final readonly class Taxonomy_Default_Term_REST_Field implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'rest_api_init', $this->on_rest_api_init( ... ) );
	}

	/**
	 * Fires when preparing to serve a REST API request.
	 */
	public function on_rest_api_init(): void {
		register_rest_field(
			'taxonomy',
			'default_term',
			[
				'get_callback' => function ( $object ): int {
					$out = 0;

					if ( is_array( $object ) && isset( $object['slug'] ) && is_string( $object['slug'] ) ) {
						$out = new Default_Term( $object['slug'] )->term_id();
					}

					return $out;
				},
				'schema'       => [
					'description' => __( 'The default term ID for the taxonomy.', 'create-wordpress-plugin' ),
					'context'     => [ 'view', 'edit' ],
					'type'        => 'integer',
					'readonly'    => true,
				],
			],
		);
	}
}
