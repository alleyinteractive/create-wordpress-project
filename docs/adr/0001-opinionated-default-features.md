---
status: accepted
---

# Ship opinionated default functionality, accepting some unused code

The project plugin starts with a broad set of feature classes and shared
bootstrap variables, instead of a minimal blank slate. These come from
patterns that repeat across mature Alley projects. We chose to ship the
commonly-needed code so every project starts from the same baseline. A
developer can then use an existing implementation instead of building a new
one. We accept that some shipped code goes unused on day one.

We selected the code by analyzing the `main()` functions of five mature
Alley publisher sites and keeping what recurred. The sample is all mature
sites. This means the defaults match what projects need over time, not
strictly what they need on day one. We accept this bias.

## Considered options

- **Minimal scaffold.** Ship only universally-needed code and add the rest
  per project. Rejected. This forces every team to build the same features
  independently and inconsistently. That is the problem this decision
  solves.
- **Inclusive scaffold with later pruning.** Chosen. Ship the common set.
  Let each project remove what it does not use.

## Consequences

- **A fresh project contains unused code on purpose.** `main()` defines
  bootstrap variables that nothing consumes yet. `src/features/` ships
  library feature classes that `main()` does not instantiate. This is
  deliberate, not an oversight. Do not remove it as routine cleanup. A
  project may remove any of it for a specific reason. See
  [project-plugin-bootstrap.md](../project-plugin-bootstrap.md) and
  [default-features.md](../default-features.md).
- **The templates promote several Composer packages to runtime `require`.**
  These are Fieldmanager, `traverse-reshape`, `symfony/http-foundation`,
  `symfony/clock`, `nyholm/psr7`, and `wp-path-dispatch`. A project that
  never uses one of these still carries it until it removes it.
- **The baseline is a convention every new project inherits.** A later
  change to the default set does not change projects already generated
  from it.
