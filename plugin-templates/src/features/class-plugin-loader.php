<?php
/**
 * Create WordPress Plugin Features: Plugin_Loader class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Alley\WP\WP_Plugin_Loader;

/**
 * Feature: Makes the plugin loader available as a feature.
 */
final readonly class Plugin_Loader implements Feature {
	/**
	 * Constructor.
	 *
	 * @phpstan-param array<int, string> $plugins
	 *
	 * @param string[] $plugins List of plugins to load.
	 */
	public function __construct(
		private array $plugins,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		// This class loads the plugin immediately.
		new WP_Plugin_Loader( $this->plugins );
	}
}
