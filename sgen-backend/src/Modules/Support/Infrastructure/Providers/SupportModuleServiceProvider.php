<?php

declare(strict_types=1);

namespace Modules\Support\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Support\Application\Ports\TicketPdfDataAssembler;
use Modules\Support\Domain\Ports\SupportRepositoryInterface;
use Modules\Support\Domain\Services\SlaPolicy;
use Modules\Support\Infrastructure\Persistence\Eloquent\EloquentSupportRepository;
use Modules\Support\Infrastructure\Presentation\PdfTicketDataAssembler;

final class SupportModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SupportRepositoryInterface::class,
            EloquentSupportRepository::class
        );

        $this->app->bind(
            TicketPdfDataAssembler::class,
            PdfTicketDataAssembler::class
        );

        $this->app->singleton(SlaPolicy::class, static fn (): SlaPolicy => SlaPolicy::fromConfig());
    }

    public function boot(): void
    {
        //
    }
}
