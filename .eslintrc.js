module.exports = {
  root: true,
  extends: ['@alleyinteractive/eslint-config/typescript-react'],
  parserOptions: {
    project: './tsconfig.eslint.json',
    tsconfigRootDir: __dirname,
  },
};
