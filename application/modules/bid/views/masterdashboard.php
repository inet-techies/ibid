<?php
/**
 * Bid Tracking Dashboard View
 * Exact Layout, Density, Colors & Structure matching Reference Image
 * Unified Dashboard (No Toggles, No Percentages)
 */
?>
<!-- Top Controls Row inside Dashboard Content -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: 0.5px; font-size: 18px;">BID TRACKING DASHBOARD</h4>
    </div>
</div>

<!-- =================================================================== -->
<!-- UNIFIED BID TRACKING DASHBOARD (Exact Reference Image Layout)       -->
<!-- =================================================================== -->
<div id="dashboard-main-view">

    <!-- Top 7 KPI Metric Cards Row -->
    <div class="row g-2 mb-3" id="overview-kpi-row">
        <!-- 1. Total Bids -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-primary text-primary" style="background: #eff6ff; color: #2563eb;">
                        <i class="feather-file-text"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Total Bids</div>
                        <div class="kpi-num" id="kpi-ov-total"><?= $overview['kpis']['totalBids']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 2. Participated -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-success text-success" style="background: #f0fdf4; color: #16a34a;">
                        <i class="feather-users"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Participated</div>
                        <div class="kpi-num" id="kpi-ov-part"><?= $overview['kpis']['participated']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 3. Under Participation -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-danger text-danger" style="background: #fff7ed; color: #ea580c;">
                        <i class="feather-clock"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Under Participation</div>
                        <div class="kpi-num" id="kpi-ov-under-part"><?= $overview['kpis']['underParticipation']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 4. Under Discussion -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-secondary text-purple" style="color: #8b5cf6; background: #f5f3ff;">
                        <i class="feather-message-square"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Under Discussion</div>
                        <div class="kpi-num" id="kpi-ov-under-disc"><?= $overview['kpis']['underDiscussion']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 5. Awarded -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-success text-success" style="background: #ecfdf5; color: #10b981;">
                        <i class="feather-award"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Awarded</div>
                        <div class="kpi-num" id="kpi-ov-awarded"><?= $overview['kpis']['awarded']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 6. Bid Types -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-dark text-dark" style="background: #f1f5f9; color: #475569;">
                        <i class="feather-layers"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Bid Types</div>
                        <div class="kpi-num" id="kpi-ov-types"><?= $overview['kpis']['bidTypes']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 7. Departments -->
        <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 kpi-col">
            <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                <div class="card-body p-2 d-flex align-items-center gap-3">
                    <div class="kpi-icon-square bg-soft-primary text-primary" style="background: #eff6ff; color: #2563eb;">
                        <i class="feather-briefcase"></i>
                    </div>
                    <div>
                        <div class="kpi-label text-nowrap">Departments</div>
                        <div class="kpi-num" id="kpi-ov-depts"><?= $overview['kpis']['departments']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Row 1: [ Bid Status ] [ Bid Value & Activity ] [ Bid Type / Category ] -->
    <div class="row g-2 mb-3">
        <!-- 1. Bid Status Donut & Table (Compact) -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-12">
            <div class="card border mb-0 shadow-none h-100">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bid Status</h6>
                </div>
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="row g-0 align-items-center w-100">
                        <div class="col-5">
                            <div id="chart-overview-status" style="min-height: 160px;"></div>
                        </div>
                        <div class="col-7 ps-1">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0 fs-11" id="table-overview-status-breakdown">
                                    <thead>
                                        <tr class="text-muted border-bottom" style="font-size: 10px;">
                                            <th class="ps-1 py-0">Status</th>
                                            <th class="text-end pe-2 py-0">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody id="overview-status-tbody">
                                        <?php foreach($overview['statusWorkflowTable'] as $st): ?>
                                        <tr>
                                            <td class="ps-1 py-1 text-nowrap" style="line-height: 1.2;">
                                                <span class="status-dot-bullet" style="background-color: <?= $st['color']; ?>; width: 7px; height: 7px;"></span>
                                                <span class="text-dark fw-medium" style="font-size: 11px;"><?= $st['name']; ?></span>
                                            </td>
                                            <td class="text-end pe-2 py-1 fw-bold" style="font-size: 11px;"><?= $st['count']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Participated Bids by Value (Combo Bar + Line) -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-12">
            <div class="card border mb-0 shadow-none h-100">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Participated Bids by Value</h6>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-value" style="min-height: 135px;"></div>
                    <div class="table-responsive mt-1">
                        <table class="table table-sm table-bordered align-middle text-center mb-0 fs-10" id="table-overview-value-activity">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>Month</th>
                                    <th>Total Bids</th>
                                    <th>Total Value (₹)</th>
                                    <th>EMD Value (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="overview-value-tbody">
                                <?php if(!empty($overview['monthlyTable'])): ?>
                                    <?php foreach(array_slice($overview['monthlyTable'], -3) as $m): ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?= $m['month']; ?></td>
                                        <td><?= $m['totalBids']; ?></td>
                                        <td class="text-dark"><?= $m['totalValueFmt']; ?></td>
                                        <td class="text-muted"><?= $m['emdValueFmt']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-muted">No monthly records</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Bids by Category (Horizontal Bar Chart) -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-12">
            <div class="card border mb-0 shadow-none h-100">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bids by Category</h6>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-type" style="min-height: 160px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Row 2: [ Bids by Participated ] [ Department Activity ] [ Bids by Month ] -->
    <div class="row g-2 mb-3">
        <!-- 4. Bids by Participated (Status Workflow Breakdown) -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-12">
            <div class="card border mb-0 shadow-none h-100">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bids by Participated</h6>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-funnel" style="min-height: 200px;"></div>
                </div>
            </div>
        </div>

        <!-- 5. Department Activity Horizontal Bar Chart -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-12">
            <div class="card border mb-0 shadow-none h-100">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Department Activity</h6>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-department" style="min-height: 200px;"></div>
                </div>
            </div>
        </div>

        <!-- 6. Bids by Month (Trend Line Chart) -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-12">
            <div class="card border mb-0 shadow-none h-100">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bids by Month</h6>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-monthly" style="min-height: 200px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bid Registry Table (Bottom Full-Width Panel) -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <div class="card border mb-0 shadow-none">
                <div class="card-header py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bid Registry</h6>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="overview-registry-search" class="form-control form-control-sm" placeholder="Search registry..." style="width: 200px; font-size: 11px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0 fs-11" id="table-overview-registry">
                            <thead class="bg-light text-muted border-bottom" style="font-size: 11px;">
                                <tr>
                                    <th style="width: 40px;" class="ps-3">#</th>
                                    <th>Bid Title</th>
                                    <th>Type</th>
                                    <th>Department</th>
                                    <th>Estimated Value (₹)</th>
                                    <th>EMD Value (₹)</th>
                                    <th>Submission Date</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="overview-registry-tbody">
                                <?php if(!empty($allBids)): ?>
                                    <?php $idx = 1; foreach($allBids as $bid): ?>
                                    <tr>
                                        <td class="ps-3 text-muted"><?= $idx++; ?></td>
                                        <td class="fw-semibold text-dark"><?= $bid['BidTitle']; ?></td>
                                        <td>
                                            <?php if(strtolower($bid['BidType']) == 'oem'): ?>
                                                <span class="badge bg-soft-info text-info">OEM</span>
                                            <?php elseif(strtolower($bid['BidType']) == 'service'): ?>
                                                <span class="badge bg-soft-primary text-primary">Service</span>
                                            <?php else: ?>
                                                <span class="badge bg-soft-secondary text-secondary"><?= $bid['BidType']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $bid['BidDepartment'] ?? '—'; ?></td>
                                        <td class="fw-medium text-dark"><?= !empty($bid['EstimatedValue']) ? number_format((float)$bid['EstimatedValue']) : '—'; ?></td>
                                        <td class="text-muted"><?= !empty($bid['EmdValue']) ? number_format((float)$bid['EmdValue']) : '—'; ?></td>
                                        <td class="text-muted"><?= !empty($bid['SubmissionDate']) ? date('d M Y', strtotime($bid['SubmissionDate'])) : '—'; ?></td>
                                        <td>
                                            <span class="badge <?= $bid['BadgeClass'] ?? 'bg-secondary'; ?>">
                                                <?= $bid['StatusName']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('bid/BidDetails'); ?>" class="btn btn-xs btn-outline-primary py-0 px-2" title="View">
                                                <i class="feather-eye me-1 fs-10"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No bids found
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

</div>

<!-- JSON Payload for External JavaScript (Pure Data Island, No inline scripts) -->
<script type="application/json" id="bid-dashboard-payload">
<?= json_encode([
    'overview'  => $overview,
    'allBids'   => $allBids,
    'statuses'  => $statuses,
    'startDate' => $startDate,
    'endDate'   => $endDate,
    'baseUrl'   => base_url()
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
</script>