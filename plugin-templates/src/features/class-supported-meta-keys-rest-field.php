<?php
/**
 * Create WordPress Plugin Features: Supported_Meta_Keys_REST_Field class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_REST_Request;

use function Alley\traverse;

/**
 * Feature: Adds a field to the REST API post type endpoint with the type's
 * supported meta keys.
 */
final readonly class Supported_Meta_Keys_REST_Field implements Feature {
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
			'type',
			'supported_meta_keys',
			[
				'schema'       => [
					'context'     => [ 'edit' ],
					'description' => __( 'Supported meta keys for the type.', 'create-wordpress-plugin' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'string',
					],
				],
				'get_callback' => function ( $response_data ): array {
					$out = [];

					if ( is_array( $response_data ) && isset( $response_data['slug'] ) && is_string( $response_data['slug'] ) ) {
						$route           = rest_get_route_for_post_type_items( $response_data['slug'] );
						$schema          = rest_do_request( new WP_REST_Request( 'OPTIONS', $route ) );
						$meta_properties = is_array( $schema->get_data() ) ? traverse( $schema->get_data(), 'schema.properties.meta.properties' ) : null;

						if ( is_array( $meta_properties ) ) {
							$out = array_keys( $meta_properties );
						}
					}

					return $out;
				},
			],
		);
	}
}
