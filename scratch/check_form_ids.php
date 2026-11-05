<?php
$content = file_get_contents('application/modules/bid/views/CreateBids.php');
$ids = [
    'startDate', 'tenderPublicationTime',
    'documentSaleStartDate', 'documentSaleStartTime',
    'documentSaleEndDate', 'documentSaleEndTime',
    'prebidQueryStartDate', 'prebidQueryStartTime',
    'prebidQueryEndDate', 'prebidQueryEndTime',
    'prebidMeetingDate', 'prebidMeetingTime',
    'prebidResponseDate', 'prebidResponseTime',
    'bidSubmissionStartDate', 'bidSubmissionStartTime',
    'dueDate', 'bidSubmissionEndTime',
    'pqBidOpeningDate', 'pqBidOpeningTime',
    'dateofBirth', 'technicalBidOpeningTime',
    'financialBidOpeningDate', 'financialBidOpeningTime',
    'bidTenderIDInput', 'bidEmdInput', 'bidSummaryInput', 'bidTenderPortalInput'
];
foreach ($ids as $id) {
    $found = strpos($content, 'id="' . $id . '"') !== false || strpos($content, "id='" . $id . "'") !== false;
    echo sprintf("%-26s: %s\n", $id, $found ? 'FOUND [OK]' : 'MISSING [X]');
}

