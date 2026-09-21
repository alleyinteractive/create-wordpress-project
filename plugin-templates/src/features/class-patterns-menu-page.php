<?php
/**
 * Create WordPress Plugin Features: Patterns_Admin_Menu class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Post_Type;

/**
 * Feature: Adds a "Patterns" menu item to the admin menu for quick access to wp_block posts.
 */
final readonly class Patterns_Menu_Page implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'admin_menu', $this->on_admin_menu( ... ) );
	}

	/**
	 * Adds the Patterns menu item to the admin menu.
	 */
	public function on_admin_menu(): void {
		$post_type = get_post_type_object( 'wp_block' );

		if ( ! $post_type instanceof WP_Post_Type ) {
			return;
		}

		// The empty callback is deliberate: With no page hook generated, the menu links to edit.php directly.
		$menu_name  = is_string( $post_type->labels->menu_name ) ? $post_type->labels->menu_name : $post_type->label;
		$capability = is_string( $post_type->cap->edit_posts ) ? $post_type->cap->edit_posts : 'edit_posts';
		$icon       = is_string( $post_type->menu_icon ) ? $post_type->menu_icon : 'dashicons-layout';

		add_menu_page(
			$menu_name,
			$menu_name,
			$capability,
			'edit.php?post_type=' . $post_type->name,
			'',
			$icon,
			25,
		);
	}
}
