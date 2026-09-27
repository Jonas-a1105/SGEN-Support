import { test, expect, type Page } from '@playwright/test';

async function login(page: Page, user = 'e2e_admin', pass = 'E2e.Pass-2026*'): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Usuario Institucional').fill(user);
    await page.getByLabel('Contraseña').fill(pass);
    await page.getByRole('button', { name: 'Iniciar Sesión' }).click();

    // Lo que esperamos: quedar autenticado. La pantalla final depende de si el
    // usuario visita por primera vez o si es tiempo técnicamente obligatorio.
    await page.waitForLoadState('networkidle');
}

const capture = async (page: Page, path: string, name: string): Promise<void> => {
    await page.goto(path);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `tests/e2e/ux-${name}.png`, fullPage: true });
};

test.describe('Auditoría visual UI (PNG que devuelven la verdad)', () => {
    test('capturas de módulos principales tras login admin', async ({ page }) => {
        await login(page);

        await capture(page, '/dashboard', 'dashboard');
        await capture(page, '/inventario', 'inventario');
        await capture(page, '/equipos', 'equipos');
        await capture(page, '/soportes', 'soportes');
        await capture(page, '/mantenimientos', 'mantenimientos');
        await capture(page, '/personal', 'personal');
        await capture(page, '/roles', 'roles');
        await capture(page, '/papelera', 'papelera');
        await capture(page, '/configuracion', 'configuracion');
        await capture(page, '/configuracion/sistema', 'configuracion-admin');
    });
});
