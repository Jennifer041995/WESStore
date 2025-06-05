<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

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
        SELECT id_direccion_usuario 
          FROM direcciones_usuarios 
         WHERE usuario_id = ? 
           AND principal = 1
         LIMIT 1
    ");
    $stmtCheck->execute([ $usuario_id ]);
    $fila = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        // 5.a) Si existe -> hacemos UPDATE de los campos (sin actualizar “actualizado_en”, porque no existe)
        $id_direccion = intval($fila['id_direccion_usuario']);
        $stmtUpd = $pdo->prepare("
            UPDATE direcciones_usuarios
               SET alias        = ?,
                   direccion    = ?,
                   ciudad       = ?,
                   departamento = ?,
                   codigo_postal= ?,
                   pais         = ?
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

    // 5.b) Si no existe -> insertamos una nueva fila y la marcamos como principal = 1
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
    // 6) Si ocurre cualquier excepción de BD, devolvemos el mensaje
    echo json_encode([
        'ok'      => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
    exit;
}