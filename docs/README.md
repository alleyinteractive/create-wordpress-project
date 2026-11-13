# Documentation

These docs describe how this project is built and the standards it follows.
The project started from Alley's create-wordpress-project scaffold, so it began
with default features, dependencies, and bootstrap wiring in the project
plugin. Those defaults are documented here so you know what is already in place
before you write something new.

The defaults are a starting point, not a finished system. The project began
with shared bootstrap variables and feature classes common across Alley
projects, and some of them are not used yet. The ADRs explain why. As the
project grows, add to this baseline, wire parts of it up, prune parts of it,
and update these docs to match.

Start with [`CONTEXT.md`](CONTEXT.md) for this project's vocabulary. Developers
and agents should also read [`CLAUDE.md`](../CLAUDE.md) at the repo root.

To maintain these files, you can use the
[`grill-with-docs` skill by Matt Pocock](https://github.com/mattpocock/skills/tree/main/skills/engineering/grill-with-docs).

## What lives here

- **Glossary** (`CONTEXT.md`): the terms this project defines for itself, for
  example project plugin and wired feature. General WordPress and Gutenberg
  vocabulary does not belong here.
- **Reference docs**: flat files at the root of `docs/`, for example
  `repo-layout.md`, `project-plugin-bootstrap.md`, `default-features.md`, and
  `comments.md`.
  These explain how a part of the system works: the mechanics, the
  assumptions, and the failure modes. Detail too long or too specific for an
  ADR belongs here.
- **Architecture decision records**: one numbered file per decision in `adr/`
  (`0001-slug.md`, `0002-slug.md`, and so on). An ADR records that a decision
  was made and why. It states the context, the decision, and the trade-off.
  Most ADRs run a few sentences. Add an ADR only when a decision is hard to
  reverse, would surprise a future reader, and involved real alternatives.
- **Frontend standards**: `frontend-standards/`, one file per branch of
  frontend work (layout, blocks, theme, styles, TypeScript, accessibility).
  Start at its `README.md`.
- **Backend standards**: `backend-standards/`, one file per branch of PHP work
  (features, data safety, performance, testing). Start at its `README.md`.

## Conventions

- Create a file only when it has content. Do not add empty placeholder files.
- ADRs use sequential numbers. Check `adr/` for the highest number and add one
  to it.
- An ADR records the decision and the reason for it. Link to a reference doc
  when the mechanics need more room.
- When you change behavior these docs describe, update them in the same PR.
- A rule belongs in `frontend-standards/` or `backend-standards/` when it is a
  convention, tooling does not enforce it, and it would surprise a newcomer.
  When tooling can enforce a rule, enforce it and delete the prose. Keep each
  fact in one file and link to it from elsewhere.
