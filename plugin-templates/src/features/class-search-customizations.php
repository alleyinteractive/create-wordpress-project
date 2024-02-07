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
			->enable_taxonomy_aggregation( 'category' )
			->restrict_post_types( [ 'post', 'page' ] );
	}
}
