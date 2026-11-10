# Testing

A test earns its place by failing when behavior breaks.

- **Assert specific behavior.** Check the outcome a user or caller sees: the
  returned value, the saved meta, the rendered markup. A test that only runs the
  code and asserts nothing specific does not count.
- **Cover the unhappy paths.** For every Feature that reads data, test the empty
  and missing cases and the early return as well as the happy path
  ([data-safety.md](data-safety.md)).
- **Do not repeat a case.** Two tests that exercise the same branch with
  different values add noise. Pick the one that names the behavior.
- **Share setup through a helper** or a data provider, not copy-pasted blocks.
- **Inject, don't fake globals.** A Feature that takes its dependencies
  ([features.md](features.md#pass-dependencies-in)) is tested by passing a
  controlled one, such as a fixed clock.

Frontend tests: [typescript.md](../frontend-standards/typescript.md).
