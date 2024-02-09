<?php
/**
 * The render callback for the create-wordpress-plugin/theme-faceted-search-facets block.
 *
 * All of the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- File doesn't load in global scope, just appears to to PHPCS.
 *
 * @var array    $attributes The array of attributes for this block.
 * @var string   $content    Rendered block output. ie. <InnerBlocks.Content />.
 * @var WP_Block $block      The instance of the WP_Block class that represents the block being rendered.
 *
 * @package create-wordpress-plugin
 */

if ( ! function_exists( 'elasticsearch_extensions' ) ) {
	return;
}

// Get the aggregations that we need to work with manually.
$post_type_aggregation = elasticsearch_extensions()->get_aggregation_by_query_var( 'post_type' );
$category_aggregation  = elasticsearch_extensions()->get_aggregation_by_query_var( 'taxonomy_category' );

// If we failed to get aggregations, don't render anything.
if ( is_null( $post_type_aggregation ) && is_null( $category_aggregation ) ) {
	return;
}

// Negotiate whether any of the facet fields have been set.
$is_facet_set = ! empty( $post_type_aggregation->get_query_values() )
	|| ! empty( $category_aggregation->get_query_values() );

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<h2 class="wp-block-create-wordpress-plugin-theme-faceted-search-facets__heading">
		<?php esc_html_e( 'Filter By', 'create-wordpress-plugin' ); ?>
	</h2>

	<?php $post_type_aggregation->checkboxes(); ?>

	<?php $category_aggregation->checkboxes(); ?>

	<?php if ( ! empty( $is_facet_set ) ) : ?>
		<a class="wp-block-create-wordpress-plugin-theme-faceted-search-facets__reset" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Reset', 'create-wordpress-plugin' ); ?></a>
	<?php endif; ?>
</div>
