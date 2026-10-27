<?php $employee_det = $this->session->userdata('logged_in');
if (empty($employee_det)) { redirect(base_url('bid/index')); }
?>

<div class="row">
    <div class="col-lg-12">
        <!-- Compact Filter Toolbar Card -->
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <h5 class="fw-bold mb-0">
                    <i class="feather-filter text-primary me-2"></i>Filter Quotation History
                </h5>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('oem/priceEntries'); ?>" class="btn btn-outline-primary btn-md">
                        <i class="feather-plus me-1"></i> New Price Entry
                    </a>
                    <a href="<?= base_url('oem/priceHistory'); ?>" class="btn btn-light btn-md">
                        <i class="feather-rotate-ccw me-1"></i> Reset Filters
                    </a>
                </div>
            </div>
            <div class="card-body bg-light border-bottom">
                <form method="get" action="<?= base_url('oem/priceHistory'); ?>" class="row g-2 align-items-end" autocomplete="off">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold fs-12 mb-1">Project</label>
                        <select name="project" class="form-select form-select-sm">
                            <option value="">-- All Projects --</option>
                            <?php if (!empty($projects)): ?>
                                <?php foreach ($projects as $pr): ?>
                                    <option value="<?= $pr['SNo']; ?>" <?= ($filter_inps['project'] ?? '') == $pr['SNo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($pr['ProjectName'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold fs-12 mb-1">Product</label>
                        <select name="product" class="form-select form-select-sm">
                            <option value="">-- All Products --</option>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $pd): ?>
                                    <option value="<?= $pd['PId']; ?>" <?= ($filter_inps['product'] ?? '') == $pd['PId'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($pd['ProductList'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold fs-12 mb-1">OEM Vendor</label>
                        <select name="oem" class="form-select form-select-sm">
                            <option value="">-- All OEMs --</option>
                            <?php if (!empty($oems)): ?>
                                <?php foreach ($oems as $om): ?>
                                    <option value="<?= $om['SNo']; ?>" <?= ($filter_inps['oem'] ?? '') == $om['SNo'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($om['OemName'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold fs-12 mb-1">Search Keyword</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="feather-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filter_inps['search'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="feather-filter me-1"></i> Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Directory Table Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-clock text-primary me-2"></i>Audit Trail & Historical Price Quotations
                </h5>
                <span class="badge bg-primary ms-2 fs-11">
                    <?= !empty($priceEntries) ? count($priceEntries) : 0; ?> Entries Recorded
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="priceHistoryTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 22%;">Project & Tender</th>
                                <th style="width: 13%;">Category</th>
                                <th style="width: 18%;">Product / Equipment</th>
                                <th style="width: 15%;">OEM Vendor</th>
                                <th class="text-end" style="width: 14%;">Quoted Price (₹)</th>
                                <th style="width: 8%;">Unit</th>
                                <th style="width: 20%;">Justification / Reason</th>
                                <th style="width: 160px;">Recorded Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($priceEntries)): ?>
                                <?php $i = 1; foreach ($priceEntries as $entry): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <?= htmlspecialchars($entry['ProjectName'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if (!empty($entry['TenderNumber'])): ?>
                                                <span class="badge bg-light text-muted border fs-11 mt-1">
                                                    <?= htmlspecialchars($entry['TenderNumber']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($entry['CategoryList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($entry['ProductList'] ?? 'Item', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">
                                                <?= htmlspecialchars($entry['OemName'] ?? 'OEM', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary fs-14">
                                                ₹ <?= number_format((float)($entry['Price'] ?? 0), 2); ?>
                                            </span>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($entry['Unit'] ?? 'Nos'); ?></span></td>
                                        <td>
                                            <div class="text-muted fs-12 text-wrap" style="max-width: 220px;" title="<?= htmlspecialchars($entry['ChangeReason'] ?? ''); ?>">
                                                <?= !empty($entry['ChangeReason']) ? htmlspecialchars($entry['ChangeReason']) : '-'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-12">
                                                <?= !empty($entry['CreatedAt']) ? date('d M Y, h:i A', strtotime($entry['CreatedAt'])) : '-'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="feather-alert-circle me-1"></i> No price history records matched your filter criteria.
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#priceHistoryTable').length) {
        $('#priceHistoryTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']]
        });
    }
});
</script>
