# Default features — what you're starting with

This is the catalogue of functionality the project plugin ships with out of the
box. Use it to answer "does this already exist?" before building something — that
is its main job (a feature you'd otherwise rebuild, like copy-ID row actions, is
probably already here).

Entries are grouped by domain. Each notes whether it is active by default
(instantiated in `main()`) or shipped unwired (a library feature — present but
dormant, with a note on how to turn it on). Classes live under
`plugin-templates/src/`; the path is given per entry and is the source of truth
for exact behavior.

> Why some of this ships unwired, and why that's deliberate rather than
> half-finished, is in [ADR-0001](./adr/0001-opinionated-default-features.md). How
> `main()` is structured is in
> [project-plugin-bootstrap.md](./project-plugin-bootstrap.md). The terms wired
> and library feature are defined in [CONTEXT.md](./CONTEXT.md).

---

## Block editor / block registration

- **`Disable_Block_Patterns`** — active. Removes core block patterns and disables
  remote (Dotcom) patterns. No args.
  `src/features/class-disable-block-patterns.php`
- **`Allowed_Block_Types`** — active. Restricts the editor to an allowlist of
  block types in a given editor context, via a callable predicate. Wired for
  `core/edit-post` with a curated allowlist of common editorial core blocks plus
  this plugin's own blocks. Args: `(context, callable $allowed, WP_Block_Type_Registry)`.
  `src/features/class-allowed-block-types.php`
- **`Query_Block_Namespace_Context`** — active. Exposes the `core/query` block's
  `namespace` attribute as block context so descendant blocks can read it. No args.
  `src/features/class-query-block-namespace-context.php`
- **`Featured_Image_Caption`** — active. Adds caption support to the featured
  image and registers the backing post meta. No args.
  `src/features/class-featured-image-caption.php`
- **`Block_Support`** — library. Adds a `supports` feature (e.g. color, spacing)
  to named block types via `register_block_type_args`. To wire: instantiate with
  `(array $block_types, string $feature, $config)`.
  `src/features/class-block-support.php`
- **`Block_Attribute`** — library. Adds an `attributes` entry to named block
  types. To wire: `(array $block_types, string $attribute, array $schema)`.
  `src/features/class-block-attribute.php`
- **`Uses_Context`** — library. Declares that a block type consumes a given
  context property. To wire: `(string $block_name, string $context)`.
  `src/features/class-uses-context.php`
- **`Default_Block_Context`** — library. Injects default values into rendered
  block context via `render_block_context`. To wire: `(array $context)`.
  `src/features/class-default-block-context.php`

> The four library classes above (`Block_Support`, `Block_Attribute`,
> `Uses_Context`, `Default_Block_Context`) plus the active
> `Query_Block_Namespace_Context` form a small toolkit for adjusting block
> registration and rendered context without forking block definitions.

## REST API fields

- **`Primary_Term_REST_Field`** — active (one instance, for `category`). Adds a
  `primary_{taxonomy}` field to every REST-enabled post type in the taxonomy,
  returning the primary term ID (resolved by the `Primary_Term` value object —
  Yoast if present, else first term alphabetically). One instance per taxonomy.
  Arg: `(string $taxonomy)`.
  `src/features/class-primary-term-rest-field.php`, `src/class-primary-term.php`
- **`Taxonomy_Default_Term_REST_Field`** — active. Adds a `default_term` field to
  the `taxonomy` REST endpoint, returning the taxonomy's configured default term
  (resolved by the `Default_Term` value object). No args.
  `src/features/class-taxonomy-default-term-rest-field.php`, `src/class-default-term.php`
- **`Supported_Meta_Keys_REST_Field`** — active. Adds a `supported_meta_keys`
  field to the `type` REST endpoint listing the post type's registered meta keys.
  No args.
  `src/features/class-supported-meta-keys-rest-field.php`
- **`Supported_Fields_REST_Field`** — active. Adds a `supported_fields` field to
  the `type` REST endpoint listing the type's REST schema properties. No args.
  `src/features/class-supported-fields-rest-field.php`

> Both `Supported_*` fields use the `Alley\traverse()` helper
> (`alleyinteractive/traverse-reshape`).

## Content model / post & term data

- **`Subheadline`** — active. Adds subheadline support and backing meta to post
  types (filterable via `…_subheadline_post_types`). No args.
  `src/features/class-subheadline.php`
- **`Alley_Change_Modified`** — active. Lets a post's modified date be controlled
  at insert time via an `alley_change_modified` parameter (true / false / date
  string / `DateTimeInterface`). No args.
  `src/features/class-alley-change-modified.php`
- **`Term_Dates`** — active. Records `term_date_gmt` / `term_modified_gmt` as term
  meta on every taxonomy, mirroring the post date columns. Arg: `(ClockInterface
  $clock)` — handed the shared `$clock` from the bootstrap block.
  `src/features/class-term-dates.php`
- **`Primary_Term`** / **`Default_Term`** — helper value objects (not features).
  Resolve a post's primary term, and a taxonomy's default term, respectively. Used
  by the REST fields above; reusable directly.
  `src/class-primary-term.php`, `src/class-default-term.php`
- **`Block_Content_Filter`** — library (root-namespace class, not under
  `Features\`). A `the_content` harness that, on the singular post being viewed and
  only when its content has blocks, hands the parsed blocks to a project-supplied
  merge callback and re-serializes. To wire: instantiate with a `callable
  $block_merge` of `(Serialized_Blocks, int): Serialized_Blocks`. Uses
  `Alley\WP\Blocks\Block_Content` / `Alley\WP\Types\Serialized_Blocks`.
  `src/class-block-content-filter.php`

## Admin DX

- **`ID_Row_Action`** — active. Adds "Copy ID" / "View ID" row actions to the
  post, page, and tag list tables (clipboard behavior from the
  `entries/id-row-action/` JS entry). No args.
  `src/features/class-id-row-action.php`, `entries/id-row-action/`
- **`Site_Settings_Page`** — active. Registers a Fieldmanager-powered settings
  page. It ships empty by design: a base group plus a
  `…_site_settings_group_children` filter that per-concern settings features hook
  into (see "Extending the settings page" below). Args: `(string $option_name,
  string $capability)`.
  `src/features/class-site-settings-page.php`
- **`Reset_Theme_Command`** — active (under `WP_CLI_Feature`). A WP-CLI command
  that resets database theme customizations back to the block theme's file
  defaults. Depends on the `create-block-theme` plugin. Arg: `(Plugin_Loader
  $plugins)`.
  `src/features/class-reset-theme-command.php`
- **`Admin_Menu_Order`** — library. Reorders the admin menu by grouping menu slugs
  with separators. To wire: `(array $groups)`.
  `src/features/class-admin-menu-order.php`
- **`Post_Type_Support`** — library. Adds a support feature to a post type,
  handling both the already-registered and not-yet-registered cases. To wire:
  `(string $post_type, string $feature)`.
  `src/features/class-post-type-support.php`

## Integrations, plugin loading & analytics

- **`Plugin_Loader`** — active (several instances). A thin `Feature` wrapper around
  `Alley\WP\WP_Plugin_Loader` that loads plugins from inside the feature tree. This
  is how the bundled plugins below get activated. Arg: `(array $plugins)`. See
  [ADR-0002](./adr/0002-declarative-plugin-loading.md).
  `src/features/class-plugin-loader.php`
- **`Search_Customizations`** — active. Configures Elasticsearch Extensions:
  restricts searchable post types and registers taxonomy aggregations. Wired for
  `post`/`page` + `category`. Args: `(array $post_types, array $taxonomies)`.
  `src/features/class-search-customizations.php`
- **`MSM_Sitemap_Integration`** — active (for `post`). Hands sitemap duties to MSM
  Sitemap and disables the core/competitor sitemaps. Arg: `(array $post_types)`.
  `src/features/class-msm-sitemap-integration.php`
- **`Google_Tag_Manager`** — library. Injects the GTM head script and body-open
  noscript fallback. Ships unwired because the container ID is project-specific. To
  wire: `(string $container_id)`.
  `src/features/class-google-tag-manager.php`
- **`Mantle_Model`** — library. Registers a Mantle ORM model class and wires its
  taxonomies to the post type. Relies on `mantle-framework`, which the starter
  plugin provides downstream. To wire: `(string $model)`.
  `src/features/class-mantle-model.php`
- **Inline tweak (not a class)** — `main()` also sets default
  `am_global_svg_attributes` (`aria-hidden`, `focusable`) via a `Quick_Feature`,
  after loading `wp-asset-manager`.

### Bundled plugins (loaded declaratively in `main()`)

These are activated by `Plugin_Loader` features in the tree, not via wp-admin:
`wp-alleyvate`, `byline-manager`, `meta-inspector`, `safe-redirect-manager`,
`wordpress-fieldmanager`, `wordpress-seo`, `wp-curate`,
`wp-new-relic-transactions`, `elasticsearch-extensions`, `wp-asset-manager`,
`msm-sitemap`, and `create-block-theme` (local environment only, plus the
`Reset_Theme_Command`). A plugin loaded here that isn't installed is skipped
silently — see the failure modes in
[project-plugin-bootstrap.md](./project-plugin-bootstrap.md).

## Inherited from the starter plugin

`main()` also wires two features that come from the
[`create-wordpress-plugin`](https://github.com/alleyinteractive/create-wordpress-plugin)
starter, not from `src/features/` in this scaffold — so don't go looking for their
class files alongside the others:

- **`Load_Entries`** — active. Loads built `entries/` assets (caches outside
  `local`).
- **`Register_Block_Manifest`** — active. Registers this plugin's blocks in bulk
  from the build manifest.

---

## Extending the settings page

The settings page is intentionally empty. To add settings, don't register a new
page — attach a Fieldmanager field group to the existing one through its filter:

```php
add_filter(
	'…_site_settings_group_children',
	function ( array $children ): array {
		$children['my_section'] = new \Fieldmanager_Group( /* … */ );
		return $children;
	}
);
```

This keeps every setting on one page and one option, which is why
`Site_Settings_Page` ships as a base with zero children rather than a fixed set.
