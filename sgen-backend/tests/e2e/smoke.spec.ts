import { test, expect } from '@playwright/test';

/**
 * Smoke E2E de los flujos críticos: autenticación real contra PostgreSQL,
 * navegación autenticada y módulos operativos renderizando con datos.
 */
test.describe('Smoke E2E — flujos críticos', () => {
    test('la página de login se renderiza y rechaza credenciales inválidas', async ({ page }) => {
        await page.goto('/login');

        await expect(page.getByLabel('Usuario Institucional')).toBeVisible();
        await expect(page.getByLabel('Contraseña')).toBeVisible();

        await page.getByLabel('Usuario Institucional').fill('e2e_admin');
        await page.getByLabel('Contraseña').fill('credencial-incorrecta');
        await page.getByRole('button', { name: 'Iniciar Sesión' }).click();

        // Permanece en login y muestra el error de credenciales.
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('body')).toContainText(/credenciales|incorrect|error/i);
    });

    test('login válido entra a inventario y navega a tickets', async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Usuario Institucional').fill('e2e_admin');
        await page.getByLabel('Contraseña').fill('E2e.Pass-2026*');
        await page.getByRole('button', { name: 'Iniciar Sesión' }).click();

        // AuthController redirige al inventario (rol admin).
        await expect(page).toHaveURL(/\/inventario/);
        await expect(page.locator('body')).toContainText(/inventario|almac/i);

        // Navegación a la bandeja de tickets.
        await page.goto('/soportes');
        await expect(page.locator('body')).toContainText(/ticket|soporte/i);
    });

    test('el detalle de un ticket inexistente responde 404 controlado', async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Usuario Institucional').fill('e2e_admin');
        await page.getByLabel('Contraseña').fill('E2e.Pass-2026*');
        await page.getByRole('button', { name: 'Iniciar Sesión' }).click();
        await expect(page).toHaveURL(/\/inventario/);

        const response = await page.goto('/soportes/99999999');
        expect(response?.status()).toBe(404);
    });

    test('el área de administración de usuarios está protegida por RBAC', async ({ page }) => {
        // Sin sesión: redirige a login.
        await page.goto('/usuarios');
        await expect(page).toHaveURL(/\/login/);
    });
});
