<?php
/**
 * Create WordPress Plugin Features: Reset_Theme_Command class file
 *
 * @package create-wordpress-plugin
 */

namespace Create_WordPress_Plugin\Features;

use Alley\WP\Types\Feature;
use WP_CLI;
use WP_Error;
use WP_REST_Request;

use function WP_CLI\Utils\get_flag_value;

/**
 * Feature: CLI command to reset database theme customizations to the block theme.
 */
final readonly class Reset_Theme_Command implements Feature {
	/**
	 * Constructor.
	 *
	 * @param Plugin_Loader $plugins Loads the create-block-theme plugin.
	 */
	public function __construct(
		private Plugin_Loader $plugins,
	) {}

	/**
	 * Boot the feature.
	 */
	public function boot(): void {
		WP_CLI::add_command( 'theme reset', $this->command( ... ) );
	}

	/**
	 * Reset database theme customizations to the block theme.
	 *
	 * @phpstan-param array<int, string> $args
	 * @phpstan-param array<string, string> $assoc_args
	 *
	 * --user=<id|login|email>
	 * : Authorized user ID, user_login, or user_email. Needs 'edit_theme_options' capability.
	 *
	 * [--reset-styles]
	 * : Reset theme styles. Defaults to true.
	 *
	 * [--reset-templates]
	 * : Reset theme templates. Defaults to true.
	 *
	 * [--reset-template-parts]
	 * : Reset theme template parts. Defaults to true.
	 *
	 * [--yes]
	 * : Skip confirmation prompt.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function command( $args, $assoc_args ): void {
		WP_CLI::confirm( 'Are you sure you want to reset the theme? This action cannot be undone.', $assoc_args );

		// Ensure the create-block-theme plugin is loaded before calling its endpoint.
		$this->plugins->boot();

		$req = new WP_REST_Request( 'POST', '/create-block-theme/v1/reset-theme' );
		$req->set_body_params(
			[
				'resetStyles'        => get_flag_value( $assoc_args, 'reset-styles', true ),
				'resetTemplates'     => get_flag_value( $assoc_args, 'reset-templates', true ),
				'resetTemplateParts' => get_flag_value( $assoc_args, 'reset-template-parts', true ),
			],
		);

		$res   = rest_do_request( $req );
		$error = $res->as_error();

		if ( $error instanceof WP_Error ) {
			WP_CLI::error( $error->get_error_message() );
		}

		$status = $res->get_status();

		if ( 200 !== $status ) {
			WP_CLI::error( 'Unexpected status code: ' . $status );
		}

		WP_CLI::success( 'Process complete!' );
	}
}
