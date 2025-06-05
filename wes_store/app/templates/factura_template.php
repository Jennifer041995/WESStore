<?php
// Archivo: factura_compra.php (o el nombre que uses para generar la factura)

// Asegurarnos de usar la zona horaria correcta
date_default_timezone_set('America/El_Salvador');

// Suponemos que ya tienes disponibles estas variables antes de incluir esta plantilla:
//   $pedido_id                   : el ID o número de la factura/orden
//   $cliente_data (array): 
//       'nombres', 'apellidos', 'dui', 'email', 'telefono',
//       'direccionCompleta', 'municipio', 'departamento'
//   $productos_para_pedido (array de arrays):
//       cada elemento con 'nombre', 'cantidad', 'precio_unitario'

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Compra #<?php echo htmlspecialchars(str_pad($pedido_id ?? '0', 10, '0', STR_PAD_LEFT)); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20mm;
            font-size: 10pt;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #333;
            font-size: 24pt;
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
            width: 50%;
        }
        .info-section .label {
            font-weight: bold;
            color: #555;
            padding-right: 5px;
        }
        .invoice-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #444;
        }
        .invoice-table tfoot td {
            border: none;
            padding-top: 10px;
        }
        .invoice-table tfoot .total {
            font-size: 12pt;
            font-weight: bold;
            color: #000;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9pt;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Logo de la empresa -->
        <img src="../../media/img/icono.png" alt="Logo Empresa">
        <h1>FACTURA DE COMPRA</h1>
    </div>

    <table class="info-section">
        <tr>
            <!-- Datos de la empresa -->
            <td style="width: 50%;">
                <img src="../../media/img/icono.png" alt="" srcset="">
                <p><span class="label">EMPRESA:</span> WES Store S.A. de C.V.</p>
                <p><span class="label">DIRECCIÓN:</span> Calle Principal #123, Colonia Centro, Ciudad XYZ, El Salvador</p>
                <p><span class="label">TELÉFONO:</span> (503) 2123-4567</p>
                <p><span class="label">EMAIL:</span> info@wesstore.com</p>
                <p><span class="label">NIT:</span> 0614-270796-101-3</p>
            </td>
            <!-- Datos de la factura -->
            <td style="width: 50%; text-align: right;">
                <p><span class="label">DTE:</span> <?php echo htmlspecialchars(str_pad($pedido_id ?? '0', 10, '0', STR_PAD_LEFT)); ?></p>
                <p><span class="label">FACTURA N°:</span> <?php echo htmlspecialchars($pedido_id ?? 'N/A'); ?></p>
                <p><span class="label">FECHA:</span> <?php echo htmlspecialchars(date('d/m/Y H:i:s')); ?></p>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px dashed #ccc; margin: 15px 0;">

    <table class="info-section">
        <tr>
            <!-- Datos del cliente -->
            <td colspan="2">
                <p><span class="label">CLIENTE:</span> <?php echo htmlspecialchars(trim(($cliente_data['nombres'] ?? '') . ' ' . ($cliente_data['apellidos'] ?? ''))); ?></p>
                <p><span class="label">DUI:</span> <?php echo htmlspecialchars($cliente_data['dui'] ?? 'N/A'); ?></p>
                <p><span class="label">EMAIL:</span> <?php echo htmlspecialchars($cliente_data['email'] ?? 'N/A'); ?></p>
                <p><span class="label">TELÉFONO:</span> <?php echo htmlspecialchars($cliente_data['telefono'] ?? 'N/A'); ?></p>
                <p><span class="label">DIRECCIÓN DE ENVÍO:</span>
                    <?php 
                        echo htmlspecialchars($cliente_data['direccionCompleta'] ?? '');
                        echo ', ' . htmlspecialchars($cliente_data['municipio'] ?? '');
                        echo ', ' . htmlspecialchars($cliente_data['departamento'] ?? '');
                        echo ', El Salvador.';
                    ?>
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
            <?php 
                $total_factura = 0;
                if (!empty($productos_para_pedido) && is_array($productos_para_pedido)): 
                    foreach ($productos_para_pedido as $producto):
                        $cantidad = intval($producto['cantidad'] ?? 0);
                        $precio_unitario = floatval($producto['precio_unitario'] ?? 0);
                        $subtotal = $cantidad * $precio_unitario;
                        $total_factura += $subtotal;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['nombre'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($cantidad); ?></td>
                    <td>$<?php echo htmlspecialchars(number_format($precio_unitario, 2)); ?></td>
                    <td>$<?php echo htmlspecialchars(number_format($subtotal, 2)); ?></td>
                </tr>
            <?php 
                    endforeach;
                else:
            ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No hay productos en este pedido.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="label">SUBTOTAL:</td>
                <td>$<?php echo htmlspecialchars(number_format($total_factura, 2)); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="label total">TOTAL:</td>
                <td class="total">$<?php echo htmlspecialchars(number_format($total_factura, 2)); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Gracias por tu compra.</p>
        <p>Este documento es una factura de ejemplo y puede no ser válido para fines fiscales.</p>
        <p>Generado el: <?php echo htmlspecialchars(date('d/m/Y H:i:s')); ?></p>
    </div>
</body>
</html>