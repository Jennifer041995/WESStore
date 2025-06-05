<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

$pdo = getConnection();
$sku = $_GET['sku'] ?? '';

try {
  $sql = "
    SELECT
      inv.id_inventario,
      p.nombre AS nombre_producto,
      p.sku,
      inv.stock,
      inv.stock_minimo
    FROM inventario inv
    JOIN productos p ON inv.producto_id = p.id_producto
    WHERE p.estado = 'Activo'
  ";
  $params = [];
  if ($sku) {
    $sql .= " AND p.sku LIKE :sku";
    $params[':sku'] = "%{$sku}%";
  }
  $sql .= " ORDER BY inv.id_inventario DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
  echo json_encode([]);
}
