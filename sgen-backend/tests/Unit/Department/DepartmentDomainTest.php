<?php

declare(strict_types=1);

namespace Tests\Unit\Department;

use InvalidArgumentException;
use Modules\Department\Domain\Models\Department;
use PHPUnit\Framework\TestCase;

final class DepartmentDomainTest extends TestCase
{
    public function test_can_instantiate_department_with_valid_invariants(): void
    {
        $dept = new Department(
            id: 1,
            nombre: 'Dpto. de Contabilidad',
            ubicacion: 'Edificio Central, Piso 3',
            jefeAreaNombre: 'Abigail Chirinos',
            jefeAreaId: 5,
            descripcion: 'Gestión contable'
        );

        $this->assertSame(1, $dept->id());
        $this->assertSame('Dpto. de Contabilidad', $dept->nombre());
        $this->assertSame('Edificio Central, Piso 3', $dept->ubicacion());
        $this->assertSame('Abigail Chirinos', $dept->jefeAreaNombre());
        $this->assertSame(5, $dept->jefeAreaId());
        $this->assertSame('Gestión contable', $dept->descripcion());
    }

    public function test_throws_exception_on_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Department(
            id: 1,
            nombre: '   '
        );
    }

    public function test_can_assign_and_clear_manager(): void
    {
        $dept = new Department(
            id: 1,
            nombre: 'Dpto. de Electricidad'
        );

        $this->assertNull($dept->jefeAreaNombre());

        $dept->assignManager('Alexis Datica', 38);
        $this->assertSame('Alexis Datica', $dept->jefeAreaNombre());
        $this->assertSame(38, $dept->jefeAreaId());

        $dept->clearManager();
        $this->assertNull($dept->jefeAreaNombre());
        $this->assertNull($dept->jefeAreaId());
    }
}
