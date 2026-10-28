<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Bid Created</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 30px 0;
        }
        .main-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 28px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .body-content {
            padding: 30px;
        }
        .greeting {
            font-size: 15px;
            line-height: 22px;
            margin-bottom: 20px;
            color: #1e293b;
        }
        .badge-alert {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #065f46;
            font-weight: 500;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 13px;
        }
        .details-table th,
        .details-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            text-align: left;
        }
        .details-table th {
            width: 35%;
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        .details-table td {
            color: #0f172a;
        }
        .highlight-val {
            font-weight: 600;
            color: #2563eb;
        }
        .cta-container {
            text-align: center;
            margin: 30px 0 15px;
        }
        .cta-btn {
            background: #2563eb;
            color: #ffffff !important;
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
            line-height: 18px;
            text-align: center;
        }
        .footer p {
            margin: 4px 0;
        }
        .footer-logo {
            margin-top: 12px;
            max-height: 38px;
        }
    </style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="main-container">
                
                <!-- Header -->
                <div class="header">
                    <h1>I-NET Bid Management</h1>
                    <p>New Bid Created Notification</p>
                </div>

                <!-- Body -->
                <div class="body-content">
                    <p class="greeting">
                        Dear <strong>Prasanna Kumari</strong> &amp; Team,
                    </p>

                    <div class="badge-alert">
                        &#10003; A new tender/bid has been successfully created and recorded in the system.
                    </div>

                    <table class="details-table" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <th>Bid ID</th>
                                <td><strong>#<?= htmlspecialchars($Bcid ?? '-'); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Bid Title</th>
                                <td><strong style="color:#1e3a8a;"><?= htmlspecialchars($bid['BidTitle'] ?? '-'); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Tender ID</th>
                                <td><?= !empty($bid['TenderId']) ? htmlspecialchars($bid['TenderId']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Department / Authority</th>
                                <td><?= !empty($bid['BidDepartment']) ? htmlspecialchars($bid['BidDepartment']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Bid Type</th>
                                <td><?= !empty($bid['BidType']) ? htmlspecialchars($bid['BidType']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Estimated Value</th>
                                <td class="highlight-val">&#8377; <?= !empty($bid['EstimatedValue']) ? number_format((float)$bid['EstimatedValue'], 2) : '0.00'; ?></td>
                            </tr>
                            <tr>
                                <th>EMD Value</th>
                                <td>&#8377; <?= !empty($bid['EmdValue']) ? number_format((float)$bid['EmdValue'], 2) : '0.00'; ?></td>
                            </tr>
                            <tr>
                                <th>Published Date</th>
                                <td><?= (!empty($bid['PublishedDate']) && strtotime($bid['PublishedDate'])) ? date('d M Y', strtotime($bid['PublishedDate'])) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Submission Date</th>
                                <td><strong style="color:#b91c1c;"><?= (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate'])) ? date('d M Y', strtotime($bid['SubmissionDate'])) : '-'; ?></strong></td>
                            </tr>
                            <tr>
                                <th>Opening Date</th>
                                <td><?= (!empty($bid['OpeningDate']) && strtotime($bid['OpeningDate'])) ? date('d M Y', strtotime($bid['OpeningDate'])) : '-'; ?></td>
                            </tr>
                            <?php if (!empty($bid['TenderPortalUrl'])): ?>
                            <tr>
                                <th>Tender Portal</th>
                                <td><a href="<?= htmlspecialchars($bid['TenderPortalUrl']); ?>" target="_blank" style="color:#2563eb;word-break:break-all;"><?= htmlspecialchars($bid['TenderPortalUrl']); ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Created By</th>
                                <td><?= htmlspecialchars($createdBy ?? 'Admin'); ?></td>
                            </tr>
                            <tr>
                                <th>Created On</th>
                                <td><?= htmlspecialchars($createdOn ?? date('d M Y, h:i A')); ?></td>
                            </tr>
                            <?php if (!empty($bid['Remarks'])): ?>
                            <tr>
                                <th>Remarks</th>
                                <td><?= nl2br(htmlspecialchars($bid['Remarks'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="cta-container">
                        <a href="<?= !empty($portalUrl) ? $portalUrl : (function_exists('base_url') ? base_url('bid/CreateBids') : '#'); ?>" class="cta-btn" target="_blank">
                            View Bid in Portal &rarr;
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p>This is an automated notification from the <strong>I-NET Bid Management System</strong>.</p>
                    <p>If you have questions, please reach us at <a href="mailto:info@inetcsc.com" style="color:#2563eb;">info@inetcsc.com</a>.</p>
                    <img src="cid:I-NET" alt="I-NET Logo" class="footer-logo">
                </div>

            </div>
        </td>
    </tr>
</table>

</body>
</html>
