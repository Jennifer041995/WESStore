<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();

    // Obtenemos todas las subcategorías activas, junto con el nombre de su categoría padre
    $stmt = $pdo->prepare("
        SELECT 
            s.id_subcategoria,
            s.nombre_subcategoria,
            s.imagen,
            c.nombre_categoria
        FROM subcategorias AS s
        JOIN categorias AS c 
          ON s.categoria_id = c.id_categoria
        WHERE s.estado = 'Activo'
        ORDER BY c.nombre_categoria ASC, s.orden ASC
    ");
    $stmt->execute();
    $subcategorias = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data'   => $subcategorias
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al obtener las subcategorías',
        'error'   => $e->getMessage()
    ]);
}
?>
