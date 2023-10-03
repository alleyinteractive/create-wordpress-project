# Packages

The packages folder should contain workspaces for shared code used by a plugin, theme, or another package.

The directories in this folder should contain a `package.json` file in order to be recognized in the NPM monorepo with Turborepo.

## Generating a new package

> Code splitting the WordPress project into individual workspaces is a great way to organize your code, speed up tasks, and improve the local development experience. With Turborepo's code generation, it's easy to generate new source code for packages, modules, and even individual UI components in a structured way that integrates with the rest of your repository.

-- https://turbo.build/repo/docs/core-concepts/monorepos/code-generation

### Generate a new workspace package.

Add a new, empty package to you the workspace.

A script is provided in the root `package.json` file that will generate a new workspace package in the `packages` folder. You will be prompted to enter the name of the new package.

```
npm run create-package
```
A package directory will be created in the `packages` folder with the following base structure:

```
├── packages
│   └── package-name
│       ├── package.json
│       └── README.md
```

All packages created in the package folder will be automatically added to the `package.json` workspaces array in the root of the repository.
