<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Mantenimientos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1a1a1a; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; color: #666; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #3b82f6; color: white; padding: 8px; text-align: left; font-size: 9px; }
        td { padding: 7px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .estado-pendiente { background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 3px; }
        .estado-en_proceso { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 3px; }
        .estado-completado { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 3px; }
        .estado-pospuesto { background: #fef9c3; color: #854d0e; padding: 2px 6px; border-radius: 3px; }
        .estado-cancelado { background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 3px; }
        .tipo-preventivo { color: #16a34a; font-weight: bold; }
        .tipo-correctivo { color: #dc2626; font-weight: bold; }
        .tipo-predictivo { color: #3b82f6; font-weight: bold; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; border-top: 1px solid #e5e5e5; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Mantenimientos</h1>
        <div class="subtitle">Sistema de Gestión de Soporte Técnico</div>
    </div>

    <div class="meta">
        <div>Generado: {{ $generatedAt }}</div>
        <div>Total de registros: {{ count($maintenances) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Equipo</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Frecuencia</th>
                <th>Próxima</th>
                <th>Técnico</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $m)
            <tr>
                <td>#{{ $m->id }}</td>
                <td>{{ $m->fecha }}</td>
                <td>{{ $m->equipo_codigo ?? '-' }}</td>
                <td class="tipo-{{ $m->tipo_mantenimiento }}">{{ ucfirst($m->tipo_mantenimiento) }}</td>
                <td>{{ Str::limit($m->descripcion, 50) }}</td>
                <td><span class="estado-{{ $m->estado }}">{{ ucfirst(str_replace('_', ' ', $m->estado)) }}</span></td>
                <td>{{ ucfirst($m->frecuencia) }}</td>
                <td>{{ $m->proxima_fecha ?? '-' }}</td>
                <td>{{ $m->tecnico_nombre ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este reporte fue generado automáticamente por el Sistema de Gestión de Soporte Técnico
    </div>
</body>
</html>
