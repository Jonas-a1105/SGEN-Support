<?php

declare(strict_types=1);

namespace Modules\Support\Application\DTOs;

final class TicketDetailDTO
{
    /**
     * @param array<string, mixed> $ticket
     * @param array<string, mixed> $asset
     * @param array<int, array<string, mixed>> $comments
     * @param array<int, array<string, mixed>> $attachments
     * @param array<int, array<string, mixed>> $materials
     * @param array<string, mixed> $rating
     * @param array<int, array<string, mixed>> $logEntries
     */
    public function __construct(
        public readonly array $ticket,
        public readonly array $asset,
        public readonly array $comments,
        public readonly array $attachments,
        public readonly array $materials,
        public readonly ?array $rating = null,
        public readonly array $logEntries = []
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ticket' => $this->ticket,
            'asset' => $this->asset,
            'comments' => $this->comments,
            'attachments' => $this->attachments,
            'materials' => $this->materials,
            'rating' => $this->rating,
            'logEntries' => $this->logEntries,
            'log_entries' => $this->logEntries,
        ];
    }
}
