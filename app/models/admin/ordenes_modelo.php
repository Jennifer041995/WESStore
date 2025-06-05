<?php
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario
header('Content-Type: application/json');
session_start();

$pdo = getConnection();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            // Listar todas las órdenes con datos de proveedor
            $stmt = $pdo->query("
              SELECT 
                oc.id_orden_compra,
                prov.nombre_proveedor,
                DATE_FORMAT(oc.fecha_orden, '%Y-%m-%d') AS fecha_orden,
                DATE_FORMAT(oc.fecha_esperada, '%Y-%m-%d') AS fecha_esperada,
                oc.subtotal,
                oc.iva,
                oc.total,
                oc.estado
              FROM ordenes_compra oc
              JOIN proveedores prov ON oc.proveedor_id = prov.id_proveedor
              ORDER BY oc.id_orden_compra DESC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'obtener':
            // Obtener una sola orden con sus detalles
            $id = intval($_GET['id']);
            // 1) Cabecera
            $stmt = $pdo->prepare("
              SELECT 
                oc.id_orden_compra,
                oc.proveedor_id,
                prov.nombre_proveedor,
                DATE_FORMAT(oc.fecha_orden, '%Y-%m-%d') AS fecha_orden,
                DATE_FORMAT(oc.fecha_esperada, '%Y-%m-%d') AS fecha_esperada,
                oc.subtotal,
                oc.iva,
                oc.total,
                oc.estado,
                oc.notas
              FROM ordenes_compra oc
              JOIN proveedores prov ON oc.proveedor_id = prov.id_proveedor
              WHERE oc.id_orden_compra = ?
            ");
            $stmt->execute([$id]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$orden) {
                echo json_encode(['status' => 'error', 'message' => 'Orden no encontrada.']);
                exit;
            }
            // 2) Detalles
            $stmt2 = $pdo->prepare("
              SELECT 
                od.producto_id,
                prod.nombre AS nombre_producto,
                od.cantidad,
                od.precio_unitario
              FROM detalles_orden_compra od
              JOIN productos prod ON od.producto_id = prod.id_producto
              WHERE od.orden_id = ?
            ");
            $stmt2->execute([$id]);
            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $orden['detalles'] = $detalles;
            echo json_encode($orden);
            break;

        case 'agregar':
            // Agregar una nueva orden (cabecera + detalles)
            $raw = $_POST['payload'] ?? '';
            $data = json_decode($raw, true);
            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
                exit;
            }
            // Validación mínima
            if (empty($data['proveedor_id']) || empty($data['fecha_orden'])) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
                exit;
            }
            $provId        = intval($data['proveedor_id']);
            $fechaOrden    = $data['fecha_orden'];
            $fechaEsperada = $data['fecha_esperada'] ?? null;
            $subtotal      = floatval($data['subtotal']);
            $iva           = floatval($data['iva']);
            $total         = floatval($data['total']);
            $notas         = $data['notas'] ?? '';
            $detalles      = $data['detalles'] ?? [];
            if (count($detalles) === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Debe agregar al menos un detalle.']);
                exit;
            }

            // 1) Prep datos previos (no hay anteriores porque es INSERT)

            $pdo->beginTransaction();
            // 2) Insertar cabecera
            $stmt = $pdo->prepare("
              INSERT INTO ordenes_compra
                (numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total, notas)
              VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            // Generar número de orden único (prefijo + timestamp)
            $numeroOrden = 'OC-' . date('YmdHis');
            $usuarioId   = $_SESSION['usuario']['id'] ?? null;
            $estado      = 'pendiente';
            $stmt->execute([
              $numeroOrden,
              $provId,
              $usuarioId,
              $fechaOrden,
              $fechaEsperada,
              $estado,
              $subtotal,
              $iva,
              $total,
              $notas
            ]);
            $nuevoId = intval($pdo->lastInsertId());

            // 3) Insertar cada detalle
            $stmtDet = $pdo->prepare("
              INSERT INTO detalles_orden_compra
                (orden_id, producto_id, cantidad, precio_unitario)
              VALUES (?, ?, ?, ?)
            ");
            foreach ($detalles as $d) {
                $prodId = intval($d['producto_id']);
                $cant   = intval($d['cantidad']);
                $pu     = floatval($d['precio_unitario']);
                $stmtDet->execute([$nuevoId, $prodId, $cant, $pu]);
            }

            $pdo->commit();

            // 4) Obtener datos posteriores al INSERT (orden + detalles)
            $stmtOrder = $pdo->prepare("
                SELECT 
                  id_orden_compra, numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total, notas
                FROM ordenes_compra
                WHERE id_orden_compra = ?
                LIMIT 1
            ");
            $stmtOrder->execute([$nuevoId]);
            $ordenCab = $stmtOrder->fetch(PDO::FETCH_ASSOC);

            $stmtDetails = $pdo->prepare("
                SELECT producto_id, cantidad, precio_unitario
                FROM detalles_orden_compra
                WHERE orden_id = ?
            ");
            $stmtDetails->execute([$nuevoId]);
            $detallesData = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

            $infoPosterior = [
                'orden'   => $ordenCab,
                'detalles'=> $detallesData
            ];

            // 5) Registrar en bitácora
            registrarBitacora(
                'ordenes_compra',
                $nuevoId,
                'INSERT',
                $usuarioId,
                null,
                $infoPosterior
            );

            echo json_encode(['status' => 'success', 'message' => 'Orden creada correctamente.']);
            break;

        case 'editar':
            // Editar una orden existente (actualizar cabecera y detalles)
            $raw = $_POST['payload'] ?? '';
            $data = json_decode($raw, true);
            if (!$data || empty($data['id_orden'])) {
                echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
                exit;
            }
            $idOrden       = intval($data['id_orden']);
            $provId        = intval($data['proveedor_id']);
            $fechaOrden    = $data['fecha_orden'];
            $fechaEsperada = $data['fecha_esperada'] ?? null;
            $subtotal      = floatval($data['subtotal']);
            $iva           = floatval($data['iva']);
            $total         = floatval($data['total']);
            $notas         = $data['notas'] ?? '';
            $detalles      = $data['detalles'] ?? [];

            // 1) Obtener datos previos (cabecera + detalles)
            $stmtPrevCab = $pdo->prepare("
                SELECT 
                  id_orden_compra, numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total, notas
                FROM ordenes_compra
                WHERE id_orden_compra = ?
                LIMIT 1
            ");
            $stmtPrevCab->execute([$idOrden]);
            $cabeceraAnterior = $stmtPrevCab->fetch(PDO::FETCH_ASSOC);
            if (!$cabeceraAnterior) {
                echo json_encode(['status' => 'error', 'message' => 'Orden no encontrada.']);
                exit;
            }
            $stmtPrevDet = $pdo->prepare("
                SELECT producto_id, cantidad, precio_unitario
                FROM detalles_orden_compra
                WHERE orden_id = ?
            ");
            $stmtPrevDet->execute([$idOrden]);
            $detallesAnterior = $stmtPrevDet->fetchAll(PDO::FETCH_ASSOC);

            $pdo->beginTransaction();
            // 2) Actualizar cabecera
            $stmt = $pdo->prepare("
              UPDATE ordenes_compra SET 
                proveedor_id = ?, 
                fecha_orden = ?, 
                fecha_esperada = ?, 
                subtotal = ?, 
                iva = ?, 
                total = ?, 
                notas = ?
              WHERE id_orden_compra = ?
            ");
            $stmt->execute([
              $provId,
              $fechaOrden,
              $fechaEsperada,
              $subtotal,
              $iva,
              $total,
              $notas,
              $idOrden
            ]);

            // 3) Borrar detalles antiguos
            $borrar = $pdo->prepare("DELETE FROM detalles_orden_compra WHERE orden_id = ?");
            $borrar->execute([$idOrden]);

            // 4) Insertar nuevos detalles
            $stmtDet = $pdo->prepare("
              INSERT INTO detalles_orden_compra
                (orden_id, producto_id, cantidad, precio_unitario)
              VALUES (?, ?, ?, ?)
            ");
            foreach ($detalles as $d) {
                $prodId = intval($d['producto_id']);
                $cant   = intval($d['cantidad']);
                $pu     = floatval($d['precio_unitario']);
                $stmtDet->execute([$idOrden, $prodId, $cant, $pu]);
            }

            $pdo->commit();

            // 5) Obtener datos posteriores (cabecera + detalles)
            $stmtPostCab = $pdo->prepare("
                SELECT 
                  id_orden_compra, numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total, notas
                FROM ordenes_compra
                WHERE id_orden_compra = ?
                LIMIT 1
            ");
            $stmtPostCab->execute([$idOrden]);
            $cabeceraPosterior = $stmtPostCab->fetch(PDO::FETCH_ASSOC);

            $stmtPostDet = $pdo->prepare("
                SELECT producto_id, cantidad, precio_unitario
                FROM detalles_orden_compra
                WHERE orden_id = ?
            ");
            $stmtPostDet->execute([$idOrden]);
            $detallesPosterior = $stmtPostDet->fetchAll(PDO::FETCH_ASSOC);

            $infoAnterior = [
                'cabecera'=> $cabeceraAnterior,
                'detalles'=> $detallesAnterior
            ];
            $infoPosterior = [
                'cabecera'=> $cabeceraPosterior,
                'detalles'=> $detallesPosterior
            ];

            // 6) Registrar en bitácora
            $usuarioIdLog = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'ordenes_compra',
                $idOrden,
                'UPDATE',
                $usuarioIdLog,
                $infoAnterior,
                $infoPosterior
            );

            echo json_encode(['status' => 'success', 'message' => 'Orden actualizada correctamente.']);
            break;

        case 'eliminar':
            $id = intval($_POST['id_orden']);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID de orden inválido.']);
                exit;
            }

            // 1) Obtener datos previos (cabecera + detalles)
            $stmtPrevCab = $pdo->prepare("
                SELECT 
                  id_orden_compra, numero_orden, proveedor_id, usuario_id, fecha_orden, fecha_esperada, estado, subtotal, iva, total, notas
                FROM ordenes_compra
                WHERE id_orden_compra = ?
                LIMIT 1
            ");
            $stmtPrevCab->execute([$id]);
            $cabeceraAnterior = $stmtPrevCab->fetch(PDO::FETCH_ASSOC);
            if (!$cabeceraAnterior) {
                echo json_encode(['status' => 'error', 'message' => 'Orden no encontrada.']);
                exit;
            }
            $stmtPrevDet = $pdo->prepare("
                SELECT producto_id, cantidad, precio_unitario
                FROM detalles_orden_compra
                WHERE orden_id = ?
            ");
            $stmtPrevDet->execute([$id]);
            $detallesAnterior = $stmtPrevDet->fetchAll(PDO::FETCH_ASSOC);

            // 2) Borrar detalles primero
            $delDet = $pdo->prepare("DELETE FROM detalles_orden_compra WHERE orden_id = ?");
            $delDet->execute([$id]);

            // 3) Borrar cabecera
            $delOrd = $pdo->prepare("DELETE FROM ordenes_compra WHERE id_orden_compra = ?");
            $delOrd->execute([$id]);

            // 4) Registrar en bitácora
            $usuarioIdLog = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'ordenes_compra',
                $id,
                'DELETE',
                $usuarioIdLog,
                [
                    'cabecera'=> $cabeceraAnterior,
                    'detalles'=> $detallesAnterior
                ],
                null
            );

            echo json_encode(['status' => 'success', 'message' => 'Orden eliminada.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción inválida.']);
            break;
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
