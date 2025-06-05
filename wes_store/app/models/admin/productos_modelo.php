<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php'; // Ajusta la ruta si es necesario

/**
 * Genera un slug amigable a partir de un texto:
 * - minúsculas
 * - quita tildes y caracteres especiales
 * - espacios → guiones
 */
function generarSlug(string $texto): string {
    $slug = mb_strtolower(trim($texto), 'UTF-8');
    $tabla = [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u',
        'ñ'=>'n','Ñ'=>'n','ü'=>'u','Ü'=>'u'
    ];
    $slug = strtr($slug, $tabla);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Verifica si un SKU ya existe en la tabla productos.
 * Si $excludeId está definido (en editar), excluye ese registro de la comprobacion.
 */
function skuExiste(PDO $pdo, string $sku, int $excludeId = null): bool {
    if ($excludeId !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE sku = ? AND id_producto <> ?");
        $stmt->execute([$sku, $excludeId]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE sku = ?");
        $stmt->execute([$sku]);
    }
    return (bool) $stmt->fetchColumn();
}

$pdo = getConnection();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            // Listar todos los productos con categoría, marca y stock actual
            $stmt = $pdo->query("
              SELECT 
                p.id_producto, p.sku, p.nombre, p.precio, p.costo, p.estado, p.destacado,
                c.nombre_categoria, m.nombre_marca,
                COALESCE((SELECT stock FROM inventario inv WHERE inv.producto_id = p.id_producto), 0) AS stock
              FROM productos p
              LEFT JOIN categorias c ON p.categoria_id = c.id_categoria
              LEFT JOIN marcas m ON p.marca_id = m.id_marca
              ORDER BY p.id_producto DESC
            ");
            echo json_encode($stmt->fetchAll());
            break;

        case 'obtener':
            // Obtener un solo producto por ID
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare("
              SELECT 
                p.*, 
                COALESCE((SELECT stock FROM inventario inv WHERE inv.producto_id = p.id_producto), 0) AS stock 
              FROM productos p 
              WHERE p.id_producto = ?
            ");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch());
            break;

        case 'agregar':
            // Leer datos desde POST
            $sku             = trim($_POST['sku']);
            $nombre          = trim($_POST['nombre']);
            $categoria_id    = intval($_POST['categoria_id']);
            $subcategoria_id = intval($_POST['subcategoria_id']);
            $marca_id        = intval($_POST['marca_id']);
            $precio          = floatval($_POST['precio']);
            $costo           = floatval($_POST['costo']);
            $stock           = intval($_POST['stock']);
            $descr_corta     = $_POST['descripcion_corta'];
            $descr_larga     = $_POST['descripcion_larga'];
            $estado          = $_POST['estado'];
            $destacado       = intval($_POST['destacado']);

            // 1) Verificar si el SKU ya existe
            if (skuExiste($pdo, $sku)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "El SKU '$sku' ya existe. Elija otro."
                ]);
                exit;
            }

            // 2) Generar slug a partir del nombre
            $slug = generarSlug($nombre);

            // 3) Insertar producto + inventario
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
              INSERT INTO productos 
                (sku, nombre, slug, categoria_id, subcategoria_id, marca_id, precio, costo, descripcion_corta, descripcion_larga, estado, destacado)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
              $sku,
              $nombre,
              $slug,
              $categoria_id,
              $subcategoria_id,
              $marca_id,
              $precio,
              $costo,
              $descr_corta,
              $descr_larga,
              $estado,
              $destacado
            ]);
            $nuevo_id = intval($pdo->lastInsertId());

            // Inventario inicial
            $stm2 = $pdo->prepare("INSERT INTO inventario(producto_id, stock) VALUES(?, ?)");
            $stm2->execute([$nuevo_id, $stock]);
            $pdo->commit();

            // Obtener datos posteriores al INSERT (para bitácora)
            $stmtPost = $pdo->prepare("
                SELECT 
                  p.id_producto, p.sku, p.nombre, p.slug, p.categoria_id, p.subcategoria_id,
                  p.marca_id, p.precio, p.costo, p.descripcion_corta, p.descripcion_larga,
                  p.estado, p.destacado,
                  COALESCE((SELECT stock FROM inventario inv WHERE inv.producto_id = p.id_producto), 0) AS stock
                FROM productos p
                WHERE p.id_producto = ?
            ");
            $stmtPost->execute([$nuevo_id]);
            $productoPosterior = $stmtPost->fetch(PDO::FETCH_ASSOC);

            // Registrar en bitácora
            $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'productos',           // tabla_afectada
                $nuevo_id,             // id_registro_afectado
                'INSERT',              // tipo_operacion
                $idUsuarioLogueado,    // id_usuario
                null,                  // info_anterior
                $productoPosterior     // info_posterior
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Producto agregado correctamente.'
            ]);
            break;

        case 'editar':
            // Leer datos desde POST
            $id              = intval($_POST['id_producto']);
            $sku             = trim($_POST['sku']);
            $nombre          = trim($_POST['nombre']);
            $categoria_id    = intval($_POST['categoria_id']);
            $subcategoria_id = intval($_POST['subcategoria_id']);
            $marca_id        = intval($_POST['marca_id']);
            $precio          = floatval($_POST['precio']);
            $costo           = floatval($_POST['costo']);
            $stock           = intval($_POST['stock']);
            $descr_corta     = $_POST['descripcion_corta'];
            $descr_larga     = $_POST['descripcion_larga'];
            $estado          = $_POST['estado'];
            $destacado       = intval($_POST['destacado']);

            // 1) Verificar si el SKU ya existe en otro producto distinto a este ID
            if (skuExiste($pdo, $sku, $id)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "El SKU '$sku' ya está asignado a otro producto."
                ]);
                exit;
            }

            // 2) Obtener datos previos (antes del UPDATE)
            $stmtPrev = $pdo->prepare("
                SELECT 
                  p.id_producto, p.sku, p.nombre, p.slug, p.categoria_id, p.subcategoria_id,
                  p.marca_id, p.precio, p.costo, p.descripcion_corta, p.descripcion_larga,
                  p.estado, p.destacado,
                  COALESCE((SELECT stock FROM inventario inv WHERE inv.producto_id = p.id_producto), 0) AS stock
                FROM productos p
                WHERE p.id_producto = ?
            ");
            $stmtPrev->execute([$id]);
            $productoAnterior = $stmtPrev->fetch(PDO::FETCH_ASSOC);

            if (!$productoAnterior) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Producto no encontrado.'
                ]);
                exit;
            }

            // 3) Generar slug a partir del nombre
            $slug = generarSlug($nombre);

            // 4) Actualizar producto + inventario
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
              UPDATE productos SET 
                sku              = ?,
                nombre           = ?, 
                slug             = ?,
                categoria_id     = ?, 
                subcategoria_id  = ?, 
                marca_id         = ?, 
                precio           = ?, 
                costo            = ?, 
                descripcion_corta = ?, 
                descripcion_larga = ?, 
                estado           = ?, 
                destacado        = ?
              WHERE id_producto = ?
            ");
            $stmt->execute([
              $sku,
              $nombre,
              $slug,
              $categoria_id,
              $subcategoria_id,
              $marca_id,
              $precio,
              $costo,
              $descr_corta,
              $descr_larga,
              $estado,
              $destacado,
              $id
            ]);

            // Actualizar stock en inventario
            $stm2 = $pdo->prepare("UPDATE inventario SET stock = ? WHERE producto_id = ?");
            $stm2->execute([$stock, $id]);
            $pdo->commit();

            // Obtener datos posteriores (después del UPDATE)
            $stmtPost = $pdo->prepare("
                SELECT 
                  p.id_producto, p.sku, p.nombre, p.slug, p.categoria_id, p.subcategoria_id,
                  p.marca_id, p.precio, p.costo, p.descripcion_corta, p.descripcion_larga,
                  p.estado, p.destacado,
                  COALESCE((SELECT stock FROM inventario inv WHERE inv.producto_id = p.id_producto), 0) AS stock
                FROM productos p
                WHERE p.id_producto = ?
            ");
            $stmtPost->execute([$id]);
            $productoPosterior = $stmtPost->fetch(PDO::FETCH_ASSOC);

            // Registrar en la bitácora
            $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'productos',
                $id,
                'UPDATE',
                $idUsuarioLogueado,
                $productoAnterior,
                $productoPosterior
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Producto actualizado correctamente.'
            ]);
            break;

        case 'eliminar':
            $id = intval($_POST['id_producto']);

            // 1) Obtener datos previos (antes del DELETE)
            $stmtPrev = $pdo->prepare("
                SELECT 
                  p.id_producto, p.sku, p.nombre, p.slug, p.categoria_id, p.subcategoria_id,
                  p.marca_id, p.precio, p.costo, p.descripcion_corta, p.descripcion_larga,
                  p.estado, p.destacado,
                  COALESCE((SELECT stock FROM inventario inv WHERE inv.producto_id = p.id_producto), 0) AS stock
                FROM productos p
                WHERE p.id_producto = ?
            ");
            $stmtPrev->execute([$id]);
            $productoAnterior = $stmtPrev->fetch(PDO::FETCH_ASSOC);

            if (!$productoAnterior) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Producto no encontrado.'
                ]);
                exit;
            }

            // 2) Eliminar el producto (y pelo cascada eliminara inventario)
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->execute([$id]);

            // 3) Registrar en la bitácora
            $idUsuarioLogueado = $_SESSION['usuario']['id'] ?? null;
            registrarBitacora(
                'productos',
                $id,
                'DELETE',
                $idUsuarioLogueado,
                $productoAnterior,
                null
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Producto eliminado.'
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
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
