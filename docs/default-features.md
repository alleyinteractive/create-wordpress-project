# Default features — what you're starting with

This file lists the functionality the project plugin ships by default. Check
this list before you build something new. It may already exist. For example,
copy-ID row actions already exist here.

Entries are grouped by domain. Each entry states whether the feature is
active by default, meaning `main()` instantiates it, or shipped unwired,
meaning it is a library feature present but dormant. Unwired entries include
a note on how to wire them. Classes live under `plugin-templates/src/`. Each
entry gives the file path. The file is the source of truth for exact
behavior.

> [ADR-0001](./adr/0001-opinionated-default-features.md) explains why some
> features ship unwired by design. [project-plugin-bootstrap.md](./project-plugin-bootstrap.md)
> explains how `main()` is structured. [CONTEXT.md](./CONTEXT.md) defines the
> terms wired feature and library feature.

---

## Block editor / block registration

- **`Disable_Block_Patterns`** — active. Removes core block patterns and
  disables remote Dotcom patterns. No args.
  `src/features/class-disable-block-patterns.php`
- **`Allowed_Block_Types`** — active. Restricts the block editor to an
  allowlist of block types for a given editor context. A callable predicate
  decides which blocks the allowlist includes. This scaffold wires it for
  `core/edit-post` with an allowlist of common editorial core blocks plus
  this plugin's own blocks. Args: `(context, callable $allowed,
  WP_Block_Type_Registry)`.
  `src/features/class-allowed-block-types.php`
- **`Query_Block_Namespace_Context`** — active. Exposes the `core/query`
  block's `namespace` attribute as block context. Descendant blocks can then
  read it. No args.
  `src/features/class-query-block-namespace-context.php`
- **`Featured_Image_Caption`** — active. Adds caption support to the
  featured image and registers the backing post meta. No args.
  `src/features/class-featured-image-caption.php`
- **`Block_Support`** — library. Adds a `supports` feature, for example
  color or spacing, to named block types through `register_block_type_args`.
  To wire: instantiate with `(array $block_types, string $feature, $config)`.
  `src/features/class-block-support.php`
- **`Block_Attribute`** — library. Adds an `attributes` entry to named block
  types. To wire: `(array $block_types, string $attribute, array $schema)`.
  `src/features/class-block-attribute.php`
- **`Uses_Context`** — library. Declares that a block type consumes a given
  context property. To wire: `(string $block_name, string $context)`.
  `src/features/class-uses-context.php`
- **`Default_Block_Context`** — library. Injects default values into
  rendered block context via `render_block_context`. To wire: `(array
  $context)`.
  `src/features/class-default-block-context.php`

> `Block_Support`, `Block_Attribute`, `Uses_Context`, and
> `Default_Block_Context` are library classes. Together with the active
> `Query_Block_Namespace_Context`, they form a toolkit for adjusting block
> registration and rendered context. None of them require forking a block
> definition.

## REST API fields

- **`Primary_Term_REST_Field`** — active. This scaffold wires one instance,
  for `category`. Adds a `primary_{taxonomy}` field to every REST-enabled
  post type in the taxonomy. The field returns the primary term ID. The
  `Primary_Term` value object resolves it: it uses Yoast's primary term if
  set, otherwise the first term alphabetically. Wire one instance per
  taxonomy. Arg: `(string $taxonomy)`.
  `src/features/class-primary-term-rest-field.php`, `src/class-primary-term.php`
- **`Taxonomy_Default_Term_REST_Field`** — active. Adds a `default_term`
  field to the `taxonomy` REST endpoint. The field returns the taxonomy's
  configured default term. The `Default_Term` value object resolves it. No
  args.
  `src/features/class-taxonomy-default-term-rest-field.php`, `src/class-default-term.php`
- **`Supported_Meta_Keys_REST_Field`** — active. Adds a
  `supported_meta_keys` field to the `type` REST endpoint. The field lists
  the post type's registered meta keys. No args.
  `src/features/class-supported-meta-keys-rest-field.php`
- **`Supported_Fields_REST_Field`** — active. Adds a `supported_fields`
  field to the `type` REST endpoint. The field lists the type's REST schema
  properties. No args.
  `src/features/class-supported-fields-rest-field.php`

> Both `Supported_*` fields use the `Alley\traverse()` helper from
> `alleyinteractive/traverse-reshape`.

## Content model / post & term data

- **`Subheadline`** — active. Adds subheadline support and backing meta to
  post types. Filter `…_subheadline_post_types` to change which post types
  get it. No args.
  `src/features/class-subheadline.php`
- **`Alley_Change_Modified`** — active. Lets code set a post's modified date
  at insert time through an `alley_change_modified` parameter. The parameter
  accepts true, false, a date string, or a `DateTimeInterface`. No args.
  `src/features/class-alley-change-modified.php`
- **`Term_Dates`** — active. Records `term_date_gmt` and
  `term_modified_gmt` as term meta on every taxonomy. These mirror the post
  date columns. Arg: `(ClockInterface $clock)`. `main()` passes the shared
  `$clock` from the bootstrap block.
  `src/features/class-term-dates.php`
- **`Primary_Term`** and **`Default_Term`** — helper value objects, not
  features. `Primary_Term` resolves a post's primary term. `Default_Term`
  resolves a taxonomy's default term. The REST fields above use them. You
  can also use them directly.
  `src/class-primary-term.php`, `src/class-default-term.php`
- **`Block_Content_Filter`** — library. This is a root-namespace class, not
  under `Features\`. It is a `the_content` harness. On the singular post
  being viewed, if the post's content has blocks, it hands the parsed
  blocks to a project-supplied merge callback and re-serializes the result.
  To wire: instantiate with a `callable $block_merge` of `(Serialized_Blocks,
  int): Serialized_Blocks`. It uses `Alley\WP\Blocks\Block_Content` and
  `Alley\WP\Types\Serialized_Blocks`.
  `src/class-block-content-filter.php`

## Admin DX

- **`ID_Row_Action`** — active. Adds Copy ID and View ID row actions to the
  post, page, and tag list tables. The `entries/id-row-action/` JS entry
  provides the clipboard behavior. No args.
  `src/features/class-id-row-action.php`, `entries/id-row-action/`
- **`Site_Settings_Page`** — active. Registers a settings page built on
  Fieldmanager. It ships empty by design: a base group plus a
  `…_site_settings_group_children` filter. Per-concern settings features
  hook into that filter. See "Extending the settings page" below. Args:
  `(string $option_name, string $capability)`.
  `src/features/class-site-settings-page.php`
- **`Reset_Theme_Command`** — active, under `WP_CLI_Feature`. A WP-CLI
  command that resets database theme customizations to the block theme's
  file defaults. It depends on the `create-block-theme` plugin. Arg:
  `(Plugin_Loader $plugins)`.
  `src/features/class-reset-theme-command.php`
- **`Admin_Menu_Order`** — library. Reorders the admin menu by grouping menu
  slugs with separators. To wire: `(array $groups)`.
  `src/features/class-admin-menu-order.php`
- **`Post_Type_Support`** — library. Adds a support feature to a post type.
  It handles both an already-registered post type and one not yet
  registered. To wire: `(string $post_type, string $feature)`.
  `src/features/class-post-type-support.php`

## Integrations, plugin loading & analytics

- **`Plugin_Loader`** — active, several instances. A `Feature` wrapper
  around `Alley\WP\WP_Plugin_Loader`. It loads plugins from inside the
  feature tree. `main()` uses it to activate the bundled plugins listed
  below. Arg: `(array $plugins)`. See
  [ADR-0002](./adr/0002-declarative-plugin-loading.md).
  `src/features/class-plugin-loader.php`
- **`Search_Customizations`** — active. Configures Elasticsearch
  Extensions. It restricts searchable post types and registers taxonomy
  aggregations. This scaffold wires it for `post` and `page` post types and
  the `category` taxonomy. Args: `(array $post_types, array $taxonomies)`.
  `src/features/class-search-customizations.php`
- **`MSM_Sitemap_Integration`** — active, wired for `post`. Hands sitemap
  generation to MSM Sitemap and disables the core and competing sitemaps.
  Arg: `(array $post_types)`.
  `src/features/class-msm-sitemap-integration.php`
- **`Google_Tag_Manager`** — library. Injects the GTM head script and
  body-open noscript fallback. Ships unwired because the container ID is
  project-specific. To wire: `(string $container_id)`.
  `src/features/class-google-tag-manager.php`
- **`Mantle_Model`** — library. Registers a Mantle ORM model class and
  wires its taxonomies to the post type. It relies on `mantle-framework`,
  which the starter plugin provides. To wire: `(string $model)`.
  `src/features/class-mantle-model.php`
- **Inline tweak, not a class** — After loading `wp-asset-manager`, `main()`
  sets default `am_global_svg_attributes` values, `aria-hidden` and
  `focusable`, through a `Quick_Feature`.

### Bundled plugins (loaded declaratively in `main()`)

`Plugin_Loader` features in the tree activate these plugins. wp-admin does
not activate them: `wp-alleyvate`, `byline-manager`, `meta-inspector`,
`safe-redirect-manager`, `wordpress-fieldmanager`, `wordpress-seo`,
`wp-curate`, `wp-new-relic-transactions`, `elasticsearch-extensions`,
`wp-asset-manager`, `msm-sitemap`, and `create-block-theme`. `main()` loads
`create-block-theme` only in the local environment, along with the
`Reset_Theme_Command`. If a listed plugin is not installed, the loader skips
it silently. See the failure modes in
[project-plugin-bootstrap.md](./project-plugin-bootstrap.md).

## Inherited from the starter plugin

`main()` also wires two features from the
[`create-wordpress-plugin`](https://github.com/alleyinteractive/create-wordpress-plugin)
starter. Their class files are not in `src/features/` in this scaffold:

- **`Load_Entries`** — active. Loads built `entries/` assets. It caches
  them outside the `local` environment.
- **`Register_Block_Manifest`** — active. Registers this plugin's blocks in
  bulk from the build manifest.

---

## Extending the settings page

The settings page is empty by design. To add settings, do not register a new
page. Attach a Fieldmanager field group to the existing page through its
filter:

```php
add_filter(
	'…_site_settings_group_children',
	function ( array $children ): array {
		$children['my_section'] = new \Fieldmanager_Group( /* … */ );
		return $children;
	}
);
```

This keeps every setting on one page and one option. That is why
`Site_Settings_Page` ships as a base with zero children, instead of a fixed
set of settings.
