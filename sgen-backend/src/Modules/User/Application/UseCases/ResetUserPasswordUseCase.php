<?php

declare(strict_types=1);

namespace Modules\User\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Restablecimiento de acceso operado por un administrador: genera una
 * contraseÃ±a temporal fuerte, la fija y obliga al usuario a cambiarla en
 * su prÃ³ximo ingreso. La credencial se retorna UNA vez al controlador
 * (flash de sesiÃ³n, jamÃ¡s almacenada en claro).
 */
final class ResetUserPasswordUseCase
{
    public function execute(int $userId): string
    {
        $user = DB::table('usuarios')->where('id', $userId)->first(['id']);
        abort_if($user === null, 404, 'Usuario no encontrado.');

        $temporary = Str::password(14, symbols: false).'@'.Str::random(3).random_int(10, 99);

        DB::transaction(function () use ($userId, $temporary): void {
            // Persistir con flag de cambio obligatorio (el modelo aplica hash cast).
            DB::table('usuarios')->where('id', $userId)->update([
                'password' => Hash::make($temporary),
                'must_change_password' => true,
                // Un reset administrativo invalida también el "recordarme":
                // el acceso previo no debe sobrevivir al cambio de credencial.
                'remember_token' => null,
                'updated_at' => now(),
            ]);

            DB::table('sessions')->where('user_id', $userId)->delete();

            DB::table('sesiones_log')
                ->where('usuario_id', $userId)
                ->whereNull('fecha_fin')
                ->update(['fecha_fin' => now(), 'motivo_cierre' => 'reset_password']);
        });

        return $temporary;
    }
}
