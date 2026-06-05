# Context

Terms this project coins for itself. General WordPress/Gutenberg vocabulary
(blocks, hooks, post types, the `Feature` interface, the `Group`/`Ordered`/
`Effect` decorators) is not redefined here — only the words whose meaning is
specific to how this scaffold is put together.

## Language

- **Project plugin** — The single, monorepo-style plugin that holds all of a
  project's custom functionality, as opposed to many small, single-purpose
  plugins. Its entry point is `main()`. This is the house pattern; the scaffold
  generates one project plugin per project. Avoid: "core plugin" (collides with
  WordPress core), "the custom plugin", "the functionality plugin".
- **Wired feature** — A feature class that is both shipped and instantiated in
  `main()`, so it is active out of the box. Avoid: "enabled feature", "default
  feature" (ambiguous — everything shipped is a default).
- **Library feature** (a.k.a. unwired feature) — A feature class that is shipped
  in `src/features/` but not instantiated in `main()`. It is dormant: available to
  wire up when a project needs it, but doing nothing until then. Its useful
  configuration is project-specific, so the scaffold leaves the wiring to the
  project. Avoid: "dead feature", "disabled feature".
