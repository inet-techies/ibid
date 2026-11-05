<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';
$extractor = new Document_extractor();

$tenders = [
    'RFPCOMPONENT'   => FCPATH . 'uploads/tender/1793870402_RFPCOMPONENT_161026.pdf',
    'Tripura Notice' => FCPATH . 'uploads/tender/1793870028_Tendernotice_2__5.pdf',
];

// Check other files
$otherFiles = glob(FCPATH . 'uploads/tender/*.{pdf,docx,doc}', GLOB_BRACE);
$tested = [];
foreach ($otherFiles as $f) {
    $base = basename($f);
    // test unique base patterns
    $key = preg_replace('/^\d+_/', '', $base);
    if (!isset($tested[$key])) {
        $tested[$key] = $f;
    }
}

echo "=== TESTING DATASET OF TENDERS ===\n";
foreach ($tested as $label => $path) {
    if (!file_exists($path)) continue;
    echo "\n------------------------------------------------------------\n";
    echo "FILE: $label (" . basename($path) . ")\n";
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $res = $extractor->extract($path, basename($path), $ext);
    echo "Status: " . ($res['status'] ?? 'unknown') . "\n";
    echo "Tender ID: " . ($res['fields']['tender_reference']['value'] ?? 'N/A') . "\n";
    
    $milestones = [
        'published_date', 'document_sale_start_date', 'document_sale_end_date',
        'prebid_query_start_date', 'prebid_query_end_date', 'prebid_meeting_date',
        'prebid_response_date', 'bid_submission_start_date', 'submission_date',
        'pq_bid_opening_date', 'technical_opening_date', 'financial_opening_date'
    ];
    $found = 0;
    foreach ($milestones as $m) {
        $date = $res['ai_summary'][$m] ?? '';
        $timeKey = str_replace('_date', '_time', $m);
        if ($m === 'submission_date') $timeKey = 'submission_time';
        $time = $res['ai_summary'][$timeKey] ?? '';
        if (!empty($date)) {
            $found++;
            echo sprintf("  %-28s: %s %s\n", $m, $date, $time ? "at $time" : "");
        }
    }
    echo "Total milestones extracted: $found\n";
}

