<?php
/**
 * Create WordPress Plugin: Primary_Term class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin;

use WPSEO_Primary_Term;

/**
 * A post's primary term in a taxonomy.
 */
final readonly class Primary_Term {
	/**
	 * Constructor.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $taxonomy The taxonomy.
	 */
	public function __construct(
		private int $post_id,
		private string $taxonomy,
	) {}

	/**
	 * Term ID.
	 *
	 * @return int The primary term ID, or 0 when none can be determined.
	 */
	public function term_id(): int {
		// Try to get the primary term from Yoast SEO.
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$primary_term    = new WPSEO_Primary_Term( $this->taxonomy, $this->post_id );
			$primary_term_id = $primary_term->get_primary_term();

			if ( is_int( $primary_term_id ) && $primary_term_id > 0 ) {
				return $primary_term_id;
			}
		}

		// Fall back to the first term alphabetically in the taxonomy.
		$terms = get_the_terms( $this->post_id, $this->taxonomy );

		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			usort( $terms, fn ( $a, $b ): int => strnatcasecmp( $a->name, $b->name ) );

			return $terms[0]->term_id;
		}

		return 0;
	}
}
