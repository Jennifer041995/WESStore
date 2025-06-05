<?php
require_once __DIR__ . '/../sql/conexion.php';

$conn = getConnection();

try {
    $stmt = $conn->query("SELECT id_categoria AS id, nombre_categoria AS nombre FROM categorias");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categorias);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>