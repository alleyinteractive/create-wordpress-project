---
status: accepted
---

# Load plugins declaratively through the `Feature` tree in `main()`

The project plugin loads its dependency plugins from inside `main()`'s
`Feature` tree, instead of relying on wp-admin activation. A `Plugin_Loader`
feature, composed with `Group`, `Ordered`, and `Effect` decorators, does the
loading. This lets a plugin sit next to its integration feature and load in
a guaranteed order. For example, `main()` loads the Elasticsearch plugin,
then the search customizations that depend on it. It also lets loading
depend on environment. For example, `main()` loads `create-block-theme` only
in `local`. The cost: `main()` grows large, and plugin activation lives in
code instead of the admin UI.

## Considered options

- **wp-admin activation.** Rejected. wp-admin activation is not
  version-controlled or reproducible. It cannot express load order or
  environment gating. It cannot place a plugin next to the integration code
  that depends on it.
- **Flat must-use loader.** This scaffold does use a mu-plugin loader, but
  only to load the project plugin itself. A flat loader cannot express
  per-plugin ordering or conditional logic within the feature tree. The
  dependency plugins need both.
- **Declarative loading in the feature tree.** Chosen.

## Consequences

- **`main()` is large on purpose.** A reviewer will notice this. The decorator
  tree of `Group`, `Ordered`, and `Effect` gives the file logical structure instead
  of splitting it into smaller files. This is an accepted house pattern.
  It is not a claim that a large function is a best practice.
- **Toggling a plugin means editing PHP**, not clicking in wp-admin.
- **This depends on the `alleyinteractive/wp-plugin-loader` package.** The
  project plugin already requires it.
- **This is separate from the mu-plugin loader** that loads the project
  plugin itself. See
  [project-plugin-bootstrap.md](../project-plugin-bootstrap.md).
