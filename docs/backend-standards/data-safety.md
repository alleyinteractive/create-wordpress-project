# Data Safety

PHPCS enforces output escaping, and PHPStan `level: max` catches type errors it
can see. Neither knows what arrives at runtime or who may act. These are the
checks to make by hand.

## Validate before you use

Treat anything from post meta, options, term meta, REST, query vars, block
attributes, and block context as possibly missing or the wrong type. Check for
null, empty, and non-array, handle the empty case, then use it. Return early on
bad input.

Example:

```php
$post_id = is_numeric( $instance->context['postId'] ?? null )
	? (int) $instance->context['postId']
	: 0;
if ( $post_id === 0 ) {
	return $block_content;
}
```

- Do not cast to silence a type error. Narrow the value, or return.
- `get_post_meta()` and `get_option()` return `''` or `false` for missing data.
  Do not assume a string or an array.
- An empty result is a state to handle, not an error to ignore. Decide what the
  page or response does when there is nothing to show.

## Authorize, then act

Anything that changes data checks both who and why:

- **Capability.** `current_user_can()` with the narrowest capability that fits.
  Admin pages and settings pass a `capability` (as `Site_Settings_Page` does).
- **Nonce.** Verify one on every request that changes state.
- **POST for side effects.** Requests that change or delete data go through
  POST, sent from a form or from JavaScript, so crawlers, prefetching, and link
  previews cannot trigger them.
- **Trust boundary.** Check the request on the server. A client-side check is
  not authorization.

## Sanitize when you register

Register meta and options with a `sanitize_callback` and a `type`, as `Term_Dates`
does, so every write goes through it. Set `show_in_rest` deliberately; say why in
the PR when it is true for sensitive data.

## Secrets

No keys, tokens, or placeholder credentials in the repo, including tests and
fixtures. Read them from the environment or a constant the host provides.
