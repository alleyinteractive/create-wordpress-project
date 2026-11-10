# CLAUDE.md

Guidance for agents and developers working in this project. Lint and static
analysis (PHPCS, PHPStan, ESLint, Stylelint) enforce the mechanics, so the docs
below hold only the conventions they cannot. Load the file the task needs.

## Read first

- `docs/README.md` for what the scaffold ships and why.
- `docs/CONTEXT.md` for this project's vocabulary.
- `docs/repo-layout.md` before adding files, or deciding plugin vs theme.

Frontend standards: docs/frontend-standards/
- Blocks, entries, slotfills → blocks.md
- theme.json, templates, parts, patterns, block styles → theme.md
- SCSS/CSS → styles.md
- TS/React → typescript.md
- Any UI (labels, focus, headings, motion) → accessibility.md

Backend standards: docs/backend-standards/
- Feature classes, build-vs-reuse → features.md
- Untrusted data, authorization → data-safety.md
- Queries, caching, hook scope → performance.md
- PHP tests → testing.md

## Always

- **Check before you build.** Look in `docs/default-features.md`, WordPress
  core, and Mantle before writing your own. Say in the PR when none fit.
- **Validate before you use.** Meta, options, REST, and block attributes can be
  missing or the wrong type. Handle the empty case.
- **Keep it small.** No speculative abstractions, dead code, debug output, or
  commented-out code.
- **Comments say why**, never what (`docs/comments.md`).

## Before opening a PR

1. Run lint and tests.
2. Review your own diff and run an AI review of it. Fix what it finds before
   asking a human, so reviewers do not have to relay the same findings.
3. Fill in the PR template: what and why, how to verify, and screenshots for UI
   changes. Put design rationale in the description, not in comments.
