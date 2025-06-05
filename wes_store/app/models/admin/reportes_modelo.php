<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario
header('Content-Type: application/json');
session_start();

$pdo = getConnection();
$tipo = $_POST['tipo'] ?? '';
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

    // Registrar en bitácora la acción de lectura del reporte
    registrarBitacora(
        'reportes',
        null,
        'READ',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
            'filtros' => null  // Sin filtros adicionales para inventario
        ])
    );

    echo json_encode($datos);
    exit;
}

if ($tipo === 'ordenes') {
    $fi = $_POST['fecha_ini'] ?? '';
    $ff = $_POST['fecha_fin'] ?? '';
    if (!$fi || !$ff) {
        echo json_encode([]);
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

    // Registrar en bitácora la acción de lectura del reporte con filtros de fechas
    registrarBitacora(
        'reportes',
        null,
        'READ',
        $idUsuarioLogueado,
        null,
        array_merge($bitParams, [
            'fecha_ini' => $fi,
            'fecha_fin' => $ff
        ])
    );

    echo json_encode($datos);
    exit;
}

echo json_encode([]);
exit;
