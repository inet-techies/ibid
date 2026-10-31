 <?php $employee_det = $this->session->userdata('logged_in');
   $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }

  $isTechEvaluatedMenu = (isset($PageTitle) && strtolower($PageTitle) === 'technical evaluated') 
      || (strtolower($this->uri->segment(2)) === 'technicalevaluated');
?>

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
                                                <th>#</th>
                                                <th>Bid Title</th>
                                                <th>Bid Type</th>
                                                <th>Department</th>
                                                <th>Published Date</th> 
                                                <th>Opening Date</th> 
                                                <th>Submission Date</th> 
                                                <th>Bid Status</th>
                                                <th>Creation Date</th>
                                                <th>Remarks</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                         <tbody>
                                        <?php
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
                                                        <td><?= $i; ?></td>
                                                        <td><?= $val['BidTitle'] ?></td>
                                                        <td>
                                                            <span class="badge <?= $badgeClass ?>">
                                                                <?= ucfirst($val['BidType']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $val['BidDepartment'] ?></td>
                                                        <td><?= $val['PublishedDate'] ?></td>
                                                        <td><?= $val['OpeningDate'] ?></td>
                                                        <td><?= $val['SubmissionDate'] ?></td>
                                                        <td>
                                                            <?php
                                                            $statusBadge = !empty($val['BadgeClass']) ? $val['BadgeClass'] : 'bg-secondary';
                                                            ?>
                                                            <span class="badge <?= $statusBadge ?>">
                                                                <?= $val['DisplayName'] ?>
                                                            </span>
                                                        </td>
                                                        <td><?= (!empty($val['CreationTs']) && strtotime($val['CreationTs'])) ? date('d M Y, h:i A', strtotime($val['CreationTs'])) : '-' ?></td>
                                                        <td>
                                                            <span class="d-inline-block text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($val['Remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= !empty($val['Remarks']) ? htmlspecialchars($val['Remarks'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md openAddForm view-bid"
                                                                    data-bid='<?= json_encode($val); ?>'
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
                                            } else {
                                            ?>
                                                <tr>
                                                    <td colspan="11" class="text-center text-muted py-4">
                                                        <i class="feather-alert-circle me-1"></i>
                                                        No details found
                                                    </td>
                                                </tr>
                                            <?php
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
    <div class="right-form-header">
        <h5>Bid Details</h5>
        <button type="button" class="close-btn" id="closeAddForm">&times;</button>
    </div>

    <div class="right-form-body">
               <!-- YOUR EXISTING FORM STARTS -->
        <div id="userModalBody"></div>
               <!-- YOUR FORM ENDS -->
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

<style>
.factsheet-panel,
.timeline-panel {
    position: fixed;
    top: 0;
    right: -920px;
    width: 880px !important;
    max-width: 95vw !important;
    height: 100vh;
    background: #ffffff;
    box-shadow: -5px 0 25px rgba(0, 0, 0, 0.2);
    transition: right 0.32s cubic-bezier(0.25, 0.8, 0.25, 1);
    z-index: 1060;
    display: flex;
    flex-direction: column;
}
.factsheet-panel.open,
.timeline-panel.open {
    right: 0 !important;
}

/* Update Bid Modal Validation Styles */
#updateBidForm .is-invalid,
#updateBidForm .is-invalid + .select2-container .select2-selection {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
}
#updateBidForm .field-error-feedback {
    color: #dc2626;
    font-size: 11.5px;
    font-weight: 500;
    margin-top: 4px;
    display: block;
    line-height: 1.3;
}
</style>

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
  <script src="<?=$theme_path?>/js/jquery-1.11.1.min.js"></script>

  
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

$(document).on('click', '.view-bid', function () {

    var bid = $(this).data('bid');

    var html = `
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            <table class="table table-bordered text-nowrap wrap-no-text mb-0">
                <tbody>

                    <tr>
                        <th width="40%">Bid Status</th>
                        <td>
                            ${
                                bid.DisplayName 
                                ? `<span class="badge ${bid.BadgeClass ?? 'bg-secondary'}">
                                       ${bid.DisplayName}
                                   </span>`
                                : '-'
                            }
                        </td>
                    </tr>
                    <tr>
                        <th width="40%">Tender ID</th>
                        <td>${bid.TenderId ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Bid Title</th>
                        <td>${bid.BidTitle ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Department</th>
                        <td>${bid.BidDepartment ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Bid Type</th>
                        <td>${bid.BidType ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Estimated Value</th>
                        <td>₹ ${bid.EstimatedValue ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>EMD Value</th>
                        <td>₹ ${bid.EmdValue ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Published Date</th>
                        <td>${bid.PublishedDate ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Submission Date</th>
                        <td>${bid.SubmissionDate ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Opening Date</th>
                        <td>${bid.OpeningDate ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Tender Portal</th>
                        <td>
                            ${bid.TenderPortalUrl 
                                ? `<a href="${bid.TenderPortalUrl}" target="_blank">View Portal</a>` 
                                : '-'
                            }
                        </td>
                    </tr>

                    <tr>
                        <th>NAS Path</th>
                        <td>${bid.NASPath ?? '-'}</td>
                    </tr>

                    <tr>
                        <th>Remarks</th>
                        <td>${bid.Remarks ?? '-'}</td>
                    </tr>

                    ${(bid.TechnicalSelected !== null && bid.TechnicalSelected !== undefined && bid.TechnicalSelected !== '') ? `
                    <tr>
                        <th>Technical Selected</th>
                        <td>
                            ${bid.TechnicalSelected == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}

                    ${(bid.PriceDetails !== null && bid.PriceDetails !== undefined && bid.PriceDetails !== '') ? `
                    <tr>
                        <th>Price Details</th>
                        <td>${bid.PriceDetails}</td>
                    </tr>` : ''}

                    ${(bid.LoaReceived !== null && bid.LoaReceived !== undefined && bid.LoaReceived !== '') ? `
                    <tr>
                        <th>LOA Received</th>
                        <td>
                            ${bid.LoaReceived == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}

                    ${(bid.LoaDocumentPath) ? `
                    <tr>
                        <th>LOA Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${bid.LoaDocumentPath}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <i class="feather-file-text"></i> View LOA Document
                            </a>
                        </td>
                    </tr>` : ''}

                    ${(bid.BgSubmitted !== null && bid.BgSubmitted !== undefined && bid.BgSubmitted !== '') ? `
                    <tr>
                        <th>BG Submitted</th>
                        <td>
                            ${bid.BgSubmitted == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}

                    ${(bid.BgDocumentPath) ? `
                    <tr>
                        <th>BG Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${bid.BgDocumentPath}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <i class="feather-file-text"></i> View BG Document
                            </a>
                        </td>
                    </tr>` : ''}

                    ${(bid.WoReceived !== null && bid.WoReceived !== undefined && bid.WoReceived !== '') ? `
                    <tr>
                        <th>WO Received</th>
                        <td>
                            ${bid.WoReceived == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}

                    ${(bid.WoDocumentPath) ? `
                    <tr>
                        <th>WO Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${bid.WoDocumentPath}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <i class="feather-file-text"></i> View WO Document
                            </a>
                        </td>
                    </tr>` : ''}

                    ${(bid.CompletionCertificateReceived !== null && bid.CompletionCertificateReceived !== undefined && bid.CompletionCertificateReceived !== '') ? `
                    <tr>
                        <th>Completion Certificate Received</th>
                        <td>
                            ${bid.CompletionCertificateReceived == 1 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>'}
                        </td>
                    </tr>` : ''}

                    ${(bid.CompletionCertificateDocumentPath) ? `
                    <tr>
                        <th>Completion Certificate Document</th>
                        <td>
                            <a href="<?= base_url(); ?>${bid.CompletionCertificateDocumentPath}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <i class="feather-file-text"></i> View Completion Certificate
                            </a>
                        </td>
                    </tr>` : ''}

                    ${(bid.ActualDate) ? `
                    <tr>
                        <th>Status Date</th>
                        <td>${bid.ActualDate}</td>
                    </tr>` : ''}

                    ${(bid.CreationTs) ? `
                    <tr>
                        <th>Creation Date</th>
                        <td>${formatBidDateTime(bid.CreationTs)}</td>
                    </tr>` : ''}

                    <tr>
                        <th>Fact Sheet</th>
                        <td>
                            <button type="button" class="btn btn-sm text-white fw-bold d-inline-flex align-items-center py-1 px-3 view-factsheet" data-bcid="${bid.Bcid}" style="background:linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);border:none;border-radius:6px;box-shadow:0 2px 6px rgba(14,165,233,0.3);">
                                <i class="feather-file-text me-1 mr-1"></i> View Fact Sheet
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <th>Timeline Intelligence</th>
                        <td>
                            <button type="button" class="btn btn-sm text-white fw-bold d-inline-flex align-items-center py-1 px-3 view-timeline" data-bcid="${bid.Bcid}" style="background:linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);border:none;border-radius:6px;box-shadow:0 2px 6px rgba(99,102,241,0.3);">
                                <i class="feather-clock me-1 mr-1"></i> View Timeline Intelligence
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>
    `;

    $('#userModalBody').html(html);

    $('#rightForm').addClass('open');
    $('#rightFormOverlay').addClass('show');
});
// CLOSE (button inside panel)
$(document).on('click', '#closeAddForm', function () {
    $('#rightForm').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});

// CLOSE (click outside overlay)
$(document).on('click', '#closeAddForm, .close-canvas, #rightFormOverlay', function () {
    $('.right-form').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});
var isTechEvaluatedMenu = <?= $isTechEvaluatedMenu ? 'true' : 'false'; ?>;

$(document).on("click", ".update-bid", function () {

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
    if (statusName === 'LBids' || statusText.toLowerCase().indexOf('l1') !== -1) {
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
</script>