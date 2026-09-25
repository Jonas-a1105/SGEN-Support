<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Entrega y Custodia - {{ $equipment['inventoryCode'] }}</title>
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
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .header h1 {
            font-size: 15pt;
            margin: 0;
            color: #4f46e5;
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
            background: #4f46e5;
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
            background: #eef2ff;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #c7d2fe;
            font-size: 8.5pt;
            color: #3730a3;
            width: 28%;
        }
        td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        .clauses {
            font-size: 8pt;
            color: #4b5563;
            text-align: justify;
            margin: 12px 0 20px;
            padding: 8px 12px;
            background: #f3f4f6;
            border-left: 3px solid #4f46e5;
        }
        .clauses p {
            margin: 4px 0;
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
        <h1>SGEN-Support · Gestión de Tecnología</h1>
        <h2>Acta Formal de Asignación y Custodia de Activo TI</h2>
    </div>

    <div class="meta-strip">
        <div class="meta-col">
            <strong>Código de Activo:</strong> {{ $equipment['inventoryCode'] }}
        </div>
        <div class="meta-col" style="text-align: right;">
            <strong>Fecha de Emisión:</strong> {{ $generated_at }}
        </div>
    </div>

    <!-- DATOS DEL ACTIVO -->
    <div class="section-title">1. Especificaciones del Activo Asignado</div>
    <table>
        <tr>
            <th>Código Patrimonial</th>
            <td>{{ $equipment['inventoryCode'] }}</td>
            <th>Número de Serie</th>
            <td>{{ $equipment['serialNumber'] ?: 'No registrado' }}</td>
        </tr>
        <tr>
            <th>Tipo de Equipo</th>
            <td>{{ ucfirst($equipment['type']) }}</td>
            <th>Marca y Modelo</th>
            <td>{{ $equipment['brand'] }} {{ $equipment['model'] }}</td>
        </tr>
        <tr>
            <th>Procesador</th>
            <td>{{ $equipment['processor'] ?: 'N/A' }}</td>
            <th>Memoria RAM</th>
            <td>{{ $equipment['ram'] ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Almacenamiento</th>
            <td>{{ $equipment['storage'] ?: 'N/A' }}</td>
            <th>Sistema Operativo</th>
            <td>{{ $equipment['os'] ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Ubicación Física</th>
            <td>{{ $equipment['physicalLocation'] ?: 'Sede Central' }}</td>
            <th>Estado Físico / Operativo</th>
            <td>{{ $equipment['status'] }}</td>
        </tr>
    </table>

    <!-- DATOS DEL CUSTODIO -->
    <div class="section-title">2. Identificación del Custodio Responsable</div>
    <table>
        @if($employee)
        <tr>
            <th>Nombre y Apellido</th>
            <td>{{ $employee->nombre }} {{ $employee->apellido }}</td>
            <th>Cédula de Identidad</th>
            <td>{{ $employee->cedula ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Cargo</th>
            <td>{{ $employee->cargo ?: 'Personal Operativo' }}</td>
            <th>Departamento</th>
            <td>{{ $equipment['departmentName'] ?: 'Sin Asignar' }}</td>
        </tr>
        <tr>
            <th>Correo Electrónico</th>
            <td colspan="3">{{ $employee->email ?: 'N/A' }}</td>
        </tr>
        @else
        <tr>
            <th>Custodio Asignado</th>
            <td colspan="3">Activo en custodia general del departamento: <strong>{{ $equipment['departmentName'] ?: 'Almacén TI' }}</strong></td>
        </tr>
        @endif
    </table>

    <!-- CLÁUSULAS DE CUSTODIA -->
    <div class="section-title">3. Términos de Uso y Responsabilidad</div>
    <div class="clauses">
        <p><strong>PRIMERA (Destino):</strong> El activo descrito se entrega exclusivamente para el cumplimiento de las labores profesionales asignadas dentro de la institución.</p>
        <p><strong>SEGUNDA (Custodia y Cuidado):</strong> El custodio asume el compromiso de velar por el buen estado físico y operacional del equipo, evitando modificaciones no autorizadas de hardware o software.</p>
        <p><strong>TERCERA (Reporte de Novedades):</strong> Cualquier falla técnica, daño accidental, pérdida o extravío debe ser notificado de manera inmediata al Departamento de TI a través del sistema de soporte.</p>
        <p><strong>CUARTA (Devolución):</strong> Ante traslados de área, cese de funciones o solicitud expresa de auditoría de TI, el equipo debe ser reintegrado con todos sus componentes y periféricos en el estado recibido.</p>
    </div>

    <!-- FIRMAS -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sign-line">Entregado por TI / Soporte Técnico</div>
                <div class="sign-subtitle">Firma, Sello y Fecha</div>
            </td>
            <td>
                <div class="sign-line">Recibido Conforme por el Custodio</div>
                <div class="sign-subtitle">{{ $employee ? ($employee->nombre . ' ' . $employee->apellido) : 'Firma del Receptor' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado automáticamente por SGEN-Support Enterprise v1.1.0 · Sistema Integral de Gestión TI y Activos
    </div>
</body>
</html>
