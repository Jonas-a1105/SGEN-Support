<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Config\ConfiguracionGlobal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Configuración global visual (Módulo 36): edición de las claves de negocio
 * persis­tidas en `configuracion_global` — todo ajuste queda en la tabla con
 * `actualizado_por` y se invalida el caché de lectura inmediato.
 */
final class ConfiguracionGlobalController extends Controller
{
    public function index(): Response
    {
        $claves = \Illuminate\Support\Facades\DB::table('configuracion_global')
            ->leftJoin('usuarios', 'configuracion_global.actualizado_por', '=', 'usuarios.id')
            ->select([
                'configuracion_global.clave',
                'configuracion_global.valor',
                'configuracion_global.descripcion',
                'configuracion_global.updated_at',
                'usuarios.username as actualizado_por_nombre',
            ])
            ->orderBy('configuracion_global.clave')
            ->get()
            ->map(fn ($r) => [
                'clave' => (string) $r->clave,
                'valor' => (string) ($r->valor ?? ''),
                'descripcion' => (string) ($r->descripcion ?? ''),
                'actualizado_at' => $r->updated_at !== null ? \Carbon\Carbon::parse((string) $r->updated_at)->format('d/m/Y H:i') : '—',
                'actualizado_por' => $r->actualizado_por_nombre !== null ? (string) $r->actualizado_por_nombre : '—',
            ])
            ->all();

        return Inertia::render('Admin/ConfiguracionGlobal', [
            'claves' => $claves,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'clave' => ['required', 'string', 'max:120'],
            'valor' => ['nullable', 'string', 'max:4000'],
        ]);

        ConfiguracionGlobal::establecer(
            (string) $validated['clave'],
            $validated['valor'] !== null ? (string) $validated['valor'] : null,
            (int) $request->user()->id
        );

        return back()->with('success', 'Clave global actualizada y en vigor en la siguiente petición.');
    }
}
