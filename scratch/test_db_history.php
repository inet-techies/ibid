<?php
require 'index.php';
$CI =& get_instance();
$CI->load->model('bid/bid_model');
$history = $CI->bid_model->getBidTrackingHistory(1);
foreach ($history as $h) {
    echo $h['CreationTs'] . ' | ' . ($h['UpdatedByName'] ?? '') . ' | ' . $h['Remarks'] . ' | ' . ($h['StatusDisplayName'] ?? '') . "\n";
}
