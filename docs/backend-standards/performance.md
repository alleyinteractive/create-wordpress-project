# Performance

Most PHP slowness here comes from work that runs when it does not need to. Ask
of every hook and query: does this run on every request, and does it have to?

## Scope the work

- **Register a hook only where it applies.** Limit it to the post types,
  screens, and queries that use it. Check the context first and return early.
  `Features\Effect` gates a whole Feature by environment.
- **Know which query you are in.** Filters such as `pre_get_posts` run for every
  query. Use the injected main query (`Global_Post_Query`) to act only on it.
- **Do not repeat work inside a loop.** Hoist anything that does not change per
  item, and avoid a query or remote call per item (N+1). Fetch in one call and
  index the result.

## Cache what is expensive

- Cache expensive queries and remote requests in the object cache
  (`wp_cache_get` / `wp_cache_set`) with a key that includes what varies and an
  expiry. `Load_Entries` takes a `cache` argument and is cached outside local.
- Invalidate when the source changes, on the hook that changes it, not on a
  timer alone.
- Do not cache what is cheap or per-user unless the key says so.

## Heavy and long-running tasks

Batch large jobs, bound memory (page through results, free what you are done
with), and run them in WP-CLI or a scheduled event, not in a web request.
`WP_CLI_Feature` wraps a command in a Feature, as `Reset_Theme_Command` does.
