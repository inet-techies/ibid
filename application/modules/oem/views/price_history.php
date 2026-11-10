<?php $employee_det = $this->session->userdata('logged_in');
if (empty($employee_det)) { redirect(base_url('bid/index')); }
if (!function_exists('convert_integer_to_indian_words')) {
    function convert_integer_to_indian_words($number) {
        $numStr = ltrim(preg_replace('/[^0-9]/', '', (string)$number), '0');
        if ($numStr === '') return 'Zero';
        $ones = [0=>'',1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five',6=>'Six',7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten',11=>'Eleven',12=>'Twelve',13=>'Thirteen',14=>'Fourteen',15=>'Fifteen',16=>'Sixteen',17=>'Seventeen',18=>'Eighteen',19=>'Nineteen'];
        $tens = [2=>'Twenty',3=>'Thirty',4=>'Forty',5=>'Fifty',6=>'Sixty',7=>'Seventy',8=>'Eighty',9=>'Ninety'];
        $twoDigits = function($n) use ($ones, $tens) {
            $n = (int)$n;
            if ($n === 0) return '';
            if ($n < 20) return $ones[$n];
            $t = (int)floor($n / 10);
            $u = $n % 10;
            return trim($tens[$t] . ($u > 0 ? ' ' . $ones[$u] : ''));
        };
        $parts = [];
        if (strlen($numStr) > 7) {
            $crorePart = ltrim(substr($numStr, 0, strlen($numStr) - 7), '0');
            if ($crorePart !== '') $parts[] = convert_integer_to_indian_words($crorePart) . ' Crore';
            $numStr = substr($numStr, -7);
        }
        $padded = str_pad($numStr, 7, '0', STR_PAD_LEFT);
        $lakh = (int)substr($padded, 0, 2);
        $thousand = (int)substr($padded, 2, 2);
        $hundred = (int)substr($padded, 4, 1);
        $rest = (int)substr($padded, 5, 2);
        if ($lakh > 0) $parts[] = $twoDigits($lakh) . ' Lakh';
        if ($thousand > 0) $parts[] = $twoDigits($thousand) . ' Thousand';
        if ($hundred > 0) $parts[] = $ones[$hundred] . ' Hundred';
        if ($rest > 0) $parts[] = (!empty($parts) ? 'and ' : '') . $twoDigits($rest);
        return trim(implode(' ', $parts));
    }
}
if (!function_exists('amount_to_indian_words')) {
    function amount_to_indian_words($rawValue, $allowZero = false) {
        if ($rawValue === null || $rawValue === '') return '';
        $str = trim((string)$rawValue);
        if ($str === '' || preg_match('/(%|lakh|lac|crore|cr\b|thousand|hundred|only|day|month|year|exempt|free|not\s+specified|na\b)/i', $str)) return '';
        if (!preg_match('/^(?:₹|&#8377;|Rs\.?|INR)?\s*([\d,]+(?:\.\d{1,2})?)\s*(?:\/-)?$/iu', $str, $m)) return '';
        $numClean = str_replace(',', '', $m[1]);
        if (!is_numeric($numClean)) return '';
        $floatVal = (float)$numClean;
        if ($floatVal < 0 || (!$allowZero && $floatVal == 0.0)) return '';
        list($rupeesStr, $paiseStr) = explode('.', number_format($floatVal, 2, '.', ''));
        $rupeeWords = convert_integer_to_indian_words($rupeesStr);
        $paiseInt = (int)$paiseStr;
        if ($paiseInt > 0) return trim($rupeeWords . ' and ' . convert_integer_to_indian_words($paiseInt) . ' Paise Only');
        return trim($rupeeWords . ' Only');
    }
}
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
                                    <option value="<?= $pr['PTId']; ?>" <?= ($filter_inps['project'] ?? '') == $pr['PTId'] ? 'selected' : ''; ?>>
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
                                    <option value="<?= $om['OId']; ?>" <?= ($filter_inps['oem'] ?? '') == $om['OId'] ? 'selected' : ''; ?>>
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
                    <table class="table table-hover align-middle mb-0 text-nowrap wrap-no-text" id="priceHistoryTable">
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
                                            <?php $pWords = amount_to_indian_words($entry['Price'] ?? '', false); if ($pWords !== ''): ?>
                                                <small class="d-block text-muted fw-normal" style="font-size:11px;white-space:normal;">(<?= htmlspecialchars($pWords, ENT_QUOTES, 'UTF-8'); ?>)</small>
                                            <?php endif; ?>
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
            order: [[0, 'asc']],
            language: {
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found'
            }
        });
    }
});
</script>
