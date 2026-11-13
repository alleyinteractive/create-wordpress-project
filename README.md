<!--delete-->
# Create WordPress Project

This is a skeleton WordPress project that can be used to quickly scaffold
WordPress projects that work with Alley's
[create-wordpress-plugin](https://github.com/alleyinteractive/create-wordpress-plugin)
and
[create-wordpress-theme](https://github.com/alleyinteractive/create-wordpress-theme)
starter kits.

This template is rooted at the `wp-content` and supports both Pantheon and
WordPress VIP hosting environments with deployment workflows for each. It is
powered by [Composer](https://getcomposer.org/) to manage dependencies and
[Turborepo](https://turbo.build/repo) to build front-end assets in your project. The template includes both GitHub Actions and Buddy workflows for continuous integration and deployment.

Also included is a configuration script to easily replace all placeholders throughout the project and scaffold out your plugin/theme.

## Getting Started

Follow these steps to get started:

1. Press the "Use template" button at the top of this repo to create a new repo
   with the contents of this skeleton.
2. Run `make` (or `php ./configure.php`) to run the configuration script that
   will replace all placeholders throughout all the files.
3. Have fun creating your WordPress site! 🎊
## Dependencies

### npm Security

This project enforces a minimum release age of 7 days for npm dependencies via `.npmrc` to guard against supply chain attacks. Most attacks are identified and resolved within this window.

If you urgently need a package newer than 7 days, you can temporarily override this with:

```bash
npm install --min-release-age=0
```
<!--/delete-->

# create-wordpress-project

A WordPress project rooted at `wp-content`. Custom functionality lives in one
project plugin under `plugins/`, and presentation lives in one theme under
`themes/`.

## Setup

Requires PHP 8.4, Node 24, npm 11, and [Composer](https://getcomposer.org/).
`.nvmrc` pins the Node version.

```bash
composer install
npm install
npm run build
```

Composer installs into `mu-plugins/vendor`. Use `npm start` to watch and
rebuild frontend assets while you work, or `npm run start:hot` for hot reload.

## Testing

```bash
composer test   # PHPCS, PHPStan, and PHPUnit
npm test        # type check, ESLint, Stylelint, and Jest
```

Fix what the linters can with `composer phpcbf`, `npm run eslint:fix`, and
`npm run stylelint:fix`.

## Working in this repo

- [`CLAUDE.md`](CLAUDE.md) holds the rules for developers and agents. Read it
  before your first change.
- [`docs/README.md`](docs/README.md) explains what the project starts with and
  where the standards live.
- Pull requests use the template in `.github/PULL_REQUEST_TEMPLATE.md`.
  Deployment and test workflows live in `.github/workflows/`.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.com/careers/).

- [author_name](https://github.com/author_username)
- [Alley Interactive](https://github.com/alleyinteractive)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
