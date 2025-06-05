<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

$pdo   = getConnection();
$buscar = $_GET['buscar'] ?? '';

try {
  $sql = "
    SELECT
      p.id_producto,
      p.sku,
      p.nombre,
      c.nombre_categoria AS categoria,
      p.precio,
      COALESCE(inv.stock,0) AS stock
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id_categoria
    LEFT JOIN inventario inv ON p.id_producto = inv.producto_id
    WHERE p.estado = 'Activo'
  ";
  $params = [];
  if ($buscar) {
    $sql .= " AND p.nombre LIKE :busq";
    $params[':busq'] = "%{$buscar}%";
  }
  $sql .= " ORDER BY p.nombre ASC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
  echo json_encode([]);
}
