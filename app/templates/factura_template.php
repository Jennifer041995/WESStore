<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20mm;
            font-size: 10pt;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #333;
            font-size: 28pt;
            margin: 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
        }
        .info-section {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-section td {
            padding: 5px;
            vertical-align: top;
            width: 50%; /* Divide el espacio para dos columnas */
        }
        .info-section .label {
            font-weight: bold;
            color: #555;
            padding-right: 10px;
        }
        .invoice-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #444;
        }
        .invoice-table tfoot td {
            text-align: right;
            border: none;
            padding-top: 10px;
        }
        .invoice-table tfoot .total {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 9pt;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURA DE COMPRA</h1>
    </div>

    <table class="info-section">
        <tr>
            <td style="width: 50%;">
                <p><span class="label">EMPRESA:</span> Tu Empresa S.A. de C.V.</p>
                <p><span class="label">DIRECCIÓN:</span> Dirección de tu empresa, Ciudad, País</p>
                <p><span class="label">TELÉFONO:</span> (XXX) XXX-XXXX</p>
                <p><span class="label">EMAIL:</span> info@tuempresa.com</p>
                <p><span class="label">NIT:</span> 0000-000000-000-0</p>
            </td>
            <td style="width: 50%; text-align: right;">
                <p><span class="label"> DTE :</span><?php echo htmlspecialchars(str_pad($pedido_id ?? '0', 10, '0', STR_PAD_LEFT)); ?></p>
                <p><span class="label">FACTURA N°:</span> <?php echo htmlspecialchars($pedido_id ?? 'N/A'); ?></p>
                <p><span class="label">FECHA:</span> <?php echo htmlspecialchars(date('d/m/Y H:i:s')); ?></p>

    </div>
                </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px dashed #ccc; margin: 20px 0;">

    <table class="info-section">
        <tr>
            <td colspan="2">
                <p><span class="label">CLIENTE:</span> <?php echo htmlspecialchars(($cliente_data['nombres'] ?? '') . ' ' . ($cliente_data['apellidos'] ?? '')); ?></p>
                <p><span class="label">DUI:</span> <?php echo htmlspecialchars($cliente_data['dui'] ?? 'N/A'); ?></p>
                <p><span class="label">EMAIL:</span> <?php echo htmlspecialchars($cliente_data['email'] ?? 'N/A'); ?></p>
                <p><span class="label">TELÉFONO:</span> <?php echo htmlspecialchars($cliente_data['telefono'] ?? 'N/A'); ?></p>
                <p><span class="label">DIRECCIÓN DE ENVÍO:</span>
                    <?php echo htmlspecialchars($cliente_data['direccionCompleta'] ?? ''); ?>
                    <?php htmlspecialchars($cliente_data['municipio'] ?? ''); ?>,
                    <?php echo htmlspecialchars($cliente_data['departamento'] ?? ''); ?>,
                    El Salvador.
                </p>
            </td>
        </tr>
    </table>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th style="width: 15%;">CANTIDAD</th>
                <th style="width: 20%;">PRECIO UNITARIO</th>
                <th style="width: 20%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php $total_factura = 0; ?>
            <?php if (!empty($productos_para_pedido)): // <-- Esta es la variable que ahora recibirá datos con extract() ?>
                <?php foreach ($productos_para_pedido as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($producto['cantidad'] ?? 'N/A'); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($producto['precio_unitario'] ?? 0, 2)); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format(($producto['cantidad'] ?? 0) * ($producto['precio_unitario'] ?? 0), 2)); ?></td>
                        <?php $total_factura += ($producto['cantidad'] ?? 0) * ($producto['precio_unitario'] ?? 0); ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No hay productos en este pedido.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="label">SUBTOTAL:</td>
                <td>$<?php echo htmlspecialchars(number_format($total_factura, 2)); ?></td>
            </tr>
            <tr>

            </tr>
            <tr>
                <td colspan="3" class="label total">TOTAL:</td>
                <td class="total">$<?php echo htmlspecialchars(number_format($total_factura, 2)); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Gracias por tu compra.<br>
        Este documento es una factura de ejemplo y puede no ser válido para fines fiscales.<br>
        Generado el: <?php echo htmlspecialchars(date('d/m/Y H:i:s')); ?>
    </div>
</body>
</html>