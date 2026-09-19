# Documentation

These docs describe what a new project starts with. This includes the default
features, dependencies, and bootstrap wiring that the scaffold puts in the
project plugin before anyone writes project code.

These docs describe a starting point, not a finished system. A fresh project
ships with shared bootstrap variables and feature classes common across Alley
projects. Some of these are not yet used. The ADRs explain why. As the project
grows, add to this baseline, wire parts of it up, prune parts of it, and
update these docs to match.

To maintain these files, you can use the
[`grill-with-docs` skill by Matt Pocock](https://github.com/mattpocock/skills/tree/main/skills/engineering/grill-with-docs).

## What lives here

- **Architecture decision records**: one numbered file per decision in `adr/`
  (`0001-slug.md`, `0002-slug.md`, and so on). An ADR records that the project
  made a decision and why. It states the context, the decision, and the
  trade-off. Most ADRs run a few sentences. Add an ADR only when a decision is
  hard to reverse, would surprise a future reader, and involved real
  alternatives.
- **Reference docs**: flat files at the root of `docs/`, for example
  `project-plugin-bootstrap.md` and `default-features.md`. These explain how a
  part of the system works: the mechanics, the assumptions, and the failure
  modes. Detail too long or too specific for an ADR belongs here.
- **Glossary** (`CONTEXT.md`): the terms this project defines for itself, for
  example project plugin and wired feature. General WordPress and Gutenberg
  vocabulary does not belong here.

## Conventions

- Create a file only when it has content. Do not add empty placeholder files.
- ADRs use sequential numbers. Check `adr/` for the highest number and add one
  to it.
- An ADR records the decision and the reason for it. Link to a reference doc
  when the mechanics need more room.
