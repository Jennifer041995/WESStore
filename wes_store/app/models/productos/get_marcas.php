<?php
require_once __DIR__ . '/../sql/conexion.php';

$conn = getConnection();

try {
    $stmt = $conn->query("SELECT id_marca AS id, nombre_marca AS nombre FROM marcas");
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($marcas);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>