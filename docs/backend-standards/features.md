# Features

A Feature is the unit of PHP behavior in the project plugin. `main()` builds
the shared dependencies once and composes the Features. See
[project-plugin-bootstrap.md](../project-plugin-bootstrap.md) for how `main()`
is structured and [CONTEXT.md](../CONTEXT.md) for wired vs library features.

## Check before you build

In this order, and say in the PR when none fit:

1. [default-features.md](../default-features.md): the scaffold may already ship
   it, wired or as a library feature to wire up.
2. WordPress core: an API, a block, a filter, or the WP HTML API.
3. Mantle or another Alley package already in `composer.json`.
4. A helper already in this repo.

Block markup edits: see [blocks.md](../frontend-standards/blocks.md#renderphp).

## Shape

```php
final readonly class Term_Dates implements Feature {
	public function __construct(
		private ClockInterface $clock,
	) {}

	public function boot(): void {
		add_action( 'init', $this->on_init( ... ) );
	}
}
```

- `final readonly class <Name> implements Feature`, in
  `src/features/class-<slug>.php`, namespaced `<Plugin>\Features`.
- A single `boot()` registers every hook. Register them there, not in the
  constructor.
- Hook callbacks are public methods named `on_<hook>` for actions and
  `filter_<hook>` for filters, passed as first-class callables:
  `$this->on_init( ... )`. (The theme names its functions `action__*` and
  `filter__*`; see [theme.md](../frontend-standards/theme.md#theme-php).)
- A docblock on each callback says when it fires and what it does.

## Pass dependencies in

`main()` creates the clock, request, main query, block registry, and settings
once. A Feature takes what it needs through its constructor, as `Term_Dates`
takes a `ClockInterface`: collaborators, the injected main query in place of
`$wp_query`, and option names all arrive as arguments. A Feature that takes its
inputs is the one you can test without WordPress state.

Make a Feature conditional with `Effect`, not an `if` inside `boot()`:

```php
new Effect(
	when: fn (): bool => $environment_type === 'local',
	then: new Features\Plugin_Loader( plugins: [ '…' ] ),
)
```

## Keep it small

[CLAUDE.md](../../CLAUDE.md) sets the bar. In PHP:

- Extract on the second repeat. A repeated block becomes a method or a small
  class.
- A literal that carries meaning becomes a class constant, a setting, or a value
  derived from its source. Do not repeat the same string in two places.
- Move logic beyond about 20 lines out of a template or callback into a class.

## Naming

Follow the naming already nearby. Classes are `Snake_Case`. Meta keys, option
names, and handles carry the plugin prefix
(`create_wordpress_plugin_featured_image_caption`). File names are in
[repo-layout.md](../repo-layout.md#naming).
