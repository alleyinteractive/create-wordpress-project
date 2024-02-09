<?php
/**
 * Search_Customizations class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;
use function Create_WordPress_Plugin\register_meta_helper;

use Alley\WP\Types\Feature;

final class Search_Customizations implements Feature {
	/**
	 * Set up.
	 *
	 * @param array $taxonomies The taxonomies to support primary term for.
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
	 */
	public function elasticsearch_extensions_config( $es_config ) {
		$es_config->enable_empty_search()
			->enable_post_type_aggregation()
			->restrict_post_types( $this->post_types );

		foreach ( $this->taxonomies as $taxonomy ) {
			$es_config->enable_taxonomy_aggregation( $taxonomy );
		}
	}
}
