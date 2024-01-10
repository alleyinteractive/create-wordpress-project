<?php
/**
 * The render callback for the create-wordpress-plugin/theme-post-subheadline block.
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
$create_wordpress_plugin_post_id = $block?->context['postId'];
if ( empty( $create_wordpress_plugin_post_id ) ) {
	return;
}
$subheadline = get_post_meta( $create_wordpress_plugin_post_id, 'create_wordpress_plugin_subheadline', true );

if ( empty( $subheadline ) ) {
	return;
}
?>
<p <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<?php echo wp_kses_post( $subheadline ); ?>
</p>
