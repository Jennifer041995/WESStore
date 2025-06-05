<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT id_categoria, nombre_categoria, descripcion, imagen FROM categorias WHERE estado = 1");
    $stmt->execute();
    $categorias = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $categorias
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener las categorías',
        'error' => $e->getMessage()
    ]);
}
