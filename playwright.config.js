import { defineConfig, devices } from '@playwright/test';
import { existsSync } from 'node:fs';
import path from 'node:path';

const fallbackChromium = process.env.PLAYWRIGHT_EXECUTABLE_PATH || path.join(
  process.env.LOCALAPPDATA || '',
  'ms-playwright',
  'chromium_headless_shell-1169',
  'chrome-win',
  'headless_shell.exe',
);
const launchOptions = existsSync(fallbackChromium) ? { executablePath: fallbackChromium } : {};

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60_000,
  expect: { timeout: 10_000 },
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://127.0.0.1:8000',
    headless: true,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        launchOptions,
      },
    },
  ],
});
