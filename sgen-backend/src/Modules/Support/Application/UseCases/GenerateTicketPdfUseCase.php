<?php

declare(strict_types=1);

namespace Modules\Support\Application\UseCases;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\Support\Application\Ports\TicketPdfDataAssembler;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;

final class GenerateTicketPdfUseCase
{
    public function __construct(
        private SupportRepositoryInterface $repository,
        private TicketPdfDataAssembler $dataAssembler
    ) {}

    public function execute(int $ticketId, ?int $viewerUserId = null): Response
    {
        // El visor importa: el PDF no debe exponer notas internas al solicitante.
        $detail = $this->repository->findById($ticketId, $viewerUserId);

        if ($detail === null) {
            throw new \DomainException("Ticket #{$ticketId} no encontrado.");
        }

        $viewData = $this->dataAssembler->assemble($detail);

        $pdf = Pdf::loadView('support.ticket-pdf', $viewData)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Acta_Servicio_'.$viewData['ticket']['code'].'.pdf');
    }
}
