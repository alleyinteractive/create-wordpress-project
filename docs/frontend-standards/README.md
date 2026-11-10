# Frontend Standards

These files describe how frontend code is written in this project. They hold the
conventions a config cannot state: how blocks are built, what goes in the theme,
and why. Versions, scripts, and lint rules live in `.nvmrc`, `package.json`, and
the shared ESLint, Stylelint, and TypeScript configs. Read those, not a copy
here.

Each file covers one branch of work. Load only the one the task needs.

| Task | Read |
| --- | --- |
| Blocks, entries, slotfills | [blocks.md](blocks.md) |
| `theme.json`, templates, parts, patterns, block style overrides | [theme.md](theme.md) |
| SCSS/CSS in either | [styles.md](styles.md) |
| TypeScript/React in either | [typescript.md](typescript.md) |
| Any UI: labels, focus, headings, motion, contrast | [accessibility.md](accessibility.md) |

Where the code lives (plugin vs theme, directories, naming) is in
[../repo-layout.md](../repo-layout.md); read it before adding any file. For the
project's vocabulary, including preset, mirror, and entry, see
[../CONTEXT.md](../CONTEXT.md).

## Comments

Say why, never what. See [../comments.md](../comments.md).

## Maintaining these files

A rule belongs here when it is a convention, is not enforced by tooling, and
would surprise a newcomer. When tooling can enforce a rule, enforce it and
delete the prose. Keep each fact in one file. When you change a convention,
update the file in the same PR.
