<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idempotency-Key (checklist #21).
 *
 * Si el cliente envía header Idempotency-Key, la respuesta de la PRIMERA
 * ejecución queda guardada y cualquier repetición devuelve esa misma
 * respuesta sin volver a ejecutar la acción (doble clic, reintento de red,
 * F5). Clave única por usuario + clave exacta. Respuestas replay: status +
 * Location (redirects Inertia) + cuerpo JSON/HTML.
 */
final class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) ($request->headers->get('Idempotency-Key') ?? '');

        if ($key === '' || strlen($key) > 100 || ! in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $usuarioId = $request->user()?->id;
        $existente = DB::table('idempotency_keys')
            ->where('clave', $key)
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($existente !== null) {
            $headers = $existente->response_headers !== null
                ? (array) json_decode((string) $existente->response_headers, true)
                : [];

            $response = response(
                (string) ($existente->response_body ?? ''),
                (int) ($existente->response_status ?? 409)
            );

            foreach ($headers as $nombre => $valor) {
                $response->headers->set($nombre, (string) $valor);
            }

            $response->headers->set('Idempotency-Replayed', '1');

            return $response;
        }

        $response = $next($request);

        // Solo se persisten respuestas "exitosas" (2xx/3xx): ante error de
        // validación o de negocio el cliente debe poder reintentar.
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
            $headers = [];
            foreach (['Location', 'X-Inertia-Location', 'Content-Type'] as $h) {
                if ($response->headers->has($h)) {
                    $headers[$h] = $response->headers->get($h);
                }
            }

            DB::table('idempotency_keys')->insertOrIgnore([
                'clave' => $key,
                'usuario_id' => $usuarioId,
                'ruta' => '/'.ltrim($request->path(), '/'),
                'response_status' => $response->getStatusCode(),
                'response_headers' => $headers === [] ? null : json_encode($headers),
                'response_body' => $response->getContent(),
                'created_at' => Carbon::now(),
            ]);
        }

        return $response;
    }
}
