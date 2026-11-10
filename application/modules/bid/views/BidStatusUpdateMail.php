<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bid Status Update - <?= htmlspecialchars($bid['BidTitle'] ?? 'Tender'); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
            font-size: 14px;
            line-height: 1.6;
        }
        .email-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 25px 20px;
        }
        .greeting {
            margin: 0 0 16px 0;
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }
        .intro {
            margin: 0 0 14px 0;
            font-size: 14px;
            color: #374151;
            line-height: 1.65;
        }
        .section-title {
            margin: 22px 0 10px 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 22px 0;
            border: 1px solid #d1d5db;
            font-size: 13.5px;
        }
        .details-table th {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            text-align: left;
            background-color: #f8fafc;
            font-weight: 700;
            color: #111827;
        }
        .details-table td {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }
        .details-table .label {
            width: 35%;
            font-weight: 600;
            color: #374151;
            background-color: #f9fafb;
        }
        .details-table .value {
            color: #111827;
            word-break: break-word;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-weight: 700;
            color: #1e40af;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
        }
        .actions-intro {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }
        .actions-list {
            margin: 0 0 22px 0;
            padding-left: 22px;
            color: #374151;
            line-height: 1.65;
            font-size: 13.5px;
        }
        .actions-list li {
            margin-bottom: 7px;
        }
        .timeline-intro {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }
        .timeline-deadline {
            margin: 10px 0 14px 0;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .timeline-note {
            margin: 0 0 12px 0;
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }
        .closing {
            margin-top: 24px;
            font-size: 14px;
            line-height: 1.6;
            color: #111827;
        }
        .portal-link {
            color: #2563eb;
            text-decoration: none;
            word-break: break-all;
        }
    </style>
</head>
<body>

<div class="email-container" style="max-width: 700px; margin: 0 auto; padding: 25px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #111827; font-size: 14px; line-height: 1.6;">

    <!-- Greeting -->
    <p class="greeting" style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: #111827;">
        Dear Team,
    </p>

    <!-- Introduction -->
    <p class="intro" style="margin: 0 0 14px 0; font-size: 14px; color: #374151; line-height: 1.65;">
        This is to inform you that the status of tender bid <strong><?= htmlspecialchars($bid['BidTitle'] ?? 'Tender'); ?></strong> has been updated to <strong><?= htmlspecialchars($currentStatus ?? ($bid['CurrentStatus'] ?? 'Updated')); ?></strong> in the <strong>Bid Management Portal</strong>.
    </p>

    <p class="intro" style="margin: 0 0 20px 0; font-size: 14px; color: #374151; line-height: 1.65;">
        Please find the tender details below for your review and further action.
    </p>

    <!-- 1. Tender Information -->
    <div class="section-title" style="margin: 22px 0 10px 0; font-size: 15px; font-weight: 700; color: #111827;">
        1. Tender Information
    </div>

    <table class="details-table" style="width: 100%; border-collapse: collapse; margin: 0 0 22px 0; border: 1px solid #d1d5db; font-size: 13.5px;">
        <thead>
            <tr style="background-color: #f8fafc;">
                <th style="padding: 9px 12px; border: 1px solid #d1d5db; text-align: left; font-weight: 700; color: #111827; width: 35%;">Particulars</th>
                <th style="padding: 9px 12px; border: 1px solid #d1d5db; text-align: left; font-weight: 700; color: #111827;">Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Bid ID</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;">#<?= htmlspecialchars($Bcid ?? ($bid['Bcid'] ?? '-')); ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Bid Title</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= htmlspecialchars($bid['BidTitle'] ?? '-'); ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Tender Reference No.</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= !empty($bid['TenderId']) ? htmlspecialchars($bid['TenderId']) : '-'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Current Status</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;">
                    <span class="status-badge" style="display: inline-block; padding: 2px 8px; font-weight: 700; color: #1e40af; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px;">
                        <?= htmlspecialchars($currentStatus ?? ($bid['CurrentStatus'] ?? 'Updated')); ?>
                    </span>
                </td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Department</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= !empty($bid['BidDepartment']) ? htmlspecialchars($bid['BidDepartment']) : '-'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Bid Type</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= !empty($bid['BidType']) ? htmlspecialchars($bid['BidType']) : '-'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Estimated Tender Value</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;">&#8377;<?= !empty($bid['EstimatedValue']) ? number_format((float)$bid['EstimatedValue'], 2) : '0.00'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">EMD Amount</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;">
                    &#8377;<?= (!empty($bid['EmdValue']) && (float)$bid['EmdValue'] > 0) ? number_format((float)$bid['EmdValue'], 2) : '0.00'; ?>
                </td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Published Date</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= (!empty($bid['PublishedDate']) && strtotime($bid['PublishedDate'])) ? date('d-M-Y', strtotime($bid['PublishedDate'])) : '-'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Submission Deadline</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate'])) ? date('d-M-Y', strtotime($bid['SubmissionDate'])) : '-'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Bid Opening Date</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= (!empty($bid['OpeningDate']) && strtotime($bid['OpeningDate'])) ? date('d-M-Y', strtotime($bid['OpeningDate'])) : '-'; ?></td>
            </tr>

            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Tender Portal</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;">
                    <?php if (!empty($bid['TenderPortalUrl'])): ?>
                        <a href="<?= htmlspecialchars($bid['TenderPortalUrl']); ?>" target="_blank" class="portal-link" style="color: #2563eb; text-decoration: none; word-break: break-all;">
                            <?= htmlspecialchars($bid['TenderPortalUrl']); ?>
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>

            <?php if (!empty($bid['Remarks'])): ?>
            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Remarks</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= htmlspecialchars($bid['Remarks']); ?></td>
            </tr>
            <?php endif; ?>

            <?php if (!empty($bid['ActualDate']) && strtotime($bid['ActualDate'])): ?>
            <tr>
                <td class="label" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: 600; color: #374151; background-color: #f9fafb;">Action Date</td>
                <td class="value" style="padding: 8px 12px; border: 1px solid #d1d5db; color: #111827;"><?= date('d-M-Y', strtotime($bid['ActualDate'])); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2. Required Actions -->
    <div class="section-title" style="margin: 24px 0 10px 0; font-size: 15px; font-weight: 700; color: #111827;">
        2. Required Actions
    </div>

    <p class="actions-intro" style="margin: 0 0 10px 0; font-size: 14px; color: #374151; line-height: 1.6;">
        The concerned teams are requested to initiate the following activities:
    </p>

    <ol class="actions-list" style="margin: 0 0 22px 0; padding-left: 22px; color: #374151; line-height: 1.65; font-size: 13.5px;">
        <li style="margin-bottom: 7px;"><strong>Tender Evaluation:</strong> Review the tender requirements, scope of work, and eligibility criteria.</li>
        <li style="margin-bottom: 7px;"><strong>Technical Assessment:</strong> Verify technical specifications, OEM requirements, and compliance conditions.</li>
        <li style="margin-bottom: 7px;"><strong>Commercial Assessment:</strong> Evaluate the estimated project value, EMD, financial implications, and commercial feasibility.</li>
        <li style="margin-bottom: 7px;"><strong>Document Preparation:</strong> Identify and arrange all mandatory certificates, declarations, and supporting documents.</li>
        <li style="margin-bottom: 7px;"><strong>Team Coordination:</strong> Assign responsibilities to the respective technical, commercial, and documentation teams.</li>
        <li style="margin-bottom: 7px;"><strong>Approval &amp; Submission:</strong> Obtain the necessary management approvals and ensure submission before the closing deadline.</li>
    </ol>

    <!-- 3. Important Timeline -->
    <div class="section-title" style="margin: 24px 0 10px 0; font-size: 15px; font-weight: 700; color: #111827;">
        3. Important Timeline
    </div>

    <p class="timeline-intro" style="margin: 0 0 10px 0; font-size: 14px; color: #374151; line-height: 1.6;">
        Please ensure that the bid evaluation and internal preparation activities are completed sufficiently ahead of the tender submission deadline.
    </p>

    <p class="timeline-deadline" style="margin: 10px 0 14px 0; font-size: 14px; font-weight: 700; color: #111827;">
        Submission Deadline: <?= (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate'])) ? date('d-M-Y', strtotime($bid['SubmissionDate'])) : '-'; ?>
    </p>

    <p class="timeline-note" style="margin: 0 0 10px 0; font-size: 14px; color: #374151; line-height: 1.6;">
        Your timely review and coordination are essential to ensure that we do not miss this bidding opportunity.
    </p>

    <p class="timeline-note" style="margin: 0 0 22px 0; font-size: 14px; color: #374151; line-height: 1.6;">
        Please review the details and share your feedback, feasibility assessment, and further action plan.
    </p>

    <!-- Closing -->
    <div class="closing" style="margin-top: 24px; font-size: 14px; line-height: 1.6; color: #111827;">
        Regards,<br>
        <strong>Bid Management Team</strong>
        <?php if (!empty($senderName) && $senderName !== 'Bid Management Team'): ?>
            <div style="font-size: 12px; color: #6b7280; margin-top: 3px;">(Updated by: <?= htmlspecialchars($senderName); ?>)</div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
