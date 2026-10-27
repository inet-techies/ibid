<?php
$popplerBin = 'd:/xampp/htdocs/ibid/application/third_party/poppler-23.11.0-0/Library/bin/pdftoppm.exe';
$winOcrScript = 'd:/xampp/htdocs/ibid/application/third_party/win_ocr_runner.ps1';

echo "Poppler pdftoppm exists: " . (file_exists($popplerBin) ? "YES" : "NO") . "\n";
echo "Win OCR script exists: " . (file_exists($winOcrScript) ? "YES" : "NO") . "\n";

// Check Tesseract
$whereTess = @trim(@shell_exec('where.exe tesseract 2>NUL'));
echo "Tesseract in PATH: " . (!empty($whereTess) ? $whereTess : "NO") . "\n";

// Test rendering and OCR on a sample PDF
$pdf = 'd:/xampp/htdocs/ibid/uploads/tender/1793097828_Tender-Summary-103059665.pdf';
if (file_exists($pdf)) {
    $tmpPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_test_' . uniqid();
    $cmd = escapeshellarg($popplerBin) . ' -png -r 150 -f 1 -l 1 ' . escapeshellarg($pdf) . ' ' . escapeshellarg($tmpPrefix);
    @exec($cmd, $o, $ret);
    $imgs = glob($tmpPrefix . '*.png');
    if (!empty($imgs)) {
        $img = $imgs[0];
        echo "Rendered page 1 to: $img (" . filesize($img) . " bytes)\n";
        
        $psCmd = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($winOcrScript) . ' -FilePath ' . escapeshellarg($img) . ' -FileType png';
        $t0 = microtime(true);
        $ocr = shell_exec($psCmd);
        $elapsed = round(microtime(true) - $t0, 2);
        echo "OCR completed in {$elapsed}s with " . strlen($ocr) . " characters.\n";
        @unlink($img);
    }
}
