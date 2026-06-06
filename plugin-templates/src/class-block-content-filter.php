<?php
/**
 * Create WordPress Plugin: Block_Content_Filter class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin;

use Alley\WP\Blocks\Block_Content;
use Alley\WP\Types\Feature;
use Alley\WP\Types\Serialized_Blocks;
use Closure;

/**
 * Filter block markup in 'the_content' for the post being viewed.
 */
final readonly class Block_Content_Filter implements Feature {
	/**
	 * Callback to merge blocks.
	 *
	 * @var Closure
	 */
	private Closure $block_merge;

	/**
	 * Constructor.
	 *
	 * @param callable $block_merge Callback to merge blocks.
	 */
	public function __construct(
		callable $block_merge,
	) {
		$this->block_merge = $block_merge( ... );
	}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'the_content', $this->filter_the_content( ... ), 8 );
	}

	/**
	 * Filters the post content.
	 *
	 * @param string $content Content of the current post.
	 * @return string Updated content.
	 */
	public function filter_the_content( $content ) {
		$post = get_post();

		/*
		 * Skip if 'the_content' is running on non-block content or on content other than the post being viewed.
		 * Run only if the *original* post content has blocks, which allows blocks to be merged into freeform HTML
		 * without causing this filter to be invoked.
		 */
		if ( $post && ( is_single( $post->ID ) || is_page( $post->ID ) ) && has_blocks( $post->post_content ) ) {
			$merge = ( $this->block_merge )( new Block_Content( $content ), $post->ID );

			if ( $merge instanceof Serialized_Blocks ) {
				$content = $merge->serialized_blocks();
			}
		}

		return $content;
	}
}
