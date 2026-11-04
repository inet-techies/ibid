
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bid Opening Reminder</title>
</head>

<body style="margin:0;padding:20px;background-color:#ffffff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;font-size:14px;line-height:1.6;">

<?php
$bidTitle = htmlspecialchars($bid['BidTitle'] ?? 'Tender', ENT_QUOTES, 'UTF-8');
$tenderId = htmlspecialchars($bid['TenderId'] ?? '-', ENT_QUOTES, 'UTF-8');
$department = htmlspecialchars($bid['BidDepartment'] ?? '-', ENT_QUOTES, 'UTF-8');
$status = htmlspecialchars($currentStatus ?? ($bid['CurrentStatus'] ?? 'Active'), ENT_QUOTES, 'UTF-8');
$sender = htmlspecialchars($senderName ?? 'Bid Management Team', ENT_QUOTES, 'UTF-8');

$openingDate = (!empty($bid['OpeningDate']) && strtotime($bid['OpeningDate']))
    ? date('d-M-Y', strtotime($bid['OpeningDate']))
    : '-';

$submissionDate = (!empty($bid['SubmissionDate']) && strtotime($bid['SubmissionDate']))
    ? date('d-M-Y', strtotime($bid['SubmissionDate']))
    : '-';

$estimatedValue = !empty($bid['EstimatedValue'])
    ? number_format((float)$bid['EstimatedValue'], 2)
    : '0.00';
?>

<!-- Main Email Container -->
<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="max-width:700px;margin:0 auto;border-collapse:collapse;">

    <!-- Header -->
    <tr>
        <td style="padding:0 0 18px 0;border-bottom:2px solid #2563eb;">

            <div style="font-size:20px;font-weight:700;color:#1e3a8a;">
                Bid Management Portal
            </div>

            <div style="font-size:12px;color:#64748b;margin-top:3px;">
                Bid Opening Reminder
            </div>

        </td>
    </tr>

    <!-- Introduction -->
    <tr>
        <td style="padding:20px 0 8px 0;">

            <p style="margin:0 0 14px 0;font-size:15px;font-weight:600;color:#111827;">
                Dear Team,
            </p>

            <p style="margin:0 0 14px 0;color:#374151;">
                This is a reminder regarding the scheduled opening of the
                bid <strong><?php echo $bidTitle; ?></strong> in the
                <strong>Bid Management Portal</strong>.
            </p>

            <p style="margin:0;color:#374151;">
                Please review the tender information and ensure that the
                concerned team is prepared for the scheduled bid opening.
            </p>

        </td>
    </tr>

    <!-- 1. Tender Information -->
    <tr>
        <td style="padding:14px 0 8px 0;">

            <div style="font-size:15px;font-weight:700;color:#1e3a8a;margin-bottom:10px;">
                1. Tender Information
            </div>

            <table width="100%" cellpadding="9" cellspacing="0" border="0"
                   style="border-collapse:collapse;background-color:#f8fafc;border:1px solid #e2e8f0;font-size:13px;">

                <tr>
                    <td width="35%" style="font-weight:600;color:#334155;border-bottom:1px solid #e2e8f0;">
                        Bid Title
                    </td>
                    <td style="color:#1e293b;border-bottom:1px solid #e2e8f0;">
                        <?php echo $bidTitle; ?>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:600;color:#334155;border-bottom:1px solid #e2e8f0;">
                        Tender ID
                    </td>
                    <td style="color:#1e293b;border-bottom:1px solid #e2e8f0;">
                        <?php echo $tenderId; ?>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:600;color:#334155;border-bottom:1px solid #e2e8f0;">
                        Bid Department
                    </td>
                    <td style="color:#1e293b;border-bottom:1px solid #e2e8f0;">
                        <?php echo $department; ?>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:600;color:#334155;border-bottom:1px solid #e2e8f0;">
                        Current Status
                    </td>
                    <td style="color:#1e293b;border-bottom:1px solid #e2e8f0;">
                        <?php echo $status; ?>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight:600;color:#334155;">
                        Estimated Value
                    </td>
                    <td style="color:#1e293b;">
                        &#8377; <?php echo $estimatedValue; ?>
                    </td>
                </tr>

                <?php if (!empty($bid['TenderPortalUrl'])): ?>
                <tr>
                    <td style="font-weight:600;color:#334155;border-top:1px solid #e2e8f0;">
                        Tender Portal
                    </td>
                    <td style="border-top:1px solid #e2e8f0;">
                        <a href="<?php echo htmlspecialchars($bid['TenderPortalUrl'], ENT_QUOTES, 'UTF-8'); ?>"
                           target="_blank"
                           style="color:#2563eb;text-decoration:none;word-break:break-all;">
                            View Tender Portal
                        </a>
                    </td>
                </tr>
                <?php endif; ?>

            </table>

        </td>
    </tr>

    <!-- 2. Required Actions -->
    <tr>
        <td style="padding:16px 0 8px 0;">

            <div style="font-size:15px;font-weight:700;color:#1e3a8a;margin-bottom:8px;">
                2. Required Actions
            </div>

            <ol style="margin:0;padding-left:22px;color:#374151;font-size:13.5px;line-height:1.8;">
                <li>
                    Review the tender details and scheduled opening date.
                </li>
                <li>
                    Ensure the concerned team is informed about the bid opening.
                </li>
                <li>
                    Complete the necessary preparations before the scheduled opening.
                </li>
            </ol>

        </td>
    </tr>

    <!-- 3. Important Timeline -->
    <tr>
        <td style="padding:16px 0 18px 0;">

            <div style="font-size:15px;font-weight:700;color:#1e3a8a;margin-bottom:10px;">
                3. Important Timeline
            </div>

            <table width="100%" cellpadding="12" cellspacing="0" border="0"
                   style="border-collapse:collapse;background-color:#eff6ff;border-left:4px solid #2563eb;">

                <tr>
                    <td style="font-size:13px;color:#334155;">
                        <strong>Opening Date</strong>
                        <div style="font-size:16px;font-weight:700;color:#b91c1c;margin-top:3px;">
                            <?php echo $openingDate; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="border-top:1px solid #dbeafe;font-size:13px;color:#334155;">
                        <strong>Submission / Closing Date</strong>
                        <div style="font-size:14px;font-weight:600;color:#1e3a8a;margin-top:3px;">
                            <?php echo $submissionDate; ?>
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>

    <!-- Closing -->
    <tr>
        <td style="padding:16px 0;border-top:1px solid #e2e8f0;">

            <p style="margin:0 0 14px 0;color:#374151;font-size:13.5px;">
                Please take note of the scheduled bid opening and ensure
                that the necessary actions are completed accordingly.
            </p>

            <p style="margin:0;color:#374151;">
                Regards,<br>
                <strong style="color:#1e3a8a;">
                    Bid Management Team
                </strong>
            </p>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="padding:12px 0;border-top:1px solid #e2e8f0;text-align:center;">

            <div style="font-size:11px;color:#94a3b8;">
                This is an automated notification from the Bid Management Portal.
            </div>

        </td>
    </tr>

</table>

</body>
</html>
