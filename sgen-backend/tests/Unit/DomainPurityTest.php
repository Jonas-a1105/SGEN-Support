<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Regla arquitectónica ejecutable: la capa Domain de los módulos es NÚCLEO
 * PURO — no puede conocer framework (Illuminate), entrega (App\Http) ni
 * persistencia (DB::, Eloquent Model::). Si este test falla, el hexágono
 * se rompió y CI lo bloquea.
 *
 * Convention over configuration: en lugar de deptrac, un test de arquitectura
 * declarativo que todo desarrollador puede leer sin aprender una herramienta.
 */
final class DomainPurityTest extends TestCase
{
    private const MODULES_DOMAIN_PATH = __DIR__.'/../../src/Modules';

    private const FORBIDDEN_IN_DOMAIN = [
        'use Illuminate\\',
        'use App\\',
        'DB::',
        'Log::',
    ];

    public function test_la_capa_domain_no_depende_del_framework(): void
    {
        $violations = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::MODULES_DOMAIN_PATH, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if ($file->getExtension() !== 'php' || ! str_contains($path, DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR)) {
                continue;
            }
            // Las carpetas Domain/Infrastructure de un módulo pertenecen a otra capa.
            if (str_contains($path, DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR.'Infrastructure')) {
                continue;
            }

            $contents = (string) file_get_contents($path);

            foreach (self::FORBIDDEN_IN_DOMAIN as $forbidden) {
                if (str_contains($contents, $forbidden)) {
                    $violations[] = sprintf('%s contiene referencia prohibida "%s"', $path, $forbidden);
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            "La capa Domain debe permanecer pura (sin framework):\n".implode("\n", $violations)
        );
    }

    public function test_todos_los_use_cases_de_dominio_exponen_contratos_por_interfaz(): void
    {
        // Los puertos deben ser interfaces, jamás clases concretas.
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::MODULES_DOMAIN_PATH, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $violations = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if ($file->getExtension() !== 'php'
                || ! str_contains($path, DIRECTORY_SEPARATOR.'Domain'.DIRECTORY_SEPARATOR.'Ports'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = (string) file_get_contents($path);

            if (! str_contains($contents, 'interface ') && ! str_contains($contents, 'enum ')) {
                $violations[] = "El puerto {$path} no es una interfaz";
            }
        }

        $this->assertSame([], $violations, "Todos los contratos en Domain/Ports deben ser interfaces:\n".implode("\n", $violations));
    }
}
