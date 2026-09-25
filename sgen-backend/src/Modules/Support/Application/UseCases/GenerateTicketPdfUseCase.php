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

    public function execute(int $ticketId): Response
    {
        $detail = $this->repository->findById($ticketId);

        if ($detail === null) {
            throw new \DomainException("Ticket #{$ticketId} no encontrado.");
        }

        $viewData = $this->dataAssembler->assemble($detail);

        $pdf = Pdf::loadView('support.ticket-pdf', $viewData)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Acta_Servicio_'.$viewData['ticket']['code'].'.pdf');
    }
}
