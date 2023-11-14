#!/usr/bin/env php
<?php
/**
 * Configure the alleyinteractive/create-wordpress-project interactively.
 *
 * This script will:
 *
 *   1. Prompt the user to replace the default values in the project.
 *   2. Configure it for the project's hosting provider (VIP, Pantheon, etc.)
 *   3. Scaffold default plugin for the project from alleyinteractive/create-wordpress-plugin.
 *
 * Supports arguments to set the values directly.
 *
 * [--project_name=<project_name>]
 * : The project name.
 *
 * [--author_name=<author_name>]
 * : The author name.
 *
 * [--author_email=<author_email>]
 * : The author email.
 *
 * phpcs:disable
 */

namespace Create_WordPress_Project\Configure;

if ( ! defined( 'STDIN' ) ) {
	die( 'Not in CLI mode.' );
}

if ( 0 === strpos( strtoupper( PHP_OS ), 'WIN' ) ) {
	die( 'Not supported in Windows. 🪟' );
}

if ( version_compare( PHP_VERSION, '8.2.0', '<' ) ) {
	die( 'PHP 8.2.0 or greater is required.' );
}

// Parse the command line arguments from $argv.
$args         = [];
$previous_key = null;

foreach ( $argv as $value ) {
	if ( str_starts_with( $value, '--' ) ) {
		if ( false !== strpos( $value, '=' ) ) {
			[ $arg, $value ] = explode( '=', substr( $value, 2 ), 2 );

			$args[ $arg ] = trim( $value );

			$previous_key = null;
		} else {
			$args[ substr( $value, 2 ) ] = true;

			$previous_key = substr( $value, 2 );
		}
	} elseif ( ! empty( $previous_key ) ) {
		$args[ $previous_key ] = trim( $value );
	} else {
		$previous_key = trim( $value );
	}
}

$terminal_width = (int) exec( 'tput cols' );

function write( string $text ): void {
	global $terminal_width;
	echo wordwrap( $text, $terminal_width - 1 ) . PHP_EOL;
}

function ask( string $question, string $default = '', bool $allow_empty = true ): string {
	while ( true ) {
		write( $question . ( $default ? " [{$default}]" : '' ) . ': ' );
		$answer = readline( '> ' );

		$value = $answer ?: $default;

		if ( ! $allow_empty && empty( $value ) ) {
			echo "This value can't be empty." . PHP_EOL;
			continue;
		}

		return $value;
	}
}

function confirm( string $question, bool $default = false ): bool {
	write( "{$question} (yes/no) [" . ( $default ? 'yes' : 'no' ) . ']: ' );

	$answer = readline( '> ' );

	if ( ! $answer ) {
		return $default;
	}

	return in_array( strtolower( trim( $answer ) ), [ 'y', 'yes', 'true', '1' ], true );
}

function run( string $command, string $dir = null ): string {
	$command = $dir ? "cd {$dir} && {$command}" : $command;

	return trim( (string) shell_exec( $command ) );
}

function str_after( string $subject, string $search ): string {
	$pos = strrpos( $subject, $search );

	if ( $pos === false ) {
		return $subject;
	}

	return substr( $subject, $pos + strlen( $search ) );
}

function slugify( string $subject ): string {
	return strtolower( trim( (string) preg_replace( '/[^A-Za-z0-9-]+/', '-', $subject ), '-' ) );
}

function title_case( string $subject ): string {
	return ensure_capitalp( str_replace( ' ', '_', ucwords( str_replace( [ '-', '_' ], ' ', $subject ) ) ) );
}

function ensure_capitalp( string $text ): string {
	return str_replace( 'Wordpress', 'WordPress', $text );
}

/**
 * @param string $file
 * @param array<string, string> $replacements
 */
function replace_in_file( string $file, array $replacements ): void {
	$contents = file_get_contents( $file );

	if ( empty( $contents ) ) {
		return;
	}

	file_put_contents(
		$file,
		str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$contents,
		)
	);
}

function remove_readme_paragraphs( string $file ): void {
	$contents = file_get_contents( $file );

	if ( empty( $contents ) ) {
		return;
	}

	file_put_contents(
		$file,
		trim( (string) preg_replace( '/<!--delete-->.*<!--\/delete-->/s', '', $contents ) ?: $contents ),
	);
}

function determine_separator( string $path ): string {
	return str_replace( '/', DIRECTORY_SEPARATOR, $path );
}

/**
 * @return array<int, string>
 */
function list_all_files_for_replacement(): array {
	return explode( PHP_EOL, run( 'grep -R -l .  --exclude LICENSE --exclude configure.php --exclude .phpunit.result.cache --exclude-dir .phpcs --exclude composer.lock --exclude-dir .git --exclude-dir .github --exclude-dir vendor --exclude-dir node_modules --exclude-dir webpack --exclude-dir modules --exclude-dir .phpcs' ) );
}

/**
 * @param string|array<int, string> $paths
 */
function delete_files( string|array $paths ): void {
	if ( ! is_array( $paths ) ) {
		$paths = [ $paths ];
	}

	foreach ( $paths as $path ) {
		$path = determine_separator( $path );

		if ( is_dir( $path ) ) {
			run( "rm -rf {$path}" );
		} elseif ( file_exists( $path ) ) {
			@unlink( $path );
		}
	}
}

echo "\nWelcome friend to alleyinteractive/create-wordpress-project! 😀\nLet's setup your WordPress Project 🚀\n\n";

$current_dir = getcwd();

if ( ! $current_dir ) {
	echo "Could not determine current directory.\n";
	exit( 1 );
}

// Determine the folder name from the parent directory
// (this project is assumed to be at the wp-content level).
$folder_name = ensure_capitalp( basename( dirname( $current_dir, 1) ) );

while ( true ) {
	$project_name = ask(
		question: 'Project name?',
		default: (string) ( $args['project_name'] ?? str_replace( '_', ' ', title_case( $folder_name ) ) ),
		allow_empty: false,
	);

	if ( false !== strpos( $project_name, '-' ) ) {
		write( 'This should be a project name and not a slug. For example, "Example Project" would be a great project name. After this step, we\'ll prompt you for the project slug.' );

		if ( ! confirm( 'Do you wish to continue anyway?', false ) ) {
			continue;
		}
	}

	break;
}

$project_name_slug = slugify( ask(
	question: 'Project slug?',
	default: slugify( $project_name ),
	allow_empty: false,
) );

$description = ask( 'Project description?', "{$project_name} Website" );

$username_guess  = explode( ':', run( 'git config remote.origin.url' ) )[1] ?? '';
$username_guess  = dirname( $username_guess );
$username_guess  = basename( $username_guess );

$vendor_name = ask(
	question: 'Vendor/organization name (usually the Github Organization)?',
	default: $username_guess,
	allow_empty: false,
);

$vendor_slug = slugify( $vendor_name );

$author_email = ask(
	question: 'Author email?',
	default: (string) ( $args['author_email'] ?? run( 'git config user.email' ) ),
	allow_empty: false,
);

$author_username = ask(
	question: 'Author username?',
	default: $username_guess,
	allow_empty: false,
);

$author_name = ask(
	question: 'Author name?',
	default: (string) ( $args['author_name'] ?? run( 'git config user.name' ) ),
	allow_empty: false,
);

$plugin_slug = slugify(
	ask(
		question: 'Project plugin name?',
		default: $project_name_slug,
		allow_empty: false,
	),
);

if ( is_dir( "plugins/{$plugin_slug}" ) ) {
	write( "Plugin already exists in plugins/{$plugin_slug}." );
	exit( 1 );
}

$plugin_namespace = title_case( $plugin_slug ) . '_Plugin';

// TODO: Prompt for theme name when create-wordpress-theme is ready.
// $theme_slug = slugify(
// 	ask(
// 		question: 'Project theme name?',
// 		default: $project_name_slug,
// 		allow_empty: false,
// 	),
// );

// if ( is_dir( "themes/{$theme_slug}" ) ) {
// 	write( "Theme already exists in themes/{$theme_slug}." );
// 	exit( 1 );
// }

// $theme_namespace = title_case( $theme_slug ) . '_Theme';

write( '------' );
write( "Project          : {$project_name} <{$project_name_slug}>" );
write( "Description      : {$description}" );
write( "Author           : {$author_name} ({$author_email})" );
write( "Vendor           : {$vendor_name} ({$vendor_slug})" );

if ( ! empty( $plugin_slug ) ) {
	write( "Plugin           : plugins/{$plugin_slug}" );
	write( "Plugin Namespace : {$plugin_namespace}" );
}


if ( ! empty( $theme_slug ) ) {
	write( "Theme            : themes/{$theme_slug}" );
	write( "Theme Namespace  : {$theme_namespace}" );
}

write( '------' );

write( 'This script will replace the above values in all relevant files in the project directory.' );

if ( ! confirm( 'Modify files?', true ) ) {
	exit( 1 );
}

$search_and_replace = [
	'author_name'                  => $author_name,
	'Alley'                        => $author_name,
	'author_username'              => $author_username,
	'email@domain.com'             => $author_email,
	'info@alley.com'               => $author_email,

	'A skeleton WordPress project' => $description,

	'create-wordpress-project'     => $project_name_slug,
	'Create WordPress Project'     => $project_name,
	'CREATE_WORDPRESS_PROJECT'     => strtoupper( str_replace( '-', '_', $project_name_slug ) ),

	'vendor_name'                  => $vendor_name,
	'alleyinteractive'             => $vendor_slug,
];

if ( ! empty( $theme_slug ) ) {
	$search_and_replace = array_merge(
		$search_and_replace,
		[
			'create-wordpress-theme'       => $theme_slug,
			'Create WordPress Theme'       => str_replace( '_', ' ', title_case( $theme_slug ) ),
			'CREATE_WORDPRESS_THEME'       => strtoupper( str_replace( '-', '_', $theme_slug ) ),
			'create_wordpress_theme'       => str_replace( '-', '_', $theme_slug ),
			'Create_WordPress_Theme'       => $theme_namespace,
		],
	);
}

if ( ! empty( $plugin_slug ) ) {
	$search_and_replace = array_merge(
		$search_and_replace,
		[
			'create-wordpress-plugin'      => $plugin_slug,
			'Create WordPress Plugin'      => str_replace( '_', ' ', title_case( $plugin_slug ) ),
			'CREATE_WORDPRESS_PLUGIN'      => strtoupper( str_replace( '-', '_', $plugin_slug ) ),
			'create_wordpress_plugin'      => str_replace( '-', '_', $plugin_slug ),
			'Create_WordPress_Plugin'      => $plugin_namespace,
		]
	);
}

run(
	'composer config extra.wordpress-autoloader.autoload --json \'' . json_encode( [
		$plugin_namespace => "plugins/{$plugin_slug}/src",
		// $theme_namespace  => "themes/{$theme_slug}/src",
	] ) . '\'',
);

if ( ! empty( $plugin_slug ) ) {
	write( "Scaffolding create-wordpress-plugin to plugins/{$plugin_slug}..." );

	run(
		"composer create-project alleyinteractive/create-wordpress-plugin plugins/{$plugin_slug}",
		$current_dir,
	);

	echo "Done!\n\n";
}

// TODO: scaffold the theme from create-wordpress-theme.
// if ( ! empty( $theme_slug ) ) {
// 	write( "Scaffolding create-wordpress-theme to themes/{$theme_slug}..." );

// 	run(
// 		"composer create-project alleyinteractive/create-wordpress-theme themes/{$theme_slug}",
// 		$current_dir,
// 	);
// }

foreach ( list_all_files_for_replacement() as $path ) {
	echo "Updating $path...\n";

	replace_in_file( $path, $search_and_replace );

	if ( str_contains( $path, 'README.md' ) ) {
		remove_readme_paragraphs( $path );
	}
}

echo "Done!\n\n";

write( 'Running composer update...' );

run( 'composer update' );

// Move the ci-templates to the root of the project.
if ( is_dir( 'ci-templates' ) ) {
	write( "Moving ci-templates files to the root of the project and removing ci-templates directory..." );

	run( 'rsync -a ci-templates/ ./ && rm -rf ci-templates' );
}

// Remove admins from the CODEOWNERS file (should only apply to the template).
write( 'Removing alleyinteractive/admins from .github/CODEOWNERS...' );
replace_in_file(
	'.github/CODEOWNERS',
	[
		'*	@alleyinteractive/admins' => '',
	]
);

write( 'Removing configuration script from theme/plugin...' );

delete_files(
	[
		// TODO: Uncomment when create-wordpress-theme is ready.
		// "themes/{$theme_slug}/configure.php",
		// "themes/{$theme_slug}/Makefile",
		"plugins/{$plugin_slug}/configure.php",
		"plugins/{$plugin_slug}/Makefile",
	]
);

echo "Done!\n\n";

if ( confirm( 'Will this project be using Buddy CI?', true ) ) {
	echo "Deleting GitHub Actions workflows...\n";

	delete_files( '.github/workflows' );

	echo "Done!\n\n";
} elseif ( confirm( 'Will this project be using GitHub Actions?', true ) ) {
	echo "Deleting Buddy CI files...\n";

	delete_files(
		[
			'.buddy',
			'buddy.yml',
		]
	);

	echo "Done!\n\n";
} else {
	write( 'Leaving GitHub Action workflows and Buddy CI files in place.' );
}

$hosting_provider = null;

// Determine the hosting provider we'll be using.
if ( confirm( 'Will this project be hosted on WordPress VIP?' ) ) {
	$hosting_provider = 'vip';
} elseif ( confirm( 'Will this project be hosted on Pantheon?', true ) ) {
	$hosting_provider = 'pantheon';
}

// Prompt the user to convert the folder structure to WordPress VIP.
if ( 'vip' === $hosting_provider && confirm( 'Would you like to setup the project for WordPress VIP?', true ) ) {
	if ( confirm( 'Delete the Pantheon-specific GitHub Action workflows?', true ) ) {
		delete_files(
			[
				'.github/workflows/deploy-to-pantheon-live.yml',
				'.github/workflows/deploy-to-pantheon-multidev.yml',
			]
		);
	}

	write( 'Moving mu-plugins to client-mu-plugins...' );

	run( 'mv mu-plugins client-mu-plugins' );

	write( 'Ignoring mu-plugins with .gitignore/.deployignore...' );

	file_put_contents( '.gitignore', "mu-plugins\n", FILE_APPEND );
	file_put_contents( '.deployignore', "mu-plugins\n", FILE_APPEND );

	write( 'Removing pantheon-systems/pantheon-mu-plugin from project\'s composer.json...' );

	run( 'composer remove pantheon-systems/pantheon-mu-plugin' );
	delete_files( 'client-mu-plugins/pantheon-mu-plugin' );

	// Remove the pantheon mu-plugin from the plugin loader file.
	replace_in_file(
		'client-mu-plugins/plugin-loader.php',
		[
			"\n// Load Pantheon's mu-plugin.\nrequire_once __DIR__ . '/pantheon-mu-plugin/pantheon.php';\n" => '',
		],
	);

	write( 'Running composer update...' );

	run( 'composer update' );

	write( 'Cloning Automattic/vip-go-mu-plugins-built to mu-plugins...' );

	run( 'git clone git@github.com:Automattic/vip-go-mu-plugins-built.git mu-plugins' );

	echo "Done!\n\n";
} elseif ( 'pantheon' === $hosting_provider ) {
	if ( confirm( 'Delete the VIP-specific GitHub Action workflows?', true ) ) {
		delete_files( '.github/workflows/deploy-to-vip.yml' );

		echo "Done!\n\n";
	}
}

if ( confirm( 'Let this script delete itself?', true ) ) {
	delete_files(
		[
			'Makefile',
			__FILE__,
		]
	);
}

echo "\n\nWe're done! 🎉\n\n";

die( 0 );
