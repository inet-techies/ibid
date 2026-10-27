 <?php $employee_det = $this->session->userdata('logged_in');
   $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
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
                                   <table class="table table-hover" id="customerList">
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
                                                        <td><?= $val['CreationTs'] ?></td>
                                                        <td>
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md openAddForm view-bid"
                                                                    data-bid='<?= json_encode($val); ?>'>
                                                                    <i class="feather feather-eye"></i>
                                                                </a>
                                                                <a href="javascript:void(0)"
                                                                    class="avatar-text avatar-md update-bid"
                                                                    data-id="<?= $val['Bcid']; ?>"
                                                                    data-current="<?= $val['BidStatus']; ?>">
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
                                                    <td colspan="10" class="text-center text-muted py-4">
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
                   
                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="bid_status" name="bid_status" >
                         
                        </select>
                    </div>  

                     <div class="mb-4">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                         <input  type="date" class="form-control" id="ActualDate" name="ActualDate" placeholder="Select Date">
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
                          <textarea class="form-control" id="addressInput" cols="30" rows="3" placeholder="Remarks"></textarea> 
                         
                    </div>
                      <div class="d-flex justify-content-end gap-2 mt-3">
                        <button id="clear" class="btn btn-md bg-soft-danger text-danger">Clear</button>
                        <button id="UpdateBid" class="btn btn-md btn-primary">Update</button>
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

$(document).on('click', '.view-bid', function () {

    var bid = $(this).data('bid');

    var html = `
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            <table class="table table-bordered mb-0">
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
$(document).on("click", ".update-bid", function () {

    var bcid = $(this).attr('data-id');
    var currentStatus = $(this).attr('data-current');

    console.log("Clicked Bcid:", bcid);

    if (!bcid) {
        alert("Bcid not found");
        return;
    }

    // Set hidden input
    $('#Bcid').val(bcid);

    console.log("Hidden field value:", $('#Bcid').val());

    // Set status dropdown
    $('#bid_status').val(currentStatus).trigger('change');

    // Close details panel
    $("#rightForm").removeClass("open");

    // Open edit panel
    $("#bidCanvas").addClass("open");
    $("#rightFormOverlay").addClass("show");
});

$(document).on("click", ".update-bid", function () {

    let bidId = $(this).data("id");
    let currentStatus = $(this).data("current");

    $("#Bcid").val(bidId);

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

            // Hide Management fields initially
            $('#ApprovedByManagement').closest('.mb-4').hide();
            $('#ApprovedByWhom').closest('.mb-4').hide();

            $("#bidCanvas").addClass("open");
            $("#rightFormOverlay").addClass("show");
        }
    });
});

$(document).on('change', '#bid_status', function(){

    let statusName = $("#bid_status option:selected").data("name");
    console.log(statusName);
    if(statusName === 'PQEvaluation'){
        $('#ApprovedByManagement').closest('.mb-4').show();
    } else {
        $('#ApprovedByManagement').closest('.mb-4').hide();
        $('#ApprovedByWhom').closest('.mb-4').hide();
    }
});

$("#updateBidForm").submit(function (e) {
    e.preventDefault();

    $.ajax({
        url: "<?= base_url('bid/updateBidStatus'); ?>",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function (res) {

            if (res.status) {

                // Close panel
                $("#bidCanvas").removeClass("show");
                $("#rightFormOverlay").removeClass("show");

                // Optional: update status badge dynamically
                location.reload(); // remove this if you want pure dynamic update
            }
        }
    });
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
$(document).on('click', '#UpdateBid', function (e) {

    e.preventDefault();

    $.ajax({
        url: "<?= base_url('bid/updateBidStatus') ?>",
        type: "POST",
        dataType: "json",
        data: {
            Bcid: $('#Bcid').val(),
            bid_status: $('#bid_status').val(),
            ActualDate: $('#ActualDate').val(),
            ApprovedByManagement: $('#ApprovedByManagement').val(),
            ApprovedByWhom: $('#ApprovedByWhom').val(),
            Remarks: $('#addressInput').val()
        },

        beforeSend: function () {
            $('#UpdateBid').prop('disabled', true).text('Updating...');
        },

        success: function (res) {

            if (res.status === true) {

                $('#responseMessage').html(
                    '<div class="alert alert-success">Status Updated Successfully</div>'
                );

                setTimeout(function () {
                    location.reload();
                }, 1200);

            } else {

                $('#responseMessage').html(
                    '<div class="alert alert-danger">' + res.message + '</div>'
                );

                $('#UpdateBid').prop('disabled', false).text('Update');
            }
        }
    });
});
</script>