<?php
/**
 * Create WordPress Plugin Features: Primary_Term_REST_Field class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Create_WordPress_Plugin\Primary_Term;

/**
 * Feature: Registers a REST field exposing the primary term in a taxonomy.
 */
final readonly class Primary_Term_REST_Field implements Feature {
	/**
	 * Set up.
	 *
	 * @param string $taxonomy The taxonomy to expose the primary term for.
	 */
	public function __construct(
		private string $taxonomy,
	) {}

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
		foreach ( get_post_types( [ 'show_in_rest' => true ] ) as $post_type ) {
			if ( is_object_in_taxonomy( $post_type, $this->taxonomy ) ) {
				register_rest_field(
					$post_type,
					"primary_{$this->taxonomy}",
					[
						'get_callback' => $this->get_callback( ... ),
						'schema'       => [
							'description' => __( 'The primary term ID for the post in the taxonomy.', 'create-wordpress-plugin' ),
							'context'     => [ 'view', 'edit' ],
							'type'        => 'integer',
							'readonly'    => true,
						],
					],
				);
			}
		}
	}

	/**
	 * Callback for the REST field.
	 *
	 * @param mixed[] $response_data The response data for the post.
	 * @return int The primary term ID.
	 */
	public function get_callback( $response_data ): int {
		$out = 0;

		if ( is_array( $response_data ) && isset( $response_data['id'] ) && is_numeric( $response_data['id'] ) ) {
			$out = new Primary_Term( (int) $response_data['id'], $this->taxonomy )->term_id();
		}

		return $out;
	}
}
