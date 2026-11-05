<?php $employee_det = $this->session->userdata('logged_in');
  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
?>

<!-- Hidden file input for document parsing -->
<input type="file" id="docParseFileInput" accept=".pdf,.docx,.doc,.txt,.rtf" style="position:absolute;top:-9999px;left:-9999px;opacity:0;width:1px;height:1px;">

<!-- Parsing status bar (hidden by default) -->
<div id="docParseStatusBar" style="display:none;" class="mb-3">
  <div class="alert alert-info d-flex align-items-center gap-3 mb-0" style="border-radius:10px;border:none;background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);border-left:4px solid #3b82f6 !important;">
    <div class="spinner-border spinner-border-sm text-primary me-2 mr-2" role="status" style="flex-shrink:0;"></div>
    <div>
      <strong class="text-primary">Parsing document...</strong>
      <span class="text-muted small ms-2 ml-2" id="docParseFileName"></span>
    </div>
  </div>
</div>

<!-- Auto-fill success alert (hidden by default) -->
<div id="docParseSuccessAlert" style="display:none;" class="mb-3">
  <div class="alert alert-success d-flex align-items-start gap-2 mb-0" style="border-radius:10px;border:none;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-left:4px solid #22c55e !important;">
    <i class="feather-check-circle text-success mt-1 me-2 mr-2" style="font-size:18px;flex-shrink:0;"></i>
    <div style="flex:1;">
      <strong class="text-success">Document Parsed & Form Auto-Filled!</strong>
      <div id="docParseFilledFields" class="text-muted small mt-1"></div>
    </div>
    <button type="button" class="btn btn-sm btn-link text-success p-0 ms-2 ml-2" onclick="document.getElementById('docParseSuccessAlert').style.display='none';" style="flex-shrink:0;">
      <i class="feather-x" style="font-size:16px;"></i>
    </button>
  </div>
</div>

<!-- Auto-fill error alert (hidden by default) -->
<div id="docParseErrorAlert" style="display:none;" class="mb-3">
  <div class="alert alert-danger d-flex align-items-start gap-2 mb-0" style="border-radius:10px;border:none;border-left:4px solid #ef4444 !important;">
    <i class="feather-alert-circle text-danger mt-1 me-2 mr-2" style="font-size:18px;flex-shrink:0;"></i>
    <div style="flex:1;">
      <strong class="text-danger">Parsing Failed</strong>
      <div id="docParseErrorMsg" class="text-muted small mt-1"></div>
    </div>
    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2 ml-2" onclick="document.getElementById('docParseErrorAlert').style.display='none';" style="flex-shrink:0;">
      <i class="feather-x" style="font-size:16px;"></i>
    </button>
  </div>
</div>

<!-- Irrelevant Document Alert (hidden by default) -->
<div id="docParseIrrelevantAlert" style="display:none;" class="mb-3">
  <div class="alert alert-warning d-flex align-items-start gap-3 mb-0" style="border-radius:10px;border:none;background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%);border-left:5px solid #d97706 !important;box-shadow:0 4px 14px rgba(217,119,6,0.18);">
    <div style="font-size:24px;color:#d97706;line-height:1;flex-shrink:0;">
      <i class="feather-alert-triangle"></i>
    </div>
    <div style="flex:1;">
      <div class="d-flex align-items-center gap-2">
        <strong style="color:#b45309;font-size:15px;">Irrelevant Document Detected</strong>
        <span class="badge" style="background:#dc2626;color:#ffffff;font-size:10.5px;padding:3px 7px;font-weight:700;letter-spacing:0.5px;">NOT A TENDER</span>
      </div>
      <div id="docParseIrrelevantMsg" style="color:#92400e;font-size:13px;margin-top:4px;line-height:1.5;"></div>
    </div>
    <button type="button" class="btn btn-sm btn-link p-0 ms-2 ml-2" onclick="document.getElementById('docParseIrrelevantAlert').style.display='none';" style="color:#b45309;flex-shrink:0;">
      <i class="feather-x" style="font-size:18px;"></i>
    </button>
  </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body lead-status">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="fw-bold mb-0 me-4">
                        <span class="d-block mb-2">Create Bid</span>
                        <span class="fs-12 fw-normal text-muted text-truncate-1-line">Create and register a new bid by capturing tender details, documents, and key submission parameters</span>
                    </h5>
                    <div>
                        <!-- Upload & Parse Button -->
                        <button type="button" id="btnUploadParseDoc"
                            onclick="var fi=document.getElementById('docParseFileInput');if(fi){fi.value='';fi.click();}"
                            class="btn btn-primary shadow-sm fw-bold d-inline-flex align-items-center"
                            style="border-radius:8px;padding:9px 20px;background:linear-gradient(135deg,#4f46e5 0%,#3b82f6 100%);border:none;font-size:13.5px;box-shadow:0 4px 14px rgba(79,70,229,0.35);">
                            <i class="feather-upload-cloud me-2 mr-2" style="font-size:16px;"></i>
                            <span>Upload &amp; Parse Document</span>
                        </button>
                    </div>
                </div>
            </div>
            <hr class="mt-0">

            <?php
            if($this->session->flashdata('true')){
            ?>
             <div class="alert alert-success mx-4">
               <?php echo $this->session->flashdata('true'); ?>
              </div>
          <?php
          } else if($this->session->flashdata('err')){
          ?>
           <div class="alert alert-danger mx-4">
             <?php echo $this->session->flashdata('err'); ?>
           </div>
          <?php } ?>

            <form method="post" enctype="multipart/form-data" action="<?php echo $this->config->item('base_url'); ?>bid/AddBidData" onsubmit="return validate();" autocomplete="off">
            <input type="hidden" id="attachedDocumentPath" name="attachedDocumentPath" value="">
            <input type="hidden" id="attachedDocumentName" name="attachedDocumentName" value="">
            <input type="hidden" id="factSheetId" name="factSheetId" value="">
            <input type="hidden" id="factSheetUrl" name="factSheetUrl" value="">
            <input type="hidden" id="Bcid" name="Bcid" value="<?= htmlspecialchars($editBid['Bcid'] ?? '') ?>">
            <div class="card-body general-info">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label class="fw-semibold">Bid Type: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <select class="form-control" data-select2-selector="category" id="bidTypeInput" name="bidTypeInput">
                             <option value="0">Select Option</option>
                            <option data-category="oe">OEM</option>
                            <option data-category="se">Service</option>
                        </select>
                    </div>
                </div><!---------Bid Type----------->
                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidTitleInput" class="fw-semibold">Bid Title: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-mail"></i></div>
                            <input type="text" class="form-control" id="bidTitleInput" name="bidTitleInput" placeholder="Enter Bid Title">
                        </div>
                    </div>
                </div><!---------Bid Title----------->
                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidDepartmentInput" class="fw-semibold">Department/Organization: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-briefcase"></i></div>
                            <input type="text" class="form-control" id="bidDepartmentInput" name="bidDepartmentInput" placeholder="Enter Department/Organization">
                        </div>
                    </div>
                </div><!---------Bid Department/Organization----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidStateInput" class="fw-semibold">State: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-map-pin"></i></div>
                            <select class="form-control" id="bidStateInput" name="bidStateInput">
                                <option value="">Select State</option>
                                <option>Andhra Pradesh</option>
                                <option>Arunachal Pradesh</option>
                                <option>Assam</option>
                                <option>Bihar</option>
                                <option>Chhattisgarh</option>
                                <option>Goa</option>
                                <option>Gujarat</option>
                                <option>Haryana</option>
                                <option>Himachal Pradesh</option>
                                <option>Jharkhand</option>
                                <option>Karnataka</option>
                                <option>Kerala</option>
                                <option>Madhya Pradesh</option>
                                <option>Maharashtra</option>
                                <option>Manipur</option>
                                <option>Meghalaya</option>
                                <option>Mizoram</option>
                                <option>Nagaland</option>
                                <option>Odisha</option>
                                <option>Punjab</option>
                                <option>Rajasthan</option>
                                <option>Sikkim</option>
                                <option>Tamil Nadu</option>
                                <option>Telangana</option>
                                <option>Tripura</option>
                                <option>Uttar Pradesh</option>
                                <option>Uttarakhand</option>
                                <option>West Bengal</option>
                                <option>Andaman and Nicobar Islands</option>
                                <option>Chandigarh</option>
                                <option>Dadra and Nagar Haveli and Daman and Diu</option>
                                <option>Delhi</option>
                                <option>Jammu and Kashmir</option>
                                <option>Ladakh</option>
                                <option>Lakshadweep</option>
                                <option>Puducherry</option>
                            </select>
                        </div>
                    </div>
                </div><!---------State----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidCustomerNameInput" class="fw-semibold">Customer Name: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-user"></i></div>
                            <input type="text" class="form-control" id="bidCustomerNameInput" name="bidCustomerNameInput" placeholder="Enter Customer / Client Name">
                        </div>
                    </div>
                </div><!---------Customer Name----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidCustomerPhoneInput" class="fw-semibold">Customer Phone: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-phone"></i></div>
                            <input type="text" class="form-control" id="bidCustomerPhoneInput" name="bidCustomerPhoneInput" placeholder="Enter Customer Phone Number" maxlength="20">
                        </div>
                    </div>
                </div><!---------Customer Phone----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidCustomerAddressInput" class="fw-semibold">Customer Address: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-home"></i></div>
                            <textarea class="form-control" id="bidCustomerAddressInput" name="bidCustomerAddressInput" cols="30" rows="3" placeholder="Enter Customer Address"></textarea>
                        </div>
                    </div>
                </div><!---------Customer Address----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidCategoryInput" class="fw-semibold">Category: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-tag"></i></div>
                            <input type="text" class="form-control" id="bidCategoryInput" name="bidCategoryInput" placeholder="Enter Tender Category (e.g. IT, Civil, Electrical)">
                        </div>
                    </div>
                </div><!---------Category----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidEstimatedInput" class="fw-semibold">Estimated Tender Value: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="fa fa-rupee"></i></div>
                            <input type="text" class="form-control" id="bidEstimatedInput" name="bidEstimatedInput" placeholder="Enter Estimated Tender Value">
                        </div>
                         <small class="text-danger fw-bold mt-1 d-block" id="EstemdInWords"></small>
                    </div>
                </div><!---------Bid ETV----------->
                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidEmdInput" class="fw-semibold">Bid EMD Value: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text"><i class="fa fa-rupee"></i></div>
                            <input type="text" class="form-control" id="bidEmdInput" name="bidEmdInput" placeholder="Enter EMD Value">
                        </div>
                        <small class="text-primary fw-bold mt-1 d-block" id="emdInWords"></small>
                    </div>
                </div><!---------Bid EMD----------->
                 <!-- 1. Tender Publication -->
                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="startDate" class="fw-semibold">1. Tender Publication: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="startDate" name="PublishedDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="tenderPublicationTime" name="tenderPublicationTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------1. Tender Publication----------->

                <!-- 2. Document Sale Start -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="documentSaleStartDate" class="fw-semibold">2. Document Sale / Download Start:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="documentSaleStartDate" name="documentSaleStartDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="documentSaleStartTime" name="documentSaleStartTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------2. Document Sale Start----------->

                <!-- 3. Document Sale End -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="documentSaleEndDate" class="fw-semibold">3. Document Sale / Download End:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="documentSaleEndDate" name="documentSaleEndDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="documentSaleEndTime" name="documentSaleEndTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------3. Document Sale End----------->

                <!-- 4. Pre-bid Query Start -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="prebidQueryStartDate" class="fw-semibold">4. Pre-Bid Clarification / Query Start:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="prebidQueryStartDate" name="prebidQueryStartDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="prebidQueryStartTime" name="prebidQueryStartTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------4. Pre-bid Query Start----------->

                <!-- 5. Pre-bid Query End -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="prebidQueryEndDate" class="fw-semibold">5. Pre-Bid Clarification / Query End:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="prebidQueryEndDate" name="prebidQueryEndDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="prebidQueryEndTime" name="prebidQueryEndTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------5. Pre-bid Query End----------->

                <!-- 6. Pre-bid Meeting -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="prebidMeetingDate" class="fw-semibold">6. Pre-Bid Meeting:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="prebidMeetingDate" name="prebidMeetingDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="prebidMeetingTime" name="prebidMeetingTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------6. Pre-bid Meeting----------->

                <!-- 7. Pre-bid Response Deadline -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="prebidResponseDate" class="fw-semibold">7. Pre-Bid Response / Clarification:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="prebidResponseDate" name="prebidResponseDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="prebidResponseTime" name="prebidResponseTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------7. Pre-bid Response Deadline----------->

                <!-- 8. Bid Submission Start -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidSubmissionStartDate" class="fw-semibold">8. Bid Submission Start:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="bidSubmissionStartDate" name="bidSubmissionStartDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="bidSubmissionStartTime" name="bidSubmissionStartTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------8. Bid Submission Start----------->

                <!-- 9. Bid Submission End -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="dueDate" class="fw-semibold">9. Bid Submission End / Deadline: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="dueDate" name="SubmissionDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="bidSubmissionEndTime" name="bidSubmissionEndTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------9. Bid Submission End----------->

                <!-- 10. PQ Bid Opening -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="pqBidOpeningDate" class="fw-semibold">10. Pre-Qualification (PQ) Bid Opening:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="pqBidOpeningDate" name="pqBidOpeningDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="pqBidOpeningTime" name="pqBidOpeningTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------10. PQ Bid Opening----------->

                <!-- 11. Technical Bid Opening -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="dateofBirth" class="fw-semibold">11. Technical Bid Opening Date: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="dateofBirth" name="OpeningDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="technicalBidOpeningTime" name="technicalBidOpeningTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------11. Technical Bid Opening----------->

                <!-- 12. Financial Bid Opening -->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="financialBidOpeningDate" class="fw-semibold">12. Financial / Commercial Bid Opening:</label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                            <div class="input-group-text date-picker-icon" style="cursor:pointer;"><i class="feather-calendar"></i></div>
                            <input type="date" class="form-control tender-schedule-date" id="financialBidOpeningDate" name="financialBidOpeningDate" placeholder="YYYY-MM-DD" style="cursor:pointer;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 time-group">
                            <span class="text-muted fw-semibold small d-inline-flex align-items-center time-icon" style="font-size:12px;cursor:pointer;"><i class="feather-clock text-secondary me-1"></i>Time:</span>
                            <input type="time" class="form-control form-control-sm" id="financialBidOpeningTime" name="financialBidOpeningTime" style="max-width:160px;height:32px;font-size:12.5px;cursor:pointer;" placeholder="HH:MM">
                        </div>
                    </div>
                </div><!---------12. Financial Bid Opening----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidSummaryInput" class="fw-semibold">Keywords for Summary: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                        	 <div class="input-group-text"><i class="feather-type"></i></div>
                            <textarea class="form-control" id="bidSummaryInput" name="bidSummaryInput" cols="30" rows="5" placeholder="Keywords for Summary"></textarea>
                        </div>
                    </div>
                </div><!---------Bid Keywords for Summary----------->

                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidTenderPortalInput" class="fw-semibold">Tender Portal URL: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                             <div class="input-group-text"><i class="feather-at-sign"></i></div>
                               <input type="text" class="form-control" id="bidTenderPortalInput" name="bidTenderPortalInput" placeholder="Enter Tender Portal URL">
                        </div>
                    </div>
                </div><!---------Bid Tender Portal URL----------->
                 <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidTenderIDInput" class="fw-semibold">Tender ID: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                             <div class="input-group-text"><i class="feather-slack"></i></div>
                               <input type="text" class="form-control" id="bidTenderIDInput" name="bidTenderIDInput" placeholder="Enter Tender ID">
                        </div>
                    </div>
                </div><!---------Bid Tender ID----------->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidNASIDInput" class="fw-semibold">NAS Path: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                             <div class="input-group-text"><i class="feather-folder"></i></div>
                               <input type="text" class="form-control" id="bidNASIDInput" name="bidNASIDInput" placeholder="Enter NAS Path">
                        </div>
                    </div>
                </div><!---------NAS Path----------->
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <label for="bidRemarksInput" class="fw-semibold">Remarks: <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-lg-8">
                        <div class="input-group">
                             <div class="input-group-text"><i class="feather-type"></i></div>
                            <textarea class="form-control" id="bidRemarksInput" name="bidRemarksInput" cols="30" rows="5" placeholder="Remarks"></textarea>
                        </div>
                    </div>
                </div><!---------Remarks----------->

                 <div class="d-flex justify-content-center align-items-center gap-2 mt-3 flex-wrap">
                    <button type="reset" class="btn btn-md bg-danger text-white" id="btnClearBidForm">Clear</button>
                    <button type="button" class="btn btn-md text-white fw-bold d-inline-flex align-items-center" id="btnViewFactSheet" title="View Document Fact Sheet" style="display:none !important;background:linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);border:none;border-radius:6px;padding:8px 18px;box-shadow:0 3px 10px rgba(14,165,233,0.3);">
                        <i class="feather-file-text me-2 mr-2" style="font-size:15px;"></i>
                        <span>Fact Sheet</span>
                    </button>
                    <button type="submit" class="btn btn-md btn-primary">Create Bid</button>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
<!---[Main Row]---->

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

<div id="factSheetOverlay" class="right-form-overlay"></div>


<script>
(function() {
  // ── Helpers ────────────────────────────────────────────────────────────────
  function setField(id, value) {
    var el = document.getElementById(id);
    if (!el || !value) return false;
    el.value = value;
    // Trigger native events so any existing listeners pick up the change
    el.dispatchEvent(new Event('input', {bubbles: true}));
    el.dispatchEvent(new Event('change', {bubbles: true}));
    return true;
  }

  function convertNumberToWords(num) {
    var a = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
             'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    var b = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    if ((num = num.toString()).length > 9) return 'Overflow';
    var n = ('000000000' + num).substr(-9).match(/.{1,2}/g);
    var str = '';
    var crore = parseInt(n[0]), lakh = parseInt(n[1]), thousand = parseInt(n[2]), hundred = parseInt(n[3]), last = parseInt(n[4]);
    if (crore)    str += (crore    < 20 ? a[crore]    : b[Math.floor(crore/10)]    + ' ' + a[crore%10])    + ' Crore ';
    if (lakh)     str += (lakh     < 20 ? a[lakh]     : b[Math.floor(lakh/10)]     + ' ' + a[lakh%10])     + ' Lakh ';
    if (thousand) str += (thousand < 20 ? a[thousand] : b[Math.floor(thousand/10)] + ' ' + a[thousand%10]) + ' Thousand ';
    if (hundred)  str += (hundred  < 20 ? a[hundred]  : b[Math.floor(hundred/10)]  + ' ' + a[hundred%10])  + ' Hundred ';
    if (last)     str += (str !== '' ? 'and ' : '') + (last < 20 ? a[last] : b[Math.floor(last/10)] + ' ' + a[last%10]);
    return str.trim();
  }

  // ── Helper: Format date as YYYY-MM-DD ──
  function formatToIsoDate(raw) {
    if (!raw) return '';
    raw = raw.toString().trim();

    // If already YYYY-MM-DD or starts with YYYY-MM-DD
    var isoMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (isoMatch) return isoMatch[1] + '-' + isoMatch[2] + '-' + isoMatch[3];

    // Strip time portions like " at 12:00", " 15:00:00", "T10:00:00"
    var cleanDate = raw.replace(/\s+(?:at\s+)?\d{1,2}:\d{2}(?::\d{2})?(?:\s*(?:AM|PM|am|pm))?.*/i, '').trim();

    // Check DD/MM/YYYY or DD-MM-YYYY or DD.MM.YYYY
    var m = cleanDate.match(/^(\d{1,2})\s*[\.\/\-]\s*(\d{1,2})\s*[\.\/\-]\s*(\d{2,4})/);
    if (m) {
      var y = parseInt(m[3], 10);
      if (y < 100) y += (y < 50 ? 2000 : 1900);
      var mon = parseInt(m[2], 10);
      var d = parseInt(m[1], 10);
      return y + '-' + (mon < 10 ? '0' : '') + mon + '-' + (d < 10 ? '0' : '') + d;
    }

    // Check named months like "23 Oct 2026", "23 October 2026", "23-Oct-2026"
    var namedMatch = cleanDate.match(/^(\d{1,2})[-\s/]+([a-zA-Z]{3,9})[-\s/]+(\d{2,4})/);
    if (namedMatch) {
      var months = {jan:1, feb:2, mar:3, apr:4, may:5, jun:6, jul:7, aug:8, sep:9, oct:10, nov:11, dec:12};
      var mKey = namedMatch[2].substring(0, 3).toLowerCase();
      if (months[mKey]) {
        var dy = parseInt(namedMatch[1], 10);
        var mn = months[mKey];
        var yr = parseInt(namedMatch[3], 10);
        if (yr < 100) yr += (yr < 50 ? 2000 : 1900);
        return yr + '-' + (mn < 10 ? '0' : '') + mn + '-' + (dy < 10 ? '0' : '') + dy;
      }
    }

    // Try standard JS Date parsing as last resort
    var parsed = new Date(cleanDate);
    if (!isNaN(parsed.getTime())) {
      var yr = parsed.getFullYear();
      var mn = parsed.getMonth() + 1;
      var dy = parsed.getDate();
      return yr + '-' + (mn < 10 ? '0' : '') + mn + '-' + (dy < 10 ? '0' : '') + dy;
    }

    return raw;
  }

  // ── Helper: Set verified input field value & attach confidence badge ──
  function setTimeField(id, val) {
    if (!val) return;
    var el = document.getElementById(id);
    if (!el) return;
    var raw = val.toString().trim();
    // Normalize time to HH:MM 24-hour format required by HTML5 <input type="time">
    var ampmMatch = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM|am|pm)$/i);
    var normalized = '';
    if (ampmMatch) {
      var h = parseInt(ampmMatch[1], 10);
      var m = ampmMatch[2];
      var p = ampmMatch[3].toUpperCase();
      if (p === 'PM' && h < 12) h += 12;
      if (p === 'AM' && h === 12) h = 0;
      normalized = (h < 10 ? '0' : '') + h + ':' + m;
    } else if (/^\d{1,2}:\d{2}(?::\d{2})?$/.test(raw)) {
      var parts = raw.split(':');
      var h = parseInt(parts[0], 10);
      var m = parts[1];
      normalized = (h < 10 ? '0' : '') + h + ':' + m;
    } else if (/^\d{4}$/.test(raw)) {
      normalized = raw.substring(0, 2) + ':' + raw.substring(2);
    } else {
      normalized = raw;
    }
    el.value = normalized;
    el.dispatchEvent(new Event('input', {bubbles: true}));
    el.dispatchEvent(new Event('change', {bubbles: true}));
  }

  function setVerifiedField(id, val, confidence, isDate, fieldLabel) {
    // CRITICAL EXCLUSIONS: Department and Estimated Tender Value must NEVER be auto-populated
    if (id === 'bidDepartmentInput' || id === 'bidEstimatedInput') {
      return false;
    }

    var el = document.getElementById(id);
    if (!el) return false;

    // For Bid Title: do NOT overwrite if the user has already entered a title manually
    if (id === 'bidTitleInput' && el.value && el.value.trim() !== '') {
      return false;
    }

    // Remove any existing badge
    var existingBadge = document.getElementById(id + '_confBadge');
    if (existingBadge) existingBadge.remove();

    var score = parseInt(confidence, 10);
    if (isNaN(score)) score = 90;

    // Only mandatory fields show "manual entry required" if missing or confidence < 70
    // Optional schedule date fields do NOT show any warning badge if not found in document
    var isMandatoryField = (id === 'startDate' || id === 'dueDate' || id === 'dateofBirth' || id === 'bidTenderIDInput' || id === 'bidTitleInput');

    if (score < 70 || !val) {
      if (isMandatoryField) {
        var notFoundBadge = document.createElement('small');
        notFoundBadge.id = id + '_confBadge';
        notFoundBadge.className = 'text-muted ms-2 mt-1 d-block';
        notFoundBadge.style.fontSize = '11px';
        notFoundBadge.innerHTML = '<i class="feather-info me-1"></i>Not found in document &mdash; manual entry required';
        if (el.parentNode && el.parentNode.parentNode) {
          el.parentNode.parentNode.appendChild(notFoundBadge);
        }
      }
      return false;
    }

    var finalVal = isDate ? formatToIsoDate(val) : val;
    el.value = finalVal;
    el.dispatchEvent(new Event('input', {bubbles: true}));
    el.dispatchEvent(new Event('change', {bubbles: true}));

    // Verify value was actually accepted by the HTML input
    if (el.value !== finalVal && el.value === '') {
      return false;
    }

    // Attach confidence badge
    var badge = document.createElement('small');
    badge.id = id + '_confBadge';
    badge.className = 'ms-2 mt-1 d-block';
    badge.style.fontSize = '11px';

    if (score >= 90) {
      badge.className += ' text-success fw-semibold';
      badge.innerHTML = '<i class="feather-check-circle me-1"></i>Verified (' + score + '% confidence)';
    } else {
      badge.className += ' text-warning fw-semibold';
      badge.innerHTML = '<i class="feather-alert-triangle me-1"></i>Review recommended (' + score + '% confidence)';
    }
    if (el.parentNode && el.parentNode.parentNode) {
      el.parentNode.parentNode.appendChild(badge);
    }

    return true;
  }

  function showStatus(show, fileName) {
    var bar = document.getElementById('docParseStatusBar');
    if (!bar) return;
    bar.style.display = show ? 'block' : 'none';
    if (fileName) {
      var fn = document.getElementById('docParseFileName');
      if (fn) fn.textContent = '"' + fileName + '"';
    }
  }

  function showSuccess(fields, resp) {
    document.getElementById('docParseSuccessAlert').style.display = 'block';
    document.getElementById('docParseErrorAlert').style.display = 'none';
    var irrAlert = document.getElementById('docParseIrrelevantAlert');
    if (irrAlert) irrAlert.style.display = 'none';

    var el = document.getElementById('docParseFilledFields');
    if (el) {
      var msg = 'Auto-filled ' + fields.length + ' validated fields: ' + (fields.length ? fields.join(', ') : 'None') + '.';
      if (resp && resp.diagnostics) {
        var d = resp.diagnostics;
        msg += ' (Universal Engine: ' + d.fields_found + ' verified, ' + d.fields_requiring_review + ' for review in ' + d.extraction_duration_ms + ' ms)';
        if (d.corrigendum_detected) {
          msg += ' [Corrigendum Document]';
        }
      }
      el.textContent = msg;
    }
    // Smooth scroll to form
    var titleEl = document.getElementById('bidTitleInput');
    if (titleEl) {
      setTimeout(function() {
        titleEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 200);
    }
  }

  function showError(msg) {
    document.getElementById('docParseErrorAlert').style.display = 'block';
    document.getElementById('docParseSuccessAlert').style.display = 'none';
    var irrAlert = document.getElementById('docParseIrrelevantAlert');
    if (irrAlert) irrAlert.style.display = 'none';

    var el = document.getElementById('docParseErrorMsg');
    if (el) el.textContent = msg || 'An error occurred during parsing.';
  }

  function showIrrelevantDocError(msg) {
    var irrAlert = document.getElementById('docParseIrrelevantAlert');
    var successAlert = document.getElementById('docParseSuccessAlert');
    var errAlert = document.getElementById('docParseErrorAlert');
    var attachBadge = document.getElementById('attachedDocumentBadge');

    if (successAlert) successAlert.style.display = 'none';
    if (errAlert) errAlert.style.display = 'none';
    if (attachBadge) attachBadge.style.display = 'none';

    // Clear hidden attachment fields
    setField('attachedDocumentPath', '');
    setField('attachedDocumentName', '');
    setField('factSheetId', '');
    setField('factSheetUrl', '');

    if (irrAlert) {
      var msgEl = document.getElementById('docParseIrrelevantMsg');
      if (msgEl) {
        msgEl.textContent = msg || 'Irrelevant Document: The uploaded file does not contain tender or bidding information. Please upload a valid tender document.';
      }
      irrAlert.style.display = 'block';
      setTimeout(function() {
        irrAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 100);
    } else {
      showError(msg);
    }
  }


  function autoFillForm(resp) {
    var fields = resp.fields || {};
    var ai     = resp.ai_summary || {};
    var filled = [];

    // ── Tender Document Attachment ──
    var filePath = resp.file_path || '';
    var fileName = resp.file_name || resp.filename || '';
    if (filePath) {
      setField('attachedDocumentPath', filePath);
      setField('attachedDocumentName', fileName);
      var attachBadge = document.getElementById('attachedDocumentBadge');
      var nameEl = document.getElementById('attachedDocName');
      var sizeEl = document.getElementById('attachedDocSize');
      var classBadge = document.getElementById('attachedDocClassification');
      var txtPathEl = document.getElementById('attachedDocTxtPath');
      if (attachBadge) {
        if (nameEl) nameEl.textContent = fileName;
        if (sizeEl && resp.filesize) sizeEl.textContent = '(' + resp.filesize + ')';
        if (classBadge) {
          var cls = (resp.classification || '').toLowerCase();
          if (cls === 'oem') {
            classBadge.className = 'badge ms-2 ml-2';
            classBadge.style.cssText = 'display:inline-block;font-size:11px;background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;';
            classBadge.textContent = 'OEM Document';
          } else if (cls === 'service') {
            classBadge.className = 'badge ms-2 ml-2';
            classBadge.style.cssText = 'display:inline-block;font-size:11px;background:#f0fdf4;color:#15803d;border:1px solid #86efac;';
            classBadge.textContent = 'Service Document';
          } else {
            classBadge.className = 'badge ms-2 ml-2';
            classBadge.style.cssText = 'display:inline-block;font-size:11px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;';
            classBadge.textContent = 'Unclassified Document';
          }
        }
        if (txtPathEl) {
          if (resp.txt_file) {
            txtPathEl.textContent = 'Extracted Text: ' + resp.txt_file;
            txtPathEl.style.display = 'block';
          } else {
            txtPathEl.style.display = 'none';
          }
        }
        attachBadge.style.display = 'flex';
        filled.push('Document Attached');
      }
    }

    // ── Fact Sheet Linking ──
    var fsId = resp.fsid || resp.tfsid || resp.factsheet_id || '';
    var fsUrl = resp.factsheet_url || '';
    if (fsId) {
      setField('factSheetId', fsId.toString());
    }
    if (fsUrl) {
      setField('factSheetUrl', fsUrl);
      var fsLink = document.getElementById('attachedDocFactSheetLink');
      if (fsLink) {
        fsLink.href = fsUrl;
        fsLink.style.display = 'none';
      }
    }

    // ── CRITICAL ARCHITECTURAL RULE ──
    // Department and Estimated Tender Value must NEVER be auto-populated.
    // They remain 100% manual entry fields.

    // ── Tender Title ──
    var titleData = fields.tender_title || {};
    var titleVal  = titleData.value || resp.bid_title || ai.bid_title || '';
    var titleConf = titleData.confidence !== undefined ? titleData.confidence : (titleVal ? 85 : 0);
    if (titleVal && setVerifiedField('bidTitleInput', titleVal, titleConf, false, 'Bid Title')) {
      filled.push('Bid Title');
    }

    // ── Tender Reference ID ──
    var refData = fields.tender_reference || {};
    var refVal  = refData.value || ai.ref_no || ai.ref_code || ai.tender_id || '';
    var refConf = refData.confidence !== undefined ? refData.confidence : (refVal ? 95 : 0);
    if (refVal && setVerifiedField('bidTenderIDInput', refVal, refConf, false, 'Tender ID')) {
      filled.push('Tender ID');
    }

    // ── EMD Value ──
    var emdData = fields.emd_value || {};
    var emdVal  = emdData.value || (ai.emd_value || '').toString().trim();
    var emdConf = emdData.confidence !== undefined ? emdData.confidence : (emdVal ? 95 : 0);
    if (emdVal && emdVal !== '0' && setVerifiedField('bidEmdInput', emdVal, emdConf, false, 'EMD Value')) {
      filled.push('EMD Value');
      var emdNum = emdVal.toString().replace(/,/g, '');
      if (!isNaN(emdNum) && emdNum !== '') {
        var ewE = document.getElementById('emdInWords');
        if (ewE) ewE.innerHTML = convertNumberToWords(emdNum) + ' Only';
      }
    }

    // ── 12 TENDER ACTIVITY MILESTONES (Dates & Times) ──

    // 1. Tender Publication
    var pubData = fields.published_date || {};
    var pubVal  = pubData.value || ai.tender_publication_date || ai.tenderPublicationDate || ai.published_date || '';
    var pubConf = pubData.confidence !== undefined ? pubData.confidence : (pubVal ? 90 : 0);
    if (pubVal && setVerifiedField('startDate', pubVal, pubConf, true, 'Tender Publication')) {
      filled.push('Tender Publication Date');
    }
    var pubTime = ai.tender_publication_time || ai.tenderPublicationTime || '';
    if (pubTime) setTimeField('tenderPublicationTime', pubTime);

    // 2. Document Sale Start
    var dssVal = ai.document_sale_start_date || ai.documentSaleStartDate || '';
    if (dssVal && setVerifiedField('documentSaleStartDate', dssVal, 85, true, 'Doc Sale Start')) {
      filled.push('Document Sale Start');
    }
    var dssTime = ai.document_sale_start_time || ai.documentSaleStartTime || '';
    if (dssTime) setTimeField('documentSaleStartTime', dssTime);

    // 3. Document Sale End
    var dseVal = ai.document_sale_end_date || ai.documentSaleEndDate || '';
    if (dseVal && setVerifiedField('documentSaleEndDate', dseVal, 85, true, 'Doc Sale End')) {
      filled.push('Document Sale End');
    }
    var dseTime = ai.document_sale_end_time || ai.documentSaleEndTime || '';
    if (dseTime) setTimeField('documentSaleEndTime', dseTime);

    // 4. Pre-bid Query Start
    var pqsVal = ai.prebid_query_start_date || ai.prebidQueryStartDate || '';
    if (pqsVal && setVerifiedField('prebidQueryStartDate', pqsVal, 85, true, 'Pre-bid Query Start')) {
      filled.push('Pre-bid Query Start');
    }
    var pqsTime = ai.prebid_query_start_time || ai.prebidQueryStartTime || '';
    if (pqsTime) setTimeField('prebidQueryStartTime', pqsTime);

    // 5. Pre-bid Query End
    var pqeVal = ai.prebid_query_end_date || ai.prebidQueryEndDate || '';
    if (pqeVal && setVerifiedField('prebidQueryEndDate', pqeVal, 85, true, 'Pre-bid Query End')) {
      filled.push('Pre-bid Query End');
    }
    var pqeTime = ai.prebid_query_end_time || ai.prebidQueryEndTime || '';
    if (pqeTime) setTimeField('prebidQueryEndTime', pqeTime);

    // 6. Pre-bid Meeting
    var pbmVal = ai.prebid_meeting_date || ai.prebidMeetingDate || '';
    if (pbmVal && setVerifiedField('prebidMeetingDate', pbmVal, 85, true, 'Pre-bid Meeting')) {
      filled.push('Pre-bid Meeting');
    }
    var pbmTime = ai.prebid_meeting_time || ai.prebidMeetingTime || '';
    if (pbmTime) setTimeField('prebidMeetingTime', pbmTime);

    // 7. Pre-bid Response Deadline
    var pbrVal = ai.prebid_response_date || ai.prebidResponseDate || '';
    if (pbrVal && setVerifiedField('prebidResponseDate', pbrVal, 85, true, 'Pre-bid Response')) {
      filled.push('Pre-bid Response Deadline');
    }
    var pbrTime = ai.prebid_response_time || ai.prebidResponseTime || '';
    if (pbrTime) setTimeField('prebidResponseTime', pbrTime);

    // 8. Bid Submission Start
    var bssVal = ai.bid_submission_start_date || ai.bidSubmissionStartDate || '';
    if (bssVal && setVerifiedField('bidSubmissionStartDate', bssVal, 85, true, 'Bid Submission Start')) {
      filled.push('Bid Submission Start');
    }
    var bssTime = ai.bid_submission_start_time || ai.bidSubmissionStartTime || '';
    if (bssTime) setTimeField('bidSubmissionStartTime', bssTime);

    // 9. Bid Submission End / Deadline
    var subData = fields.submission_date || {};
    var subVal  = subData.value || ai.bid_submission_end_date || ai.bidSubmissionEndDate || ai.submission_date || '';
    if (!subVal && ai.submission_deadline) {
      subVal = ai.submission_deadline.indexOf(' at ') !== -1 ? ai.submission_deadline.split(' at ')[0].trim() : ai.submission_deadline;
    }
    var subConf = subData.confidence !== undefined ? subData.confidence : (subVal ? 95 : 0);
    if (subVal && setVerifiedField('dueDate', subVal, subConf, true, 'Submission End Date')) {
      filled.push('Submission End Date');
    }
    var subTime = ai.bid_submission_end_time || ai.bidSubmissionEndTime || ai.submission_time || '';
    if (subTime) setTimeField('bidSubmissionEndTime', subTime);

    // 10. PQ Bid Opening
    var pqbVal = ai.pq_bid_opening_date || ai.pqBidOpeningDate || '';
    if (pqbVal && setVerifiedField('pqBidOpeningDate', pqbVal, 85, true, 'PQ Bid Opening')) {
      filled.push('PQ Bid Opening');
    }
    var pqbTime = ai.pq_bid_opening_time || ai.pqBidOpeningTime || '';
    if (pqbTime) setTimeField('pqBidOpeningTime', pqbTime);

    // 11. Technical Bid Opening
    var opData = fields.technical_opening_date || {};
    var opVal  = opData.value || ai.technical_bid_opening_date || ai.technicalBidOpeningDate || ai.opening_date || '';
    if (opVal && opVal.indexOf(' at ') !== -1) opVal = opVal.split(' at ')[0].trim();
    var opConf = opData.confidence !== undefined ? opData.confidence : (opVal ? 90 : 0);
    if (opVal && setVerifiedField('dateofBirth', opVal, opConf, true, 'Technical Bid Opening')) {
      filled.push('Technical Bid Opening');
    }
    var opTime = ai.technical_bid_opening_time || ai.technicalBidOpeningTime || ai.technical_opening_time || '';
    if (opTime) setTimeField('technicalBidOpeningTime', opTime);

    // 12. Financial Bid Opening
    var fboVal = ai.financial_bid_opening_date || ai.financialBidOpeningDate || ai.financial_opening_date || '';
    if (fboVal && setVerifiedField('financialBidOpeningDate', fboVal, 85, true, 'Financial Bid Opening')) {
      filled.push('Financial Bid Opening');
    }
    var fboTime = ai.financial_bid_opening_time || ai.financialBidOpeningTime || ai.financial_opening_time || '';
    if (fboTime) setTimeField('financialBidOpeningTime', fboTime);

    // ── Keywords for Summary ──
    var kwData = fields.keywords_summary || {};
    var kw     = kwData.value || ai.keywords || '';
    if (!kw && ai.highlights && ai.highlights.length) {
      var kwParts = [];
      ai.highlights.forEach(function(h) {
        var idx = h.indexOf(': ');
        if (idx !== -1) kwParts.push(h.substring(idx + 2));
        else kwParts.push(h);
      });
      kw = kwParts.join(', ');
    }
    var kwConf = kwData.confidence !== undefined ? kwData.confidence : (kw ? 80 : 0);
    if (kw && setVerifiedField('bidSummaryInput', kw, kwConf, false, 'Keywords')) {
      filled.push('Keywords');
    }

    // ── Tender Portal URL ──
    var portData = fields.tender_portal || {};
    var portal   = portData.value || ai.portal || ai.portal_or_mode || '';
    var portConf = portData.confidence !== undefined ? portData.confidence : (portal ? 90 : 0);
    if (portal && setVerifiedField('bidTenderPortalInput', portal, portConf, false, 'Portal URL')) {
      filled.push('Portal URL');
    }

    return filled;
  }

  function uploadAndParse(file) {
    if (!file) return;

    var ext = file.name.split('.').pop().toLowerCase();
    var allowed = ['pdf', 'docx', 'doc', 'txt', 'rtf'];
    if (allowed.indexOf(ext) === -1) {
      showError('Unsupported file type (.' + ext + '). Please upload PDF, DOCX, DOC, RTF or TXT.');
      return;
    }

    // Show spinner, hide old alerts
    document.getElementById('docParseSuccessAlert').style.display = 'none';
    document.getElementById('docParseErrorAlert').style.display   = 'none';
    var irrAlert = document.getElementById('docParseIrrelevantAlert');
    if (irrAlert) irrAlert.style.display = 'none';

    
    var statusBar = document.getElementById('docParseStatusBar');
    var cardBody  = document.querySelector('.card-body.general-info');
    if (statusBar && cardBody) {
      cardBody.insertBefore(statusBar, cardBody.firstChild);
    }
    showStatus(true, file.name);

    // Disable button
    var btn = document.getElementById('btnUploadParseDoc');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2 mr-2" role="status"></span>Parsing...';
    }

    var formData = new FormData();
    formData.append('file', file);

    var uploadUrl = '<?= $this->config->item('base_url') ?>document_parser/extract';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl, true);
    xhr.onload = function() {
      showStatus(false);
      // Restore button
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="feather-upload-cloud me-2 mr-2" style="font-size:16px;"></i><span>Upload &amp; Parse Document</span>';
      }
      // Reset file input so same file can be re-uploaded
      document.getElementById('docParseFileInput').value = '';

      var resp;
      try {
        resp = JSON.parse(xhr.responseText);
      } catch(e) {
        showError('Server returned an invalid response. Please try again.');
        return;
      }

      if (resp && resp.redirect) {
        window.location.href = resp.redirect;
        return;
      }

      // Immediate Irrelevant Document Detection
      if (resp && (resp.error_type === 'irrelevant_document' || resp.classification === 'irrelevant' || resp.is_relevant === false)) {
        showIrrelevantDocError(resp.message || 'Irrelevant Document: The uploaded document is not a tender or RFP. Please upload a valid tender document.');
        return;
      }

      if (resp && resp.status === 'success') {
        var filled = autoFillForm(resp);
        showSuccess(filled, resp);
      } else {
        showError((resp && resp.message) ? resp.message : 'Parsing failed. Please try a different document.');
      }
    };
    xhr.onerror = function() {
      showStatus(false);
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="feather-upload-cloud me-2 mr-2" style="font-size:16px;"></i><span>Upload &amp; Parse Document</span>';
      }
      showError('Network error. Please check your connection and try again.');
    };
    xhr.send(formData);
  }

  
  function initDocParserEvents() {
    var btn       = document.getElementById('btnUploadParseDoc');
    var fileInput = document.getElementById('docParseFileInput');

    if (btn && fileInput && !btn._boundUpload) {
      btn._boundUpload = true;
      btn.onclick = function(e) {
        if (e) e.preventDefault();
        fileInput.value = ''; // reset so same file triggers change
        fileInput.click();
      };

      fileInput.onchange = function() {
        if (this.files && this.files.length > 0) {
          uploadAndParse(this.files[0]);
        }
      };
    }
  }

  // ── Universal Date & Time Picker Enabler ──
  function setupDateAndTimePickers() {
    var dateIds = [
      'startDate', 'dueDate', 'dateofBirth',
      'documentSaleStartDate', 'documentSaleEndDate',
      'prebidQueryStartDate', 'prebidQueryEndDate',
      'prebidMeetingDate', 'prebidResponseDate',
      'bidSubmissionStartDate', 'pqBidOpeningDate',
      'financialBidOpeningDate'
    ];

    dateIds.forEach(function(id) {
      var el = document.getElementById(id);
      if (!el) return;

      // Ensure HTML input type is explicitly 'date'
      if (el.getAttribute('type') !== 'date') {
        try { el.type = 'date'; } catch(e){}
      }

      // If vanillajs Datepicker attached itself to this native date input, destroy it
      if (el.datepicker && typeof el.datepicker.destroy === 'function') {
        try { el.datepicker.destroy(); } catch(err){}
      }
      el.classList.remove('datepicker-input');

      el.style.cursor = 'pointer';

      // Clicking anywhere on the date input opens the native picker
      if (!el._hasPickerClick) {
        el._hasPickerClick = true;
        el.addEventListener('click', function(e) {
          if (typeof this.showPicker === 'function') {
            try { this.showPicker(); } catch(err) {}
          }
        });
      }

      // Clicking the calendar icon in the input-group opens the picker
      var group = el.closest('.input-group');
      if (group) {
        var icon = group.querySelector('.date-picker-icon, .input-group-text');
        if (icon && !icon._hasPickerClick) {
          icon._hasPickerClick = true;
          icon.style.cursor = 'pointer';
          icon.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof el.showPicker === 'function') {
              try { el.showPicker(); } catch(err) { el.focus(); }
            } else {
              el.focus();
            }
          });
        }
      }
    });

    // Time inputs
    var timeIds = [
      'tenderPublicationTime', 'documentSaleStartTime', 'documentSaleEndTime',
      'prebidQueryStartTime', 'prebidQueryEndTime', 'prebidMeetingTime',
      'prebidResponseTime', 'bidSubmissionStartTime', 'bidSubmissionEndTime',
      'pqBidOpeningTime', 'technicalBidOpeningTime', 'financialBidOpeningTime'
    ];

    timeIds.forEach(function(id) {
      var el = document.getElementById(id);
      if (!el) return;

      el.style.cursor = 'pointer';

      if (!el._hasTimeClick) {
        el._hasTimeClick = true;
        el.addEventListener('click', function() {
          if (typeof this.showPicker === 'function') {
            try { this.showPicker(); } catch(err) {}
          }
        });
      }

      var parent = el.closest('.time-group') || el.parentElement;
      if (parent) {
        var icon = parent.querySelector('.time-icon, .feather-clock');
        if (icon && !icon._hasTimeClick) {
          icon._hasTimeClick = true;
          icon.style.cursor = 'pointer';
          icon.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof el.showPicker === 'function') {
              try { el.showPicker(); } catch(err) { el.focus(); }
            } else {
              el.focus();
            }
          });
        }
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initDocParserEvents();
      setupDateAndTimePickers();
    });
  } else {
    initDocParserEvents();
    setupDateAndTimePickers();
  }

  window.addEventListener('load', setupDateAndTimePickers);
  setTimeout(setupDateAndTimePickers, 150);
  setTimeout(setupDateAndTimePickers, 500);

  document.addEventListener('DOMContentLoaded', function() {
    initDocParserEvents();
    setupDateAndTimePickers();

    // EMD words on manual input
    var emdEl = document.getElementById('bidEmdInput');
    if (emdEl) {
      emdEl.addEventListener('input', function() {
        var num = this.value.replace(/,/g, '');
        var el = document.getElementById('emdInWords');
        if (!el) return;
        el.innerHTML = (num !== '' && !isNaN(num)) ? convertNumberToWords(num) + ' Only' : '';
      });
    }

    // Estimated value words on manual input
    var estEl = document.getElementById('bidEstimatedInput');
    if (estEl) {
      estEl.addEventListener('input', function() {
        var num = this.value.replace(/,/g, '');
        var el = document.getElementById('EstemdInWords');
        if (!el) return;
        el.innerHTML = (num !== '' && !isNaN(num)) ? convertNumberToWords(num) + ' Only' : '';
      });
    }

    // Remove attached document handler
    var removeDocBtn = document.getElementById('btnRemoveAttachedDoc');
    if (removeDocBtn) {
      removeDocBtn.addEventListener('click', function(e) {
        e.preventDefault();
        setField('attachedDocumentPath', '');
        setField('attachedDocumentName', '');
        var nasEl = document.getElementById('bidNASIDInput');
        if (nasEl && (nasEl.value.indexOf('uploads/tender_documents/') !== -1 || nasEl.value.indexOf('uploads/tender/') !== -1)) {
          nasEl.value = '';
        }
        var classBadge = document.getElementById('attachedDocClassification');
        if (classBadge) classBadge.style.display = 'none';
        var txtPathEl = document.getElementById('attachedDocTxtPath');
        if (txtPathEl) txtPathEl.style.display = 'none';
        setField('factSheetId', '');
        setField('factSheetUrl', '');
        var fsLink = document.getElementById('attachedDocFactSheetLink');
        if (fsLink) {
          fsLink.style.display = 'none';
          fsLink.href = 'javascript:void(0);';
        }
        var attachBadge = document.getElementById('attachedDocumentBadge');
        if (attachBadge) attachBadge.style.display = 'none';
      });
    }

    // ── Comprehensive Bid Form Validation (All Fields) ──
    window.validateBidForm = function(showInline, showSummary) {
      var errors = [];
      var firstEl = null;

      function addErr(el, msg) {
        errors.push({ el: el, msg: msg });
        if (!firstEl) firstEl = el;
        if (showInline && el) {
          el.classList.add('is-invalid');
          var group = el.closest('.input-group');
          if (group) group.classList.add('has-validation-error');
          
          if (el.id === 'bidTypeInput') {
            var s2 = el.nextElementSibling;
            if (s2 && s2.classList.contains('select2-container')) {
              s2.style.border = '1px solid #ef4444';
              s2.style.borderRadius = '6px';
            }
          }

          var col = el.closest('.col-lg-8');
          if (col) {
            var feed = col.querySelector('.field-error-feedback');
            if (!feed) {
              feed = document.createElement('div');
              feed.className = 'field-error-feedback text-danger fw-semibold mt-1';
              col.appendChild(feed);
            }
            feed.textContent = msg;
            feed.style.display = 'block';
          }
        }
      }

      function removeErr(el) {
        if (!el) return;
        el.classList.remove('is-invalid');
        var group = el.closest('.input-group');
        if (group) group.classList.remove('has-validation-error');
        if (el.id === 'bidTypeInput') {
          var s2 = el.nextElementSibling;
          if (s2 && s2.classList.contains('select2-container')) {
            s2.style.border = '';
          }
        }
        var col = el.closest('.col-lg-8');
        if (col) {
          var feed = col.querySelector('.field-error-feedback');
          if (feed) feed.style.display = 'none';
        }
      }

      // 1. Bid Type
      var elType = document.getElementById('bidTypeInput');
      if (elType) {
        var valType = elType.value ? elType.value.trim() : '';
        if (valType === '' || valType === '0') {
          addErr(elType, 'Please select a Bid Type (OEM or Service).');
        } else {
          removeErr(elType);
        }
      }

      // 2. Bid Title
      var elTitle = document.getElementById('bidTitleInput');
      if (elTitle) {
        var valTitle = elTitle.value ? elTitle.value.trim() : '';
        if (valTitle === '') {
          addErr(elTitle, 'Please enter the Bid Title.');
        } else {
          removeErr(elTitle);
        }
      }

      // 3. Department
      var elDept = document.getElementById('bidDepartmentInput');
      if (elDept) {
        var valDept = elDept.value ? elDept.value.trim() : '';
        if (valDept === '') {
          addErr(elDept, 'Please enter Department/Organization.');
        } else {
          removeErr(elDept);
        }
      }

      // 4. Estimated Tender Value
      var elEst = document.getElementById('bidEstimatedInput');
      if (elEst) {
        var rawEst = elEst.value ? elEst.value.replace(/,/g, '').trim() : '';
        if (rawEst === '' || isNaN(rawEst) || parseFloat(rawEst) <= 0) {
          addErr(elEst, 'Please enter a valid positive Estimated Tender Value (e.g. 500000).');
        } else {
          removeErr(elEst);
        }
      }

      // 5. Bid EMD Value
      var elEmd = document.getElementById('bidEmdInput');
      if (elEmd) {
        var rawEmd = elEmd.value ? elEmd.value.replace(/,/g, '').trim() : '';
        if (rawEmd === '' || isNaN(rawEmd) || parseFloat(rawEmd) < 0) {
          addErr(elEmd, 'Please enter a valid EMD Value (enter 0 if exempt).');
        } else {
          removeErr(elEmd);
        }
      }

      // 6. Bid Published Date
      var elPub = document.getElementById('startDate');
      if (elPub) {
        var valPub = elPub.value ? elPub.value.trim() : '';
        if (valPub === '') {
          addErr(elPub, 'Please select the Bid Published Date.');
        } else {
          removeErr(elPub);
        }
      }

      // 7. Bid Submission End Date
      var elSub = document.getElementById('dueDate');
      if (elSub) {
        var valSub = elSub.value ? elSub.value.trim() : '';
        if (valSub === '') {
          addErr(elSub, 'Please select the Bid Submission End Date.');
        } else if (elPub && elPub.value && new Date(valSub) < new Date(elPub.value)) {
          addErr(elSub, 'Submission Date cannot be earlier than Published Date.');
        } else {
          removeErr(elSub);
        }
      }

      // 8. Bid Opening Date
      var elOpen = document.getElementById('dateofBirth');
      if (elOpen) {
        var valOpen = elOpen.value ? elOpen.value.trim() : '';
        if (valOpen === '') {
          addErr(elOpen, 'Please select the Bid Opening Date.');
        } else if (elSub && elSub.value && new Date(valOpen) < new Date(elSub.value)) {
          addErr(elOpen, 'Opening Date cannot be earlier than Submission Date.');
        } else {
          removeErr(elOpen);
        }
      }

      // 9. Summary Keywords
      var elSumm = document.getElementById('bidSummaryInput');
      if (elSumm) {
        var valSumm = elSumm.value ? elSumm.value.trim() : '';
        if (valSumm === '') {
          addErr(elSumm, 'Please enter Keywords for Summary.');
        } else {
          removeErr(elSumm);
        }
      }

      // 10. Tender Portal URL
      var elPortal = document.getElementById('bidTenderPortalInput');
      if (elPortal) {
        var valPortal = elPortal.value ? elPortal.value.trim() : '';
        if (valPortal === '') {
          addErr(elPortal, 'Please enter the Tender Portal URL.');
        } else {
          removeErr(elPortal);
        }
      }

      // 11. Tender ID
      var elTenderId = document.getElementById('bidTenderIDInput');
      if (elTenderId) {
        var valTenderId = elTenderId.value ? elTenderId.value.trim() : '';
        if (valTenderId === '') {
          addErr(elTenderId, 'Please enter the Tender ID.');
        } else {
          removeErr(elTenderId);
        }
      }

      // 12. NAS Path
      var elNas = document.getElementById('bidNASIDInput');
      var elAttached = document.getElementById('attachedDocumentPath');
      if (elNas) {
        var valNas = elNas.value ? elNas.value.trim() : '';
        var valAttached = elAttached && elAttached.value ? elAttached.value.trim() : '';
        if (valNas === '' && valAttached === '') {
          addErr(elNas, 'Please enter the NAS Path or upload a tender document.');
        } else {
          removeErr(elNas);
        }
      }

      // 13. State
      var elState = document.getElementById('bidStateInput');
      if (elState) {
        var valState = elState.value ? elState.value.trim() : '';
        if (valState === '') {
          addErr(elState, 'Please select a State.');
        } else {
          removeErr(elState);
        }
      }

      // 14. Customer Name
      var elCustName = document.getElementById('bidCustomerNameInput');
      if (elCustName) {
        var valCustName = elCustName.value ? elCustName.value.trim() : '';
        if (valCustName === '') {
          addErr(elCustName, 'Please enter the Customer Name.');
        } else {
          removeErr(elCustName);
        }
      }

      // 15. Customer Phone
      var elCustPhone = document.getElementById('bidCustomerPhoneInput');
      if (elCustPhone) {
        var valCustPhone = elCustPhone.value ? elCustPhone.value.trim() : '';
        if (valCustPhone === '') {
          addErr(elCustPhone, 'Please enter the Customer Phone number.');
        } else if (!/^[0-9+\-\s()]{7,20}$/.test(valCustPhone)) {
          addErr(elCustPhone, 'Please enter a valid phone number (7–20 digits).');
        } else {
          removeErr(elCustPhone);
        }
      }

      // 16. Customer Address
      var elCustAddr = document.getElementById('bidCustomerAddressInput');
      if (elCustAddr) {
        var valCustAddr = elCustAddr.value ? elCustAddr.value.trim() : '';
        if (valCustAddr === '') {
          addErr(elCustAddr, 'Please enter the Customer Address.');
        } else {
          removeErr(elCustAddr);
        }
      }

      // 17. Category
      var elCategory = document.getElementById('bidCategoryInput');
      if (elCategory) {
        var valCategory = elCategory.value ? elCategory.value.trim() : '';
        if (valCategory === '') {
          addErr(elCategory, 'Please enter the Tender Category.');
        } else {
          removeErr(elCategory);
        }
      }

      // 18. Remarks
      var elRemarks = document.getElementById('bidRemarksInput');
      if (elRemarks) {
        var valRemarks = elRemarks.value ? elRemarks.value.trim() : '';
        if (valRemarks === '') {
          addErr(elRemarks, 'Please enter Remarks.');
        } else {
          removeErr(elRemarks);
        }
      }

      if (errors.length > 0) {
        if (firstEl) {
          firstEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
          setTimeout(function() {
            try { firstEl.focus(); } catch(err){}
          }, 300);
        }
        return false;
      } else {
        return true;
      }
    };

    // Helper to dismiss error on a specific field when user edits it
    function clearFieldError(el) {
      if (!el) return;
      el.classList.remove('is-invalid');
      var group = el.closest('.input-group');
      if (group) group.classList.remove('has-validation-error');
      if (el.id === 'bidTypeInput') {
        var s2 = el.nextElementSibling;
        if (s2 && s2.classList.contains('select2-container')) {
          s2.style.border = '';
        }
      }
      var col = el.closest('.col-lg-8');
      if (col) {
        var feed = col.querySelector('.field-error-feedback');
        if (feed) feed.style.display = 'none';
      }
    }

    // Global validate() function called ONLY when user clicks "Create Bid" (form onsubmit)
    window.validate = function() {
      return window.validateBidForm(true, true);
    };

    // When user fixes an error by typing/changing that field, clear only that field's error
    [
      'bidTypeInput', 'bidTitleInput', 'bidDepartmentInput', 'bidEstimatedInput',
      'bidEmdInput', 'startDate', 'dueDate', 'dateofBirth', 'bidSummaryInput',
      'bidTenderPortalInput', 'bidTenderIDInput', 'bidNASIDInput', 'bidRemarksInput',
      'bidStateInput', 'bidCustomerNameInput', 'bidCustomerPhoneInput', 'bidCustomerAddressInput', 'bidCategoryInput'
    ].forEach(function(id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', function() {
        clearFieldError(this);
      });
      el.addEventListener('change', function() {
        clearFieldError(this);
      });
    });

    // ── Open Right-Side Fact Sheet Modal ──
    function openFactSheetPanel() {

      var fsId = document.getElementById('factSheetId') ? document.getElementById('factSheetId').value : '';
      var attachedPath = document.getElementById('attachedDocumentPath') ? document.getElementById('attachedDocumentPath').value : '';
      var fsUrl = document.getElementById('factSheetUrl') ? document.getElementById('factSheetUrl').value : '';

      var panel = document.getElementById('factSheetPanel');
      var overlay = document.getElementById('factSheetOverlay');
      var body = document.getElementById('factSheetPanelBody');
      var openTabBtn = document.getElementById('btnOpenFactSheetTab');

      if (!panel || !overlay || !body) return;

      // Update full page link
      var tabUrl = fsUrl || (fsId ? '<?= $this->config->item('base_url') ?>bid/factsheet?id=' + encodeURIComponent(fsId) + '&standalone=1' : '<?= $this->config->item('base_url') ?>bid/factsheet?doc=' + encodeURIComponent(attachedPath) + '&standalone=1');
      if (openTabBtn) openTabBtn.href = tabUrl;

      // Slide open panel
      panel.classList.add('open');
      overlay.classList.add('show');

      // Show loader
      body.innerHTML = `
        <div class="text-center py-5 text-muted" style="min-height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
          <div class="spinner-border text-primary mb-3" role="status" style="width:2.2rem;height:2.2rem;"></div>
          <div class="fw-bold" style="font-size:14px;color:#1e293b;">Loading Tender Fact Sheet...</div>
          <div class="small text-muted mt-1">Retrieving sections from TenderFactSheet</div>
        </div>
      `;

      // AJAX Fetch Fact Sheet content passing current form fields so it reflects the live filled data dynamically
      var ajaxUrl = fsId 
        ? '<?= $this->config->item('base_url') ?>bid/factsheet?id=' + encodeURIComponent(fsId) + '&ajax=1'
        : (attachedPath ? '<?= $this->config->item('base_url') ?>bid/factsheet?doc=' + encodeURIComponent(attachedPath) + '&ajax=1' : '<?= $this->config->item('base_url') ?>bid/factsheet?ajax=1');

      var syncPayload = new FormData();
      syncPayload.append('bidTitle', document.getElementById('bidTitleInput') ? document.getElementById('bidTitleInput').value : '');
      syncPayload.append('bidDepartment', document.getElementById('bidDepartmentInput') ? document.getElementById('bidDepartmentInput').value : '');
      syncPayload.append('bidType', document.getElementById('bidTypeInput') ? document.getElementById('bidTypeInput').value : '');
      syncPayload.append('bidEstimated', document.getElementById('bidEstimatedInput') ? document.getElementById('bidEstimatedInput').value : '');
      syncPayload.append('bidEmd', document.getElementById('bidEmdInput') ? document.getElementById('bidEmdInput').value : '');
      syncPayload.append('startDate', document.getElementById('startDate') ? document.getElementById('startDate').value : '');
      syncPayload.append('dueDate', document.getElementById('dueDate') ? document.getElementById('dueDate').value : '');
      syncPayload.append('dateofBirth', document.getElementById('dateofBirth') ? document.getElementById('dateofBirth').value : '');
      // Sync all 12 Tender Milestone Activities
      syncPayload.append('tenderPublicationTime', document.getElementById('tenderPublicationTime') ? document.getElementById('tenderPublicationTime').value : '');
      syncPayload.append('documentSaleStartDate', document.getElementById('documentSaleStartDate') ? document.getElementById('documentSaleStartDate').value : '');
      syncPayload.append('documentSaleStartTime', document.getElementById('documentSaleStartTime') ? document.getElementById('documentSaleStartTime').value : '');
      syncPayload.append('documentSaleEndDate', document.getElementById('documentSaleEndDate') ? document.getElementById('documentSaleEndDate').value : '');
      syncPayload.append('documentSaleEndTime', document.getElementById('documentSaleEndTime') ? document.getElementById('documentSaleEndTime').value : '');
      syncPayload.append('prebidQueryStartDate', document.getElementById('prebidQueryStartDate') ? document.getElementById('prebidQueryStartDate').value : '');
      syncPayload.append('prebidQueryStartTime', document.getElementById('prebidQueryStartTime') ? document.getElementById('prebidQueryStartTime').value : '');
      syncPayload.append('prebidQueryEndDate', document.getElementById('prebidQueryEndDate') ? document.getElementById('prebidQueryEndDate').value : '');
      syncPayload.append('prebidQueryEndTime', document.getElementById('prebidQueryEndTime') ? document.getElementById('prebidQueryEndTime').value : '');
      syncPayload.append('prebidMeetingDate', document.getElementById('prebidMeetingDate') ? document.getElementById('prebidMeetingDate').value : '');
      syncPayload.append('prebidMeetingTime', document.getElementById('prebidMeetingTime') ? document.getElementById('prebidMeetingTime').value : '');
      syncPayload.append('prebidResponseDate', document.getElementById('prebidResponseDate') ? document.getElementById('prebidResponseDate').value : '');
      syncPayload.append('prebidResponseTime', document.getElementById('prebidResponseTime') ? document.getElementById('prebidResponseTime').value : '');
      syncPayload.append('bidSubmissionStartDate', document.getElementById('bidSubmissionStartDate') ? document.getElementById('bidSubmissionStartDate').value : '');
      syncPayload.append('bidSubmissionStartTime', document.getElementById('bidSubmissionStartTime') ? document.getElementById('bidSubmissionStartTime').value : '');
      syncPayload.append('bidSubmissionEndTime', document.getElementById('bidSubmissionEndTime') ? document.getElementById('bidSubmissionEndTime').value : '');
      syncPayload.append('pqBidOpeningDate', document.getElementById('pqBidOpeningDate') ? document.getElementById('pqBidOpeningDate').value : '');
      syncPayload.append('pqBidOpeningTime', document.getElementById('pqBidOpeningTime') ? document.getElementById('pqBidOpeningTime').value : '');
      syncPayload.append('technicalBidOpeningTime', document.getElementById('technicalBidOpeningTime') ? document.getElementById('technicalBidOpeningTime').value : '');
      syncPayload.append('financialBidOpeningDate', document.getElementById('financialBidOpeningDate') ? document.getElementById('financialBidOpeningDate').value : '');
      syncPayload.append('financialBidOpeningTime', document.getElementById('financialBidOpeningTime') ? document.getElementById('financialBidOpeningTime').value : '');
      syncPayload.append('bidSummary', document.getElementById('bidSummaryInput') ? document.getElementById('bidSummaryInput').value : '');
      syncPayload.append('bidTenderID', document.getElementById('bidTenderIDInput') ? document.getElementById('bidTenderIDInput').value : '');

      var xhr = new XMLHttpRequest();
      xhr.open('POST', ajaxUrl, true);
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
          body.innerHTML = xhr.responseText;
        } else {
          body.innerHTML = `
            <div class="alert alert-danger m-4 border-0 shadow-sm" style="border-radius:8px;">
              <div class="fw-bold"><i class="feather-alert-triangle me-1"></i> Unable to Load Fact Sheet</div>
              <div class="small mt-1">Please ensure the tender document has been parsed successfully.</div>
            </div>
          `;
        }
      };
      xhr.onerror = function() {
        body.innerHTML = `
          <div class="alert alert-danger m-4 border-0 shadow-sm" style="border-radius:8px;">
            <div class="fw-bold"><i class="feather-alert-triangle me-1"></i> Network Error</div>
            <div class="small mt-1">Failed to connect to server. Please try again.</div>
          </div>
        `;
      };
      xhr.send(syncPayload);
    }

    function closeFactSheetPanel() {
      var panel = document.getElementById('factSheetPanel');
      var overlay = document.getElementById('factSheetOverlay');
      if (panel) panel.classList.remove('open');
      if (overlay) overlay.classList.remove('show');
    }

    // Fact Sheet button click handler (opens right-side modal)
    var fsBtn = document.getElementById('btnViewFactSheet');
    if (fsBtn) {
      fsBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openFactSheetPanel();
      });
    }

    // Attached badge link click handler
    var fsBadgeLink = document.getElementById('attachedDocFactSheetLink');
    if (fsBadgeLink) {
      fsBadgeLink.addEventListener('click', function(e) {
        e.preventDefault();
        openFactSheetPanel();
      });
    }

    // Close panel button & overlay click handlers
    var closePanelBtn = document.getElementById('closeFactSheetPanel');
    if (closePanelBtn) {
      closePanelBtn.addEventListener('click', function(e) {
        e.preventDefault();
        closeFactSheetPanel();
      });
    }

    var panelOverlay = document.getElementById('factSheetOverlay');
    if (panelOverlay) {
      panelOverlay.addEventListener('click', function() {
        closeFactSheetPanel();
      });
    }

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

    var downloadModalBtn = document.getElementById('btnDownloadFactSheetInModal');
    if (downloadModalBtn) {
      downloadModalBtn.addEventListener('click', function(e) {
        e.preventDefault();
        downloadFactSheetAsPdf();
      });
    }

    // Clear form button handler
    var clearBtn = document.getElementById('btnClearBidForm');
    if (clearBtn) {
      clearBtn.addEventListener('click', function() {
        setField('attachedDocumentPath', '');
        setField('attachedDocumentName', '');
        setField('factSheetId', '');
        setField('factSheetUrl', '');
        var classBadge = document.getElementById('attachedDocClassification');
        if (classBadge) classBadge.style.display = 'none';
        var txtPathEl = document.getElementById('attachedDocTxtPath');
        if (txtPathEl) txtPathEl.style.display = 'none';
        var fsLink = document.getElementById('attachedDocFactSheetLink');
        if (fsLink) {
          fsLink.style.display = 'none';
          fsLink.href = 'javascript:void(0);';
        }
        var attachBadge = document.getElementById('attachedDocumentBadge');
        if (attachBadge) attachBadge.style.display = 'none';

        // Clear all validation errors and summary alert
        document.querySelectorAll('.is-invalid').forEach(function(el) {
          el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.has-validation-error').forEach(function(el) {
          el.classList.remove('has-validation-error');
        });
        document.querySelectorAll('.field-error-feedback').forEach(function(el) {
          el.style.display = 'none';
        });
        var typeEl = document.getElementById('bidTypeInput');
        if (typeEl && typeEl.nextElementSibling && typeEl.nextElementSibling.classList.contains('select2-container')) {
          typeEl.nextElementSibling.style.border = '';
        }
      });
    }
  });
})();
</script>