<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php';               // Para registrar bitácora
require_once __DIR__ . '/../../../resources/dompdf/vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

session_start();
header('Content-Type: application/json');

$tipo = $_POST['tipo'] ?? '';
$fi   = $_POST['fecha_ini'] ?? '';
$ff   = $_POST['fecha_fin'] ?? '';
$now  = date('Ymd_His');

$pdo = getConnection();
function esc($t) { return htmlspecialchars($t, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

// 1) Validar tipo
if ($tipo !== 'ordenes_abiertas') {
    echo json_encode(['ok' => false, 'message' => 'Tipo no válido']);
    exit;
}

// ID del usuario para bitácora (puede ser NULL)
$idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;

try {
    // 2) Obtener órdenes pendientes/procesando en el rango
    $stmt = $pdo->prepare("
        SELECT
          p.id_pedido,
          CONCAT(u.nombre,' ',u.apellido) AS nombre_cliente,
          DATE_FORMAT(p.fecha_pedido, '%Y-%m-%d') AS fecha_pedido,
          p.estado,
          p.total
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id_usuario
        WHERE p.estado IN ('Pendiente','Procesando')
          AND DATE(p.fecha_pedido) BETWEEN ? AND ?
        ORDER BY p.fecha_pedido DESC
    ");
    $stmt->execute([$fi, $ff]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Guardar JSON en media/temp
    $folderJson = __DIR__ . '/../../../media/temp/';
    if (!is_dir($folderJson)) mkdir($folderJson, 0755, true);
    $fnJ   = "ordenes_abiertas_{$fi}_{$ff}_{$now}.json";
    $pathJ = $folderJson . $fnJ;
    file_put_contents(
        $pathJ,
        json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    // Registrar en bitácora: lectura/exportación de reporte JSON
    registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        [
            'reporte'    => 'ordenes_abiertas',
            'fecha_ini'  => $fi,
            'fecha_fin'  => $ff,
            'archivo'    => $fnJ,
            'formato'    => 'JSON'
        ]
    );

    // 4) Generar HTML para PDF
    ob_start();
    ?>
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
      <h2>Órdenes Abiertas (<?= esc($fi) ?> a <?= esc($ff) ?>)</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Total ($)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($datos as $r): ?>
            <tr>
              <td><?= esc($r['id_pedido']) ?></td>
              <td><?= esc($r['nombre_cliente']) ?></td>
              <td><?= esc($r['fecha_pedido']) ?></td>
              <td><?= esc($r['estado']) ?></td>
              <td>$<?= number_format($r['total'],2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    // 5) Generar PDF con Dompdf
    $folderPdf = __DIR__ . '/../../../public/reportes_pdf/';
    if (!is_dir($folderPdf)) mkdir($folderPdf, 0755, true);
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $fnP   = "ordenes_abiertas_{$fi}_{$ff}_{$now}.pdf";
    $pathP = $folderPdf . $fnP;
    file_put_contents($pathP, $dompdf->output());

    // Registrar en bitácora: exportación de reporte PDF
    registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        [
            'reporte'    => 'ordenes_abiertas',
            'fecha_ini'  => $fi,
            'fecha_fin'  => $ff,
            'archivo'    => $fnP,
            'formato'    => 'PDF'
        ]
    );

    // 6) Responder al cliente con URLs
    echo json_encode([
        'ok'       => true,
        'pdf_url'  => "public/reportes_pdf/{$fnP}",
        'json_url' => "media/temp/{$fnJ}"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
