<?php
/**
 * Primary_Term_Rest class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;
use function Create_WordPress_Plugin\register_meta_helper;

use Alley\WP\Types\Feature;

final class Primary_Term_Rest implements Feature {
	/**
	 * Set up.
	 *
	 * @param array $taxonomies The taxonomies to support primary term for.
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
	 * Adds support for subheadline to the `post` post type.
	 * Registers the meta field only for post types that support subheadline.
	 */
	public function register_rest_field() {
		register_rest_field(
			'post',
			'create_wordpress_plugin_primary_term',
			[
				'get_callback'    => [ $this, 'rest_callback' ],
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'The primary term for the post.' ),
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
	 * Callback functionf for the rest field.
	 *
	 * @param \WP_REST_Reqest $request The rest request.
	 * @return array
	 */
	public function rest_callback( $request ) {
		$output = [];
		$post_id = $request['id'];
		foreach ( $this->taxonomies as $taxonomy ) {
			$output[ $taxonomy ] = $this->get_primary_term( $post_id, $taxonomy );
		}
		return $output;
	}

	/**
	 * Gets the primary term for the post.
	 *
	 * @param int $post_id The post id.
	 * @param string $taxonomy The taxonomy to get the primary term for.
	 * @return array
	 */
	public function get_primary_term( $post_id, $taxonomy ) {
		if ( function_exists( 'yoast_get_primary_term_id' ) ) {
			$primary_term_id = yoast_get_primary_term_id( $taxonomy, $post_id );
		}
		if ( empty( $primary_term_id ) ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( ! empty( $terms ) ) {
				$primary_term_id = $terms[0]->term_id;
			}
		}
		if ( empty( $primary_term_id ) ) {
			return [];
		}
		$primary_term = get_term( $primary_term_id, $taxonomy );
		$term         = [
			'term_name' => $primary_term->name,
			'term_id'   => $primary_term->term_id,
			'term_link' => get_term_link( $primary_term ),
		];
		/**
		 * Filters the primary term for the post.
		 *
		 * @param array $term The primary term for the post.
		 * @param int   $id   The post ID.
		 * @param string $taxonomy The taxonomy.
		 */
		return \apply_filters( 'create_wordpress_plugin_primary_term', $term, $post_id, $taxonomy );
	}
}
