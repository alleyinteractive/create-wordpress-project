<?php
/**
 * Create WordPress Plugin Features: Search_Customizations class
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Elasticsearch_Extensions\Controller;

/**
 * Feature: Customizes the search experience for the site.
 *
 * @package create-wordpress-plugin
 */
final class Search_Customizations implements Feature {
	/**
	 * Set up.
	 *
	 * @param string[] $post_types A list of post types to restrict the search to.
	 * @param string[] $taxonomies A list of the taxonomies to enable aggregation for.
	 */
	public function __construct(
		private readonly array $post_types = [ 'post', 'page' ],
		private readonly array $taxonomies = [ 'category' ],
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'elasticsearch_extensions_config', [ $this, 'elasticsearch_extensions_config' ] );
	}

	/**
	 * Configure Elasticsearch Extensions.
	 *
	 * @param Controller $es_config The Elasticsearch Extensions configuration object.
	 */
	public function elasticsearch_extensions_config( Controller $es_config ): void {
		$es_config->enable_empty_search()
			->enable_post_type_aggregation()
			->restrict_post_types( $this->post_types );

		foreach ( $this->taxonomies as $taxonomy ) {
			$es_config->enable_taxonomy_aggregation( $taxonomy );
		}
	}
}
