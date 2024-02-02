<?php
/**
 * The render callback for the create-wordpress-plugin/primary-term block.
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
$post_id = isset( $block->context['postId'] ) ? $block->context['postId'] : get_the_ID();

$primary_term_rest = new \Create_WordPress_Plugin\Features\Primary_Term_Rest;
$primary_term      = $primary_term_rest->get_primary_term( $post_id, $attributes['taxonomy'] );
if ( ! $primary_term ) {
	return;
}
?>
<span <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<?php if ( $attributes['isLink'] ) : ?>
		<a href="<?php echo esc_url( $primary_term['term_link'] ); ?>">
			<?php echo esc_html( $primary_term['term_name'] ); ?>
		</a>
	<?php else : ?>
		<?php echo esc_html( $primary_term['term_name'] ); ?>
	<?php endif; ?>
</span>
