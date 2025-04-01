module.exports = {
  root: true,
  extends: ['@alleyinteractive/eslint-config/typescript-react'],
  parserOptions: {
    project: true,
    tsconfigRootDir: __dirname,
  },
  rules: {
    'import/no-extraneous-dependencies': [0],
  },
};
