<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

$pdo     = getConnection();
$fi      = $_POST['fecha_ini']  ?? '';
$ff      = $_POST['fecha_fin']  ?? '';

try {
  if (!$fi || !$ff) {
    echo json_encode([]);
    exit;
  }
  $stmt = $pdo->prepare("
    SELECT
      p.id_pedido,
      CONCAT(u.nombre,' ',u.apellido) AS nombre_cliente,
      DATE_FORMAT(p.fecha_pedido, '%Y-%m-%d') AS fecha_pedido,
      p.estado,
      p.total
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id_usuario
    WHERE DATE(p.fecha_pedido) BETWEEN ? AND ?
    ORDER BY p.fecha_pedido DESC
  ");
  $stmt->execute([$fi, $ff]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
  echo json_encode([]);
}
