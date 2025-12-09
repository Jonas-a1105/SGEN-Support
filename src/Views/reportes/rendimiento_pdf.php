<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Rendimiento</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        h1 { color: #2c3e50; margin-bottom: 5px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 20px; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 14px; font-weight: bold; border-bottom: 2px solid #6366f1; padding-bottom: 5px; margin-bottom: 10px; color: #1e1b4b; }
        .stat-grid { display: block; margin-bottom: 20px; }
        .stat-box { display: inline-block; width: 30%; background: #f3f4f6; padding: 10px; margin-right: 2%; border-radius: 5px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #4338ca; display: block; }
        .stat-label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background-color: #f9fafb; font-weight: bold; color: #374151; }
    </style>
</head>
<body>
    <header>
        <h1>Reporte de Rendimiento del Equipo</h1>
        <div class="meta">
            Generado el: <?= date('d/m/Y H:i') ?> por <?= htmlspecialchars($usuario) ?>
        </div>
    </header>

    <div class="section">
        <div class="section-title">Resumen General</div>
        <div class="stat-grid">
            <div class="stat-box">
                <span class="stat-value">95%</span>
                <span class="stat-label">Satisfacción</span>
            </div>
            <div class="stat-box">
                <span class="stat-value">45m</span>
                <span class="stat-label">Tiempo Promedio</span>
            </div>
            <div class="stat-box">
                <span class="stat-value">124</span>
                <span class="stat-label">Tickets Cerrados</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Desempeño por Técnico</div>
        <table>
            <thead>
                <tr>
                    <th>Técnico</th>
                    <th>Tickets Asignados</th>
                    <th>Tickets Resueltos</th>
                    <th>Tiempo Promedio</th>
                    <th>Calificación</th>
                </tr>
            </thead>
            <tbody>
                <!-- Datos simulados para demostración -->
                <tr>
                    <td>Carlos Rodríguez</td>
                    <td>45</td>
                    <td>42</td>
                    <td>35 min</td>
                    <td>4.8/5.0</td>
                </tr>
                <tr>
                    <td>Ana Martínez</td>
                    <td>38</td>
                    <td>35</td>
                    <td>42 min</td>
                    <td>4.9/5.0</td>
                </tr>
                <tr>
                    <td>Juan Pérez</td>
                    <td>30</td>
                    <td>28</td>
                    <td>50 min</td>
                    <td>4.5/5.0</td>
                </tr>
                <tr>
                    <td>Sofía López</td>
                    <td>25</td>
                    <td>25</td>
                    <td>30 min</td>
                    <td>5.0/5.0</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Métricas de Servicio</div>
        <p>Este reporte muestra un resumen del rendimiento operativo del equipo de soporte técnico basado en los registros del sistema.</p>
    </div>

</body>
</html>
