<?php

declare(strict_types=1);

namespace Tests\Unit\Employee;

use Modules\Employee\Application\Mappers\EmployeeDetailMapper;
use Modules\Employee\Application\Mappers\EmployeeListItemMapper;
use PHPUnit\Framework\TestCase;

final class EmployeeMapperTest extends TestCase
{
    public function test_employee_list_item_mapper_computes_fields_correctly(): void
    {
        $row = (object) [
            'id' => 1,
            'nombre' => 'Alexis',
            'apellido' => 'Datica',
            'email' => 'alexis@empresa.com',
            'cargo' => 'Soporte TI',
            'cedula' => 'V-12345678',
            'departamento_id' => 2,
            'departamento_nombre' => 'Dpto. de Informática',
            'username' => 'alexisd',
            'rol' => 'tecnico',
        ];

        $dto = EmployeeListItemMapper::fromRow($row);

        $this->assertSame(1, $dto->numericId);
        $this->assertSame('EMP-01', $dto->id);
        $this->assertSame('Alexis Datica', $dto->fullName);
        $this->assertSame('AD', $dto->initials);
        $this->assertSame('@alexisd', $dto->userAccount);
        $this->assertSame('Dpto. de Informática', $dto->dept);
        $this->assertSame('V-12345678', $dto->idDoc);
    }

    public function test_employee_detail_mapper_computes_fields_correctly(): void
    {
        $row = (object) [
            'id' => 2,
            'nombre' => 'Ana',
            'apellido' => 'García',
            'email' => 'ana.garcia@empresa.com',
            'cargo' => 'Gerente de Ventas',
            'cedula' => 'V-87654321',
            'departamento_id' => 1,
            'departamento_nombre' => 'Ventas',
            'usuario_id' => null,
            'username' => null,
            'rol' => 'consultor',
        ];

        $dto = EmployeeDetailMapper::fromRow($row);

        $this->assertSame(2, $dto->id);
        $this->assertSame('EMP-02', $dto->formattedId);
        $this->assertSame('Ana García', $dto->fullName);
        $this->assertSame('AG', $dto->initials);
        $this->assertNull($dto->usuarioId);
        $this->assertNull($dto->username);
    }
}
