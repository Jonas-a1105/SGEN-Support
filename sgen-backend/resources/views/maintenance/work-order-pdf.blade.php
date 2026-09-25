<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Mantenimiento MAN-{{ $maintenance['id'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            margin: 25px 30px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #7c3aed;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .header h1 {
            font-size: 15pt;
            margin: 0;
            color: #7c3aed;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 11pt;
            margin: 4px 0 0;
            font-weight: normal;
            color: #4b5563;
        }
        .meta-strip {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            font-size: 9pt;
            background: #f9fafb;
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
        }
        .meta-col {
            display: table-cell;
            width: 50%;
        }
        .section-title {
            background: #7c3aed;
            color: #ffffff;
            padding: 4px 10px;
            font-size: 9.5pt;
            margin: 12px 0 6px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th {
            background: #f5f3ff;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #ddd6fe;
            font-size: 8.5pt;
            color: #5b21b6;
            width: 28%;
        }
        td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        .checklist-item {
            margin: 4px 0;
            font-size: 8.5pt;
        }
        .checkbox-square {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #4b5563;
            margin-right: 6px;
            vertical-align: middle;
        }
        .signatures-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
            border: none;
        }
        .signatures-table td {
            border: none;
            width: 50%;
            text-align: center;
            padding: 0 20px;
            vertical-align: top;
        }
        .sign-line {
            border-top: 1px solid #4b5563;
            margin-top: 45px;
            padding-top: 4px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #111827;
        }
        .sign-subtitle {
            font-size: 8pt;
            color: #6b7280;
        }
        .footer {
            margin-top: 25px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SGEN-Support · Gestión CMMS</h1>
        <h2>Orden Técnica de Trabajo / Hoja de Servicio</h2>
    </div>

    <div class="meta-strip">
        <div class="meta-col">
            <strong>Orden N°:</strong> MAN-{{ str_pad((string)$maintenance['id'], 5, '0', STR_PAD_LEFT) }}
        </div>
        <div class="meta-col" style="text-align: right;">
            <strong>Fecha de Emisión:</strong> {{ $generated_at }}
        </div>
    </div>

    <!-- DATOS DE LA ORDEN -->
    <div class="section-title">1. Información de la Orden de Trabajo</div>
    <table>
        <tr>
            <th>Tipo de Mantenimiento</th>
            <td>{{ ucfirst($maintenance['tipoMantenimiento'] ?? 'General') }}</td>
            <th>Estado Actual</th>
            <td>{{ strtoupper($maintenance['estado'] ?? 'Pendiente') }}</td>
        </tr>
        <tr>
            <th>Fecha Programada</th>
            <td>{{ $maintenance['fecha'] ?? 'N/A' }}</td>
            <th>Técnico Responsable</th>
            <td>{{ $maintenance['tecnicoNombre'] ?? 'Por Asignar' }}</td>
        </tr>
        <tr>
            <th>Costo Estimado/Registrado</th>
            <td>${{ number_format((float)($maintenance['costo'] ?? 0), 2) }}</td>
            <th>Frecuencia</th>
            <td>{{ ucfirst($maintenance['frecuencia'] ?? 'Puntual') }}</td>
        </tr>
    </table>

    <!-- DATOS DEL EQUIPO INTERVENIDO -->
    <div class="section-title">2. Activo / Equipo Intervenido</div>
    <table>
        <tr>
            <th>Código de Inventario</th>
            <td>{{ $maintenance['equipoCodigo'] ?? ('EQ-' . ($maintenance['equipoId'] ?? '')) }}</td>
            <th>Número de Serie</th>
            <td>{{ $maintenance['equipoSerial'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Modelo / Descripción</th>
            <td>{{ $maintenance['equipoModelo'] ?? 'Equipo General' }}</td>
            <th>Departamento / Ubicación</th>
            <td>{{ $maintenance['departamentoNombre'] ?? 'Área Operativa' }}</td>
        </tr>
    </table>

    <!-- DESCRIPCIÓN DEL SERVICIO -->
    <div class="section-title">3. Tareas Técnicas y Procedimientos Realizados</div>
    <table>
        <tr>
            <td>{{ $maintenance['descripcion'] ?? 'Mantenimiento preventivo general y diagnóstico de hardware.' }}</td>
        </tr>
    </table>

    <!-- CHECKLIST DE INSPECCIÓN -->
    <div class="section-title">4. Protocolo de Verificación Estándar</div>
    <div style="background: #faf5ff; padding: 8px 12px; border: 1px solid #e9d5ff; margin-bottom: 12px;">
        <div class="checklist-item"><span class="checkbox-square"></span> Inspección visual externa de chasis, puertos y cableado de alimentación.</div>
        <div class="checklist-item"><span class="checkbox-square"></span> Limpieza interna profunda, disipadores y ventiladores de enfriamiento.</div>
        <div class="checklist-item"><span class="checkbox-square"></span> Verificación de voltajes de fuente de poder y temperaturas de operación.</div>
        <div class="checklist-item"><span class="checkbox-square"></span> Diagnóstico de salud SMART de discos y pruebas de memoria RAM.</div>
        <div class="checklist-item"><span class="checkbox-square"></span> Pruebas finales de encendido, sistema operativo y conectividad a la red.</div>
    </div>

    <!-- MATERIALES Y REPUESTOS CONSUMIDOS -->
    @if(count($materiales) > 0)
    <div class="section-title">5. Repuestos y Materiales de Inventario Utilizados</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Código</th>
                <th style="width: 50%;">Descripción del Repuesto</th>
                <th style="width: 25%; text-align: center;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materiales as $mat)
            <tr>
                <td>{{ $mat->codigo ?? ('ITEM-' . ($mat->item_id ?? '')) }}</td>
                <td>{{ $mat->nombre ?? 'Repuesto de inventario' }}</td>
                <td style="text-align: center;">{{ $mat->cantidad }} und.</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- FIRMAS DE CONFORMIDAD -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sign-line">Técnico Ejecutor</div>
                <div class="sign-subtitle">{{ $maintenance['tecnicoNombre'] ?? 'Firma y Sello del Técnico' }}</div>
            </td>
            <td>
                <div class="sign-line">Recibido Conforme / Responsable de Área</div>
                <div class="sign-subtitle">Nombre, Firma y Fecha de Aceptación</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado automáticamente por SGEN-Support CMMS v1.1.0 · Sistema de Mantenimiento y Control de Activos
    </div>
</body>
</html>
