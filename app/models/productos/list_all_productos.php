<?php
// app/models/productos/list_all_products.php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/connection.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query(
        "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock,
                p.categoria_id, p.marca_id,
                IFNULL(i.url_imagen, '../../../media/img/ico.png') AS url_imagen,
                c.nombre_categoria, m.nombre_marca
         FROM productos p
         LEFT JOIN imagenes_productos i ON i.producto_id = p.id
         LEFT JOIN categorias c ON c.id = p.categoria_id
         LEFT JOIN marcas m ON m.id = p.marca_id
         ORDER BY c.nombre_categoria, p.nombre"
    );
    $products = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}
?>
