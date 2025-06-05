<?php
// C:\xampp1\htdocs\WESStore\app\models\cart\checkout.php

// Paso 1: Inicio de Sesión y Configuración Inicial
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../sql/conexion.php';
require_once __DIR__ . '/../sql/bitacora.php';           // Para registrar bitácora
require_once __DIR__ . '/../PDF/PdfGenerator.php';

// Paso 2: Manejo de Conexión a la Base de Datos y Transacción
try {
    $conn = getConnection();
    $conn->beginTransaction();
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
    ]);
    exit;
}

// Paso 3: Validación de Usuario Autenticado
if (!isset($_SESSION['usuario']['id'])) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: usuario no autenticado.']);
    exit;
}
$usuario_id = $_SESSION['usuario']['id'];

// Paso 4: Validación del Carrito de Sesión
if (empty($_SESSION['carrito'])) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'El carrito de sesión está vacío.']);
    exit;
}

// Paso 5: Procesamiento de Productos del Carrito de Sesión
$productos_en_sesion   = $_SESSION['carrito'];
$productos_para_pedido  = [];
$total_pedido           = 0;

foreach ($productos_en_sesion as $producto_id => $cantidad_sesion) {
    if (!is_numeric($cantidad_sesion) || $cantidad_sesion <= 0) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Cantidad inválida para producto ' . $producto_id
        ]);
        exit;
    }

    $sqlProductoData = "
        SELECT p.precio, p.nombre, i.stock
        FROM productos p
        JOIN inventario i ON p.id_producto = i.producto_id
        WHERE p.id_producto = ?
    ";
    $stmtProductoData = $conn->prepare($sqlProductoData);
    $stmtProductoData->execute([$producto_id]);
    $resultProductoData = $stmtProductoData->fetch(PDO::FETCH_ASSOC);

    if (!$resultProductoData) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Producto con ID ' . $producto_id . ' no encontrado o sin datos de inventario.'
        ]);
        exit;
    }

    $precioProducto   = $resultProductoData['precio'];
    $nombreProducto   = $resultProductoData['nombre'];
    $stockDisponible  = $resultProductoData['stock'];

    if ($cantidad_sesion > $stockDisponible) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'No hay suficiente stock para el producto: ' . $nombreProducto .
                         '. Stock disponible: ' . $stockDisponible .
                         ', Cantidad solicitada: ' . $cantidad_sesion
        ]);
        exit;
    }

    $subtotal = $precioProducto * $cantidad_sesion;
    $total_pedido += $subtotal;

    $productos_para_pedido[] = [
        'producto_id'     => $producto_id,
        'cantidad'        => $cantidad_sesion,
        'precio_unitario' => $precioProducto,
        'nombre'          => $nombreProducto,
        'stock'           => $stockDisponible
    ];
}

if (empty($productos_para_pedido)) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'No hay productos válidos en el carrito para procesar el pedido.'
    ]);
    exit;
}

// Paso 6: Recepción y Validación de Datos del Cliente
$input        = json_decode(file_get_contents('php://input'), true);
$cliente_data = $input['cliente'] ?? [];

if (
    empty($cliente_data['nombres']) ||
    empty($cliente_data['apellidos']) ||
    empty($cliente_data['email']) ||
    empty($cliente_data['telefono']) ||
    empty($cliente_data['departamento']) ||
    empty($cliente_data['municipio']) ||
    empty($cliente_data['direccionCompleta']) ||
    empty($cliente_data['dui'])
) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'Faltan datos esenciales del cliente o dirección. Asegúrate de llenar todos los campos.'
    ]);
    exit;
}

if (!filter_var($cliente_data['email'], FILTER_VALIDATE_EMAIL)) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'El formato del correo electrónico no es válido.'
    ]);
    exit;
}

if (!preg_match('/^\d{8}-\d$/', $cliente_data['dui'])) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'El formato del DUI no es válido. Debe ser 8 dígitos - 1 dígito.'
    ]);
    exit;
}

if (!preg_match('/^\d{8}$/', $cliente_data['telefono'])) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'El formato del teléfono no es válido. Debe ser 8 dígitos.'
    ]);
    exit;
}

try {
    // Paso 7: Inserción de Dirección de Envío
    $alias_envio = 'Pedido-' . date('YmdHis');
    $ciudad_envio = $cliente_data['municipio'];
    $codigo_postal_envio = '00000';

    $sqlInsertDireccion = "
        INSERT INTO direcciones_usuarios
          (usuario_id, alias, direccion, ciudad, departamento, codigo_postal, pais, principal)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
    ";
    $stmtInsertDireccion = $conn->prepare($sqlInsertDireccion);
    if (!$stmtInsertDireccion->execute([
        $usuario_id,
        $alias_envio,
        $cliente_data['direccionCompleta'],
        $ciudad_envio,
        $cliente_data['departamento'],
        $codigo_postal_envio,
        'El Salvador'
    ])) {
        throw new Exception(
            'Error al guardar la dirección de envío: '
            . implode(" ", $stmtInsertDireccion->errorInfo())
        );
    }
    $direccion_id_para_pedido = intval($conn->lastInsertId());

    // Registrar en bitácora: dirección inserta
    registrarBitacora(
        'direcciones_usuarios',
        $direccion_id_para_pedido,
        'INSERT',
        $usuario_id,
        null,
        [
            'alias'        => $alias_envio,
            'direccion'    => $cliente_data['direccionCompleta'],
            'ciudad'       => $ciudad_envio,
            'departamento' => $cliente_data['departamento'],
            'codigo_postal'=> $codigo_postal_envio,
            'pais'         => 'El Salvador'
        ]
    );

    // Paso 8: Creación de Carrito de Historial
    $sqlCreateNewCarrito = "INSERT INTO carritos (usuario_id) VALUES (?)";
    $stmtCreateNewCarrito = $conn->prepare($sqlCreateNewCarrito);
    if (!$stmtCreateNewCarrito->execute([$usuario_id])) {
        throw new Exception(
            'Error al crear un nuevo registro de carrito (historial): '
            . implode(" ", $stmtCreateNewCarrito->errorInfo())
        );
    }
    $carrito_id_db = intval($conn->lastInsertId());

    // Registrar en bitácora: nuevo carrito creado
    registrarBitacora(
        'carritos',
        $carrito_id_db,
        'INSERT',
        $usuario_id,
        null,
        ['usuario_id' => $usuario_id]
    );

    // Paso 9: Detalles del Carrito de Historial
    $sqlInsertDetalleCarrito = "
        INSERT INTO detalles_carrito (carrito_id, producto_id, cantidad)
        VALUES (?, ?, ?)
    ";
    $stmtInsertDetalleCarrito = $conn->prepare($sqlInsertDetalleCarrito);

    foreach ($productos_para_pedido as $producto) {
        if (
            !$stmtInsertDetalleCarrito->execute([
                $carrito_id_db,
                $producto['producto_id'],
                $producto['cantidad']
            ])
        ) {
            throw new Exception(
                "Error al guardar producto ({$producto['producto_id']}) en carrito historial: "
                . implode(" ", $stmtInsertDetalleCarrito->errorInfo())
            );
        }

        // Registrar en bitácora: detalle_carrito (uno por producto)
        registrarBitacora(
            'detalles_carrito',
            null,
            'INSERT',
            $usuario_id,
            null,
            [
                'carrito_id'  => $carrito_id_db,
                'producto_id' => $producto['producto_id'],
                'cantidad'    => $producto['cantidad']
            ]
        );
    }

    // Paso 10: Crear Pedido Principal
    $metodo_pago = 'Tarjeta de crédito';
    $sqlPedido = "
        INSERT INTO pedidos
          (usuario_id, direccion_envio_id, total, metodo_pago, estado)
        VALUES (?, ?, ?, ?, 'Pendiente')
    ";
    $stmtPedido = $conn->prepare($sqlPedido);
    if (
        !$stmtPedido->execute([
            $usuario_id,
            $direccion_id_para_pedido,
            $total_pedido,
            $metodo_pago
        ])
    ) {
        throw new Exception(
            'Error al crear el pedido: ' . implode(" ", $stmtPedido->errorInfo())
        );
    }
    $pedido_id = intval($conn->lastInsertId());

    // Registrar en bitácora: pedido creado
    registrarBitacora(
        'pedidos',
        $pedido_id,
        'INSERT',
        $usuario_id,
        null,
        [
            'usuario_id'           => $usuario_id,
            'direccion_envio_id'   => $direccion_id_para_pedido,
            'total'                => $total_pedido,
            'metodo_pago'          => $metodo_pago,
            'estado'               => 'Pendiente'
        ]
    );

    // Paso 11: Inserción de Detalles del Pedido
    $sqlDetallePedido = "
        INSERT INTO detalles_pedido
          (pedido_id, producto_id, cantidad, precio_unitario)
        VALUES (?, ?, ?, ?)
    ";
    $stmtDetallePedido = $conn->prepare($sqlDetallePedido);

    foreach ($productos_para_pedido as $producto) {
        if (
            !$stmtDetallePedido->execute([
                $pedido_id,
                $producto['producto_id'],
                $producto['cantidad'],
                $producto['precio_unitario']
            ])
        ) {
            throw new Exception(
                "Error al insertar detalle de pedido para producto {$producto['producto_id']}: "
                . implode(" ", $stmtDetallePedido->errorInfo())
            );
        }

        // Registrar en bitácora: detalle_pedido (uno por producto)
        registrarBitacora(
            'detalles_pedido',
            null,
            'INSERT',
            $usuario_id,
            null,
            [
                'pedido_id'       => $pedido_id,
                'producto_id'     => $producto['producto_id'],
                'cantidad'        => $producto['cantidad'],
                'precio_unitario' => $producto['precio_unitario']
            ]
        );
    }

    // Paso 12: Actualización de Inventario y Movimientos
    $sqlInventarioUpdate = "
        UPDATE inventario
        SET stock = stock - ?
        WHERE producto_id = ?
    ";
    $stmtInventarioUpdate = $conn->prepare($sqlInventarioUpdate);

    $sqlMovimientoInsert = "
        INSERT INTO movimientos_inventario
          (producto_id, tipo_movimiento, cantidad, referencia, notas, usuario_id)
        VALUES (?, 'salida', ?, ?, ?, ?)
    ";
    $stmtMovimientoInsert = $conn->prepare($sqlMovimientoInsert);

    foreach ($productos_para_pedido as $producto) {
        // 12a) Actualizar stock
        if (
            !$stmtInventarioUpdate->execute([
                $producto['cantidad'],
                $producto['producto_id']
            ])
        ) {
            throw new Exception(
                "Error al actualizar el stock para el producto {$producto['nombre']}."
            );
        }

        // Registrar en bitácora: inventario actualizado
        registrarBitacora(
            'inventario',
            null,
            'UPDATE',
            $usuario_id,
            null,
            [
                'producto_id' => $producto['producto_id'],
                'cantidad'    => $producto['cantidad'],
                'operacion'   => 'salida'
            ]
        );

        // 12b) Registrar movimiento de inventario
        if (
            !$stmtMovimientoInsert->execute([
                $producto['producto_id'],
                $producto['cantidad'],
                'Pedido #' . $pedido_id,
                'Venta de producto',
                $usuario_id
            ])
        ) {
            throw new Exception(
                "Error al registrar movimiento en inventario para producto {$producto['nombre']}."
            );
        }

        // Registrar en bitácora: movimiento_inventario insertado
        registrarBitacora(
            'movimientos_inventario',
            null,
            'INSERT',
            $usuario_id,
            null,
            [
                'producto_id'     => $producto['producto_id'],
                'tipo_movimiento' => 'salida',
                'cantidad'        => $producto['cantidad'],
                'referencia'      => 'Pedido #' . $pedido_id,
                'notas'           => 'Venta de producto'
            ]
        );
    }

    // Paso 13: Vaciado del Carrito de Sesión
    unset($_SESSION['carrito']);

    // Paso 14: Confirmación de la Transacción
    $conn->commit();

    // Paso 15: Generación de Factura PDF
    $root_path = __DIR__ . '/../../../';
    $pdf_save_directory = $root_path . 'media/temp/invoices/';
    $pdf_file_name = "factura-pedido-{$pedido_id}.pdf";
    $pdf_full_path = $pdf_save_directory . $pdf_file_name;

    if (!is_dir($pdf_save_directory)) {
        if (!mkdir($pdf_save_directory, 0775, true)) {
            throw new Exception("No se pudo crear el directorio para facturas: " . $pdf_save_directory);
        }
    }

    $pdfGenerator = new PdfGenerator();
    if ($pdfGenerator->generateInvoicePdf($pedido_id, $cliente_data, $productos_para_pedido, $pdf_full_path)) {
        echo json_encode([
            'status'      => 'success',
            'message'     => '¡Pedido realizado y factura generada!',
            'pedido_id'   => $pedido_id,
            'invoice_url' => '/WESStore/app/models/download_invoice.php?pedido_id=' . $pedido_id
        ]);
    } else {
        echo json_encode([
            'status'    => 'warning',
            'message'   => 'Pedido realizado, pero no se pudo generar la factura PDF.',
            'pedido_id' => $pedido_id
        ]);
    }

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error en el checkout: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn = null;
}
