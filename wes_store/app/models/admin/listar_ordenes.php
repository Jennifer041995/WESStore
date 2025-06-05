<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

try {
    $pdo = getConnection();
    $stmt = $pdo->query("
      SELECT oc.id_orden_compra, oc.numero_orden, pr.nombre_proveedor, DATE_FORMAT(oc.fecha_orden, '%Y-%m-%d') AS fecha_orden,
             oc.estado, oc.subtotal, oc.iva, oc.total
      FROM ordenes_compra oc
      INNER JOIN proveedores pr ON oc.proveedor_id = pr.id_proveedor
      ORDER BY oc.id_orden_compra DESC
    ");
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
