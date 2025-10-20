import type { Config } from 'jest';

const config: Config = {
  moduleNameMapper: {
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
  },
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['@testing-library/jest-dom'],
  testMatch: [
    '**/plugins/create-wordpress-plugin/**/__tests__/**/*.ts?(x)',
    '**/themes/create-wordpress-theme/**/__tests__/**/*.ts?(x)',
    '**/plugins/create-wordpress-plugin/**/?(*.)+(spec|test).ts?(x)',
    '**/themes/create-wordpress-theme/**/?(*.)+(spec|test).ts?(x)',
  ],
};

export default config;
