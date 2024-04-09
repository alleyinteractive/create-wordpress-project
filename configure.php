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
	echo "This script may not work in Windows. 🪟\n";
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

if ( ! $terminal_width ) {
	$terminal_width = 80;
}

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
	return trim( (string) preg_replace( '/[^a-z0-9-]+/', '-', strtolower( $subject ) ), '-' );
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
		trim( (string) preg_replace( '/<!--delete-->.*?<!--\/delete-->/s', '', $contents ) ?: $contents ),
	);
}

function normalize_path_separator( string $path ): string {
	return str_replace( '/', DIRECTORY_SEPARATOR, $path );
}

/**
 * @return array<int, string>
 */
function list_all_files_for_replacement(): array {
	$exclude = [
		'LICENSE',
		'configure.php',
		'.phpunit.result.cache',
		'.phpcs',
		'composer.lock',
	];

	$exclude_dirs = [
		'.git',
		'pantheon-mu-plugin',
		'vendor',
		'node_modules',
		'.phpcs',
	];

	$exclude = array_map(
		fn ( string $file ) => "--exclude {$file}",
		$exclude,
	);

	$exclude_dirs = array_map(
		fn ( string $dir ) => "--exclude-dir {$dir}",
		$exclude_dirs,
	);

	return explode(
		PHP_EOL,
		run(
			"grep -R -l . " . implode( ' ', $exclude_dirs ) . ' ' . implode( ' ', $exclude ),
		),
	);
}

/**
 * Gets the subfolders of a given path.
 *
 * @param string $path
 * @return array<string>
 */
function list_subfolders( $path ): array {
	$path = escapeshellarg($path);
	return explode(
		PHP_EOL,
		run(
			"find " . $path . " -type d -maxdepth 1 -mindepth 1",
		),
	);
}

/**
 * @param string|array<int, string> $paths
 */
function delete_files( string|array $paths ): void {
	if ( ! is_array( $paths ) ) {
		$paths = [ $paths ];
	}

	foreach ( $paths as $path ) {
		$path = normalize_path_separator( $path );

		if ( is_dir( $path ) ) {
			run( "rm -rf {$path}" );
		} elseif ( file_exists( $path ) ) {
			@unlink( $path );
		}
	}
}

/**
 * Install a plugin with composer
 *
 * @param array $plugin_data The plugin information.
 * @param bool $prompt Whether or not to prompt to install.
 * @param array $installed_plugins The list of installed plugins.
 */
function install_plugin( array $plugin_data, bool $prompt, &$installed_plugins ): void {
	$plugin_name   = $plugin_data['name'];
	$plugin_path   = $plugin_data['path'];
	$plugin_repo   = isset( $plugin_data['repo'] ) ? $plugin_data['repo'] : null;
	$repo_type     = isset( $plugin_data['repo_type'] ) ? $plugin_data['repo_type'] : 'github';
	$auto_activate = isset( $plugin_data['activate'] ) ? $plugin_data['activate'] : true;

	if ( $prompt && ! confirm( "Install {$plugin_name}?", true ) ) {
		return;
	}

	write( "Installing {$plugin_name}..." );
	$plugin_short_name = str_after( $plugin_path, '/' );

	if ( ! empty( $plugin_repo ) ) {
		run( "composer config repositories.{$plugin_short_name} {$repo_type} {$plugin_repo}" );
	}

	run( "composer require -W --no-interaction --quiet {$plugin_path} --ignore-platform-req=ext-redis" );
	if ( $auto_activate ) {
		array_push( $installed_plugins, $plugin_short_name );
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

$theme_slug = slugify(
	ask(
		question: 'Project theme name?',
		default: $project_name_slug,
		allow_empty: false,
	),
);

if ( is_dir( "themes/{$theme_slug}" ) ) {
	write( "Theme already exists in themes/{$theme_slug}." );
	exit( 1 );
}

$theme_namespace = title_case( $theme_slug ) . '_Theme';

$slack_channel_id = ask(
	question: 'Slack Channel ID? (for deploy notifications)',
	allow_empty: true,
);

$slack_channel_name = ask(
	question: 'Slack Channel Name? (for deploy notifications)',
	allow_empty: true,
);

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

if ( ! empty( $slack_channel_id ) ) {
	write( "Slack Channel ID : {$slack_channel_id}" );
}
if ( ! empty( $slack_channel_name ) ) {
	write( "Slack Channel Name : {$slack_channel_name}" );
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

if ( ! empty( $slack_channel_id ) ) {
	$search_and_replace = array_merge(
		$search_and_replace,
		[
			'slack_channel_id' => $slack_channel_id,
		]
	);
}

if ( ! empty( $slack_channel_name ) ) {
	$search_and_replace = array_merge(
		$search_and_replace,
		[
			'slack_channel_name' => $slack_channel_name,
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

	run( "mv plugins/{$plugin_slug}/plugin.php plugins/{$plugin_slug}/{$plugin_slug}.php" );

	// Move the contents of each subfolder in plugin-templates to the plugin folder.
	$templates = list_subfolders( 'plugin-templates' ) ?: [];
	foreach( $templates as $template ) {
		$folder = explode( '/', $template )[1];
		run( "mkdir -p plugins/{$plugin_slug}/{$folder}/" );
		run( "cp -R {$template}/* plugins/{$plugin_slug}/{$folder}/" );
	}

	// Copy the initial features from features.txt into the plugin main file.
	$features = file_get_contents( 'plugin-templates/features.txt' );
	replace_in_file( "plugins/{$plugin_slug}/{$plugin_slug}.php", [ '		// Add initial features here.' => $features ] );

	echo "Done!\n\n";
}

if ( ! empty( $theme_slug ) ) {
	write( "Scaffolding create-wordpress-theme to themes/{$theme_slug}..." );

	run(
		"composer create-project alleyinteractive/create-wordpress-theme themes/{$theme_slug}",
		$current_dir,
	);
	run(
		"wp theme activate {$theme_slug}",
		$current_dir,
	);
}

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

	run( 'rm -rf .github && rsync -a ci-templates/ ./ && rm -rf ci-templates' );
}

write( 'Removing configuration script from theme/plugin...' );

delete_files(
	[
		"themes/{$theme_slug}/configure.php",
		"themes/{$theme_slug}/Makefile",
		"plugins/{$plugin_slug}/configure.php",
		"plugins/{$plugin_slug}/Makefile",
		"plugin-templates",
	]
);

echo "Done!\n\n";

if ( confirm( 'Will this project be using GitHub Actions?', true ) ) {
	echo "Deleting Buddy CI files...\n";

	delete_files(
		[
			'.buddy',
			'buddy.yml',
		]
	);

	echo "Done!\n\n";
} elseif ( confirm( 'Will this project be using Buddy CI?', true ) ) {
	echo "Deleting GitHub Actions workflows...\n";

	delete_files( '.github/workflows' );

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
if ( 'vip' === $hosting_provider ) {
	$vip_repo_name = ask(
		question: 'VIP Repository Name?',
		default: $project_name_slug,
		allow_empty: false,
	);

	replace_in_file(
		'.buddy/push-to-vip.yml',
		[
			'vip-repo-name' => $vip_repo_name,
		],
	);

	write( 'Deleting Pantheon-specific GitHub Action workflows...' );

	delete_files(
		[
			'.github/workflows/deploy-to-pantheon-live.yml',
			'.github/workflows/deploy-to-pantheon-multidev.yml',
		]
	);

	write( 'Moving mu-plugins to client-mu-plugins...' );

	run( 'mv mu-plugins client-mu-plugins' );

	write( 'Ignoring mu-plugins with .gitignore/.deployignore...' );

	file_put_contents( '.gitignore', "mu-plugins\n", FILE_APPEND );
	file_put_contents( '.deployignore', "mu-plugins\n", FILE_APPEND );

	write( 'Removing pantheon-systems/pantheon-mu-plugin from project\'s composer.json...' );

	run( 'composer remove pantheon-systems/pantheon-mu-plugin' );
	delete_files( 'client-mu-plugins/pantheon-mu-plugin' );

	write( 'Adding VIP composer configuration...' );
	run( 'composer config vendor-dir client-mu-plugins/vendor' );

	replace_in_file(
		'client-mu-plugins/001-composer.php',
		[
			'/vendor/autoload.php' => '/client-mu-plugins/vendor/autoload.php',
		],
	);

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

	if ( ! is_file( __DIR__ . '/object-cache.php' ) ) {
		write( 'Symlinking object-cache.php from mu-plugins to wp-content/object-cache.php...' );

		run( 'ln -s mu-plugins/drop-ins/object-cache.php object-cache.php' );
	}

	write( 'Scaffolding out vip-config...' );

	run( 'mkdir -p vip-config && touch vip-config/.gitkeep' );

	write( 'Scaffolding out VIP directories...' );

	run( 'mkdir -p images && touch images/.gitkeep' );
	run( 'mkdir -p languages && touch languages/.gitkeep' );
	run( 'mkdir -p private && touch private/.gitkeep' );

	echo "Done!\n\n";
} elseif ( 'pantheon' === $hosting_provider ) {
	write( 'Deleting VIP-specific GitHub Action workflows...' );

	delete_files(
		[
			'.github/workflows/deploy-to-vip.yml',
			'.circleci',
		]
	);

	echo "Done!\n\n";
}

// Track the installed plugins, so we can activate them.
$installed_plugins = [];

write( 'Installing Required Plugins...' );
$required_file_contents = file_get_contents( 'composer-templates/default.json' );
$required_plugins       = json_decode( $required_file_contents, true );
foreach( $required_plugins as $plugin ) {
	install_plugin( $plugin, false, $installed_plugins );
}

write( 'Installing Suggested Plugins...' );
$suggested_file_contents = file_get_contents( 'composer-templates/suggested.json' );
$suggested_plugins       = json_decode( $suggested_file_contents, true );
foreach( $suggested_plugins as $plugin ) {
	install_plugin( $plugin, true, $installed_plugins );
}

if ( 'pantheon' === $hosting_provider ) {
	write( 'Installing Pantheon Plugins...' );
	$license_key = ask(
		question: 'Object Cache Pro License Key? Run \'terminus remote:wp "<site>.<env>" -- eval "echo getenv(\'OCP_LICENSE\');"\' to get the license key. (Leave blank to skip)',
		allow_empty: true,
	);

	$pantheon_file_contents = file_get_contents( 'composer-templates/pantheon.json' );
	$pantheon_plugins       = json_decode( $pantheon_file_contents, true );
	foreach( $pantheon_plugins as $plugin ) {
		install_plugin( $plugin, false, $installed_plugins );
	}
	if ( ! empty( $license_key ) ) {
		run( "mv composer-templates/auth.json ./auth.json" );
		replace_in_file( 'auth.json', [ 'object_cache_pro_token' => $license_key ] );

		install_plugin(
			[
				'name'      => 'Object Cache Pro',
				'repo'      => 'https://objectcache.pro/repo/',
				'repo_type' => 'composer',
				'path'      => 'rhubarbgroup/object-cache-pro'
			],
			false,
			$installed_plugins
		);
	}
}

// Automatically activate the installed plugins.
$plugin_files = array_filter(
	array_map( function( $plugin_dir ) {
		$file_names = [
			$plugin_dir.'php',
			strtolower($plugin_dir).'.php',
			'plugin.php',
			'index.php',
		];
		// Include some one-off exceptions.
		if ( strpos( $plugin_dir, 'wp-' ) === 0) {
			$file_names[] = substr( $plugin_dir, 3 ).'.php';
			$file_names[] = 'wordpress-'.substr( $plugin_dir, 3 ).'.php';
		} elseif ( strpos( $plugin_dir, 'wordpress-' ) === 0) {
			$file_names[] = substr( $plugin_dir, 10 ).'.php';
			$file_names[] = 'wp-'.substr( $plugin_dir, 10 ).'.php';
		}

		foreach ( $file_names as $file ) {
			// Check if the file exists and is not (virtually) empty.
			if ( file_exists( "plugins/{$plugin_dir}/{$file}" ) && 50 < filesize( "plugins/{$plugin_dir}/{$file}" ) ) {
				return "'{$plugin_dir}/{$file}',";
			}
		}
		return null;
	},
	$installed_plugins )
);
sort( $plugin_files );
$plugin_files[] = "'{$plugin_slug}/{$plugin_slug}.php',";

replace_in_file(
	'vip' === $hosting_provider ? 'client-mu-plugins/plugin-loader.php' : 'mu-plugins/plugin-loader.php',
	[
		"'{$plugin_slug}/{$plugin_slug}.php'," => implode( "\n\t\t", $plugin_files ),
	]
);

// Delete the composer-templates directory.
delete_files(
	[
		"composer-templates",
	]
);

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
