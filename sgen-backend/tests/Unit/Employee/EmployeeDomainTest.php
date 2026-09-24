<?php

declare(strict_types=1);

namespace Tests\Unit\Employee;

use InvalidArgumentException;
use Modules\Employee\Domain\Enums\EmployeeRole;
use Modules\Employee\Domain\Models\Employee;
use PHPUnit\Framework\TestCase;

final class EmployeeDomainTest extends TestCase
{
    public function test_can_instantiate_employee_with_valid_invariants(): void
    {
        $employee = new Employee(
            id: 1,
            nombre: 'Alexis',
            apellido: 'Datica',
            email: 'alexis.datica@empresa.com',
            cedula: 'V-18.765.432',
            cargo: 'Soporte TI',
            departamentoId: 2,
            rol: EmployeeRole::TECNICO,
            usuarioId: 10
        );

        $this->assertSame(1, $employee->id());
        $this->assertSame('Alexis', $employee->nombre());
        $this->assertSame('Datica', $employee->apellido());
        $this->assertSame('Alexis Datica', $employee->fullName());
        $this->assertSame('alexis.datica@empresa.com', $employee->email());
        $this->assertSame('V-18.765.432', $employee->cedula());
        $this->assertSame('Soporte TI', $employee->cargo());
        $this->assertSame(2, $employee->departamentoId());
        $this->assertSame(EmployeeRole::TECNICO, $employee->rol());
        $this->assertSame(10, $employee->usuarioId());
        $this->assertTrue($employee->hasUserAccount());
    }

    public function test_throws_exception_on_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Employee(
            id: 1,
            nombre: '   ',
            apellido: 'Datica',
            email: 'alexis@empresa.com'
        );
    }

    public function test_throws_exception_on_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Employee(
            id: 1,
            nombre: 'Alexis',
            apellido: 'Datica',
            email: 'not-an-email'
        );
    }

    public function test_can_link_and_unlink_user_account(): void
    {
        $employee = new Employee(
            id: 1,
            nombre: 'Alexis',
            apellido: 'Datica',
            email: 'alexis@empresa.com'
        );

        $this->assertFalse($employee->hasUserAccount());

        $employee->linkUserAccount(5);
        $this->assertTrue($employee->hasUserAccount());
        $this->assertSame(5, $employee->usuarioId());

        $employee->unlinkUserAccount();
        $this->assertFalse($employee->hasUserAccount());
        $this->assertNull($employee->usuarioId());
    }
}
