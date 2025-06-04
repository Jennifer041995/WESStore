<?php
// C:\xampp1\htdocs\WESStore2\app\models\cart\checkout.php

// Paso 1: Inicio de Sesión y Configuración Inicial
session_start(); // Inicia o reanuda la sesión PHP para acceder a $_SESSION.
header('Content-Type: application/json'); // Establece el tipo de contenido de la respuesta como JSON.
require_once __DIR__ . '/../sql/conexion.php'; // Incluye el archivo de conexión a la base de datos.
require_once __DIR__ . '/../PDF/PdfGenerator.php'; // Incluye la clase para generar PDFs.

// Paso 2: Manejo de Conexión a la Base de Datos y Transacción
try {
    $conn = getConnection(); // Intenta obtener una conexión a la base de datos.
    $conn->beginTransaction(); // Inicia una transacción para asegurar la atomicidad de las operaciones.
} catch (PDOException $e) {
    // Si hay un error de conexión, envía una respuesta JSON de error y termina el script.
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

// Paso 3: Validación de Usuario Autenticado
if (!isset($_SESSION['usuario']['id'])) {
    // Si el usuario no está autenticado, revierte la transacción y envía un error.
    echo json_encode(['status' => 'error', 'message' => 'Error: usuario no autenticado.']);
    $conn->rollBack(); // Deshace cualquier cambio pendiente en la base de datos.
    exit;
}

$usuario_id = $_SESSION['usuario']['id']; // Obtiene el ID del usuario de la sesión.

// Paso 4: Validación del Carrito de Sesión
if (empty($_SESSION['carrito'])) {
    // Si el carrito de sesión está vacío, revierte la transacción y envía un error.
    echo json_encode(['status' => 'error', 'message' => 'El carrito de sesión está vacío.']);
    $conn->rollBack(); // Deshace cualquier cambio pendiente en la base de datos.
    exit;
}

// Paso 5: Procesamiento de Productos del Carrito de Sesión
$productos_en_sesion = $_SESSION['carrito']; // Obtiene los productos del carrito de la sesión.
$productos_para_pedido = []; // Array para almacenar los detalles de los productos del pedido.
$total_pedido = 0; // Variable para calcular el total del pedido.

foreach ($productos_en_sesion as $producto_id => $cantidad_sesion) {
    // Valida que la cantidad del producto sea numérica y positiva.
    if (!is_numeric($cantidad_sesion) || $cantidad_sesion <= 0) {
        $conn->rollBack(); // Deshace cambios si la cantidad es inválida.
        echo json_encode(['status' => 'error', 'message' => 'Cantidad inválida para producto ' . $producto_id]);
        exit;
    }

    // Consulta el precio, nombre y stock del producto desde la base de datos.
    // ***** CORRECCIÓN AQUÍ: Obtener stock de la tabla 'inventario' *****
    $sqlProductoData = "SELECT p.precio, p.nombre, i.stock 
                        FROM productos p
                        JOIN inventario i ON p.id_producto = i.producto_id
                        WHERE p.id_producto = ?";
    $stmtProductoData = $conn->prepare($sqlProductoData);
    $stmtProductoData->execute([$producto_id]);
    $resultProductoData = $stmtProductoData->fetch(PDO::FETCH_ASSOC);

    // Valida que el producto exista, tenga precio y que el stock esté disponible en inventario.
    if (!$resultProductoData) {
        $conn->rollBack(); // Deshace cambios si el producto no se encuentra.
        echo json_encode(['status' => 'error', 'message' => 'Producto con ID ' . $producto_id . ' no encontrado o sin datos de inventario.']);
        exit;
    }

    $precioProducto = $resultProductoData['precio']; // Obtiene el precio del producto.
    $nombreProducto = $resultProductoData['nombre']; // Obtiene el nombre del producto.
    $stockDisponible = $resultProductoData['stock']; // Obtiene el stock disponible desde la tabla 'inventario'.

    // Validar si hay suficiente stock
    if ($cantidad_sesion > $stockDisponible) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'No hay suficiente stock para el producto: ' . $nombreProducto . '. Stock disponible: ' . $stockDisponible . ', Cantidad solicitada: ' . $cantidad_sesion]);
        exit;
    }

    $subtotal = $precioProducto * $cantidad_sesion; // Calcula el subtotal del producto.

    $total_pedido += $subtotal; // Suma el subtotal al total general del pedido.
    $productos_para_pedido[] = [
        // Almacena los detalles del producto para su uso posterior en el pedido y factura.
        'producto_id' => $producto_id,
        'cantidad' => $cantidad_sesion,
        'precio_unitario' => $precioProducto,
        'nombre' => $nombreProducto, // Añadir nombre para el PDF
        'stock' => $stockDisponible // Añadir stock para referencia (no necesario en PDF final, pero útil para depuración)
    ];
}

// Validación final: Asegura que haya productos válidos para el pedido.
if (empty($productos_para_pedido)) {
    echo json_encode(['status' => 'error', 'message' => 'No hay productos válidos en el carrito para procesar el pedido.']);
    $conn->rollBack(); // Deshace cambios si no hay productos.
    exit;
}

// Paso 6: Recepción y Validación de Datos del Cliente
$input = json_decode(file_get_contents('php://input'), true); // Decodifica los datos JSON enviados por el cliente.
$cliente_data = $input['cliente'] ?? []; // Extrae los datos del cliente, si existen.

// Valida que todos los campos esenciales del cliente estén presentes.
if (empty($cliente_data['nombres']) || empty($cliente_data['apellidos']) || empty($cliente_data['email']) || empty($cliente_data['telefono']) || empty($cliente_data['departamento']) || empty($cliente_data['municipio']) || empty($cliente_data['direccionCompleta']) || empty($cliente_data['dui'])) {
    $conn->rollBack(); // Deshace cambios si faltan datos.
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos esenciales del cliente o dirección. Asegúrate de llenar todos los campos de información personal y dirección.']);
    exit;
}
// Valida el formato del correo electrónico.
if (!filter_var($cliente_data['email'], FILTER_VALIDATE_EMAIL)) {
    $conn->rollBack(); // Deshace cambios si el email es inválido.
    echo json_encode(['status' => 'error', 'message' => 'El formato del correo electrónico no es válido.']);
    exit;
}
// Valida el formato del DUI (si es que se requiere validar en backend también)
if (!preg_match('/^\d{7}-\d$/', $cliente_data['dui'])) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'El formato del DUI no es válido. Debe ser 7 dígitos - 1 dígito.']);
    exit;
}
// Valida el formato del teléfono (si es que se requiere validar en backend también)
if (!preg_match('/^\d{8}$/', $cliente_data['telefono'])) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'El formato del teléfono no es válido. Debe ser 8 dígitos.']);
    exit;
}


// Paso 7: Inserción de Dirección de Envío y Creación de Carrito de Historial
try {
    $alias_envio = 'Pedido-' . date('YmdHis'); // Genera un alias único para la dirección.
    $ciudad_envio = $cliente_data['municipio']; // Obtiene la ciudad de envío.
    $codigo_postal_envio = '00000'; // Define un código postal predeterminado (o se obtendría del cliente).

    // Inserta la dirección de envío del pedido en la tabla 'direcciones_usuarios'.
    $sqlInsertDireccion = "INSERT INTO direcciones_usuarios (usuario_id, alias, direccion, ciudad, departamento, codigo_postal, pais, principal) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
    $stmtInsertDireccion = $conn->prepare($sqlInsertDireccion);

    if (!$stmtInsertDireccion->execute([$usuario_id, $alias_envio, $cliente_data['direccionCompleta'], $ciudad_envio, $cliente_data['departamento'], $codigo_postal_envio, 'El Salvador'])) {
        throw new Exception('Error al guardar la dirección de envío del pedido: ' . implode(" ", $stmtInsertDireccion->errorInfo()));
    }
    $direccion_id_para_pedido = $conn->lastInsertId(); // Obtiene el ID de la dirección recién insertada.

    // *** MODIFICACIÓN CLAVE: Siempre insertar un nuevo registro en 'carritos' para este checkout. ***
    // Esto crea un historial de cada carrito tal como fue al momento de la compra.
    $sqlCreateNewCarrito = "INSERT INTO carritos (usuario_id) VALUES (?)";
    $stmtCreateNewCarrito = $conn->prepare($sqlCreateNewCarrito);
    if (!$stmtCreateNewCarrito->execute([$usuario_id])) {
        throw new Exception('Error al crear un nuevo registro de carrito para el historial de checkout.');
    }
    $carrito_id_db = $conn->lastInsertId(); // Obtiene el ID del nuevo carrito de historial.
    // 'creado_en' y 'actualizado_en' en la tabla 'carritos' se establecerán automáticamente al momento de la inserción.

    // *** MODIFICACIÓN CLAVE: Insertar los productos en 'detalles_carrito' para el nuevo carrito de historial. ***
    // NO se borran detalles de carritos anteriores, ya que cada registro de 'carritos' es un nuevo historial.
    $sqlInsertDetalleCarrito = "INSERT INTO detalles_carrito (carrito_id, producto_id, cantidad) VALUES (?, ?, ?)";
    $stmtInsertDetalleCarrito = $conn->prepare($sqlInsertDetalleCarrito);

    foreach ($productos_para_pedido as $producto) {
        if (!$stmtInsertDetalleCarrito->execute([$carrito_id_db, $producto['producto_id'], $producto['cantidad']])) {
            throw new Exception("Error al guardar un producto en el carrito de la base de datos (historial): " . implode(" ", $stmtInsertDetalleCarrito->errorInfo()));
        }
    }

    // Paso 8: Creación del Pedido Principal
    $metodo_pago = 'Tarjeta de crédito'; // Define el método de pago (podría ser dinámico).
    $sqlPedido = "INSERT INTO pedidos (usuario_id, direccion_envio_id, total, metodo_pago, estado) VALUES (?, ?, ?, ?, 'Pendiente')";
    $stmtPedido = $conn->prepare($sqlPedido);

    if (!$stmtPedido->execute([$usuario_id, $direccion_id_para_pedido, $total_pedido, $metodo_pago])) {
        throw new Exception('Error al crear el pedido: ' . implode(" ", $stmtPedido->errorInfo()));
    }
    $pedido_id = $conn->lastInsertId(); // Obtiene el ID del pedido recién creado.

    // Paso 9: Inserción de Detalles del Pedido
    $sqlDetallePedido = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmtDetallePedido = $conn->prepare($sqlDetallePedido);

    foreach ($productos_para_pedido as $producto) {
        // Inserta cada producto del carrito como un detalle del pedido.
        if (!$stmtDetallePedido->execute([
            $pedido_id,
            $producto['producto_id'],
            $producto['cantidad'],
            $producto['precio_unitario']
        ])) {
            throw new Exception("Error al insertar detalle de pedido para producto " . $producto['producto_id'] . ": " . implode(" ", $stmtDetallePedido->errorInfo()));
        }
    }

    // Paso 10: Actualización de Inventario y Registro de Movimientos
    // ***** ESTA SECCIÓN YA ESTÁ CORRECTA (usa 'inventario' y 'producto_id') *****
    $sqlInventarioUpdate = "UPDATE inventario SET stock = stock - ? WHERE producto_id = ?";
    $stmtInventarioUpdate = $conn->prepare($sqlInventarioUpdate);

    $sqlMovimientoInsert = "INSERT INTO movimientos_inventario (producto_id, tipo_movimiento, cantidad, referencia, notas, usuario_id) VALUES (?, 'salida', ?, ?, ?, ?)";
    $stmtMovimientoInsert = $conn->prepare($sqlMovimientoInsert);

    foreach ($productos_para_pedido as $producto) {
        // Actualiza el stock de cada producto vendido.
        if (!$stmtInventarioUpdate->execute([$producto['cantidad'], $producto['producto_id']])) {
            throw new Exception("Error al actualizar el stock para el producto " . $producto['nombre']);
        }

        // Registra un movimiento de salida en el inventario para cada producto vendido.
        if (!$stmtMovimientoInsert->execute([
            $producto['producto_id'],
            $producto['cantidad'],
            'Pedido #' . $pedido_id,
            'Venta de producto',
            $usuario_id
        ])) {
            throw new Exception("Error al registrar movimiento de inventario para el producto " . $producto['nombre']);
        }
    }

    // Paso 11: Vaciado del Carrito de Sesión
    // El carrito de sesión se vacía una vez que el pedido se ha procesado correctamente en la DB.
    unset($_SESSION['carrito']);

    // Paso 12: Confirmación de la Transacción
    $conn->commit(); // Confirma todos los cambios en la base de datos, haciéndolos permanentes.

    // Paso 13: Generación y Manejo de la Factura PDF
    $root_path = __DIR__ . '/../../../'; // Define la ruta raíz del proyecto.
    $pdf_save_directory = $root_path . 'media/invoices/'; // Define el directorio donde se guardarán las facturas.
    $pdf_file_name = "factura-pedido-{$pedido_id}.pdf"; // Define el nombre del archivo PDF.
    $pdf_full_path = $pdf_save_directory . $pdf_file_name; // Ruta completa del archivo PDF.

    // Asegura que el directorio para guardar las facturas exista. Si no, intenta crearlo.
    if (!is_dir($pdf_save_directory)) {
        if (!mkdir($pdf_save_directory, 0775, true)) { // Usa 0775 para dar permisos de escritura al grupo
            throw new Exception("No se pudo crear el directorio para las facturas: " . $pdf_save_directory . " Verifique los permisos.");
        }
    }

    $pdfGenerator = new PdfGenerator(); // Instancia la clase PdfGenerator.

    // Mensaje de depuración para ver los productos antes de generar el PDF (útil para el desarrollador).
    error_log("Contenido de \$productos_para_pedido antes de generar PDF: " . print_r($productos_para_pedido, true));

    // Intenta generar y guardar la factura PDF.
    if ($pdfGenerator->generateInvoicePdf($pedido_id, $cliente_data, $productos_para_pedido, $pdf_full_path)) {
        // Si el PDF se genera correctamente, envía una respuesta JSON de éxito con la URL de la factura.
        echo json_encode([
            'status' => 'success',
            'message' => '¡Pedido realizado y factura generada!',
            'pedido_id' => $pedido_id,
            'invoice_url' => '/WESStore2/app/models/download_invoice.php?pedido_id=' . $pedido_id
        ]);
    } else {
        // Si el PDF no se puede generar, envía una advertencia (el pedido ya se realizó).
        echo json_encode([
            'status' => 'warning',
            'message' => 'Pedido realizado, pero no se pudo generar la factura PDF. (Error de escritura: revise permisos o ruta del directorio de invoices).',
            'pedido_id' => $pedido_id
        ]);
    }

} catch (Exception $e) {
    // Paso 14: Manejo de Errores y Reversión
    $conn->rollBack(); // Si ocurre cualquier excepción, revierte todas las operaciones de la transacción.
    error_log("Error en el checkout: " . $e->getMessage()); // Registra el error en el log del servidor.
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); // Envía una respuesta JSON de error.
} finally {
    // Paso 15: Cierre de Conexión a la Base de Datos
    $conn = null; // Cierra la conexión a la base de datos para liberar recursos.
}