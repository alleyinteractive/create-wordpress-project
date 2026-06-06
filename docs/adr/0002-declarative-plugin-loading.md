---
status: accepted
---

# Load plugins declaratively through the `Feature` tree in `main()`

The project plugin loads its dependency plugins declaratively from inside
`main()`'s `Feature` tree — a `Plugin_Loader` feature composed with `Group`,
`Ordered`, and `Effect` decorators — rather than relying on wp-admin activation.
This lets each plugin be colocated with its integration feature and loaded in a
guaranteed order (e.g. load the Elasticsearch plugin, then the search
customizations that depend on it), and lets loading be conditional on environment
(e.g. the `create-block-theme` plugin only in `local`). The cost is a large
`main()` and plugin activation that lives in code rather than the admin UI.

## Considered options

- **wp-admin activation** — rejected: not version-controlled or reproducible, no
  way to express load order, env-gating, or to colocate a plugin with the
  integration code that depends on it.
- **Flat must-use loader** — this scaffold does use a mu-plugin loader, but for
  loading the project plugin itself. It can't express per-plugin ordering or
  conditional logic within the feature tree, which is what the dependency plugins
  need.
- **Declarative loading in the feature tree** (chosen).

## Consequences

- **`main()` is intentionally large**, and a reviewer will notice (PR #207 raised
  exactly this). The decorator tree (`Group`/`Ordered`/`Effect`) provides the
  logical structure in lieu of splitting the file; this is an accepted house
  pattern, not a claim that a big function is best practice.
- **Toggling a plugin means editing PHP**, not clicking in wp-admin.
- **Relies on the `alleyinteractive/wp-plugin-loader` package** (already
  required).
- **Distinct from the mu-plugin loader** that loads the project plugin itself.
  See [project-plugin-bootstrap.md](../project-plugin-bootstrap.md).
