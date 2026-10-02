import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    use: { baseURL: 'http://127.0.0.1:8000', browserName: 'chromium' },
    workers: 1,
    projects: [
        { name: 'small-phone', use: { viewport: { width: 320, height: 780 } } },
        { name: 'desktop', use: { viewport: { width: 1440, height: 1000 } } },
        { name: 'mobile', use: { viewport: { width: 390, height: 844 } } },
    ],
});
