<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';
require_once APPPATH . 'libraries/tender_extractor/Tender_schedule_parser.php';

$extractor = new Document_extractor();
$file = FCPATH . 'uploads/tender/1793870402_RFPCOMPONENT_161026.pdf';
$pageMap = $extractor->extractPageMap($file);

$p1_2 = [1 => $pageMap[1], 2 => $pageMap[2]];
$records = Tender_schedule_parser::extractMilestones($p1_2);

print_r($records);

