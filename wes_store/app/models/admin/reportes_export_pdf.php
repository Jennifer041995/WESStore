<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario
require_once __DIR__ . '/../../../resources/dompdf/vendor/autoload.php'; // Ajusta ruta si usas otro autoload

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');
session_start();

$tipo = $_POST['tipo'] ?? '';
$now  = date('Ymd_His');
$folder = __DIR__ . '/../../../media/temp/';
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

$pdo = getConnection();
// ID del usuario que genera el reporte (puede ser null si no hay sesión)
$idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;

// Parámetros para auditoría
$bitParams = [
    'tipo_reporte' => $tipo
];

if ($tipo === 'inventario') {
    // Obtener datos de inventario crítico
    $stmt = $pdo->query("
      SELECT 
        p.id_producto, p.sku, p.nombre, inv.stock, inv.stock_minimo
      FROM inventario inv
      JOIN productos p ON inv.producto_id = p.id_producto
      WHERE inv.stock <= inv.stock_minimo
      ORDER BY inv.stock ASC
    ");
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar HTML para PDF
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: center; }
        th { background-color: #eee; }
        h2 { text-align: center; }
      </style>
    </head>
    <body>
      <h2>Reporte de Inventario Crítico</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>SKU</th>
            <th>Nombre</th>
            <th>Stock</th>
            <th>Stock Mínimo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($datos as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['id_producto']) ?></td>
              <td><?= htmlspecialchars($row['sku']) ?></td>
              <td><?= htmlspecialchars($row['nombre']) ?></td>
              <td><?= htmlspecialchars($row['stock']) ?></td>
              <td><?= htmlspecialchars($row['stock_minimo']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    // Configurar Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Guardar en carpeta temp
    $filename = "inventario_{$now}.pdf";
    $fullpath = $folder . $filename;
    file_put_contents($fullpath, $dompdf->output());

    // Registrar en bitácora
    registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
            'archivo_pdf' => $filename
        ])
    );

    echo json_encode(['ok' => true, 'url' => "media/temp/{$filename}"]);
    exit;
}

if ($tipo === 'ordenes') {
    $fi = $_POST['fecha_ini'] ?? '';
    $ff = $_POST['fecha_fin'] ?? '';
    if (!$fi || !$ff) {
        echo json_encode(['ok' => false, 'message' => 'Fechas inválidas.']);
        exit;
    }

    // Obtener datos de órdenes
    $stmt = $pdo->prepare("
      SELECT 
        oc.id_orden_compra, oc.numero_orden, pr.nombre_proveedor,
        DATE_FORMAT(oc.fecha_orden, '%Y-%m-%d') AS fecha_orden,
        oc.estado, oc.total
      FROM ordenes_compra oc
      JOIN proveedores pr ON oc.proveedor_id = pr.id_proveedor
      WHERE DATE(oc.fecha_orden) BETWEEN ? AND ?
      ORDER BY oc.fecha_orden DESC
    ");
    $stmt->execute([$fi, $ff]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generar HTML para PDF
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: center; }
        th { background-color: #eee; }
        h2 { text-align: center; }
      </style>
    </head>
    <body>
      <h2>Reporte de Órdenes (<?= htmlspecialchars($fi) ?> a <?= htmlspecialchars($ff) ?>)</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>N° Orden</th>
            <th>Proveedor</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Total ($)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($datos as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['id_orden_compra']) ?></td>
              <td><?= htmlspecialchars($row['numero_orden']) ?></td>
              <td><?= htmlspecialchars($row['nombre_proveedor']) ?></td>
              <td><?= htmlspecialchars($row['fecha_orden']) ?></td>
              <td><?= htmlspecialchars($row['estado']) ?></td>
              <td><?= number_format($row['total'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    // Configurar Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Guardar en carpeta temp
    $filename = "ordenes_{$fi}_{$ff}_{$now}.pdf";
    $fullpath = $folder . $filename;
    file_put_contents($fullpath, $dompdf->output());

    // Registrar en bitácora
    registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
            'fecha_ini'    => $fi,
            'fecha_fin'    => $ff,
            'archivo_pdf'  => $filename
        ])
    );

    echo json_encode(['ok' => true, 'url' => "media/temp/{$filename}"]);
    exit;
}

echo json_encode(['ok' => false, 'message' => 'Tipo no válido.']);
