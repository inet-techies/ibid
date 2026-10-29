<?php $employee_det = $this->session->userdata('logged_in');
if (empty($employee_det)) { redirect(base_url('bid/index')); }
?>

<div class="row">
    <div class="col-lg-12">
        <!-- Main Comparison Card -->
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h5 class="fw-bold mb-0">
                    <i class="feather-bar-chart-2 text-primary me-2"></i>OEM Price Comparison Matrix
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('oem/priceEntries'); ?>" class="btn btn-outline-secondary btn-md">
                        <i class="feather-dollar-sign me-1"></i> Price Entries
                    </a>
                    <a href="<?= base_url('oem/priceHistory'); ?>" class="btn btn-outline-primary btn-md">
                        <i class="feather-clock me-1"></i> Price History
                    </a>
                </div>
            </div>

            <!-- Project Selector Toolbar -->
            <div class="card-body bg-light border-bottom py-3">
                <form method="get" action="<?= base_url('oem/priceComparison'); ?>" class="row g-2 align-items-center" autocomplete="off">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="feather-briefcase text-muted"></i></span>
                            <select name="project_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Choose Project to Compare OEM Quotations --</option>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $pr): 
                                        $hasQuotes = isset($quoteCounts[$pr['SNo']]);
                                        $qCount = $hasQuotes ? $quoteCounts[$pr['SNo']]['quotes'] : 0;
                                        $oCount = $hasQuotes ? $quoteCounts[$pr['SNo']]['oems'] : 0;
                                    ?>
                                        <option value="<?= $pr['SNo']; ?>" <?= ($selectedProjectId == $pr['SNo']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($pr['ProjectName'] ?? ''); ?>
                                            <?= !empty($pr['TenderNumber']) ? ' ('.htmlspecialchars($pr['TenderNumber']).')' : ''; ?>
                                            <?= $hasQuotes ? ' — ['.$qCount.' quote'.($qCount > 1 ? 's' : '').' from '.$oCount.' OEM'.($oCount > 1 ? 's' : '').']' : ' — [No Quotes Yet]'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="feather-eye me-1"></i> View Comparison
                        </button>
                    </div>
                </form>
            </div>

            <!-- Comparison Content -->
            <?php if (!empty($project)): ?>
                <!-- Project Summary Strip -->
                <div class="card-body border-bottom py-3 bg-white">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">
                                <?= htmlspecialchars($project['ProjectName'] ?? ''); ?>
                            </h6>
                            <div class="fs-12 text-muted">
                                <span>Tender: <strong class="text-primary"><?= htmlspecialchars($project['TenderNumber'] ?? '-'); ?></strong></span>
                                <span class="mx-2">•</span>
                                <span>State: <strong><?= htmlspecialchars($project['State'] ?? '-'); ?></strong></span>
                                <span class="mx-2">•</span>
                                <span>Dept: <strong><?= htmlspecialchars($project['Department'] ?? '-'); ?></strong></span>
                            </div>
                        </div>
                        <div>
                            <a href="<?= base_url('oem/priceEntries?project='.$project['SNo']); ?>" class="btn btn-sm btn-primary">
                                <i class="feather-plus me-1"></i> Add OEM Quotes
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (empty($quotes)): ?>
                    <!-- Clean Warning Alert when Selected Project has No Quotes -->
                    <div class="card-body p-4">
                        <div class="alert alert-warning border-warning shadow-sm rounded-3 p-4 mb-0">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-warning text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                    <i class="feather-alert-triangle fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading fw-bold text-dark mb-1">No OEM Price Quotes Recorded for this Project</h5>
                                    <p class="text-muted mb-2">
                                        No OEM vendor price quotations have been submitted or recorded yet for <strong><?= htmlspecialchars($project['ProjectName'] ?? ''); ?></strong> (Tender: <?= htmlspecialchars($project['TenderNumber'] ?? '-'); ?>).
                                    </p>
                                    
                                    <?php if (!empty($projectsWithPrices)): ?>
                                        <div class="bg-white border rounded-2 p-3 my-3">
                                            <div class="fw-semibold text-dark mb-1">
                                                <i class="feather-info text-primary me-1"></i>
                                                Available Projects with Price Entries:
                                            </div>
                                            <p class="text-muted fs-13 mb-2">
                                                <?= (count($projectsWithPrices) === 1) ? 'Currently, only 1 project has price entries completed:' : 'Price entries have been recorded for the following project(s):'; ?>
                                            </p>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($projectsWithPrices as $pwp): ?>
                                                    <a href="<?= base_url('oem/priceComparison?project_id='.$pwp['SNo']); ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="feather-bar-chart-2 me-1"></i>
                                                        <?= htmlspecialchars($pwp['ProjectName']); ?>
                                                        <span class="badge bg-primary text-white ms-1"><?= $pwp['QuoteCount']; ?> quote<?= ($pwp['QuoteCount'] > 1 ? 's' : ''); ?></span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted fs-13 mb-3">
                                            No price quotes have been recorded across any projects yet. Click below to add the first OEM quote.
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-2 pt-2">
                                        <a href="<?= base_url('oem/priceEntries?project='.$project['SNo']); ?>" class="btn btn-primary">
                                            <i class="feather-plus me-1"></i> Add OEM Quotes for <?= htmlspecialchars($project['ProjectName'] ?? ''); ?>
                                        </a>
                                        <a href="<?= base_url('oem/priceEntries'); ?>" class="btn btn-outline-secondary">
                                            <i class="feather-dollar-sign me-1"></i> View All Price Entries
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <?php 
                        $oemIds = array_unique(array_column($quotes, 'OemId'));
                        $oemCount = count($oemIds);
                    ?>

                    <?php if ($oemCount < 2): ?>
                        <!-- Informational Banner when Only 1 OEM Quote Exists -->
                        <div class="card-body border-bottom bg-warning-subtle py-3 px-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="feather-info text-warning fs-5"></i>
                                    <span class="text-dark fs-13">
                                        <strong>Only 1 OEM Quote Recorded:</strong> Only 1 OEM vendor (<strong><?= htmlspecialchars($quotes[0]['OemName'] ?? 'OEM'); ?></strong>) has submitted quotations for this project. To effectively compare prices, at least two OEM quotations are recommended.
                                    </span>
                                </div>
                                <a href="<?= base_url('oem/priceEntries?project='.$project['SNo']); ?>" class="btn btn-sm btn-warning text-dark fw-semibold">
                                    <i class="feather-plus me-1"></i> Add Another OEM Quote
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Matrix Table Body -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap wrap-no-text" id="comparisonTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Category</th>
                                        <th>Product / Equipment</th>
                                        <th>OEM Vendor</th>
                                        <th style="width: 170px;">Quoted Price (₹)</th>
                                        <th style="width: 90px;">Unit</th>
                                        <th style="width: 170px;">Quoted Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $i = 1;
                                        // Group by product to identify lowest price (L1)
                                        $productPrices = [];
                                        foreach ($quotes as $q) {
                                            $pid = $q['PId'];
                                            if (!isset($productPrices[$pid])) {
                                                $productPrices[$pid] = (float)$q['Price'];
                                            } else {
                                                $productPrices[$pid] = min($productPrices[$pid], (float)$q['Price']);
                                            }
                                        }

                                        foreach ($quotes as $quote): 
                                            $isLowest = ($oemCount > 1 && (float)$quote['Price'] === $productPrices[$quote['PId']]);
                                    ?>
                                        <tr class="single-item <?= $isLowest ? 'table-success-subtle' : ''; ?>">
                                            <td><?= $i++; ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($quote['CategoryList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td class="fw-semibold text-dark">
                                                <?= htmlspecialchars($quote['ProductList'] ?? 'Item', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <i class="feather-briefcase text-muted me-1"></i>
                                                    <?= htmlspecialchars($quote['OemName'] ?? 'OEM', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold fs-14 text-dark">
                                                    ₹ <?= number_format((float)($quote['Price'] ?? 0), 2); ?>
                                                </span>
                                                <?php if ($isLowest): ?>
                                                    <span class="badge bg-success ms-2 fs-11">L1 Lowest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($quote['Unit'] ?? 'Nos'); ?></span></td>
                                            <td>
                                                <span class="text-muted fs-12">
                                                    <?= !empty($quote['CreatedAt']) ? date('d M Y, h:i A', strtotime($quote['CreatedAt'])) : '-'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- No Project Selected State -->
                <div class="card-body text-center py-5">
                    <i class="feather-bar-chart-2 fs-1 text-muted mb-2 d-block"></i>
                    <h6 class="fw-bold text-dark mb-1">No Project Selected</h6>
                    <p class="text-muted fs-13 mb-3">Please choose a project from the dropdown above to view and compare OEM quotations.</p>
                    
                    <?php if (!empty($projectsWithPrices)): ?>
                        <div class="d-inline-flex flex-column align-items-center bg-light border rounded-3 p-3 text-start">
                            <span class="fs-12 text-muted fw-semibold mb-2 text-uppercase">
                                <i class="feather-check-circle text-success me-1"></i>
                                <?= (count($projectsWithPrices) === 1) ? 'Currently, only 1 project has price entries completed:' : 'Projects with price entries ready for comparison:'; ?>
                            </span>
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <?php foreach ($projectsWithPrices as $pwp): ?>
                                    <a href="<?= base_url('oem/priceComparison?project_id='.$pwp['SNo']); ?>" class="btn btn-sm btn-primary">
                                        <i class="feather-eye me-1"></i> <?= htmlspecialchars($pwp['ProjectName']); ?>
                                        <span class="badge bg-white text-primary ms-1"><?= $pwp['QuoteCount']; ?> quote<?= ($pwp['QuoteCount'] > 1 ? 's' : ''); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (window.jQuery && typeof $.fn.DataTable === 'function') {
        // Prevent DataTables default native alert popup
        $.fn.dataTable.ext.errMode = 'none';

        if ($('#comparisonTable tbody tr.single-item').length > 0) {
            $('#comparisonTable').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[2, 'asc'], [4, 'asc']]
            });
        }
    }
});
</script>
