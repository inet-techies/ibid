 <?php $employee_det = $this->session->userdata('logged_in');
   $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
?>

  <div class="row">
  <div class="col-lg-12">
      <!-- <div class="card stretch stretch-full">
        <div class="card-body p-0">

        </div>

      </div> -->
      <div class="card">
          <div class="card-header">
              <h5 class="card-title">User Menu Permission</h5>
          </div>

          <div class="card-body">

              <!-- User Dropdown -->
              <div class="mb-3">
                  <label>Select User</label>
                  <select id="userSelect" class="form-control">
                      <option value="">-- Select User --</option>
                      <?php foreach($UsersData as $user): ?>
                          <option value="<?= $user['Brid']; ?>">
                              <?= $user['Name']; ?> - <?= $user['Mobile']; ?> (<?= $user['RoleName']; ?>)
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <!-- Menu List -->
              <div id="menuContainer">
                  <p class="text-muted">Select user to load menus</p>
              </div>

          </div>
      </div>
      
  </div>
</div>
  <script src="<?=$theme_path?>/js/jquery-1.11.1.min.js"></script>

<script>
$('#userSelect').change(function () {

    let roleId = $(this).val();

    if(roleId === '') {
        $('#menuContainer').html('<p class="text-muted">Select user to load menus</p>');
        return;
    }

    $.ajax({
        url: "<?= base_url('bid/manageRoleMenus'); ?>",
        type: "POST",
        data: { roleId: roleId },
        success: function (response) {
            $('#menuContainer').html(response);
        }
    });

});

function updatePermission(roleId, menuId, checkbox) {

    let action = checkbox.checked ? 'add' : 'remove';

    $.ajax({
        url: "<?= base_url('bid/updatePermission'); ?>",
        type: "POST",
        data: {
            roleId: roleId,
            menuId: menuId,
            action: action
        },
        success: function (res) {
            console.log(res);
        }
    });
}
</script>