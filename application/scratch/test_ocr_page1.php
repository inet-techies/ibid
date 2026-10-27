<?php
$poppler = 'd:/xampp/htdocs/ibid/application/third_party/poppler-23.11.0-0/Library/bin/pdftoppm.exe';
$pdf = 'd:/xampp/htdocs/ibid/uploads/tender/1793097828_Tender-Summary-103059665.pdf';
$tmpPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_p1_' . uniqid();

$cmd = escapeshellarg($poppler) . ' -png -r 150 -f 1 -l 1 ' . escapeshellarg($pdf) . ' ' . escapeshellarg($tmpPrefix);
exec($cmd, $out, $ret);
echo "pdftoppm return: $ret\n";

$renderedImg = null;
foreach (glob($tmpPrefix . '*.png') as $img) {
    echo "Rendered image: $img (size: " . filesize($img) . " bytes)\n";
    $renderedImg = $img;
    break;
}

if ($renderedImg) {
    $script = 'd:/xampp/htdocs/ibid/application/third_party/win_ocr_runner.ps1';
    $psCmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script) . ' -FilePath ' . escapeshellarg($renderedImg) . ' -FileType png';
    $ocrText = shell_exec($psCmd);
    echo "\nOCR Result (" . strlen($ocrText) . " chars):\n" . substr(trim($ocrText), 0, 300) . "...\n";
    @unlink($renderedImg);
}
