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

        <!-- Register Product Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-plus-circle text-primary me-2"></i>Register New Product
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('oem/product'); ?>" method="post" autocomplete="off">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="category_name" class="form-label fw-semibold">
                                Category <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-folder"></i></div>
                                <select name="category_name" id="category_name" class="form-select" required>
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
                            <label for="product_name" class="form-label fw-semibold">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-package"></i></div>
                                <input type="text" class="form-control" id="product_name" name="product_name" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="oem_name" class="form-label fw-semibold">
                                OEM Vendor <span class="text-muted">(Optional)</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-award"></i></div>
                                <select name="oem_name" id="oem_name" class="form-select">
                                    <option value="">-- Select OEM (Optional) --</option>
                                    <?php if (!empty($oems)): ?>
                                        <?php foreach ($oems as $oem): ?>
                                            <option value="<?= $oem['SNo']; ?>">
                                                <?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                            <button type="reset" class="btn btn-md bg-danger text-white">
                                <i class="feather-x me-1"></i> Clear
                            </button>
                            <button type="submit" class="btn btn-md btn-primary">
                                <i class="feather-check me-1"></i> Register Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Directory Table Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-box text-primary me-2"></i>Product Catalogue
                    <span class="badge bg-primary ms-2 fs-11"><?= !empty($products) ? count($products) : 0; ?> Total</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap wrap-no-text" id="productTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Associated OEM</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 180px;">Created Date</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php $i = 1; foreach ($products as $prod): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="feather-tag me-1"></i>
                                                <?= htmlspecialchars($prod['CategoryList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= !empty($prod['OemName']) ? htmlspecialchars($prod['OemName'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Not specified</span>'; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($prod['Status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success" id="badge-prod-<?= $prod['PId']; ?>">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger" id="badge-prod-<?= $prod['PId']; ?>">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-12">
                                                <?= !empty($prod['CreatedAt']) ? date('d M Y, h:i A', strtotime($prod['CreatedAt'])) : '-'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md edit-prod-btn"
                                                    title="Edit Product"
                                                    data-id="<?= $prod['PId']; ?>"
                                                    data-name="<?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-cat="<?= $prod['CategoryId']; ?>"
                                                    data-oem="<?= $prod['OemId'] ?? ''; ?>"
                                                    data-status="<?= $prod['Status']; ?>">
                                                    <i class="feather-edit-2"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md toggle-prod-btn"
                                                    title="Toggle Status (Active/Inactive)"
                                                    data-id="<?= $prod['PId']; ?>"
                                                    data-status="<?= $prod['Status']; ?>"
                                                    data-name="<?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-refresh-cw"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md delete-prod-btn text-danger"
                                                    title="Delete Product"
                                                    data-id="<?= $prod['PId']; ?>"
                                                    data-name="<?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Right-Side Drawer Modal (Matching Bid Style) -->
<div class="right-form" id="editProductModal">
    <div class="right-form-header">
        <h5 class="fw-bold mb-0">
            <i class="feather-edit-2 text-primary me-2"></i>Edit Product
        </h5>
        <button type="button" class="close-btn close-right-form" aria-label="Close">&times;</button>
    </div>
    <div class="right-form-body">
        <form action="<?= base_url('oem/updateProduct'); ?>" method="post" autocomplete="off">
            <input type="hidden" name="p_id" id="edit_p_id" />
            <div class="mb-3">
                <label for="edit_product_cat" class="form-label fw-semibold">
                    Category <span class="text-danger">*</span>
                </label>
                <select name="category_name" id="edit_product_cat" class="form-select" required>
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
                <label for="edit_product_name" class="form-label fw-semibold">
                    Product Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="edit_product_name" name="product_name" required />
            </div>
            <div class="mb-3">
                <label for="edit_product_oem" class="form-label fw-semibold">
                    Associated OEM Vendor <span class="text-muted">(Optional)</span>
                </label>
                <select name="oem_name" id="edit_product_oem" class="form-select">
                    <option value="">-- Select OEM (Optional) --</option>
                    <?php if (!empty($oems)): ?>
                        <?php foreach ($oems as $oem): ?>
                            <option value="<?= $oem['SNo']; ?>">
                                <?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="edit_product_status" class="form-label fw-semibold">Status</label>
                <select name="status" id="edit_product_status" class="form-select">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light close-right-form">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-check me-1"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- Product Toggle Confirmation Modal -->
<div class="modal fade" id="productConfirmModal" tabindex="-1" aria-hidden="true">
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
                <p id="productConfirmMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <div id="productStatusError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmToggleProdBtn">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<!-- Product Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
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
                <p id="deleteProductMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
                <div id="deleteProductError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteProdBtn">
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
    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#productTable').length) {
        $('#productTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            language: {
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found'
            }
        });
    }

    // Delegated click for Edit Product
    $(document).on('click', '.edit-prod-btn', function(e) {
        e.preventDefault();
        $('#edit_p_id').val($(this).attr('data-id'));
        $('#edit_product_name').val($(this).attr('data-name'));
        $('#edit_product_cat').val($(this).attr('data-cat'));
        $('#edit_product_oem').val($(this).attr('data-oem') || '');
        $('#edit_product_status').val($(this).attr('data-status') || '1');
        openModal('editProductModal');
    });

    // Delegated click for Delete Product
    let targetDeleteProdId = null;
    $(document).on('click', '.delete-prod-btn', function(e) {
        e.preventDefault();
        targetDeleteProdId = $(this).attr('data-id');
        const name = $(this).attr('data-name');

        $('#deleteProductMsg').text('Are you sure you want to delete product "' + name + '"?');
        $('#deleteProductError').addClass('d-none').text('');
        $('#confirmDeleteProdBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
        openModal('deleteProductModal');
    });

    $(document).on('click', '#confirmDeleteProdBtn', function() {
        if (!targetDeleteProdId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

        $.ajax({
            url: '<?= base_url("oem/deleteProduct"); ?>',
            type: 'POST',
            data: { id: targetDeleteProdId, p_id: targetDeleteProdId },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                    $('#deleteProductError').removeClass('d-none').text(data.message || 'Failed to delete product.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                $('#deleteProductError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });

    // Delegated click for Product Status Toggle
    let targetProdId = null;
    let targetProdStatus = null;

    $(document).on('click', '.toggle-prod-btn', function(e) {
        e.preventDefault();
        targetProdId = $(this).attr('data-id');
        targetProdStatus = parseInt($(this).attr('data-status'));
        const prodName = $(this).attr('data-name');
        const actionText = (targetProdStatus === 1) ? 'deactivate' : 'activate';

        $('#productConfirmMsg').text('Are you sure you want to ' + actionText + ' product "' + prodName + '"?');
        $('#productStatusError').addClass('d-none').text('');
        $('#confirmToggleProdBtn').prop('disabled', false).text('Confirm Change');
        openModal('productConfirmModal');
    });

    $(document).on('click', '#confirmToggleProdBtn', function() {
        if (!targetProdId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: '<?= base_url("oem/toggleProductStatus"); ?>',
            type: 'POST',
            data: { id: targetProdId, p_id: targetProdId, status: targetProdStatus },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).text('Confirm Change');
                    $('#productStatusError').removeClass('d-none').text(data.message || 'Operation failed.');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Confirm Change');
                $('#productStatusError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });
});
</script>
