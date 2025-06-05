<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario
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

// Parámetros comunes para auditoría
$bitParams = [
    'tipo_reporte' => $tipo
];

if ($tipo === 'inventario') {
    $stmt = $pdo->query("
      SELECT 
        p.id_producto, p.sku, p.nombre, inv.stock, inv.stock_minimo
      FROM inventario inv
      JOIN productos p ON inv.producto_id = p.id_producto
      WHERE inv.stock <= inv.stock_minimo
      ORDER BY inv.stock ASC
    ");
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = "inventario_{$now}.json";
    $fullpath = $folder . $filename;
    file_put_contents(
        $fullpath,
        json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    // Registrar en bitácora
    registrarBitacora(
        'reportes',
        null,
        'EXPORT',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
            'archivo_json' => $filename
        ])
    );

    echo json_encode([
        'ok'  => true, 
        'url' => "media/temp/{$filename}"
    ]);
    exit;
}

if ($tipo === 'ordenes') {
    $fi = $_POST['fecha_ini'] ?? '';
    $ff = $_POST['fecha_fin'] ?? '';
    if (!$fi || !$ff) {
        echo json_encode(['ok' => false, 'message' => 'Fechas inválidas.']);
        exit;
    }

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

    $filename = "ordenes_{$fi}_{$ff}_{$now}.json";
    $fullpath = $folder . $filename;
    file_put_contents(
        $fullpath,
        json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

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
            'archivo_json' => $filename
        ])
    );

    echo json_encode([
        'ok'  => true, 
        'url' => "media/temp/{$filename}"
    ]);
    exit;
}

echo json_encode(['ok' => false, 'message' => 'Tipo no válido.']);
