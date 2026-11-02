<?php
/**
 * Bid Tracking Dashboard View
 * 100% Dynamic, Data-Driven Architecture
 * 
 * Strict Compliance:
 * - ZERO hard-coded State names
 * - ZERO hard-coded Category names
 * - Single source of truth: $allBids from BidCreationDetails
 * - Dynamic cascading filter bar with active filter pills
 * - Dynamic State chart & Category chart generated from active dataset
 */
?>

<!-- Dashboard Top Header Row -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: 0.5px; font-size: 18px;">BID TRACKING DASHBOARD</h4>
        <span class="badge bg-soft-primary text-primary fs-11 fw-semibold" id="dash-total-badge"><?= count($allBids); ?> Total Bids</span>
    </div>
</div>

<!-- =================================================================== -->
<!-- UNIFIED DYNAMIC FILTER PANEL                                        -->
<!-- =================================================================== -->
<div class="card mb-3 border shadow-sm filter-control-panel">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            
            <!-- Category Filter Dropdown -->
            <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                <label for="filter-category" class="form-label fs-11 fw-bold text-muted mb-1 text-uppercase">
                    <i class="feather-tag me-1 text-primary"></i> Category
                </label>
                <select id="filter-category" class="form-select form-select-sm filter-select">
                    <option value="ALL">All Categories</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $catItem): ?>
                            <option value="<?= htmlspecialchars($catItem, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($catItem, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- State Filter Dropdown -->
            <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                <label for="filter-state" class="form-label fs-11 fw-bold text-muted mb-1 text-uppercase">
                    <i class="feather-map-pin me-1 text-info"></i> State
                </label>
                <select id="filter-state" class="form-select form-select-sm filter-select">
                    <option value="ALL">All States</option>
                    <?php if (!empty($states)): ?>
                        <?php foreach ($states as $stateItem): ?>
                            <option value="<?= htmlspecialchars($stateItem, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($stateItem, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Date Range Picker -->
            <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                <label for="filter-date-range" class="form-label fs-11 fw-bold text-muted mb-1 text-uppercase">
                    <i class="feather-calendar me-1 text-success"></i> Date Range
                </label>
                <div id="filter-date-range" class="reportrange-picker d-flex align-items-center form-control form-control-sm c-pointer">
                    <span class="reportrange-picker-field fs-11 text-truncate">All Dates</span>
                </div>
            </div>

            <!-- Clear Filters Button -->
            <div class="col-xl-3 col-md-12 col-sm-6 col-12 text-xl-end mt-xl-4 pt-xl-1">
                <button type="button" id="btn-clear-filters" class="btn btn-sm btn-outline-danger px-3 py-1 d-inline-flex align-items-center gap-1" style="font-size: 12px;" title="Reset all filters">
                    <i class="feather-rotate-ccw fs-11"></i> Clear Filters
                </button>
            </div>

        </div>

        <!-- Active Filters Display Bar (Populated dynamically) -->
        <div id="active-filter-strip" class="d-none align-items-center flex-wrap gap-2 pt-2 mt-2 border-top">
            <span class="fs-11 fw-bold text-muted"><i class="feather-filter me-1"></i>Active:</span>
            <div id="active-filter-badges" class="d-flex align-items-center flex-wrap gap-1"></div>
        </div>

    </div>
</div>

<!-- =================================================================== -->
<!-- MAIN DASHBOARD CONTENT AREA                                         -->
<!-- =================================================================== -->
<div id="dashboard-main-view">

    <div class="row g-3 mb-3">
        <!-- ============================================================== -->
        <!-- LEFT COLUMN: DYNAMIC CATEGORY CHART & DYNAMIC STATE CHART      -->
        <!-- ============================================================== -->
        <div class="col-xxl-4 col-xl-4 col-lg-5 col-12">
            
            <!-- 1. Bids by Category Chart (100% Dynamic from filteredBids) -->
            <div class="card mb-3 border shadow-none" id="card-bids-by-category">
                <div class="card-header py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bids by Category</h6>
                    <span class="badge bg-soft-primary text-primary fs-10" id="badge-category-count">0 Categories</span>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-category" style="min-height: 230px; width: 100%;"></div>
                    <div class="text-center mt-1">
                        <small class="text-muted fs-11"><i class="feather-info me-1"></i>Click on any category bar to filter</small>
                    </div>
                </div>
            </div>

            <!-- 2. Bids by States/ UT's Chart (100% Dynamic from filteredBids) -->
            <div class="card mb-3 border shadow-none state-chart-card" id="card-bids-by-state">
                <div class="card-header py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bids by States/ UT's</h6>
                    <span class="badge bg-soft-info text-info fs-10" id="badge-state-count">0 States</span>
                </div>
                <div class="card-body p-2">
                    <div id="chart-overview-state" style="min-height: 380px; width: 100%;"></div>
                    <div class="text-center mt-1">
                        <small class="text-muted fs-11"><i class="feather-info me-1"></i>Click on any state bar to filter</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- RIGHT COLUMN: METRICS, CHARTS & ACTIVITY                       -->
        <!-- ============================================================== -->
        <div class="col-xxl-8 col-xl-8 col-lg-7 col-12">
            
            <!-- Top KPI Metric Cards (6 cards in a clean row) -->
            <div class="row g-2 mb-3" id="overview-kpi-row">
                <!-- 1. Total Bids -->
                <div class="col-xl-4 col-md-4 col-sm-6 col-12">
                    <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                        <div class="card-body p-2 d-flex align-items-center gap-3">
                            <div class="kpi-icon-square bg-soft-primary text-primary" style="background: #eff6ff; color: #2563eb;">
                                <i class="feather-file-text"></i>
                            </div>
                            <div>
                                <div class="kpi-label text-nowrap">Total Bids</div>
                                <div class="kpi-num" id="kpi-ov-total"><?= count($allBids); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 2. Participated -->
                <div class="col-xl-4 col-md-4 col-sm-6 col-12">
                    <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                        <div class="card-body p-2 d-flex align-items-center gap-3">
                            <div class="kpi-icon-square bg-soft-success text-success" style="background: #f0fdf4; color: #16a34a;">
                                <i class="feather-users"></i>
                            </div>
                            <div>
                                <div class="kpi-label text-nowrap">Participated</div>
                                <div class="kpi-num" id="kpi-ov-part">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 3. Under Participation -->
                <div class="col-xl-4 col-md-4 col-sm-6 col-12">
                    <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                        <div class="card-body p-2 d-flex align-items-center gap-3">
                            <div class="kpi-icon-square bg-soft-danger text-danger" style="background: #fff7ed; color: #ea580c;">
                                <i class="feather-clock"></i>
                            </div>
                            <div>
                                <div class="kpi-label text-nowrap">Under Participation</div>
                                <div class="kpi-num" id="kpi-ov-under-part">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 4. Under Discussion -->
                <div class="col-xl-4 col-md-4 col-sm-6 col-12">
                    <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                        <div class="card-body p-2 d-flex align-items-center gap-3">
                            <div class="kpi-icon-square bg-soft-secondary text-purple" style="color: #8b5cf6; background: #f5f3ff;">
                                <i class="feather-message-square"></i>
                            </div>
                            <div>
                                <div class="kpi-label text-nowrap">Under Discussion</div>
                                <div class="kpi-num" id="kpi-ov-under-disc">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 5. Awarded -->
                <div class="col-xl-4 col-md-4 col-sm-6 col-12">
                    <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                        <div class="card-body p-2 d-flex align-items-center gap-3">
                            <div class="kpi-icon-square bg-soft-success text-success" style="background: #ecfdf5; color: #10b981;">
                                <i class="feather-award"></i>
                            </div>
                            <div>
                                <div class="kpi-label text-nowrap">Awarded</div>
                                <div class="kpi-num" id="kpi-ov-awarded">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 6. Bid Types -->
                <div class="col-xl-4 col-md-4 col-sm-6 col-12">
                    <div class="card border mb-0 h-100 shadow-none kpi-card-box">
                        <div class="card-body p-2 d-flex align-items-center gap-3">
                            <div class="kpi-icon-square bg-soft-dark text-dark" style="background: #f1f5f9; color: #475569;">
                                <i class="feather-layers"></i>
                            </div>
                            <div>
                                <div class="kpi-label text-nowrap">Bid Types</div>
                                <div class="kpi-num" id="kpi-ov-types">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1: [ Bid Status ] [ Participated Bids by Value ] -->
            <div class="row g-2 mb-3">
                <!-- 1. Bid Status Donut & Table -->
                <div class="col-md-6 col-12">
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
                                        <table class="table table-sm table-borderless align-middle mb-0 fs-11 text-nowrap wrap-no-text" id="table-overview-status-breakdown">
                                            <thead>
                                                <tr class="text-muted border-bottom" style="font-size: 10px;">
                                                    <th class="ps-1 py-0">Status</th>
                                                    <th class="text-end pe-2 py-0">Count</th>
                                                </tr>
                                            </thead>
                                            <tbody id="overview-status-tbody">
                                                <!-- Populated dynamically from filteredBids -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Participated Bids by Value (Combo Bar + Line) -->
                <div class="col-md-6 col-12">
                    <div class="card border mb-0 shadow-none h-100">
                        <div class="card-header py-2 px-3 border-bottom">
                            <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Participated Bids by Value</h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="chart-overview-value" style="min-height: 135px;"></div>
                            <div class="table-responsive mt-1">
                                <table class="table table-sm table-bordered align-middle text-center mb-0 fs-10 text-nowrap wrap-no-text" id="table-overview-value-activity">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th>Month</th>
                                            <th>Total Bids</th>
                                            <th>Total Value (₹)</th>
                                            <th>EMD Value (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="overview-value-tbody">
                                        <!-- Populated dynamically from filteredBids -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2: [ Bids by Participated ] [ Department Activity ] -->
            <div class="row g-2 mb-3">
                <!-- 3. Bids by Participated (Status Workflow Breakdown) -->
                <div class="col-md-6 col-12">
                    <div class="card border mb-0 shadow-none h-100">
                        <div class="card-header py-2 px-3 border-bottom">
                            <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bids by Participated</h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="chart-overview-funnel" style="min-height: 200px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 4. Department Activity -->
                <div class="col-md-6 col-12">
                    <div class="card border mb-0 shadow-none h-100">
                        <div class="card-header py-2 px-3 border-bottom">
                            <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Department Activity</h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="chart-overview-department" style="min-height: 200px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bid Registry Table (Bottom Full-Width Panel) -->
    <div class="row g-2 mb-3">
        <div class="col-12">
            <div class="card border mb-0 shadow-none">
                <div class="card-header py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="card-title mb-0 fs-13 fw-bold text-dark">Bid Registry</h6>
                        <span class="badge bg-soft-primary text-primary" id="registry-count-badge"><?= count($allBids); ?> Bids</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="overview-registry-search" class="form-control form-control-sm" placeholder="Search registry..." style="width: 220px; font-size: 11px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0 fs-11 text-nowrap wrap-no-text" id="table-overview-registry">
                            <thead class="bg-light text-muted border-bottom" style="font-size: 11px;">
                                <tr>
                                    <th style="width: 40px;" class="ps-3">#</th>
                                    <th>Bid Title</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>State / UT</th>
                                    <th>Department</th>
                                    <th>Estimated Value (₹)</th>
                                    <th>EMD Value (₹)</th>
                                    <th>Submission Date</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="overview-registry-tbody">
                                <!-- Populated dynamically from filteredBids -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- JSON Payload for External JavaScript (Pure Data Island from Database, Zero Hardcoded Values) -->
<script type="application/json" id="bid-dashboard-payload">
<?= json_encode([
    'allBids'    => $allBids,
    'statuses'   => $statuses,
    'categories' => $categories,
    'states'     => $states,
    'startDate'  => $startDate,
    'endDate'    => $endDate,
    'baseUrl'    => base_url()
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
</script>