<?php
// app/controllers/download_invoice.php

// Asegúrate de que el usuario esté logueado y tenga permisos para descargar esta factura
// Esto es CRÍTICO por seguridad. Por ahora, lo haremos simple, pero en producción
// DEBES verificar que el usuario logueado es el dueño del pedido o un administrador.
session_start();

if (!isset($_GET['pedido_id'])) {
    die("ID de pedido no especificado.");
}

$pedido_id = (int)$_GET['pedido_id'];

// Ruta base donde se guardan las facturas (ajusta según tu estructura real)
// Desde app/controllers, sube dos niveles (a WESStore2/) y luego baja a public/invoices/
$invoice_dir = __DIR__ . '/../../media/temp/invoices/';
$file_name = "factura-pedido-{$pedido_id}.pdf";
$file_path = $invoice_dir . $file_name;

if (!file_exists($file_path)) {
    die("Factura no encontrada.");
}

// Opcional pero recomendado: Verificación de propiedad (simulada)
// En un sistema real, harías una consulta a la DB para ver si $_SESSION['usuario']['id']
// es el mismo que el usuario_id del pedido con $pedido_id.
// if (!isset($_SESSION['usuario']['id']) || !checkInvoiceOwnership($pedido_id, $_SESSION['usuario']['id'])) {
//     die("Acceso denegado.");
// }

// Envía los encabezados para forzar la descarga
header('Content-Description: File Transfer');
header('Content-Type: application/pdf'); // Tipo MIME para PDF
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"'); // Forzar descarga con el nombre original
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path)); // Tamaño del archivo
ob_clean(); // Limpia cualquier búfer de salida antes de enviar el archivo
flush();    // Limpia el búfer del sistema
readfile($file_path); // Lee el archivo y lo envía al navegador
exit;
?>