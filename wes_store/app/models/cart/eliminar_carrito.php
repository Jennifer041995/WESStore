<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['producto_id'] ?? 0);

if ($id <= 0 || !isset($_SESSION['carrito'][$id])) {
    echo json_encode(['status'=>'error','message'=>'Producto no existe en el carrito']);
    exit;
}

// eliminar de sesión
unset($_SESSION['carrito'][$id]);

// si usuario logueado, también quitar de BD
if (isset($_SESSION['id_usuario'])) {
    try {
        $pdo = getConnection();
        $user = $_SESSION['id_usuario'];
        // buscar carrito
        $stmt = $pdo->prepare("SELECT id_carrito FROM carritos WHERE usuario_id = ?");
        $stmt->execute([$user]);
        if ($row = $stmt->fetch()) {
            $carrito_id = $row['id_carrito'];
            $pdo->prepare("DELETE FROM detalles_carrito WHERE carrito_id = ? AND producto_id = ?")
                ->execute([$carrito_id, $id]);
        }
    } catch (PDOException $e) {
        // no interrumpir
    }
}

echo json_encode(['status'=>'success','message'=>'Producto eliminado del carrito']);
