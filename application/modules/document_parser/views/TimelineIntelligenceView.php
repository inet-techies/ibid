<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$fs = isset($factsheet) ? $factsheet : [];
$timelineData = isset($timelineData) && is_array($timelineData) ? $timelineData : (!empty($fs['TimelineJson']) ? json_decode($fs['TimelineJson'], true) : []);
$corrigendaList = isset($corrigendaList) && is_array($corrigendaList) ? $corrigendaList : [];
$tfs_id = isset($tfs_id) ? (int)$tfs_id : (int)($fs['Tfsid'] ?? 0);
$bcid = isset($bcid) ? (int)$bcid : (int)($fs['Bcid'] ?? 0);

$tlMilestones = $timelineData['milestones'] ?? [];
$tlMetrics    = $timelineData['metrics'] ?? ($timelineData['executive_summary'] ?? []);
$tlValidation = $timelineData['validation'] ?? [];
$tlWarnings   = $tlValidation['warnings'] ?? ($tlValidation['issues'] ?? []);
$tlConflicts  = $timelineData['conflicts'] ?? [];

$tenderTitle = !empty($fs['TenderTitle']) ? $fs['TenderTitle'] : 'Tender Timeline';
$tenderRef   = !empty($fs['TenderRefNo']) ? $fs['TenderRefNo'] : (!empty($fs['DocumentName']) ? $fs['DocumentName'] : 'Tender #' . $tfs_id);
?>

<!-- ═════════════════════════════════════════════════════════════════════
     TENDER TIMELINE INTELLIGENCE VIEW
     ═════════════════════════════════════════════════════════════════════ -->
<style>
.tl-container {
    padding: 16px;
    background: #f8fafc;
    font-family: inherit;
    color: #1e293b;
}

/* Header bar */
.tl-top-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.tl-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.tl-actions-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Summary metrics */
.tl-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}
.tl-metric-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.tl-metric-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.tl-metric-val {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
}
.tl-metric-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 3px;
}

/* Status Badges */
.timeline-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.status-upcoming { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.status-due_soon { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; animation: pulseAmber 2s infinite; }
.status-completed { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.status-overdue { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.status-date_missing { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.status-needs_review { background: #fae8ff; color: #86198f; border: 1px solid #f5d0fe; }
.status-superseded { background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; text-decoration: line-through; }

@keyframes pulseAmber {
    0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
    70% { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
    100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}

/* Warnings Box */
.timeline-warning-box {
    margin-bottom: 14px;
    padding: 10px 14px;
    background: #fffbeb;
    border: 1px solid #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 6px;
    font-size: 12px;
}
.timeline-warning-box.danger {
    background: #fef2f2;
    border-color: #fee2e2;
    border-left-color: #ef4444;
}
.timeline-warning-title {
    font-weight: 700;
    color: #92400e;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.timeline-warning-box.danger .timeline-warning-title {
    color: #991b1b;
}

/* Timeline Stream / Steps */
.timeline-stream-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.timeline-stream {
    position: relative;
    padding-left: 28px;
}
.timeline-stream::before {
    content: '';
    position: absolute;
    left: 11px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: #cbd5e1;
}
.timeline-step {
    position: relative;
    margin-bottom: 18px;
}
.timeline-step:last-child {
    margin-bottom: 0;
}
.timeline-step-dot {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: #475569;
    z-index: 2;
}
.timeline-step.overdue .timeline-step-dot { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.timeline-step.due_soon .timeline-step-dot { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }
.timeline-step.upcoming .timeline-step-dot { border-color: #0284c7; color: #0284c7; background: #f0f9ff; }
.timeline-step.completed .timeline-step-dot { border-color: #10b981; color: #10b981; background: #ecfdf5; }
.timeline-step.needs_review .timeline-step-dot { border-color: #a855f7; color: #a855f7; background: #faf5ff; }

.timeline-step-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 14px;
    transition: all 0.15s ease-in-out;
}
.timeline-step-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}
.timeline-step-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 8px;
}
.timeline-step-name {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}
.timeline-step-dates {
    margin-top: 4px;
    font-size: 12px;
    color: #334155;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.timeline-step-dates .norm-dt {
    font-weight: 600;
    color: #0369a1;
}
.timeline-step-dates .countdown-badge {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    padding: 1px 6px;
    border-radius: 3px;
}
.timeline-step.overdue .countdown-badge { color: #dc2626; background: #fee2e2; }
.timeline-step.due_soon .countdown-badge { color: #b45309; background: #fef3c7; }

.timeline-step-quote {
    margin-top: 6px;
    font-size: 11.5px;
    color: #475569;
    background: #f8fafc;
    border-left: 3px solid #cbd5e1;
    padding: 4px 8px;
    border-radius: 0 4px 4px 0;
    font-style: italic;
    cursor: pointer;
}
.timeline-step-quote:hover {
    background: #f1f5f9;
    border-left-color: #0284c7;
}

.timeline-step-footer {
    margin-top: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
}
.timeline-btn-action {
    background: none;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.timeline-btn-action:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

/* Modals */
.evidence-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(2px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.evidence-modal-dialog {
    background: #ffffff;
    border-radius: 8px;
    width: 600px;
    max-width: 95vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.evidence-modal-header {
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #0f172a;
    color: #fff;
}
.evidence-modal-title {
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
}
.evidence-modal-subtitle {
    font-size: 11.5px;
    color: #94a3b8;
}
.evidence-modal-close {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
}
.evidence-modal-close:hover {
    color: #fff;
}
.evidence-modal-body {
    padding: 16px;
    overflow-y: auto;
    font-size: 13px;
}
.evidence-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px;
}
.evidence-card-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 6px;
}
.evidence-quote-box {
    font-family: inherit;
    font-size: 12.5px;
    color: #1e293b;
    background: #ffffff;
    border-left: 3px solid #0284c7;
    padding: 10px 12px;
    border-radius: 0 4px 4px 0;
    font-style: italic;
    line-height: 1.5;
}

/* Corrigendum Diff Table */
.corrigendum-diff-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin-top: 10px;
}
.corrigendum-diff-table th {
    background: #f1f5f9;
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
    font-weight: 600;
    color: #334155;
}
.corrigendum-diff-table td {
    padding: 8px 10px;
    border: 1px solid #e2e8f0;
    vertical-align: top;
}
.diff-del {
    background: #fef2f2;
    color: #991b1b;
    text-decoration: line-through;
    padding: 2px 4px;
    border-radius: 3px;
    display: inline-block;
}
.diff-add {
    background: #ecfdf5;
    color: #065f46;
    font-weight: 600;
    padding: 2px 4px;
    border-radius: 3px;
    display: inline-block;
}
</style>

<div class="tl-container">

    <!-- Top Action / Info Bar -->
    <div class="tl-top-bar">
        <div class="tl-title-wrap">
            <div>
                <div class="fw-bold fs-14 text-dark"><?= htmlspecialchars($tenderTitle) ?></div>
                <div class="text-muted small" style="font-size:11.5px;">
                    <i class="feather-tag me-1"></i> Ref: <strong class="text-primary"><?= htmlspecialchars($tenderRef) ?></strong>
                    <?php if (!empty($timelineData['timezone'])): ?>
                        <span class="badge bg-secondary ms-2" style="font-size:9.5px;"><?= htmlspecialchars($timelineData['timezone']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="tl-actions-wrap">
            <button type="button" class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2" id="btnToggleTimelineView" onclick="toggleTimelineMode();" style="font-size:11px;">
                <i class="feather-list"></i> <span id="lblTimelineToggle">Table View</span>
            </button>
        </div>
    </div>



    <!-- Intelligent Validation Warnings Banner -->
    <?php if (!empty($tlWarnings)): ?>
        <div class="timeline-warning-box <?= (strpos(json_encode($tlWarnings), 'CONFLICT') !== false || strpos(json_encode($tlWarnings), 'DANGER') !== false) ? 'danger' : '' ?>">
            <div class="timeline-warning-title">
                <i class="feather-alert-triangle"></i> Intelligent Date &amp; Sequence Validation Warnings (<?= count($tlWarnings) ?> detected)
            </div>
            <ul class="mb-0 ps-3" style="font-size:11.5px;line-height:1.4;">
                <?php foreach ($tlWarnings as $w): ?>
                    <li>
                        <strong><?= htmlspecialchars($w['code'] ?? ($w['type'] ?? 'NOTICE')) ?>:</strong>
                        <?= htmlspecialchars($w['message'] ?? '') ?>
                        <?php if (!empty($w['supporting_quote'])): ?>
                            <em class="text-muted ms-1">"<?= htmlspecialchars(substr($w['supporting_quote'], 0, 80)) ?>..."</em>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Interactive Timeline Stream View -->
    <div class="timeline-stream-container" id="timelineStreamView">
        <div class="timeline-stream">
            <?php if (empty($tlMilestones)): ?>
                <div class="text-center text-muted py-4">
                    <i class="feather-calendar fs-3 d-block mb-2"></i>
                    No timeline milestones available for this tender.
                </div>
            <?php else: ?>
                <?php foreach ($tlMilestones as $mIndex => $m): 
                    $status = $m['status'] ?? 'upcoming';
                    $statusClass = 'status-' . $status;
                    $dtDisplay = !empty($m['normalized_datetime']) ? date('d-m-Y \a\t h:i A', strtotime($m['normalized_datetime'])) : (!empty($m['normalized_date']) ? date('d-m-Y', strtotime($m['normalized_date'])) . ' <span class="text-muted" style="font-size:11px;">(Time not specified)</span>' : 'Date unstated in document');
                    $rawQuote = $m['supporting_quote'] ?? ($m['quote'] ?? '');
                    $page = $m['source_page'] ?? ($m['page'] ?? null);
                    $type = $m['milestone_type'] ?? ($m['type'] ?? 'milestone');
                    $title = $m['milestone_title'] ?? ($m['title'] ?? ucfirst(str_replace('_', ' ', $type)));
                    $countdown = $m['countdown_text'] ?? ($m['status_badge']['label'] ?? '');
                    $isCorrigendum = !empty($m['is_corrigendum']);
                ?>
                    <div class="timeline-step <?= htmlspecialchars($status) ?>">
                        <div class="timeline-step-dot">
                            <?php if ($status === 'completed'): ?><i class="feather-check"></i>
                            <?php elseif ($status === 'overdue'): ?><i class="feather-alert-circle"></i>
                            <?php elseif ($status === 'due_soon'): ?><i class="feather-clock"></i>
                            <?php elseif ($status === 'needs_review'): ?><i class="feather-help-circle"></i>
                            <?php else: ?><i class="feather-calendar"></i>
                            <?php endif; ?>
                        </div>
                        <div class="timeline-step-card">
                            <div class="timeline-step-top">
                                <div>
                                    <span class="timeline-step-name"><?= htmlspecialchars($title) ?></span>
                                    <?php if ($isCorrigendum): ?>
                                        <span class="badge bg-primary text-white ms-1" style="font-size:9.5px;">Corrigendum Revision</span>
                                    <?php endif; ?>
                                    <?php if (!empty($m['is_calculated'])): ?>
                                        <span class="badge bg-secondary ms-1" style="font-size:9.5px;" title="<?= htmlspecialchars($m['calculation_rule'] ?? '') ?>">Calculated Relative Deadline</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="timeline-status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars(str_replace('_', ' ', $status)) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="timeline-step-dates">
                                <span class="norm-dt"><i class="feather-clock"></i> <?= $dtDisplay ?></span>
                                <?php if (!empty($countdown)): ?>
                                    <span class="countdown-badge"><?= htmlspecialchars($countdown) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($m['timezone'])): ?>
                                    <span class="text-muted" style="font-size:10.5px;">(<?= htmlspecialchars($m['timezone']) ?>)</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($rawQuote)): ?>
                                <div class="timeline-step-quote" onclick="openTimelineQuoteModal('<?= htmlspecialchars(addslashes($title)) ?>', '<?= htmlspecialchars(addslashes($rawQuote)) ?>', '<?= (int)$page ?>', '<?= htmlspecialchars($m['normalized_datetime'] ?? '') ?>', '<?= htmlspecialchars($status) ?>');" title="Click to inspect exact passage from tender">
                                    <i class="feather-file-text me-1"></i>
                                    "<?= htmlspecialchars(substr($rawQuote, 0, 110)) ?><?= strlen($rawQuote) > 110 ? '...' : '' ?>"
                                    <?php if (!empty($page)): ?>
                                        <strong class="text-primary ms-1">[Page <?= (int)$page ?>]</strong>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="timeline-step-footer">
                                <span class="text-muted">
                                    <?= !empty($m['extraction_method']) ? htmlspecialchars($m['extraction_method']) : 'Deterministic Schedule Parser' ?>
                                </span>
                                <?php if (!empty($page)): ?>
                                    <button type="button" class="timeline-btn-action" onclick="openTimelineQuoteModal('<?= htmlspecialchars(addslashes($title)) ?>', '<?= htmlspecialchars(addslashes($rawQuote)) ?>', '<?= (int)$page ?>', '<?= htmlspecialchars($m['normalized_datetime'] ?? '') ?>', '<?= htmlspecialchars($status) ?>');">
                                        <i class="feather-search"></i> Evidence
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alternate Accessible Table View -->
    <div id="timelineTableView" style="display:none; background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
        <table class="table table-sm table-striped mb-0" style="font-size:12px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 36%;">Activity / Milestone</th>
                    <th style="width: 34%;">Normalized Date &amp; Time</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 15%; text-align:right;">Evidence</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tlMilestones)): ?>
                    <?php foreach ($tlMilestones as $m): 
                        $mTitle = $m['milestone_title'] ?? ($m['title'] ?? ucfirst(str_replace('_', ' ', $m['milestone_type'] ?? '')));
                        $mDt = !empty($m['normalized_datetime']) ? date('d-m-Y h:i A', strtotime($m['normalized_datetime'])) : (!empty($m['normalized_date']) ? date('d-m-Y', strtotime($m['normalized_date'])) . ' (Time unstated)' : 'Date unstated');
                        $mStatus = $m['status'] ?? 'upcoming';
                        $mPage = $m['source_page'] ?? ($m['page'] ?? null);
                        $mQuote = $m['supporting_quote'] ?? ($m['quote'] ?? '');
                    ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($mTitle) ?></td>
                            <td><?= htmlspecialchars($mDt) ?></td>
                            <td><span class="timeline-status-badge status-<?= htmlspecialchars($mStatus) ?>"><?= htmlspecialchars(str_replace('_', ' ', $mStatus)) ?></span></td>
                            <td style="text-align:right;">
                                <?php if (!empty($mPage)): ?>
                                    <a href="javascript:void(0);" onclick="openTimelineQuoteModal('<?= htmlspecialchars(addslashes($mTitle)) ?>', '<?= htmlspecialchars(addslashes($mQuote)) ?>', '<?= (int)$mPage ?>', '<?= htmlspecialchars($m['normalized_datetime'] ?? '') ?>', '<?= htmlspecialchars($mStatus) ?>');" class="badge bg-light text-primary border">Page <?= (int)$mPage ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No milestones available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- 1. Milestone Supporting Evidence Modal -->
<div id="timelineQuoteModal" class="evidence-modal-backdrop" style="display:none;">
    <div class="evidence-modal-dialog">
        <div class="evidence-modal-header">
            <div>
                <div class="evidence-modal-title" id="tlModalTitle">Milestone Verification</div>
                <div class="evidence-modal-subtitle">Exact Supporting Passage from Tender Document</div>
            </div>
            <button type="button" class="evidence-modal-close" onclick="closeTimelineQuoteModal();">&times;</button>
        </div>
        <div class="evidence-modal-body">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <span class="text-muted small">Status: </span>
                    <span id="tlModalStatusBadge" class="timeline-status-badge status-upcoming">Upcoming</span>
                </div>
                <div>
                    <span class="text-muted small">Schedule: </span>
                    <strong id="tlModalDatetime" class="text-primary small">-</strong>
                </div>
            </div>

            <div class="evidence-card">
                <div class="evidence-card-label">Supporting Clause / Schedule Text</div>
                <div class="evidence-quote-box" id="tlModalQuote">-</div>
            </div>

            <div id="tlModalPageBadgeWrap" class="mt-2" style="display:none;">
                <span class="badge bg-light text-dark border" id="tlModalPageBadge">Page Evidence</span>
            </div>
        </div>
        <div class="p-2 px-3 border-top bg-light d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeTimelineQuoteModal();">Close</button>
        </div>
    </div>
</div>



<script>
function toggleTimelineMode() {
    var stream = document.getElementById('timelineStreamView');
    var table = document.getElementById('timelineTableView');
    var lbl = document.getElementById('lblTimelineToggle');

    if (!stream || !table) return;

    if (stream.style.display === 'none') {
        stream.style.display = 'block';
        table.style.display = 'none';
        if (lbl) lbl.textContent = 'Table View';
    } else {
        stream.style.display = 'none';
        table.style.display = 'block';
        if (lbl) lbl.textContent = 'Timeline Stream';
    }
}

function openTimelineQuoteModal(title, quote, page, datetime, status) {
    var modal = document.getElementById('timelineQuoteModal');
    if (!modal) return;

    document.getElementById('tlModalTitle').textContent = title || 'Milestone Verification';
    document.getElementById('tlModalQuote').textContent = quote || 'No quote passage available.';
    document.getElementById('tlModalDatetime').textContent = datetime || 'Time not specified';

    var statusBadge = document.getElementById('tlModalStatusBadge');
    if (statusBadge) {
        statusBadge.className = 'timeline-status-badge status-' + (status || 'upcoming');
        statusBadge.textContent = (status || 'upcoming').replace('_', ' ').toUpperCase();
    }

    var pageWrap = document.getElementById('tlModalPageBadgeWrap');
    var pageBadge = document.getElementById('tlModalPageBadge');
    if (page && parseInt(page, 10) > 0) {
        pageBadge.textContent = 'Page ' + page + ' Evidence';
        pageWrap.style.display = 'flex';
    } else {
        pageWrap.style.display = 'none';
    }

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    modal.style.setProperty('display', 'flex', 'important');
}

function closeTimelineQuoteModal() {
    var modal = document.getElementById('timelineQuoteModal');
    if (modal) modal.style.display = 'none';
}

</script>
