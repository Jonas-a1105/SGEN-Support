<?php

declare(strict_types=1);

namespace Tests\Unit\Department;

use Modules\Department\Application\Mappers\DepartmentDetailMapper;
use Modules\Department\Application\Mappers\DepartmentListItemMapper;
use PHPUnit\Framework\TestCase;

final class DepartmentMapperTest extends TestCase
{
    public function test_department_list_item_mapper_computes_fields_correctly(): void
    {
        $row = (object) [
            'id' => 4,
            'nombre' => 'Dpto. de Contabilidad',
            'ubicacion' => 'Edificio Central, Piso 3',
            'jefe_area_nombre' => 'Abigail Chirinos',
            'jefe_area_id' => 5,
            'descripcion' => 'Gestión de contabilidad',
            'equipos_count' => 41,
            'empleados_count' => 4,
        ];

        $dto = DepartmentListItemMapper::fromRow($row);

        $this->assertSame(4, $dto->numericId);
        $this->assertSame('dept_04', $dto->id);
        $this->assertSame('DEPT-04', $dto->code);
        $this->assertSame('Dpto. de Contabilidad', $dto->name);
        $this->assertSame('Edificio Central, Piso 3', $dto->location);
        $this->assertSame('Abigail Chirinos', $dto->manager);
        $this->assertSame(41, $dto->equipos);
        $this->assertSame(4, $dto->empleados);
        $this->assertNotEmpty($dto->color);
    }

    public function test_department_detail_mapper_computes_fields_correctly(): void
    {
        $row = (object) [
            'id' => 5,
            'nombre' => 'Dpto. de control de calidad',
            'ubicacion' => 'Planta de Producción',
            'jefe_area_nombre' => 'Ing. Carlos Ortiz',
            'jefe_area_id' => 8,
            'descripcion' => 'Aseguramiento de calidad',
            'equipos_count' => 8,
            'empleados_count' => 3,
        ];

        $dto = DepartmentDetailMapper::fromRow($row);

        $this->assertSame(5, $dto->id);
        $this->assertSame('DEPT-05', $dto->code);
        $this->assertSame('Dpto. de control de calidad', $dto->nombre);
        $this->assertSame('Ing. Carlos Ortiz', $dto->jefeAreaNombre);
        $this->assertSame(8, $dto->equiposCount);
        $this->assertSame(3, $dto->empleadosCount);
    }
}
