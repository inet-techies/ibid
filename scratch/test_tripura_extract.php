<?php
define('BASEPATH', 'true');
define('APPPATH', __DIR__ . '/../application/');
define('FCPATH', __DIR__ . '/../');

require_once APPPATH . 'libraries/Document_extractor.php';

$extractor = new Document_extractor();
$file = FCPATH . 'uploads/tender/1793870028_Tendernotice_2__5.pdf';
$res = $extractor->extract($file, 'Tendernotice_2__5.pdf', 'pdf');

echo "=== STATUS: " . ($res['status'] ?? 'unknown') . "\n";
echo "=== FIELDS ===\n";
foreach ($res['fields'] as $k => $f) {
    echo "$k: " . json_encode($f) . "\n";
}

echo "=== AI SUMMARY MILESTONES ===\n";
$ms = [
    'tender_id', 'ref_no', 'emd_value', 'tender_fee', 'portal',
    'published_date', 'tender_publication_time',
    'document_sale_start_date', 'document_sale_start_time',
    'document_sale_end_date', 'document_sale_end_time',
    'prebid_query_start_date', 'prebid_query_start_time',
    'prebid_query_end_date', 'prebid_query_end_time',
    'prebid_meeting_date', 'prebid_meeting_time',
    'prebid_response_date', 'prebid_response_time',
    'bid_submission_start_date', 'bid_submission_start_time',
    'submission_date', 'bid_submission_end_time',
    'pq_bid_opening_date', 'pq_bid_opening_time',
    'technical_opening_date', 'technical_opening_time',
    'financial_opening_date', 'financial_opening_time'
];
foreach ($ms as $k) {
    if (isset($res['ai_summary'][$k])) {
        echo "$k: " . $res['ai_summary'][$k] . "\n";
    }
}

