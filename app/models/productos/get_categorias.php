<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();

    // Seleccionamos sólo id y nombre para el filtro
    $stmt = $pdo->query("
        SELECT 
            id_categoria, 
            nombre_categoria 
        FROM categorias 
        WHERE estado = 'Activo' 
        ORDER BY nombre_categoria
    ");

    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categorias, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Si hay un error, devolvemos un array vacío o un mensaje de error
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al cargar categorías: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
