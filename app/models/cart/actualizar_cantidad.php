<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

if (!isset($_POST['id'], $_POST['cantidad'])) {
    echo json_encode(['status'=>'error','message'=>'Datos incompletos']);
    exit;
}

$id = intval($_POST['id']);
$cant = intval($_POST['cantidad']);
if ($cant < 1) {
    echo json_encode(['status'=>'error','message'=>'La cantidad debe ser ≥ 1']);
    exit;
}

if (!isset($_SESSION['carrito'][$id])) {
    echo json_encode(['status'=>'error','message'=>'Producto no está en carrito']);
    exit;
}

// validar stock real
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT stock FROM inventario WHERE producto_id = ?");
    $stmt->execute([$id]);
    $stock = ($r = $stmt->fetch()) ? intval($r['stock']) : 0;
    if ($cant > $stock) {
        echo json_encode(['status'=>'error','message'=>'Stock insuficiente']);
        exit;
    }

    // actualizar sesión
    $_SESSION['carrito'][$id] = $cant;

    // si usuario logueado, actualizar BD
    if (isset($_SESSION['id_usuario'])) {
        $user = $_SESSION['id_usuario'];
        $pdo->beginTransaction();
        // obtener o crear carrito
        $stmt = $pdo->prepare("SELECT id_carrito FROM carritos WHERE usuario_id = ?");
        $stmt->execute([$user]);
        if (!$row = $stmt->fetch()) {
            $pdo->prepare("INSERT INTO carritos(usuario_id) VALUES(?)")->execute([$user]);
            $carrito_id = $pdo->lastInsertId();
        } else {
            $carrito_id = $row['id_carrito'];
        }
        // update detalle
        $pdo->prepare("
            INSERT INTO detalles_carrito(carrito_id, producto_id, cantidad)
            VALUES(?,?,?)
            ON DUPLICATE KEY UPDATE cantidad = ?
        ")->execute([$carrito_id, $id, $cant, $cant]);
        $pdo->commit();
    }

    echo json_encode(['status'=>'success','message'=>'Cantidad actualizada']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status'=>'error','message'=>'Error de servidor']);
}
