<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

try {
    $pdo = getConnection();

    // 1) Total de productos activos
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM productos WHERE estado = 'Activo'");
    $total_productos = $stmt->fetch()['total'];

    // 2) Órdenes pendientes
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE estado = 'pendiente'");
    $ordenes_pendientes = $stmt->fetch()['total'];

    // 3) Proveedores activos
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM proveedores WHERE estado = 'Activo'");
    $proveedores_activos = $stmt->fetch()['total'];

    // 4) Stock crítico: productos cuyo stock <= stock_minimo
    $stmt = $pdo->query("
      SELECT COUNT(*) AS total 
      FROM inventario inv 
      JOIN productos p ON inv.producto_id = p.id_producto
      WHERE inv.stock <= inv.stock_minimo
    ");
    $stock_critico = $stmt->fetch()['total'];

    echo json_encode([
        'status' => 'success',
        'total_productos' => (int)$total_productos,
        'ordenes_pendientes' => (int)$ordenes_pendientes,
        'proveedores_activos' => (int)$proveedores_activos,
        'stock_critico' => (int)$stock_critico
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
