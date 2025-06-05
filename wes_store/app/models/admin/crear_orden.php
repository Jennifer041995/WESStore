<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario

header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['proveedor_id'], $input['fecha_esperada'], $input['detalles'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

$proveedor_id    = intval($input['proveedor_id']);
$fecha_esperada  = $input['fecha_esperada'];
$detalles        = $input['detalles']; // array de { producto_id, cantidad, precio_unitario }

try {
    $pdo = getConnection();
    $pdo->beginTransaction();

    // 1) Generar número de orden (prefijo + timestamp)
    $numero_orden = 'OC-' . date('YmdHis');

    // 2) Calcular subtotal, IVA y total
    $subtotal = 0;
    foreach ($detalles as $d) {
        $subtotal += ($d['cantidad'] * $d['precio_unitario']);
    }
    $iva   = round($subtotal * 0.13, 2);
    $total = round($subtotal + $iva, 2);

    // 3) Insertar en ordenes_compra
    $stmt = $pdo->prepare("
      INSERT INTO ordenes_compra 
        (numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total)
      VALUES (?, ?, ?, NOW(), ?, 'pendiente', ?, ?, ?)
    ");
    // Asumimos $_SESSION['usuario']['id'] guarda el ID del usuario logueado
    $usuario_id = $_SESSION['usuario']['id'] ?? null;
    $stmt->execute([
        $numero_orden,
        $proveedor_id,
        $usuario_id,
        $fecha_esperada,
        $subtotal,
        $iva,
        $total
    ]);
    $orden_id = intval($pdo->lastInsertId());

    // 4) Insertar detalles_orden_compra y actualizar stock en inventario
    $stmtDet = $pdo->prepare("
      INSERT INTO detalles_orden_compra (orden_id, producto_id, cantidad, precio_unitario) 
      VALUES (?, ?, ?, ?)
    ");
    $stmtInv = $pdo->prepare("UPDATE inventario SET stock = stock + ? WHERE producto_id = ?");

    foreach ($detalles as $d) {
        $pid  = intval($d['producto_id']);
        $cant = intval($d['cantidad']);
        $pu   = floatval($d['precio_unitario']);
        $stmtDet->execute([$orden_id, $pid, $cant, $pu]);
        // Incrementar stock en inventario
        $stmtInv->execute([$cant, $pid]);
    }

    $pdo->commit();

    // ----- Auditoría en bitácora -----

    // 5) Obtener datos de la orden recién creada
    $stmtOrder = $pdo->prepare("
        SELECT 
          id_orden_compra AS id, numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total
        FROM ordenes_compra
        WHERE id_orden_compra = ?
        LIMIT 1
    ");
    $stmtOrder->execute([$orden_id]);
    $ordenData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    // 6) Obtener los detalles asociados a esa orden
    $stmtDetails = $pdo->prepare("
        SELECT producto_id, cantidad, precio_unitario
        FROM detalles_orden_compra
        WHERE orden_id = ?
    ");
    $stmtDetails->execute([$orden_id]);
    $detallesData = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

    // 7) Preparar info_posterior consolidada
    $infoPosterior = [
        'orden'   => $ordenData,
        'detalles'=> $detallesData
    ];

    // 8) Registrar en bitácora la inserción de la orden de compra
    registrarBitacora(
        'ordenes_compra',      // tabla_afectada
        $orden_id,             // id_registro_afectado
        'INSERT',              // tipo_operacion
        $usuario_id,           // id_usuario que ejecutó la acción
        null,                  // info_anterior (no existía antes)
        $infoPosterior         // info_posterior con orden + detalles
    );

    echo json_encode(['status' => 'success', 'message' => 'Orden creada con éxito.']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
