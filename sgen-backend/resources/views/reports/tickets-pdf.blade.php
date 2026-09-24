<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tickets</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #f97316; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1a1a1a; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; color: #666; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f97316; color: white; padding: 8px; text-align: left; font-size: 9px; }
        td { padding: 7px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .status-pendiente { background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 3px; }
        .status-en_proceso { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 3px; }
        .status-resuelto { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 3px; }
        .prioridad-critica { color: #dc2626; font-weight: bold; }
        .prioridad-alta { color: #ea580c; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; border-top: 1px solid #e5e5e5; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Tickets de Soporte</h1>
        <div class="subtitle">Sistema de Gestión de Soporte Técnico</div>
    </div>

    <div class="meta">
        <div>Generado: {{ $generatedAt }}</div>
        <div>Total de registros: {{ count($tickets) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Categoría</th>
                <th>Técnico</th>
                <th>Departamento</th>
                <th>Equipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>T-{{ $ticket->id }}</td>
                <td>{{ $ticket->titulo }}</td>
                <td>{{ $ticket->fecha }}</td>
                <td><span class="status-{{ $ticket->estado }}">{{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}</span></td>
                <td class="prioridad-{{ $ticket->prioridad }}">{{ ucfirst($ticket->prioridad) }}</td>
                <td>{{ $ticket->categoria_nombre ?? 'Sin categoría' }}</td>
                <td>{{ trim(($ticket->tech_nombre ?? '') . ' ' . ($ticket->tech_apellido ?? '')) ?: 'Sin asignar' }}</td>
                <td>{{ $ticket->depto_nombre ?? '-' }}</td>
                <td>{{ $ticket->equipo_codigo ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este reporte fue generado automáticamente por el Sistema de Gestión de Soporte Técnico
    </div>
</body>
</html>
