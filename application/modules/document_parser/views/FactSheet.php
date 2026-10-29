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


// ── Evidence Data for Traceable Verification ──
$evidenceData = ['by_field' => [], 'all' => []];
if (!empty($fs['Tfsid'])) {
    $ci =& get_instance();
    $ci->load->model('document_parser/Factsheet_model');
    $evidenceData = $ci->Factsheet_model->getEvidenceByTfsid((int)$fs['Tfsid'], $fs);
}
$evidenceByField = $evidenceData['by_field'] ?? [];
$evidenceAll     = $evidenceData['all'] ?? [];

$renderEvidenceBadge = function($fieldKey, $fieldVal = '') use ($evidenceByField, $evidenceAll) {
    $cleanKey = trim($fieldKey);
    // CRITICAL: Skip manual fields (Department, Tender Title, Project Value)
    if (in_array(strtolower($cleanKey), ['department', 'tender title', 'project value'])) {
        return '';
    }

    $ev = null;
    if (isset($evidenceByField[$cleanKey])) {
        $ev = $evidenceByField[$cleanKey];
    } else {
        foreach ($evidenceByField as $k => $item) {
            if (strcasecmp($k, $cleanKey) === 0) {
                $ev = $item;
                break;
            }
        }
    }

    $fEsc   = htmlspecialchars($cleanKey, ENT_QUOTES, 'UTF-8');
    $valEsc = htmlspecialchars($ev['extracted_value'] ?? (string)$fieldVal, ENT_QUOTES, 'UTF-8');

    if ($ev) {
        $status = $ev['verification_status'] ?? 'needs_review';
        $page   = !empty($ev['page_number']) ? (int)$ev['page_number'] : '';
        $conf   = (isset($ev['confidence_score']) && $ev['confidence_score'] > 0) ? round($ev['confidence_score'] * 100) . '%' : '';
        $quote  = htmlspecialchars($ev['supporting_quote'] ?? '', ENT_QUOTES, 'UTF-8');
        $allPassages = isset($evidenceAll[$cleanKey]) ? $evidenceAll[$cleanKey] : [$ev];
        $allJson= htmlspecialchars(json_encode($allPassages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');

        if ($status === 'verified') {
            return '<button type="button" class="btn-evidence verified" data-field="' . $fEsc . '" data-value="' . $valEsc . '" data-page="' . $page . '" data-status="verified" data-conf="' . $conf . '" data-quote="' . $quote . '" data-all="' . $allJson . '" title="Verified supporting evidence on Page ' . $page . ' (' . $conf . ' confidence)">' .
                   '<i class="fa fa-shield text-success"></i> Pg ' . $page . ' <span class="evidence-link-text">Evidence</span>' .
                   '</button>';
        } elseif ($status === 'needs_review') {
            return '<button type="button" class="btn-evidence needs-review" data-field="' . $fEsc . '" data-value="' . $valEsc . '" data-page="' . $page . '" data-status="needs_review" data-conf="' . $conf . '" data-quote="' . $quote . '" data-all="' . $allJson . '" title="Needs Review: ambiguous or unconfirmed supporting evidence">' .
                   '<i class="fa fa-exclamation-triangle text-warning"></i> ' . ($page ? 'Pg ' . $page . ' ' : '') . '<span class="evidence-link-text">Review</span>' .
                   '</button>';
        } else {
            return '<button type="button" class="btn-evidence not-found" data-field="' . $fEsc . '" data-value="' . $valEsc . '" data-page="" data-status="not_found" data-conf="0%" data-quote="No direct supporting passage located in the source document." data-all="[]" title="Evidence not located in document">' .
                   '<i class="fa fa-minus-circle text-muted"></i> <span class="evidence-link-text">No Source</span>' .
                   '</button>';
        }
    } else {
        // Fallback: If no evidence is linked yet, show "No Source" button
        return '<button type="button" class="btn-evidence not-found" data-field="' . $fEsc . '" data-value="' . $valEsc . '" data-page="" data-status="not_found" data-conf="0%" data-quote="No supporting source document linked for this field." data-all="[]" title="No source document linked">' .
               '<i class="fa fa-question-circle text-muted"></i> <span class="evidence-link-text">No Source</span>' .
               '</button>';
    }
};

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

<style>
/* ── Fact Sheet Core Document Styles ── */
.pdf-factsheet-wrapper {
    width: 100%;
    padding: 12px;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    background: #f8fafc;
}
.pdf-factsheet-sheet {
    background: #ffffff;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}
.pdf-main-title {
    font-size: 20px;
    font-weight: 800;
    text-align: center;
    color: #0f172a;
    letter-spacing: 0.5px;
    padding-bottom: 14px;
    margin-bottom: 20px;
    border-bottom: 2px solid #0284c7;
    text-transform: uppercase;
}
.pdf-section-title {
    font-size: 15px;
    font-weight: 700;
    color: #0369a1;
    margin-top: 24px;
    margin-bottom: 12px;
    padding: 6px 12px;
    background: #f0f9ff;
    border-left: 4px solid #0284c7;
    border-radius: 0 6px 6px 0;
}
.pdf-fact-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
    font-size: 13px;
}
.pdf-fact-table th {
    background: #f1f5f9;
    color: #334155;
    font-weight: 700;
    text-align: left;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    font-size: 12.5px;
}
.pdf-fact-table td {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
    color: #334155;
    line-height: 1.5;
}
.pdf-fact-table tr:nth-child(even) {
    background-color: #fafbfc;
}
.pdf-blue-text {
    color: #0369a1 !important;
    font-weight: 600;
}
.pdf-text-lead {
    font-size: 13.5px;
    font-weight: 600;
    color: #334155;
    margin: 8px 0 4px 0;
}
.pdf-text-follow {
    font-size: 13px;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 10px;
}
.pdf-disc-list, .pdf-circle-list {
    margin: 6px 0 12px 20px;
    padding: 0;
    font-size: 13px;
    color: #334155;
    line-height: 1.6;
}
.pdf-circle-list {
    list-style-type: circle;
}
.pdf-red-bullet {
    color: #b91c1c;
    font-weight: 500;
}

/* ── Clean PDF Export & Print Rules (Previous clean layout without badges) ── */
@media print {
    .btn-evidence,
    .evidence-tag,
    .evidence-modal-backdrop,
    #evidenceModal,
    .no-print {
        display: none !important;
    }
}
.pdf-export-mode .btn-evidence,
.pdf-export-mode .evidence-tag,
.pdf-export-mode .evidence-modal-backdrop,
.pdf-export-mode #evidenceModal,
.pdf-export-mode .no-print {
    display: none !important;
}
.pdf-export-mode.pdf-factsheet-wrapper,
.pdf-export-mode .pdf-factsheet-sheet {
    box-shadow: none !important;
    background: #ffffff !important;
}

/* ── Evidence Badges ── */
.btn-evidence {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.3;
    border-radius: 4px;
    border: 1px solid transparent;
    cursor: pointer;
    vertical-align: middle;
    margin-left: 6px;
    transition: all 0.15s ease-in-out;
    background: transparent;
    text-decoration: none;
    font-family: inherit;
}
.btn-evidence.verified {
    color: #15803d;
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.btn-evidence.verified:hover {
    background: #dcfce7;
    border-color: #86efac;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(22, 101, 52, 0.15);
}
.btn-evidence.needs-review {
    color: #b45309;
    background: #fffbeb;
    border-color: #fde68a;
}
.btn-evidence.needs-review:hover {
    background: #fef3c7;
    border-color: #fcd34d;
    transform: translateY(-1px);
}
.evidence-tag.not-found {
    font-size: 10.5px;
    padding: 2px 6px;
    border-radius: 4px;
    background: #f1f5f9;
    color: #94a3b8;
    margin-left: 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

/* ── Evidence Source Modal & Viewer ── */
.evidence-modal-backdrop {
    display: none;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.72) !important;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 99999999 !important;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}
.evidence-modal-dialog {
    background: #ffffff;
    border-radius: 12px;
    width: 95%;
    max-width: 920px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
    border: 1px solid #cbd5e1;
    overflow: hidden;
    animation: evPopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes evPopIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.evidence-modal-header {
    padding: 14px 20px;
    background: #0f172a;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #334155;
}
.evidence-modal-title {
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    color: #f8fafc;
}
.evidence-modal-close {
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #e2e8f0;
    font-size: 22px;
    line-height: 1;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.evidence-modal-close:hover {
    background: rgba(255, 255, 255, 0.28);
    color: #ffffff;
}
.evidence-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.evidence-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
    flex-wrap: wrap;
}
.evidence-tab-btn {
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s;
}
.evidence-tab-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.evidence-tab-btn.active {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
}
.evidence-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
}
.evidence-meta-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
}
.evidence-meta-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 3px;
}
.evidence-meta-value {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    word-break: break-word;
}
.evidence-quote-box {
    background: #f1f5f9;
    border-left: 4px solid #64748b;
    padding: 12px 16px;
    border-radius: 0 8px 8px 0;
    max-height: 180px;
    overflow-y: auto;
}
.evidence-quote-box.verified {
    background: #f0fdf4;
    border-left-color: #16a34a;
}
.evidence-quote-box.needs-review {
    background: #fffbeb;
    border-left-color: #f59e0b;
}
.evidence-quote-text {
    margin: 0;
    font-size: 13px;
    line-height: 1.6;
    color: #1e293b;
    font-style: italic;
}
.evidence-pdf-viewer-wrap {
    display: none;
    flex-direction: column;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 8px;
    background: #0f172a;
}
.evidence-pdf-viewer-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    background: #1e293b;
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
}
.evidence-pdf-frame {
    width: 100%;
    height: 380px;
    border: none;
    background: #ffffff;
}
</style>



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
            // Apply export mode so evidence badges & modals disappear in PDF (preserving clean previous output)
            printArea.classList.add('pdf-export-mode');

            var opt = {
                margin:       [8, 8, 8, 8],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            var cleanUp = function() {
                printArea.classList.remove('pdf-export-mode');
            };

            var worker = html2pdf().set(opt).from(printArea).save();
            if (worker && typeof worker.then === 'function') {
                worker.then(cleanUp).catch(cleanUp);
            } else {
                setTimeout(cleanUp, 1500);
            }
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
                    <td><?= safeVal($type) ?> <?= $renderEvidenceBadge("Tender Type") ?></td>
                </tr>
                <tr>
                    <td>Tender Reference No.</td>
                    <td><?= safeVal($refNo) ?> <?= $renderEvidenceBadge("Tender Reference No") ?></td>
                </tr>
                <tr>
                    <td>Tender Date</td>
                    <td><?= safeVal($tDate) ?> <?= $renderEvidenceBadge("Tender Date") ?></td>
                </tr>
                <tr>
                    <td>Project Value</td>
                    <td><?= safeVal($projVal) ?></td>
                </tr>
                <tr>
                    <td>Contract Type</td>
                    <td><?= safeVal($contType) ?> <?= $renderEvidenceBadge("Contract Type") ?></td>
                </tr>
                <tr>
                    <td>Warranty + AMC</td>
                    <td><?= safeVal($warrAmc) ?> <?= $renderEvidenceBadge("Warranty") ?></td>
                </tr>
                <tr>
                    <td>Bid System</td>
                    <td><?= safeVal($bidSys) ?> <?= $renderEvidenceBadge("Bid System") ?></td>
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
                            <td><?= htmlspecialchars($dt) ?> <?= $renderEvidenceBadge($act) ?></td>
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
                    <td><?= safeVal($tenderFee) ?> <?= $renderEvidenceBadge("Tender Fee") ?></td>
                </tr>
                <tr>
                    <td>EMD</td>
                    <td><?= safeVal($emdVal) ?> <?= $renderEvidenceBadge("EMD") ?></td>
                </tr>
                <tr>
                    <td>Performance Bank Guarantee</td>
                    <td><?= safeVal($pbgSummary) ?> <?= $renderEvidenceBadge("Performance Bank Guarantee") ?></td>
                </tr>
                <tr>
                    <td>Bid Validity</td>
                    <td><?= safeVal($validVal) ?> <?= $renderEvidenceBadge("Bid Validity") ?></td>
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


<!-- Evidence Source Modal & PDF Viewer -->
<div class="evidence-modal-backdrop" id="evidenceModal" style="display:none !important;">
    <div class="evidence-modal-dialog">
        <div class="evidence-modal-header">
            <h4 class="evidence-modal-title">
                <i class="fa fa-file-text-o text-info"></i>
                <span id="evModalFieldTitle">Field Evidence</span>
            </h4>
            <button type="button" class="evidence-modal-close" id="evCloseBtn" title="Close">&times;</button>
        </div>
        <div class="evidence-modal-body">
            <div id="evPassageTabs" class="evidence-tabs" style="display:none;"></div>
            
            <div class="evidence-meta-grid">
                <div class="evidence-meta-card">
                    <div class="evidence-meta-label">Extracted Value</div>
                    <div class="evidence-meta-value" id="evMetaValue">-</div>
                </div>
                <div class="evidence-meta-card">
                    <div class="evidence-meta-label">Verification Status</div>
                    <div class="evidence-meta-value" id="evMetaStatus">-</div>
                </div>
                <div class="evidence-meta-card">
                    <div class="evidence-meta-label">Source Document Page</div>
                    <div class="evidence-meta-value" id="evMetaPage">-</div>
                </div>
                <div class="evidence-meta-card">
                    <div class="evidence-meta-label">Confidence Score</div>
                    <div class="evidence-meta-value" id="evMetaConf">-</div>
                </div>
            </div>

            <div>
                <div style="font-weight: 600; font-size: 13px; color:#334155; margin-bottom: 6px;">
                    <i class="fa fa-quote-left text-muted mr-1"></i> Exact Supporting Quotation from Original Document:
                </div>
                <div class="evidence-quote-box" id="evQuoteBox">
                    <p class="evidence-quote-text" id="evQuoteText">Supporting quotation text...</p>
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; margin-top: 4px;">
                <button type="button" class="btn btn-primary btn-sm" id="btnTogglePdfViewer">
                    <i class="fa fa-file-pdf-o"></i> View in Original PDF Page
                </button>
                <a href="#" target="_blank" class="btn btn-default btn-sm" id="btnOpenPdfNewTab" style="display:none;">
                    <i class="fa fa-external-link"></i> Open PDF in New Tab
                </a>
                <span class="text-muted" style="font-size: 12px;" id="evPdfNote">Navigates directly to the exact page of the tender.</span>
            </div>

            <div class="evidence-pdf-viewer-wrap" id="evPdfViewerWrap">
                <div class="evidence-pdf-viewer-bar">
                    <span id="evPdfBarTitle"><i class="fa fa-file-pdf-o"></i> PDF Viewer - Page <span id="evPdfPageNum">1</span></span>
                    <button type="button" class="btn btn-xs btn-default" id="btnClosePdfViewer" style="color:#000;">Hide PDF</button>
                </div>
                <iframe src="" class="evidence-pdf-frame" id="evPdfFrame" title="Tender PDF Viewer"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('evidenceModal');
    var closeBtn = document.getElementById('evCloseBtn');
    var fieldTitle = document.getElementById('evModalFieldTitle');
    var metaVal = document.getElementById('evMetaValue');
    var metaStatus = document.getElementById('evMetaStatus');
    var metaPage = document.getElementById('evMetaPage');
    var metaConf = document.getElementById('evMetaConf');
    var quoteBox = document.getElementById('evQuoteBox');
    var quoteText = document.getElementById('evQuoteText');
    var tabsContainer = document.getElementById('evPassageTabs');
    var btnTogglePdf = document.getElementById('btnTogglePdfViewer');
    var btnOpenNewTab = document.getElementById('btnOpenPdfNewTab');
    var btnClosePdf = document.getElementById('btnClosePdfViewer');
    var pdfWrap = document.getElementById('evPdfViewerWrap');
    var pdfFrame = document.getElementById('evPdfFrame');
    var pdfPageNum = document.getElementById('evPdfPageNum');

    var currentPassages = [];
    var currentTfsid = <?= (int)(!empty($fs['Tfsid']) ? $fs['Tfsid'] : 0) ?>;
    var basePdfUrl = '<?= site_url("bid/view_tender_document?tfsid=") ?>' + currentTfsid;

    function closeModal() {
        if (modal) modal.style.setProperty('display', 'none', 'important');
        if (pdfFrame) pdfFrame.src = '';
        if (pdfWrap) pdfWrap.style.display = 'none';
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    function renderPassage(idx) {
        var p = currentPassages[idx];
        if (!p) return;

        // Active tab styling
        var tabBtns = tabsContainer.querySelectorAll('.evidence-tab-btn');
        tabBtns.forEach(function(b, i) {
            if (i === idx) b.classList.add('active');
            else b.classList.remove('active');
        });

        metaVal.textContent = p.extracted_value || '-';
        
        var statusHtml = '';
        quoteBox.className = 'evidence-quote-box';
        if (p.verification_status === 'verified') {
            statusHtml = '<span class="text-success"><i class="fa fa-check-circle"></i> Verified</span>';
            quoteBox.classList.add('verified');
        } else if (p.verification_status === 'needs_review') {
            statusHtml = '<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Needs Review</span>';
            quoteBox.classList.add('needs-review');
        } else {
            statusHtml = '<span class="text-muted"><i class="fa fa-minus-circle"></i> Not Found</span>';
        }
        metaStatus.innerHTML = statusHtml;

        metaPage.textContent = p.page_number ? ('Page ' + p.page_number) : 'Not specified';
        metaConf.textContent = p.confidence_score ? (Math.round(p.confidence_score * 100) + '% (' + (p.confidence_level || 'Rule') + ')') : '-';
        
        // Highlight matching extracted value in quotation if present
        var quote = p.supporting_quote || 'No supporting passage located.';
        quoteText.textContent = quote;

        // PDF Navigation setup
        var targetPage = p.page_number || 1;
        pdfPageNum.textContent = targetPage;
        var pageUrl = basePdfUrl + '#page=' + targetPage;
        
        btnOpenNewTab.href = pageUrl;
        btnOpenNewTab.style.display = p.page_number ? 'inline-block' : 'none';
        btnTogglePdf.style.display = p.page_number ? 'inline-block' : 'none';

        if (pdfWrap.style.display === 'flex') {
            pdfFrame.src = pageUrl;
        }
    }

    if (btnTogglePdf) {
        btnTogglePdf.addEventListener('click', function() {
            if (pdfWrap.style.display === 'flex') {
                pdfWrap.style.display = 'none';
                pdfFrame.src = '';
            } else {
                pdfWrap.style.display = 'flex';
                var targetPage = parseInt(pdfPageNum.textContent, 10) || 1;
                pdfFrame.src = basePdfUrl + '#page=' + targetPage;
            }
        });
    }

    if (btnClosePdf) {
        btnClosePdf.addEventListener('click', function() {
            pdfWrap.style.display = 'none';
            pdfFrame.src = '';
        });
    }

    // Attach click listeners to all evidence badges
    document.addEventListener('click', function(e) {
        var trigger = e.target.closest('.btn-evidence');
        if (!trigger) return;
        e.preventDefault();

        var fieldName = trigger.getAttribute('data-field') || 'Field Evidence';
        fieldTitle.textContent = fieldName;

        var allRaw = trigger.getAttribute('data-all');
        currentPassages = [];
        try {
            if (allRaw) {
                currentPassages = JSON.parse(allRaw);
            }
        } catch (err) {}

        if (!currentPassages || !currentPassages.length) {
            currentPassages = [{
                field_key: fieldName,
                extracted_value: trigger.getAttribute('data-value') || '',
                page_number: trigger.getAttribute('data-page') || null,
                verification_status: trigger.getAttribute('data-status') || 'verified',
                confidence_score: parseFloat(trigger.getAttribute('data-conf')) / 100 || 0.95,
                confidence_level: 'high',
                supporting_quote: trigger.getAttribute('data-quote') || ''
            }];
        }

        // Render passage tabs if multiple passages
        tabsContainer.innerHTML = '';
        if (currentPassages.length > 1) {
            tabsContainer.style.display = 'flex';
            currentPassages.forEach(function(p, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'evidence-tab-btn' + (i === 0 ? ' active' : '');
                btn.textContent = 'Passage ' + (i + 1) + (p.page_number ? ' (Page ' + p.page_number + ')' : '');
                btn.addEventListener('click', function() { renderPassage(i); });
                tabsContainer.appendChild(btn);
            });
        } else {
            tabsContainer.style.display = 'none';
        }

        renderPassage(0);
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.style.setProperty('display', 'flex', 'important');
    });
})();
</script>
