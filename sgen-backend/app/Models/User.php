<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
}
