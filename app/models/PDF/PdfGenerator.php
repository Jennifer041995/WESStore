<?php
// C:\xampp1\htdocs\WESStore\app\models\PDF\PdfGenerator.php

require_once __DIR__ . '/../../../resources/dompdf/autoload.inc.php';



use Dompdf\Dompdf;
use Dompdf\Options;


class PdfGenerator {

    public function generateInvoicePdf($pedido_id, $cliente_data, $productos_para_pedido, $outputFilePath) {
        // Configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // ABSOLUTAMENTE NECESARIO para cargar imágenes desde URL externa
        $dompdf = new Dompdf($options);

        // --- INICIO DE LÓGICA PARA GENERAR URL DEL QR CODE ---
        // Generar una URL simulada para el QR que "apuntaría" a Hacienda
        // Esto es una SIMULACIÓN y no se conecta con Hacienda real.
        // Un DTE real tendría una estructura de URL y parámetros más complejos y una URL oficial de Hacienda.
        $dte_code_simulated = str_pad($pedido_id, 10, '0', STR_PAD_LEFT); // Simula un código de DTE
        // URL de verificación de DTE simulada para El Salvador.
        // El NIT 06140101000000 es un ejemplo, no un NIT real de Hacienda.
        $qr_data_content = "https://www.hacienda.gob.sv/dte/verificar?dte=" . $dte_code_simulated . "&nit=06140101000000";

        // Codificar la URL para que sea segura en la URL del generador de QR.
        $encoded_qr_data = urlencode($qr_data_content);

        // Construir la URL del generador de QR de Google Charts.
        // ChS = "Chart Size" (ej. 150x150 píxeles)
        // ChL = "Chart Data" (lo que se va a codificar)
        $qrcode_url = "https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=" . $encoded_qr_data;
        // --- FIN DE LÓGICA PARA GENERAR URL DEL QR CODE ---


        // Iniciar el buffer de salida para capturar el HTML
        ob_start();
        // Incluir la plantilla HTML de la factura.
        // Las variables $pedido_id, $cliente_data, $productos_para_pedido, $qrcode_url (NUEVA)
        // se pasan al ámbito de la plantilla.
        include __DIR__ . '/../../../app/templates/factura_template.php'; // Asegúrate de que esta ruta es correcta
        $html = ob_get_clean(); // Capturar el HTML generado

        $dompdf->loadHtml($html);

        // (Opcional) Configurar tamaño y orientación del papel
        $dompdf->setPaper('letter', 'portrait');

        // Renderizar el HTML como PDF
        $dompdf->render();

        // Guardar el PDF en el servidor
        $output = $dompdf->output();
        if (file_put_contents($outputFilePath, $output)) {
            return true;
        } else {
            error_log("Error al guardar el PDF en: " . $outputFilePath);
            return false;
        }
    }
}
