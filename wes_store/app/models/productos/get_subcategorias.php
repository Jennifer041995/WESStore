<?php
require_once __DIR__ . '/../sql/conexion.php';

$conn = getConnection();

try {
    $stmt = $conn->query("SELECT id_subcategoria AS id, nombre_subcategoria AS nombre FROM subcategorias");
    $subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subcategorias);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>