<?php
require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

$pdo = getConnection();
$accion = $_POST['accion'] ?? '';

switch ($accion) {
  case 'resumen':
    cargarResumen($pdo);
    break;
  case 'grafico_ordenes':
    cargarGraficoOrdenes($pdo);
    break;
  case 'grafico_pendientes':
    cargarGraficoPendientes($pdo);
    break;
  case 'producto_top':
    cargarProductoTop($pdo);
    break;
  case 'cliente_top':
    cargarClienteTop($pdo);
    break;
  case 'ultimas_ordenes':
    cargarUltimasOrdenes($pdo);
    break;
  default:
    echo json_encode(['error' => 'Acción no válida']);
    break;
}

function cargarResumen($pdo) {
  // 1) Total de productos
  $r = $pdo->query("SELECT COUNT(*) FROM productos");
  $total_products = (int)$r->fetchColumn();

  // 2) Total de proveedores
  $r = $pdo->query("SELECT COUNT(*) FROM proveedores");
  $total_proveedores = (int)$r->fetchColumn();

  // 3) Total de órdenes (tabla pedidos)
  $r = $pdo->query("SELECT COUNT(*) FROM pedidos");
  $total_ordenes = (int)$r->fetchColumn();

  // 4) Total ingresos (suma total de pedidos)
  $r = $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos");
  $total_ingresos = (float)$r->fetchColumn();

  // 5) Total clientes (usuarios con rol “cliente”)
  $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM usuarios u
    JOIN roles r ON u.rol_id = r.id_rol
    WHERE r.nombre_rol = 'Cliente'
  ");
  $stmt->execute();
  $total_clientes = (int)$stmt->fetchColumn();

  // 6) Total admins
  $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM usuarios u
    JOIN roles r ON u.rol_id = r.id_rol
    WHERE r.nombre_rol = 'Administrador'
  ");
  $stmt->execute();
  $total_admins = (int)$stmt->fetchColumn();

  echo json_encode([
    'productos'   => $total_products,
    'proveedores' => $total_proveedores,
    'ordenes'     => $total_ordenes,
    'ingresos'    => $total_ingresos,
    'clientes'    => $total_clientes,
    'admins'      => $total_admins
  ]);
}

function cargarGraficoOrdenes($pdo) {
  // Órdenes por mes (últimos 12 meses)
  $meses = [];
  $valores = [];
  $stmt = $pdo->query("
    SELECT MONTH(fecha_pedido) AS mes, COUNT(*) AS total
    FROM pedidos
    WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY MONTH(fecha_pedido)
    ORDER BY MONTH(fecha_pedido)
  ");
  $map = [
    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',
    6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',
    10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
  ];
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $meses[] = $map[intval($row['mes'])];
    $valores[] = (int)$row['total'];
  }
  echo json_encode(['meses' => $meses, 'valores' => $valores]);
}

function cargarGraficoPendientes($pdo) {
  // Órdenes pendientes vs entregadas
  $stmt = $pdo->query("
    SELECT 
      SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
      SUM(CASE WHEN estado = 'Entregado' THEN 1 ELSE 0 END) AS entregadas
    FROM pedidos
  ");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  echo json_encode([
    'pendientes' => (int)$row['pendientes'],
    'entregadas' => (int)$row['entregadas']
  ]);
}

function cargarProductoTop($pdo) {
  // Producto más vendido (detalles_pedido)
  $stmt = $pdo->prepare("
    SELECT 
      p.id_producto, p.nombre,
      (SELECT imagen_url 
         FROM imagenes_productos ip 
         WHERE ip.producto_id = p.id_producto AND ip.principal = 1 
         LIMIT 1) AS imagen_principal,
      SUM(dp.cantidad) AS cantidad
    FROM detalles_pedido dp
    JOIN productos p ON dp.producto_id = p.id_producto
    GROUP BY p.id_producto
    ORDER BY cantidad DESC
    LIMIT 1
  ");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    echo json_encode(['nombre'=>'N/A','cantidad'=>0,'imagen_principal'=>'']);
  } else {
    echo json_encode([
      'nombre' => $row['nombre'],
      'cantidad' => (int)$row['cantidad'],
      'imagen_principal' => $row['imagen_principal'] ?? ''
    ]);
  }
}

function cargarClienteTop($pdo) {
  // Cliente (usuario) con más pedidos
  $stmt = $pdo->prepare("
    SELECT 
      u.id_usuario,
      CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
      COUNT(*) AS cantidad
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id_usuario
    GROUP BY p.usuario_id
    ORDER BY cantidad DESC
    LIMIT 1
  ");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    echo json_encode(['nombre_completo'=>'N/A','cantidad'=>0]);
  } else {
    echo json_encode([
      'nombre_completo' => $row['nombre_completo'],
      'cantidad' => (int)$row['cantidad']
    ]);
  }
}

function cargarUltimasOrdenes($pdo) {
  // Últimas 5 órdenes
  $stmt = $pdo->prepare("
    SELECT 
      p.id_pedido,
      CONCAT(u.nombre,' ',u.apellido) AS nombre_cliente,
      DATE_FORMAT(p.fecha_pedido,'%Y-%m-%d') AS fecha,
      p.total,
      p.estado
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id_usuario
    ORDER BY p.fecha_pedido DESC
    LIMIT 5
  ");
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($rows);
}
