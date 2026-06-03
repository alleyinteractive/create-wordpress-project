<?php
/**
 * Create WordPress Plugin Features: Term_Dates class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use DateTimeZone;
use Psr\Clock\ClockInterface;

use function Mantle\Support\Helpers\register_meta_helper;

/**
 * Feature: Record date and modified GMT dates as term meta, mirroring the
 * `post_date_gmt` and `post_modified_gmt` columns posts already have.
 */
final readonly class Term_Dates implements Feature {
	/**
	 * Constructor.
	 *
	 * @param ClockInterface $clock Current time.
	 */
	public function __construct(
		private ClockInterface $clock,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		add_action( 'init', $this->on_init( ... ) );
		add_action( 'saved_term', $this->on_saved_term( ... ), 10, 4 );
	}

	/**
	 * Fires after WordPress has finished loading but before any headers are sent.
	 *
	 * Registers the date and modified GMT meta for all taxonomies.
	 */
	public function on_init(): void {
		register_meta_helper(
			'term',
			'all',
			'term_date_gmt',
			[
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
			]
		);

		register_meta_helper(
			'term',
			'all',
			'term_modified_gmt',
			[
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
			]
		);
	}

	/**
	 * Fires after a term has been saved, and the term cache has been cleared.
	 *
	 * Records the term's date and modified date.
	 *
	 * 'term_modified_gmt' is recorded on every save. When a term is created, the
	 * current time is stored as 'term_date_gmt'. When a term is updated and
	 * 'term_date_gmt' wasn't previously set, the all-zeroes placeholder is stored,
	 * since the term's true creation date can't be known after the fact.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param bool   $update   Whether this is an existing term being updated.
	 */
	public function on_saved_term( $term_id, $tt_id, $taxonomy, $update ): void {
		$now = $this->clock->now()->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );

		// The modified date is always refreshed, whether creating or updating.
		update_term_meta( $term_id, 'term_modified_gmt', $now );

		if ( $update === false ) {
			// On create, record the current date.
			update_term_meta( $term_id, 'term_date_gmt', $now );
		} elseif ( ! metadata_exists( 'term', $term_id, 'term_date_gmt' ) ) {
			// On update, fall back to all-zeroes when no date is stored.
			update_term_meta( $term_id, 'term_date_gmt', '0000-00-00 00:00:00' );
		}
	}
}
