import type { Config } from 'jest';

const config: Config = {
  moduleNameMapper: {
    '@/(.*)': '<rootDir>/$1',
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
  transform: {
    '^.+\\.tsx?$': [
      'ts-jest',
      {
        tsconfig: {
          jsx: 'react',
          esModuleInterop: true,
          allowSyntheticDefaultImports: true,
        },
      },
    ],
  },
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json', 'node'],
  globals: {
    'ts-jest': {
      isolatedModules: true,
    },
  },
};

export default config;
