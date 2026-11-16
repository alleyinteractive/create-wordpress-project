# Repo Layout

## Ownership

The **project plugin** owns behavior and anything editable: blocks, slotfills,
meta, REST fields, and Feature classes. The **theme** owns presentation:
`theme.json`, templates, parts, patterns, and style overrides for core blocks.

Decision rule: a new block goes in the plugin. Styling for an existing block
goes in the theme. The theme registers no blocks; an exception needs a written
reason in the PR. Even theme-flavored blocks (navigation, primary term, search
meta) live in the plugin, which keeps the theme swappable.

## Monorepo

```
themes/<name>/
plugins/<name>/
packages/*          # shared TypeScript
```

npm workspaces with Turbo. The root owns the lint, TypeScript, and Jest
configs; workspaces extend them without local rule overrides. A local override
carries a comment saying why.

## Plugin and theme structure

The plugin and theme start from
[create-wordpress-plugin](https://github.com/alleyinteractive/create-wordpress-plugin)
and
[create-wordpress-theme](https://github.com/alleyinteractive/create-wordpress-theme).
Their READMEs and `CLAUDE.md` files describe the directory structure, so it is
not repeated here. Read those for what goes where inside each one.

Setup adds to the plugin: the Feature classes in `src/features/` (see
[default-features.md](default-features.md)), `config/` (`post-meta.json`,
`term-meta.json`), and starter `blocks/` and `entries/`. Document anything the
base plugin does not define here, not in those repos.

## Naming

- Directories and files: lowercase kebab-case.
- PHP class files: WordPress `class-{slug}.php`.
- React component files: PascalCase (`FeaturedImageCaption.tsx`).
- Other TS modules: kebab-case (`autosubmit.ts`, `format-date.ts`).
- Script and style handles: `<slug>-<entry>-js` and `<slug>-<entry>-css`.

## Build output

`alley-build` produces `build/`. Output is never committed to main; it ships
through the built-release workflow (`npx @alleyinteractive/create-release`).

## Scaffold first

Generate with `npm run create-block`, `create-entry`, `create-slotfill`, or
`scaffold`. When scaffold output disagrees with these docs, fix the scaffold.
