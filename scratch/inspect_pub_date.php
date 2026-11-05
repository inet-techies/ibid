<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';
$extractor = new Document_extractor();
$file = FCPATH . 'uploads/tender/1793870402_RFPCOMPONENT_161026.pdf';
$res = $extractor->extract($file, 'RFPCOMPONENT_161026.pdf', 'pdf');

echo "published_date field:\n";
print_r($res['fields']['published_date']);

