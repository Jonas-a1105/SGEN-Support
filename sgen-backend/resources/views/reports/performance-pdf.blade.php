<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Rendimiento</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #8b5cf6; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1a1a1a; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; color: #666; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 9px; color: #666; }
        .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
        .kpi-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 28px; font-weight: bold; color: #1a1a1a; }
        .kpi-label { font-size: 10px; color: #666; margin-top: 5px; }
        h2 { font-size: 14px; color: #1a1a1a; margin-bottom: 12px; border-bottom: 1px solid #e5e5e5; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #8b5cf6; color: white; padding: 8px; text-align: left; font-size: 9px; }
        td { padding: 7px; border-bottom: 1px solid #e5e5e5; font-size: 9px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .rank { background: #8b5cf6; color: white; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; border-top: 1px solid #e5e5e5; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Rendimiento</h1>
        <div class="subtitle">Sistema de Gestión de Soporte Técnico</div>
    </div>

    <div class="meta">
        <div>Generado: {{ $generatedAt }}</div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpis['tickets_total'] }}</div>
            <div class="kpi-label">Tickets Totales</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpis['tickets_resueltos'] }}</div>
            <div class="kpi-label">Tickets Resueltos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpis['tickets_pendientes'] }}</div>
            <div class="kpi-label">Tickets Pendientes</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpis['equipos_total'] }}</div>
            <div class="kpi-label">Equipos Registrados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpis['mantenimientos_completados'] }}</div>
            <div class="kpi-label">Mantenimientos Completados</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpis['tickets_total'] > 0 ? round(($kpis['tickets_resueltos'] / $kpis['tickets_total']) * 100, 1) : 0 }}%</div>
            <div class="kpi-label">Tasa de Resolución</div>
        </div>
    </div>

    <h2>Top 10 Técnicos con Más Tickets Resueltos</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Técnico</th>
                <th>Tickets Resueltos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topTechnicians as $index => $tech)
            <tr>
                <td><span class="rank">{{ $index + 1 }}</span></td>
                <td>{{ trim($tech->nombre . ' ' . $tech->apellido) }}</td>
                <td>{{ $tech->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este reporte fue generado automáticamente por el Sistema de Gestión de Soporte Técnico
    </div>
</body>
</html>
