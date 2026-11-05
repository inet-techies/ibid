 <?php $employee_det = $this->session->userdata('logged_in');
  $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index');

 

  }
?>
 
  <!-- [ Main Content ] start -->
 
                <!-- Permanently Visible Header Filters (Teleported to extreme right corner of breadcrumb row) -->
<div id="page-filter-actions" style="display:none;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="header-filter-item">
            <select id="user-filter-role" class="form-select form-select-sm" style="min-width: 130px;">
                <option value="">All Roles</option>
                <?php 
                $roles = [];
                if (!empty($UsersData)) {
                    foreach ($UsersData as $u) {
                        if (!empty($u['RoleName'])) {
                            $rVal = trim($u['RoleName']);
                            if (!in_array($rVal, $roles)) $roles[] = $rVal;
                        }
                    }
                }
                sort($roles);
                foreach ($roles as $r): ?>
                    <option value="<?= htmlspecialchars($r); ?>"><?= htmlspecialchars($r); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="header-filter-item">
            <select id="user-filter-status" class="form-select form-select-sm" style="min-width: 120px;">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <div class="header-filter-item">
            <button type="button" class="btn btn-sm btn-icon btn-light-brand border" id="btn-reset-user-filters" title="Reset Filters" style="width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
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
                                                    <td><?= htmlspecialchars($val['Designation'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
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
                                                     <td><?= (!empty($val['CreationTs']) && strtotime($val['CreationTs'])) ? date('d M Y, h:i A', strtotime($val['CreationTs'])) : '-' ?></td>
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
               
        <div id="userModalBody"></div>
             
    </div>
</div>

<div id="rightFormOverlay"></div>
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

// DataTables filter integration for UserUpdate
$(document).ready(function() {
    var table = $('#customerList').DataTable({ retrieve: true });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'customerList') return true;

        var filterRole = ($('#user-filter-role').val() || '').toLowerCase().trim();
        var filterStatus = ($('#user-filter-status').val() || '').toLowerCase().trim();

        var rowRole = (data[4] || '').toLowerCase().trim();
        var rowStatus = (data[5] || '').toLowerCase().trim();

        if (filterRole && rowRole.indexOf(filterRole) === -1) return false;
        if (filterStatus && rowStatus.indexOf(filterStatus) === -1) return false;

        return true;
    });

    function updateUserFilterBadge() {
        var count = 0;
        if ($('#user-filter-role').val()) count++;
        if ($('#user-filter-status').val()) count++;
        var $badge = $('#user-filter-count');
        if (count > 0) {
            $badge.text(count).removeClass('d-none');
        } else {
            $badge.addClass('d-none');
        }
    }

    $('#user-filter-role, #user-filter-status').on('change', function() {
        table.draw();
    });

    $('#btn-reset-user-filters').on('click', function(e) {
        e.preventDefault();
        $('#user-filter-role').val('');
        $('#user-filter-status').val('');
        updateUserFilterBadge();
        table.draw();
    });
});
</script>