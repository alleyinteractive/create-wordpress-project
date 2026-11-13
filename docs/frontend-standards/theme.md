# Theme

## theme.json first

`theme.json` (version 3, `$schema` set) is the source of truth for design
decisions. Reach for SCSS only for what it cannot express: pseudo-elements,
`:has()`, media queries, floats.

- **Presets, not values.** Colors, font sizes, spacing, and layout are
  presets. SCSS references them as `var(--wp--preset--…)` and
  `var(--wp--custom--…)`.
- **Locked down by default.** The scaffold turns off custom colors, default
  palettes, custom font sizes, custom spacing sizes, and appearance tools.
  A project opens a control deliberately, with the reason in the PR.
- **Slugs are semantic or t-shirt.** `primary`, `black`; font sizes
  `xs…4xl`; a numeric spacing scale. Slugs describe role or size, never a hex
  value or `color-1`.
- **Shared dimensions live in `settings.custom`**: `custom.spacing.site-edge`,
  `custom.spacing.block-gap`. Reference them as
  `var(--wp--custom--spacing--site-edge)`.
- **Layout.** `contentSize` and `wideSize` in rem, with
  `useRootPaddingAwareAlignments: true` and padding set at the root.
- **Block-level `styles.blocks.*` before SCSS** for anything it can express.

## Block style overrides

Per-block SCSS goes in `assets/scss/blocks/<namespace>/<block-name>.scss`, and
the path mirrors the block name: `core/image` → `core/image.scss`,
`core/media-text` → `core/media-text.scss`.

Each file builds as its own entry and enqueues through
`wp_enqueue_block_style` with `path`, so the CSS inlines and loads only when
the block appears on the page. This is why block overrides stay out of
`global`.

`entries/global` holds the normalize/reset and rules that are truly global.

## Templates, parts, patterns

- Templates and parts are HTML files built from blocks and patterns. Declare
  parts and their areas in `theme.json`.
- Patterns live in `patterns/*.php` with file-header registration.
- The Site Editor is off outside local and development environments, so
  template changes go through code review.

## Theme PHP

Procedural and namespaced as `Alley\WP\<Theme>\<Concern>`, split by concern in
`inc/` (`assets.php`, `blocks.php`, `theme.php`, …). `functions.php` defines
constants and requires `inc/*`; nothing else.

- Callbacks are named `action__*` and `filter__*`.
- Register hooks at the top of the file:
  `add_action( 'after_setup_theme', __NAMESPACE__ . '\action__after_setup_theme' )`.

## Styling stack

theme.json, native CSS, and SCSS. A utility CSS framework is not part of the
stack.
