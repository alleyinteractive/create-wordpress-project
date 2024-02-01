<?php
/**
 * Block Name: Primary Term.
 *
 * @package create-wordpress-plugin
 */

/**
 * Registers the create-wordpress-plugin/primary-term block using the metadata loaded from the `block.json` file.
 */
function primary_term_primary_term_block_init(): void {
	// Register the block by passing the location of block.json.
	register_block_type(
		__DIR__
	);
}
add_action( 'init', 'primary_term_primary_term_block_init' );
