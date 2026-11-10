 <?php $employee_det = $this->session->userdata('logged_in');
   $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }

  $isTechEvaluatedMenu = (isset($PageTitle) && strtolower($PageTitle) === 'technical evaluated') 
      || (strtolower($this->uri->segment(2)) === 'technicalevaluated');
?>

<style>
/* Fixed width and scrollable container for Bid Title in customerList table */
#customerList th.col-bid-title,
#customerList td.col-bid-title {
    width: 260px !important;
    max-width: 260px !important;
    min-width: 200px !important;
    white-space: normal !important;
}
.bid-title-container {
    width: 100%;
    max-width: 260px;
    max-height: 54px;
    overflow-y: auto;
    overflow-x: auto;
    white-space: normal !important;
    word-break: break-word;
    line-height: 1.35;
    font-size: 13px;
    padding-right: 4px;
    color: #1e293b;
    cursor: pointer;
    transition: color 0.15s ease;
}
.bid-title-container:hover {
    color: #2563eb;
    text-decoration: underline;
}
.bid-title-container::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.bid-title-container::-webkit-scrollbar-track {
    background: transparent;
}
.bid-title-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.bid-title-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<!-- Permanently Visible Header Filters (Teleported to extreme right corner of breadcrumb row) -->
<div id="page-filter-actions" style="display:none;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="header-filter-item">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="feather-calendar text-muted"></i></span>
                <input type="text" id="bid-filter-date" class="form-control form-control-sm border-start-0" placeholder="All Dates" readonly style="width: 175px; cursor: pointer; font-size: 12px; background: #fff;">
            </div>
        </div>
        <div class="header-filter-item">
            <select id="bid-filter-type" class="form-select form-select-sm" style="min-width: 130px;">
                <option value="">All Bid Types</option>
                <?php 
                $types = [];
                if (!empty($BidData)) {
                    foreach ($BidData as $b) {
                        if (!empty($b['BidType'])) {
                            $tVal = trim($b['BidType']);
                            if (!in_array($tVal, $types)) $types[] = $tVal;
                        }
                    }
                }
                sort($types);
                foreach ($types as $t): ?>
                    <option value="<?= htmlspecialchars($t); ?>"><?= ucfirst(htmlspecialchars($t)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="header-filter-item">
            <select id="bid-filter-dept" class="form-select form-select-sm" style="min-width: 140px;">
                <option value="">All Departments</option>
                <?php 
                $depts = [];
                if (!empty($BidData)) {
                    foreach ($BidData as $b) {
                        if (!empty($b['BidDepartment'])) {
                            $dVal = trim($b['BidDepartment']);
                            if (!in_array($dVal, $depts)) $depts[] = $dVal;
                        }
                    }
                }
                sort($depts);
                foreach ($depts as $d): ?>
                    <option value="<?= htmlspecialchars($d); ?>"><?= htmlspecialchars($d); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="header-filter-item">
            <button type="button" class="btn btn-sm btn-icon btn-light-brand border" id="btn-reset-bid-filters" title="Reset Filters" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                <i class="feather-rotate-ccw"></i>
            </button>
        </div>
    </div>
</div>

<div class="row">
                    <div class="col-lg-12">
                        <div class="card stretch stretch-full">
                            <br/>
                           <?php 
                             if($this->session->flashdata('true')){
                           ?>
                             <div class="alert alert-success"> 
                               <?php  echo $this->session->flashdata('true'); ?>
                              </div>
                          <?php    
                          } else if($this->session->flashdata('err')){
                          ?>
                           <div class = "alert alert-danger">
                             <?php echo $this->session->flashdata('err'); ?>
                           </div>
                          <?php } ?>

                          <div id="responseMessage"></div>
                             <div class="card-body p-0">
                                <div class="table-responsive">
                                   <table class="table table-hover text-nowrap wrap-no-text" id="customerList">
                                        <thead>
                                            <tr> 
                                                <th data-orderable="false" class="text-center" style="width: 50px;">S.No</th>
                                                <th class="col-bid-title">Bid Title</th>
                                                <th>Bid Type</th>
                                                <th>Department</th>
                                                <th>Published Date</th> 
                                                <th>Opening Date</th> 
                                                <th>Submission Date</th> 
                                                <th>Bid Status</th>
                                                <th>Created On</th>
                                                <th class="text-center" data-orderable="false" style="width: 140px;">Actions</th>
                                            </tr>
                                        </thead>
                                         <tbody>
                                        <?php
                                            $formatBidColDateTime = function ($dateVal, $timeVal = '') {
                                                $dateStr = trim((string)($dateVal ?? ''));
                                                $timeStr = trim((string)($timeVal ?? ''));
                                                $hasDate = ($dateStr !== '' && $dateStr !== '0000-00-00' && strtotime($dateStr));
                                                if (!$hasDate) {
                                                    return '-';
                                                }
                                                $formattedDate = date('d-m-Y', strtotime($dateStr));
                                                if ($timeStr !== '' && strtotime($timeStr) !== false) {
                                                    return $formattedDate . ', ' . date('h:i A', strtotime($timeStr));
                                                }
                                                return $formattedDate;
                                            };

                                            if (isset($BidData) && !empty($BidData)) {
                                                $i = 1;
                                                foreach ($BidData as $val) {

                                                    $type = strtolower(trim($val['BidType']));

                                                    if ($type == 'oem') {
                                                        $badgeClass = 'bg-danger text-white';
                                                    } elseif ($type == 'service') {
                                                        $badgeClass = 'bg-primary text-white';
                                                    } else {
                                                        $badgeClass = 'bg-secondary text-white';
                                                    }
                                            ?>
                                                    <tr class="single-item">
                                                        <td class="text-center fw-bold"><?= $i; ?></td>
                                                        <td class="col-bid-title">
                                                            <div class="bid-title-container" title="<?= htmlspecialchars($val['BidTitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($val['BidTitle'] ?? ''); ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?= $badgeClass ?>">
                                                                <?= ucfirst($val['BidType']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $val['BidDepartment'] ?></td>
                                                        <td data-order="<?= htmlspecialchars(trim(($val['PublishedDate'] ?? '') . ' ' . ($val['tenderPublicationTime'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><?= $formatBidColDateTime($val['PublishedDate'] ?? '', $val['tenderPublicationTime'] ?? '') ?></td>
                                                        <td data-order="<?= htmlspecialchars(trim(($val['OpeningDate'] ?? '') . ' ' . ($val['technicalBidOpeningTime'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><?= $formatBidColDateTime($val['OpeningDate'] ?? '', $val['technicalBidOpeningTime'] ?? '') ?></td>
                                                        <td data-order="<?= htmlspecialchars(trim(($val['SubmissionDate'] ?? '') . ' ' . ($val['bidSubmissionEndTime'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><?= $formatBidColDateTime($val['SubmissionDate'] ?? '', $val['bidSubmissionEndTime'] ?? '') ?></td>
                                                        <td>
                                                            <?php
                                                            $statusBadge = !empty($val['BadgeClass']) ? $val['BadgeClass'] : 'bg-secondary';
                                                                            ?>
<span class="badge <?= $statusBadge ?>">
                                                                <?= $val['DisplayName'] ?>
                                                            </span>
                                                        </td>
                                                        <td><?= (!empty($val['CreationTs']) && strtotime($val['CreationTs'])) ? date('d M Y, h:i A', strtotime($val['CreationTs'])) : '-' ?></td>
                                                        <td class="text-center">
                                                            <div class="hstack gap-2 justify-content-center">
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md openAddForm view-bid"
                                                                    data-bid="<?= htmlspecialchars(json_encode($val), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    title="View Bid Details">
                                                                    <i class="feather feather-eye"></i>
                                                                </a>
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md view-factsheet"
                                                                    data-bcid="<?= $val['Bcid']; ?>"
                                                                    title="View Tender Fact Sheet"
                                                                    style="color:#0284c7;background:#e0f2fe;border:1px solid #bae6fd;">
                                                                    <i class="feather feather-file-text"></i>
                                                                </a>
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md view-timeline"
                                                                    data-bcid="<?= $val['Bcid']; ?>"
                                                                    title="View Timeline Intelligence"
                                                                    style="color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;">
                                                                    <i class="feather feather-clock"></i>
                                                                </a>
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md update-bid"
                                                                    data-id="<?= $val['Bcid']; ?>"
                                                                    data-current="<?= $val['BidStatus']; ?>"
                                                                    data-tech="<?= isset($val['TechnicalSelected']) ? $val['TechnicalSelected'] : ''; ?>"
                                                                    data-price="<?= htmlspecialchars($val['PriceDetails'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-loa="<?= isset($val['LoaReceived']) ? $val['LoaReceived'] : ''; ?>"
                                                                    data-loadoc="<?= htmlspecialchars($val['LoaDocumentPath'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-bg="<?= isset($val['BgSubmitted']) ? $val['BgSubmitted'] : ''; ?>"
                                                                    data-bgdoc="<?= htmlspecialchars($val['BgDocumentPath'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-wo="<?= isset($val['WoReceived']) ? $val['WoReceived'] : ''; ?>"
                                                                    data-wodoc="<?= htmlspecialchars($val['WoDocumentPath'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-cc="<?= isset($val['CompletionCertificateReceived']) ? $val['CompletionCertificateReceived'] : ''; ?>"
                                                                    data-ccdoc="<?= htmlspecialchars($val['CompletionCertificateDocumentPath'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-remarks="<?= htmlspecialchars($val['Remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-actualdate="<?= htmlspecialchars($val['ActualDate'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-mgmt="<?= htmlspecialchars($val['ApprovedByManagement'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-whom="<?= htmlspecialchars($val['ApprovedByWhom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                    title="Update Bid Status">
                                                                    <i class="feather feather-edit-2"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                            <?php
                                                    $i++;
                                                }
                                             }
                                         ?>
                                         </tbody>
                                    </table>
                                </div>
                            </div>
                         </div>
                        
                    </div>
                </div>
 <div id="rightForm" class="right-form">
    <div class="right-form-header" id="bidModalHeader">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="d-flex align-items-center gap-2">
                <i class="feather-file-text text-primary fs-18" id="bidModalHeaderIcon"></i>
                <h5 class="mb-0 fw-bold fs-15 text-dark" id="bidModalHeaderTitle">Bid Details</h5>
                <span id="bidModalHeaderBadge" style="display:none;" class="badge bg-soft-warning text-warning border border-warning" style="font-size: 11px;">Editing Mode</span>
            </div>
            <div class="d-flex align-items-center gap-2" id="bidModalHeaderActions">
                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center py-1 px-3" id="btnEditBidDetails" title="Edit Bid Details">
                    <i class="feather-edit-2 me-1 mr-1"></i> Edit
                </button>
                <button type="button" class="close-btn ms-2 ml-2" id="closeAddForm" title="Close Panel" style="font-size:24px;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
            </div>
        </div>
    </div>

    <div class="right-form-body p-3" style="overflow-y:auto; overflow-x:hidden !important; flex:1; background:#f8fafc;">
        <div id="bidAlertContainer"></div>
        <div id="userModalBody"></div>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- Right-Side Fact Sheet Slide-out Modal Panel -->
<div id="factSheetPanel" class="right-form factsheet-panel">
    <div class="right-form-header d-flex justify-content-between align-items-center py-2 px-3 border-bottom bg-white" style="position:sticky;top:0;z-index:10;">
        <div class="d-flex align-items-center gap-2">
            <i class="feather-file-text text-primary" style="font-size:19px;"></i>
            <h5 class="mb-0 fw-bold fs-15 text-dark">Tender Fact Sheet</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center py-1 px-2" id="btnDownloadFactSheetInModal" title="Download Fact Sheet">
                <i class="feather-download me-1 mr-1"></i> Download
            </button>
            <button type="button" class="close-btn ms-2 ml-2" id="closeFactSheetPanel" title="Close Panel" style="font-size:24px;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
        </div>
    </div>
    <div class="right-form-body p-0" id="factSheetPanelBody" style="background:#f1f5f9;overflow-y:auto;flex:1;">
        <div class="text-center py-5 text-muted" style="min-height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <div>Loading Fact Sheet...</div>
        </div>
    </div>
</div>

<!-- Right-Side Timeline Intelligence Slide-out Modal Panel -->
<div id="timelinePanel" class="right-form timeline-panel">
    <div class="right-form-header d-flex justify-content-between align-items-center py-2 px-3 border-bottom bg-white" style="position:sticky;top:0;z-index:10;">
        <div class="d-flex align-items-center gap-2">
            <i class="feather-clock text-primary" style="font-size:19px;"></i>
            <div>
                <h5 class="mb-0 fw-bold fs-15 text-dark">Timeline Intelligence</h5>
                <span class="text-muted" style="font-size:11px;">Schedule &amp; Corrigenda Tracking</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="close-btn ms-2 ml-2" id="closeTimelinePanel" title="Close Panel" style="font-size:24px;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
        </div>
    </div>
    <div class="right-form-body p-0" id="timelinePanelBody" style="background:#f8fafc;overflow-y:auto;flex:1;">
        <div class="text-center py-5 text-muted" style="min-height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <div>Loading Timeline Intelligence...</div>
        </div>
    </div>
</div>


<!-- Small Centered Modal for Full Bid Title -->
<div class="modal fade" id="bidTitleModal" tabindex="-1" aria-labelledby="bidTitleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center mb-0" id="bidTitleModalLabel">
                    <i class="feather-file-text text-primary me-2"></i> Bid Title
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div id="bidTitleModalMeta" class="mb-2" style="display: none;"></div>
                <div id="bidTitleModalText" class="text-dark fw-semibold" style="font-size: 14px; line-height: 1.6; white-space: pre-wrap; word-break: break-word;"></div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Right Side Update Panel -->
<div class="right-form" id="bidCanvas">
    <div class="right-form-header">
        <h5>Update Bid Status</h5>
        <button type="button" class="close-btn" id="closeAddForm">&times;</button>
    </div>
      <form id="updateBidForm">
        <input type="hidden" name="Bcid" id="Bcid">
         <div class="card stretch stretch-full">
            <div class="card-body lead-status">
                    <div id="responseMessage"></div>
                   
                    <div class="mb-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="bid_status" name="bid_status" >
                         
                        </select>
                    </div>  

                     <div class="mb-4">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                         <input  type="date" class="form-control" id="ActualDate" name="ActualDate" placeholder="Select Date">
                    </div>
                    <div class="mb-4" id="techSelectedGroup" style="display:none;">
                        <label class="form-label">Technical Selected <span class="text-danger">*</span></label>
                        <select class="form-control" id="TechnicalSelected" name="TechnicalSelected">
                            <option value="">Select Option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-4" id="priceDetailsGroup" style="display:none;">
                        <label class="form-label">Price Details <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="PriceDetails" name="PriceDetails" placeholder="Enter Price Details">
                    </div>
                    <div class="mb-4" id="loaReceivedGroup" style="display:none;">
                        <label class="form-label">LOA Received <span class="text-danger">*</span></label>
                        <select class="form-control" id="LoaReceived" name="LoaReceived">
                            <option value="">Select Option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-4" id="loaDocumentGroup" style="display:none;">
                        <label class="form-label">Upload LOA Document <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="LoaDocument" name="loa_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                        <div id="loaExistingDoc" class="mt-2" style="display:none;"></div>
                    </div>
                    <div class="mb-4" id="bgSubmittedGroup" style="display:none;">
                        <label class="form-label">BG Submitted <span class="text-danger">*</span></label>
                        <select class="form-control" id="BgSubmitted" name="BgSubmitted">
                            <option value="">Select Option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-4" id="bgDocumentGroup" style="display:none;">
                        <label class="form-label">Upload BG Document <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="BgDocument" name="bg_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                        <div id="bgExistingDoc" class="mt-2" style="display:none;"></div>
                    </div>
                    <div class="mb-4" id="woReceivedGroup" style="display:none;">
                        <label class="form-label">WO Received <span class="text-danger">*</span></label>
                        <select class="form-control" id="WoReceived" name="WoReceived">
                            <option value="">Select Option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-4" id="woDocumentGroup" style="display:none;">
                        <label class="form-label">Upload WO Document <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="WoDocument" name="wo_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                        <div id="woExistingDoc" class="mt-2" style="display:none;"></div>
                    </div>
                    <div class="mb-4" id="ccReceivedGroup" style="display:none;">
                        <label class="form-label">Completion Certificate Received <span class="text-danger">*</span></label>
                        <select class="form-control" id="CompletionCertificateReceived" name="CompletionCertificateReceived">
                            <option value="">Select Option</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-4" id="ccDocumentGroup" style="display:none;">
                        <label class="form-label">Upload Completion Certificate Document <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="CompletionCertificateDocument" name="completion_certificate_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                        <div id="ccExistingDoc" class="mt-2" style="display:none;"></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Approved By Management <span class="text-danger">*</span></label>
                        <select class="form-control" id="ApprovedByManagement" name="ApprovedByManagement"  data-select2="icon">
                            <option value="0" >Select Option</option>
                            <option value="1" data-icon="feather-at-sign">Yes</option>
                            <option value="2" data-icon="feather-users">No</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Approved By Whom <span class="text-danger">*</span></label>
                        <select class="form-control" id="ApprovedByWhom" name="ApprovedByWhom"  data-select2-selector="icon">
                            <!-- <option value="1" data-icon="feather-users">Gajapathy C</option>
                            <option value="2" data-icon="feather-users">Prabhakar N</option>
                            <option value="2" data-icon="feather-users">Dinakaran VR</option>
                            <option value="2" data-icon="feather-users">Jayakumar M</option> -->
                        </select>
                    </div>
                     <div class="mb-4">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                          <textarea class="form-control" id="addressInput" name="Remarks" cols="30" rows="3" placeholder="Remarks"></textarea>
                         
                    </div>
                      <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" id="clear" class="btn btn-md bg-soft-danger text-danger">Clear</button>
                        <button type="button" id="UpdateBid" class="btn btn-md btn-primary">Update</button>
                    </div>
            </div>
        </div>
    </form>
  
</div>
            <!-- [ Main Content ] end -->
  <!-- legacy jquery-1.11.1 removed to prevent multi-jquery collision -->

  
<script>
$(document).on('change', '.status-change', function () {

    var status = $(this).val();
    var userId = $(this).data('id');

    $.ajax({
        url: "<?= base_url('Bid/updateUserStatus'); ?>",
        type: "POST",
        data: {
            id: userId,
            status: status
        },
        success: function (response) {
            // console.log(response);
            var res = JSON.parse(response);

            if(res.status === true){
                $('#responseMessage').html(
                    '<div class="alert alert-success">'+res.message+'</div>'
                );
            } else {
                $('#responseMessage').html(
                    '<div class="alert alert-danger">'+res.message+'</div>'
                );
            }

            // auto hide after 3 sec
            setTimeout(function(){
                $('#responseMessage').html('');
            },3000);
        }
    });

});

$(document).on('click', '.view-user', function () {

    var userId = $(this).data('id');

    $.ajax({
        url: "<?= base_url('Bid/getUserData'); ?>",
        type: "POST",
        data: { id: userId },
        success: function (response) {
            // console.log(response);
            $('#userModalBody').html(response);
            // $('#userModal').modal('show');
        }
    });
});

function formatBidDateTime(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr.replace(/-/g, '/'));
    if (isNaN(d.getTime())) return dateStr;
    var day = ('0' + d.getDate()).slice(-2);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var month = months[d.getMonth()];
    var year = d.getFullYear();
    var hours = d.getHours();
    var minutes = ('0' + d.getMinutes()).slice(-2);
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? ('0' + hours).slice(-2) : '12';
    return day + ' ' + month + ' ' + year + ', ' + hours + ':' + minutes + ' ' + ampm;
}

// ── Bid Details Modal State & Interactive Edit Management ──
var currentBidData = null;
var currentBidLastModified = null;
var currentBidHistory = [];
var availableCategories = [];
var isBidEditMode = false;

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatIsoDate(dateStr) {
    if (!dateStr) return '';
    var match = String(dateStr).match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
}

function formatIsoTime(timeStr) {
    if (timeStr === null || timeStr === undefined) return '';
    var t = String(timeStr).trim();
    if (!t) return '';
    var m = t.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?(?:\s*(AM|PM))?$/i);
    if (m) {
        var hh = parseInt(m[1], 10);
        var mm = m[2];
        var ampm = m[4] ? m[4].toUpperCase() : '';
        if (ampm === 'PM' && hh < 12) hh += 12;
        if (ampm === 'AM' && hh === 12) hh = 0;
        return ('0' + hh).slice(-2) + ':' + mm;
    }
    return '';
}

function formatDisplayDate(dateStr) {
    if (!dateStr || dateStr === '0000-00-00') return '-';
    var matchIso = String(dateStr).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (matchIso) return matchIso[3] + '-' + matchIso[2] + '-' + matchIso[1];
    var matchDmy = String(dateStr).match(/^(\d{2})-(\d{2})-(\d{4})/);
    if (matchDmy) return matchDmy[1] + '-' + matchDmy[2] + '-' + matchDmy[3];
    var d = new Date(String(dateStr).replace(/-/g, '/'));
    if (isNaN(d.getTime())) return String(dateStr);
    return ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + d.getFullYear();
}

function formatDisplayTime(timeStr) {
    if (timeStr === null || timeStr === undefined) return '';
    var t = String(timeStr).trim();
    if (!t) return '';
    var m = t.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?(?:\s*(AM|PM))?$/i);
    if (m) {
        var hh = parseInt(m[1], 10);
        var mm = m[2];
        var ampm = m[4] ? m[4].toUpperCase() : (hh >= 12 ? 'PM' : 'AM');
        if (!m[4]) {
            hh = hh % 12;
            hh = hh ? hh : 12;
        }
        return ('0' + hh).slice(-2) + ':' + mm + ' ' + ampm;
    }
    return t;
}

function formatDisplayDateTime(dateStr, timeStr) {
    var hasDate = dateStr && dateStr !== '0000-00-00' && String(dateStr).trim() !== '';
    var dPart = hasDate ? formatDisplayDate(dateStr) : '';
    var tPart = formatDisplayTime(timeStr);
    if (dPart && dPart !== '-' && tPart) return dPart + ', ' + tPart;
    if (dPart && dPart !== '-') return dPart;
    if (tPart) return tPart;
    return '';
}

function cleanNum(val) {
    if (val === null || val === undefined || val === '') return '';
    var clean = String(val).replace(/[,\s]/g, '');
    var num = parseFloat(clean);
    return isNaN(num) ? '' : num.toFixed(2);
}

function renderBidReadOnlyView() {
    var bid = currentBidData || {};
    var lastMod = currentBidLastModified || { name: 'Not modified yet', ts: bid.CreationTs };
    var history = currentBidHistory || [];

    // Header updates
    $('#bidModalHeaderTitle').text('Bid Details');
    $('#bidModalHeaderIcon').attr('class', 'feather-file-text text-primary fs-18');
    $('#bidModalHeaderBadge').hide();
    $('#bidModalHeaderActions').html(`
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center py-1 px-3 shadow-sm" id="btnEditBidDetails" title="Edit Bid Details">
            <i class="feather-edit-2 me-1 mr-1"></i> Edit
        </button>
        <button type="button" class="close-btn ms-2 ml-2" id="closeAddForm" title="Close Panel" style="font-size:24px;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
    `);

    // Category display name
    var catDisplay = bid.CategoryName || bid.Category || '-';

    // All tender schedule date & time fields (shown only if not null/empty)
    var scheduleFields = [
        { label: 'Published Date', dateKey: 'PublishedDate', timeKey: 'tenderPublicationTime' },
        { label: 'Document Sale / Download Start', dateKey: 'documentSaleStartDate', timeKey: 'documentSaleStartTime' },
        { label: 'Document Sale / Download End', dateKey: 'documentSaleEndDate', timeKey: 'documentSaleEndTime' },
        { label: 'Pre-Bid Query Start', dateKey: 'prebidQueryStartDate', timeKey: 'prebidQueryStartTime' },
        { label: 'Pre-Bid Query End', dateKey: 'prebidQueryEndDate', timeKey: 'prebidQueryEndTime' },
        { label: 'Pre-Bid Meeting', dateKey: 'prebidMeetingDate', timeKey: 'prebidMeetingTime' },
        { label: 'Pre-Bid Response', dateKey: 'prebidResponseDate', timeKey: 'prebidResponseTime' },
        { label: 'Bid Submission Start', dateKey: 'bidSubmissionStartDate', timeKey: 'bidSubmissionStartTime' },
        { label: 'Submission Date', dateKey: 'SubmissionDate', timeKey: 'bidSubmissionEndTime' },
        { label: 'PQ Bid Opening', dateKey: 'pqBidOpeningDate', timeKey: 'pqBidOpeningTime' },
        { label: 'Opening Date', dateKey: 'OpeningDate', timeKey: 'technicalBidOpeningTime' },
        { label: 'Financial Bid Opening', dateKey: 'financialBidOpeningDate', timeKey: 'financialBidOpeningTime' }
    ];
    var scheduleRowsHtml = scheduleFields.map(function(f) {
        var val = formatDisplayDateTime(bid[f.dateKey], bid[f.timeKey]);
        if (!val) return '';
        return `
                    <tr>
                        <th class="bg-light text-muted">${escapeHtml(f.label)}</th>
                        <td>${escapeHtml(val)}</td>
                    </tr>`;
    }).join('');

    var html = `
    <!-- Read-Only Bid Information Table -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px; overflow:hidden;">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 bid-detail-table" style="font-size:13px; table-layout:fixed; width:100%;">
                <colgroup>
                    <col style="width: 35%;">
                    <col style="width: 65%;">
                </colgroup>
                <tbody>
                    <tr>
                        <th width="35%" class="bg-light text-muted">Bid Status</th>
                        <td>
                            ${
                                bid.DisplayName 
                                ? `<span class="badge ${bid.BadgeClass ?? 'bg-secondary'}">${bid.DisplayName}</span>`
                                : '-'
                            }
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Tender ID</th>
                        <td class="fw-semibold text-dark">${escapeHtml(bid.TenderId ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Bid Title</th>
                        <td class="fw-bold text-dark text-wrap">${escapeHtml(bid.BidTitle ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Department</th>
                        <td>${escapeHtml(bid.BidDepartment ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">State</th>
                        <td>${escapeHtml(bid.State ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Customer Name</th>
                        <td>${escapeHtml(bid.CustomerName ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Customer Phone</th>
                        <td>${escapeHtml(bid.CustomerPhone ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Customer Address</th>
                        <td class="text-wrap">${escapeHtml(bid.CustomerAddress ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Category</th>
                        <td><span class="badge bg-soft-info text-info border border-info">${escapeHtml(catDisplay)}</span></td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Bid Type</th>
                        <td><span class="badge bg-soft-primary text-primary">${escapeHtml(bid.BidType ?? '-')}</span></td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Estimated Value</th>
                        <td class="fw-bold text-success fs-14">₹ ${bid.EstimatedValue ? Number(bid.EstimatedValue).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">EMD Value</th>
                        <td class="fw-semibold text-dark">₹ ${bid.EmdValue ? Number(bid.EmdValue).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-'}</td>
                    </tr>
                    ${scheduleRowsHtml}
                    <tr>
                        <th class="bg-light text-muted">Tender Portal</th>
                        <td>
                            ${bid.TenderPortalUrl 
                                ? `<a href="${escapeHtml(bid.TenderPortalUrl)}" target="_blank" class="text-primary text-decoration-underline d-inline-flex align-items-center gap-1"><i class="feather-external-link"></i> View Portal</a>` 
                                : '-'
                            }
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Summary Keywords</th>
                        <td class="text-wrap">${escapeHtml(bid.SummaryKeywords ?? '-')}</td>
                    </tr>
                    <tr>
                        <th class="bg-light text-muted">Remarks</th>
                        <td class="text-wrap">${escapeHtml(bid.Remarks ?? '-')}</td>
                    </tr>
                    ${(bid.TechnicalSelected !== null && bid.TechnicalSelected !== undefined && bid.TechnicalSelected !== '') ? `
                    <tr>
                        <th class="bg-light text-muted">Technical Selected</th>
                        <td>
                            ${bid.TechnicalSelected == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}
                    ${(bid.PriceDetails !== null && bid.PriceDetails !== undefined && bid.PriceDetails !== '') ? `
                    <tr>
                        <th class="bg-light text-muted">Price Details</th>
                        <td>${escapeHtml(bid.PriceDetails)}</td>
                    </tr>` : ''}
                    ${(bid.LoaReceived !== null && bid.LoaReceived !== undefined && bid.LoaReceived !== '') ? `
                    <tr>
                        <th class="bg-light text-muted">LOA Received</th>
                        <td>
                            ${bid.LoaReceived == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}
                    ${(bid.LoaDocumentPath) ? `
                    <tr>
                        <th class="bg-light text-muted">LOA Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${escapeHtml(bid.LoaDocumentPath)}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2">
                                <i class="feather-file-text"></i> View LOA Document
                            </a>
                        </td>
                    </tr>` : ''}
                    ${(bid.BgSubmitted !== null && bid.BgSubmitted !== undefined && bid.BgSubmitted !== '') ? `
                    <tr>
                        <th class="bg-light text-muted">BG Submitted</th>
                        <td>
                            ${bid.BgSubmitted == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}
                    ${(bid.BgDocumentPath) ? `
                    <tr>
                        <th class="bg-light text-muted">BG Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${escapeHtml(bid.BgDocumentPath)}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2">
                                <i class="feather-file-text"></i> View BG Document
                            </a>
                        </td>
                    </tr>` : ''}
                    ${(bid.WoReceived !== null && bid.WoReceived !== undefined && bid.WoReceived !== '') ? `
                    <tr>
                        <th class="bg-light text-muted">WO Received</th>
                        <td>
                            ${bid.WoReceived == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}
                    ${(bid.WoDocumentPath) ? `
                    <tr>
                        <th class="bg-light text-muted">WO Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${escapeHtml(bid.WoDocumentPath)}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2">
                                <i class="feather-file-text"></i> View WO Document
                            </a>
                        </td>
                    </tr>` : ''}
                    ${(bid.CompletionCertificateReceived !== null && bid.CompletionCertificateReceived !== undefined && bid.CompletionCertificateReceived !== '') ? `
                    <tr>
                        <th class="bg-light text-muted">Completion Certificate</th>
                        <td>
                            ${bid.CompletionCertificateReceived == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}
                    ${(bid.CompletionCertificateDocumentPath) ? `
                    <tr>
                        <th class="bg-light text-muted">Completion Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${escapeHtml(bid.CompletionCertificateDocumentPath)}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2">
                                <i class="feather-file-text"></i> View Certificate
                            </a>
                        </td>
                    </tr>` : ''}
                    <tr>
                        <th class="bg-light text-muted">Creation Date</th>
                        <td>${formatBidDateTime(bid.CreationTs)}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Last Modified Information Card ── -->
    <div class="card border bg-white shadow-sm mb-3" style="border-radius:10px;">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-text avatar-sm bg-soft-primary text-primary" style="border-radius:6px;width:32px;height:32px;">
                        <i class="feather-user-check" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase" style="font-size:10px; font-weight:700; letter-spacing:0.5px;">Last Edited By</div>
                        <div class="fw-bold text-dark fs-13" id="modalLastEditedBy">${escapeHtml(lastMod.name || 'Prasanna Kumari C')}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-text avatar-sm bg-soft-success text-success" style="border-radius:6px;width:32px;height:32px;">
                        <i class="feather-clock" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase" style="font-size:10px; font-weight:700; letter-spacing:0.5px;">Last Edited Date &amp; Time</div>
                        <div class="fw-bold text-dark fs-13" id="modalLastEditedTs">${formatBidDateTime(lastMod.ts || bid.LastChangeTs || bid.CreationTs)}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Edit History Section (Collapsible Table) ── -->
    <div class="card border bg-white shadow-sm mb-3" style="border-radius:10px; overflow:hidden;">
        <div class="card-header py-2 px-3 bg-white border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="feather-rotate-ccw text-info fs-16"></i>
                <h6 class="mb-0 fw-bold fs-13 text-dark">Edit History</h6>
                <span class="badge bg-soft-info text-info border border-info" style="font-size:11px;" id="historyCountBadge">${history.length} event${history.length === 1 ? '' : 's'}</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1" id="btnToggleHistory" style="font-size:12px;">
                <i class="feather-chevron-down" id="historyToggleIcon"></i> <span id="historyToggleText">View History</span>
            </button>
        </div>
        <div id="historyCollapseContainer" style="display:none;" class="p-3">
            ${
                history && history.length > 0 ? `
                <div class="table-responsive" style="overflow-x:hidden !important; width:100%;">
                    <table class="table table-sm table-bordered mb-2 history-table" style="font-size:12px; table-layout:fixed; width:100%;">
                        <colgroup>
                            <col style="width: 145px;">
                            <col style="width: 125px;">
                            <col style="width: auto;">
                            <col style="width: 85px;">
                        </colgroup>
                        <thead class="bg-light">
                            <tr>
                                <th style="white-space:nowrap; width:145px;">Date &amp; Time</th>
                                <th style="white-space:nowrap; width:125px;">Edited By</th>
                                <th style="white-space:nowrap;">Action / Remarks</th>
                                <th class="text-center" style="white-space:nowrap; width:85px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${history.map(function(h) {
                                return `
                                <tr>
                                    <td style="white-space:nowrap; vertical-align:middle; font-size:11.5px;">${formatBidDateTime(h.CreationTs)}</td>
                                    <td class="fw-semibold text-dark" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; vertical-align:middle; font-size:11.5px;" title="${escapeHtml(h.UpdatedByName || 'User')}"><i class="feather-user me-1 text-muted"></i> ${escapeHtml(h.UpdatedByName || 'User')}</td>
                                    <td class="col-remarks" style="white-space:normal !important; word-break:break-word; overflow-wrap:break-word; vertical-align:middle; font-size:11.5px; line-height:1.4;">${escapeHtml(h.Remarks || 'Status update logged')}</td>
                                    <td class="text-center" style="white-space:nowrap; vertical-align:middle;">
                                        <span class="badge ${h.BadgeClass || 'bg-secondary'}" style="font-size:11px;">
                                            ${escapeHtml(h.StatusDisplayName || 'Updated')}
                                        </span>
                                    </td>
                                </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
                ` : `
                <div class="text-center py-3 text-muted" style="font-size:12px;">
                    <i class="feather-inbox me-1"></i> No previous modification records logged yet.
                </div>
                `
            }

        </div>
    </div>

    <!-- ── Fact Sheet & Timeline Action Buttons ── -->
    <div class="d-flex align-items-center justify-content-end gap-2 pt-2">
        <button type="button" class="btn btn-sm text-white fw-bold d-inline-flex align-items-center py-2 px-3 view-factsheet" data-bcid="${bid.Bcid}" style="background:linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);border:none;border-radius:6px;box-shadow:0 2px 6px rgba(14,165,233,0.3);">
            <i class="feather-file-text me-1 mr-1"></i> View Fact Sheet
        </button>
        <button type="button" class="btn btn-sm text-white fw-bold d-inline-flex align-items-center py-2 px-3 view-timeline" data-bcid="${bid.Bcid}" style="background:linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);border:none;border-radius:6px;box-shadow:0 2px 6px rgba(99,102,241,0.3);">
            <i class="feather-clock me-1 mr-1"></i> View Timeline Intelligence
        </button>
    </div>
    `;

    $('#userModalBody').html(html);
}

function renderBidEditView() {
    var bid = currentBidData || {};
    var cats = availableCategories || [];

    // Header updates
    $('#bidModalHeaderTitle').text('Edit Bid Details');
    $('#bidModalHeaderIcon').attr('class', 'feather-edit-2 text-warning fs-18');
    $('#bidModalHeaderBadge').show();
    $('#bidModalHeaderActions').html(`
        <button type="button" class="btn btn-sm btn-light border d-inline-flex align-items-center py-1 px-2" id="btnCancelEditBid" title="Discard Changes">
            <i class="feather-x me-1 mr-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center py-1 px-3 shadow-sm" id="btnSaveBidDetails" title="Save Changes">
            <i class="feather-save me-1 mr-1"></i> Save Changes
        </button>
        <button type="button" class="close-btn ms-2 ml-2" id="closeAddForm" title="Close Panel" style="font-size:24px;line-height:1;border:none;background:none;cursor:pointer;">&times;</button>
    `);

    // Category options
    var catOptions = '<option value="">Select Category</option>';
    cats.forEach(function(c) {
        var isSelected = (bid.Category == c.CId || bid.Category == c.CategoryList || bid.CategoryName == c.CategoryList) ? 'selected' : '';
        catOptions += `<option value="${escapeHtml(c.CId)}" ${isSelected}>${escapeHtml(c.CategoryList)}</option>`;
    });

    // Bid types
    var bidTypes = ['Service', 'Product', 'Turnkey', 'Works', 'Supply'];
    var currentType = (bid.BidType || '').trim();
    if (currentType && !bidTypes.includes(currentType)) {
        bidTypes.push(currentType);
    }
    var typeOptions = '<option value="">Select Bid Type</option>';
    bidTypes.forEach(function(t) {
        var isSelected = (currentType.toLowerCase() === t.toLowerCase()) ? 'selected' : '';
        typeOptions += `<option value="${escapeHtml(t)}" ${isSelected}>${escapeHtml(t)}</option>`;
    });

    var editScheduleFields = [
        { label: 'Published Date', dateKey: 'PublishedDate', timeKey: 'tenderPublicationTime' },
        { label: 'Document Sale / Download Start', dateKey: 'documentSaleStartDate', timeKey: 'documentSaleStartTime' },
        { label: 'Document Sale / Download End', dateKey: 'documentSaleEndDate', timeKey: 'documentSaleEndTime' },
        { label: 'Pre-Bid Query Start', dateKey: 'prebidQueryStartDate', timeKey: 'prebidQueryStartTime' },
        { label: 'Pre-Bid Query End', dateKey: 'prebidQueryEndDate', timeKey: 'prebidQueryEndTime' },
        { label: 'Pre-Bid Meeting', dateKey: 'prebidMeetingDate', timeKey: 'prebidMeetingTime' },
        { label: 'Pre-Bid Response', dateKey: 'prebidResponseDate', timeKey: 'prebidResponseTime' },
        { label: 'Bid Submission Start', dateKey: 'bidSubmissionStartDate', timeKey: 'bidSubmissionStartTime' },
        { label: 'Submission Date (End)', dateKey: 'SubmissionDate', timeKey: 'bidSubmissionEndTime' },
        { label: 'PQ Bid Opening', dateKey: 'pqBidOpeningDate', timeKey: 'pqBidOpeningTime' },
        { label: 'Opening Date (Technical)', dateKey: 'OpeningDate', timeKey: 'technicalBidOpeningTime' },
        { label: 'Financial Bid Opening', dateKey: 'financialBidOpeningDate', timeKey: 'financialBidOpeningTime' }
    ];
    var editScheduleHtml = editScheduleFields.map(function(f) {
        return `
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">${escapeHtml(f.label)}</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control form-control-sm" name="${f.dateKey}" value="${formatIsoDate(bid[f.dateKey])}">
                            <input type="time" class="form-control form-control-sm" name="${f.timeKey}" value="${formatIsoTime(bid[f.timeKey])}" style="max-width:115px;">
                        </div>
                    </div>`;
    }).join('');

    var html = `
    <form id="editBidForm" class="needs-validation" novalidate autocomplete="off">
        <input type="hidden" name="Bcid" value="${escapeHtml(bid.Bcid)}">

        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold fs-13 text-dark"><i class="feather-edit me-1 text-primary"></i> Edit Bid Parameters</span>
                <span class="badge ${bid.BadgeClass || 'bg-secondary'}">${bid.DisplayName || 'Status'}</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Tender ID</label>
                        <input type="text" class="form-control form-control-sm" name="TenderId" value="${escapeHtml(bid.TenderId || '')}" placeholder="Tender ID">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Bid Type</label>
                        <select class="form-select form-select-sm" name="BidType">
                            ${typeOptions}
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px;">Bid Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="BidTitle" value="${escapeHtml(bid.BidTitle || '')}" required placeholder="Enter Bid Title">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Department</label>
                        <input type="text" class="form-control form-control-sm" name="BidDepartment" value="${escapeHtml(bid.BidDepartment || '')}" placeholder="Department">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">State</label>
                        <input type="text" class="form-control form-control-sm" name="State" value="${escapeHtml(bid.State || '')}" placeholder="State">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Customer Name</label>
                        <input type="text" class="form-control form-control-sm" name="CustomerName" value="${escapeHtml(bid.CustomerName || '')}" placeholder="Customer Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Customer Phone</label>
                        <input type="tel" class="form-control form-control-sm" name="CustomerPhone" value="${escapeHtml(bid.CustomerPhone || '')}" placeholder="Phone number">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px;">Customer Address</label>
                        <textarea class="form-control form-control-sm" name="CustomerAddress" rows="2" placeholder="Customer address">${escapeHtml(bid.CustomerAddress || '')}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Category</label>
                        <select class="form-select form-select-sm" name="Category">
                            ${catOptions}
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">Estimated Value (₹) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="EstimatedValue" value="${cleanNum(bid.EstimatedValue)}" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="font-size:12px;">EMD Value (₹)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">₹</span>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="EmdValue" value="${cleanNum(bid.EmdValue)}" placeholder="0.00">
                        </div>
                    </div>

                    ${editScheduleHtml}

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px;">Tender Portal URL</label>
                        <input type="url" class="form-control form-control-sm" name="TenderPortalUrl" value="${escapeHtml(bid.TenderPortalUrl || '')}" placeholder="https://...">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px;">Summary Keywords</label>
                        <textarea class="form-control form-control-sm" name="SummaryKeywords" rows="2" placeholder="Keywords, comma separated">${escapeHtml(bid.SummaryKeywords || '')}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px;">Remarks</label>
                        <textarea class="form-control form-control-sm" name="Remarks" rows="2" placeholder="Remarks">${escapeHtml(bid.Remarks || '')}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-sm btn-light border py-2 px-3" id="btnCancelEditBidBottom">
                        <i class="feather-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary py-2 px-4 shadow-sm" id="btnSaveBidDetailsBottom">
                        <i class="feather-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
    `;

    $('#userModalBody').html(html);
}

function loadBidModalData(bcid) {
    if (!bcid) return;
    $.ajax({
        url: "<?= base_url('bid/getBidModalDetails'); ?>",
        type: "GET",
        data: { bcid: bcid },
        dataType: "json",
        success: function(res) {
            if (res && res.status) {
                currentBidData         = res.bid || currentBidData;
                currentBidLastModified = res.lastModified || currentBidLastModified;
                currentBidHistory      = res.history || [];
                availableCategories    = res.categories || [];
                if (!isBidEditMode) {
                    renderBidReadOnlyView();
                }
            }
        }
    });
}

// ── Open View Bid Details Drawer ──
$(document).on('click', '.view-bid', function (e) {
    if (e) e.preventDefault();
    $('.right-form').removeClass('open');

    var bid = $(this).data('bid');
    if (typeof bid === 'string') {
        try { bid = JSON.parse(bid); } catch(err) { bid = {}; }
    }
    currentBidData         = bid || {};
    currentBidLastModified = { name: 'Prasanna Kumari C', ts: currentBidData.LastChangeTs || currentBidData.CreationTs };
    currentBidHistory      = [];
    isBidEditMode          = false;

    $('#bidAlertContainer').empty();
    renderBidReadOnlyView();

    $('#rightForm').addClass('open');
    $('#rightFormOverlay').addClass('show');

    // Fetch fresh details, history and categories
    if (currentBidData.Bcid) {
        loadBidModalData(currentBidData.Bcid);
    }
});

// ── Enter Edit Mode ──
$(document).on('click', '#btnEditBidDetails', function(e) {
    e.preventDefault();
    isBidEditMode = true;
    $('#bidAlertContainer').empty();
    renderBidEditView();
});

// ── Cancel Edit Mode ──
$(document).on('click', '#btnCancelEditBid, #btnCancelEditBidBottom', function(e) {
    e.preventDefault();
    isBidEditMode = false;
    $('#bidAlertContainer').empty();
    renderBidReadOnlyView();
});

// ── Toggle History Accordion ──
$(document).on('click', '#btnToggleHistory', function(e) {
    e.preventDefault();
    var $container = $('#historyCollapseContainer');
    var $icon = $('#historyToggleIcon');
    var $text = $('#historyToggleText');

    if ($container.is(':visible')) {
        $container.slideUp(200);
        $icon.removeClass('feather-chevron-up').addClass('feather-chevron-down');
        $text.text('View History');
    } else {
        $container.slideDown(200);
        $icon.removeClass('feather-chevron-down').addClass('feather-chevron-up');
        $text.text('Hide History');
    }
});

// ── Save Bid Details (via Button or Form Submit) ──
function executeSaveBidDetails() {
    var $form = $('#editBidForm');
    if (!$form.length) return;

    var title = $.trim($form.find('input[name="BidTitle"]').val());
    if (!title) {
        $('#bidAlertContainer').html(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="feather-alert-circle me-2"></i> Bid Title is required.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $form.find('input[name="BidTitle"]').focus();
        return;
    }

    var $saveBtns = $('#btnSaveBidDetails, #btnSaveBidDetailsBottom');
    $saveBtns.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    $.ajax({
        url: "<?= base_url('bid/updateBidDetails'); ?>",
        type: "POST",
        data: $form.serialize(),
        dataType: "json",
        success: function(res) {
            $saveBtns.prop('disabled', false).html('<i class="feather-save me-1"></i> Save Changes');
            if (res && res.status) {
                // Update in-memory state
                currentBidData         = res.bid;
                currentBidLastModified = res.lastModified;
                currentBidHistory      = res.history;
                isBidEditMode          = false;

                // Update Table Row in DataTables to reflect changes immediately
                if (window.bidCustomerDataTable || $.fn.DataTable.isDataTable('#customerList')) {
                    var dt = $('#customerList').DataTable();
                    $('#customerList tbody tr').each(function() {
                        var $tr = $(this);
                        var $viewBtn = $tr.find('.view-bid');
                        var rowBid = $viewBtn.data('bid');
                        if (typeof rowBid === 'string') {
                            try { rowBid = JSON.parse(rowBid); } catch(e) {}
                        }
                        if (rowBid && String(rowBid.Bcid) === String(res.bid.Bcid)) {
                            // Update anchor data-bid attribute
                            $viewBtn.attr('data-bid', JSON.stringify(res.bid));
                            $viewBtn.data('bid', res.bid);

                            // Update visible cells in DOM (Title, Type, Dept, Published, Opening, Submission)
                            var $titleCell = $tr.find('.bid-title-container');
                            if ($titleCell.length) {
                                $titleCell.text(res.bid.BidTitle).attr('title', res.bid.BidTitle);
                            } else {
                                $tr.find('td.col-bid-title').html('<div class="bid-title-container" title="' + escapeHtml(res.bid.BidTitle) + '">' + escapeHtml(res.bid.BidTitle) + '</div>');
                            }
                            dt.cell($tr.find('td:eq(1)')).invalidate();

                            if (res.bid.BidType) {
                                var typeLower = String(res.bid.BidType).toLowerCase().trim();
                                var typeBadge = (typeLower === 'oem') ? 'bg-danger text-white' : ((typeLower === 'service') ? 'bg-primary text-white' : 'bg-secondary text-white');
                                $tr.find('td:eq(2)').html('<span class="badge ' + typeBadge + '">' + escapeHtml(res.bid.BidType) + '</span>');
                                dt.cell($tr.find('td:eq(2)')).invalidate();
                            }
                            if (res.bid.BidDepartment !== undefined) {
                                $tr.find('td:eq(3)').text(res.bid.BidDepartment || '');
                                dt.cell($tr.find('td:eq(3)')).invalidate();
                            }

                            var pubTd = $tr.find('td:eq(4)');
                            pubTd.attr('data-order', ((res.bid.PublishedDate || '') + ' ' + (res.bid.tenderPublicationTime || '')).trim());
                            pubTd.text(formatDisplayDateTime(res.bid.PublishedDate, res.bid.tenderPublicationTime) || '-');
                            dt.cell(pubTd).invalidate();

                            var opTd = $tr.find('td:eq(5)');
                            opTd.attr('data-order', ((res.bid.OpeningDate || '') + ' ' + (res.bid.technicalBidOpeningTime || '')).trim());
                            opTd.text(formatDisplayDateTime(res.bid.OpeningDate, res.bid.technicalBidOpeningTime) || '-');
                            dt.cell(opTd).invalidate();

                            var subTd = $tr.find('td:eq(6)');
                            subTd.attr('data-order', ((res.bid.SubmissionDate || '') + ' ' + (res.bid.bidSubmissionEndTime || '')).trim());
                            subTd.text(formatDisplayDateTime(res.bid.SubmissionDate, res.bid.bidSubmissionEndTime) || '-');
                            dt.cell(subTd).invalidate();
                        }
                    });
                    dt.draw(false);
                }

                // Render back in read-only mode
                renderBidReadOnlyView();

                // Display success banner
                $('#bidAlertContainer').html(`
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:8px;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="feather-check-circle fs-16 text-success"></i>
                            <div><strong>Success!</strong> ${escapeHtml(res.message)}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);

                // Auto-scroll modal body to top to see banner
                $('.right-form-body').scrollTop(0);
            } else {
                $('#bidAlertContainer').html(`
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:8px;">
                        <i class="feather-alert-triangle me-2"></i> ${escapeHtml(res.message || 'Failed to update bid details.')}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }
        },
        error: function(xhr, status, err) {
            $saveBtns.prop('disabled', false).html('<i class="feather-save me-1"></i> Save Changes');
            $('#bidAlertContainer').html(`
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:8px;">
                    <i class="feather-alert-triangle me-2"></i> Error connecting to server: ${escapeHtml(err || status)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        }
    });
}

$(document).on('click', '#btnSaveBidDetails', function(e) {
    e.preventDefault();
    executeSaveBidDetails();
});

$(document).on('submit', '#editBidForm', function(e) {
    e.preventDefault();
    executeSaveBidDetails();
});
// CLOSE (button inside panel, backdrop, or Escape key)
$(document).on('click', '.close-btn, #closeAddForm, #closeFactSheetPanel, #closeTimelinePanel, .close-canvas, #rightFormOverlay', function () {
    $('.right-form').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});
$(document).on('keydown', function (e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        $('.right-form').removeClass('open');
        $('#rightFormOverlay').removeClass('show');
    }
});
var isTechEvaluatedMenu = <?= $isTechEvaluatedMenu ? 'true' : 'false'; ?>;

$(document).on("click", ".update-bid", function (e) {
    if (e) e.preventDefault();
    $('.right-form').removeClass('open');

    if (typeof clearModalValidation === 'function') {
        clearModalValidation();
    }

    let bidId = $(this).data("id");
    let currentStatus = $(this).data("current");
    let currentTech = $(this).data("tech");
    let currentPrice = $(this).data("price");
    let currentRemarks = $(this).data("remarks") || '';
    let currentActualDate = $(this).data("actualdate") || '';
    let currentMgmt = $(this).data("mgmt") || '0';
    let currentWhom = $(this).data("whom") || '';
    let currentLoa = $(this).data("loa");
    let currentLoaDoc = $(this).data("loadoc") || '';

    $("#Bcid").val(bidId);
    $("#rightForm").removeClass("open");
    $('#responseMessage').empty();

    // Reset Remarks and Date to empty for new status entry
    $('#addressInput').val('');
    $('#ActualDate').val('');
    $('#ApprovedByManagement').val('0');
    $('#ApprovedByWhom').val('');

    // Hide Technical Selected & Price Details initially
    $('#techSelectedGroup').hide();
    $('#TechnicalSelected').val('');
    $('#priceDetailsGroup').hide();
    $('#PriceDetails').val('');

    // Reset LOA fields
    $('#loaReceivedGroup').hide();
    $('#LoaReceived').val('');
    $('#loaDocumentGroup').hide();
    $('#LoaDocument').val('');
    $('#loaExistingDoc').hide().empty();

    // Reset BG fields
    $('#bgSubmittedGroup').hide();
    $('#BgSubmitted').val('');
    $('#bgDocumentGroup').hide();
    $('#BgDocument').val('');
    $('#bgExistingDoc').hide().empty();

    // Reset WO fields
    $('#woReceivedGroup').hide();
    $('#WoReceived').val('');
    $('#woDocumentGroup').hide();
    $('#WoDocument').val('');
    $('#woExistingDoc').hide().empty();

    // Reset Completion Certificate fields
    $('#ccReceivedGroup').hide();
    $('#CompletionCertificateReceived').val('');
    $('#ccDocumentGroup').hide();
    $('#CompletionCertificateDocument').val('');
    $('#ccExistingDoc').hide().empty();

    $("#bid_status").html('<option>Loading...</option>');
    $("#bidCanvas").addClass("open");
    $("#rightFormOverlay").addClass("show");

    $.ajax({
        url: "<?= base_url('bid/getStatuses'); ?>",
        type: "POST",
        data: { current_status: currentStatus },
        dataType: "json",
        success: function (res) {

            let options = '<option value="">Select Option</option>';

            $.each(res, function (i, status) {
                options += `<option value="${status.Sid}" 
                             data-name="${status.StatusName}">
                             ${status.DisplayName}
                            </option>`;
            });

            $("#bid_status").html(options);

            // Hide Management and conditional fields initially
            $('#ApprovedByManagement').closest('.mb-4').hide();
            $('#ApprovedByWhom').closest('.mb-4').hide();
            $('#techSelectedGroup').hide();
            $('#priceDetailsGroup').hide();
            $('#loaReceivedGroup').hide();
            $('#loaDocumentGroup').hide();
            $('#bgSubmittedGroup').hide();
            $('#bgDocumentGroup').hide();
            $('#woReceivedGroup').hide();
            $('#woDocumentGroup').hide();
            $('#ccReceivedGroup').hide();
            $('#ccDocumentGroup').hide();

            $("#bidCanvas").addClass("open");
            $("#rightFormOverlay").addClass("show");
        }
    });
});

$(document).on('change', '#bid_status', function(){

    if (typeof clearModalValidation === 'function') {
        clearModalValidation();
    }

    let statusName = $("#bid_status option:selected").data("name");
    let statusText = $.trim($("#bid_status option:selected").text());
    console.log(statusName);

    if(statusName === 'PQEvaluation'){
        $('#ApprovedByManagement').closest('.mb-4').show();
    } else {
        $('#ApprovedByManagement').closest('.mb-4').hide();
        $('#ApprovedByWhom').closest('.mb-4').hide();
    }

    // Technical Selected ONLY appears if the "Technical Evaluated" option is selected
    if (statusName === 'TechnicalEvaluated' || statusText.toLowerCase() === 'technical evaluated') {
        $('#techSelectedGroup').show();
    } else {
        $('#techSelectedGroup').hide();
        $('#TechnicalSelected').val('');
    }

    // Price Details ONLY appears if the "L1 Bids" option is selected
    if (statusName === 'LBids' || statusName === 'L1Bids' || statusText.toLowerCase().indexOf('l1') !== -1) {
        $('#priceDetailsGroup').show();
    } else {
        $('#priceDetailsGroup').hide();
        $('#PriceDetails').val('');
    }

    // LOA Received ONLY appears if the "LOA Received" option is selected
    if (statusName === 'LoaReceived' || statusText.toLowerCase().indexOf('loa') !== -1) {
        $('#loaReceivedGroup').slideDown();
        if ($('#LoaReceived').val() === '1') {
            $('#loaDocumentGroup').slideDown();
        }
    } else {
        $('#loaReceivedGroup').slideUp();
        $('#LoaReceived').val('');
        $('#loaDocumentGroup').slideUp();
        $('#LoaDocument').val('');
    }

    // BG Submitted ONLY appears if the "BG Submitted" option is selected
    if (statusName === 'BgSubmitted' || statusText.toLowerCase().indexOf('bg') !== -1) {
        $('#bgSubmittedGroup').slideDown();
        if ($('#BgSubmitted').val() === '1') {
            $('#bgDocumentGroup').slideDown();
        }
    } else {
        $('#bgSubmittedGroup').slideUp();
        $('#BgSubmitted').val('');
        $('#bgDocumentGroup').slideUp();
        $('#BgDocument').val('');
    }

    // WO Received ONLY appears if the "WO Received" option is selected
    if (statusName === 'WoReceived' || statusText.toLowerCase().indexOf('wo') !== -1) {
        $('#woReceivedGroup').slideDown();
        if ($('#WoReceived').val() === '1') {
            $('#woDocumentGroup').slideDown();
        }
    } else {
        $('#woReceivedGroup').slideUp();
        $('#WoReceived').val('');
        $('#woDocumentGroup').slideUp();
        $('#WoDocument').val('');
    }

    // Completion Certificate ONLY appears if the "Completion Certificate" option is selected
    if (statusName === 'CompletionCertificate' || statusText.toLowerCase().indexOf('completion') !== -1) {
        $('#ccReceivedGroup').slideDown();
        if ($('#CompletionCertificateReceived').val() === '1') {
            $('#ccDocumentGroup').slideDown();
        }
    } else {
        $('#ccReceivedGroup').slideUp();
        $('#CompletionCertificateReceived').val('');
        $('#ccDocumentGroup').slideUp();
        $('#CompletionCertificateDocument').val('');
    }
});

// Show document upload when LOA Received is Yes, hide when No
$(document).on('change', '#LoaReceived', function () {
    let val = $(this).val();
    if (val === '1') {
        $('#loaDocumentGroup').slideDown();
    } else {
        $('#loaDocumentGroup').slideUp();
        $('#LoaDocument').val('');
    }
});

// Show document upload when BG Submitted is Yes, hide when No
$(document).on('change', '#BgSubmitted', function () {
    let val = $(this).val();
    if (val === '1') {
        $('#bgDocumentGroup').slideDown();
    } else {
        $('#bgDocumentGroup').slideUp();
        $('#BgDocument').val('');
    }
});

// Show document upload when WO Received is Yes, hide when No
$(document).on('change', '#WoReceived', function () {
    let val = $(this).val();
    if (val === '1') {
        $('#woDocumentGroup').slideDown();
    } else {
        $('#woDocumentGroup').slideUp();
        $('#WoDocument').val('');
    }
});

// Show document upload when Completion Certificate is Yes, hide when No
$(document).on('change', '#CompletionCertificateReceived', function () {
    let val = $(this).val();
    if (val === '1') {
        $('#ccDocumentGroup').slideDown();
    } else {
        $('#ccDocumentGroup').slideUp();
        $('#CompletionCertificateDocument').val('');
    }
});

$("#updateBidForm").submit(function (e) {
    e.preventDefault();
    $('#UpdateBid').trigger('click');
});
$(document).on('change', '#ApprovedByManagement', function () {

    let value = $(this).val();

    if (value == 1) {

        $.ajax({
            url: "<?= base_url('bid/getManagementUsers') ?>",
            type: "GET",
            dataType: "json",
            success: function (response) {

                let options = '<option value="">Select</option>';

                $.each(response, function (key, user) {
                    options += '<option value="' + user.Baid + '">' + user.Approver_name + '</option>';
                });

                $('#ApprovedByWhom').html(options);
                $('#ApprovedByWhom').closest('.mb-4').show();
            }
        });

    } else {
        $('#ApprovedByWhom').closest('.mb-4').hide();
    }
});
// ── Modal Field Validation Helpers ──
function clearModalValidation() {
    $('#updateBidForm .is-invalid').removeClass('is-invalid');
    $('#updateBidForm .field-error-feedback').remove();
}

function showModalFieldError($elem, message) {
    $elem.addClass('is-invalid');
    var $group = $elem.closest('.mb-4');
    $group.find('.field-error-feedback').remove();
    $group.append('<span class="field-error-feedback"><i class="feather-alert-circle me-1"></i> ' + message + '</span>');
}

function validateUpdateBidModal() {
    clearModalValidation();
    var errors = [];

    // 1. Status
    var $status = $('#bid_status');
    var statusVal = $status.val();
    if (!statusVal || statusVal === '' || statusVal === '0' || statusVal === 'Loading...') {
        errors.push({ elem: $status, msg: 'Please select a status.' });
    }

    // 2. Date
    var $date = $('#ActualDate');
    var dateVal = $.trim($date.val());
    if (!dateVal) {
        errors.push({ elem: $date, msg: 'Please select a date.' });
    }

    // 3. Technical Selected (if visible)
    if ($('#techSelectedGroup').is(':visible')) {
        var $tech = $('#TechnicalSelected');
        var techVal = $tech.val();
        if (techVal === '' || techVal === null || techVal === undefined) {
            errors.push({ elem: $tech, msg: 'Please select whether Technical Selected (Yes / No).' });
        }
    }

    // 4. Price Details (if visible)
    if ($('#priceDetailsGroup').is(':visible')) {
        var $price = $('#PriceDetails');
        var priceVal = $.trim($price.val());
        if (!priceVal) {
            errors.push({ elem: $price, msg: 'Please enter price details.' });
        }
    }

    // 5. LOA Received (if visible)
    if ($('#loaReceivedGroup').is(':visible')) {
        var $loa = $('#LoaReceived');
        var loaVal = $loa.val();
        if (loaVal === '' || loaVal === null || loaVal === undefined) {
            errors.push({ elem: $loa, msg: 'Please select whether LOA was received (Yes / No).' });
        } else if (loaVal === '1' && $('#loaDocumentGroup').is(':visible')) {
            var $loaDoc = $('#LoaDocument');
            var loaFileInput = $loaDoc[0];
            var hasNewLoaFile = loaFileInput && loaFileInput.files && loaFileInput.files.length > 0;
            var hasExistingLoaDoc = $('#loaExistingDoc').is(':visible') && $('#loaExistingDoc').text().trim() !== '';
            if (!hasNewLoaFile && !hasExistingLoaDoc) {
                errors.push({ elem: $loaDoc, msg: 'Please upload the LOA document.' });
            }
        }
    }

    // 6. BG Submitted (if visible)
    if ($('#bgSubmittedGroup').is(':visible')) {
        var $bg = $('#BgSubmitted');
        var bgVal = $bg.val();
        if (bgVal === '' || bgVal === null || bgVal === undefined) {
            errors.push({ elem: $bg, msg: 'Please select whether BG was submitted (Yes / No).' });
        } else if (bgVal === '1' && $('#bgDocumentGroup').is(':visible')) {
            var $bgDoc = $('#BgDocument');
            var bgFileInput = $bgDoc[0];
            var hasNewBgFile = bgFileInput && bgFileInput.files && bgFileInput.files.length > 0;
            var hasExistingBgDoc = $('#bgExistingDoc').is(':visible') && $('#bgExistingDoc').text().trim() !== '';
            if (!hasNewBgFile && !hasExistingBgDoc) {
                errors.push({ elem: $bgDoc, msg: 'Please upload the BG document.' });
            }
        }
    }

    // 7. WO Received (if visible)
    if ($('#woReceivedGroup').is(':visible')) {
        var $wo = $('#WoReceived');
        var woVal = $wo.val();
        if (woVal === '' || woVal === null || woVal === undefined) {
            errors.push({ elem: $wo, msg: 'Please select whether WO was received (Yes / No).' });
        } else if (woVal === '1' && $('#woDocumentGroup').is(':visible')) {
            var $woDoc = $('#WoDocument');
            var woFileInput = $woDoc[0];
            var hasNewWoFile = woFileInput && woFileInput.files && woFileInput.files.length > 0;
            var hasExistingWoDoc = $('#woExistingDoc').is(':visible') && $('#woExistingDoc').text().trim() !== '';
            if (!hasNewWoFile && !hasExistingWoDoc) {
                errors.push({ elem: $woDoc, msg: 'Please upload the WO document.' });
            }
        }
    }

    // 8. Completion Certificate Received (if visible)
    if ($('#ccReceivedGroup').is(':visible')) {
        var $cc = $('#CompletionCertificateReceived');
        var ccVal = $cc.val();
        if (ccVal === '' || ccVal === null || ccVal === undefined) {
            errors.push({ elem: $cc, msg: 'Please select whether Completion Certificate was received (Yes / No).' });
        } else if (ccVal === '1' && $('#ccDocumentGroup').is(':visible')) {
            var $ccDoc = $('#CompletionCertificateDocument');
            var ccFileInput = $ccDoc[0];
            var hasNewCcFile = ccFileInput && ccFileInput.files && ccFileInput.files.length > 0;
            var hasExistingCcDoc = $('#ccExistingDoc').is(':visible') && $('#ccExistingDoc').text().trim() !== '';
            if (!hasNewCcFile && !hasExistingCcDoc) {
                errors.push({ elem: $ccDoc, msg: 'Please upload the Completion Certificate document.' });
            }
        }
    }

    // 9. Approved By Management (if visible)
    if ($('#ApprovedByManagement').closest('.mb-4').is(':visible')) {
        var $mgmt = $('#ApprovedByManagement');
        var mgmtVal = $mgmt.val();
        if (!mgmtVal || mgmtVal === '0' || mgmtVal === '') {
            errors.push({ elem: $mgmt, msg: 'Please select whether Approved By Management (Yes / No).' });
        } else if (mgmtVal === '1' && $('#ApprovedByWhom').closest('.mb-4').is(':visible')) {
            var $whom = $('#ApprovedByWhom');
            var whomVal = $whom.val();
            if (!whomVal || whomVal === '0' || whomVal === '') {
                errors.push({ elem: $whom, msg: 'Please select who approved this bid.' });
            }
        }
    }

    // 10. Remarks
    var $remarks = $('#addressInput');
    var remarksVal = $.trim($remarks.val());
    if (!remarksVal) {
        errors.push({ elem: $remarks, msg: 'Please enter remarks.' });
    }

    if (errors.length > 0) {
        // Apply inline error styles and messages
        for (var i = 0; i < errors.length; i++) {
            showModalFieldError(errors[i].elem, errors[i].msg);
        }

        // Focus & scroll first invalid element smoothly into view
        var $first = errors[0].elem;
        $first.focus();
        if ($first.length && $first[0].scrollIntoView) {
            $first[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return false;
    }

    return true;
}

// Clear field error in real-time when user types or changes input
$(document).on('input change', '#updateBidForm input, #updateBidForm select, #updateBidForm textarea', function () {
    $(this).removeClass('is-invalid');
    $(this).closest('.mb-4').find('.field-error-feedback').remove();
});

$(document).on('click', '#clear', function (e) {
    e.preventDefault();
    clearModalValidation();
    $('#ActualDate').val('');
    $('#ApprovedByManagement').val('0');
    $('#ApprovedByWhom').val('');
    $('#TechnicalSelected').val('');
    $('#PriceDetails').val('');
    $('#LoaReceived').val('');
    $('#LoaDocument').val('');
    $('#loaDocumentGroup').hide();
    $('#loaExistingDoc').hide().empty();
    $('#BgSubmitted').val('');
    $('#BgDocument').val('');
    $('#bgDocumentGroup').hide();
    $('#bgExistingDoc').hide().empty();
    $('#WoReceived').val('');
    $('#WoDocument').val('');
    $('#woDocumentGroup').hide();
    $('#woExistingDoc').hide().empty();
    $('#CompletionCertificateReceived').val('');
    $('#CompletionCertificateDocument').val('');
    $('#ccDocumentGroup').hide();
    $('#ccExistingDoc').hide().empty();
    $('#ApprovedByManagement').val('0');
    $('#ApprovedByWhom').val('');
    $('#addressInput').val('');
    $('#responseMessage').empty();
});

$(document).on('click', '#UpdateBid', function (e) {

    e.preventDefault();

    // Trigger validation strictly on Update button click
    if (!validateUpdateBidModal()) {
        return false;
    }

    // Use FormData to allow file upload
    let formData = new FormData($('#updateBidForm')[0]);

    $.ajax({
        url: "<?= base_url('bid/updateBidStatus') ?>",
        type: "POST",
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function () {
            $('#UpdateBid').prop('disabled', true).text('Updating...');
        },

        success: function (res) {

            if (res.status === true) {

                let successMsg = res.message || 'Status Updated Successfully';
                $('#responseMessage').html(
                    '<div class="alert alert-success">' + successMsg + '</div>'
                );

                setTimeout(function () {
                    location.reload();
                }, 1200);

            } else {

                $('#responseMessage').html(
                    '<div class="alert alert-danger">' + (res.message || 'Failed to update status') + '</div>'
                );

                $('#UpdateBid').prop('disabled', false).text('Update');
            }
        },

        error: function(xhr, status, error) {
            $('#responseMessage').html(
                '<div class="alert alert-danger">Error: ' + error + '</div>'
            );
            $('#UpdateBid').prop('disabled', false).text('Update');
        }
    });
});

// ── Fact Sheet Slide-out Modal Handlers ──
$(document).on('click', '.view-factsheet', function (e) {
    e.preventDefault();
    var bcid = $(this).data('bcid') || $(this).attr('data-bcid');

    // Close other panels if open
    $('#rightForm').removeClass('open');
    $('#bidCanvas').removeClass('open');
    $('#timelinePanel').removeClass('open');

    // Update full page link
    var tabUrl = "<?= base_url('bid/factsheet'); ?>?bcid=" + encodeURIComponent(bcid) + "&standalone=1";
    $('#btnOpenFactSheetTab').attr('href', tabUrl);

    // Show loading spinner
    $('#factSheetPanelBody').html(`
        <div class="text-center py-5 text-muted" style="min-height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <div class="spinner-border text-primary mb-3" role="status" style="width:2.2rem;height:2.2rem;"></div>
            <div class="fw-bold" style="font-size:14px;color:#1e293b;">Loading Tender Fact Sheet...</div>
            <div class="small text-muted mt-1">Retrieving sections from TenderFactSheet</div>
        </div>
    `);

    // Slide open right modal
    $('#factSheetPanel').addClass('open');
    $('#rightFormOverlay').addClass('show');

    // Fetch Fact Sheet via AJAX
    $.ajax({
        url: "<?= base_url('bid/factsheet'); ?>",
        type: "GET",
        data: { bcid: bcid, ajax: 1 },
        success: function (response) {
            $('#factSheetPanelBody').html(response);
        },
        error: function () {
            $('#factSheetPanelBody').html(`
                <div class="alert alert-danger m-4 border-0 shadow-sm" style="border-radius:8px;">
                    <div class="fw-bold"><i class="feather-alert-triangle me-1"></i> Unable to Load Fact Sheet</div>
                    <div class="small mt-1">No Fact Sheet was found for this bid.</div>
                </div>
            `);
        }
    });
});

$(document).on('click', '#closeFactSheetPanel', function (e) {
    e.preventDefault();
    $('#factSheetPanel').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});

// ── Timeline Intelligence Slide-out Modal Handlers ──
$(document).on('click', '.view-timeline', function (e) {
    e.preventDefault();
    var bcid = $(this).data('bcid') || $(this).attr('data-bcid');

    // Close other panels if open
    $('#rightForm').removeClass('open');
    $('#bidCanvas').removeClass('open');
    $('#factSheetPanel').removeClass('open');

    // Show loading spinner
    $('#timelinePanelBody').html(`
        <div class="text-center py-5 text-muted" style="min-height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <div class="spinner-border text-primary mb-3" role="status" style="width:2.2rem;height:2.2rem;"></div>
            <div class="fw-bold" style="font-size:14px;color:#1e293b;">Loading Timeline Intelligence...</div>
            <div class="small text-muted mt-1">Analyzing schedule milestones &amp; corrigenda</div>
        </div>
    `);

    // Slide open right modal
    $('#timelinePanel').addClass('open');
    $('#rightFormOverlay').addClass('show');

    // Fetch Timeline Intelligence via AJAX
    $.ajax({
        url: "<?= base_url('bid/timeline'); ?>",
        type: "GET",
        data: { bcid: bcid, ajax: 1 },
        success: function (response) {
            $('#timelinePanelBody').html(response);
        },
        error: function () {
            $('#timelinePanelBody').html(`
                <div class="alert alert-danger m-4 border-0 shadow-sm" style="border-radius:8px;">
                    <div class="fw-bold"><i class="feather-alert-triangle me-1"></i> Unable to Load Timeline Intelligence</div>
                    <div class="small mt-1">No timeline milestones or fact sheet found for this bid.</div>
                </div>
            `);
        }
    });
});

$(document).on('click', '#closeTimelinePanel', function (e) {
    e.preventDefault();
    $('#timelinePanel').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});

// Download Fact Sheet as PDF handler
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

$(document).on('click', '#btnDownloadFactSheetInModal', function (e) {
    e.preventDefault();
    downloadFactSheetAsPdf();
});

// DataTables filter integration for BidDetails
$(document).ready(function() {
    var table = $('#customerList').DataTable({ retrieve: true });

    // Dynamic S.No sequence handler for sorting, searching and pagination
    function updateSNo() {
        if (!table) return;
        var info = table.page.info();
        if (!info || info.recordsDisplay === 0) return;
        $('#customerList tbody tr').each(function(idx) {
            if ($(this).find('.dataTables_empty').length || $(this).hasClass('dataTables_empty') || $(this).find('td:first').hasClass('dataTables_empty')) return;
            $(this).find('td:first').text(info.start + idx + 1);
        });
    }

    table.on('draw.dt order.dt search.dt page.dt', function () {
        updateSNo();
    });

    updateSNo();
    var filterStart = null;
    var filterEnd = null;

    var dateInput = $('#bid-filter-date');
    if (dateInput.length && $.fn.daterangepicker) {
        dateInput.daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'DD-MM-YYYY' }
        });
        dateInput.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            filterStart = picker.startDate.startOf('day').toDate();
            filterEnd = picker.endDate.endOf('day').toDate();
            table.draw();
        });
        dateInput.on('cancel.daterangepicker', function() {
            $(this).val('');
            filterStart = null;
            filterEnd = null;
            table.draw();
        });
    }

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'customerList') return true;

        var filterType = ($('#bid-filter-type').val() || '').toLowerCase().trim();
        var filterDept = ($('#bid-filter-dept').val() || '').toLowerCase().trim();

        var rowType = (data[2] || '').toLowerCase().trim();
        var rowDept = (data[3] || '').toLowerCase().trim();

        if (filterType && rowType.indexOf(filterType) === -1) return false;
        if (filterDept && rowDept.indexOf(filterDept) === -1) return false;

        if (filterStart || filterEnd) {
            var subStr = (data[6] || '').trim();
            var pubStr = (data[4] || '').trim();
            var parseFilterDate = function(str) {
                if (!str || str === '-') return null;
                var mDmy = str.match(/^(\d{2})-(\d{2})-(\d{4})/);
                if (mDmy) return new Date(parseInt(mDmy[3], 10), parseInt(mDmy[2], 10) - 1, parseInt(mDmy[1], 10));
                var mYmd = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (mYmd) return new Date(parseInt(mYmd[1], 10), parseInt(mYmd[2], 10) - 1, parseInt(mYmd[3], 10));
                var ts = Date.parse(str);
                return isNaN(ts) ? null : new Date(ts);
            };
            var rowDate = parseFilterDate(subStr) || parseFilterDate(pubStr);
            if (rowDate) {
                if (filterStart && rowDate < filterStart) return false;
                if (filterEnd && rowDate > filterEnd) return false;
            }
        }
        return true;
    });

    function updateBidFilterBadge() {
        var count = 0;
        if ($('#bid-filter-type').val()) count++;
        if ($('#bid-filter-dept').val()) count++;
        if ($('#bid-filter-date').val()) count++;
        var $badge = $('#bid-filter-count');
        if (count > 0) {
            $badge.text(count).removeClass('d-none');
        } else {
            $badge.addClass('d-none');
        }
    }

    $('#bid-filter-type, #bid-filter-dept').on('change', function() {
        table.draw();
    });

    $('#btn-reset-bid-filters').on('click', function(e) {
        e.preventDefault();
        $('#bid-filter-type').val('');
        $('#bid-filter-dept').val('');
        $('#bid-filter-date').val('');
        filterStart = null;
        filterEnd = null;
        updateBidFilterBadge();
        table.draw();
    });

    // Click Bid Title in table to view full title clearly in a small modal
    $(document).on('click', '#customerList .bid-title-container', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var fullTitle = $(this).text().trim() || $(this).attr('title') || '-';
        var $tr = $(this).closest('tr');
        var rowBid = $tr.find('.view-bid').data('bid');
        if (typeof rowBid === 'string') {
            try { rowBid = JSON.parse(rowBid); } catch (err) { rowBid = null; }
        }

        var $meta = $('#bidTitleModalMeta');
        if (rowBid && rowBid.TenderId) {
            $meta.html('<span class="badge bg-soft-primary text-primary border border-primary" style="font-size:11px;">Tender ID: ' + escapeHtml(rowBid.TenderId) + '</span>').show();
        } else {
            $meta.empty().hide();
        }

        $('#bidTitleModalText').text(fullTitle);

        var modalEl = document.getElementById('bidTitleModal');
        if (modalEl) {
            if (modalEl.parentNode !== document.body) {
                document.body.appendChild(modalEl);
            }
            if (window.bootstrap && bootstrap.Modal) {
                var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.show();
            } else if (typeof $(modalEl).modal === 'function') {
                $(modalEl).modal('show');
            }
        }
    });
});
</script>