<?php
/**
 * Create WordPress Plugin Features: Site_Settings_Page class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Fieldmanager_Field;
use Fieldmanager_Group;

/**
 * Feature: A catch-all site settings page.
 *
 * Registers a Fieldmanager-powered settings page under the Settings menu and
 * exposes a `create_wordpress_plugin_site_settings_group_children` filter so that
 * other features can attach their own field groups.
 */
final readonly class Site_Settings_Page implements Feature {
	/**
	 * Set up.
	 *
	 * @param string $option_name Option name the settings are stored under.
	 * @param string $capability  Capability required to access the settings page.
	 */
	public function __construct(
		private string $option_name,
		private string $capability,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		if ( function_exists( 'fm_register_submenu_page' ) ) {
			fm_register_submenu_page(
				$this->option_name,
				'options-general.php',
				__( 'Site Settings', 'create-wordpress-plugin' ),
				__( 'Site Settings', 'create-wordpress-plugin' ),
				$this->capability,
			);

			add_action( 'fm_submenu_' . $this->option_name, $this->on_fm_submenu( ... ) );
		}
	}

	/**
	 * Build and render the settings page from the children added by other features.
	 *
	 * @throws \FM_Developer_Exception If fields are invalid.
	 */
	public function on_fm_submenu(): void {
		$settings = [
			'name'     => $this->option_name,
			'tabbed'   => 'vertical',
			'children' => [],
		];

		/**
		 * Filters the Site Settings Fieldmanager group children.
		 *
		 * @param array $children Group children, keyed by group name.
		 */
		$settings['children'] = apply_filters( 'create_wordpress_plugin_site_settings_group_children', $settings['children'] );

		if ( is_array( $settings['children'] ) && count( $settings['children'] ) > 0 ) {
			uasort(
				$settings['children'],
				fn ( $a, $b ): int => $a instanceof Fieldmanager_Field && $b instanceof Fieldmanager_Field ? $a->label <=> $b->label : 0,
			);

			$fm = new Fieldmanager_Group( $settings );
			$fm->activate_submenu_page();
		}
	}
}
