<?php
/**
 * Create WordPress Plugin Features: Primary_Term_Rest class
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Term;

/**
 * Feature: Adds a REST API field for the primary term.
 *
 * @package create-wordpress-plugin
 */
final class Primary_Term_Rest implements Feature {
	/**
	 * Set up.
	 *
	 * @param string[] $taxonomies The taxonomies to support primary term for.
	 */
	public function __construct(
		private readonly array $taxonomies = [ 'category' ],
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'init', [ $this, 'register_rest_field' ] );
	}

	/**
	 * Adds the create_wordpress_plugin_primary_term field to the REST API to return the primary term for a post.
	 */
	public function register_rest_field(): void {
		register_rest_field(
			'post',
			'create_wordpress_plugin_primary_term',
			[
				'get_callback'    => [ $this, 'rest_callback' ],
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'The primary term for the post.', 'create-wordpress-plugin' ),
					'type'        => [
						'term_name' => 'string',
						'term_id'   => 'integer',
						'term_link' => 'string',
					],
				],
			]
		);
	}

	/**
	 * Callback function for the REST field.
	 *
	 * @param array $request The REST request.
	 *
	 * @phpstan-param array{id: int} $request
	 *
	 * @return array An array containing taxonomy slugs as keys with term objects as values.
	 *
	 * @phpstan-return array<string, array{term_name?: string, term_id?: int, term_link?: string}>
	 */
	public function rest_callback( array $request ): array {
		$output  = [];
		$post_id = $request['id'];
		foreach ( $this->taxonomies as $taxonomy ) {
			$output[ $taxonomy ] = $this->get_primary_term( $post_id, $taxonomy );
		}
		return $output;
	}

	/**
	 * Gets the primary term for the post for a given taxonomy.
	 *
	 * @param int    $post_id  The post id.
	 * @param string $taxonomy The taxonomy to get the primary term for.
	 *
	 * @return array An array containing the term name, ID, and link.
	 *
	 * @phpstan-return array{term_name?: string, term_id?: int, term_link?: string}
	 */
	public function get_primary_term( int $post_id, string $taxonomy ): array {
		$term = [];
		if ( function_exists( 'yoast_get_primary_term_id' ) ) {
			$primary_term_id = yoast_get_primary_term_id( $taxonomy, $post_id );
		}
		if ( empty( $primary_term_id ) ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( ! empty( $terms[0]->term_id ) ) {
				$primary_term_id = $terms[0]->term_id;
			}
		}
		if ( ! empty( $primary_term_id ) ) {
			$primary_term = get_term( $primary_term_id, $taxonomy );
			if ( $primary_term instanceof WP_Term ) {
				$term_link = get_term_link( $primary_term );
				$term      = [
					'term_name' => $primary_term->name,
					'term_id'   => $primary_term->term_id,
					'term_link' => ! is_wp_error( $term_link ) ? $term_link : '',
				];
			}
		}
		/**
		 * Filters the primary term for the post.
		 *
		 * @param array $term The primary term for the post.
		 * @param int   $id   The post ID.
		 * @param string $taxonomy The taxonomy.
		 *
		 * @phpstan-param array{term_name?: string, term_id?: int, term_link?: string} $term
		 */
		return apply_filters( 'create_wordpress_plugin_primary_term', $term, $post_id, $taxonomy );
	}
}
