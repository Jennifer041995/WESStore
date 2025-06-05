<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Para registrar la bitácora

try {
    // 1) Validar que el usuario esté autenticado
    if (!isset($_SESSION['usuario']['id'])) {
        echo json_encode([
            'ok'      => false,
            'message' => 'Usuario no autenticado.'
        ]);
        exit;
    }
    $usuario_id = intval($_SESSION['usuario']['id']);

    // 2) Leer el payload JSON enviado desde AJAX
    $input = json_decode(file_get_contents('php://input'), true);

    $alias         = trim($input['alias'] ?? '');
    $direccion     = trim($input['direccion'] ?? '');
    $ciudad        = trim($input['ciudad'] ?? '');
    $departamento  = trim($input['departamento'] ?? '');
    $codigo_postal = trim($input['codigo_postal'] ?? '');
    $pais          = trim($input['pais'] ?? '');

    // 3) Validar campos obligatorios
    if ($alias === '' || $direccion === '' || $ciudad === '' || $departamento === '') {
        echo json_encode([
            'ok'      => false,
            'message' => 'Alias, dirección, ciudad y departamento son obligatorios.'
        ]);
        exit;
    }

    // 4) Conectar a la base de datos
    $pdo = getConnection();

    // 5) Verificar si ya existe una dirección “principal” para este usuario
    $stmtCheck = $pdo->prepare("
        SELECT *
          FROM direcciones_usuarios 
         WHERE usuario_id = ? 
           AND principal = 1
         LIMIT 1
    ");
    $stmtCheck->execute([$usuario_id]);
    $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        // 5.a) Si existe → hacemos UPDATE de los campos
        $id_direccion = intval($fila['id_direccion_usuario']);

        // Construir infoAnterior
        $infoAnterior = [
            'id_direccion_usuario' => intval($fila['id_direccion_usuario']),
            'usuario_id'           => intval($fila['usuario_id']),
            'alias'                => $fila['alias'],
            'direccion'            => $fila['direccion'],
            'ciudad'               => $fila['ciudad'],
            'departamento'         => $fila['departamento'],
            'codigo_postal'        => $fila['codigo_postal'],
            'pais'                 => $fila['pais'],
            'principal'            => intval($fila['principal'])
        ];

        $stmtUpd = $pdo->prepare("
            UPDATE direcciones_usuarios
               SET alias         = ?,
                   direccion     = ?,
                   ciudad        = ?,
                   departamento  = ?,
                   codigo_postal = ?,
                   pais          = ?
             WHERE id_direccion_usuario = ?
        ");
        $ok = $stmtUpd->execute([
            $alias,
            $direccion,
            $ciudad,
            $departamento,
            $codigo_postal,
            $pais ?: 'El Salvador',
            $id_direccion
        ]);

        if ($ok) {
            // Construir infoPosterior
            $infoPosterior = [
                'id_direccion_usuario' => $id_direccion,
                'usuario_id'           => $usuario_id,
                'alias'                => $alias,
                'direccion'            => $direccion,
                'ciudad'               => $ciudad,
                'departamento'         => $departamento,
                'codigo_postal'        => $codigo_postal,
                'pais'                 => $pais ?: 'El Salvador',
                'principal'            => 1
            ];

            // Registrar en bitácora
            registrarBitacora(
                'direcciones_usuarios',
                $id_direccion,
                'UPDATE',
                $usuario_id,
                $infoAnterior,
                $infoPosterior
            );

            echo json_encode([
                'ok'      => true,
                'message' => 'Ubicación actualizada correctamente.'
            ]);
        } else {
            echo json_encode([
                'ok'      => false,
                'message' => 'Error al actualizar la ubicación.'
            ]);
        }
        exit;
    }

    // 5.b) Si no existe → insertamos una nueva fila y la marcamos como principal = 1
    $stmtIns = $pdo->prepare("
        INSERT INTO direcciones_usuarios 
               (usuario_id, alias, direccion, ciudad, departamento, codigo_postal, pais, principal) 
        VALUES(?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $okInsert = $stmtIns->execute([
        $usuario_id,
        $alias,
        $direccion,
        $ciudad,
        $departamento,
        $codigo_postal,
        $pais ?: 'El Salvador'
    ]);

    if ($okInsert) {
        $newId = intval($pdo->lastInsertId());

        // Construir infoPosterior
        $infoPosterior = [
            'id_direccion_usuario' => $newId,
            'usuario_id'           => $usuario_id,
            'alias'                => $alias,
            'direccion'            => $direccion,
            'ciudad'               => $ciudad,
            'departamento'         => $departamento,
            'codigo_postal'        => $codigo_postal,
            'pais'                 => $pais ?: 'El Salvador',
            'principal'            => 1
        ];

        // Registrar en bitácora
        registrarBitacora(
            'direcciones_usuarios',
            $newId,
            'INSERT',
            $usuario_id,
            null,
            $infoPosterior
        );

        echo json_encode([
            'ok'      => true,
            'message' => 'Ubicación guardada correctamente.'
        ]);
    } else {
        echo json_encode([
            'ok'      => false,
            'message' => 'Error al guardar la ubicación.'
        ]);
    }
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'ok'      => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
    exit;
}
