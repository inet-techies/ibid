 <?php $employee_det = $this->session->userdata('logged_in');
  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
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
                            <form method="post" enctype="multipart/form-data" action="<?php echo $this->config->item('base_url'); ?>bid/saveUsers" onsubmit="return validate();">
                             <div class="card-body general-info">
                                <div class="mb-5 d-flex align-items-center justify-content-between">
                                    <h5 class="fw-bold mb-0 me-4">
                                        <span class="d-block mb-2">Add User:</span>
                                        <span class="fs-12 fw-normal text-muted text-truncate-1-line">General information for User</span>
                                    </h5>
                                    <!-- <a href="javascript:void(0);" class="btn btn-sm btn-light-brand">Edit Lead</a> -->
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="fullnameInput" class="fw-semibold">Name: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-user"></i></div>
                                            <input type="text" class="form-control" id="fullnameInput"  name="fullnameInput" placeholder="Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="phoneInput" class="fw-semibold">Mobile No: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-phone"></i></div>
                                            <input type="text" class="form-control" id="phoneInput" name="phoneInput" placeholder="Phone">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="mailInput" class="fw-semibold">Email: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-mail"></i></div>
                                            <input type="text" class="form-control" id="mailInput" name="mailInput" placeholder="Email">
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="usernameInput" class="fw-semibold">Username: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-link-2"></i></div>
                                            <input type="url" class="form-control" id="usernameInput" placeholder="Username">
                                        </div>
                                    </div>
                                </div> -->
                                
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="designationInput" class="fw-semibold">Designation: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                            <input type="text" class="form-control" id="designationInput" name="designationInput" placeholder="Designation">
                                        </div>
                                    </div>
                                </div> 
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label class="fw-semibold">User Type: </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="form-control" id="usertypeInput" name="usertypeInput" data-select2-selector="city">
                                            <option value="0">Select Option</option>
                                            <option data-city="bg-primary" value="3">Employee</option>
                                            <option data-city="bg-secondary" value="2">Admin</option> 
                                        </select>
                                    </div>
                                </div> 
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                <button type="reset" class="btn btn-md bg-danger text-white">Clear</button>
                                <button type="submit" class="btn btn-md btn-primary">Add User</button>
                             </div> 
                            </div>
                         </form>
                        </div>
                        
                    </div>
                </div>
 
            <!-- [ Main Content ] end -->