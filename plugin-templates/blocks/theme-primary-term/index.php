<?php
/**
 * Block Name: Primary Term.
 *
 * @package create-wordpress-plugin
 */

/**
 * Registers the create-wordpress-plugin/theme-primary-term block using the metadata loaded from the `block.json` file.
 */
function create_wordpress_plugin_primary_term_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'create_wordpress_plugin_primary_term_block_init' );
