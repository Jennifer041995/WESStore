<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/connection.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query('SELECT id, nombre_categoria FROM categorias');
    $cats = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $cats]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
}