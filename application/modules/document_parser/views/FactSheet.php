<?php $employee_det = $this->session->userdata('logged_in');
   $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$fs = isset($factsheet) ? $factsheet : [];
$isAjax = !empty($is_ajax);
$theme_path = $this->config->item('theme_locations') . $this->config->item('active_template');

// Robust deserialization helpers: Support JSON first, then legacy pipe-delimited strings
if (!function_exists('parseSectionKV')) {
    function parseSectionKV($raw) {
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;

        $rawStr = trim((string)$raw);
        if ($rawStr === '') return [];

        // 1. Try standard JSON decode
        $decoded = json_decode($rawStr, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // 2. Fallback: Legacy "Label: Value | Label: Value" pipe-delimited parser
        $out = [];
        $segments = preg_split('/(?<!\\\\)\s*\|\s*/', $rawStr);
        foreach ($segments as $seg) {
            $seg = trim($seg);
            if (empty($seg)) continue;
            $colonPos = strpos($seg, ': ');
            if ($colonPos !== false) {
                $label = trim(substr($seg, 0, $colonPos));
                $val   = trim(substr($seg, $colonPos + 2));
                $val   = str_replace('\|', '|', $val);
                $out[$label] = $val;
            } else {
                $out[] = str_replace('\|', '|', $seg);
            }
        }
        return $out;
    }
}

if (!function_exists('parseSectionList')) {
    function parseSectionList($raw) {
        if (empty($raw)) return [];
        if (is_array($raw)) return array_values($raw);

        $rawStr = trim((string)$raw);
        if ($rawStr === '') return [];

        // 1. Try standard JSON decode
        $decoded = json_decode($rawStr, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values($decoded);
        }

        // 2. Fallback: Legacy "1. Item | 2. Item" pipe-delimited parser
        $out = [];
        $segments = preg_split('/(?<!\\\\)\s*\|\s*/', $rawStr);
        foreach ($segments as $seg) {
            $seg = trim($seg);
            if (empty($seg)) continue;
            // Clean leading numbering, bullets, wingdings
            $clean = preg_replace('/^\d+[\.\)]\s*/', '', $seg);
            $clean = preg_replace('/^[\s*•\-·∩é╖\x{F0B7}\x{00B7}\.o\d\)]+/u', '', $clean);
            $clean = trim(str_replace('\|', '|', $clean));
            // Also clean internal wingdings if merged
            $subItems = preg_split('/[\r\n]+|[•·*∩é╖]|\x{F0B7}|\x{00B7}/u', $clean);
            foreach ($subItems as $sub) {
                $sub = trim(preg_replace('/^[\s*•\-·∩é╖\x{F0B7}\x{00B7}\.o\d\)]+/u', '', $sub));
                if (!empty($sub) && strlen($sub) > 2) {
                    $out[] = $sub;
                }
            }
        }
        return $out;
    }
}

// Helper safe display
if (!function_exists('safeVal')) {
    function safeVal($val, $fallback = 'Not specified in document') {
        $trimmed = trim((string)$val);
        return !empty($trimmed) ? htmlspecialchars($trimmed) : '<span class="text-muted" style="font-style:italic;">' . htmlspecialchars($fallback) . '</span>';
    }
}

// ── 1. Parse Section 1: TenderOverview ──
$secOverview = !empty($fs['TenderOverview']) ? parseSectionKV($fs['TenderOverview']) : [];
$dept       = isset($secOverview['Department']) ? $secOverview['Department'] : (!empty($fs['Department']) ? $fs['Department'] : '');
$zone       = isset($secOverview['Zone']) ? $secOverview['Zone'] : (!empty($fs['ZoneRegion']) ? $fs['ZoneRegion'] : '');
$title      = isset($secOverview['Tender Title']) ? $secOverview['Tender Title'] : (!empty($fs['TenderTitle']) ? $fs['TenderTitle'] : '');
$type       = isset($secOverview['Tender Type']) ? $secOverview['Tender Type'] : (!empty($fs['TenderType']) ? $fs['TenderType'] : '');
$refNo      = isset($secOverview['Tender Reference No']) ? $secOverview['Tender Reference No'] : (!empty($fs['TenderRefNo']) ? $fs['TenderRefNo'] : '');
$tDate      = isset($secOverview['Tender Date']) ? $secOverview['Tender Date'] : (!empty($fs['TenderDate']) ? $fs['TenderDate'] : '');
$projVal    = isset($secOverview['Project Value']) ? $secOverview['Project Value'] : (!empty($fs['ProjectValue']) ? $fs['ProjectValue'] : (!empty($fs['EstimatedValue']) ? $fs['EstimatedValue'] : ''));
$contType   = isset($secOverview['Contract Type']) ? $secOverview['Contract Type'] : (!empty($fs['ContractType']) ? $fs['ContractType'] : '');
$warrAmc    = isset($secOverview['Warranty + AMC']) ? $secOverview['Warranty + AMC'] : (!empty($fs['WarrantyAmc']) ? $fs['WarrantyAmc'] : '');
$bidSys     = isset($secOverview['Bid System']) ? $secOverview['Bid System'] : (!empty($fs['BidSystem']) ? $fs['BidSystem'] : '');

// ── 2. Parse Section 2: ImportantDates ──
$secDates = !empty($fs['ImportantDates']) ? parseSectionKV($fs['ImportantDates']) : [];
if (empty($secDates) && !empty($fs['ImportantDatesJson'])) {
    $arr = json_decode($fs['ImportantDatesJson'], true);
    if (is_array($arr)) {
        foreach ($arr as $d) {
            if (!empty($d['activity']) && !empty($d['date_text'])) {
                $secDates[$d['activity']] = $d['date_text'];
            }
        }
    }
}

// ── 3. Parse Section 3: TenderValue ──
$secValue   = !empty($fs['TenderValue']) ? parseSectionKV($fs['TenderValue']) : [];
$tenderFee  = isset($secValue['Tender Fee']) ? $secValue['Tender Fee'] : (!empty($fs['TenderFee']) ? $fs['TenderFee'] : '');
$estVal     = isset($secValue['Estimated Value']) ? $secValue['Estimated Value'] : (!empty($fs['EstimatedValue']) ? $fs['EstimatedValue'] : '');
$emdVal     = isset($secValue['EMD']) ? $secValue['EMD'] : (!empty($fs['EmdAmount']) ? $fs['EmdAmount'] : '');
$pbgSummary = isset($secValue['Performance Bank Guarantee']) ? $secValue['Performance Bank Guarantee'] : (!empty($fs['PbgDetails']) ? $fs['PbgDetails'] : (!empty($fs['PbgAmount']) ? $fs['PbgAmount'] : ''));
$validVal   = isset($secValue['Bid Validity']) ? $secValue['Bid Validity'] : (!empty($fs['BidValidityDays']) ? $fs['BidValidityDays'] : (!empty($fs['BidValidityDuration']) ? $fs['BidValidityDuration'] : ''));

// ── 4. Parse Section 4: ScopeOfWork ──
$scopesList = !empty($fs['ScopeOfWork']) ? parseSectionList($fs['ScopeOfWork']) : [];
if (empty($scopesList) && !empty($fs['ScopeItemsJson'])) {
    $scopesList = json_decode($fs['ScopeItemsJson'], true);
}

// ── 5. Parse Section 5: AreasCovered ──
$secAreas = !empty($fs['AreasCovered']) ? parseSectionKV($fs['AreasCovered']) : [];
if (empty($secAreas) && !empty($fs['AreasCoveredJson'])) {
    $arr = json_decode($fs['AreasCoveredJson'], true);
    if (is_array($arr)) {
        foreach ($arr as $a) {
            if (!empty($a['region']) && !empty($a['coverage'])) {
                $secAreas[$a['region']] = $a['coverage'];
            }
        }
    }
}

// ── 6. Parse Section 6: ContractPeriod ──
$secPeriod = !empty($fs['ContractPeriod']) ? parseSectionKV($fs['ContractPeriod']) : [];
$initialDur = isset($secPeriod['Initial Contract']) ? $secPeriod['Initial Contract'] : (!empty($fs['ContractInitialDuration']) ? $fs['ContractInitialDuration'] : '');
$extendable = isset($secPeriod['Extendable']) ? $secPeriod['Extendable'] : (!empty($fs['ContractExtendable']) ? $fs['ContractExtendable'] : '');
$maxPeriod  = isset($secPeriod['Maximum Period']) ? $secPeriod['Maximum Period'] : (!empty($fs['ContractMaxPeriod']) ? $fs['ContractMaxPeriod'] : '');
$extCond    = isset($secPeriod['Extension Conditions']) ? $secPeriod['Extension Conditions'] : (!empty($fs['ContractExtensionConditions']) ? $fs['ContractExtensionConditions'] : '');

// ── 7. Parse Section 7: AwardCriteria ──
$secAward   = !empty($fs['AwardCriteria']) ? parseSectionKV($fs['AwardCriteria']) : [];
$evalMethod = isset($secAward['Evaluation Method']) ? $secAward['Evaluation Method'] : (!empty($fs['AwardEvaluationMethod']) ? $fs['AwardEvaluationMethod'] : '');
$splitWork  = isset($secAward['Split Work']) ? $secAward['Split Work'] : (!empty($fs['AwardSplitWork']) ? $fs['AwardSplitWork'] : '');
$allocation = isset($secAward['Possible Allocation']) ? $secAward['Possible Allocation'] : (!empty($fs['AwardAllocation']) ? $fs['AwardAllocation'] : '');
$matching   = isset($secAward['Matching Requirements']) ? $secAward['Matching Requirements'] : (!empty($fs['AwardL2L3Matching']) ? $fs['AwardL2L3Matching'] : '');

// ── 8. Parse Section 8: PerformanceGuarantee ──
$secPBG      = !empty($fs['PerformanceGuarantee']) ? parseSectionKV($fs['PerformanceGuarantee']) : [];
$pbgAmt      = isset($secPBG['Amount']) ? $secPBG['Amount'] : (!empty($fs['PbgAmount']) ? $fs['PbgAmount'] : '');
$pbgValidity = isset($secPBG['Validity']) ? $secPBG['Validity'] : (!empty($fs['PbgValidity']) ? $fs['PbgValidity'] : '');
$pbgSub      = isset($secPBG['Submission']) ? $secPBG['Submission'] : (!empty($fs['PbgSubmissionDeadline']) ? $fs['PbgSubmissionDeadline'] : '');

// ── 9. Parse Section 9: BidValidity ──
$secValidity = !empty($fs['BidValidity']) ? parseSectionKV($fs['BidValidity']) : [];
$bidValDur   = isset($secValidity['Duration']) ? $secValidity['Duration'] : (!empty($fs['BidValidityDuration']) ? $fs['BidValidityDuration'] : (!empty($fs['BidValidityDays']) ? $fs['BidValidityDays'] : ''));

// ── 10. Parse Section 10: Warranty ──
$secWarranty = !empty($fs['Warranty']) ? parseSectionKV($fs['Warranty']) : [];
$warrTerms   = isset($secWarranty['Terms']) ? $secWarranty['Terms'] : (!empty($fs['WarrantyTerms']) ? $fs['WarrantyTerms'] : '');
$warrScope   = isset($secWarranty['Scope']) ? $secWarranty['Scope'] : (!empty($fs['WarrantyAmc']) ? $fs['WarrantyAmc'] : '');

// ── 11. Parse Section 11: RiskPoints ──
$risksList = !empty($fs['RiskPoints']) ? parseSectionList($fs['RiskPoints']) : [];
if (empty($risksList) && !empty($fs['RiskPointsJson'])) {
    $risksList = json_decode($fs['RiskPointsJson'], true);
}

// ── 12. Parse Section 12: OpportunityAssessment ──
$secAssessments = !empty($fs['OpportunityAssessment']) ? parseSectionKV($fs['OpportunityAssessment']) : [];
$prebidNotes = isset($secAssessments['Pre-Bid Queries']) ? $secAssessments['Pre-Bid Queries'] : (!empty($fs['PrebidQueryNotes']) ? $fs['PrebidQueryNotes'] : '');
$attentionAreas = [];
foreach ($secAssessments as $k => $v) {
    if (strpos($k, 'Key Attention Area') !== false || strpos($k, 'Assessment') !== false) {
        $attentionAreas[] = $v;
    }
}
if (empty($attentionAreas) && !empty($fs['OpportunityAssessmentJson'])) {
    $arr = json_decode($fs['OpportunityAssessmentJson'], true);
    if (is_array($arr)) $attentionAreas = $arr;
}
?>

<?php if (!$isAjax && !empty($standalone_view)): ?>
<link rel="stylesheet" type="text/css" href="<?= $theme_path ?>/assets/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?= $theme_path ?>/assets/vendors/css/vendors.min.css">
<link rel="stylesheet" type="text/css" href="<?= $theme_path ?>/assets/css/theme.min.css">
<link rel="stylesheet" type="text/css" href="<?= $theme_path ?>/assets/css/document.css">
<?php endif; ?>

<?php if (!$isAjax): ?>
<div class="no-print d-flex justify-content-between align-items-center mb-3 pt-2 px-1 pdf-factsheet-actions">
    <a href="javascript:window.history.back();" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
        <i class="feather-arrow-left"></i> Back
    </a>
    <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" onclick="downloadFactSheetAsPdf();">
        <i class="feather-download"></i> Download Fact Sheet
    </button>
</div>
<script>
function downloadFactSheetAsPdf() {
    var printArea = document.getElementById('pdfPrintableArea') || document.querySelector('.pdf-factsheet-sheet');
    if (!printArea) {
        alert('Fact Sheet content is not available to download.');
        return;
    }
    var filename = 'Tender_FactSheet.pdf';
    function executeDownload() {
        if (window.html2pdf) {
            html2pdf().set({
                margin:       [8, 8, 8, 8],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).from(printArea).save();
        } else {
            window.print();
        }
    }
    if (window.html2pdf) {
        executeDownload();
    } else {
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = executeDownload;
        script.onerror = function() {
            window.print();
        };
        document.head.appendChild(script);
    }
}
</script>
<?php endif; ?>

<div class="pdf-factsheet-wrapper py-2 <?= $isAjax ? 'pdf-factsheet-ajax' : '' ?>" id="pdfPrintableArea">
    <div class="pdf-factsheet-sheet shadow-sm border rounded-1">

        <!-- Main Header -->
        <div class="pdf-main-title">TENDER FACTSHEET</div>

        <!-- 1. Tender Overview -->
        <div class="pdf-section-title">1. Tender Overview</div>
        <table class="pdf-fact-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Particular</th>
                    <th style="width: 68%;">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="pdf-blue-text">Department</td>
                    <td class="pdf-blue-text"><?= safeVal($dept) ?></td>
                </tr>
                <tr>
                    <td class="pdf-blue-text">Zone</td>
                    <td class="pdf-blue-text"><?= safeVal($zone) ?></td>
                </tr>
                <tr>
                    <td class="pdf-blue-text">Tender Title</td>
                    <td class="pdf-blue-text"><?= safeVal($title) ?></td>
                </tr>
                <tr>
                    <td>Tender Type</td>
                    <td><?= safeVal($type) ?></td>
                </tr>
                <tr>
                    <td>Tender Reference No.</td>
                    <td><?= safeVal($refNo) ?></td>
                </tr>
                <tr>
                    <td>Tender Date</td>
                    <td><?= safeVal($tDate) ?></td>
                </tr>
                <tr>
                    <td>Project Value</td>
                    <td><?= safeVal($projVal) ?></td>
                </tr>
                <tr>
                    <td>Contract Type</td>
                    <td><?= safeVal($contType) ?></td>
                </tr>
                <tr>
                    <td>Warranty + AMC</td>
                    <td><?= safeVal($warrAmc) ?></td>
                </tr>
                <tr>
                    <td>Bid System</td>
                    <td><?= safeVal($bidSys) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- 2. Important Dates -->
        <div class="pdf-section-title">2. Important Dates</div>
        <table class="pdf-fact-table">
            <thead>
                <tr>
                    <th style="width: 48%;">Activity</th>
                    <th style="width: 52%;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($secDates)): ?>
                    <?php foreach ($secDates as $act => $dt): ?>
                        <tr>
                            <td><?= htmlspecialchars($act) ?></td>
                            <td><?= htmlspecialchars($dt) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>Tender Published</td>
                        <td><?= safeVal(!empty($fs['TenderPublishedDate']) ? $fs['TenderPublishedDate'] : null) ?></td>
                    </tr>
                    <tr>
                        <td>Tender Download</td>
                        <td><?= safeVal(!empty($fs['TenderDownloadPeriod']) ? $fs['TenderDownloadPeriod'] : null) ?></td>
                    </tr>
                    <tr>
                        <td>Pre-Bid Query Submission</td>
                        <td><?= safeVal(!empty($fs['PreBidQueryDeadline']) ? $fs['PreBidQueryDeadline'] : null) ?></td>
                    </tr>
                    <tr>
                        <td>Pre-Bid Meeting</td>
                        <td><?= safeVal(!empty($fs['PreBidMeeting']) ? $fs['PreBidMeeting'] : null) ?></td>
                    </tr>
                    <tr>
                        <td>Bid Submission</td>
                        <td><?= safeVal(!empty($fs['BidSubmissionDeadline']) ? $fs['BidSubmissionDeadline'] : null) ?></td>
                    </tr>
                    <tr>
                        <td>Technical Bid Opening</td>
                        <td><?= safeVal(!empty($fs['TechnicalBidOpening']) ? $fs['TechnicalBidOpening'] : null) ?></td>
                    </tr>
                    <tr>
                        <td>Financial Bid Opening</td>
                        <td><?= safeVal(!empty($fs['FinancialBidOpening']) ? $fs['FinancialBidOpening'] : null, 'Will be intimated later') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 3. Tender Value -->
        <div class="pdf-section-title">3. Tender Value</div>
        <table class="pdf-fact-table">
            <thead>
                <tr>
                    <th style="width: 48%;">Particular</th>
                    <th style="width: 52%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tender Fee</td>
                    <td><?= safeVal($tenderFee) ?></td>
                </tr>
                <tr>
                    <td>EMD</td>
                    <td><?= safeVal($emdVal) ?></td>
                </tr>
                <tr>
                    <td>Performance Bank Guarantee</td>
                    <td><?= safeVal($pbgSummary) ?></td>
                </tr>
                <tr>
                    <td>Bid Validity</td>
                    <td><?= safeVal($validVal) ?></td>
                </tr>
                <?php if (!empty($estVal) && $estVal !== 'Not specified in document'): ?>
                <tr>
                    <td>Estimated Value</td>
                    <td><?= htmlspecialchars($estVal) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 4. Scope of Work -->
        <div class="pdf-section-title">4. Scope of Work</div>
        <div class="pdf-text-lead">Selected SI shall provide:</div>
        <ul class="pdf-disc-list">
            <?php if (!empty($scopesList)): ?>
                <?php foreach ($scopesList as $sc): ?>
                    <?php $scText = trim((string)$sc); if (empty($scText)) continue; ?>
                    <li><?= htmlspecialchars($scText) ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><span class="text-muted" style="font-style:italic;">As specified in tender scope of work</span></li>
            <?php endif; ?>
        </ul>

        <!-- 5. Areas Covered -->
        <div class="pdf-section-title">5. Areas Covered</div>
        <div class="pdf-text-lead">The contract covers the following regions:</div>
        <table class="pdf-fact-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Region</th>
                    <th style="width: 68%;">Coverage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($secAreas)): ?>
                    <?php foreach ($secAreas as $reg => $cov): ?>
                        <?php if (strtolower(trim($reg)) === 'region') continue; ?>
                        <tr>
                            <td><?= htmlspecialchars($reg) ?></td>
                            <td><?= htmlspecialchars($cov) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-muted" style="font-style:italic;">Jurisdiction and coverage as specified in tender document</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 6. Contract Period -->
        <div class="pdf-section-title">6. Contract Period</div>
        <ul class="pdf-disc-list">
            <li>Initial Contract:
                <ul class="pdf-circle-list">
                    <li><?= safeVal($initialDur, 'Not specified in document') ?></li>
                </ul>
            </li>
            <li>Extendable:
                <ul class="pdf-circle-list">
                    <?php $cleanExt = trim((string)$extendable, " :\t\n\r"); ?>
                    <li><?= safeVal(!empty($cleanExt) ? $cleanExt : null, 'Not specified in document') ?></li>
                </ul>
            </li>
            <li>Maximum Period:
                <ul class="pdf-circle-list">
                    <li><?= safeVal($maxPeriod, 'Not specified in document') ?></li>
                </ul>
            </li>
        </ul>
        <?php if (!empty($extCond)): ?>
            <div class="pdf-text-follow"><?= htmlspecialchars($extCond) ?></div>
        <?php endif; ?>

        <!-- 7. Award Criteria -->
        <div class="pdf-section-title">7. Award Criteria</div>
        <div class="pdf-text-lead">The Tendering Authority criteria:</div>
        <ul class="pdf-disc-list">
            <li>Evaluation Method: <?= safeVal($evalMethod, 'Not specified in document') ?></li>
            <li>Split Work: <?= safeVal($splitWork, 'Not specified in document') ?></li>
        </ul>
        <?php if (!empty($allocation) && trim($allocation) !== ':'): ?>
        <div class="pdf-text-lead">Possible allocation:</div>
        <ul class="pdf-disc-list">
            <li><?= nl2br(htmlspecialchars($allocation)) ?></li>
        </ul>
        <?php endif; ?>
        <?php if (!empty($matching)): ?>
            <div class="pdf-text-follow"><?= htmlspecialchars($matching) ?></div>
        <?php endif; ?>

        <!-- 08. Performance Guarantee -->
        <div class="pdf-section-title">08. Performance Guarantee</div>
        <table class="pdf-fact-table">
            <thead>
                <tr>
                    <th style="width: 32%;">Requirement</th>
                    <th style="width: 68%;">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Amount</td>
                    <td><?= safeVal($pbgAmt, 'Not specified in document') ?></td>
                </tr>
                <tr>
                    <td>Validity</td>
                    <td><?= safeVal($pbgValidity, 'Not specified in document') ?></td>
                </tr>
                <tr>
                    <td>Submission</td>
                    <td><?= safeVal($pbgSub, 'Not specified in document') ?></td>
                </tr>
            </tbody>
        </table>

        <!-- 09. Bid Validity -->
        <div class="pdf-section-title">09. Bid Validity</div>
        <?php if (!empty($bidValDur) && (stripos($bidValDur, 'conflict') !== false || (stripos($bidValDur, '180') !== false && stripos($bidValDur, '120') !== false))): ?>
            <div class="alert alert-warning py-2 px-3 my-2" style="font-size: 13px; border-left: 4px solid #f59e0b; background: #fffbeb; color: #92400e;">
                <i class="feather-alert-triangle mr-1 me-1 text-warning"></i>
                <strong>Conflicting Clauses Flagged:</strong> <?= htmlspecialchars($bidValDur) ?>
            </div>
        <?php else: ?>
            <div class="pdf-text-follow"><?= safeVal($bidValDur, 'Not specified in document') ?></div>
        <?php endif; ?>

        <!-- 10. Warranty -->
        <div class="pdf-section-title">10. Warranty</div>
        <div class="pdf-text-lead">Warranty & Maintenance Terms:</div>
        <div class="pdf-text-follow"><?= safeVal(!empty($warrTerms) ? $warrTerms : (!empty($warrScope) ? $warrScope : null), 'Not specified in document') ?></div>

        <!-- 11. Risk Points -->
        <div class="pdf-section-title">11. Risk Points</div>
        <?php 
        $explicitRisks = [];
        $inferredRisks = [];
        foreach ($risksList as $r) {
            $rStr = trim((string)$r);
            if (empty($rStr)) continue;
            if (stripos($rStr, '[Inferred]') !== false || stripos($rStr, 'Inferred:') !== false) {
                $inferredRisks[] = preg_replace('/^\[Inferred\]\s*|^Inferred:\s*/i', '', $rStr);
            } else {
                $explicitRisks[] = $rStr;
            }
        }
        ?>
        <div class="pdf-text-lead" style="font-weight:600;">Explicit Tender Requirements:</div>
        <ul class="pdf-disc-list">
            <?php if (!empty($explicitRisks)): ?>
                <?php foreach ($explicitRisks as $er): ?>
                    <li><?= htmlspecialchars($er) ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="text-muted" style="font-style:italic;">Not specified in document</li>
            <?php endif; ?>
        </ul>
        <?php if (!empty($inferredRisks)): ?>
        <div class="pdf-text-lead" style="font-weight:600; color:#b45309;"><i class="feather-info mr-1 me-1"></i> Inferred Operational Risks (Analysis):</div>
        <ul class="pdf-disc-list">
            <?php foreach ($inferredRisks as $ir): ?>
                <li style="color:#92400e;"><?= htmlspecialchars($ir) ?> <span class="badge bg-warning text-dark ms-1 ml-1" style="font-size:10.5px;">Inferred</span></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <!-- 12. Opportunity Assessment -->
        <div class="pdf-section-title">12. Opportunity Assessment</div>
        <?php if (!empty($prebidNotes)): ?>
            <div class="pdf-text-lead" style="font-weight:600;">Pre-Bid Queries:</div>
            <ul class="pdf-disc-list">
                <li class="pdf-red-bullet"><?= htmlspecialchars($prebidNotes) ?></li>
            </ul>
        <?php endif; ?>
        <div style="font-weight: 600; margin-top: 10px; margin-bottom: 6px; font-size: 13px;">Key Attention Areas:</div>
        <ul class="pdf-disc-list">
            <?php if (!empty($attentionAreas)): ?>
                <?php foreach ($attentionAreas as $as): ?>
                    <?php 
                        $asText = trim((string)$as); 
                        if (empty($asText)) continue; 
                        $isInferred = (stripos($asText, '[Inferred]') !== false || stripos($asText, 'Inferred:') !== false);
                        $cleanText = preg_replace('/^\[Inferred\]\s*|^Inferred:\s*/i', '', $asText);
                    ?>
                    <li>
                        <?= htmlspecialchars($cleanText) ?>
                        <?php if ($isInferred): ?>
                            <span class="badge bg-warning text-dark ms-1 ml-1" style="font-size:10.5px;">Inferred</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="text-muted" style="font-style:italic;">Not specified in document</li>
            <?php endif; ?>
        </ul>

    </div>
</div>
