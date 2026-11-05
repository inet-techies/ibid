<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';
require_once APPPATH . 'libraries/tender_extractor/Tender_schedule_parser.php';

$extractor = new Document_extractor();
$file = FCPATH . 'uploads/tender/1793870028_Tendernotice_2__5.pdf';
$pageMap = $extractor->extractPageMap($file);

echo "Total pages: " . count($pageMap) . "\n";
echo "Page 6 raw text:\n" . $pageMap[6] . "\n";

$records = Tender_schedule_parser::extractMilestones($pageMap);
echo "\n=== EXTRACTED ATOMIC MILESTONES ===\n";
print_r($records);

