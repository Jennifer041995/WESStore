<?php
require_once __DIR__ . '/../sql/conexion.php';
try {
    $conn = getConnection();
    $categoria   = $_GET['categoria']   ?? '';
    $subcategoria= $_GET['subcategoria']?? '';
    $marca       = $_GET['marca']       ?? '';
    $buscar      = $_GET['buscar']      ?? '';

    $sql = "
      SELECT 
        p.id_producto,
        p.sku,
        p.nombre,
        p.slug,
        p.descripcion_corta,
        p.precio,
        p.precio_anterior,
        p.destacado,
        /* Imagen principal */
        (SELECT imagen_url 
           FROM imagenes_productos ip 
           WHERE ip.producto_id = p.id_producto AND principal = 1 
           LIMIT 1
        ) AS imagen_principal,
        /* Stock actual */
        COALESCE((SELECT stock 
                   FROM inventario inv 
                   WHERE inv.producto_id = p.id_producto
                  ), 0) AS stock,
        /* Valoración promedio */
        COALESCE((SELECT ROUND(AVG(calificacion),1) 
                   FROM comentarios_productos cp 
                   WHERE cp.producto_id = p.id_producto AND cp.aprobado = 1
                  ), 0) AS valoracion_promedio
      FROM productos p
      WHERE p.estado = 'Activo'
    ";

    $params = [];
    if ($categoria)    { $sql .= " AND p.categoria_id = :categoria";    $params[':categoria']    = $categoria; }
    if ($subcategoria) { $sql .= " AND p.subcategoria_id = :subcategoria"; $params[':subcategoria'] = $subcategoria; }
    if ($marca)        { $sql .= " AND p.marca_id = :marca";            $params[':marca']        = $marca; }
    if ($buscar)       { $sql .= " AND p.nombre LIKE :buscar";          $params[':buscar']       = "%$buscar%"; }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($productos);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Conexión fallida: ' . $e->getMessage()]);
}
?>