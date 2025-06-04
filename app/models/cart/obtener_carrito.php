<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

if (empty($_SESSION['carrito'])) {
    echo json_encode(['status' => 'empty', 'data' => []]);
    exit;
}

try {
    $pdo = getConnection();
    $carrito = $_SESSION['carrito'];
    $ids = array_keys($carrito);
    $placeholders = rtrim(str_repeat('?,', count($ids)), ',');

    $stmt = $pdo->prepare("
        SELECT 
          p.id_producto, p.nombre, p.precio,
          /* traer imagen principal */
          (SELECT imagen_url 
             FROM imagenes_productos ip 
             WHERE ip.producto_id = p.id_producto AND principal = 1 
             LIMIT 1
          ) AS imagen_principal,
          /* stock disponible */
          COALESCE((SELECT stock FROM inventario WHERE producto_id = p.id_producto), 0) AS stock
        FROM productos p
        WHERE p.id_producto IN ($placeholders)
    ");
    $stmt->execute($ids);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productos as &$p) {
        $p['cantidad'] = $carrito[$p['id_producto']];
        $p['subtotal'] = $p['precio'] * $p['cantidad'];
    }

    echo json_encode(['status' => 'success', 'data' => $productos]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener el carrito']);
}