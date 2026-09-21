# Context

Terms this project defines for itself. This file does not redefine general
WordPress and Gutenberg vocabulary, such as blocks, hooks, post types, and the
`Feature`, `Group`, `Ordered`, and `Effect` decorators. It defines only the
words specific to this scaffold.

## Language

- **Project plugin** — The one plugin that holds all of a project's custom
  functionality, instead of many small single-purpose plugins. Its entry
  point is `main()`. The scaffold generates one project plugin per project.
  Do not call it "core plugin". That name collides with WordPress core. Do
  not call it "the custom plugin" or "the functionality plugin".
- **Wired feature** — A feature class that the scaffold ships and
  instantiates in `main()`. It is active by default. Do not call it "enabled
  feature" or "default feature". "Default feature" is ambiguous, since
  everything shipped is a default.
- **Library feature** (also called unwired feature) — A feature class that
  the scaffold ships in `src/features/` but does not instantiate in
  `main()`. It is dormant. A project can wire it up when needed. Its
  configuration is project-specific, so the scaffold leaves the wiring to
  the project. Do not call it "dead feature" or "disabled feature".
