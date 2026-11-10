# CLAUDE.md

Guidance for agents and developers working in this project. Lint and static
analysis (PHPCS, PHPStan, ESLint, Stylelint) enforce the mechanics, so the docs
below hold only the conventions they cannot. Load only the file the task needs.

## Read when

- `docs/repo-layout.md`: before adding files, or deciding plugin vs theme.
- `docs/CONTEXT.md`: when a term (project plugin, wired feature, preset,
  mirror, entry) is unfamiliar.
- `docs/default-features.md`: before writing a Feature or dependency of your
  own.
- `docs/project-plugin-bootstrap.md`: when changing how `main()` composes
  Features.
- `docs/README.md`: when adding an ADR or a reference doc.

## Standards

- Frontend (blocks, theme, styles, TypeScript, accessibility):
  `docs/frontend-standards/README.md` routes each task to one file.
- Backend (Features, data safety, performance, PHP tests):
  `docs/backend-standards/README.md` routes each task to one file.

## Always

- **Check before you build.** Follow the lookup order in
  `docs/backend-standards/features.md`, and say in the PR when nothing fits.
- **Validate before you use.** Meta, options, REST, and block attributes can be
  missing or the wrong type. Handle the empty case
  (`docs/backend-standards/data-safety.md`).
- **Keep it small.** Build for the case in front of you, and delete dead code,
  debug output, and commented-out code.
- **Comments say why** (`docs/comments.md`).

## Before opening a PR

1. Run `composer test` and `npm test`; both pass.
2. Review your own diff and run an AI review of it. Every finding is fixed, or
   dismissed with a reason in the PR description, before you ask a human, so
   reviewers do not relay the same findings.
3. Fill in the PR template: what and why, how to verify, and screenshots for UI
   changes. Put design rationale in the description, not in comments.
