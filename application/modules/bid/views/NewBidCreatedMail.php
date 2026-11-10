
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        New Bid Registered - <?= htmlspecialchars($bid['BidTitle'] ?? 'Tender'); ?>
    </title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI',
                         Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
            font-size: 14px;
            line-height: 1.6;
        }

        .email-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px 25px;
        }

        .greeting {
            margin: 0 0 18px 0;
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        .intro {
            margin: 0 0 24px 0;
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
        }

        .section-title {
            margin: 24px 0 12px 0;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 24px 0;
            border: 1px solid #d1d5db;
        }

        .details-table td {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            font-size: 13.5px;
        }

        .details-table .label {
            width: 35%;
            font-weight: 600;
            color: #374151;
            background-color: #f8fafc;
        }

        .details-table .value {
            color: #111827;
            word-break: break-word;
        }

        .actions {
            margin: 0 0 24px 0;
            padding-left: 22px;
            color: #374151;
        }

        .actions li {
            margin-bottom: 8px;
            font-size: 13.5px;
        }

        .timeline-box {
            margin: 0 0 24px 0;
            padding: 14px 16px;
            background-color: #f8fafc;
            border: 1px solid #d1d5db;
        }

        .timeline-box div {
            margin-bottom: 6px;
            font-size: 13.5px;
            color: #374151;
        }

        .timeline-box div:last-child {
            margin-bottom: 0;
        }

        .closing {
            margin-top: 26px;
            font-size: 14px;
            line-height: 1.7;
            color: #374151;
        }

        .portal-link {
            color: #2563eb;
            text-decoration: none;
            word-break: break-all;
        }
    </style>
</head>

<body>

<div class="email-container">

    <!-- Greeting -->
    <p class="greeting">
        Dear Team,
    </p>

    <!-- Introduction -->
    <p class="intro">
        The new tender bid
        <strong><?= htmlspecialchars($bid['BidTitle'] ?? 'Tender'); ?></strong>
        has been successfully registered in the
        <strong>Bid Management Portal</strong>.
    </p>


    <!-- 1. Tender Information -->
    <div class="section-title">
        1. Tender Information
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Bid ID</td>
            <td class="value">
                #<?= htmlspecialchars($Bcid ?? '-'); ?>
            </td>
        </tr>

        <tr>
            <td class="label">Bid Title</td>
            <td class="value">
                <?= htmlspecialchars($bid['BidTitle'] ?? '-'); ?>
            </td>
        </tr>

        <tr>
            <td class="label">Tender ID</td>
            <td class="value">
                <?= !empty($bid['TenderId'])
                    ? htmlspecialchars($bid['TenderId'])
                    : '-'; ?>
            </td>
        </tr>

        <tr>
            <td class="label">Bid Department</td>
            <td class="value">
                <?= !empty($bid['BidDepartment'])
                    ? htmlspecialchars($bid['BidDepartment'])
                    : '-'; ?>
            </td>
        </tr>

        <tr>
            <td class="label">Bid Type</td>
            <td class="value">
                <?= !empty($bid['BidType'])
                    ? htmlspecialchars($bid['BidType'])
                    : '-'; ?>
            </td>
        </tr>

        <tr>
            <td class="label">Estimated Tender Value</td>
            <td class="value">
                &#8377;
                <?= !empty($bid['EstimatedValue'])
                    ? number_format((float)$bid['EstimatedValue'], 2)
                    : '0.00'; ?>
            </td>
        </tr>

        <?php if (!empty($bid['EmdValue']) && (float)$bid['EmdValue'] > 0): ?>
        <tr>
            <td class="label">EMD Value</td>
            <td class="value">
                &#8377;
                <?= number_format((float)$bid['EmdValue'], 2); ?>
            </td>
        </tr>
        <?php endif; ?>

        <tr>
            <td class="label">Published Date</td>
            <td class="value">
                <?= (!empty($bid['PublishedDate']) && strtotime($bid['PublishedDate']))
                    ? date('d-m-Y', strtotime($bid['PublishedDate']))
                    : '-'; ?>
            </td>
        </tr>

        <tr>
            <td class="label">Submission / Close Date</td>
            <td class="value">
                <?= (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate']))
                    ? date('d-m-Y', strtotime($bid['SubmissionDate']))
                    : '-'; ?>
            </td>
        </tr>

        <tr>
            <td class="label">Opening Date</td>
            <td class="value">
                <?= (!empty($bid['OpeningDate']) && strtotime($bid['OpeningDate']))
                    ? date('d-m-Y', strtotime($bid['OpeningDate']))
                    : '-'; ?>
            </td>
        </tr>

        <?php if (!empty($bid['TenderPortalUrl'])): ?>
        <tr>
            <td class="label">Tender Portal</td>
            <td class="value">
                <a
                    href="<?= htmlspecialchars($bid['TenderPortalUrl']); ?>"
                    target="_blank"
                    class="portal-link"
                >
                    <?= htmlspecialchars($bid['TenderPortalUrl']); ?>
                </a>
            </td>
        </tr>
        <?php endif; ?>

        <?php if (!empty($bid['Remarks'])): ?>
        <tr>
            <td class="label">Remarks</td>
            <td class="value">
                <?= htmlspecialchars($bid['Remarks']); ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>


    <!-- 2. Required Actions -->
    <div class="section-title">
        2. Required Actions
    </div>

    <ol class="actions">
        <li>Review the tender details provided above.</li>
        <li>Verify the tender requirements and submission timelines.</li>
        <li>Proceed with the necessary bid preparation and coordination activities.</li>
        <li>Ensure that all required documents and information are completed before the submission deadline.</li>
    </ol>


    <!-- 3. Important Timeline -->
    <div class="section-title">
        3. Important Timeline
    </div>

    <div class="timeline-box">

        <div>
            <strong>Published Date:</strong>
            <?= (!empty($bid['PublishedDate']) && strtotime($bid['PublishedDate']))
                ? date('d-m-Y', strtotime($bid['PublishedDate']))
                : '-'; ?>
        </div>

        <div>
            <strong>Submission / Close Date:</strong>
            <?= (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate']))
                ? date('d-m-Y', strtotime($bid['SubmissionDate']))
                : '-'; ?>
        </div>

        <div>
            <strong>Opening Date:</strong>
            <?= (!empty($bid['OpeningDate']) && strtotime($bid['OpeningDate']))
                ? date('d-m-Y', strtotime($bid['OpeningDate']))
                : '-'; ?>
        </div>

    </div>


    <!-- Closing -->
    <p class="closing">
        Please review the above information and take the necessary action
        within the applicable timeline.
    </p>

    <div class="closing">
        Regards,<br>
        <strong>Bid Management Team</strong>
    </div>

</div>

</body>
</html>