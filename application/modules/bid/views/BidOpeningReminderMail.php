<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bid Opening Reminder - <?= htmlspecialchars($bid['BidTitle'] ?? 'Tender'); ?></title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
            font-size: 14.5px;
            line-height: 1.6;
        }
        .mention-tag {
            background-color: #fef2f2;
            color: #b91c1c;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
        }
        .details-box {
            margin: 18px 0 22px 0;
            padding: 14px 18px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13.5px;
            line-height: 1.75;
            color: #334155;
            max-width: 580px;
        }
        .details-box strong {
            color: #0f172a;
        }
        .date-highlight {
            color: #b91c1c;
            font-weight: 700;
        }
        .closing {
            margin-top: 22px;
            font-size: 14px;
            line-height: 1.6;
            color: #111827;
        }
    </style>
</head>
<body>

<p style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: #000000;">Dear All,</p>

<p style="margin: 0 0 18px 0; font-size: 14.5px; line-height: 1.6; color: #111827;">
    This is a reminder regarding the scheduled opening date for bid <strong><?= htmlspecialchars($bid['BidTitle'] ?? 'Tender'); ?></strong> on the <strong>Bid Management Portal</strong>.
</p>

<div class="details-box">
    <div><strong>Bid Title:</strong> <?= htmlspecialchars($bid['BidTitle'] ?? '-'); ?></div>
    <div><strong>Tender ID:</strong> <?= !empty($bid['TenderId']) ? htmlspecialchars($bid['TenderId']) : '-'; ?></div>
    <div><strong>Opening Date:</strong> <span class="date-highlight"><?= (!empty($bid['OpeningDate']) && strtotime($bid['OpeningDate'])) ? date('d-M-Y', strtotime($bid['OpeningDate'])) : '-'; ?></span></div>
    <div><strong>Submission / Close Date:</strong> <?= (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate'])) ? date('d-M-Y', strtotime($bid['SubmissionDate'])) : '-'; ?></div>
    <div><strong>Current Status:</strong> <?= htmlspecialchars($currentStatus ?? ($bid['CurrentStatus'] ?? 'Active')); ?></div>
    <div><strong>Bid Department:</strong> <?= !empty($bid['BidDepartment']) ? htmlspecialchars($bid['BidDepartment']) : '-'; ?></div>
    <div><strong>Estimated Value:</strong> &#8377; <?= !empty($bid['EstimatedValue']) ? number_format((float)$bid['EstimatedValue'], 2) : '0.00'; ?></div>
    <?php if (!empty($bid['TenderPortalUrl'])): ?>
    <div><strong>Tender Portal:</strong> <a href="<?= htmlspecialchars($bid['TenderPortalUrl']); ?>" target="_blank" style="color:#2563eb;word-break:break-all;"><?= htmlspecialchars($bid['TenderPortalUrl']); ?></a></div>
    <?php endif; ?>
</div>

<p style="margin: 0 0 22px 0; font-size: 14px; color: #374151;">
    Please take note of the scheduled bid opening and ensure the necessary action is completed.
</p>

<div class="closing">
    Thanks &amp; Regards,<br>
    <strong><?= htmlspecialchars($senderName ?? 'Reka R.'); ?></strong>
</div>

</body>
</html>
