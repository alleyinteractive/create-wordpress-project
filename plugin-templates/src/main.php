<?php
/**
 * The main plugin function
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin;

use Alley\WP\Features\Effect;
use Alley\WP\Features\Group;
use Alley\WP\Features\Ordered;
use Alley\WP\Features\Quick_Feature;
use Alley\WP\Features\WP_CLI_Feature;
use Alley\WP\Post_Query\Global_Post_Query;
use Nyholm\Psr7\Uri;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\HttpFoundation\Request;
use WP_Block_Type_Registry;
use WP_Path_Dispatch\Path_Dispatch;
use WP_Query;

/**
 * Instantiate the plugin.
 */
function main(): void {
	// Environment.
	$environment_type = wp_get_environment_type();

	// Current HTTP request.
	$request = Request::createFromGlobals();

	// Requested URI.
	try {
		$request_uri = new Uri( $request->getUri() );
	} catch ( \Exception $e ) {
		// If the request URI is malformed, default to root.
		$request_uri = new Uri( '/' );
	}

	// Home URI.
	$home_uri = new Uri( home_url() );

	// Main query.
	$main_query = new Global_Post_Query( 'wp_the_query', new WP_Query() );

	// Block type registry.
	$block_type_registry = WP_Block_Type_Registry::get_instance();

	// Custom endpoints.
	$path_dispatch = Path_Dispatch::instance();

	// Clock.
	$clock = new NativeClock();

	// Site settings.
	$site_settings = get_option( 'create_wordpress_plugin_site_settings', [] );

	$plugin = new Group(
		new Ordered(
			first: new Ordered(
				first: new Features\Plugin_Loader(
					plugins: [
						'wp-alleyvate/wp-alleyvate.php',
					],
				),
				then: new Group(
					new Features\Plugin_Loader(
						plugins: [
							'byline-manager/byline-manager.php',
							'meta-inspector/meta-inspector.php',
							'safe-redirect-manager/safe-redirect-manager.php',
							'wordpress-fieldmanager/fieldmanager.php',
							'wordpress-seo/wp-seo.php',
							'wp-curate/wp-curate.php',
							'wp-new-relic-transactions/plugin.php',
						],
					),
					new Group(
						new Effect(
							when: fn (): bool => $environment_type === 'local',
							then: new Features\Plugin_Loader(
								plugins: [
									'create-block-theme/create-block-theme.php',
								],
							),
						),
						new WP_CLI_Feature(
							new Features\Reset_Theme_Command(
								plugins: new Features\Plugin_Loader(
									plugins: [
										'create-block-theme/create-block-theme.php',
									],
								),
							),
						),
					),
					new Ordered(
						first: new Features\Plugin_Loader(
							plugins: [
								'elasticsearch-extensions/elasticsearch-extensions.php',
							],
						),
						then: new Features\Search_Customizations(
							post_types: [ 'post', 'page' ],
							taxonomies: [ 'category' ],
						),
					),
					new Ordered(
						first: new Features\Plugin_Loader(
							plugins: [
								'wp-asset-manager/wp-asset-manager.php',
							],
						),
						then: new Quick_Feature(
							// Set default attributes.
							fn (): true => add_filter(
								'am_global_svg_attributes',
								fn (): array => [
									'aria-hidden' => 'true',
									'focusable'   => 'false',
								],
							),
						),
					),
					new Ordered(
						first: new Features\Plugin_Loader(
							[
								'msm-sitemap/msm-sitemap.php',
							],
						),
						then: new Features\MSM_Sitemap_Integration(
							post_types: [ 'post' ],
						),
					),
				),
			),
			then: new Group(
				new Features\Load_Entries(
					cache: 'local' !== $environment_type,
				),
				new Features\Site_Settings_Page(
					option_name: 'create_wordpress_plugin_site_settings',
					capability: 'manage_options',
				),
				new Features\Alley_Change_Modified(),
				new Features\Term_Dates(
					clock: $clock,
				),
				new Group(
					new Features\Disable_Block_Patterns(),
					new Features\Register_Block_Manifest(),
					new Features\Allowed_Block_Types(
						context: 'core/edit-post',
						allowed: function ( \WP_Block_Type $block_type ): bool {
							$core_block = in_array(
								$block_type->name,
								[
									'core/audio',
									'core/button',
									'core/buttons',
									'core/code',
									'core/column',
									'core/columns',
									'core/cover',
									'core/embed',
									'core/gallery',
									'core/group',
									'core/heading',
									'core/html',
									'core/image',
									'core/list',
									'core/list-item',
									'core/media-text',
									'core/paragraph',
									'core/pullquote',
									'core/quote',
									'core/separator',
									'core/spacer',
									'core/table',
									'core/video',
								],
								true,
							);

							$plugin_block = str_starts_with( $block_type->name, 'create-wordpress-plugin/' );

							return $core_block || $plugin_block;
						},
						registry: $block_type_registry,
					),
				),
				new Features\Taxonomy_Default_Term_REST_Field(),
				new Features\Supported_Meta_Keys_REST_Field(),
				new Features\Supported_Fields_REST_Field(),
				new Features\Query_Block_Namespace_Context(),
				new Features\Featured_Image_Caption(),
				new Features\Subheadline(),
				new Features\Primary_Term_REST_Field(
					taxonomy: 'category',
				),
				new Features\ID_Row_Action(),
			),
		),
	);

	$plugin->boot();
}
