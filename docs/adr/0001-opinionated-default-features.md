---
status: accepted
---

# Ship opinionated default functionality, accepting some unused code

The project plugin starts with a broad set of feature classes and shared
bootstrap variables drawn from what mature Alley projects repeatedly grow into,
rather than a minimal blank slate. We chose inclusivity: ship the commonly-needed
code ready-to-hand so every project starts from the same baseline and developers
reach for an existing implementation instead of rebuilding one — explicitly
accepting that some shipped code is unused on day one.

The selection came from analysing the `main()` functions of five mature Alley
publisher sites and keeping what recurred. Because the sample is all mature sites,
the defaults lean toward what projects grow into, not strictly what they need on
day one (survivorship bias, accepted knowingly).

## Considered options

- **Minimal scaffold** (only universally-needed code, add the rest per project) —
  rejected. It pushes every team to re-derive the same features independently and
  inconsistently, which is the problem this change exists to solve.
- **Inclusive scaffold with later pruning** (chosen) — ship the common set; let
  each project prune what it doesn't use.

## Consequences

- **A fresh project contains intentional unused code.** `main()` defines bootstrap
  variables that nothing consumes yet, and `src/features/` ships library feature
  classes that are not instantiated. This is deliberate, not an oversight. It
  should not be removed as reflexive cleanup — but a project may prune any of it
  deliberately if it has a reason to. See
  [project-plugin-bootstrap.md](../project-plugin-bootstrap.md) and
  [default-features.md](../default-features.md).
- **Several Composer packages are promoted to runtime `require`** by the templates
  (Fieldmanager, `traverse-reshape`, `symfony/http-foundation`, `symfony/clock`,
  `nyholm/psr7`, `wp-path-dispatch`). A project that never uses one still carries
  it until it chooses to remove it.
- **The baseline is a convention every downstream project inherits.** Changing the
  default set later does not retroactively change projects already generated from
  it.
