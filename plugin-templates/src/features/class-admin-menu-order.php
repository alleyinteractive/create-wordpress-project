<?php
/**
 * Create WordPress Plugin Features: Admin_Menu_Order class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Customize the order of the admin menu by grouping menu slugs.
 */
final readonly class Admin_Menu_Order implements Feature {
	/**
	 * Constructor.
	 *
	 * @phpstan-param array<array<string[]>> $groups
	 *
	 * @param array[] $groups Groups of menu slugs, in the desired order.
	 */
	public function __construct(
		private array $groups,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'admin_menu', $this->on_admin_menu( ... ) );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', $this->filter_menu_order( ... ) );
	}

	/**
	 * Fires before the administration menu loads in the admin.
	 *
	 * Adds the menu separators needed for the desired menu structure.
	 */
	public function on_admin_menu(): void {
		global $menu;

		if ( is_array( $menu ) ) {
			foreach ( array_keys( $this->groups ) as $i ) {
				// The indices don't matter as long as they're all reused in the custom menu order later.
				$index = 9990 + $i;

				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$menu[ $index ] = [ '', 'read', "create-wordpress-plugin-separator{$i}", '', 'wp-menu-separator' ];
			}
		}
	}

	/**
	 * Filters the order of administration menu items.
	 *
	 * @param mixed[] $menu_order An ordered array of menu items.
	 * @return mixed[] Updated array of menu items.
	 */
	public function filter_menu_order( $menu_order ): array {
		$preferred_order = [];

		foreach ( $this->groups as $i => $group ) {
			$preferred_order = [ ...$preferred_order, ...$group, "create-wordpress-plugin-separator{$i}" ];
		}

		// Remove any conditionally added menu items not active for this request.
		$preferred_order = array_filter( $preferred_order );

		// Remove everything from the current order that exists in the preferred order.
		$menu_order = array_values(
			array_diff(
				array_filter( $menu_order, is_scalar( ... ) ),
				array_filter( $preferred_order, is_scalar( ... ) ),
			),
		);

		/*
		 * Assume the menu items in the preferred order should live between the first
		 * two core separators. Find those separators and replace whatever is between
		 * them with the preferred items.
		 */
		$separator1 = array_search( 'separator1', $menu_order, true );
		$separator2 = array_search( 'separator2', $menu_order, true );

		if ( ! is_int( $separator1 ) || ! is_int( $separator2 ) ) {
			return $menu_order;
		}

		$start_index = $separator1 + 1;

		array_splice( $menu_order, $start_index, $separator2 - $start_index, $preferred_order );

		return $menu_order;
	}
}
