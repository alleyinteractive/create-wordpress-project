<?php
/**
 * Create WordPress Plugin Features: ID_Row_Action class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_Comment;
use WP_Post;
use WP_Site;
use WP_Term;
use WP_User;

/**
 * Feature: Adds a row action to copy and view the current post or term ID.
 */
final readonly class ID_Row_Action implements Feature {
	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_filter( 'post_row_actions', $this->filter_row_actions( ... ), 100, 2 );
		add_filter( 'page_row_actions', $this->filter_row_actions( ... ), 100, 2 );
		add_filter( 'tag_row_actions', $this->filter_row_actions( ... ), 100, 2 );
	}

	/**
	 * Filters the array of row action links on list tables.
	 *
	 * @param string[]        $actions Row actions.
	 * @param WP_Post|WP_Term $object  Post or term object.
	 * @return string[] Updated row actions.
	 */
	public function filter_row_actions( $actions, $object ) {
		$id = $this->object_id( $object );

		$actions['copy_id'] = sprintf(
			'<button class="button-link create-wordpress-plugin-copy-object-id" data-object-id="%d">%s</button>',
			$id,
			esc_html__( 'Copy ID', 'create-wordpress-plugin' ),
		);

		/* translators: %d: object ID */
		$actions['view_id'] = '<span style="color: #555;">' . esc_html( sprintf( __( '#&nbsp;%d', 'create-wordpress-plugin' ), $id ) ) . '</span>';

		return $actions;
	}

	/**
	 * Resolve a core object instance to its ID.
	 *
	 * @param mixed $object Object instance.
	 * @return int The object ID, or 0 when it cannot be determined.
	 */
	private function object_id( $object ): int {
		return match ( true ) {
			$object instanceof WP_Post    => (int) $object->ID,
			$object instanceof WP_Term    => (int) $object->term_id,
			$object instanceof WP_User    => (int) $object->ID,
			$object instanceof WP_Comment => (int) $object->comment_ID,
			$object instanceof WP_Site    => (int) $object->blog_id,
			is_numeric( $object )         => (int) $object,
			default                       => 0,
		};
	}
}
