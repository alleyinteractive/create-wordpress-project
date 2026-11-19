<?php
/**
 * Create WordPress Plugin Features: Denied_Template_Capabilities class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Deny the capabilities for templates, template parts and global styles to all roles.
 *
 * Global styles are included because the post editor renders the full Styles
 * sidebar in "Show template" view for anyone who can update them, so
 * redirecting away from the site editor does not stop an administrator from
 * changing site-wide styles. Reading them stays allowed, since the editor
 * needs that to render them.
 *
 * The capabilities are denied by mapping them to `do_not_allow` rather than by
 * removing them from roles: `WP_User::has_cap()` grants a multisite super admin
 * everything without consulting `user_has_cap`, and `do_not_allow` is the one
 * answer it still refuses.
 */
final readonly class Denied_Template_Capabilities implements Feature {
	/**
	 * Post types whose capabilities are denied.
	 */
	private const array POST_TYPES = [
		'wp_template',
		'wp_template_part',
		'wp_global_styles',
	];

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		foreach ( self::POST_TYPES as $post_type ) {
			add_filter( "register_{$post_type}_post_type_args", $this->filter_register_post_type_args( ... ), 10, 2 );
		}

		add_filter( 'map_meta_cap', $this->filter_map_meta_cap( ... ), 10, 2 );
	}

	/**
	 * Map any of the denied post types' primitive capabilities to `do_not_allow`.
	 *
	 * @param mixed $caps Primitive capabilities required of the user.
	 * @param mixed $cap  Capability being checked.
	 * @return mixed Primitive capabilities required of the user.
	 */
	public function filter_map_meta_cap( $caps, $cap ) {
		if ( ! is_array( $caps ) || ! is_string( $cap ) ) {
			return $caps;
		}

		$denied = $this->denied_caps();

		foreach ( [ $cap, ...$caps ] as $required ) {
			if ( in_array( $required, $denied, true ) ) {
				return [ 'do_not_allow' ];
			}
		}

		return $caps;
	}

	/**
	 * The primitive capabilities of the denied post types, leaving out `read`.
	 *
	 * Meta capabilities such as `edit_post` are left out because they resolve to
	 * these primitives, and `read` because every post type shares it.
	 *
	 * @return string[]
	 */
	private function denied_caps(): array {
		$denied = [];

		foreach ( self::POST_TYPES as $post_type ) {
			$object = get_post_type_object( $post_type );

			if ( ! $object instanceof \WP_Post_Type ) {
				continue;
			}

			foreach ( get_object_vars( $object->cap ) as $key => $name ) {
				if ( is_string( $name ) && ! in_array( $key, [ 'read', 'edit_post', 'read_post', 'delete_post' ], true ) ) {
					$denied[] = $name;
				}
			}
		}

		return $denied;
	}

	/**
	 * Filters the arguments for registering a specific post type.
	 *
	 * @param mixed[] $args      Array of arguments for registering a post type.
	 * @param string  $post_type Post type key.
	 * @return mixed[] Array of arguments for registering a post type.
	 */
	public function filter_register_post_type_args( $args, $post_type ) {
		if ( is_array( $args ) && is_string( $post_type ) ) {
			$args['capability_type'] = $post_type;

			unset( $args['capabilities'] );
		}

		return $args;
	}
}
