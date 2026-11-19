<?php
/**
 * Create WordPress Plugin Features: Hidden_Site_Editor_Links class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Admin_Bar;

/**
 * Feature: Hides the admin links to the site editor and font library.
 *
 * These links are gated on `edit_theme_options` alone, so denying the
 * template and global styles capabilities does not hide them. The font
 * library still works, but fonts can only be applied through global styles.
 */
final readonly class Hidden_Site_Editor_Links implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'admin_menu', $this->on_admin_menu( ... ) );
		add_action( 'admin_bar_menu', $this->on_admin_bar_menu( ... ), 999 );
	}

	/**
	 * Removes the Editor and Fonts items from the Appearance menu.
	 */
	public function on_admin_menu(): void {
		remove_submenu_page( 'themes.php', 'site-editor.php' );
		remove_submenu_page( 'themes.php', 'font-library.php' );
	}

	/**
	 * Removes the "Edit Site" item from the front-end admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar instance.
	 */
	public function on_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ): void {
		$wp_admin_bar->remove_node( 'site-editor' );
	}
}
