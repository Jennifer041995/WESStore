<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT id_marca, nombre_marca, logo FROM marcas WHERE estado = 1");
    $stmt->execute();
    $marcas = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $marcas
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener las marcas',
        'error' => $e->getMessage()
    ]);
}
?>