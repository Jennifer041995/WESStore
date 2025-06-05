<?php
session_start();
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id   = intval($input['producto_id'] ?? 0);
$cant = intval($input['cantidad']    ?? 1);

if ($id<=0 || $cant<1) {
    echo json_encode(['status'=>'error','message'=>'Datos inválidos']);
    exit;
}

try {
    $pdo = getConnection();
    // obtener stock real
    $stmt = $pdo->prepare("SELECT stock FROM inventario WHERE producto_id = ?");
    $stmt->execute([$id]);
    $stock = ($r=$stmt->fetch()) ? intval($r['stock']) : 0;

    if ($cant > $stock) {
        echo json_encode(['status'=>'error','message'=>'Stock insuficiente']);
        exit;
    }

    // Sesión
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + $cant;

    // Si usuario autenticado, guardar en BD
    if (isset($_SESSION['id_usuario'])) {
      $user = $_SESSION['id_usuario'];
      // obtener o crear carrito en BD
      $pdo->beginTransaction();
      $stmt = $pdo->prepare("SELECT id_carrito FROM carritos WHERE usuario_id = ?");
      $stmt->execute([$user]);
      if (!$row = $stmt->fetch()) {
        $pdo->prepare("INSERT INTO carritos(usuario_id) VALUES(?)")->execute([$user]);
        $carrito_id = $pdo->lastInsertId();
      } else {
        $carrito_id = $row['id_carrito'];
      }
      // insertar o actualizar detalle
      $stmt = $pdo->prepare("
        INSERT INTO detalles_carrito(carrito_id, producto_id, cantidad)
        VALUES(?,?,?)
        ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)
      ");
      $stmt->execute([$carrito_id, $id, $cant]);
      $pdo->commit();
    }

    echo json_encode(['status'=>'success','message'=>'Añadido al carrito']);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status'=>'error','message'=>'Error de servidor']);
}
?>