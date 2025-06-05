<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

$cat = isset($_GET['categoria']) && intval($_GET['categoria'])>0
       ? intval($_GET['categoria'])
       : null;

$pdo = getConnection();
if ($cat) {
    $stmt = $pdo->prepare("
      SELECT id_subcategoria, nombre_subcategoria, categoria_id
        FROM subcategorias
       WHERE estado = 'Activo'
         AND categoria_id = ?
       ORDER BY nombre_subcategoria
    ");
    $stmt->execute([$cat]);
} else {
    $stmt = $pdo->query("
      SELECT id_subcategoria, nombre_subcategoria, categoria_id
        FROM subcategorias
       WHERE estado = 'Activo'
       ORDER BY nombre_subcategoria
    ");
}

$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($list);
