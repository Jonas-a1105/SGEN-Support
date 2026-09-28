<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad HTTP para todas las respuestas web.
 *
 * - HSTS solo se anuncia bajo HTTPS (anunciarlo en HTTP rompería el acceso).
 * - CSP estricta con nonce criptográfico por petición: los únicos scripts en
 *   línea autorizados son los de la plantilla Blade (Ziggy y el bootstrap de
 *   tema), que reciben el nonce compartido como `cspNonce`. Todo script en
 *   línea inyectado por XSS queda bloqueado por el navegador.
 * - En desarrollo (Vite "hot") se permite el origen local del servidor de
 *   módulos y su WebSocket de HMR; en producción solo recursos del propio
 *   origen.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        $response->headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy($nonce));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Política de seguridad de contenido:
     * scripts solo del propio origen o con nonce válido; estilos con
     * 'unsafe-inline' (los bindings :style de Vue lo requieren); sin plugins
     * ni iframes externos; formularios y base-uri restringidos al origen.
     */
    private function buildContentSecurityPolicy(string $nonce): string
    {
        $scriptSrc = ["'self'", "'nonce-{$nonce}'"];
        $styleSrc = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'];
        $fontSrc = ["'self'", 'https://fonts.gstatic.com', 'data:'];
        $imgSrc = ["'self'", 'data:', 'blob:'];
        $connectSrc = ["'self'"];

        // Servidor de desarrollo de Vite (módulos ESM y WebSocket de HMR).
        if (Vite::isRunningHot()) {
            $viteHttp = ['http://127.0.0.1:5173', 'http://127.0.0.1:5174', 'http://localhost:5173', 'http://localhost:5174', 'http://[::1]:5173', 'http://[::1]:5174'];
            $viteWs = ['ws://127.0.0.1:5173', 'ws://127.0.0.1:5174', 'ws://localhost:5173', 'ws://localhost:5174', 'ws://[::1]:5173', 'ws://[::1]:5174'];
            $scriptSrc = array_merge($scriptSrc, $viteHttp);
            $connectSrc = array_merge($connectSrc, $viteHttp, $viteWs);
            $styleSrc = array_merge($styleSrc, $viteHttp);
        }

        return implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', $scriptSrc),
            'style-src '.implode(' ', $styleSrc),
            'font-src '.implode(' ', $fontSrc),
            'img-src '.implode(' ', $imgSrc),
            'connect-src '.implode(' ', $connectSrc),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
