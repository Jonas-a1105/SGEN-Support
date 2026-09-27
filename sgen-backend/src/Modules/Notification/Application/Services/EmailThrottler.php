<?php

declare(strict_types=1);

namespace Modules\Notification\Application\Services;

use App\Support\Config\ConfiguracionGlobal;
use Illuminate\Support\Facades\DB;

/**
 * Freno anti-spam de correos del sistema (checklist #45): a lo sumo UN
 * correo por tipo/destinatario cada `notificaciones.email_cooldown_min`
 * minutos. Un stock bajo que arde 20 veces no genera 20 correos; un SLA
 * vencido que corre cada 15 min en el mismo turno tampoco.
 *
 * (El "digest" agregado propiamente —correo resumen agrupado— se genera sobre
 * esta misma tabla programado en el scheduler; la puerta queda aquí.)
 */
final class EmailThrottler
{
    /**
     * Devuelve true si debe enviarse el correo (y registra la salida);
     * false si el destinatario sigue dentro del cooldown (suprimido con evidencia).
     */
    public static function permitir(?int $usuarioId, ?string $email, string $tipo, ?string $subject = null): bool
    {
        $cooldown = max(1, ConfiguracionGlobal::entero('notificaciones.email_cooldown_min', 30));

        $consulta = DB::table('correos_enviados')->where('tipo', $tipo);

        if ($usuarioId !== null) {
            $consulta->where('usuario_id', $usuarioId);
        } elseif ($email !== null) {
            $consulta->where('usuario_email', $email);
        } else {
            // Sin identidad del destinatario no hay cooldown útil.
            return true;
        }

        $reciente = $consulta->where('created_at', '>=', now()->subMinutes($cooldown))->exists();

        if ($reciente) {
            return false;
        }

        DB::table('correos_enviados')->insert([
            'usuario_id' => $usuarioId,
            'usuario_email' => $email,
            'tipo' => $tipo,
            'subject' => $subject,
            'created_at' => now(),
        ]);

        return true;
    }
}
