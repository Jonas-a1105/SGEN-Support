import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const dbEnv = {
    ...process.env,
    DB_CONNECTION: process.env.DB_CONNECTION ?? 'pgsql',
    DB_DATABASE: process.env.DB_DATABASE_TESTING ?? 'testing',
};

/**
 * Prepara la base de datos de E2E (conexión `testing`) con el esquema
 * migrado, el catálogo RBAC y el usuario administrador determinista.
 */
export default function globalSetup(): void {
    const backend = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');
    const opts = { cwd: backend, env: dbEnv, stdio: 'inherit' as const };

    execSync('php artisan migrate --force', opts);
    execSync('php artisan db:seed --class="Database\\Seeders\\E2ESeeder" --force', opts);
}
