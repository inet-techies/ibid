<?php
define('BASEPATH', 'd:/xampp/htdocs/ibid/system/');
define('FCPATH', 'd:/xampp/htdocs/ibid/');
require_once 'd:/xampp/htdocs/ibid/vendor/autoload.php';

// Mock CI environment
class CI_Dummy {
    public $config;
    public function __construct() {
        $this->config = new stdClass();
    }
}
function get_instance() {
    static $ci;
    if (!$ci) $ci = new CI_Dummy();
    return $ci;
}
function log_message($level, $msg) {
    echo "  [LOG: $level] $msg\n";
}

require_once 'd:/xampp/htdocs/ibid/application/libraries/Document_extractor.php';

$ext = new Document_extractor();

echo "=== OCR Engine Verification ===\n";
$engines = $ext->verifyOcrEngines();
print_r($engines);

echo "\n=== Test 1: Searchable PDF (Bank of Baroda Fact Sheet) ===\n";
$pdf1 = 'd:/xampp/htdocs/ibid/uploads/tender/1793094169_BANK_OF_BARODA-_FACTSHEET.pdf';
if (file_exists($pdf1)) {
    $res1 = $ext->extract($pdf1, basename($pdf1));
    echo "Title: " . $res1['title'] . "\n";
    echo "Title Source: " . (isset($res1['title_source']) ? $res1['title_source'] : 'N/A') . "\n";
    echo "Authority: " . $res1['ai_summary']['authority'] . "\n";
    echo "Ref No: " . $res1['ai_summary']['ref_no'] . "\n";
}

echo "\n=== Test 2: Tender Summary with Page 1 OCR needed ===\n";
$pdf2 = 'd:/xampp/htdocs/ibid/uploads/tender/1793097828_Tender-Summary-103059665.pdf';
if (file_exists($pdf2)) {
    $res2 = $ext->extract($pdf2, basename($pdf2));
    echo "Title: " . $res2['title'] . "\n";
    echo "Title Source: " . (isset($res2['title_source']) ? $res2['title_source'] : 'N/A') . "\n";
    echo "Authority: " . $res2['ai_summary']['authority'] . "\n";
    echo "Ref No: " . $res2['ai_summary']['ref_no'] . "\n";
}
