<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Requests;

use App\Models\User;
use App\Support\Config\ConfiguracionGlobal;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Username: insensible a mayúsculas y espacios sobrantes (MySQL collation
        // legacy lo toleraba; PostgreSQL es estricto y bloqueaba usuarios válidos).
        $username = trim((string) $this->input('username'));
        $password = (string) $this->input('password');
        $remember = (bool) $this->boolean('remember');

        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [Str::lower($username)])
            ->first();

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            RateLimiter::hit($this->throttleKey());
            $this->registrarIntento($username, false);
            $this->alertarBloqueoSiCorresponde($username);

            throw ValidationException::withMessages([
                'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // Cuenta desactivada: la identidad se conserva, pero no hay acceso.
        // El intento queda en el registro forense sin consumir el bloqueo.
        if (! (bool) ($user->activo ?? true)) {
            $this->registrarIntento($username, false);

            throw ValidationException::withMessages([
                'username' => 'Esta cuenta está desactivada. Contacta a la administración.',
            ]);
        }

        Auth::login($user, $remember);
        RateLimiter::clear($this->throttleKey());
        $this->registrarIntento($username, true);

        // Migración transparente a Argon2id: el hash legado (bcrypt) se
        // reemplaza en el primer inicio de sesión exitoso, sin fricción.
        if (Hash::needsRehash((string) $user->password)) {
            $user->password = $password; // cast 'hashed' aplica el driver vigente
            $user->save();
        }
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->intentosMaximos())) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        // Misma normalización que authenticate() (trim + lower): sin ella,
        // "admin", " admin " y "ADMIN" producían claves de throttle distintas,
        // permitiendo eludir el bloqueo temporal y duplicando las alertas.
        $username = trim((string) $this->input('username'));

        return Str::transliterate(Str::lower($username).'|'.$this->ip());
    }

    /**
     * Umbral de bloqueo temporal: parámetro operativo de seguridad leído de
     * la configuración global persistida (con valor por defecto seguro).
     */
    private function intentosMaximos(): int
    {
        return max(1, ConfiguracionGlobal::entero('seguridad.login_intentos_max', 5));
    }

    /**
     * Registro forense del intento (regla #44: jamás se guarda la contraseña).
     */
    private function registrarIntento(string $username, bool $exitoso): void
    {
        DB::table('intentos_login')->insert([
            'username' => mb_substr($username, 0, 100),
            'ip' => $this->ip() !== null ? mb_substr((string) $this->ip(), 0, 45) : null,
            'user_agent' => $this->userAgent() !== null ? mb_substr((string) $this->userAgent(), 0, 512) : null,
            'exitoso' => $exitoso,
            'created_at' => now(),
        ]);
    }

    /**
     * Al agotarse los intentos se alerta una única vez a la administración:
     * alguien puede estar forzando una cuenta legítima.
     */
    private function alertarBloqueoSiCorresponde(string $username): void
    {
        if (RateLimiter::attempts($this->throttleKey()) !== $this->intentosMaximos()) {
            return;
        }

        $ahora = now();
        $admins = DB::table('usuarios')->where('rol', 'admin')->pluck('id');

        $mensaje = mb_substr(
            "La cuenta [{$username}] fue bloqueada temporalmente tras "
            .$this->intentosMaximos().' intentos fallidos de inicio de sesión'
            .($this->ip() !== null ? " desde la IP {$this->ip()}" : '').'.',
            0,
            255
        );

        foreach ($admins as $adminId) {
            DB::table('notificaciones')->insert([
                'usuario_id' => (int) $adminId,
                'tipo' => 'seguridad_login',
                'titulo' => 'Bloqueo temporal por intentos fallidos',
                'mensaje' => $mensaje,
                'enlace' => '/auditoria',
                'icono' => 'bi-shield-exclamation',
                'leido' => false,
                'read_at' => null,
                'created_at' => $ahora,
            ]);
        }
    }
}
