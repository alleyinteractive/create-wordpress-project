<?php
/**
 * Create WordPress Plugin Features: Google_Tag_Manager class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;

/**
 * Feature: Injects the Google Tag Manager container snippets.
 */
final readonly class Google_Tag_Manager implements Feature {
	/**
	 * Constructor.
	 *
	 * @param string $container_id The GTM container ID, e.g. 'GTM-XXXXXX'.
	 */
	public function __construct(
		private string $container_id,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		if ( '' === $this->container_id ) {
			return;
		}

		add_action( 'wp_head', $this->on_wp_head( ... ), 1 );
		add_action( 'wp_body_open', $this->on_wp_body_open( ... ), 1 );
	}

	/**
	 * Prints scripts or data in the head tag on the front end.
	 *
	 * Outputs the Google Tag Manager container snippet.
	 */
	public function on_wp_head(): void {
		?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_js( $this->container_id ); ?>');</script>
		<!-- End Google Tag Manager -->
		<?php
	}

	/**
	 * Triggered after the opening body tag.
	 *
	 * Outputs the Google Tag Manager noscript fallback.
	 */
	public function on_wp_body_open(): void {
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( rawurlencode( $this->container_id ) ); ?>"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}
}
