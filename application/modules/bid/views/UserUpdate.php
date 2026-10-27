 <?php $employee_det = $this->session->userdata('logged_in');
  $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index');

 

  }
?>
 
  <!-- [ Main Content ] start -->
 
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
                                                <!-- <th class="wd-30">
                                                    <div class="btn-group mb-1">
                                                        <div class="custom-control custom-checkbox ms-1">
                                                            <input type="checkbox" class="custom-control-input" id="checkAllCustomer">
                                                            <label class="custom-control-label" for="checkAllCustomer"></label>
                                                        </div>
                                                    </div>
                                                </th> -->
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Mobile</th>
                                                <!-- <th>Phone</th>  -->
                                                <th>Designation</th> 
                                                <th>Role</th> 
                                                <th>Status</th>
                                                <th>Creation Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                         <tbody>
                                          <?php
                                           // echo "<pre>"; print_r($UsersData); exit;
                                            if(isset($UsersData) && !empty($UsersData))
                                            {
                                                $i =1;
                                                    foreach($UsersData as $val)
                                                    {  

                                                       
                                                ?>
                                                <tr class="single-item">
                                                     <td>
                                                        <?=$val['Name']?>
                                                      
                                                    </td>
                                                    <td><?=$val['Email']?></td>
                                                    <td><?=$val['Mobile']?></td>
                                                    <td><?=$val['CreationTs']?></td>
                                                    <td><?=$val['RoleName']?></td>
                                                     <td>
                                                        <!-- <select class="form-control status-change"  data-id="<?= $val['Buid']; ?>">
                                                            <option value="1" data-bg="bg-success" selected>Active</option>
                                                            <option value="0" data-bg="bg-warning">Inactive</option>
                                                            <option value="danger" data-bg="bg-danger">Declined</option> 
                                                        </select> -->
                                                        <select class="form-control status-change" data-id="<?= $val['Buid']; ?>">
                                                            <option value="1" <?= ($val['Status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                                            <option value="0" <?= ($val['Status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                                        </select>
                                                    </td>
                                                     <td><?=$val['CreationTs']?></td>
                                                      <td>
                                                    <div class="hstack gap-2 justify-content-end">
                                                       <a href="javascript:void(0)" 
                                                               class="avatar-text avatar-md openAddForm view-user" 
                                                               data-id="<?= $val['Buid']; ?>">
                                                                <i class="feather feather-eye"></i>
                                                            </a>
                                                      
                                                      <!--   <div class="dropdown">
                                                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                                <i class="feather feather-more-horizontal"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-edit-3 me-3"></i>
                                                                        <span>Edit</span>
                                                                    </a>
                                                                </li>    
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-trash-2 me-3"></i>
                                                                        <span>Delete</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div> -->
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
    <div class="right-form-header">
        <h5>View User Details</h5>
        <button type="button" class="close-btn" id="closeAddForm">&times;</button>
    </div>

    <div class="right-form-body">
               <!-- YOUR EXISTING FORM STARTS -->
        <div id="userModalBody"></div>
               <!-- YOUR FORM ENDS -->
    </div>
</div>

<div id="rightFormOverlay"></div>
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
            console.log(response);
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
            console.log(response);
            $('#userModalBody').html(response);
            // $('#userModal').modal('show');
        }
    });

});

// OPEN
$(document).on('click', '.openAddForm', function () {
    $('#rightForm').addClass('open');
    $('#rightFormOverlay').addClass('show');
});

// CLOSE (button inside panel)
$(document).on('click', '#closeAddForm', function () {
    $('#rightForm').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});

// CLOSE (click outside overlay)
$(document).on('click', '#rightFormOverlay', function () {
    $('#rightForm').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});
</script>