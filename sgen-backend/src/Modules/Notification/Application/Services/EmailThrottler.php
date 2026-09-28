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
 * Contrato en dos pasos: `canSend()` decide sin efectos secundarios y
 * `markSent()` registra la salida SOLO cuando el envío ya fue aceptado.
 * Así un fallo de envío no consume la ventana de cooldown y el intento
 * puede reintentarse en la próxima ejecución.
 *
 * (El "digest" agregado propiamente —correo resumen agrupado— se genera sobre
 * esta misma tabla programado en el scheduler; la puerta queda aquí.)
 */
final class EmailThrottler
{
    /**
     * Indica si el correo puede salir, sin registrar nada. false significa
     * que el destinatario sigue dentro del cooldown (suprimido con evidencia).
     */
    public static function canSend(?int $usuarioId, ?string $email, string $tipo): bool
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

        return ! $consulta->where('created_at', '>=', now()->subMinutes($cooldown))->exists();
    }

    /**
     * Registra una salida efectiva en `correos_enviados`. Debe invocarse
     * únicamente después de que el envío (o su encolado) haya terminado bien.
     */
    public static function markSent(?int $usuarioId, ?string $email, string $tipo, ?string $subject = null): void
    {
        if ($usuarioId === null && ($email === null || $email === '')) {
            return;
        }

        DB::table('correos_enviados')->insert([
            'usuario_id' => $usuarioId,
            'usuario_email' => $email,
            'tipo' => $tipo,
            'subject' => $subject,
            'created_at' => now(),
        ]);
    }
}
