<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de Baja Patrimonial {{ $equipment['inventoryCode'] ?? '' }}</title>
    <style>
        @page { margin: 28mm 20mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2126; margin: 0; }
        .membrete { text-align: center; border-bottom: 3px solid #111; padding-bottom: 12px; margin-bottom: 20px; }
        .membrete h1 { font-size: 18px; margin: 0 0 4px; letter-spacing: 0.04em; }
        .membrete p { margin: 0; font-size: 11px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: left; font-size: 11.5px; }
        th { background: #f0f0f2; width: 34%; font-weight: 700; }
        .caja-nota { margin-top: 18px; padding: 10px 12px; border: 1px dashed #999; background: #fbfbfb; font-size: 11px; }
        .firmas { margin-top: 60px; display: table; width: 100%; }
        .firma-col { display: table-cell; width: 50%; text-align: center; padding-top: 40px; font-size: 11px; }
        .firma-col::before { content: ''; display: block; border-top: 1px solid #333; margin: 0 30px; padding-top: 8px; }
        .hash-pie { margin-top: 28px; font-size: 10px; font-family: monospace; color: #475; word-break: break-all; }
    </style>
</head>
<body>
    <div class="membrete">
        <h1>ACTA DE BAJA PATRIMONIAL</h1>
        <p>Retiro legal del activo del ciclo operativo IT (historia conservada)</p>
    </div>

    <table>
        <tr><th>Código patrimonial</th><td>{{ $equipment['inventoryCode'] }}</td></tr>
        <tr><th>Activo</th><td>{{ trim(($equipment['brand'] ?? '').' '.($equipment['model'] ?? '')) ?: $equipment['name'] }}</td></tr>
        <tr><th>Serial</th><td>{{ $equipment['serialNumber'] ?? 'No registrado' }}</td></tr>
        <tr><th>Tipo</th><td>{{ $equipment['type'] }}</td></tr>
        <tr><th>Último estado operativo</th><td>De baja</td></tr>
        <tr><th>Departamento último</th><td>{{ $equipment['departmentName'] ?? '—' }}</td></tr>
        <tr><th>Custodio último</th><td>{{ $equipment['employeeName'] ?? '—' }}</td></tr>
        <tr><th>Motivo de baja</th><td>{{ ucfirst($equipment['motivo_baja'] ?? '') }}</td></tr>
        <tr><th>Valor de recuperación</th><td>{{ isset($equipment['valor_recuperacion']) ? number_format((float) $equipment['valor_recuperacion'], 2, ',', '.').' USD' : '—' }}</td></tr>
        <tr><th>Destino</th><td>{{ $equipment['destino_baja'] ?? 'Reclasin TI' }}</td></tr>
        <tr><th>Responsable TI (firma)</th><td>{{ $equipment['responsable_nombre'] ?? 'Equipo TI' }}</td></tr>
        <tr><th>Fecha legal</th><td>{{ isset($equipment['fecha_baja']) ? \Carbon\Carbon::parse($equipment['fecha_baja'])->format('d/m/Y H:i') : $generada_en->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Notas</th><td>{{ $equipment['nota_baja'] ?? '—' }}</td></tr>
    </table>

    <div class="caja-nota">
        Artículo <strong>no actuable</strong> tras esta baja: no admite nuevos tickets ni órdenes de trabajo,
        pero su historia (tickets anteriores, mantenimientos, custodias) se conserva íntegramente en el sistema
        para fines contables y de auditoría interna.
    </div>

    <div class="firmas">
        <div class="firma-col">
            <strong>Firma del responsable IT</strong>
        </div>
        <div class="firma-col">
            <strong>Firma del custodio legitimado</strong>
        </div>
    </div>

    <p class="hash-pie">
        Huella probatoria (SHA-256 sobre los datos núcleo del activo y la baja):<br>
        {{ $hash_acta ?? 'N/D' }}<br>
        Emisión: {{ $generada_en->format('d/m/Y H:i:s') }}
    </p>
</body>
</html>
