<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/connection.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query('SELECT id AS marca_id, nombre_marca FROM marcas ORDER BY nombre_marca');
    $brands = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $brands]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}