<?php

declare(strict_types=1);

namespace Tests\Unit\Equipment;

use InvalidArgumentException;
use Modules\Equipment\Domain\Enums\EquipmentStatus;
use Modules\Equipment\Domain\Enums\EquipmentType;
use Modules\Equipment\Domain\Models\Equipment;
use PHPUnit\Framework\TestCase;

final class EquipmentDomainTest extends TestCase
{
    public function test_can_instantiate_equipment_with_valid_invariants(): void
    {
        $equipment = new Equipment(
            id: 1,
            inventoryCode: '00271',
            serialNumber: 'SN-12345',
            type: EquipmentType::COMPUTADORA,
            brand: 'Intel',
            model: 'N5095',
            status: EquipmentStatus::DISPONIBLE
        );

        $this->assertSame(1, $equipment->id());
        $this->assertSame('00271', $equipment->inventoryCode());
        $this->assertSame('SN-12345', $equipment->serialNumber());
        $this->assertSame(EquipmentType::COMPUTADORA, $equipment->type());
        $this->assertSame(EquipmentStatus::DISPONIBLE, $equipment->status());
        $this->assertTrue($equipment->isOperational());
    }

    public function test_throws_exception_on_empty_inventory_code(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Equipment(
            id: 1,
            inventoryCode: '',
            serialNumber: 'SN-12345',
            type: EquipmentType::COMPUTADORA,
            brand: 'Intel',
            model: 'N5095',
            status: EquipmentStatus::DISPONIBLE
        );
    }

    public function test_throws_exception_on_empty_serial_number(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Equipment(
            id: 1,
            inventoryCode: '00271',
            serialNumber: '   ',
            type: EquipmentType::COMPUTADORA,
            brand: 'Intel',
            model: 'N5095',
            status: EquipmentStatus::DISPONIBLE
        );
    }

    public function test_assigns_department_and_employee(): void
    {
        $equipment = new Equipment(
            id: 1,
            inventoryCode: '00271',
            serialNumber: 'SN-12345',
            type: EquipmentType::COMPUTADORA,
            brand: 'Intel',
            model: 'N5095',
            status: EquipmentStatus::DISPONIBLE
        );

        $equipment->assignToDepartment(5, 'Oficina 201');
        $this->assertSame(5, $equipment->departmentId());
        $this->assertSame('Oficina 201', $equipment->physicalLocation());

        $equipment->assignToEmployee(10);
        $this->assertSame(10, $equipment->employeeId());
        $this->assertSame(EquipmentStatus::EN_USO, $equipment->status());
    }

    public function test_change_status_updates_operational_state(): void
    {
        $equipment = new Equipment(
            id: 1,
            inventoryCode: '00271',
            serialNumber: 'SN-12345',
            type: EquipmentType::COMPUTADORA,
            brand: 'Intel',
            model: 'N5095',
            status: EquipmentStatus::EN_USO,
            employeeId: 10
        );

        $this->assertTrue($equipment->isOperational());

        $equipment->changeStatus(EquipmentStatus::EN_REPARACION);
        $this->assertFalse($equipment->isOperational());

        $equipment->changeStatus(EquipmentStatus::FUERA_DE_SERVICIO);
        $this->assertFalse($equipment->isOperational());
        $this->assertNull($equipment->employeeId());
    }
}
