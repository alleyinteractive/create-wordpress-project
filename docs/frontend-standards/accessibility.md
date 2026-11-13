# Accessibility

Applies to blocks, theme templates, and styles. `eslint-plugin-jsx-a11y` checks
React markup only. Templates in PHP, block markup, and CSS are covered by
review, so check these yourself before opening a PR. All code must meet WCAG
2.2 Level AA success criteria.

- **Semantic elements first.** Use `button`, `nav`, `ul`, and real headings
  before a `div` with a role. Add ARIA only where no element fits.
- **Every control has an accessible name.** A visible label, or for an
  icon-only control, text in `<span class="screen-reader-text">` inside it.
  Prefer on-page text to `aria-label`, which assistive tech announces less
  consistently. Placeholder text is not a label.
- **Headings keep their order.** One `h1` per page, no skipped levels. Choose
  the level by document structure, not by size; size comes from styles.
- **Focus stays visible and predictable.** Never remove an outline without
  replacing it. Menus, dialogs, and popovers move focus in on open, return it to
  the trigger on close, and close on Escape.
- **Keyboard parity.** Anything a pointer can do, the keyboard can do.
  Hover-only behavior has a focus equivalent.
- **Respect reduced motion.** Wrap non-essential animation and transitions in
  `@media (prefers-reduced-motion: no-preference)`.
- **Touch targets are large enough.** At least 24 by 24 CSS pixels, with
  spacing, and larger for primary actions.
- **Images have alt text** that says what the image is for. Decorative images
  use `alt=""`.
- **Contrast.** Text and interactive states meet WCAG AA, including hover and
  focus states.
- **Announce dynamic changes.** Content that updates without a page load, such
  as search results or form errors, goes in a live region (`aria-live`) or
  moves focus to the message.
