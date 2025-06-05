<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario

$pdo = getConnection();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            // Listar todos los proveedores
            $stmt = $pdo->query("
              SELECT 
                id_proveedor, nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado
              FROM proveedores
              ORDER BY id_proveedor DESC
            ");
            echo json_encode($stmt->fetchAll());
            break;

        case 'obtener':
            // Obtener un solo proveedor por ID
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("
              SELECT 
                id_proveedor, nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado
              FROM proveedores
              WHERE id_proveedor = ?
            ");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch());
            break;

        case 'agregar':
            // Agregar nuevo proveedor
            $nombre_proveedor = trim($_POST['nombre_proveedor']);
            $contacto         = trim($_POST['contacto']);
            $email            = trim($_POST['email']);
            $telefono         = trim($_POST['telefono']);
            $direccion        = trim($_POST['direccion']);
            $ciudad           = trim($_POST['ciudad']);
            $pais             = trim($_POST['pais']);
            $estado           = $_POST['estado'];

            // (Opcional) Validar nombre_proveedor obligatorio
            if ($nombre_proveedor === '') {
                echo json_encode([
                  'status' => 'error',
                  'message' => 'El nombre del proveedor es obligatorio.'
                ]);
                exit;
            }

            // 1) Insertar nuevo proveedor
            $stmtInsert = $pdo->prepare("
              INSERT INTO proveedores 
                (nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->execute([
                $nombre_proveedor,
                $contacto,
                $email,
                $telefono,
                $direccion,
                $ciudad,
                $pais,
                $estado
            ]);
            $nuevoId = intval($pdo->lastInsertId());

            // 2) Obtener datos posteriores al INSERT
            $stmtPost = $pdo->prepare("
                SELECT 
                  id_proveedor, nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado
                FROM proveedores
                WHERE id_proveedor = ?
                LIMIT 1
            ");
            $stmtPost->execute([$nuevoId]);
            $proveedorPosterior = $stmtPost->fetch(PDO::FETCH_ASSOC);

            // 3) Registrar en bitácora
            $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'proveedores',          // tabla_afectada
                $nuevoId,               // id_registro_afectado
                'INSERT',               // tipo_operacion
                $idUsuarioLogueado,     // id_usuario
                null,                   // info_anterior
                $proveedorPosterior     // info_posterior
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Proveedor agregado correctamente.'
            ]);
            break;

        case 'editar':
            // Editar proveedor existente
            $id_proveedor     = intval($_POST['id_proveedor']);
            $nombre_proveedor = trim($_POST['nombre_proveedor']);
            $contacto         = trim($_POST['contacto']);
            $email            = trim($_POST['email']);
            $telefono         = trim($_POST['telefono']);
            $direccion        = trim($_POST['direccion']);
            $ciudad           = trim($_POST['ciudad']);
            $pais             = trim($_POST['pais']);
            $estado           = $_POST['estado'];

            if ($nombre_proveedor === '') {
                echo json_encode([
                  'status' => 'error',
                  'message' => 'El nombre del proveedor es obligatorio.'
                ]);
                exit;
            }

            // 1) Obtener datos previos
            $stmtPrev = $pdo->prepare("
                SELECT 
                  id_proveedor, nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado
                FROM proveedores
                WHERE id_proveedor = ?
                LIMIT 1
            ");
            $stmtPrev->execute([$id_proveedor]);
            $proveedorAnterior = $stmtPrev->fetch(PDO::FETCH_ASSOC);

            if (!$proveedorAnterior) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado.'
                ]);
                exit;
            }

            // 2) Actualizar proveedor
            $stmtUpdate = $pdo->prepare("
              UPDATE proveedores SET 
                nombre_proveedor = ?, 
                contacto         = ?, 
                email            = ?, 
                telefono         = ?, 
                direccion        = ?, 
                ciudad           = ?, 
                pais             = ?, 
                estado           = ?
              WHERE id_proveedor = ?
            ");
            $stmtUpdate->execute([
                $nombre_proveedor,
                $contacto,
                $email,
                $telefono,
                $direccion,
                $ciudad,
                $pais,
                $estado,
                $id_proveedor
            ]);

            // 3) Obtener datos posteriores al UPDATE
            $stmtPost = $pdo->prepare("
                SELECT 
                  id_proveedor, nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado
                FROM proveedores
                WHERE id_proveedor = ?
                LIMIT 1
            ");
            $stmtPost->execute([$id_proveedor]);
            $proveedorPosterior = $stmtPost->fetch(PDO::FETCH_ASSOC);

            // 4) Registrar en bitácora
            $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'proveedores',
                $id_proveedor,
                'UPDATE',
                $idUsuarioLogueado,
                $proveedorAnterior,
                $proveedorPosterior
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Proveedor actualizado correctamente.'
            ]);
            break;

        case 'eliminar':
            $id_proveedor = intval($_POST['id_proveedor']);

            // 1) Obtener datos previos
            $stmtPrev = $pdo->prepare("
                SELECT 
                  id_proveedor, nombre_proveedor, contacto, email, telefono, direccion, ciudad, pais, estado
                FROM proveedores
                WHERE id_proveedor = ?
                LIMIT 1
            ");
            $stmtPrev->execute([$id_proveedor]);
            $proveedorAnterior = $stmtPrev->fetch(PDO::FETCH_ASSOC);

            if (!$proveedorAnterior) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado.'
                ]);
                exit;
            }

            // 2) Eliminar proveedor
            $stmtDelete = $pdo->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
            $stmtDelete->execute([$id_proveedor]);

            // 3) Registrar en bitácora
            $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'proveedores',
                $id_proveedor,
                'DELETE',
                $idUsuarioLogueado,
                $proveedorAnterior,
                null
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Proveedor eliminado.'
            ]);
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción inválida.'
            ]);
            break;
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
