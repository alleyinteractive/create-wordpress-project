<?php
/**
 * Create WordPress Plugin Features: Mantle_Model class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use InvalidArgumentException;
use Mantle\Database\Model\Model;
use WP_Post_Type;

/**
 * Feature: Register a Mantle model.
 */
final readonly class Mantle_Model implements Feature {
	/**
	 * Constructor.
	 *
	 * @param string $model The name of the Mantle model class to register on boot.
	 */
	public function __construct(
		private string $model,
	) {}

	/**
	 * Boot the feature.
	 *
	 * @throws InvalidArgumentException If the provided model class is not a valid Mantle model.
	 */
	public function boot(): void {
		if ( ! is_subclass_of( $this->model, Model::class ) ) {
			throw new InvalidArgumentException( 'The provided model class must be a valid Mantle model.' );
		}

		$this->model::boot_if_not_booted();

		add_action( "registered_post_type_{$this->model::get_object_name()}", $this->on_registered_post_type( ... ), 10, 2 );
	}

	/**
	 * Fires after a post type is registered.
	 *
	 * @param string       $post_type        Post type.
	 * @param WP_Post_Type $post_type_object Arguments used to register the post type.
	 */
	public function on_registered_post_type( $post_type, $post_type_object ): void {
		/*
		 * Work around the chance that the taxonomies for the post type might not be
		 * registered before the post type is registered.
		 */
		foreach ( $post_type_object->taxonomies as $taxonomy ) {
			add_action(
				"registered_taxonomy_{$taxonomy}",
				function () use ( $taxonomy, $post_type ): void {
					register_taxonomy_for_object_type( $taxonomy, $post_type );
				},
			);
		}
	}
}
