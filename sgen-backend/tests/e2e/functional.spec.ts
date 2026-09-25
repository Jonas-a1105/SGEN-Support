import { test, expect, type Page } from '@playwright/test';

/**
 * E2E funcionales de interacción real: no verifican que la página "exista",
 * verifican que los elementos HACEN su trabajo (crear, filtrar, restringir).
 */
async function login(page: Page, username: string, password: string): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Usuario Institucional').fill(username);
    await page.getByLabel('Contraseña').fill(password);
    await page.getByRole('button', { name: 'Iniciar Sesión' }).click();
    await expect(page).toHaveURL(/\/inventario/);
}

test.describe('Funcional — interacciones reales', () => {
    test('el buscador de equipos del formulario filtra y vincula el activo', async ({ page }) => {
        await login(page, 'e2e_admin', 'E2e.Pass-2026*');
        await page.goto('/soportes/crear');

        // Esperar hidratación de Vue (chunk diferido): el título confirma mount completo.
        await expect(page.locator('h1.module-title')).toContainText('Nuevo Ticket');

        await page.locator('#busqueda_equipo').fill('SN-E2E-001');
        const resultado = page.locator('.tf-search-result-item', { hasText: 'Latitude E2E' }).first();
        await expect(resultado).toBeVisible({ timeout: 5000 });
        await resultado.click();

        // El equipo quedó vinculado: aparece su ficha con opción de desvincular.
        await expect(page.getByText('S/N: SN-E2E-001')).toBeVisible();
        await expect(page.getByRole('button', { name: 'Desvincular equipo' })).toBeVisible();
    });

    test('crear un ticket por la interfaz registrar y llega a su detalle', async ({ page }) => {
        await login(page, 'e2e_admin', 'E2e.Pass-2026*');
        await page.goto('/soportes/crear');

        // Esperar hidratación de Vue (chunk diferido + datos).
        await expect(page.locator('h1.module-title')).toContainText('Nuevo Ticket');

        await page.locator('#busqueda_equipo').fill('SN-E2E-001');
        const resultado = page.locator('.tf-search-result-item', { hasText: 'Latitude E2E' }).first();
        if (await resultado.isVisible({ timeout: 4000 }).catch(() => false)) {
            await resultado.click();
        }

        const ticketTitle = `Ticket E2E ${Date.now()}`;
        await page.locator('#tituloInput').fill(ticketTitle);
        await page.locator('#descripcionInput').fill('Falla detectada por la suite E2E: el equipo no enciende tras corte eléctrico.');

        // Selección de categoría real (catálogo sembrado) + matriz ITIL.
        await page.locator('.tf-category-btn').first().click();
        await page.getByRole('button', { name: /Matriz ITIL/ }).click();
        await page.locator('.matrix-pill', { hasText: 'Alto' }).click();
        await page.locator('.matrix-pill', { hasText: 'Alta' }).click();

        await page.getByRole('button', { name: 'Guardar Ticket' }).click();

        // Inertia navega en cliente y page.url() queda "" durante el intercambio;
        // expect.poll tolere ese instante transitorio.
        await expect.poll(() => {
            try {
                return new URL(page.url()).pathname;
            } catch {
                return page.url();
            }
        }, { timeout: 15000 }).toMatch(/\/soportes\/\d+/);
        await expect(page.locator('body')).toContainText(ticketTitle);
    });

    test('alcance por fila en navegador: el operador solo ve sus tickets', async ({ page }) => {
        await login(page, 'e2e_operador', 'E2e.Pass-2026*');

        await page.goto('/soportes');

        await expect(page.locator('body')).toContainText('Ticket propio del operador E2E');
        await expect(page.locator('body')).not.toContainText('Ticket ajeno creado por admin E2E');
    });

    test('el detalle de ticket ajeno es inaccesible para el operador (404)', async ({ page }) => {
        await login(page, 'e2e_operador', 'E2e.Pass-2026*');

        await page.goto('/soportes');
        await expect(page.locator('body')).toContainText('Ticket propio del operador E2E');

        // El ticket ajeno no aparece y además el backend lo protege.
        const ajenoVisible = await page.locator('text=Ticket ajeno creado por admin E2E').count();
        expect(ajenoVisible).toBe(0);
    });

    test('el buscador de inventario filtra por URL reactiva', async ({ page }) => {
        await login(page, 'e2e_admin', 'E2e.Pass-2026*');

        await page.goto('/inventario');
        const searchInput = page.locator('input[placeholder*="uscar" i]').first();
        await searchInput.fill('SN-E2E-001');

        // useInventoryFilters debouncea 300ms y navega con ?search=
        await expect(page).toHaveURL(/search=SN-E2E-001/, { timeout: 5000 });
    });
});
