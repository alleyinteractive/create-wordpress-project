<?php
/**
 * The render callback for the create-wordpress-plugin/theme-search-meta block.
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

global $wp_query;

$search_query          = get_search_query( false );
$found_posts           = $wp_query->found_posts;
$found_posts_formatted = 10000 === $found_posts ? '10,000+' : number_format( $found_posts );

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<?php if ( ! empty( $search_query ) && empty( $found_posts ) ) : ?>
		<span class="wp-block-create-wordpress-plugin-theme-search-meta__no-results">
			<?php
			// translators: %s is the search query.
			echo esc_html(
				sprintf(
					__( 'No search results found for &lsquo;%s&rsquo;. Try again by using different keywords or adjusting the search filters.', 'create-wordpress-plugin' ),
					$search_query
				)
			);
			?>
		</span>
	<?php else : ?>
		<span class="wp-block-create-wordpress-plugin-theme-search-meta__results-count">
			<?php
			if ( ! empty( $search_query ) ) :
				echo esc_html(
					sprintf(
						// translators: %1$s is the number of results, %2$s is the search query.
						_n(
							'%1$s result for &lsquo;%2$s&rsquo;',
							'%1$s results for &lsquo;%2$s&rsquo;',
							$found_posts,
							'create-wordpress-plugin'
						),
						$found_posts_formatted,
						$search_query
					)
				);
			else :
				echo esc_html(
					sprintf(
						// translators: %s is the number of results.
						_n(
							'%s result',
							'%s results',
							$found_posts,
							'create-wordpress-plugin'
						),
						$found_posts_formatted,
					)
				);
			endif;
			?>
		</span>
	<?php endif; ?>
</div>
