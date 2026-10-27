<?php $employee_det = $this->session->userdata('logged_in');
if (empty($employee_det)) { redirect(base_url('bid/index')); }
?>

<div class="row">
    <div class="col-lg-12">
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('true')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="feather-check-circle me-2"></i>
                <?= $this->session->flashdata('true'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('err')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="feather-alert-triangle me-2"></i>
                <?= $this->session->flashdata('err'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add Material Item Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-plus-circle text-primary me-2"></i>Add Material
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('oem/materialMaster'); ?>" method="post" autocomplete="off">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-semibold">
                                Category <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-folder"></i></div>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['CId']; ?>">
                                                <?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="product_id" class="form-label fw-semibold">
                                Product <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-package"></i></div>
                                <select name="product_id" id="product_id" class="form-select" required>
                                    <option value="">-- Select Product --</option>
                                    <?php if (!empty($products)): ?>
                                        <?php foreach ($products as $prod): ?>
                                            <option value="<?= $prod['PId']; ?>" data-cat="<?= $prod['CategoryId']; ?>">
                                                <?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="material_name" class="form-label fw-semibold">
                                Material Name / Specification <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-layers"></i></div>
                                <input type="text" class="form-control" id="material_name" name="material_name" required />
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                            <button type="reset" class="btn btn-md bg-danger text-white">
                                <i class="feather-x me-1"></i> Clear
                            </button>
                            <button type="submit" class="btn btn-md btn-primary">
                                <i class="feather-check me-1"></i> Save Material
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Material Directory Table Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-layers text-primary me-2"></i>Material Directory
                    <span class="badge bg-primary ms-2 fs-11"><?= !empty($materials) ? count($materials) : 0; ?> Total</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="materialTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Material Name / Specification</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 180px;">Created Date</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($materials)): ?>
                                <?php $i = 1; foreach ($materials as $mat): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($mat['MaterialName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="feather-box me-1"></i>
                                                <?= htmlspecialchars($mat['ProductList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="feather-tag me-1"></i>
                                                <?= htmlspecialchars($mat['CategoryList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ((int)($mat['Status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success" id="badge-mat-<?= $mat['MId']; ?>">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger" id="badge-mat-<?= $mat['MId']; ?>">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-12">
                                                <?= !empty($mat['CreatedAt']) ? date('d M Y, h:i A', strtotime($mat['CreatedAt'])) : '-'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md edit-mat-btn"
                                                    title="Edit Material"
                                                    data-id="<?= $mat['MId']; ?>"
                                                    data-name="<?= htmlspecialchars($mat['MaterialName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cat="<?= $mat['CategoryId']; ?>"
                                                    data-prod="<?= $mat['ProductId']; ?>"
                                                    data-status="<?= $mat['Status']; ?>">
                                                    <i class="feather-edit-2"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md toggle-mat-btn"
                                                    title="Toggle Status (Active/Inactive)"
                                                    data-id="<?= $mat['MId']; ?>"
                                                    data-status="<?= $mat['Status']; ?>"
                                                    data-name="<?= htmlspecialchars($mat['MaterialName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-refresh-cw"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md delete-mat-btn text-danger"
                                                    title="Delete Material"
                                                    data-id="<?= $mat['MId']; ?>"
                                                    data-name="<?= htmlspecialchars($mat['MaterialName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="feather-alert-circle me-1"></i> No materials registered yet. Use the form above to add material specifications.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Material Right-Side Drawer Modal (Matching Bid Style) -->
<div class="right-form" id="editMaterialModal">
    <div class="right-form-header">
        <h5 class="fw-bold mb-0">
            <i class="feather-edit-2 text-primary me-2"></i>Edit Material Specification
        </h5>
        <button type="button" class="close-btn close-right-form" aria-label="Close">&times;</button>
    </div>
    <div class="right-form-body">
        <form action="<?= base_url('oem/updateMaterial'); ?>" method="post" autocomplete="off">
            <input type="hidden" name="m_id" id="edit_m_id" />
            <div class="mb-3">
                <label for="edit_cat_select" class="form-label fw-semibold">
                    Category <span class="text-danger">*</span>
                </label>
                <select name="category_id" id="edit_cat_select" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['CId']; ?>">
                                <?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="edit_prod_select" class="form-label fw-semibold">
                    Product Line <span class="text-danger">*</span>
                </label>
                <select name="product_id" id="edit_prod_select" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['PId']; ?>">
                                <?= htmlspecialchars($p['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="edit_mat_name" class="form-label fw-semibold">
                    Material / Spec Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_mat_name" name="material_name" required />
            </div>
            <div class="mb-3">
                <label for="edit_mat_status" class="form-label fw-semibold">Status</label>
                <select name="status" id="edit_mat_status" class="form-select">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light close-right-form">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-check me-1"></i> Update Material
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- Material Toggle Confirmation Modal -->
<div class="modal fade" id="materialConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="feather-alert-circle text-warning me-1"></i> Confirm Status Change
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <i class="feather-alert-circle fs-1 text-warning mb-3 d-block"></i>
                <p id="materialConfirmMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <div id="materialStatusError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmToggleMatBtn">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<!-- Material Delete Confirmation Modal -->
<div class="modal fade" id="deleteMaterialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="feather-alert-triangle me-1"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <i class="feather-trash-2 fs-1 text-danger mb-3 d-block"></i>
                <p id="deleteMaterialMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
                <div id="deleteMaterialError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteMatBtn">
                    <i class="feather-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        if ($(el).hasClass('right-form')) {
            $(el).addClass('open');
            $('#rightFormOverlay').addClass('show');
            $('body').addClass('modal-open');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            let m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            m.show();
        } else if (window.jQuery && typeof $(el).modal === 'function') {
            $(el).modal('show');
        } else {
            $(el).addClass('show').css('display', 'block');
            $('body').addClass('modal-open');
            if (!$('.modal-backdrop').length) {
                $('<div class="modal-backdrop fade show"></div>').appendTo('body');
            }
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        if ($(el).hasClass('right-form')) {
            $(el).removeClass('open');
            $('#rightFormOverlay').removeClass('show');
            $('body').removeClass('modal-open');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            let m = bootstrap.Modal.getInstance(el);
            if (m) m.hide();
        }
        if (window.jQuery && typeof $(el).modal === 'function') {
            $(el).modal('hide');
        }
        $(el).removeClass('show').css('display', 'none');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }

    // 1. Move all modals and right-forms directly to body
    if (window.jQuery) {
        $('.modal, .right-form, #rightFormOverlay').appendTo('body');
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            const m = $(this).closest('.modal');
            if (m.length) closeModal(m.attr('id'));
        });
        $(document).on('click', '.close-btn, .close-right-form, #rightFormOverlay', function() {
            $('.right-form').removeClass('open');
            $('#rightFormOverlay').removeClass('show');
            $('body').removeClass('modal-open');
        });
    }

    // Safe DataTables initialization
    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#materialTable').length) {
        $('#materialTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']]
        });
    }

    // Dependent Dropdown for Category -> Product (Add form)
    $(document).on('change', '#category_id', function() {
        const catId = $(this).val();
        const $prodSelect = $('#product_id');

        if (!catId) {
            $prodSelect.html('<option value="">-- Select Product --</option>');
            return;
        }

        $.ajax({
            url: '<?= base_url("oem/getProductsByCategory"); ?>',
            type: 'POST',
            data: { category_id: catId },
            dataType: 'json',
            success: function(products) {
                let html = '<option value="">-- Select Product --</option>';
                products.forEach(function(p) {
                    html += '<option value="' + p.PId + '">' + p.ProductList + '</option>';
                });
                $prodSelect.html(html);
            }
        });
    });

    // Dependent Dropdown for Category -> Product (Edit modal)
    $(document).on('change', '#edit_cat_select', function() {
        const catId = $(this).val();
        const $prodSelect = $('#edit_prod_select');

        if (!catId) {
            $prodSelect.html('<option value="">-- Select Product --</option>');
            return;
        }

        $.ajax({
            url: '<?= base_url("oem/getProductsByCategory"); ?>',
            type: 'POST',
            data: { category_id: catId },
            dataType: 'json',
            success: function(products) {
                let html = '<option value="">-- Select Product --</option>';
                products.forEach(function(p) {
                    html += '<option value="' + p.PId + '">' + p.ProductList + '</option>';
                });
                $prodSelect.html(html);
            }
        });
    });

    // Delegated click for Edit Material
    $(document).on('click', '.edit-mat-btn', function(e) {
        e.preventDefault();
        const mId = $(this).attr('data-id');
        const name = $(this).attr('data-name');
        const catId = $(this).attr('data-cat');
        const prodId = $(this).attr('data-prod');
        const status = $(this).attr('data-status') || '1';

        $('#edit_m_id').val(mId);
        $('#edit_mat_name').val(name);
        $('#edit_cat_select').val(catId);
        $('#edit_prod_select').val(prodId);
        $('#edit_mat_status').val(status);

        openModal('editMaterialModal');
    });

    // Delegated click for Delete Material
    let targetDeleteMatId = null;
    $(document).on('click', '.delete-mat-btn', function(e) {
        e.preventDefault();
        targetDeleteMatId = $(this).attr('data-id');
        const name = $(this).attr('data-name');

        $('#deleteMaterialMsg').text('Are you sure you want to delete material "' + name + '"?');
        $('#deleteMaterialError').addClass('d-none').text('');
        $('#confirmDeleteMatBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
        openModal('deleteMaterialModal');
    });

    $(document).on('click', '#confirmDeleteMatBtn', function() {
        if (!targetDeleteMatId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

        $.ajax({
            url: '<?= base_url("oem/deleteMaterial"); ?>',
            type: 'POST',
            data: { id: targetDeleteMatId, m_id: targetDeleteMatId },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                    $('#deleteMaterialError').removeClass('d-none').text(data.message || 'Failed to delete material.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                $('#deleteMaterialError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });

    // Delegated click for Toggle Material Status
    let targetMatId = null;
    let targetMatStatus = null;

    $(document).on('click', '.toggle-mat-btn', function(e) {
        e.preventDefault();
        targetMatId = $(this).attr('data-id');
        targetMatStatus = parseInt($(this).attr('data-status'));
        const matName = $(this).attr('data-name');
        const actionText = (targetMatStatus === 1) ? 'deactivate' : 'activate';

        $('#materialConfirmMsg').text('Are you sure you want to ' + actionText + ' material "' + matName + '"?');
        $('#materialStatusError').addClass('d-none').text('');
        $('#confirmToggleMatBtn').prop('disabled', false).text('Confirm Change');
        openModal('materialConfirmModal');
    });

    $(document).on('click', '#confirmToggleMatBtn', function() {
        if (!targetMatId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: '<?= base_url("oem/toggleMaterialStatus"); ?>',
            type: 'POST',
            data: { id: targetMatId, m_id: targetMatId, status: targetMatStatus },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).text('Confirm Change');
                    $('#materialStatusError').removeClass('d-none').text(data.message || 'Operation failed.');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Confirm Change');
                $('#materialStatusError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });
});
</script>
