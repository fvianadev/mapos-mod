<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/../vendor/autoload.php';

function pdf_create($html, $filename, $stream = true, $landscape = false)
{
    if ($landscape) {
        $mpdf = new \Mpdf\Mpdf(['c', 'A4-L', 'tempDir' => FCPATH . 'assets/uploads/temp/']);
    } else {
        $mpdf = new \Mpdf\Mpdf(['c', 'A4', 'tempDir' => FCPATH . 'assets/uploads/temp/']);
    }

    $mpdf->showImageErrors = true;

    foreach ((array) $html as $pedaco) {
        $mpdf->WriteHTML($pedaco);
    }

    if ($stream) {
        $mpdf->Output($filename . '.pdf', 'I');
    } else {
        $mpdf->Output(FCPATH . 'assets/uploads/temp/' . $filename . '.pdf', 'F');

        return FCPATH . 'assets/uploads/temp/' . $filename . '.pdf';
    }
}

function barcode_valido($code, $type)
{
    if ((string) $type === 'QR') {
        return class_exists('Mpdf\QrCode\QrCode');
    }

    try {
        return (new \Mpdf\Barcode())->getBarcodeArray((string) $code, (string) $type) !== false;
    } catch (\Throwable $e) {
        return false;
    }
}
