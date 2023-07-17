import type { Config } from 'jest';

const config: Config = {
  moduleNameMapper: {
    '@/(.*)': '<rootDir>/$1',
  },
  preset: 'ts-jest',
  setupFilesAfterEnv: ['@testing-library/jest-dom/extend-expect'],
  testEnvironment: 'jsdom',
  testMatch: [
    '**/plugins/create-wordpress-plugin/**/__tests__/**/*.ts?(x)',
    '**/themes/create-wordpress-theme/**/__tests__/**/*.ts?(x)',
    '**/plugins/create-wordpress-plugin/**/?(*.)+(spec|test).ts?(x)',
    '**/themes/create-wordpress-theme/**/?(*.)+(spec|test).ts?(x)',
  ],
};

export default config;
