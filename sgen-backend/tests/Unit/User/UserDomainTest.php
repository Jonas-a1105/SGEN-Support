<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use InvalidArgumentException;
use Modules\User\Domain\Enums\UserRole;
use Modules\User\Domain\Models\SystemUser;
use PHPUnit\Framework\TestCase;

final class UserDomainTest extends TestCase
{
    public function test_can_instantiate_system_user_with_valid_attributes(): void
    {
        $user = SystemUser::create(
            username: 'jdoe',
            password: 'hashedpassword',
            role: UserRole::ADMIN,
            theme: 'dark',
            employeeId: 10,
            departmentId: 2,
            id: 1
        );

        $this->assertSame(1, $user->id());
        $this->assertSame('jdoe', $user->username());
        $this->assertSame(UserRole::ADMIN, $user->role());
        $this->assertSame('dark', $user->theme());
        $this->assertSame(10, $user->employeeId());
        $this->assertSame(2, $user->departmentId());
    }

    public function test_throws_exception_on_empty_username(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SystemUser::create(username: '   ', password: '123');
    }

    public function test_user_role_label_and_normalization(): void
    {
        $this->assertSame('Administrador', UserRole::ADMIN->label());
        $this->assertSame('Técnico', UserRole::TECNICO->label());
        $this->assertSame('Consultor', UserRole::CONSULTOR->label());
        $this->assertSame('Operador', UserRole::OPERADOR->label());

        $this->assertSame(UserRole::ADMIN, UserRole::tryFromString('administrador'));
        $this->assertSame(UserRole::TECNICO, UserRole::tryFromString('técnico'));
        $this->assertSame(UserRole::OPERADOR, UserRole::tryFromString('operador'));
        $this->assertSame(UserRole::CONSULTOR, UserRole::tryFromString('otro'));
        $this->assertSame(UserRole::CONSULTOR, UserRole::tryFromString(null));
    }

    public function test_can_update_system_user(): void
    {
        $user = SystemUser::create(
            username: 'mgarcia',
            password: 'pw',
            role: UserRole::CONSULTOR,
            id: 2
        );

        $updated = $user->update(
            username: 'mgarcia_updated',
            role: UserRole::TECNICO,
            departmentId: 5,
            employeeId: 12
        );

        $this->assertSame(2, $updated->id());
        $this->assertSame('mgarcia_updated', $updated->username());
        $this->assertSame(UserRole::TECNICO, $updated->role());
        $this->assertSame(5, $updated->departmentId());
        $this->assertSame(12, $updated->employeeId());
    }
}
