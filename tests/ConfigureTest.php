<?php
/**
 * Create WordPress Project Tests: Configure Script
 *
 * Exercises configure.php end-to-end: every test copies the skeleton into a
 * temporary directory, runs the script there with a scripted set of answers,
 * and asserts on the files it leaves behind.
 *
 * The script shells out to Composer, WP-CLI, git and curl, so the tests put a
 * directory of test doubles for those commands at the front of `PATH`. The
 * doubles log every call, scaffold the plugin and theme from the fixtures in
 * this file instead of downloading them, and create a plugin directory for
 * every `composer require`. Nothing here touches the network.
 *
 * The plugin and theme fixtures stand in for the alleyinteractive/
 * create-wordpress-plugin and create-wordpress-theme skeletons. They are
 * deliberately small, but the parts the configure script reads, renames,
 * rewrites and deletes are copied from the real skeletons: refresh them when
 * those repositories change.
 *
 * This test only exists in the skeleton itself. The configure script excludes
 * it from the search and replace, so that its expectations stay readable, and
 * deletes it when it deletes itself.
 *
 * Run with `composer test:configure`.
 *
 * @package create-wordpress-project
 *
 * phpcs:disable WordPress.WP.AlternativeFunctions
 * phpcs:disable WordPress.PHP.DiscouragedPHPFunctions
 * phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions
 */

declare(strict_types=1);

namespace Create_WordPress_Project\Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase as PHPUnit_Test_Case;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Tests for the configure.php setup script.
 */
final class ConfigureTest extends PHPUnit_Test_Case {
	/**
	 * Tokens that should be replaced in every file the script touches.
	 *
	 * @var array<int, string>
	 */
	private const PLACEHOLDERS = [
		'A skeleton WordPress project',
		'CREATE_WORDPRESS_PLUGIN',
		'CREATE_WORDPRESS_PROJECT',
		'CREATE_WORDPRESS_THEME',
		'Create WordPress Plugin',
		'Create WordPress Project',
		'Create WordPress Theme',
		'Create_WordPress_Plugin',
		'Create_WordPress_Theme',
		'author_name',
		'author_username',
		'create-wordpress-plugin',
		'create-wordpress-project',
		'create-wordpress-theme',
		'create_wordpress_plugin',
		'create_wordpress_project',
		'create_wordpress_theme',
		'email@domain.com',
		'vendor_name',
		'vendor_slug',
	];

	/**
	 * Paths the script leaves alone, relative to the project root.
	 *
	 * @var array<int, string>
	 */
	private const UNTOUCHED_PATHS = [
		'LICENSE',
		'composer.lock',
		'package-lock.json',
		'tests/ConfigureTest.php',
	];

	/**
	 * Directory names that are never copied into the test workspace.
	 *
	 * @var array<int, string>
	 */
	private const SKIPPED_PATHS = [
		'.git',
		'.phpcs.cache.json',
		'.phpunit.result.cache',
		'.turbo',
		'build',
		'composer.lock',
		'node_modules',
		'package-lock.json',
		'vendor',
	];

	/**
	 * Temporary directory holding the copies of the skeleton for one test.
	 */
	private string $workspace = '';

	/**
	 * Set up the workspace, or skip when the skeleton isn't available.
	 */
	protected function setUp(): void {
		parent::setUp();

		if ( ! file_exists( $this->skeleton_root() . '/configure.php' ) ) {
			$this->markTestSkipped( 'configure.php is not present: this project has already been configured.' );
		}

		if ( ! is_dir( $this->skeleton_root() . '/ci-templates' ) ) {
			$this->markTestSkipped( 'The skeleton is incomplete: run these tests from the project root with `composer test:configure`.' );
		}

		$workspace = sys_get_temp_dir() . '/create-wordpress-project-configure-' . bin2hex( random_bytes( 6 ) );

		mkdir( $workspace, 0777, true );

		$this->workspace = (string) realpath( $workspace );

		$this->write_command_doubles();
	}

	/**
	 * Remove the workspace.
	 */
	protected function tearDown(): void {
		if ( '' !== $this->workspace ) {
			$this->delete_directory( $this->workspace );

			$this->workspace = '';
		}

		parent::tearDown();
	}

	/**
	 * The placeholder tokens should be gone from every file the script touches.
	 */
	public function test_replaces_every_placeholder_token_in_the_project(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$this->assertSame( [], $this->find_placeholders( $project, self::PLACEHOLDERS ) );
	}

	/**
	 * The project name, slug, description and vendor should be replaced in the
	 * root Composer configuration.
	 */
	public function test_replaces_the_tokens_in_the_root_composer_json(): void {
		$composer = $this->read_json( $this->configure( $this->pantheon_answers() ) . '/composer.json' );

		$this->assertSame( 'test-vendor/my-cool-site', $composer['name'] );
		$this->assertSame( 'A very cool site.', $composer['description'] );
		$this->assertContains( 'my-cool-site', $composer['keywords'] );
		$this->assertSame( 'https://github.com/test-vendor/my-cool-site', $composer['homepage'] );

		// The plugin and theme test namespaces follow the new slugs.
		$this->assertSame(
			[
				'Cool_Features_Plugin\\Tests\\' => 'plugins/cool-features/tests',
				'Cool_Theme_Theme\\Tests\\'     => 'themes/cool-theme/tests',
			],
			$composer['autoload-dev']['psr-4'],
		);
	}

	/**
	 * The autoloader map should be rewritten to point at the scaffolded plugin
	 * and theme, which the script does through `composer config`.
	 */
	public function test_registers_the_plugin_and_theme_with_the_autoloader(): void {
		$composer = $this->read_json( $this->configure( $this->pantheon_answers() ) . '/composer.json' );

		$this->assertSame(
			[
				'Cool_Features_Plugin' => 'plugins/cool-features/src',
				'Cool_Theme_Theme'     => 'themes/cool-theme/src',
			],
			$composer['extra']['wordpress-autoloader']['autoload'],
		);
	}

	/**
	 * The project slug should be replaced in the root front-end configuration,
	 * which references the plugin and theme directories by name.
	 */
	public function test_replaces_the_tokens_in_the_root_front_end_configuration(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$package = $this->read_json( $project . '/package.json' );

		$this->assertSame( 'my-cool-site', $package['name'] );
		$this->assertSame(
			[ 'packages/*', 'plugins/cool-features', 'themes/cool-theme' ],
			$package['workspaces'],
		);

		$this->assertStringContainsString(
			'**/plugins/cool-features/**/__tests__/**/*.ts?(x)',
			$this->read( $project . '/jest.config.ts' ),
		);

		$tsconfig = $this->read_json( $project . '/tsconfig.json' );

		$this->assertContains( './plugins/cool-features/build', $tsconfig['exclude'] );
		$this->assertContains( './themes/cool-theme/**/*', $tsconfig['include'] );
	}

	/**
	 * The must-use plugins should be rewritten: the function prefix, the
	 * documentation block and the path to the project's own plugin.
	 */
	public function test_replaces_the_tokens_in_the_mu_plugins(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$loader = $this->read( $project . '/mu-plugins/plugin-loader.php' );

		$this->assertStringContainsString( 'Must-use plugin loader for my-cool-site.', $loader );
		$this->assertStringContainsString( 'function my_cool_site_local_plugins(): array', $loader );
		$this->assertStringContainsString( 'function my_cool_site_core_plugins(): array', $loader );
		$this->assertStringContainsString( 'my_cool_site_local_plugins()', $loader );
		$this->assertStringContainsString( 'cool-features/cool-features.php', $loader );

		$this->assertStringContainsString(
			'@package my-cool-site',
			$this->read( $project . '/mu-plugins/000-wp-environment.php' ),
		);
	}

	/**
	 * The linting configuration should be rewritten, including the paths that
	 * name the plugin and theme directories.
	 */
	public function test_replaces_the_tokens_in_the_linting_configuration(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$phpcs = $this->read( $project . '/.phpcs.xml' );

		$this->assertStringContainsString( 'name="my-cool-site"', $phpcs );
		$this->assertStringContainsString( 'PHP_CodeSniffer standard for my-cool-site.', $phpcs );
		$this->assertStringContainsString( './plugins/cool-features/src/post-types/', $phpcs );

		$phpstan = $this->read( $project . '/phpstan.neon' );

		$this->assertStringContainsString( '- plugins/cool-features/', $phpstan );
		$this->assertStringContainsString( '- themes/cool-theme/', $phpstan );

		$this->assertStringContainsString(
			'plugins/cool-features/tests',
			$this->read( $project . '/.deployignore' ),
		);
	}

	/**
	 * The paragraphs about the skeleton should be removed from the README, and
	 * the author credits filled in.
	 */
	public function test_removes_the_skeleton_paragraphs_from_the_readme(): void {
		$readme = $this->read( $this->configure( $this->pantheon_answers() ) . '/README.md' );

		$this->assertStringNotContainsString( '<!--delete-->', $readme );
		$this->assertStringNotContainsString( '<!--/delete-->', $readme );
		$this->assertStringNotContainsString( 'Press the "Use template" button', $readme );

		$this->assertStringStartsWith( '# my-cool-site', $readme );
		$this->assertStringContainsString( '[Test Author](https://github.com/test-user)', $readme );
	}

	/**
	 * The plugin should be scaffolded into the plugins directory, with its main
	 * file renamed after the plugin slug.
	 */
	public function test_scaffolds_and_renames_the_plugin(): void {
		$project = $this->configure( $this->pantheon_answers() );
		$plugin  = $project . '/plugins/cool-features';

		$this->assertFileDoesNotExist( $plugin . '/plugin.php' );

		$main = $this->read( $plugin . '/cool-features.php' );

		$this->assertStringContainsString( 'Plugin Name: Cool Features', $main );
		$this->assertStringContainsString( 'Author: Test Author', $main );
		$this->assertStringContainsString( 'Text Domain: cool-features', $main );
		$this->assertStringContainsString( '@package cool-features', $main );
		$this->assertStringContainsString( 'namespace Cool_Features_Plugin;', $main );
		$this->assertStringContainsString( "define( 'COOL_FEATURES_DIR', __DIR__ );", $main );

		/*
		 * Known issues: the description the script replaces is the project's
		 * ("A skeleton WordPress project"), so the plugin keeps the plugin
		 * skeleton's own, and only the composer package name is rewritten with
		 * the new vendor, so the plugin header still points at Alley's
		 * repository. Update these when the script handles them.
		 */
		$this->assertStringContainsString( 'Description: A skeleton WordPress plugin', $main );
		$this->assertStringContainsString( 'Plugin URI: https://github.com/alleyinteractive/cool-features', $main );

		// The test bootstrap follows the renamed main file.
		$this->assertStringContainsString(
			"require_once __DIR__ . '/../cool-features.php'",
			$this->read( $plugin . '/tests/bootstrap.php' ),
		);

		// The example class is removed in favor of the templated features.
		$this->assertFileDoesNotExist( $plugin . '/src/class-example-plugin.php' );
	}

	/**
	 * The theme should be scaffolded into the themes directory and activated.
	 */
	public function test_scaffolds_and_activates_the_theme(): void {
		$project = $this->configure( $this->pantheon_answers() );
		$theme   = $project . '/themes/cool-theme';

		$style = $this->read( $theme . '/style.css' );

		$this->assertStringContainsString( 'Theme Name: Cool Theme', $style );
		$this->assertStringContainsString( 'Author: Test Author', $style );
		$this->assertStringContainsString( 'Text Domain: cool-theme', $style );

		$functions = $this->read( $theme . '/functions.php' );

		$this->assertStringContainsString( '@package cool-theme', $functions );
		$this->assertStringContainsString( 'namespace Cool_Theme_Theme;', $functions );
		$this->assertStringContainsString( "define( 'COOL_THEME_DIR', __DIR__ );", $functions );

		$this->assertStringContainsString( 'wp theme activate cool-theme', $this->command_log() );
	}

	/**
	 * The plugin templates should be copied into the plugin and rewritten, then
	 * the templates directory removed from the project.
	 */
	public function test_copies_the_plugin_templates_into_the_plugin(): void {
		$project = $this->configure( $this->pantheon_answers() );
		$plugin  = $project . '/plugins/cool-features';

		$this->assertDirectoryDoesNotExist( $project . '/plugin-templates' );

		$this->assertDirectoryExists( $plugin . '/blocks' );
		$this->assertDirectoryExists( $plugin . '/config' );
		$this->assertDirectoryExists( $plugin . '/entries' );
		$this->assertFileExists( $plugin . '/src/features/class-subheadline.php' );

		$feature = $this->read( $plugin . '/src/features/class-subheadline.php' );

		$this->assertStringContainsString( 'namespace Cool_Features_Plugin\Features;', $feature );
		$this->assertStringContainsString( '@package cool-features', $feature );

		// The templated PHPCS configuration carries the new text domain and
		// global prefix.
		$phpcs = $this->read( $plugin . '/.phpcs.xml' );

		$this->assertStringContainsString( 'name="Cool Features Configuration"', $phpcs );
		$this->assertStringContainsString( '<property name="text_domain" type="array" value="cool-features" />', $phpcs );
		$this->assertStringContainsString( '<property name="prefixes" type="array" value="cool_features" />', $phpcs );

		// The features list is consumed by the script, not shipped.
		$this->assertFileDoesNotExist( $plugin . '/features.txt' );
	}

	/**
	 * The templated features should be written into the plugin's main function
	 * when the skeleton still carries the marker comment.
	 */
	public function test_adds_the_templated_features_to_the_plugin(): void {
		$project = $this->configure(
			$this->pantheon_answers(),
			'my-cool-site',
			[ 'src/main.php' => $this->plugin_main_with_features_marker() ],
		);

		$main = $this->read( $project . '/plugins/cool-features/src/main.php' );

		$this->assertStringNotContainsString( '// Add features here.', $main );
		$this->assertStringContainsString( 'new Features\Featured_Image_Caption(),', $main );
		$this->assertStringContainsString( 'new Features\Subheadline(),', $main );
		$this->assertStringContainsString( 'new Features\Primary_Term_Rest(', $main );
	}

	/**
	 * The templated features are dropped when the plugin skeleton has no marker
	 * comment to replace.
	 *
	 * This documents the current behavior rather than endorsing it: the plugin
	 * skeleton no longer carries the `// Add features here.` comment that the
	 * script looks for, so `plugin-templates/features.txt` is read and thrown
	 * away. Update this test when the script stops relying on the comment.
	 */
	public function test_drops_the_templated_features_without_the_marker(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$this->assertStringNotContainsString(
			'new Features\Subheadline(),',
			$this->read( $project . '/plugins/cool-features/src/main.php' ),
		);
	}

	/**
	 * The plugin's README is replaced by the one that documents the templates.
	 *
	 * This documents the current behavior rather than endorsing it: the whole
	 * `plugin-templates` directory is copied over the plugin, so its README
	 * lands on top of the plugin's own. Update this test when the script copies
	 * only the template subdirectories.
	 */
	public function test_overwrites_the_plugin_readme_with_the_templates_readme(): void {
		$readme = $this->read(
			$this->configure( $this->pantheon_answers() ) . '/plugins/cool-features/README.md',
		);

		$this->assertStringContainsString( '# Plugin Templates', $readme );
		$this->assertStringNotContainsString( 'A very cool site.', $readme );
	}

	/**
	 * The build directories should be ignored by the linters in both the plugin
	 * and the theme.
	 */
	public function test_ignores_the_build_directories_in_the_plugin_and_theme(): void {
		$project = $this->configure( $this->pantheon_answers() );

		foreach ( [ 'plugins/cool-features', 'themes/cool-theme' ] as $path ) {
			$this->assertSame( "build/\n", $this->read( $project . '/' . $path . '/.eslintignore' ) );
			$this->assertSame( "build/\n", $this->read( $project . '/' . $path . '/.stylelintignore' ) );
		}
	}

	/**
	 * The front-end dependencies should be hoisted out of the plugin and theme
	 * and into the root package.json.
	 */
	public function test_hoists_the_front_end_dependencies_to_the_root(): void {
		$project = $this->configure( $this->pantheon_answers() );
		$package = $this->read_json( $project . '/package.json' );

		$this->assertArrayHasKey( 'classnames', $package['dependencies'], 'The plugin dependency should be hoisted.' );
		$this->assertArrayHasKey( 'react', $package['dependencies'], 'The existing dependencies should be kept.' );
		$this->assertArrayHasKey( '@wordpress/scripts', $package['devDependencies'] );

		// A package that appears in both lists is kept in the one with the
		// higher version and dropped from the other.
		$this->assertSame( '^2.5.1', $package['dependencies']['classnames'] );
		$this->assertArrayNotHasKey( 'classnames', $package['devDependencies'] );

		// The last `engines` block wins, which is the theme's.
		$this->assertSame( '22', $package['engines']['node'] );

		$this->assertSame( $this->sorted( array_keys( $package['dependencies'] ) ), array_keys( $package['dependencies'] ) );
		$this->assertSame( $this->sorted( array_keys( $package['devDependencies'] ) ), array_keys( $package['devDependencies'] ) );

		/*
		 * Known issue: the hoisted versions overwrite the project's own, so a
		 * plugin or theme pinned to an older release downgrades the root.
		 * Update this when the script keeps the higher version.
		 */
		$this->assertSame( '^30.0.0', $package['devDependencies']['jest'] );
	}

	/**
	 * The plugin and theme package.json files should keep only the scripts that
	 * still make sense inside the monorepo.
	 */
	public function test_truncates_the_plugin_and_theme_package_json(): void {
		$project = $this->configure( $this->pantheon_answers() );

		foreach ( [ 'plugins/cool-features', 'themes/cool-theme' ] as $path ) {
			$package = $this->read_json( $project . '/' . $path . '/package.json' );

			$this->assertArrayNotHasKey( 'dependencies', $package );
			$this->assertArrayNotHasKey( 'devDependencies', $package );
			$this->assertArrayNotHasKey( 'engines', $package );

			$this->assertSame( [ 'build' => 'alley-build' ], $package['scripts'] );
		}

		$this->assertSame( 'cool-features', $this->read_json( $project . '/plugins/cool-features/package.json' )['name'] );
		$this->assertSame( 'cool-theme', $this->read_json( $project . '/themes/cool-theme/package.json' )['name'] );
	}

	/**
	 * The Composer dependencies should be hoisted out of the plugin and theme
	 * and into the root composer.json, sorted, with `php` kept first.
	 */
	public function test_hoists_the_composer_dependencies_to_the_root(): void {
		$composer = $this->read_json( $this->configure( $this->pantheon_answers() ) . '/composer.json' );

		$this->assertArrayHasKey( 'alleyinteractive/wp-type-extensions', $composer['require'] );
		$this->assertArrayHasKey( 'alleyinteractive/wp-plugin-loader', $composer['require'], 'The existing requirements should be kept.' );
		$this->assertArrayHasKey( 'alleyinteractive/block-theme-tools', $composer['require'] );
		$this->assertArrayHasKey( 'mantle-framework/testkit', $composer['require-dev'] );

		$this->assertSame( 'php', array_key_first( $composer['require'] ) );
		$this->assertSame(
			$this->sorted( array_keys( array_slice( $composer['require'], 1 ) ) ),
			array_keys( array_slice( $composer['require'], 1 ) ),
		);
		$this->assertSame( $this->sorted( array_keys( $composer['require-dev'] ) ), array_keys( $composer['require-dev'] ) );

		/*
		 * Known issue: the hoisted requirements overwrite the project's own, so
		 * the plugin's PHP constraint replaces the project's. Update this when
		 * the script merges the constraints instead.
		 */
		$this->assertSame( '^8.2', $composer['require']['php'] );
	}

	/**
	 * The continuous integration templates should be moved to the root of the
	 * project, replacing the workflows that only test the skeleton.
	 */
	public function test_moves_the_ci_templates_to_the_root(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$this->assertDirectoryDoesNotExist( $project . '/ci-templates' );
		$this->assertFileDoesNotExist( $project . '/.github/workflows/action.yml' );
		$this->assertFileDoesNotExist( $project . '/.github/CODEOWNERS' );

		$workflow = $this->read( $project . '/.github/workflows/all-pr-tests.yml' );

		$this->assertStringContainsString( "github.repository == 'test-vendor/my-cool-site'", $workflow );
	}

	/**
	 * A Pantheon project should keep the Pantheon workflow and lose the ones
	 * that deploy to WordPress VIP.
	 */
	public function test_keeps_only_the_pantheon_workflows_for_a_pantheon_project(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$this->assertFileExists( $project . '/.github/workflows/deploy-to-pantheon.yml' );
		$this->assertFileDoesNotExist( $project . '/.github/workflows/copy-to-vip.yml' );
		$this->assertFileDoesNotExist( $project . '/.github/workflows/deploy-to-vip-built-branch.yml' );

		// The Pantheon plugins are installed and the VIP moves are skipped.
		$this->assertStringContainsString( 'composer require', $this->command_log() );
		$this->assertStringContainsString( 'wpackagist-plugin/pantheon-advanced-page-cache', $this->command_log() );
		$this->assertDirectoryExists( $project . '/mu-plugins' );
		$this->assertDirectoryDoesNotExist( $project . '/client-mu-plugins' );
	}

	/**
	 * Every plugin the script installs should be added to the plugin loader,
	 * with the project's own plugin last.
	 */
	public function test_activates_the_installed_plugins_in_the_plugin_loader(): void {
		$loader = $this->read( $this->configure( $this->pantheon_answers() ) . '/mu-plugins/plugin-loader.php' );

		// A required plugin, the suggested plugin that was accepted, and a
		// Pantheon plugin.
		$this->assertStringContainsString( "'wp-alleyvate/wp-alleyvate.php',", $loader );
		$this->assertStringContainsString( "'es-wp-query/es-wp-query.php',", $loader );
		$this->assertStringContainsString( "'query-monitor/query-monitor.php',", $loader );

		// The suggested plugins that were declined are not loaded.
		$this->assertStringNotContainsString( 'es-admin', $loader );
		$this->assertStringNotContainsString( 'duplicate-post', $loader );

		// Neither is the Pantheon plugin that opts out of activation.
		$this->assertStringNotContainsString( 'wp-mail-smtp', $loader );

		// The project's plugin is appended after the sorted list.
		$this->assertStringContainsString(
			"'wp-new-relic-transactions/wp-new-relic-transactions.php',\n\t\t\t'cool-features/cool-features.php',",
			$loader,
		);
	}

	/**
	 * A WordPress VIP project should be restructured for the VIP file layout.
	 */
	public function test_configures_the_project_for_wordpress_vip(): void {
		$project = $this->configure( $this->vip_answers() );

		$this->assertFileExists( $project . '/.github/workflows/copy-to-vip.yml' );
		$this->assertFileExists( $project . '/.github/workflows/deploy-to-vip-built-branch.yml' );
		$this->assertFileDoesNotExist( $project . '/.github/workflows/deploy-to-pantheon.yml' );

		// The repository slug is filled into both VIP workflows.
		foreach ( [ 'copy-to-vip.yml', 'deploy-to-vip-built-branch.yml' ] as $workflow ) {
			$contents = $this->read( $project . '/.github/workflows/' . $workflow );

			$this->assertStringNotContainsString( 'VIP_REPO_SLUG', $contents );
			$this->assertStringContainsString( 'my-cool-site-vip', $contents );
		}

		// The must-use plugins move to the directory VIP loads them from.
		$this->assertDirectoryExists( $project . '/client-mu-plugins' );
		$this->assertFileExists( $project . '/client-mu-plugins/plugin-loader.php' );

		$this->assertSame(
			'client-mu-plugins/vendor',
			$this->read_json( $project . '/composer.json' )['config']['vendor-dir'],
		);

		$this->assertStringContainsString(
			'- client-mu-plugins/plugin-loader.php',
			$this->read( $project . '/phpstan.neon' ),
		);

		// Pantheon's must-use plugin is removed along with its package.
		$this->assertStringContainsString( 'composer remove pantheon-systems/pantheon-mu-plugin', $this->command_log() );
		$this->assertStringNotContainsString(
			'pantheon-mu-plugin/pantheon.php',
			$this->read( $project . '/client-mu-plugins/plugin-loader.php' ),
		);

		// VIP's own must-use plugins are cloned in and the drop-in symlinked.
		$this->assertStringContainsString( 'git clone git@github.com:Automattic/vip-go-mu-plugins-built.git mu-plugins', $this->command_log() );
		$this->assertTrue( is_link( $project . '/object-cache.php' ) );

		// The VIP directories are scaffolded out.
		foreach ( [ 'vip-config', 'images', 'languages', 'private' ] as $directory ) {
			$this->assertFileExists( $project . '/' . $directory . '/.gitkeep' );
		}

		// The suggested plugin VIP already provides is never offered.
		$this->assertStringNotContainsString( 'alleyinteractive/es-wp-query', $this->command_log() );
	}

	/**
	 * The VIP ignore rules are appended with a literal `\n` instead of a
	 * newline.
	 *
	 * This documents the current behavior rather than endorsing it: the escape
	 * is written inside single quotes, so both files gain the text `\n` and the
	 * rule is appended to whatever line came before it. Update this test when
	 * the script is fixed.
	 */
	public function test_appends_a_literal_escape_to_the_vip_ignore_files(): void {
		$project = $this->configure( $this->vip_answers() );

		foreach ( [ '.gitignore', '.deployignore' ] as $file ) {
			$contents = $this->read( $project . '/' . $file );

			$this->assertStringContainsString( 'client-mu-plugins\\n', $contents );
			$this->assertStringEndsNotWith( "client-mu-plugins\n", $contents );
		}
	}

	/**
	 * The files that only document the skeleton should be removed from the
	 * project, the plugin and the theme.
	 */
	public function test_removes_the_extraneous_files(): void {
		$project = $this->configure( $this->pantheon_answers() );

		foreach ( [ 'CONTRIBUTING.md', 'CONTRIBUTORS.md', 'composer-templates', 'plugin-templates' ] as $path ) {
			$this->assertFileDoesNotExist( $project . '/' . $path );
		}

		$plugin_files = [
			'.deployignore',
			'.editorconfig',
			'.eslintrc.json',
			'.gitattributes',
			'.github',
			'.gitignore',
			'.nvmrc',
			'.phpcs',
			'.stylelintrc.json',
			'.wp-env.json',
			'CHANGELOG.md',
			'Makefile',
			'composer.json',
			'configure.php',
			'jest.config.js',
			'package-lock.json',
			'phpstan.neon',
			'tests/ConfigureTest.php',
			'tsconfig.eslint.json',
			'tsconfig.json',
			'vendor',
		];

		foreach ( $plugin_files as $path ) {
			$this->assertFileDoesNotExist( $project . '/plugins/cool-features/' . $path );
		}

		$theme_files = [
			'.editorconfig',
			'.eslintrc.json',
			'.github',
			'.gitignore',
			'.nvmrc',
			'.stylelintrc.json',
			'CHANGELOG.md',
			'Makefile',
			'composer.json',
			'configure.php',
			'jest.config.js',
			'package-lock.json',
			'phpstan.neon',
			'tests/ConfigureTest.php',
			'tsconfig.eslint.json',
			'tsconfig.json',
			'vendor',
		];

		foreach ( $theme_files as $path ) {
			$this->assertFileDoesNotExist( $project . '/themes/cool-theme/' . $path );
		}

		// The files the plugin and theme still need are kept.
		$this->assertFileExists( $project . '/plugins/cool-features/tests/bootstrap.php' );
		$this->assertFileExists( $project . '/plugins/cool-features/package.json' );
		$this->assertFileExists( $project . '/themes/cool-theme/package.json' );
	}

	/**
	 * The section of the .gitignore that only applies to the skeleton should be
	 * removed, so that the lock files are committed.
	 */
	public function test_cleans_up_the_gitignore(): void {
		$gitignore = $this->read( $this->configure( $this->pantheon_answers() ) . '/.gitignore' );

		$this->assertStringNotContainsString( '# BEGIN DELETE AFTER INSTALL #', $gitignore );
		$this->assertStringNotContainsString( '# END DELETE AFTER INSTALL #', $gitignore );
		$this->assertStringNotContainsString( 'package-lock.json', $gitignore );

		// The rest of the file survives, with the new plugin and theme names.
		$this->assertStringContainsString( '!/plugins/cool-features', $gitignore );
		$this->assertStringContainsString( '!/themes/cool-theme', $gitignore );
		$this->assertStringContainsString( 'node_modules', $gitignore );
	}

	/**
	 * The script, its Makefile entry point and this test should all be cleaned
	 * up when the script deletes itself.
	 */
	public function test_deletes_itself_and_its_own_test(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$this->assertFileDoesNotExist( $project . '/configure.php' );
		$this->assertFileDoesNotExist( $project . '/Makefile' );
		$this->assertFileDoesNotExist( $project . '/tests/ConfigureTest.php' );
		$this->assertDirectoryDoesNotExist( $project . '/tests' );

		$scripts = $this->read_json( $project . '/composer.json' )['scripts'];

		$this->assertArrayNotHasKey( 'test:configure', $scripts );
		$this->assertNotContains( '@test:configure', $scripts['test'] );
		$this->assertContains( '@phpunit', $scripts['test'] );
	}

	/**
	 * Declining the self-deletion should leave the script and its test behind.
	 */
	public function test_keeps_itself_when_the_self_deletion_is_declined(): void {
		$project = $this->configure( $this->pantheon_answers( [ 'delete_configure' => 'no' ] ) );

		$this->assertFileExists( $project . '/configure.php' );
		$this->assertFileExists( $project . '/Makefile' );
		$this->assertFileExists( $project . '/tests/ConfigureTest.php' );

		// This file is excluded from the search and replace, so that it can
		// still describe the skeleton after the script has run.
		$this->assertStringContainsString(
			'create-wordpress-project',
			$this->read( $project . '/tests/ConfigureTest.php' ),
		);

		$scripts = $this->read_json( $project . '/composer.json' )['scripts'];

		$this->assertArrayHasKey( 'test:configure', $scripts );
		$this->assertContains( '@test:configure', $scripts['test'] );
	}

	/**
	 * Every value but the author details can be derived from the folder the
	 * project is checked out into.
	 */
	public function test_derives_the_defaults_from_the_parent_folder(): void {
		$project = $this->configure(
			$this->pantheon_answers(
				[
					'project_name'    => '',
					'project_slug'    => '',
					'description'     => '',
					'vendor_name'     => '',
					'author_email'    => '',
					'author_username' => '',
					'author_name'     => '',
					'plugin_slug'     => '',
					'theme_slug'      => '',
				]
			),
			'my-wordpress-site',
		);

		$composer = $this->read_json( $project . '/composer.json' );

		// The vendor and author fall back to the git remote and config.
		$this->assertSame( 'test-vendor/my-wordpress-site', $composer['name'] );
		$this->assertSame( 'My WordPress Site Website', $composer['description'] );

		// The capital P in WordPress survives the title casing.
		$this->assertStringContainsString(
			'# my-wordpress-site',
			$this->read( $project . '/README.md' ),
		);
		$this->assertStringContainsString(
			'[Git User](https://github.com/test-vendor)',
			$this->read( $project . '/README.md' ),
		);

		// The plugin takes the project slug and the theme adds the year.
		$this->assertFileExists( $project . '/plugins/my-wordpress-site/my-wordpress-site.php' );
		$this->assertDirectoryExists( $project . '/themes/my-wordpress-site-' . gmdate( 'Y' ) );
	}

	/**
	 * A project name that looks like a slug should be questioned, and accepted
	 * when the author insists.
	 */
	public function test_warns_when_the_project_name_looks_like_a_slug(): void {
		$result = $this->run_configure(
			[
				'project_name'          => 'my-cool-site',
				'project_name_continue' => 'yes',
				'project_slug'          => 'my-cool-site',
				'description'           => 'A very cool site.',
				'vendor_name'           => 'Test Vendor',
				'author_email'          => 'test@example.com',
				'author_username'       => 'test-user',
				'author_name'           => 'Test Author',
				'plugin_slug'           => 'cool-features',
				'mantle'                => 'no',
				'theme_slug'            => 'cool-theme',
				'slack_channel_id'      => '',
				'slack_channel_name'    => '',
				'modify_files'          => 'no',
			],
			$this->install_skeleton(),
		);

		$this->assertSame( 1, $result['exit_code'] );
		$this->assertStringContainsString( 'This should be a project name and not a slug.', $result['stdout'] );
		$this->assertStringContainsString( 'Project          : my-cool-site <my-cool-site>', $result['stdout'] );
	}

	/**
	 * Nothing should be modified when the confirmation is declined.
	 */
	public function test_makes_no_changes_when_the_modification_is_declined(): void {
		$project = $this->install_skeleton();
		$before  = $this->all_files( $project );
		$result  = $this->run_configure( $this->base_answers( [ 'modify_files' => 'no' ] ), $project );

		$this->assertSame( 1, $result['exit_code'] );
		$this->assertSame( $before, $this->all_files( $project ) );

		$this->assertStringContainsString(
			'A skeleton WordPress project',
			$this->read( $project . '/composer.json' ),
		);
		$this->assertFileExists( $project . '/configure.php' );
		$this->assertFileExists( $project . '/tests/ConfigureTest.php' );
	}

	/**
	 * The script should stop rather than scaffold over an existing plugin or
	 * theme.
	 */
	public function test_stops_when_the_plugin_directory_already_exists(): void {
		$project = $this->install_skeleton();

		mkdir( $project . '/plugins/cool-features' );

		$answers = $this->base_answers();
		$result  = $this->run_configure(
			array_slice( $answers, 0, 1 + (int) array_search( 'plugin_slug', array_keys( $answers ), true ) ),
			$project,
		);

		$this->assertSame( 1, $result['exit_code'] );
		$this->assertStringContainsString( 'Plugin already exists in plugins/cool-features.', $result['stdout'] );
	}

	/**
	 * The script should stop rather than scaffold over an existing theme.
	 */
	public function test_stops_when_the_theme_directory_already_exists(): void {
		$project = $this->install_skeleton();

		mkdir( $project . '/themes/cool-theme' );

		$answers = $this->base_answers();
		$result  = $this->run_configure(
			array_slice( $answers, 0, 1 + (int) array_search( 'theme_slug', array_keys( $answers ), true ) ),
			$project,
		);

		$this->assertSame( 1, $result['exit_code'] );
		$this->assertStringContainsString( 'Theme already exists in themes/cool-theme.', $result['stdout'] );
	}

	/**
	 * The Slack answers are collected but never used.
	 *
	 * This documents the current behavior rather than endorsing it: the script
	 * prompts for a Slack channel and adds `slack_channel_id` and
	 * `slack_channel_name` to the search and replace, but neither placeholder
	 * appears anywhere in the skeleton, so the answers are discarded. Update
	 * this test when the deploy workflows use them.
	 */
	public function test_collects_the_slack_channel_but_never_uses_it(): void {
		$project = $this->configure( $this->pantheon_answers() );

		$this->assertSame(
			[],
			$this->find_placeholders( $project, [ 'C012ABCDEF', 'my-cool-site-deploys' ] ),
		);
	}

	/**
	 * Answers for the prompts that are asked before any file is touched.
	 *
	 * The keys only document which prompt each answer belongs to. Overrides
	 * replace an answer in place; new keys are appended, which is where the
	 * remaining prompts belong.
	 *
	 * @param array<string, string> $overrides Answers to replace or append.
	 * @return array<string, string>
	 */
	private function base_answers( array $overrides = [] ): array {
		return array_merge(
			[
				'project_name'       => 'My Cool Site',
				'project_slug'       => 'my-cool-site',
				'description'        => 'A very cool site.',
				'vendor_name'        => 'Test Vendor',
				'author_email'       => 'test@example.com',
				'author_username'    => 'test-user',
				'author_name'        => 'Test Author',
				'plugin_slug'        => 'cool-features',
				'mantle'             => 'no',
				'theme_slug'         => 'cool-theme',
				'slack_channel_id'   => 'C012ABCDEF',
				'slack_channel_name' => 'my-cool-site-deploys',
				'modify_files'       => 'yes',
			],
			$overrides,
		);
	}

	/**
	 * Answers for a Pantheon project with a plugin and a theme.
	 *
	 * @param array<string, string> $overrides Answers to replace.
	 * @return array<string, string>
	 */
	private function pantheon_answers( array $overrides = [] ): array {
		return $this->base_answers(
			array_merge(
				[
					'vip'                    => 'no',
					'pantheon'               => 'yes',
					'suggest_es_admin'       => 'no',
					'suggest_es_wp_query'    => 'yes',
					'suggest_fieldmanager'   => 'no',
					'suggest_duplicate_post' => 'no',
					'license_key'            => '',
					'delete_configure'       => 'yes',
				],
				$overrides,
			),
		);
	}

	/**
	 * Answers for a WordPress VIP project.
	 *
	 * VIP already provides ES WP Query, so that suggestion is not offered.
	 *
	 * @param array<string, string> $overrides Answers to replace.
	 * @return array<string, string>
	 */
	private function vip_answers( array $overrides = [] ): array {
		return $this->base_answers(
			array_merge(
				[
					'vip'                    => 'yes',
					'vip_repo_name'          => 'my-cool-site-vip',
					'suggest_es_admin'       => 'no',
					'suggest_fieldmanager'   => 'no',
					'suggest_duplicate_post' => 'no',
					'delete_configure'       => 'yes',
				],
				$overrides,
			),
		);
	}

	/**
	 * Copy the skeleton, run the configure script and assert that it succeeded.
	 *
	 * @param array<string, string> $answers          Answers to the prompts, in order.
	 * @param string                $folder           Folder to check the project out into.
	 * @param array<string, string> $plugin_overrides Plugin fixture files to replace.
	 * @return string The configured project directory.
	 */
	private function configure( array $answers, string $folder = 'my-cool-site', array $plugin_overrides = [] ): string {
		$project = $this->install_skeleton( $folder, $plugin_overrides );

		$this->assert_exited_cleanly( $this->run_configure( $answers, $project ) );

		return $project;
	}

	/**
	 * Assert that the script ran to completion.
	 *
	 * @param array{exit_code: int, stdout: string, stderr: string} $result Result of the run.
	 */
	private function assert_exited_cleanly( array $result ): void {
		$this->assertSame(
			0,
			$result['exit_code'],
			"The configure script did not exit cleanly:\n" . $result['stdout'] . $result['stderr'],
		);
	}

	/**
	 * Run the configure script against a copy of the skeleton.
	 *
	 * @param array<string, string> $answers Answers to the prompts, in order.
	 * @param string                $project Project directory to run in.
	 * @return array{exit_code: int, stdout: string, stderr: string}
	 */
	private function run_configure( array $answers, string $project ): array {
		$descriptors = [
			0 => [ 'pipe', 'r' ],
			1 => [ 'pipe', 'w' ],
			2 => [ 'pipe', 'w' ],
		];

		$pipes   = [];
		$process = proc_open(
			[ PHP_BINARY, 'configure.php' ],
			$descriptors,
			$pipes,
			$project,
			array_merge(
				getenv(),
				[
					'PATH' => $this->workspace . '/bin:' . getenv( 'PATH' ),
					'TERM' => 'dumb',
				],
			),
		);

		$this->assertIsResource( $process, 'Unable to start the configure script.' );

		fwrite( $pipes[0], implode( PHP_EOL, array_values( $answers ) ) . PHP_EOL );
		fclose( $pipes[0] );

		stream_set_blocking( $pipes[1], false );
		stream_set_blocking( $pipes[2], false );

		$stdout    = '';
		$stderr    = '';
		$exit_code = null;
		$deadline  = microtime( true ) + 120;

		while ( true ) {
			$stdout .= (string) stream_get_contents( $pipes[1] );
			$stderr .= (string) stream_get_contents( $pipes[2] );

			$status = proc_get_status( $process );

			if ( ! $status['running'] ) {
				$exit_code = $status['exitcode'];

				break;
			}

			if ( microtime( true ) > $deadline ) {
				proc_terminate( $process, 9 );

				break;
			}

			usleep( 20000 );
		}

		$stdout .= (string) stream_get_contents( $pipes[1] );
		$stderr .= (string) stream_get_contents( $pipes[2] );

		fclose( $pipes[1] );
		fclose( $pipes[2] );
		proc_close( $process );

		$this->assertNotNull(
			$exit_code,
			"The configure script timed out, which usually means it asked a question that wasn't answered:\n" . $stdout . $stderr,
		);

		return [
			'exit_code' => $exit_code,
			'stdout'    => $stdout,
			'stderr'    => $stderr,
		];
	}

	/**
	 * Copy the skeleton into the workspace, as though it were checked out at the
	 * wp-content level of a project.
	 *
	 * @param string                $folder           Folder to check the project out into.
	 * @param array<string, string> $plugin_overrides Plugin fixture files to replace.
	 * @return string The absolute path to the copy.
	 */
	private function install_skeleton( string $folder = 'my-cool-site', array $plugin_overrides = [] ): string {
		$project = $this->workspace . '/' . $folder . '/wp-content';

		mkdir( $project, 0777, true );

		$this->copy_directory( $this->skeleton_root(), $project );

		$this->write_files( $this->workspace . '/fixtures/create-wordpress-plugin', array_merge( $this->plugin_fixture(), $plugin_overrides ) );
		$this->write_files( $this->workspace . '/fixtures/create-wordpress-theme', $this->theme_fixture() );

		return $project;
	}

	/**
	 * The root directory of the skeleton.
	 */
	private function skeleton_root(): string {
		return dirname( __DIR__ );
	}

	/**
	 * The commands the test doubles were asked to run, one per line.
	 */
	private function command_log(): string {
		$log = $this->workspace . '/commands.log';

		return file_exists( $log ) ? (string) file_get_contents( $log ) : '';
	}

	/**
	 * Write the test doubles the configure script shells out to.
	 *
	 * They go at the front of `PATH` so that no test reaches the network. Each
	 * one appends the command it was given to the log.
	 */
	private function write_command_doubles(): void {
		$bin = $this->workspace . '/bin';
		$log = $this->workspace . '/commands.log';

		mkdir( $bin, 0777, true );

		$this->write_files(
			$bin,
			[
				'composer'         => sprintf(
					"#!/bin/sh\nexec %s %s \"\$@\"\n",
					escapeshellarg( PHP_BINARY ),
					escapeshellarg( $bin . '/composer-double.php' ),
				),
				'composer-double.php' => strtr(
					$this->composer_double(),
					[
						'{{LOG}}'      => var_export( $log, true ),
						'{{FIXTURES}}' => var_export( $this->workspace . '/fixtures', true ),
					],
				),
				'wp'               => $this->shell_double( $log, "exit 0\n" ),
				'curl'             => $this->shell_double( $log, "exit 0\n" ),
				'git'              => $this->shell_double(
					$log,
					<<<'SHELL'
case "$1 $2" in
	'config remote.origin.url') echo 'git@github.com:test-vendor/example-project.git' ;;
	'config user.email') echo 'git@example.com' ;;
	'config user.name') echo 'Git User' ;;
esac

if [ "$1" = 'clone' ] && [ -n "$3" ]; then
	mkdir -p "$3"
fi

exit 0
SHELL
				),
			],
		);

		foreach ( [ 'composer', 'wp', 'curl', 'git' ] as $command ) {
			chmod( $bin . '/' . $command, 0755 );
		}
	}

	/**
	 * A shell test double that logs the command it was given.
	 *
	 * @param string $log  Path to the log file.
	 * @param string $body Shell to run after logging.
	 */
	private function shell_double( string $log, string $body ): string {
		return sprintf(
			"#!/bin/sh\nprintf '%%s %%s\\n' \"$(basename \"$0\")\" \"$*\" >> %s\n\n%s",
			escapeshellarg( $log ),
			$body,
		);
	}

	/**
	 * The Composer test double.
	 *
	 * It applies `composer config`, scaffolds `composer create-project` from
	 * the fixtures, and creates a plugin directory for every `composer
	 * require`, the way composer/installers would. Everything else is logged
	 * and ignored.
	 */
	private function composer_double(): string {
		return <<<'DOUBLE'
<?php
/**
 * Test double for Composer, used by tests/ConfigureTest.php.
 *
 * phpcs:ignoreFile
 */

declare(strict_types=1);

const LOG      = {{LOG}};
const FIXTURES = {{FIXTURES}};

$arguments = array_slice( $argv, 1 );

file_put_contents( LOG, 'composer ' . implode( ' ', $arguments ) . PHP_EOL, FILE_APPEND );

$positional = array_values( array_filter( $arguments, fn ( string $argument ) => ! str_starts_with( $argument, '-' ) ) );

switch ( $positional[0] ?? '' ) {
	case 'create-project':
		create_project( $positional[1] ?? '', $positional[2] ?? '' );
		break;

	case 'config':
		write_config( $positional, $arguments );
		break;

	case 'require':
		install_packages( array_slice( $positional, 1 ) );
		break;
}

exit( 0 );

/**
 * Copy the fixture for a package into place.
 */
function create_project( string $package, string $target ): void {
	$source = FIXTURES . '/' . basename( $package );

	if ( '' === $target || ! is_dir( $source ) ) {
		fwrite( STDERR, "No fixture for {$package}.\n" );

		exit( 1 );
	}

	if ( ! is_dir( $target ) ) {
		mkdir( $target, 0777, true );
	}

	copy_directory( $source, $target );
}

/**
 * Apply a `composer config` call to composer.json.
 *
 * @param array<int, string> $positional Arguments that are not flags.
 * @param array<int, string> $arguments  Every argument.
 */
function write_config( array $positional, array $arguments ): void {
	$key    = $positional[1] ?? '';
	$values = array_slice( $positional, 2 );

	if ( '' === $key || [] === $values || ! file_exists( 'composer.json' ) ) {
		return;
	}

	$json = (array) json_decode( (string) file_get_contents( 'composer.json' ), true );

	if ( in_array( '--json', $arguments, true ) ) {
		$value = json_decode( $values[0], true );
	} elseif ( 1 === count( $values ) ) {
		$value = $values[0];
	} else {
		$value = [ 'type' => $values[0], 'url' => $values[1] ];
	}

	// Composer keeps its own settings under "config".
	$path = str_contains( $key, '.' ) ? explode( '.', $key ) : [ 'config', $key ];

	$target = &$json;

	foreach ( $path as $segment ) {
		if ( ! isset( $target[ $segment ] ) || ! is_array( $target[ $segment ] ) ) {
			$target[ $segment ] = [];
		}

		$target = &$target[ $segment ];
	}

	$target = $value;

	unset( $target );

	file_put_contents(
		'composer.json',
		json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL,
	);
}

/**
 * Create a plugin directory for each required package.
 *
 * @param array<int, string> $packages Packages that were required.
 */
function install_packages( array $packages ): void {
	foreach ( $packages as $package ) {
		if ( ! str_contains( $package, '/' ) ) {
			continue;
		}

		$name      = basename( $package );
		$directory = 'plugins/' . $name;

		if ( is_dir( $directory ) ) {
			continue;
		}

		mkdir( $directory, 0777, true );

		file_put_contents(
			$directory . '/' . $name . '.php',
			"<?php\n/**\n * Plugin Name: {$name}\n * Description: Installed by the Composer test double.\n */\n",
		);
	}
}

/**
 * Copy a directory recursively.
 */
function copy_directory( string $from, string $to ): void {
	foreach ( (array) scandir( $from ) as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}

		$source = $from . '/' . $entry;
		$target = $to . '/' . $entry;

		if ( is_dir( $source ) ) {
			if ( ! is_dir( $target ) ) {
				mkdir( $target, 0777, true );
			}

			copy_directory( $source, $target );

			continue;
		}

		copy( $source, $target );
	}
}
DOUBLE;
	}

	/**
	 * Find the files that still contain the given placeholder tokens.
	 *
	 * @param string             $directory Directory to search.
	 * @param array<int, string> $tokens    Tokens to search for.
	 * @return array<string, array<int, string>> Matching files, keyed by token.
	 */
	private function find_placeholders( string $directory, array $tokens ): array {
		$found = [];

		foreach ( $this->all_files( $directory ) as $file ) {
			foreach ( self::UNTOUCHED_PATHS as $untouched ) {
				if ( str_starts_with( $file, $untouched ) ) {
					continue 2;
				}
			}

			$contents = (string) file_get_contents( $directory . '/' . $file );

			foreach ( $tokens as $token ) {
				if ( str_contains( $contents, $token ) ) {
					$found[ $token ][] = $file;
				}
			}
		}

		return $found;
	}

	/**
	 * List every file in a directory, relative to it and sorted.
	 *
	 * @param string $directory Directory to list.
	 * @return array<int, string>
	 */
	private function all_files( string $directory ): array {
		$files = [];

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST,
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$files[] = substr( $file->getPathname(), strlen( $directory ) + 1 );
			}
		}

		return $this->sorted( $files );
	}

	/**
	 * Sort a list of strings, discarding the keys.
	 *
	 * @param array<int|string, string> $values Values to sort.
	 * @return array<int, string>
	 */
	private function sorted( array $values ): array {
		$values = array_values( $values );

		sort( $values );

		return $values;
	}

	/**
	 * Read a file, asserting that it exists.
	 *
	 * @param string $path File to read.
	 */
	private function read( string $path ): string {
		$this->assertFileExists( $path );

		return (string) file_get_contents( $path );
	}

	/**
	 * Read and decode a JSON file.
	 *
	 * @param string $path File to read.
	 * @return array<string, mixed>
	 */
	private function read_json( string $path ): array {
		$decoded = json_decode( $this->read( $path ), true );

		$this->assertIsArray( $decoded, "{$path} is not valid JSON." );

		return $decoded;
	}

	/**
	 * Write a set of files, creating the directories they need.
	 *
	 * @param string                $directory Directory to write into.
	 * @param array<string, string> $files     Contents, keyed by relative path.
	 */
	private function write_files( string $directory, array $files ): void {
		foreach ( $files as $path => $contents ) {
			$target = $directory . '/' . $path;
			$parent = dirname( $target );

			if ( ! is_dir( $parent ) ) {
				mkdir( $parent, 0777, true );
			}

			file_put_contents( $target, $contents );
		}
	}

	/**
	 * Copy a directory, skipping the paths that don't belong in a test copy.
	 *
	 * @param string $from Source directory.
	 * @param string $to   Target directory.
	 */
	private function copy_directory( string $from, string $to ): void {
		foreach ( (array) scandir( $from ) as $entry ) {
			if ( '.' === $entry || '..' === $entry || in_array( $entry, self::SKIPPED_PATHS, true ) ) {
				continue;
			}

			$source = $from . '/' . $entry;
			$target = $to . '/' . $entry;

			if ( is_link( $source ) ) {
				continue;
			}

			if ( is_dir( $source ) ) {
				mkdir( $target );

				$this->copy_directory( $source, $target );

				continue;
			}

			copy( $source, $target );
		}
	}

	/**
	 * Recursively delete a directory inside the system temporary directory.
	 *
	 * @param string $path Directory to delete.
	 */
	private function delete_directory( string $path ): void {
		$temporary = (string) realpath( sys_get_temp_dir() );

		if ( $path === $temporary || ! str_starts_with( $path, $temporary . '/' ) ) {
			$this->fail( "Refusing to delete {$path}: it is not inside {$temporary}." );
		}

		if ( ! is_dir( $path ) ) {
			return;
		}

		foreach ( (array) scandir( $path ) as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$child = $path . '/' . $entry;

			if ( is_dir( $child ) && ! is_link( $child ) ) {
				$this->delete_directory( $child );

				continue;
			}

			unlink( $child );
		}

		rmdir( $path );
	}

	/**
	 * A stand-in for the alleyinteractive/create-wordpress-plugin skeleton.
	 *
	 * @return array<string, string> Contents, keyed by relative path.
	 */
	private function plugin_fixture(): array {
		$files = [
			'plugin.php' => <<<'FILE'
<?php
/**
 * Plugin Name: Create WordPress Plugin
 * Plugin URI: https://github.com/alleyinteractive/create-wordpress-plugin
 * Description: A skeleton WordPress plugin
 * Version: 0.0.0
 * Author: author_name
 * Author URI: https://github.com/alleyinteractive/create-wordpress-plugin
 *
 * Text Domain: create-wordpress-plugin
 * Domain Path: /languages/
 *
 * @package create-wordpress-plugin
 */

namespace Alley\WP\Create_WordPress_Plugin;

/**
 * Root directory to this plugin.
 */
define( 'CREATE_WORDPRESS_PLUGIN_DIR', __DIR__ );

require_once __DIR__ . '/src/main.php';

main();
FILE,
			'src/main.php' => <<<'FILE'
<?php
/**
 * The main plugin function
 *
 * @package create-wordpress-plugin
 */

namespace Alley\WP\Create_WordPress_Plugin;

use Alley\WP\Features\Group;

/**
 * Instantiate the plugin.
 */
function main(): void {
	$plugin = new Group();

	$plugin->boot();
}
FILE,
			'src/class-example-plugin.php' => <<<'FILE'
<?php
/**
 * Example_Plugin class file
 *
 * @package create-wordpress-plugin
 */

namespace Alley\WP\Create_WordPress_Plugin;

/**
 * Example plugin class.
 */
class Example_Plugin {}
FILE,
			'composer.json' => <<<'FILE'
{
  "name": "alleyinteractive/create-wordpress-plugin",
  "description": "A skeleton WordPress plugin",
  "type": "wordpress-plugin",
  "license": "GPL-2.0-or-later",
  "authors": [
    {
      "name": "author_name",
      "email": "email@domain.com"
    }
  ],
  "require": {
    "php": "^8.2",
    "alleyinteractive/composer-wordpress-autoloader": "^1.0",
    "alleyinteractive/wp-type-extensions": "^3.0"
  },
  "require-dev": {
    "alleyinteractive/alley-coding-standards": "^2.0",
    "mantle-framework/testkit": "^1.4"
  },
  "autoload-dev": {
    "psr-4": {
      "Create_WordPress_Plugin\\Tests\\": "tests"
    }
  },
  "extra": {
    "wordpress-autoloader": {
      "autoload": {
        "Create_WordPress_Plugin\\": "src"
      }
    }
  }
}
FILE,
			'package.json' => <<<'FILE'
{
  "name": "create-wordpress-plugin",
  "version": "0.0.0",
  "license": "GPL-2.0-or-later",
  "engines": {
    "node": "20",
    "npm": "10"
  },
  "dependencies": {
    "classnames": "^2.5.1"
  },
  "devDependencies": {
    "@wordpress/scripts": "^30.0.0",
    "jest": "^30.0.0"
  },
  "scripts": {
    "build": "alley-build",
    "check-types": "tsc --noEmit",
    "packages-update": "alley-build packages-update",
    "postinstall": "alley-build postinstall",
    "release": "npx @alleyinteractive/create-release@latest",
    "test": "npm run jest"
  }
}
FILE,
			'README.md' => <<<'FILE'
<!--delete-->
# Create WordPress Plugin

Press the "Use template" button to start a plugin of your own.
<!--/delete-->

# create-wordpress-plugin

A skeleton WordPress plugin

## Credits

- [author_name](https://github.com/author_username)
FILE,
			'tests/bootstrap.php' => <<<'FILE'
<?php
/**
 * Create WordPress Plugin Tests: Bootstrap
 *
 * @package create-wordpress-plugin
 */

\Mantle\Testing\manager()
	->maybe_rsync_plugin()
	->loaded( fn () => require_once __DIR__ . '/../plugin.php' )
	->install();
FILE,
			'tests/ConfigureTest.php' => <<<'FILE'
<?php
/**
 * Create WordPress Plugin Tests: Configure Script
 *
 * Tests create-wordpress-plugin's own configure.php, which this project never
 * runs. It should be removed along with that script.
 *
 * @package create-wordpress-plugin
 */
FILE,
			'configure.php' => "#!/usr/bin/env php\n<?php\n// The plugin skeleton's own configure script.\n",
			'Makefile'      => "setup:\n\tphp ./configure.php\n",
		];

		// The project-level files the plugin brings with it and the project
		// removes, kept trivial: only their presence matters.
		$boilerplate = [
			'.deployignore'        => "*.md\n",
			'.editorconfig'        => "root = true\n",
			'.eslintrc.json'       => "{}\n",
			'.gitattributes'       => "* text=auto\n",
			'.github/workflows/all-pr-tests.yml' => "name: All Pull Request Tests\n",
			'.gitignore'           => "vendor\n",
			'.nvmrc'               => "22\n",
			'.phpcs/ruleset.xml'   => "<?xml version=\"1.0\"?>\n<ruleset name=\"create-wordpress-plugin\" />\n",
			'.stylelintrc.json'    => "{}\n",
			'.wp-env.json'         => "{}\n",
			'CHANGELOG.md'         => "# Changelog\n",
			'jest.config.js'       => "module.exports = {};\n",
			'package-lock.json'    => "{\n  \"name\": \"create-wordpress-plugin\"\n}\n",
			'phpstan.neon'         => "parameters:\n  level: max\n",
			'tsconfig.eslint.json' => "{}\n",
			'tsconfig.json'        => "{}\n",
			'vendor/autoload.php'  => "<?php\n// Composer autoloader.\n",
		];

		return array_merge( $files, $boilerplate );
	}

	/**
	 * The plugin's main function with the marker comment the script replaces.
	 */
	private function plugin_main_with_features_marker(): string {
		return <<<'FILE'
<?php
/**
 * The main plugin function
 *
 * @package create-wordpress-plugin
 */

namespace Alley\WP\Create_WordPress_Plugin;

use Alley\WP\Features\Group;

/**
 * Instantiate the plugin.
 */
function main(): void {
	// Add features here.

	$plugin->boot();
}
FILE;
	}

	/**
	 * A stand-in for the alleyinteractive/create-wordpress-theme skeleton.
	 *
	 * @return array<string, string> Contents, keyed by relative path.
	 */
	private function theme_fixture(): array {
		$files = [
			'style.css' => <<<'FILE'
/*
Theme Name: Create WordPress Theme
Theme URI: https://github.com/alleyinteractive/create-wordpress-theme
Author: author_name
Author URI: https://github.com/alleyinteractive
Description: A skeleton WordPress theme
Version: 0.0.0
Text Domain: create-wordpress-theme
*/
FILE,
			'functions.php' => <<<'FILE'
<?php
/**
 * Create WordPress Theme functions
 *
 * @package create-wordpress-theme
 */

namespace Alley\WP\Create_WordPress_Theme;

/**
 * Root directory to this theme.
 */
define( 'CREATE_WORDPRESS_THEME_DIR', __DIR__ );

require_once __DIR__ . '/src/main.php';
FILE,
			'src/main.php' => <<<'FILE'
<?php
/**
 * The main theme function
 *
 * @package create-wordpress-theme
 */

namespace Alley\WP\Create_WordPress_Theme;

/**
 * Instantiate the theme.
 */
function main(): void {}
FILE,
			'composer.json' => <<<'FILE'
{
  "name": "alleyinteractive/create-wordpress-theme",
  "description": "A skeleton WordPress theme",
  "type": "wordpress-theme",
  "license": "GPL-2.0-or-later",
  "authors": [
    {
      "name": "author_name",
      "email": "email@domain.com"
    }
  ],
  "require": {
    "php": "^8.2",
    "alleyinteractive/block-theme-tools": "^1.0"
  },
  "require-dev": {
    "alleyinteractive/alley-coding-standards": "^2.0"
  },
  "autoload-dev": {
    "psr-4": {
      "Create_WordPress_Theme\\Tests\\": "tests"
    }
  }
}
FILE,
			'package.json' => <<<'FILE'
{
  "name": "create-wordpress-theme",
  "version": "0.0.0",
  "license": "GPL-2.0-or-later",
  "engines": {
    "node": "22",
    "npm": "10"
  },
  "devDependencies": {
    "@wordpress/scripts": "^30.0.0",
    "classnames": "^2.3.0"
  },
  "scripts": {
    "build": "alley-build",
    "check-types": "tsc --noEmit",
    "packages-update": "alley-build packages-update",
    "postinstall": "alley-build postinstall",
    "release": "npx @alleyinteractive/create-release@latest",
    "test": "npm run jest"
  }
}
FILE,
			'README.md' => <<<'FILE'
<!--delete-->
# Create WordPress Theme

Press the "Use template" button to start a theme of your own.
<!--/delete-->

# create-wordpress-theme

A skeleton WordPress theme

## Credits

- [author_name](https://github.com/author_username)
FILE,
			'tests/bootstrap.php' => <<<'FILE'
<?php
/**
 * Create WordPress Theme Tests: Bootstrap
 *
 * @package create-wordpress-theme
 */

\Mantle\Testing\manager()->install();
FILE,
			'tests/ConfigureTest.php' => <<<'FILE'
<?php
/**
 * Create WordPress Theme Tests: Configure Script
 *
 * Tests create-wordpress-theme's own configure.php, which this project never
 * runs. It should be removed along with that script.
 *
 * @package create-wordpress-theme
 */
FILE,
			'configure.php' => "#!/usr/bin/env php\n<?php\n// The theme skeleton's own configure script.\n",
			'Makefile'      => "setup:\n\tphp ./configure.php\n",
		];

		$boilerplate = [
			'.editorconfig'        => "root = true\n",
			'.eslintrc.json'       => "{}\n",
			'.github/workflows/all-pr-tests.yml' => "name: All Pull Request Tests\n",
			'.gitignore'           => "vendor\n",
			'.nvmrc'               => "22\n",
			'.stylelintrc.json'    => "{}\n",
			'CHANGELOG.md'         => "# Changelog\n",
			'jest.config.js'       => "module.exports = {};\n",
			'package-lock.json'    => "{\n  \"name\": \"create-wordpress-theme\"\n}\n",
			'phpstan.neon'         => "parameters:\n  level: max\n",
			'tsconfig.eslint.json' => "{}\n",
			'tsconfig.json'        => "{}\n",
			'vendor/autoload.php'  => "<?php\n// Composer autoloader.\n",
		];

		return array_merge( $files, $boilerplate );
	}
}
