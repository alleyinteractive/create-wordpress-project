# The project plugin's `main()`

The project plugin boots from a single function, `main()`, in `src/main.php`. It
does two things: it defines a block of shared bootstrap variables, and it composes
every active feature into one nested `Feature` tree that it then `boot()`s. This
doc explains how that function is shaped and — importantly — why some of it is
deliberately unused at the start.

> See also: [ADR-0002](./adr/0002-declarative-plugin-loading.md) (why plugins load
> from inside the tree) and [ADR-0001](./adr/0001-opinionated-default-features.md)
> (why unused code ships on purpose).

## Where it lives

- `src/main.php` — the `main()` function. The plugin's bootstrap file calls it.
- `src/features/` — the feature classes that `main()` instantiates (see
  [default-features.md](./default-features.md)).
- The `Feature`, `Group`, `Ordered`, `Effect`, `Quick_Feature`, and
  `WP_CLI_Feature` decorators come from `alleyinteractive/wp-type-extensions`.

## How it works

### The bootstrap variables

`main()` opens by deriving a handful of shared values once, up front, so that any
feature wired below can be handed the same instance instead of re-deriving it:

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

These are written with fully-qualified class names (imported via `use` at the top
of the file) rather than helper wrappers, so the dependency of each value is
explicit and greppable. The packages behind them — `symfony/http-foundation`,
`nyholm/psr7`, `symfony/clock`, `wp-path-dispatch`, and the `Global_Post_Query`
from `wp-type-extensions` — are all runtime requires.

Not all of these are consumed yet. That is intentional (see
[ADR-0001](./adr/0001-opinionated-default-features.md)): the common values are
present and ready so the next developer reaches for the shared instance instead of
constructing their own. Current status:

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

After the variables, `main()` builds one expression: features wrapped in
decorators that control grouping and order, assigned to `$plugin` and booted with
`$plugin->boot()`. The decorator vocabulary:

- **`Group`** — boot a set of features together; order within is not significant.
- **`Ordered`** (`first:` / `then:`) — boot `first` before `then`. Used when a
  feature depends on something the prior step set up (most often: load a plugin,
  then run the feature that integrates with it).
- **`Effect`** (`when:` / `then:`) — boot `then` only if the `when:` predicate is
  true. Used for environment gating (e.g. `create-block-theme` only in `local`).
- **`Quick_Feature`** — wrap a bare closure as a one-off feature.
- **`WP_CLI_Feature`** — boot a feature only in a WP-CLI context.

`boot()` walks the tree depth-first; each leaf feature's own `boot()` runs its
`add_action`/`add_filter` registrations. There is no separate registry — the tree
is the manifest of what the plugin does.

### Plugin loading lives inside the tree

Dependency plugins (Fieldmanager, Yoast, byline-manager, Elasticsearch, MSM
Sitemap, …) are loaded by `Plugin_Loader` features inside this tree, not through
wp-admin. That is what lets a plugin be colocated with its integration feature via
`Ordered` (load Elasticsearch, then `Search_Customizations`) and gated by
environment via `Effect`. The full rationale and trade-offs are in
[ADR-0002](./adr/0002-declarative-plugin-loading.md). Note this is distinct from —
and sits above — the mu-plugin loader that loads the project plugin itself.

## A note on the unused code

The inert variables above, and the library (unwired) feature classes in
`src/features/`, are present on purpose. Do not remove them as part of an
unrelated cleanup or "remove dead code" pass — they are the project's ready-to-use
toolkit, and stripping them defeats the reason they ship (see
[ADR-0001](./adr/0001-opinionated-default-features.md)).

They are not sacred, either: if you have a deliberate reason to drop one — the
project will never use it, you're trimming the dependency surface — that is a
legitimate prune. The rule is don't delete reflexively, not never delete.

## Assumptions

- **The packages behind the bootstrap variables are installed.** They are runtime
  requires; removing one without removing its variable breaks `main()`.
- **Features are order-independent unless wrapped in `Ordered`.** Anything with a
  real ordering dependency must say so with `Ordered`/`Group` nesting; a flat
  `Group` makes no ordering promise.
- **`wp_get_environment_type()` returns the expected value per environment.**
  Environment gating (`Effect`) is only as correct as that return value.

## Failure modes

- **A malformed request URI** is caught: `$request_uri` falls back to `new Uri(
  '/' )` rather than throwing. Features reading it get root, not an exception.
- **Removing a consumed variable** (e.g. `$clock`, `$block_type_registry`) without
  updating its consumer is a fatal error, not dead-code removal — check the table
  above before deleting.
- **A plugin loaded by `Plugin_Loader` is absent** from the filesystem: the loader
  silently skips it, so an integration feature wrapped after it via `Ordered` may
  run against a plugin that never loaded. Confirm the plugin is installed in the
  target environment.
