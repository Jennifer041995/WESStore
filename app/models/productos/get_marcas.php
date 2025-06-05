<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();

    // Seleccionamos sólo id y nombre para el filtro
    $stmt = $pdo->query("
        SELECT 
            id_marca, 
            nombre_marca 
        FROM marcas 
        WHERE estado = 'Activo' 
        ORDER BY nombre_marca
    ");

    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($marcas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al cargar marcas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
