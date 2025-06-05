<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['status'=>'error','message'=>'ID no proporcionado']);
    exit;
}
$id = intval($_GET['id']);

try {
    $pdo = getConnection();
    // 1) Producto con categoría y marca
    $stmt = $pdo->prepare("
      SELECT p.*, c.nombre_categoria, m.nombre_marca
      FROM productos p
      LEFT JOIN categorias c ON p.categoria_id = c.id_categoria
      LEFT JOIN marcas m ON p.marca_id = m.id_marca 
      WHERE p.id_producto = ?
    ");
    $stmt->execute([$id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prod) {
        echo json_encode(['status'=>'error','message'=>'Producto no encontrado']);
        exit;
    }

    // 2) Imágenes
    $stmt = $pdo->prepare("
      SELECT imagen_url, principal 
      FROM imagenes_productos 
      WHERE producto_id = ? 
      ORDER BY principal DESC, orden ASC
    ");
    $stmt->execute([$id]);
    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Atributos
    $stmt = $pdo->prepare("
      SELECT nombre, valor 
      FROM atributos_productos 
      WHERE producto_id = ?
    ");
    $stmt->execute([$id]);
    $atributos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4) Stock
    $stmt = $pdo->prepare("SELECT stock FROM inventario WHERE producto_id = ?");
    $stmt->execute([$id]);
    $stock = ($r = $stmt->fetch()) ? $r['stock'] : 0;

    // 5) Oferta vigente
    $stmt = $pdo->prepare("
      SELECT o.tipo_descuento, o.valor
      FROM ofertas o
      INNER JOIN productos_ofertas po ON po.oferta_id = o.id_oferta
      WHERE po.producto_id = ? 
        AND CURDATE() BETWEEN o.fecha_inicio AND o.fecha_fin
        AND o.activo = 1
    ");
    $stmt->execute([$id]);
    $oferta = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // 6) Comentarios aprobados
    $stmt = $pdo->prepare("
      SELECT cp.comentario, cp.calificacion, u.nombre AS nombre_usuario, cp.creado_en
      FROM comentarios_productos cp
      JOIN usuarios u ON u.id_usuario = cp.usuario_id
      WHERE cp.producto_id = ? AND cp.aprobado = 1
      ORDER BY cp.creado_en DESC
    ");
    $stmt->execute([$id]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
      'status'=>'success',
      'data'=>[
        'producto'    => $prod,
        'imagenes'    => $imagenes,
        'atributos'   => $atributos,
        'stock'       => $stock,
        'oferta'      => $oferta,
        'comentarios' => $comentarios
      ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status'=>'error','message'=>'Error en servidor']);
}
?>