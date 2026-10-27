<?php $employee_det = $this->session->userdata('logged_in');
if (empty($employee_det)) { redirect(base_url('bid/index')); }
?>

<div class="row">
    <div class="col-lg-12">
        <!-- Top Navigation & Action Header -->
        <div class="card mb-4">
            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= base_url('oem/project'); ?>" class="avatar-text avatar-md bg-light text-dark" title="Back to Projects">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">
                            <?= htmlspecialchars($project['ProjectName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 fs-12 text-muted">
                            <span>Tender No: <strong class="text-primary"><?= htmlspecialchars($project['TenderNumber'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></span>
                            <span>•</span>
                            <span>State: <strong><?= htmlspecialchars($project['State'] ?? '-'); ?></strong></span>
                            <span>•</span>
                            <span>Dept: <strong><?= htmlspecialchars($project['Department'] ?? '-'); ?></strong></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('oem/priceComparison?project_id='.$project['SNo']); ?>" class="btn btn-outline-primary btn-md">
                        <i class="feather-bar-chart-2 me-1"></i> Price Comparison
                    </a>
                    <a href="<?= base_url('oem/priceEntries?project='.$project['SNo']); ?>" class="btn btn-primary btn-md">
                        <i class="feather-plus me-1"></i> Add OEM Quotes
                    </a>
                </div>
            </div>
        </div>

        <!-- Structured Project & Tender Overview Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="fw-bold mb-0">
                    <i class="feather-info text-primary me-2"></i>Project & Tender Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Project Info -->
                    <div class="col-md-4 border-end-md">
                        <h6 class="text-uppercase text-muted fw-bold fs-12 mb-3">
                            <i class="feather-briefcase me-1"></i> Project Information
                        </h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted ps-0" style="width: 120px;">State / Region:</td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($project['State'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Department:</td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($project['Department'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Portal Link:</td>
                                <td>
                                    <?php if (!empty($project['PortalLink'])): ?>
                                        <a href="<?= htmlspecialchars($project['PortalLink']); ?>" target="_blank" class="text-primary text-decoration-none">
                                            <i class="feather-external-link me-1"></i> View Portal
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Tender Info -->
                    <div class="col-md-4 border-end-md">
                        <h6 class="text-uppercase text-muted fw-bold fs-12 mb-3">
                            <i class="feather-hash me-1"></i> Tender Details
                        </h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted ps-0" style="width: 130px;">Tender Number:</td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($project['TenderNumber'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Service / Bid OEM:</td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($project['ServiceBidOem'] ?? 'OEM'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Awarded To:</td>
                                <td class="fw-semibold text-dark">
                                    <?= !empty($project['ProjectAwarded']) ? htmlspecialchars($project['ProjectAwarded']) : '<span class="text-muted fw-normal">Under Evaluation</span>'; ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Timeline & Status -->
                    <div class="col-md-4">
                        <h6 class="text-uppercase text-muted fw-bold fs-12 mb-3">
                            <i class="feather-calendar me-1"></i> Timeline & Status
                        </h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted ps-0" style="width: 140px;">Current Status:</td>
                                <td>
                                    <?php 
                                        $st = (int)($project['Status'] ?? 1);
                                        if ($st === 1) echo '<span class="badge bg-success">Active / Ongoing</span>';
                                        elseif ($st === 2) echo '<span class="badge bg-info">Upcoming</span>';
                                        elseif ($st === 3) echo '<span class="badge bg-primary">Completed</span>';
                                        else echo '<span class="badge bg-danger">Cancelled</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Published Date:</td>
                                <td class="text-dark">
                                    <?= !empty($project['RfbPublishedDate']) ? date('d M Y', strtotime($project['RfbPublishedDate'])) : '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Submission Date:</td>
                                <td class="fw-semibold text-dark">
                                    <?= !empty($project['RfbSubmissionDate']) ? date('d M Y', strtotime($project['RfbSubmissionDate'])) : '-'; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill of Materials Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-list text-primary me-2"></i>Required Materials & Equipment (BOM)
                    <span class="badge bg-primary ms-2 fs-11"><?= !empty($materials) ? count($materials) : 0; ?> Items</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Product / Equipment</th>
                                <th>Category</th>
                                <th style="width: 160px;">Required Quantity</th>
                                <th style="width: 120px;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($materials)): ?>
                                <?php $i = 1; foreach ($materials as $mat): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($mat['ProductList'] ?? 'Item', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($mat['MaterialName'])): ?>
                                                <span class="d-block text-muted fs-12"><?= htmlspecialchars($mat['MaterialName']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="feather-tag me-1"></i>
                                                <?= htmlspecialchars($mat['CategoryList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark fs-14"><?= $mat['Quantity']; ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($mat['Unit'] ?? 'Nos'); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="feather-alert-circle me-1"></i> No bill of materials registered for this project.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recorded OEM Price Quotations Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-dollar-sign text-primary me-2"></i>Recorded OEM Price Quotes
                    <span class="badge bg-success ms-2 fs-11"><?= !empty($prices) ? count($prices) : 0; ?> Quotes</span>
                </h5>
                <a href="<?= base_url('oem/priceComparison?project_id='.$project['SNo']); ?>" class="btn btn-sm btn-outline-primary">
                    <i class="feather-eye me-1"></i> Open Comparison Matrix
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Product / Item</th>
                                <th>OEM Vendor</th>
                                <th style="width: 140px;">Quoted Price</th>
                                <th style="width: 100px;">Unit</th>
                                <th>Change Reason / Notes</th>
                                <th style="width: 160px;">Recorded Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($prices)): ?>
                                <?php $i = 1; foreach ($prices as $quote): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($quote['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($quote['CategoryList'])): ?>
                                                <span class="d-block text-muted fs-12"><?= htmlspecialchars($quote['CategoryList']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">
                                                <?= htmlspecialchars($quote['OemName'] ?? 'OEM', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary fs-14">
                                                ₹ <?= number_format((float)($quote['Price'] ?? 0), 2); ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($quote['Unit'] ?? 'Nos'); ?></span></td>
                                        <td class="text-muted fs-13">
                                            <?= !empty($quote['ChangeReason']) ? htmlspecialchars($quote['ChangeReason'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                                        </td>
                                        <td class="text-muted fs-12">
                                            <?= !empty($quote['CreatedAt']) ? date('d M Y, h:i A', strtotime($quote['CreatedAt'])) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="feather-alert-circle me-1"></i> No price quotes recorded for this project yet. Click "Add OEM Quotes" above to record quotes.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
