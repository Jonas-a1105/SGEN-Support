<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #16a34a; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1a1a1a; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; color: #666; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #16a34a; color: white; padding: 8px; text-align: left; font-size: 9px; }
        td { padding: 7px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .stock-bajo { background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 3px; }
        .stock-ok { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 3px; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; border-top: 1px solid #e5e5e5; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inventario</h1>
        <div class="subtitle">Sistema de Gestión de Soporte Técnico</div>
    </div>

    <div class="meta">
        <div>Generado: {{ $generatedAt }}</div>
        <div>Total de ítems: {{ count($items) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Unidad</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->codigo }}</td>
                <td>{{ $item->nombre }}</td>
                <td>{{ $item->categoria_nombre ?? 'Sin categoría' }}</td>
                <td>
                    @if($item->stock_actual <= $item->stock_minimo)
                    <span class="stock-bajo">{{ $item->stock_actual }} ⚠️</span>
                    @else
                    <span class="stock-ok">{{ $item->stock_actual }}</span>
                    @endif
                </td>
                <td>{{ $item->stock_minimo ?? '-' }}</td>
                <td>{{ $item->unidad ?? '-' }}</td>
                <td>{{ $item->ubicacion ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este reporte fue generado automáticamente por el Sistema de Gestión de Soporte Técnico
    </div>
</body>
</html>
