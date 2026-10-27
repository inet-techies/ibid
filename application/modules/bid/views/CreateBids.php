 <?php $employee_det = $this->session->userdata('logged_in');
  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
?>

<div class="row">
                    <div class="col-lg-12">
                        <div class="card stretch stretch-full">
                            <div class="card-body lead-status">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="fw-bold mb-0 me-4">
                                        <span class="d-block mb-2">Create Bid</span>
                                        <span class="fs-12 fw-normal text-muted text-truncate-1-line">Create and register a new bid by capturing tender details, documents, and key submission parameters</span>
                                    </h5>
                                    <!-- <a href="javascript:void(0);" class="btn btn-sm btn-light-brand">Create Invoice</a> -->
                                </div> 
                            </div>
                            <hr class="mt-0">
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

                           
                            <form method="post" enctype="multipart/form-data" action="<?php echo $this->config->item('base_url'); ?>bid/AddBidData" onsubmit="return validate();"autocomplete="off">
                            <div class="card-body general-info">  
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label class="fw-semibold">Bid Type: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="form-control" data-select2-selector="category" id="bidTypeInput" name="bidTypeInput">
                                             <option value="0">Select Option</option>
                                            <option data-category="oe">OEM</option>
                                            <option data-category="se">Service</option> 
                                        </select>
                                    </div>
                                </div>	<!---------Bid Type----------->
                                 <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidTitleInput" class="fw-semibold">Bid Title: </label>
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
                                        <label for="bidDepartmentInput" class="fw-semibold">Department/Organization: </label>
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
                                        <label for="bidEstimatedInput" class="fw-semibold">Estimated Tender Value: </label>
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
                                        <label for="bidEmdInput" class="fw-semibold">Bid EMD Value: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fa fa-rupee"></i></div>
                                            <input type="text" class="form-control" id="bidEmdInput" name="bidEmdInput" placeholder="Enter EMD Value"> 
                                        </div>
                                        <small class="text-primary fw-bold mt-1 d-block" id="emdInWords"></small>
                                    </div>
                                </div><!---------Bid EMD----------->
                                 <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidPublishedDateInput" class="fw-semibold">Bid Published Date: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input class="form-control" id="startDate" name="PublishedDate" placeholder="Select Date">
                                        </div>
                                    </div>
                                </div><!---------Bid Published Date----------->
                                 <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidSubmissionDateInput" class="fw-semibold">Bid Submission End Date: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input  class="form-control" id="dueDate" name="SubmissionDate" placeholder="Select Date">
                                        </div>
                                    </div>
                                </div><!---------Bid Submission Date----------->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidOpeningDateInput" class="fw-semibold">Bid Opening Date: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                    <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input  class="form-control" id="dateofBirth" name="OpeningDate" placeholder="Select Date">
                                        </div>
                                     </div>
                                </div><!---------Bid Opening Date----------->
                                
                                 <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidSummaryInput" class="fw-semibold">Keywords for Summary:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                        	 <div class="input-group-text"><i class="feather-type"></i></div>
                                            <textarea class="form-control"  id="bidSummaryInput" name="bidSummaryInput" cols="30" rows="5" placeholder="Keywords for Summary"></textarea>

                                        </div>
                                    </div>
                                </div><!---------Bid Keywords for Summary----------->
                                 
                                 <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidTenderPortalInput" class="fw-semibold">Tender Portal URL:</label>
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
                                        <label for="bidTenderIDInput" class="fw-semibold">Tender ID:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                             <div class="input-group-text"><i class="feather-slack"></i></div>
                                               <input type="test" class="form-control" id="bidTenderIDInput" name="bidTenderIDInput" placeholder="Enter Tender ID">
                                        </div>
                                    </div>
                                </div><!---------Bid Tender Portal URL----------->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidNASIDInput" class="fw-semibold">NAS Path:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                             <div class="input-group-text"><i class="feather-folder"></i></div>
                                               <input type="test" class="form-control" id="bidNASIDInput" name="bidNASIDInput" placeholder="Enter NAS Path">
                                        </div>
                                    </div>
                                </div><!---------Bid Tender Portal URL----------->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="bidRemarksInput" class="fw-semibold">Remarks:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                             <div class="input-group-text"><i class="feather-type"></i></div>
                                            <textarea class="form-control"  id="bidRemarksInput" name="bidRemarksInput" cols="30" rows="5" placeholder="Remarks"></textarea>

                                        </div>
                                    </div>
                                </div><!---------Remarks----------->

                                 <div class="d-flex justify-content-center gap-2 mt-3">
                                    <button type="reset" class="btn btn-md bg-danger text-white">Clear</button>
                                    <button type="submit" class="btn btn-md btn-primary">Create Bid</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            <!---[Main Row]---->
 

<script src="<?=$theme_path?>/js/jquery-1.11.1.min.js"></script>
<script>
    
    document.getElementById('bidEmdInput').addEventListener('input', function () {
    let number = this.value.replace(/,/g,'');
    if (number == '' || isNaN(number)) {
        document.getElementById('emdInWords').innerHTML = '';
        return;
    }
    document.getElementById('emdInWords').innerHTML = convertNumberToWords(number) + ' Only';
}); 
    document.getElementById('bidEstimatedInput').addEventListener('input', function () {
    let number = this.value.replace(/,/g,'');
    if (number == '' || isNaN(number)) {
        document.getElementById('EstemdInWords').innerHTML = '';
        return;
    }
    document.getElementById('EstemdInWords').innerHTML = convertNumberToWords(number) + ' Only';
});

function convertNumberToWords(num) {

    const a = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
        'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
        'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];

    const b = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    if ((num = num.toString()).length > 9) return 'Overflow';

    let n = ('000000000' + num).substr(-9).match(/.{1,2}/g);
    let str = '';

    let crore = parseInt(n[0]);
    let lakh = parseInt(n[1]);
    let thousand = parseInt(n[2]);
    let hundred = parseInt(n[3]);
    let last = parseInt(n[4]);

    if (crore) {
        str += (crore < 20 ? a[crore] : b[Math.floor(crore/10)] + ' ' + a[crore%10]) + ' Crore ';
    }

    if (lakh) {
        str += (lakh < 20 ? a[lakh] : b[Math.floor(lakh/10)] + ' ' + a[lakh%10]) + ' Lakh ';
    }

    if (thousand) {
        str += (thousand < 20 ? a[thousand] : b[Math.floor(thousand/10)] + ' ' + a[thousand%10]) + ' Thousand ';
    }

    if (hundred) {
        str += (hundred < 20 ? a[hundred] : b[Math.floor(hundred/10)] + ' ' + a[hundred%10]) + ' Hundred ';
    }

    if (last) {
        str += (str != '' ? 'and ' : '') +
               (last < 20 ? a[last] : b[Math.floor(last/10)] + ' ' + a[last%10]);
    }

    return str.trim();
}
</script>