<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$id = intval($_GET['id']);

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
      SELECT d.cantidad, d.precio_unitario, p.nombre
      FROM detalles_orden_compra d
      INNER JOIN productos p ON d.producto_id = p.id_producto
      WHERE d.orden_id = ?
    ");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    echo json_encode([]);
}
