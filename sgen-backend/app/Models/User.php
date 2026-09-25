<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $rol
 * @property string $tema
 * @property int|null $empleado_id
 * @property int|null $departamento_id
 */
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $table = 'usuarios';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'rol',
        'tema',
        'empleado_id',
        'departamento_id',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isTecnico(): bool
    {
        return in_array($this->rol, ['admin', 'tecnico'], true);
    }

    /**
     * La columna legacy `rol` es la identidad; el rol Spatie es su reflejo.
     * Cada guardado del usuario mantiene ambos consistentes para que el
     * middleware permission:* siempre disponga del rol efectivo.
     */
    protected static function booted(): void
    {
        static::saved(static function (User $user): void {
            $user->syncSpatieRoleFromColumn();
        });
    }

    public function syncSpatieRoleFromColumn(): void
    {
        $roleName = trim((string) $this->rol);
        if ($roleName === '') {
            return;
        }

        $role = Role::findOrCreate($roleName, 'web');

        // syncRoles solo toca la tabla pivote; no vuelve a guardar el modelo.
        if (! $this->hasRole($role->name)) {
            $this->syncRoles([$role]);
        }
    }
}
