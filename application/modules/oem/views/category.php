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

        <!-- Category Card with Natural Height -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-tag text-primary me-2"></i>Category List
                    <span class="badge bg-primary ms-2 fs-11"><?= !empty($categories) ? count($categories) : 0; ?> Total</span>
                </h5>
                <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="feather-plus me-1"></i> Add Category
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="categoryTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Category Name</th>
                                <th style="width: 160px;">Status</th>
                                <th style="width: 180px;">Created Date</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php $i = 1; foreach ($categories as $cat): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($cat['Status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success" id="badge-<?= $cat['CId']; ?>">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger" id="badge-<?= $cat['CId']; ?>">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-muted fs-12">
                                                <?= !empty($cat['CreatedAt']) ? date('d M Y, h:i A', strtotime($cat['CreatedAt'])) : '-'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md edit-cat-btn"
                                                    title="Edit Category"
                                                    data-id="<?= $cat['CId']; ?>"
                                                    data-name="<?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-edit-2"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md toggle-cat-btn"
                                                    title="Toggle Status (Active/Inactive)"
                                                    data-id="<?= $cat['CId']; ?>"
                                                    data-status="<?= $cat['Status']; ?>"
                                                    data-name="<?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-refresh-cw"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md delete-cat-btn text-danger"
                                                    title="Delete Category"
                                                    data-id="<?= $cat['CId']; ?>"
                                                    data-name="<?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="feather-alert-circle me-1"></i> No categories registered yet. Click "+ Add Category" to create one.
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="feather-plus-circle text-primary me-2"></i>Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('oem/category'); ?>" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label fw-semibold">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-text"><i class="feather-tag"></i></div>
                            <input type="text" class="form-control" id="category_name" name="category_name" required />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-check me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Right-Side Drawer Modal (Matching Bid Style) -->
<div class="right-form" id="editCategoryModal">
    <div class="right-form-header">
        <h5 class="fw-bold mb-0">
            <i class="feather-edit-2 text-primary me-2"></i>Edit Category
        </h5>
        <button type="button" class="close-btn close-right-form" aria-label="Close">&times;</button>
    </div>
    <div class="right-form-body">
        <form action="<?= base_url('oem/updateCategory'); ?>" method="post" autocomplete="off">
            <input type="hidden" name="c_id" id="edit_c_id" />
            <div class="mb-3">
                <label for="edit_category_name" class="form-label fw-semibold">
                    Category Name <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-tag"></i></div>
                    <input type="text" class="form-control" id="edit_category_name" name="category_name" required />
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light close-right-form">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-check me-1"></i> Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- Category Toggle Confirmation Modal -->
<div class="modal fade" id="categoryConfirmModal" tabindex="-1" aria-hidden="true">
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
                <p id="categoryConfirmMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <div id="categoryStatusError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmToggleBtn">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<!-- Category Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
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
                <p id="deleteCategoryMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
                <div id="deleteCategoryError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteCategoryBtn">
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
    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#categoryTable').length) {
        $('#categoryTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']]
        });
    }

    // Delegated click for Edit Category
    $(document).on('click', '.edit-cat-btn', function(e) {
        e.preventDefault();
        const cId = $(this).attr('data-id');
        const name = $(this).attr('data-name');
        $('#edit_c_id').val(cId);
        $('#edit_category_name').val(name);
        openModal('editCategoryModal');
    });

    // Delegated click for Delete Category
    let targetDeleteCatId = null;
    $(document).on('click', '.delete-cat-btn', function(e) {
        e.preventDefault();
        targetDeleteCatId = $(this).attr('data-id');
        const name = $(this).attr('data-name');

        $('#deleteCategoryMsg').text('Are you sure you want to delete category "' + name + '"?');
        $('#deleteCategoryError').addClass('d-none').text('');
        $('#confirmDeleteCategoryBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
        openModal('deleteCategoryModal');
    });

    $(document).on('click', '#confirmDeleteCategoryBtn', function() {
        if (!targetDeleteCatId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

        $.ajax({
            url: '<?= base_url("oem/deleteCategory"); ?>',
            type: 'POST',
            data: { id: targetDeleteCatId, c_id: targetDeleteCatId },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                    $('#deleteCategoryError').removeClass('d-none').text(data.message || 'Failed to delete category.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                $('#deleteCategoryError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });

    // Delegated click for Status Toggle
    let targetCatId = null;
    let targetCatStatus = null;

    $(document).on('click', '.toggle-cat-btn', function(e) {
        e.preventDefault();
        targetCatId = $(this).attr('data-id');
        targetCatStatus = parseInt($(this).attr('data-status'));
        const catName = $(this).attr('data-name');
        const actionText = (targetCatStatus === 1) ? 'deactivate' : 'activate';

        $('#categoryConfirmMsg').text('Are you sure you want to ' + actionText + ' category "' + catName + '"?');
        $('#categoryStatusError').addClass('d-none').text('');
        $('#confirmToggleBtn').prop('disabled', false).text('Confirm Change');
        openModal('categoryConfirmModal');
    });

    $(document).on('click', '#confirmToggleBtn', function() {
        if (!targetCatId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: '<?= base_url("oem/toggleCategoryStatus"); ?>',
            type: 'POST',
            data: { id: targetCatId, c_id: targetCatId, status: targetCatStatus },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).text('Confirm Change');
                    $('#categoryStatusError').removeClass('d-none').text(data.message || 'Operation failed.');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Confirm Change');
                $('#categoryStatusError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });
});
</script>
