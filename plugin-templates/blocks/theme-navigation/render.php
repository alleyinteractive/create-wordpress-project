<?php
/**
 * The render callback for the create-wordpress-plugin/theme-navigation block.
 *
 * All of the parameters passed to the function where this file is being required are accessible in this scope:
 *
 * @var array<string, mixed> $attributes The array of attributes for this block.
 * @var string               $content   Rendered block output. ie. <InnerBlocks.Content />.
 * @var WP_Block             $block     The instance of the WP_Block class that represents the block being rendered.
 *
 * @package create-wordpress-plugin
 */

$create_wordpress_plugin_menu_location = isset( $attributes['menuLocation'] ) && is_string( $attributes['menuLocation'] )
	? $attributes['menuLocation']
	: '';

if ( empty( $create_wordpress_plugin_menu_location ) ) {
	return;
}

?>
<nav <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> data-location="<?php echo esc_attr( $create_wordpress_plugin_menu_location ); ?>">
	<?php
	wp_nav_menu(
		[
			'theme_location' => $create_wordpress_plugin_menu_location,
			'container'      => false,
			'fallback_cb'    => false,
		]
	);
	?>
</nav>
