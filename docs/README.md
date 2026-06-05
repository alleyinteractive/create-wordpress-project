# Documentation

These docs describe what a new project starts with — the default features,
dependencies, and bootstrap wiring that the scaffold puts in the project plugin
before anyone writes a line of project-specific code.

They are deliberately not tied to any particular project. They describe a
starting point, not a finished system: a fresh project ships with shared
bootstrap variables and feature classes that are common across Alley projects,
some of which are not yet used (see the ADRs). As the project matures, expect to
add, wire up, and prune from this baseline — and to revise these docs to match.

You might wish to use the
[`grill-with-docs` skill by Matt Pocock](https://github.com/mattpocock/skills/tree/main/skills/engineering/grill-with-docs)
to help you maintain these files.

## What lives here

- **Architecture decision records**: one numbered file per decision in `adr/`
  (`0001-slug.md`, `0002-slug.md`, …). An ADR records that a decision was made
  and why — context, the call, the trade-off. Most are a few sentences. Add an
  ADR only when a decision is hard to reverse, would surprise a future reader, and
  was a real trade-off with genuine alternatives.
- **Reference docs**: flat files at the root of `docs/` (e.g.
  `project-plugin-bootstrap.md`, `default-features.md`). These explain how a
  piece of the system works: mechanics, assumptions, and failure modes. Detail
  that's too long-lived and too granular for an ADR belongs here.
- **Glossary** (`CONTEXT.md`): the handful of terms this project coins for itself
  (e.g. project plugin, wired/library feature). General WordPress/Gutenberg
  vocabulary does not belong here.

## Conventions

- Create files only when there's something worth writing. Don't scaffold empty
  placeholders.
- ADRs are numbered sequentially; scan `adr/` for the highest number and add one.
- An ADR records the decision and rationale. Link out to a reference doc when the
  mechanics or gotchas need room to breathe.
