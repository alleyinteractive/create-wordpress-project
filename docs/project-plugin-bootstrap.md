# The project plugin's `main()`

The project plugin boots from one function, `main()`, in `src/main.php`.
`main()` does two things. It defines a set of shared bootstrap variables. It
composes every active feature into one nested `Feature` tree and calls
`boot()` on it. This doc explains how `main()` is structured and why some of
it is unused at the start on purpose.

> [ADR-0002](./adr/0002-declarative-plugin-loading.md) explains why plugins
> load from inside the tree. [ADR-0001](./adr/0001-opinionated-default-features.md)
> explains why unused code ships on purpose.

## Where it lives

- `src/main.php` — the `main()` function. The plugin's bootstrap file calls
  it.
- `src/features/` — the feature classes that `main()` instantiates. See
  [default-features.md](./default-features.md).
- The `Feature`, `Group`, `Ordered`, `Effect`, `Quick_Feature`, and
  `WP_CLI_Feature` decorators come from `alleyinteractive/wp-type-extensions`.

## How it works

### The bootstrap variables

`main()` derives a set of shared values once, at the top of the function. Any
feature wired below receives the same instance, instead of deriving its own:

```php
$environment_type    = wp_get_environment_type();
$request             = Request::createFromGlobals();
$request_uri         = /* guarded new Uri( $request->getUri() ), falls back to "/" */;
$home_uri            = new Uri( home_url() );
$main_query          = new Global_Post_Query( 'wp_the_query', new WP_Query() );
$block_type_registry = WP_Block_Type_Registry::get_instance();
$path_dispatch       = Path_Dispatch::instance();
$clock               = new NativeClock();
$site_settings       = get_option( '…_site_settings', [] );
```

The code uses fully-qualified class names, imported through `use` at the top
of the file, instead of helper wrappers. This makes the dependency of each
value explicit and searchable. The packages behind them are runtime
requires: `symfony/http-foundation`, `nyholm/psr7`, `symfony/clock`,
`wp-path-dispatch`, and the `Global_Post_Query` class from
`wp-type-extensions`.

Not all of these variables are consumed yet. This is intentional. See
[ADR-0001](./adr/0001-opinionated-default-features.md). The common values
are ready so a developer can use the shared instance instead of building a
new one. Current status:

| Variable | Status | What consumes it / when to reach for it |
|---|---|---|
| `$environment_type` | consumed | gates the local-only `create-block-theme` load and the `Load_Entries` cache flag |
| `$block_type_registry` | consumed | passed to `Allowed_Block_Types` |
| `$clock` | consumed | passed to `Term_Dates` |
| `$request` / `$request_uri` | inert | request- or routing-aware features that need the current URL |
| `$home_uri` | inert | features that build URLs relative to the site home |
| `$main_query` | inert | query-context features (e.g. the unwired `Similarity_Queries`) |
| `$path_dispatch` | inert | features that register custom front-end endpoints |
| `$site_settings` | inert | reading values saved by the settings page |

### The feature tree

After the variables, `main()` builds one expression. Decorators wrap the
features to control grouping and order. `main()` assigns the expression to
`$plugin` and calls `$plugin->boot()`. The decorators are:

- **`Group`** — boots a set of features together. Order within the group
  does not matter.
- **`Ordered`** — takes `first:` and `then:`. Boots `first` before `then`.
  Use it when a feature depends on something the prior step set up. The
  most common case: load a plugin, then run the feature that integrates
  with it.
- **`Effect`** — takes `when:` and `then:`. Boots `then` only if the
  `when:` predicate is true. Use it for environment gating, for example
  loading `create-block-theme` only in `local`.
- **`Quick_Feature`** — wraps a bare closure as a one-off feature.
- **`WP_CLI_Feature`** — boots a feature only in a WP-CLI context.

`boot()` walks the tree depth-first. Each leaf feature's own `boot()` runs
its `add_action` and `add_filter` registrations. There is no separate
registry. The tree is the manifest of what the plugin does.

### Plugin loading lives inside the tree

`Plugin_Loader` features inside this tree load dependency plugins, such as
Fieldmanager, Yoast, byline-manager, Elasticsearch, and MSM Sitemap.
wp-admin does not load them. This lets a plugin sit next to its integration
feature through `Ordered`, for example loading Elasticsearch and then
`Search_Customizations`. It also lets loading depend on environment through
`Effect`. [ADR-0002](./adr/0002-declarative-plugin-loading.md) gives the
full rationale and trade-offs. This loading is separate from the mu-plugin
loader that loads the project plugin itself.

## A note on the unused code

The inert variables above, and the library feature classes in
`src/features/`, are present on purpose. Do not remove them as part of an
unrelated cleanup or a "remove dead code" pass. They are ready to use.
Removing them defeats the reason they ship. See
[ADR-0001](./adr/0001-opinionated-default-features.md).

You may still remove one for a deliberate reason, for example the project
will never use it, or you are reducing the number of dependencies. That is a
legitimate prune. The rule is: do not delete without a reason. It is not:
never delete.

## Assumptions

- **The packages behind the bootstrap variables are installed.** They are
  runtime requires. Removing a package without removing its variable breaks
  `main()`.
- **Features are order-independent unless wrapped in `Ordered`.** A feature
  with a real ordering dependency must say so through `Ordered` or `Group`
  nesting. A flat `Group` makes no ordering promise.
- **`wp_get_environment_type()` returns the expected value for each
  environment.** Environment gating through `Effect` depends on that return
  value.

## Failure modes

- **A malformed request URI does not throw.** `$request_uri` falls back to
  `new Uri( '/' )`. Features that read it get the root path, not an
  exception.
- **Removing a consumed variable causes a fatal error, not a clean
  removal.** Check the table above before deleting a variable such as
  `$clock` or `$block_type_registry`. Update its consumer first.
- **A plugin loaded by `Plugin_Loader` may be missing from the
  filesystem.** The loader skips a missing plugin silently. An integration
  feature wrapped after it through `Ordered` may then run against a plugin
  that never loaded. Confirm the plugin is installed in the target
  environment.
