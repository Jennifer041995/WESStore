<?php

require_once __DIR__ . '/../sql/conexion.php';
header('Content-Type: application/json');

$pdo = getConnection();

// Función auxiliar para escapar HTML (aunque aquí no es esencial pues sólo devolvemos JSON)
function esc($t) {
  return htmlspecialchars($t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

try {
  // 1) Total de productos activos
  $stmt1 = $pdo->query("SELECT COUNT(*) AS total FROM productos WHERE estado = 'Activo'");
  $totalProductos = (int)$stmt1->fetch()['total'];

  // 2) Órdenes abiertas (Pendiente o Procesando)
  $stmt2 = $pdo->query("
    SELECT COUNT(*) AS total
    FROM pedidos
    WHERE estado IN ('Pendiente','Procesando')
  ");
  $ordenesAbiertas = (int)$stmt2->fetch()['total'];

  // 3) Ítems con stock bajo (stock <= stock_minimo)
  $stmt3 = $pdo->query("
    SELECT COUNT(*) AS total
    FROM inventario
    WHERE stock <= stock_minimo
  ");
  $stockBajo = (int)$stmt3->fetch()['total'];

  // 4) Ventas del día (0:00 a hoy 23:59)
  $stmt4 = $pdo->prepare("
    SELECT 
      COALESCE(SUM(total),0) AS ventas_hoy
    FROM pedidos
    WHERE DATE(fecha_pedido) = CURDATE()
      AND estado NOT IN ('Cancelado')
  ");
  $stmt4->execute();
  $ventasHoy = (float)$stmt4->fetch()['ventas_hoy'];

  // 5) Nuevos clientes en últimos 7 días
  $stmt5 = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE DATE(creado_en) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
  ");
  $stmt5->execute();
  $clientesNuevos = (int)$stmt5->fetch()['total'];

  // 6) Órdenes creadas en cada uno de los últimos 7 días
  //    Devuelve un arreglo con fecha, cantidad, y etiqueta corta (p.ej. “Mar 27”)
  $stmt6 = $pdo->prepare("
    SELECT 
      DATE(fecha_pedido) AS dia,
      COUNT(*) AS cantidad
    FROM pedidos
    WHERE DATE(fecha_pedido) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
      AND estado NOT IN ('Cancelado')
    GROUP BY DATE(fecha_pedido)
    ORDER BY DATE(fecha_pedido) ASC
  ");
  $stmt6->execute();
  $rawSemana = $stmt6->fetchAll(PDO::FETCH_ASSOC);

  // Asegurar que haya 7 filas, incluso si no hay datos en algún día
  $ordenesSemana = [];
  $hoy = new DateTime();
  for ($i = 6; $i >= 0; $i--) {
    $diaObj = (clone $hoy)->sub(new DateInterval("P{$i}D"));
    $fecha   = $diaObj->format('Y-m-d');
    $label   = $diaObj->format('D d'); // ej. “Mon 27”
    $cantidad = 0;
    foreach ($rawSemana as $r) {
      if ($r['dia'] === $fecha) {
        $cantidad = (int)$r['cantidad'];
        break;
      }
    }
    $ordenesSemana[] = [
      'dia'       => esc($fecha),
      'dia_label' => esc($label),
      'cantidad'  => $cantidad
    ];
  }

  // 7) Últimas 5 órdenes (sin importar estado)
  $stmt7 = $pdo->query("
    SELECT
      p.id_pedido,
      CONCAT(u.nombre,' ',u.apellido) AS nombre_cliente,
      DATE_FORMAT(p.fecha_pedido,'%Y-%m-%d') AS fecha_pedido,
      p.estado,
      p.total
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id_usuario
    ORDER BY p.fecha_pedido DESC
    LIMIT 5
  ");
  $ultimasOrdenes = $stmt7->fetchAll(PDO::FETCH_ASSOC);

  // 8) Devolver todo en un solo JSON
  echo json_encode([
    'ok'                => true,
    'total_productos'   => $totalProductos,
    'ordenes_abiertas'  => $ordenesAbiertas,
    'stock_bajo'        => $stockBajo,
    'ventas_hoy'        => $ventasHoy,
    'clientes_nuevos'   => $clientesNuevos,
    'ordenes_semana'    => $ordenesSemana,
    'ultimas_ordenes'   => $ultimasOrdenes
  ]);
} catch (Exception $e) {
  echo json_encode([
    'ok'      => false,
    'message' => $e->getMessage()
  ]);
}
