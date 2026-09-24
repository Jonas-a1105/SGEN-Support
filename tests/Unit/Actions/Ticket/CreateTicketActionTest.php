<?php
/**
 * Tests para CreateTicketAction - Ejemplo de testabilidad
 * Ejecutar: ./vendor/bin/pest tests/Unit/Actions/Ticket/CreateTicketActionTest.php
 */

use App\Actions\Ticket\CreateTicketAction;
use App\DTO\Ticket\CreateTicketDTO;
use App\Models\Equipo;
use App\Models\Soporte;
use App\Services\TicketService;
use App\Exceptions\ValidationException;
use App\Exceptions\ForbiddenException;
use PHPUnit\Framework\MockObject\MockObject;

it('crea ticket exitosamente con datos válidos', function () {
    // Arrange
    $ticketService = mock(TicketService::class);
    $equipoModel = mock(Equipo::class);
    $soporteModel = mock(Soporte::class);

    $equipoModel->shouldReceive('findById')
        ->with(1)
        ->andReturn((object)['id' => 1, 'departamento_id' => 5]);

    $ticketService->shouldReceive('validarEquipoDepartamento')
        ->with(1, 5)
        ->andReturn(true);

    $ticketService->shouldReceive('crearTicket')
        ->once()
        ->andReturn(42);

    $action = new CreateTicketAction($ticketService, $soporteModel, $equipoModel);
    $dto = new CreateTicketDTO(
        equipoId: 1,
        descripcion: 'Problema con la impresora',
        prioridad: 'alta',
        categoriaId: 3,
        userId: 10,
        departamentoId: 5
    );

    // Act
    $ticketId = $action->execute($dto);

    // Assert
    expect($ticketId)->toBe(42);
});

it('lanza ValidationException si equipo no existe', function () {
    $ticketService = mock(TicketService::class);
    $equipoModel = mock(Equipo::class);
    $soporteModel = mock(Soporte::class);

    $equipoModel->shouldReceive('findById')
        ->with(999)
        ->andReturn(null);

    $action = new CreateTicketAction($ticketService, $soporteModel, $equipoModel);
    $dto = new CreateTicketDTO(
        equipoId: 999,
        descripcion: 'Test',
        prioridad: 'media',
        categoriaId: null,
        userId: 1
    );

    $action->execute($dto);
})->throws(ValidationException::class, 'El equipo seleccionado no existe.');

it('lanza ForbiddenException si técnico intenta crear ticket fuera de su depto', function () {
    $ticketService = mock(TicketService::class);
    $equipoModel = mock(Equipo::class);
    $soporteModel = mock(Soporte::class);

    $equipoModel->shouldReceive('findById')
        ->with(1)
        ->andReturn((object)['id' => 1, 'departamento_id' => 10]);

    $ticketService->shouldReceive('validarEquipoDepartamento')
        ->with(1, 5) // técnico de depto 5 intentando equipo de depto 10
        ->andReturn(false);

    $action = new CreateTicketAction($ticketService, $soporteModel, $equipoModel);
    $dto = new CreateTicketDTO(
        equipoId: 1,
        descripcion: 'Test',
        prioridad: 'media',
        categoriaId: null,
        userId: 1,
        departamentoId: 5
    );

    $action->execute($dto);
})->throws(ForbiddenException::class, 'No tiene permisos para crear tickets para equipos fuera de su departamento.');

it('lanza ValidationException si descripción muy corta', function () {
    $action = new CreateTicketAction(
        mock(TicketService::class),
        mock(Soporte::class),
        mock(Equipo::class)
    );

    // DTO validation happens in fromRequest, but we can test DTO directly
    expect(fn() => new CreateTicketDTO(
        equipoId: 1,
        descripcion: 'Corto', // < 10 chars
        prioridad: 'media',
        categoriaId: null,
        userId: 1
    ))->toThrow(ValidationException::class);
});

it('usa prioridad por defecto "media" si no se especifica', function () {
    // DTO validation test
    $dto = CreateTicketDTO::fromRequest([
        'equipo_id' => 1,
        'descripcion' => 'Descripción válida suficiente',
        // prioridad omitida
    ], 1);

    expect($dto->prioridad)->toBe('media');
});