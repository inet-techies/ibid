<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';

$extractor = new Document_extractor();
$file = FCPATH . 'uploads/tender/1793870402_RFPCOMPONENT_161026.pdf';
$res = $extractor->extract($file, 'RFPCOMPONENT_161026.pdf', 'pdf');

echo "=== STATUS: " . ($res['status'] ?? 'unknown') . "\n";
echo "=== EXTRACTED MILESTONES IN AI_SUMMARY ===\n";
$expected = [
    'document_sale_start_date' => '2026-10-17',
    'document_sale_start_time' => '17:00',
    'prebid_query_start_date'  => '2026-10-17',
    'prebid_query_start_time'  => '17:30',
    'prebid_query_end_date'    => '2026-10-21',
    'prebid_query_end_time'    => '18:30',
    'prebid_meeting_date'      => '2026-10-23',
    'prebid_meeting_time'      => '12:00',
    'prebid_response_date'     => '2026-10-28',
    'prebid_response_time'     => '18:00',
    'bid_submission_start_date'=> '2026-10-29',
    'bid_submission_start_time'=> '18:30',
    'submission_date'          => '2026-11-10',
    'submission_time'          => '15:00',
    'pq_bid_opening_date'      => '2026-11-11',
    'pq_bid_opening_time'      => '15:10'
];

$allPassed = true;
foreach ($expected as $k => $expVal) {
    $actVal = $res['ai_summary'][$k] ?? ($res['fields'][$k]['value'] ?? 'NOT_SET');
    $status = ($actVal === $expVal) ? 'PASS [OK]' : 'FAIL [X]';
    if ($actVal !== $expVal) $allPassed = false;
    echo sprintf("%-28s : Expected: %-12s | Actual: %-12s | %s\n", $k, $expVal, $actVal, $status);
}

echo "\nCheck unmentioned fields (must be empty/null):\n";
$unmentioned = ['published_date', 'tender_publication_time', 'technical_opening_date', 'technical_opening_time', 'financial_opening_date', 'financial_opening_time'];
foreach ($unmentioned as $u) {
    $val = $res['ai_summary'][$u] ?? '';
    echo sprintf("%-28s : %s\n", $u, empty($val) ? 'BLANK [OK]' : "PRESENT: $val [FAIL]");
}

echo "\nALL 8 ACTIVITIES MATCH EXPECTED: " . ($allPassed ? "YES! 100% SUCCESS" : "NO") . "\n";

