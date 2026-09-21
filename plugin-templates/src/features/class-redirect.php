<?php
/**
 * Create WordPress Plugin Features: Redirect class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use Psr\Http\Message\UriInterface;

/**
 * Performs a redirect to the given URI when the feature is booted.
 */
final readonly class Redirect implements Feature {
	/**
	 * Constructor.
	 *
	 * @param UriInterface $location The URI to redirect to.
	 * @param int          $status   The HTTP status code for the redirect.
	 * @param string       $by       The application doing the redirect.
	 */
	public function __construct(
		private UriInterface $location,
		private int $status,
		private string $by,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		if ( function_exists( 'wp_safe_redirect' ) ) {
			$this->redirect();
		} else {
			// After pluggable functions are loaded, we can safely call wp_safe_redirect().
			add_action( 'plugins_loaded', $this->redirect( ... ) );
		}
	}

	/**
	 * Perform the redirect.
	 */
	private function redirect(): never {
		wp_safe_redirect( (string) $this->location, $this->status, $this->by );
		exit;
	}
}
