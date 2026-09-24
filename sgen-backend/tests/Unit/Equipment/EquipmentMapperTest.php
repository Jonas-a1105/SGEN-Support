<?php

declare(strict_types=1);

namespace Tests\Unit\Equipment;

use Modules\Equipment\Application\Mappers\EquipmentDetailMapper;
use Modules\Equipment\Application\Mappers\EquipmentListItemMapper;
use PHPUnit\Framework\TestCase;

final class EquipmentMapperTest extends TestCase
{
    public function test_equipment_list_item_mapper_transforms_row_correctly(): void
    {
        $row = (object) [
            'id' => 271,
            'codigo_inventario' => '00271',
            'marca' => 'Intel',
            'modelo' => 'N5095',
            'tipo' => 'Computadora',
            'estado' => 'en_uso',
            'departamento_nombre' => 'Dpto. de Recursos Humanos',
            'ubicacion_fisica' => 'Edificio Central',
            'empleado_nombre' => 'Alexis',
            'empleado_apellido' => 'Datica',
            'numero_serie' => 'SN-998877',
            'direccion_ip' => '192.168.1.50',
        ];

        $dto = EquipmentListItemMapper::fromRow($row);

        $this->assertSame(271, $dto->numericId);
        $this->assertSame('00271', $dto->id);
        $this->assertSame('Intel N5095', $dto->name);
        $this->assertSame('Computadora', $dto->type);
        $this->assertSame('En Uso', $dto->status);
        $this->assertSame('en_uso', $dto->rawStatus);
        $this->assertSame('Dpto. de Recursos Humanos', $dto->dept);
        $this->assertSame('Alexis Datica', $dto->assignedTo);
        $this->assertSame('SN-998877', $dto->serialNumber);
    }

    public function test_equipment_detail_mapper_transforms_row_correctly(): void
    {
        $row = (object) [
            'id' => 272,
            'codigo_inventario' => '00272',
            'numero_serie' => 'SN-EPSON-123',
            'marca' => 'EPSON',
            'modelo' => 'L5590',
            'tipo' => 'Impresora',
            'estado' => 'disponible',
            'departamento_id' => 4,
            'departamento_nombre' => 'Dpto. de Contabilidad',
            'empleado_id' => null,
            'empleado_nombre' => null,
            'empleado_apellido' => null,
            'ubicacion_fisica' => 'Piso 3',
            'procesador' => null,
            'memoria_ram' => null,
            'almacenamiento' => null,
            'sistema_operativo' => null,
            'direccion_ip' => '192.168.1.100',
            'driver' => 'Epson Universal Driver',
            'toner' => 'T504',
            'fecha_compra' => '2025-01-15',
            'proveedor' => 'CompuMall',
            'garantia' => '2026-01-15',
            'valor_compra' => 350.00,
        ];

        $dto = EquipmentDetailMapper::fromRow($row);

        $this->assertSame(272, $dto->id);
        $this->assertSame('EPSON L5590', $dto->name);
        $this->assertSame('Impresora', $dto->type);
        $this->assertSame('Disponible', $dto->status);
        $this->assertSame('Dpto. de Contabilidad', $dto->departmentName);
        $this->assertSame('192.168.1.100', $dto->ipAddress);
        $this->assertSame(350.00, $dto->purchaseValue);
    }
}
