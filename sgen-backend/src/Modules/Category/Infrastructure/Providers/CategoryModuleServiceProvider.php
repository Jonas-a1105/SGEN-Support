<?php

declare(strict_types=1);

namespace Modules\Category\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Category\Domain\Ports\CategoryRepositoryInterface;
use Modules\Category\Infrastructure\Persistence\Eloquent\EloquentCategoryRepository;

final class CategoryModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
    }
}
