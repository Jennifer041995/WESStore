<?php
require_once __DIR__ . '/conexion.php';

/**
 * Registra una acción en la tabla bitacora_acciones.
 *
 * @param string      $tabla           Nombre de la tabla afectada (e.g., 'productos', 'usuarios', 'reportes').
 * @param int|null    $idRegistro      ID del registro afectado (puede ser NULL si no aplica).
 * @param string      $operacion       'INSERT', 'UPDATE', 'DELETE', 'READ' o 'EXPORT'.
 * @param int|null    $idUsuario       ID del usuario que realiza la acción (NULL si no hay usuario logueado).
 * @param array|null  $infoAnterior    Arreglo asociativo con los datos antes de la operación (o NULL).
 * @param array|null  $infoPosterior   Arreglo asociativo con los datos después de la operación (o NULL).
 *
 * @throws InvalidArgumentException    Si $operacion no está dentro de los permitidos.
 * @throws PDOException                Si falla la inserción en la base de datos.
 */
function registrarBitacora(
    string $tabla,
    ?int $idRegistro,
    string $operacion,
    ?int $idUsuario,
    ?array $infoAnterior,
    ?array $infoPosterior
) {
    // 1) Validar tipo de operación (ahora incluye READ y EXPORT)
    $operacion = strtoupper($operacion);
    $valoresValidos = ['INSERT', 'UPDATE', 'DELETE', 'READ', 'EXPORT'];
    if (!in_array($operacion, $valoresValidos, true)) {
        throw new InvalidArgumentException("Tipo de operación inválido: $operacion");
    }

    // 2) Convertir los arrays a JSON (o dejar NULL)
    $jsonAnterior  = $infoAnterior  !== null ? json_encode($infoAnterior, JSON_UNESCAPED_UNICODE) : null;
    $jsonPosterior = $infoPosterior !== null ? json_encode($infoPosterior, JSON_UNESCAPED_UNICODE) : null;

    // 3) Insertar en bitacora_acciones
    $pdo = getConnection();
    $sql = "
        INSERT INTO bitacora_acciones
            (id_registro_afectado, tabla_afectada, tipo_operacion, id_usuario, info_anterior, info_posterior)
        VALUES
            (:id_registro, :tabla, :operacion, :id_usuario, :info_anterior, :info_posterior)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_registro',   $idRegistro,    PDO::PARAM_INT);
    $stmt->bindValue(':tabla',         $tabla,         PDO::PARAM_STR);
    $stmt->bindValue(':operacion',     $operacion,     PDO::PARAM_STR);
    $stmt->bindValue(':id_usuario',    $idUsuario,     PDO::PARAM_INT);
    $stmt->bindValue(':info_anterior', $jsonAnterior,  PDO::PARAM_STR);
    $stmt->bindValue(':info_posterior',$jsonPosterior, PDO::PARAM_STR);
    $stmt->execute();
}