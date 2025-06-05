<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario
require_once __DIR__ . '/../../../resources/dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');
session_start();

$tipo = $_POST['tipo'] ?? '';
$fi   = $_POST['fecha_ini']  ?? '';
$ff   = $_POST['fecha_fin']  ?? '';
$now  = date('Ymd_His');

// Carpetas
$folderJson = __DIR__ . '/../../../media/temp/';
if (!is_dir($folderJson)) mkdir($folderJson, 0755, true);
$folderPdf  = __DIR__ . '/../../../media/temp/reportes_pdf/';
if (!is_dir($folderPdf)) mkdir($folderPdf, 0755, true);

$pdo = getConnection();
function esc($t) { return htmlspecialchars($t, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

// ID del usuario que solicita el reporte (puede ser null si no hay sesión)
$idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;

// Parámetros para bitácora
$bitParams = [
    'tipo_reporte' => $tipo,
    'fecha_ini'    => $fi,
    'fecha_fin'    => $ff
];

try {
  switch ($tipo) {
    // -----------------------
    // INVENTARIO
    // -----------------------
    case 'inventario':
      $rs = $pdo->query("
        SELECT 
          p.id_producto, p.sku, p.nombre, inv.stock, inv.stock_minimo
        FROM inventario inv
        JOIN productos p ON inv.producto_id = p.id_producto
        WHERE inv.stock <= inv.stock_minimo
        ORDER BY inv.stock ASC
      ");
      $datos = $rs->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "inventario_{$now}.json";
      file_put_contents($folderJson . $fnJ,
        json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
      );

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Reporte de Inventario Crítico</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>SKU</th><th>Nombre</th><th>Stock</th><th>Stock Mínimo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['id_producto']) ?></td>
                <td><?= esc($r['sku']) ?></td>
                <td><?= esc($r['nombre']) ?></td>
                <td><?= esc($r['stock']) ?></td>
                <td><?= esc($r['stock_minimo']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "inventario_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // ÓRDENES
    // -----------------------
    case 'ordenes':
      if (!$fi || !$ff) { echo json_encode(['ok'=>false,'message'=>'Fechas inválidas']); exit; }
      $stmt = $pdo->prepare("
        SELECT 
          oc.id_orden_compra, oc.numero_orden, pr.nombre_proveedor,
          DATE_FORMAT(oc.fecha_orden,'%Y-%m-%d') AS fecha_orden,
          oc.estado, oc.total
        FROM ordenes_compra oc
        JOIN proveedores pr ON oc.proveedor_id = pr.id_proveedor
        WHERE DATE(oc.fecha_orden) BETWEEN ? AND ?
        ORDER BY oc.fecha_orden DESC
      ");
      $stmt->execute([$fi,$ff]);
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "ordenes_{$fi}_{$ff}_{$now}.json";
      file_put_contents($folderJson . $fnJ, 
        json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
      );

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Reporte de Órdenes (<?= esc($fi) ?> a <?= esc($ff) ?>)</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>N° Orden</th><th>Proveedor</th>
              <th>Fecha</th><th>Estado</th><th>Total ($)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['id_orden_compra']) ?></td>
                <td><?= esc($r['numero_orden']) ?></td>
                <td><?= esc($r['nombre_proveedor']) ?></td>
                <td><?= esc($r['fecha_orden']) ?></td>
                <td><?= esc($r['estado']) ?></td>
                <td><?= number_format($r['total'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "ordenes_{$fi}_{$ff}_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // VENTAS POR CATEGORÍA
    // -----------------------
    case 'ventas_categoria':
      if (!$fi || !$ff) { echo json_encode(['ok'=>false,'message'=>'Fechas inválidas']); exit; }
      $stmt = $pdo->prepare("
        SELECT
          c.nombre_categoria,
          SUM(dp.cantidad * dp.precio_unitario) AS total_vendido
        FROM detalles_pedido dp
        JOIN productos p ON dp.producto_id = p.id_producto
        JOIN categorias c ON p.categoria_id = c.id_categoria
        JOIN pedidos pe ON dp.pedido_id = pe.id_pedido
        WHERE DATE(pe.fecha_pedido) BETWEEN ? AND ?
        GROUP BY c.id_categoria
        ORDER BY total_vendido DESC
      ");
      $stmt->execute([$fi,$ff]);
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "ventas_categoria_{$fi}_{$ff}_{$now}.json";
      file_put_contents($folderJson . $fnJ, json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Ventas por Categoría (<?= esc($fi) ?> a <?= esc($ff) ?>)</h2>
        <table>
          <thead>
            <tr>
              <th>Categoría</th><th>Total Vendido ($)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['nombre_categoria']) ?></td>
                <td>$<?= number_format($r['total_vendido'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "ventas_categoria_{$fi}_{$ff}_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // PRODUCTOS MENOS VENDIDOS
    // -----------------------
    case 'productos_menos_vendidos':
      $stmt = $pdo->query("
        SELECT
          p.id_producto, p.sku, p.nombre,
          COALESCE(SUM(dp.cantidad),0) AS unidades_vendidas
        FROM productos p
        LEFT JOIN detalles_pedido dp ON p.id_producto = dp.producto_id
        LEFT JOIN pedidos pe ON dp.pedido_id = pe.id_pedido
          AND pe.fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        WHERE p.estado = 'Activo'
        GROUP BY p.id_producto
        ORDER BY unidades_vendidas ASC
        LIMIT 50
      ");
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "productos_menos_vendidos_{$now}.json";
      file_put_contents($folderJson . $fnJ, json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Productos Menos Vendidos (Últimos 6 meses)</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>SKU</th><th>Nombre</th><th>Unidades Vendidas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['id_producto']) ?></td>
                <td><?= esc($r['sku']) ?></td>
                <td><?= esc($r['nombre']) ?></td>
                <td><?= esc($r['unidades_vendidas']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "productos_menos_vendidos_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // CLIENTES TOP
    // -----------------------
    case 'clientes_top':
      if (!$fi || !$ff) { echo json_encode(['ok'=>false,'message'=>'Fechas inválidas']); exit; }
      $stmt = $pdo->prepare("
        SELECT
          CONCAT(u.nombre,' ',u.apellido) AS nombre_cliente,
          COUNT(*) AS cantidad_pedidos,
          SUM(p.total) AS total_comprado
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id_usuario
        WHERE DATE(p.fecha_pedido) BETWEEN ? AND ?
        GROUP BY u.id_usuario
        ORDER BY total_comprado DESC
        LIMIT 10
      ");
      $stmt->execute([$fi,$ff]);
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "clientes_top_{$fi}_{$ff}_{$now}.json";
      file_put_contents($folderJson . $fnJ, json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Clientes Top (<?= esc($fi) ?> a <?= esc($ff) ?>)</h2>
        <table>
          <thead>
            <tr>
              <th>Cliente</th><th>Cantidad Pedidos</th><th>Total Comprado ($)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['nombre_cliente']) ?></td>
                <td><?= esc($r['cantidad_pedidos']) ?></td>
                <td>$<?= number_format($r['total_comprado'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "clientes_top_{$fi}_{$ff}_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // MOVIMIENTOS DE INVENTARIO
    // -----------------------
    case 'movimientos_inventario':
      if (!$fi || !$ff) { echo json_encode(['ok'=>false,'message'=>'Fechas inválidas']); exit; }
      $stmt = $pdo->prepare("
        SELECT
          mi.id_movimiento_inventario,
          p.nombre AS nombre_producto,
          mi.tipo_movimiento,
          mi.cantidad,
          mi.referencia,
          u.nombre AS usuario,
          DATE_FORMAT(mi.creado_en, '%Y-%m-%d %H:%i') AS fecha
        FROM movimientos_inventario mi
        JOIN productos p ON mi.producto_id = p.id_producto
        LEFT JOIN usuarios u ON mi.usuario_id = u.id_usuario
        WHERE DATE(mi.creado_en) BETWEEN ? AND ?
        ORDER BY mi.creado_en DESC
        LIMIT 100
      ");
      $stmt->execute([$fi,$ff]);
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "movimientos_inventario_{$fi}_{$ff}_{$now}.json";
      file_put_contents($folderJson . $fnJ, json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Movimientos Inventario (<?= esc($fi) ?> a <?= esc($ff) ?>)</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Producto</th><th>Tipo</th>
              <th>Cantidad</th><th>Referencia</th><th>Usuario</th><th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['id_movimiento_inventario']) ?></td>
                <td><?= esc($r['nombre_producto']) ?></td>
                <td><?= esc($r['tipo_movimiento']) ?></td>
                <td><?= esc($r['cantidad']) ?></td>
                <td><?= esc($r['referencia']) ?></td>
                <td><?= esc($r['usuario']) ?></td>
                <td><?= esc($r['fecha']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "movimientos_inventario_{$fi}_{$ff}_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // ÓRDENES PENDIENTES/PAR...
    // -----------------------
    case 'ordenes_pendientes':
      $stmt = $pdo->query("
        SELECT
          oc.id_orden_compra,
          oc.numero_orden,
          pr.nombre_proveedor,
          DATE_FORMAT(oc.fecha_orden,'%Y-%m-%d') AS fecha_orden,
          DATE_FORMAT(oc.fecha_esperada,'%Y-%m-%d') AS fecha_esperada,
          oc.estado,
          oc.total
        FROM ordenes_compra oc
        JOIN proveedores pr ON oc.proveedor_id = pr.id_proveedor
        WHERE oc.estado IN ('pendiente','parcial')
        ORDER BY oc.fecha_esperada ASC
      ");
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "ordenes_pendientes_{$now}.json";
      file_put_contents($folderJson . $fnJ, json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Órdenes Pendientes/Parciales</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th><th>N° Orden</th><th>Proveedor</th>
              <th>Fecha</th><th>Fecha Esperada</th><th>Estado</th><th>Total ($)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['id_orden_compra']) ?></td>
                <td><?= esc($r['numero_orden']) ?></td>
                <td><?= esc($r['nombre_proveedor']) ?></td>
                <td><?= esc($r['fecha_orden']) ?></td>
                <td><?= esc($r['fecha_esperada']) ?></td>
                <td><?= esc($r['estado']) ?></td>
                <td><?= number_format($r['total'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "ordenes_pendientes_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    // -----------------------
    // MARGEN DE GANANCIA
    // -----------------------
    case 'margen':
      if (!$fi || !$ff) { echo json_encode(['ok'=>false,'message'=>'Fechas inválidas']); exit; }
      $stmt = $pdo->prepare("
        SELECT
          p.id_producto,
          p.nombre,
          p.precio,
          p.costo,
          (p.precio - p.costo) AS margen_unitario,
          COALESCE(SUM(dp.cantidad),0) AS unidades_vendidas,
          COALESCE(SUM(dp.cantidad),0) * (p.precio - p.costo) AS margen_total
        FROM productos p
        LEFT JOIN detalles_pedido dp ON p.id_producto = dp.producto_id
        LEFT JOIN pedidos pe ON dp.pedido_id = pe.id_pedido
          AND DATE(pe.fecha_pedido) BETWEEN ? AND ?
        WHERE p.estado = 'Activo'
        GROUP BY p.id_producto
        ORDER BY margen_total DESC
      ");
      $stmt->execute([$fi,$ff]);
      $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // → JSON
      $fnJ = "margen_{$fi}_{$ff}_{$now}.json";
      file_put_contents($folderJson . $fnJ, json_encode($datos, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

      // → HTML PDF
      ob_start(); ?>
      <!DOCTYPE html>
      <html lang="es">
      <head><meta charset="UTF-8">
      <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #444; padding:6px; text-align:center; }
        th { background-color:#eee; }
        h2 { text-align:center; margin-bottom:10px; }
      </style>
      </head>
      <body>
        <h2>Margen de Ganancia (<?= esc($fi) ?> a <?= esc($ff) ?>)</h2>
        <table>
          <thead>
            <tr>
              <th>ID Producto</th><th>Nombre</th><th>Precio</th><th>Costo</th>
              <th>Margen Unitario ($)</th><th>Unidades Vendidas</th><th>Margen Total ($)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($datos as $r): ?>
              <tr>
                <td><?= esc($r['id_producto']) ?></td>
                <td><?= esc($r['nombre']) ?></td>
                <td>$<?= number_format($r['precio'],2) ?></td>
                <td>$<?= number_format($r['costo'],2) ?></td>
                <td>$<?= number_format($r['margen_unitario'],2) ?></td>
                <td><?= esc($r['unidades_vendidas']) ?></td>
                <td>$<?= number_format($r['margen_total'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </body>
      </html>
      <?php
      $html = ob_get_clean();
      $options = new Options(); $options->set('isRemoteEnabled', true);
      $dompdf = new Dompdf($options);
      $dompdf->loadHtml($html, 'UTF-8');
      $dompdf->setPaper('A4','landscape');
      $dompdf->render();

      $fnP = "margen_{$fi}_{$ff}_{$now}.pdf";
      file_put_contents($folderPdf . $fnP, $dompdf->output());

      // Registrar en bitácora
      registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
          'archivo_json' => $fnJ,
          'archivo_pdf'  => $fnP
        ])
      );

      echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
      ]);
      exit;

    default:
      echo json_encode(['ok'=>false,'message'=>'Tipo no válido.']);
      exit;
  }
} catch (Exception $e) {
  echo json_encode(['ok'=>false,'message'=>$e->getMessage()]);
}
