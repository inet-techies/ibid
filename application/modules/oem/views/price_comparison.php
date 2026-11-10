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
                                        $hasQuotes = isset($quoteCounts[$pr['PTId']]);
                                        $qCount = $hasQuotes ? $quoteCounts[$pr['PTId']]['quotes'] : 0;
                                        $oCount = $hasQuotes ? $quoteCounts[$pr['PTId']]['oems'] : 0;
                                    ?>
                                        <option value="<?= $pr['PTId']; ?>" <?= ($selectedProjectId == $pr['PTId']) ? 'selected' : ''; ?>>
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
                            <a href="<?= base_url('oem/priceEntries?project='.$project['PTId']); ?>" class="btn btn-sm btn-primary">
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
                                                    <a href="<?= base_url('oem/priceComparison?project_id='.$pwp['PTId']); ?>" class="btn btn-sm btn-outline-primary">
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
                                        <a href="<?= base_url('oem/priceEntries?project='.$project['PTId']); ?>" class="btn btn-primary">
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
                                <a href="<?= base_url('oem/priceEntries?project='.$project['PTId']); ?>" class="btn btn-sm btn-warning text-dark fw-semibold">
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
                                                <?php $pWords = amount_to_indian_words($quote['Price'] ?? '', false); if ($pWords !== ''): ?>
                                                    <small class="d-block text-muted fw-normal" style="font-size:11px;white-space:normal;">(<?= htmlspecialchars($pWords, ENT_QUOTES, 'UTF-8'); ?>)</small>
                                                <?php endif; ?>
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
                                    <a href="<?= base_url('oem/priceComparison?project_id='.$pwp['PTId']); ?>" class="btn btn-sm btn-primary">
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
