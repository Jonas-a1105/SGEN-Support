<?php

declare(strict_types=1);

namespace Tests\Unit\Audit;

use DateTimeImmutable;
use Modules\Audit\Domain\Models\SessionLog;
use PHPUnit\Framework\TestCase;

final class AuditDomainTest extends TestCase
{
    public function test_can_instantiate_active_session_log(): void
    {
        $start = new DateTimeImmutable('2026-03-23 10:00:00');
        $session = new SessionLog(
            id: 1,
            userId: 5,
            username: 'admin',
            startedAt: $start,
            endedAt: null
        );

        $this->assertSame(1, $session->id());
        $this->assertSame(5, $session->userId());
        $this->assertSame('admin', $session->username());
        $this->assertTrue($session->isActive());
        $this->assertSame('En curso', $session->durationFormatted());
    }

    public function test_duration_formatted_with_minutes_only(): void
    {
        $start = new DateTimeImmutable('2026-03-23 10:00:00');
        $end = new DateTimeImmutable('2026-03-23 10:45:00');
        $session = new SessionLog(
            id: 2,
            userId: 5,
            username: 'admin',
            startedAt: $start,
            endedAt: $end
        );

        $this->assertFalse($session->isActive());
        $this->assertSame('45 min', $session->durationFormatted());
    }

    public function test_duration_formatted_with_hours_and_minutes(): void
    {
        $start = new DateTimeImmutable('2026-03-23 08:00:00');
        $end = new DateTimeImmutable('2026-03-23 10:30:00');
        $session = new SessionLog(
            id: 3,
            userId: 8,
            username: 'tecnico',
            startedAt: $start,
            endedAt: $end
        );

        $this->assertFalse($session->isActive());
        $this->assertSame('2h 30m', $session->durationFormatted());
    }
}
