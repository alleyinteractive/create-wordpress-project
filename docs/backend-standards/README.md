# Backend Standards

These files describe how PHP is written in this project. They hold the
conventions a config cannot state: how to shape a Feature, what to check before
trusting a value, and what to keep off the hot path. Rules that PHPCS
(`Alley-Interactive`) and PHPStan (`level: max`) enforce live in `.phpcs.xml`
and `phpstan.neon`. Read those, not a copy here.

Each file covers one branch of work. Load only the one the task needs.

| Task | Read |
| --- | --- |
| Adding or changing a Feature class, or deciding whether to build one | [features.md](features.md) |
| Reading meta, options, REST, or request data; anything that changes data | [data-safety.md](data-safety.md) |
| Queries, remote calls, hooks that run on every request | [performance.md](performance.md) |
| Writing PHP tests | [testing.md](testing.md) |

Where the code lives (plugin vs theme, directories, naming) is in
[../repo-layout.md](../repo-layout.md). For
the project's vocabulary, see [CONTEXT.md](../CONTEXT.md). For what the project
plugin ships with, see [default-features.md](../default-features.md).

## Comments

Say why, never what. See [../comments.md](../comments.md). In PHP that means a
docblock that states the contract (parameters, return, when it fires) and an
inline comment only for a non-obvious constraint.

## Maintaining these files

A rule belongs here when it is a convention, is not enforced by tooling, and
would surprise a newcomer. When tooling can enforce a rule, enforce it and
delete the prose. Keep each fact in one file. When you change a convention,
update the file in the same PR.
