<?php

declare(strict_types=1);

namespace Modules\User;

use Illuminate\Support\ServiceProvider;
use Modules\User\Domain\Ports\UserRepositoryInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;

final class UserModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }
}
