<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';
$extractor = new Document_extractor();
$file = FCPATH . 'uploads/tender/1793870402_RFPCOMPONENT_161026.pdf';
$pageMap = $extractor->extractPageMap($file);

echo "Total pages: " . count($pageMap) . "\n";
foreach ($pageMap as $pNo => $pText) {
    if (stripos($pText, '2026-10') !== false || stripos($pText, '23/10/2026') !== false || stripos($pText, '23-10-2026') !== false || stripos($pText, '10/11/2026') !== false || stripos($pText, 'pre-bid') !== false || stripos($pText, 'schedule') !== false) {
        echo "=== PAGE $pNo ===\n";
        $lines = explode("\n", $pText);
        foreach ($lines as $l) {
            if (preg_match('/2026|pre-bid|submission|sale|query|clarification|opening|schedule/i', $l)) {
                echo "   " . trim($l) . "\n";
            }
        }
    }
}

