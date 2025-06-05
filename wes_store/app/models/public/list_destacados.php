<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();

    // Obtenemos hasta 12 productos destacados (destacado = 1 y activos),
    // junto con su categoría, subcategoría, marca e imagen principal
    $stmt = $pdo->prepare("
        SELECT 
            p.id_producto,
            p.nombre                AS nombre_producto,
            p.precio,
            c.nombre_categoria,
            sc.nombre_subcategoria,
            m.nombre_marca,
            ip.imagen_url           AS imagen_principal
        FROM productos AS p
        LEFT JOIN categorias AS c 
          ON p.categoria_id = c.id_categoria
        LEFT JOIN subcategorias AS sc 
          ON p.subcategoria_id = sc.id_subcategoria
        LEFT JOIN marcas AS m 
          ON p.marca_id = m.id_marca
        LEFT JOIN imagenes_productos AS ip 
          ON ip.producto_id = p.id_producto 
         AND ip.principal = 1
        WHERE p.destacado = 1
          AND p.estado = 'Activo'
        ORDER BY p.actualizado_en DESC
        LIMIT 12
    ");
    $stmt->execute();
    $destacados = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data'   => $destacados
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al obtener productos destacados',
        'error'   => $e->getMessage()
    ]);
}
?>
