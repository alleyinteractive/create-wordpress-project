# TypeScript and React

ESLint (`@alleyinteractive/eslint-config/typescript-react`) and `tsc --noEmit`
enforce the mechanics. These are the conventions on top.

- **TypeScript for all new JS.** Config extends `@alleyinteractive/tsconfig`.
- **Type everything.** Use `unknown` and narrow where a type is uncertain.
  A `@ts-expect-error` carries a comment with the reason, such as
  "Provided types are inaccurate to the actual plugin API."
- **Narrow, don't cast.** Prefer a type guard or a corrected type over `as`.
  A cast that stays carries a comment saying why it is safe.
- **Function components.** Components default-export (or export by name) from a
  PascalCase file. Utility modules use named exports.
- **WordPress packages for data and UI.** `@wordpress/data` stores for state,
  `@wordpress/components` for controls. webpack externals provide `wp-*` and
  React, so the bundle stays free of them.
- **Tests sit next to the code** as `*.test.ts(x)` or in a sibling
  `__tests__/`.
- **Tests assert behavior.** Check a specific outcome, not that code ran. Do not
  repeat cases that exercise the same path, and share setup through a helper.
- **Components get an explicit props `type`** declared above the component.
