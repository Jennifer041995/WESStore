<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT id_rol AS id, nombre_rol FROM roles ORDER BY nombre_rol ASC");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($roles);
} catch (Exception $e) {
    echo json_encode([]);
}
