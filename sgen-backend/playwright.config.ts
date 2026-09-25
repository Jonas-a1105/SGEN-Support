import { defineConfig, devices } from '@playwright/test';

/**
 * Suite E2E: navegador real contra `php artisan serve` apuntando a la base
 * de datos `testing` (aislada de desarrollo). La autenticación usa el usuario
 * sembrado por Database\Seeders\E2ESeeder.
 */
export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/global-setup.ts',
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    timeout: 30_000,
    reporter: process.env.CI ? [['github'], ['list']] : 'list',
    use: {
        baseURL: 'http://127.0.0.1:8899',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        command: 'php artisan serve --port=8899 --no-reload',
        url: 'http://127.0.0.1:8899/login',
        timeout: 120_000,
        reuseExistingServer: !process.env.CI,
        env: {
            ...process.env,
            APP_ENV: 'testing',
            DB_CONNECTION: process.env.DB_CONNECTION ?? 'pgsql',
            DB_DATABASE: process.env.DB_DATABASE_TESTING ?? 'testing',
            CACHE_STORE: 'file',
            SESSION_DRIVER: 'file',
            QUEUE_CONNECTION: 'sync',
        },
    },
});
