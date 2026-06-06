<?php
/**
 * Create WordPress Plugin: Default_Term class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin;

/**
 * A taxonomy's default term.
 */
final readonly class Default_Term {
	/**
	 * Constructor.
	 *
	 * @param string $taxonomy The taxonomy.
	 */
	public function __construct(
		private string $taxonomy,
	) {}

	/**
	 * Term ID.
	 *
	 * @return int The default term ID, or 0 when none is set.
	 */
	public function term_id(): int {
		$out = 0;

		$new_style = get_option( 'default_term_' . $this->taxonomy );
		$old_style = get_option( 'default_' . $this->taxonomy );

		if ( is_numeric( $new_style ) ) {
			$out = $new_style;
		} elseif ( is_numeric( $old_style ) ) {
			$out = $old_style;
		}

		return (int) $out;
	}
}
