<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Servicio {{ $ticket['code'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1f2937; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 16pt; margin: 0; color: #4f46e5; }
        .header h2 { font-size: 11pt; margin: 4px 0 0; font-weight: normal; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #eef2ff; text-align: left; padding: 6px 8px; border: 1px solid #c7d2fe; font-size: 9pt; color: #4338ca; width: 30%; }
        td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .section-title { background: #4f46e5; color: #fff; padding: 5px 10px; font-size: 10pt; margin: 14px 0 8px; font-weight: bold; }
        .footer { margin-top: 28px; border-top: 1px solid #d1d5db; padding-top: 8px; font-size: 8pt; color: #9ca3af; text-align: center; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; background: #e0e7ff; color: #4338ca; font-size: 9pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SGEN-Support</h1>
        <h2>Acta de Servicio T&eacute;cnico</h2>
    </div>

    <div class="section-title">Datos del Ticket</div>
    <table>
        <tr><th>C&oacute;digo</th><td>{{ $ticket['code'] }}</td></tr>
        <tr><th>T&iacute;tulo</th><td>{{ $ticket['title'] }}</td></tr>
        <tr><th>Estado</th><td>{{ $ticket['status_label'] }}</td></tr>
        <tr><th>Prioridad</th><td>{{ $ticket['priority_label'] }}</td></tr>
        <tr><th>Categor&iacute;a</th><td>{{ $ticket['category'] }}</td></tr>
        <tr><th>Fecha de reporte</th><td>{{ $ticket['report_date'] }}</td></tr>
        <tr><th>T&eacute;cnico asignado</th><td>{{ $ticket['tech_name'] }}</td></tr>
        <tr><th>Tiempo de atenci&oacute;n</th><td>{{ $ticket['attention_time'] }}</td></tr>
        <tr><th>Fecha de cierre</th><td>{{ $ticket['close_date'] }}</td></tr>
    </table>

    <div class="section-title">Equipo Afectado</div>
    <table>
        <tr><th>ID Interno</th><td>{{ $asset['id'] }}</td></tr>
        <tr><th>Serial</th><td>{{ $asset['serial'] }}</td></tr>
        <tr><th>Tipo</th><td>{{ $asset['type'] }}</td></tr>
        <tr><th>Modelo</th><td>{{ $asset['model'] }}</td></tr>
        <tr><th>Departamento</th><td>{{ $asset['department'] }}</td></tr>
        <tr><th>Asignado a</th><td>{{ $asset['assigned_to'] }}</td></tr>
    </table>

    <div class="section-title">Descripci&oacute;n del Problema</div>
    <table>
        <tr><td>{{ $ticket['description'] }}</td></tr>
    </table>

    @if(!empty($solution))
    <div class="section-title">Soluci&oacute;n Aplicada</div>
    <table>
        <tr><td>{{ $solution }}</td></tr>
    </table>
    @endif

    @if(count($materials) > 0)
    <div class="section-title">Materiales Utilizados</div>
    <table>
        <tr>
            <th style="width: 15%">C&oacute;digo</th>
            <th style="width: 55%">&Iacute;tem</th>
            <th style="width: 15%">Cantidad</th>
            <th style="width: 15%">Fecha</th>
        </tr>
        @foreach($materials as $material)
        <tr>
            <td>{{ $material['code'] }}</td>
            <td>{{ $material['item_name'] }}</td>
            <td>{{ $material['quantity'] }}</td>
            <td>{{ $material['date'] }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">
        Documento generado electr&oacute;nicamente el {{ $generatedAt }} &mdash; {{ $ticket['code'] }}
    </div>
</body>
</html>
