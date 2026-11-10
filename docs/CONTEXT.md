# Context

Terms this project defines for itself. This file does not redefine general
WordPress and Gutenberg vocabulary, such as blocks, hooks, post types, and the
`Feature`, `Group`, `Ordered`, and `Effect` decorators. It defines only the
words specific to this scaffold.

## Language

- **Project plugin** — The one plugin that holds all of a project's custom
  functionality, instead of many small single-purpose plugins. Its entry
  point is `main()`. The scaffold generates one project plugin per project.
  "Core plugin" would collide with WordPress core.
- **Wired feature** — A feature class that the scaffold ships and
  instantiates in `main()`. It is active by default. "Default feature" is
  ambiguous, since everything shipped is a default.
- **Library feature** (also called unwired feature) — A feature class that
  the scaffold ships in `src/features/` but does not instantiate in
  `main()`. It is dormant. A project can wire it up when needed. Its
  configuration is project-specific, so the scaffold leaves the wiring to
  the project.
- **Preset** — A named `theme.json` value (color, size, spacing) that code
  references instead of a raw value.
- **Mirror** — A path or name that copies the name of the block it belongs
  to, for example `core/image` → `core/image.scss`.
- **Entry** — A directory under `entries/` that builds to `build/<name>/`.
