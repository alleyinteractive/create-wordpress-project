# Styles (SCSS/CSS)

Applies to block, entry, and theme styles. Stylelint enforces the formatting;
these are the conventions it cannot.

- **Presets for values.** Colors, sizes, and spacing come from `theme.json`
  presets ([theme.md](theme.md)).
- **`@use`** for partials (`@use '…/normalize' as *`).
- **Logical properties** for anything directional: `margin-inline-start`,
  `padding-block`, `inset-inline-start`.
- **Reuse before hard-coding.** Reach for an existing variable or preset before
  writing a literal, and turn a value you repeat into a variable. A hard-coded
  `px` for type or spacing is a review flag; use a preset or `rem`.
- **`rem` values come from a rem function.** Write the size in pixels and
  let the function convert it: `functions.rem(24px)`, not `1.5rem`.
- **Specificity from the wrapper class.** Scope under the WordPress wrapper
  (`.wp-block-<ns>-<name>`) to win specificity.
- **Mobile first.** Media queries add width. Breakpoints come from one shared
  definition per project (a `theme.json` `custom.breakpoints` value and an SCSS
  partial), not inline numbers.
- **BEM-style elements** inside a block: `.wp-block-<ns>-<name>__element`.
- **Nest full class names.** Write the full class name and nest it inside its
  parent, so a selector can be searched for and read as written. Group every
  rule for a parent under one block instead of repeating the parent selector in
  separate top-level rules:

  ```scss
  .wp-block-ns-name {
  	.wp-block-ns-name__title {
  		font-size: var(--wp--preset--font-size--large);
  	}

  	.wp-block-ns-name__body {
  		margin-block-start: var(--wp--preset--spacing--30);
  	}
  }
  ```

  `&` is for the parent's own state or pseudo-class, such as `&:hover` or
  `&.is-active`; it never assembles `&__element` or `&--modifier`.
- **Nesting stops at three levels.**
- **Comments say why** ([comments.md](../comments.md)).

## Block stylesheets

- `style.scss`: loads on the front end and in the editor.
- `index.scss`: loads in the editor only. Declare it as `editorStyle` in
  `block.json`.

Core block overrides live in the theme
([theme.md](theme.md#block-style-overrides)).
