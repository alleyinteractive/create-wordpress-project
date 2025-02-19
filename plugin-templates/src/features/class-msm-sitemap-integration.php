<?php
/**
 * Create WordPress Plugin Features: MSM_Sitemap_Integration class
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Sitemaps;

/**
 * Feature: Integrates with MSM Sitemap as the site's one true sitemap provider.
 */
final class MSM_Sitemap_Integration implements Feature {
	/**
	 * Set up.
	 *
	 * @param string[] $post_types A list of post types to include in sitemaps.
	 */
	public function __construct(
		private readonly array $post_types,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		// Include provided post types in sitemaps.
		add_filter( 'msm_sitemap_entry_post_type', fn () => $this->post_types );

		// Turn off other sitemap providers...

		// Core.
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
		add_action( 'wp_sitemaps_init', [ $this, 'action_wp_sitemaps_init' ] );
		// WordPress SEO, by forcing the option to be false.
		add_filter(
			'option_wpseo',
			function ( $value ) {
				if ( is_array( $value ) ) {
					$value['enable_xml_sitemap'] = false;
				}

				return $value;
			}
		);
		// Jetpack, by removing sitemaps as an available module and removing it from active modules.
		add_filter(
			'jetpack_get_available_modules',
			function ( $modules ) {
				unset( $modules['sitemaps'] );

				return $modules;
			}
		);
		add_filter(
			'jetpack_active_modules',
			fn ( $active ) => array_values( array_diff( $active, [ 'sitemaps' ] ) )
		);
	}

	/**
	 * Fires when initializing the Sitemaps object.
	 *
	 * @param WP_Sitemaps $wp_sitemaps Sitemaps object.
	 */
	public function action_wp_sitemaps_init( $wp_sitemaps ): void {
		/*
		 * By default, core will continue to register its rewrite rules for sitemaps even when core
		 * sitemaps are disabled so that it can send a 404 response when attempting to access
		 * sitemaps at their default URLs. This makes sense in most cases, except that both core and
		 * MSM Sitemap use the 'sitemap' query var to render their sitemaps. We don't want a 404
		 * response to be sent when accessing the MSM sitemap, so we remove the action that sends
		 * the 404 when the 'sitemap' query var is set to 'true', which is the only value that MSM
		 * Sitemap uses for the 'sitemap' query var in a rewrite rule.
		 */
		add_action(
			'template_redirect',
			function () use ( $wp_sitemaps ) {
				$qv = get_query_var( 'sitemap' );

				if ( 'true' === $qv ) {
					remove_action( 'template_redirect', [ $wp_sitemaps, 'render_sitemaps' ] );
				}
			},
			0
		);
	}
}
