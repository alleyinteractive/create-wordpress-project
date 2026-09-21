<?php
/**
 * The render callback for the create-wordpress-plugin/primary-term block.
 *
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- File doesn't load in global scope, just appears to to PHPCS.
 *
 * All of the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @phpstan-var array<string, mixed> $attributes
 *
 * @var array    $attributes The array of attributes for this block.
 * @var string   $content    Rendered block output. ie. <InnerBlocks.Content />.
 * @var WP_Block $block      The instance of the WP_Block class that represents the block being rendered.
 *
 * @package create-wordpress-plugin
 */


if ( ! isset( $block->context['postId'] ) || ! is_numeric( $block->context['postId'] ) || ! $block->context['postId'] > 0 ) {
	return;
}

if ( ! isset( $attributes['taxonomy'] ) || ! is_string( $attributes['taxonomy'] ) ) {
	return;
}

$primary_term = new \Create_WordPress_Plugin\Primary_Term( (int) $block->context['postId'], $attributes['taxonomy'] );
$term         = get_term( $primary_term->term_id() );

if ( ! $term instanceof WP_Term ) {
	return;
}

$term_name = html_entity_decode( $term->name );
$term_link = get_term_link( $term );

if ( ! is_string( $term_link ) ) {
	$term_link = '';
}

?>
<span <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<?php if ( $attributes['isLink'] && $term_link !== '' ) : ?>
		<a href="<?php echo esc_url( $term_link ); ?>">
			<?php echo esc_html( $term_name ); ?>
		</a>
	<?php else : ?>
		<?php echo esc_html( $term_name ); ?>
	<?php endif; ?>
</span>
