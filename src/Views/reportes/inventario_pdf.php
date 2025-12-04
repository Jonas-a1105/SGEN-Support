<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte General de Inventario</h2>
        <p>Generado el: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Ubicación</th>
                <th>Stock</th>
                <th>Valor Unit.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalValor = 0;
            foreach ($items as $item): 
                $valorTotalItem = $item->stock_actual * $item->valor_compra;
                $totalValor += $valorTotalItem;
            ?>
            <tr>
                <td><?= htmlspecialchars($item->codigo) ?></td>
                <td><?= htmlspecialchars($item->nombre) ?></td>
                <td><?= htmlspecialchars($item->categoria) ?></td>
                <td><?= htmlspecialchars($item->ubicacion) ?></td>
                <td><?= $item->stock_actual ?></td>
                <td>$<?= number_format($item->valor_compra, 2) ?></td>
                <td>$<?= number_format($valorTotalItem, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" style="text-align: right;">Valor Total del Inventario:</th>
                <th>$<?= number_format($totalValor, 2) ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        SGEN-Support - Sistema de Gestión de Soporte
    </div>
</body>
</html>
