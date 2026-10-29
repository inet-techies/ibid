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

        <!-- OEM Vendors Main Card -->
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="feather-briefcase text-primary me-2"></i>OEM Vendors Directory
                    <span class="badge bg-primary ms-2 fs-11"><?= !empty($oems) ? count($oems) : 0; ?> Total</span>
                </h5>
                <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#addOemModal">
                    <i class="feather-plus me-1"></i> Register OEM
                </button>
            </div>

            <!-- Integrated Filter Toolbar -->
            <div class="card-body border-bottom bg-light py-3">
                <form method="get" action="<?= base_url('oem/oemMaster'); ?>" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="feather-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filter_inps['search'] ?? ''); ?>" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="all" <?= ($filter_inps['status'] ?? '') === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="1" <?= ($filter_inps['status'] ?? '') === '1' ? 'selected' : ''; ?>>Active Only</option>
                            <option value="0" <?= ($filter_inps['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="feather-filter me-1"></i> Filter
                        </button>
                        <a href="<?= base_url('oem/oemMaster'); ?>" class="btn btn-light" title="Reset Filters">
                            <i class="feather-rotate-ccw"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Body -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap wrap-no-text" id="oemTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>OEM Vendor</th>
                                <th>Contact Information</th>
                                <th>Office Address</th>
                                <th style="width: 140px;">Status</th>
                                <th class="text-end" style="width: 130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($oems)): ?>
                                <?php $i = 1; foreach ($oems as $oem): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark fs-14">
                                                <?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <span class="text-muted fs-11">Registered Vendor</span>
                                        </td>
                                        <td>
                                            <?php if (!empty($oem['MobileNumber'])): ?>
                                                <div><i class="feather-phone me-1 text-muted fs-12"></i><?= htmlspecialchars($oem['MobileNumber'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($oem['EmailId'])): ?>
                                                <div class="fs-12 text-muted"><i class="feather-mail me-1 text-muted fs-12"></i><?= htmlspecialchars($oem['EmailId'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php endif; ?>
                                            <?php if (empty($oem['MobileNumber']) && empty($oem['EmailId'])): ?>
                                                <span class="text-muted fs-12">No contact details</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($oem['Address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= !empty($oem['Address']) ? htmlspecialchars($oem['Address'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($oem['Status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success" id="badge-oem-<?= $oem['SNo']; ?>">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger" id="badge-oem-<?= $oem['SNo']; ?>">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md edit-oem-btn"
                                                    title="Edit OEM Details"
                                                    data-id="<?= $oem['SNo']; ?>"
                                                    data-name="<?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-mobile="<?= htmlspecialchars($oem['MobileNumber'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-email="<?= htmlspecialchars($oem['EmailId'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-address="<?= htmlspecialchars($oem['Address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-status="<?= $oem['Status']; ?>">
                                                    <i class="feather-edit-2"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md toggle-oem-btn"
                                                    title="Toggle Status (Active/Inactive)"
                                                    data-id="<?= $oem['SNo']; ?>"
                                                    data-status="<?= $oem['Status']; ?>"
                                                    data-name="<?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="feather-refresh-cw"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md delete-oem-btn text-danger"
                                                    title="Delete OEM Vendor"
                                                    data-id="<?= $oem['SNo']; ?>"
                                                    data-name="<?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Add OEM Modal -->
<div class="modal fade" id="addOemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="feather-plus-circle text-primary me-2"></i>Register New OEM Vendor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('oem/oemMaster'); ?>" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="oem_name" class="form-label fw-semibold">
                                OEM Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                <input type="text" class="form-control" id="oem_name" name="oem_name" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="mobile_number" class="form-label fw-semibold">Mobile Number</label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-phone"></i></div>
                                <input type="text" class="form-control" id="mobile_number" name="mobile_number" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email_id" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-mail"></i></div>
                                <input type="email" class="form-control" id="email_id" name="email_id" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold">Office Address / Location</label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-check me-1"></i> Register OEM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit OEM Right-Side Drawer Modal (Matching Bid Style) -->
<div class="right-form" id="editOemModal">
    <div class="right-form-header">
        <h5 class="fw-bold mb-0">
            <i class="feather-edit-2 text-primary me-2"></i>Edit OEM Vendor Details
        </h5>
        <button type="button" class="close-btn close-right-form" aria-label="Close">&times;</button>
    </div>
    <div class="right-form-body">
        <form action="<?= base_url('oem/updateOem'); ?>" method="post" autocomplete="off">
            <input type="hidden" name="s_no" id="edit_s_no" />
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">OEM Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-briefcase"></i></div>
                        <input type="text" name="oem_name" id="edit_oem_name" class="form-control" required />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mobile Number</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-phone"></i></div>
                        <input type="text" name="mobile_number" id="edit_mobile_number" class="form-control" />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email ID</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-mail"></i></div>
                        <input type="email" name="email_id" id="edit_email_id" class="form-control" />
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Office Address</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-map-pin"></i></div>
                        <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light close-right-form">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-check me-1"></i> Update OEM Details
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- OEM Toggle Confirmation Modal -->
<div class="modal fade" id="oemConfirmModal" tabindex="-1" aria-hidden="true">
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
                <p id="oemConfirmMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <div id="oemStatusError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmToggleOemBtn">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<!-- OEM Delete Confirmation Modal -->
<div class="modal fade" id="deleteOemModal" tabindex="-1" aria-hidden="true">
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
                <p id="deleteOemMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
                <div id="deleteOemError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteOemBtn">
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
    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#oemTable').length) {
        $('#oemTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            language: {
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found'
            }
        });
    }

    // Delegated click for Edit OEM
    $(document).on('click', '.edit-oem-btn', function(e) {
        e.preventDefault();
        $('#edit_s_no').val($(this).attr('data-id'));
        $('#edit_oem_name').val($(this).attr('data-name'));
        $('#edit_mobile_number').val($(this).attr('data-mobile'));
        $('#edit_email_id').val($(this).attr('data-email'));
        $('#edit_address').val($(this).attr('data-address'));
        $('#edit_status').val($(this).attr('data-status'));
        openModal('editOemModal');
    });

    // Delegated click for Toggle OEM Status
    let targetOemId = null;
    let targetOemStatus = null;

    $(document).on('click', '.toggle-oem-btn', function(e) {
        e.preventDefault();
        targetOemId = $(this).attr('data-id');
        targetOemStatus = parseInt($(this).attr('data-status'));
        const oemName = $(this).attr('data-name');
        const actionText = (targetOemStatus === 1) ? 'deactivate' : 'activate';

        $('#oemConfirmMsg').text('Are you sure you want to ' + actionText + ' OEM "' + oemName + '"?');
        $('#oemStatusError').addClass('d-none').text('');
        $('#confirmToggleOemBtn').prop('disabled', false).text('Confirm Change');
        openModal('oemConfirmModal');
    });

    $(document).on('click', '#confirmToggleOemBtn', function() {
        if (!targetOemId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: '<?= base_url("oem/toggleOemStatus"); ?>',
            type: 'POST',
            data: { id: targetOemId, s_no: targetOemId, status: targetOemStatus },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).text('Confirm Change');
                    $('#oemStatusError').removeClass('d-none').text(data.message || 'Operation failed.');
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Confirm Change');
                $('#oemStatusError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });

    // Delegated click for Delete OEM
    let targetDeleteOemId = null;
    $(document).on('click', '.delete-oem-btn', function(e) {
        e.preventDefault();
        targetDeleteOemId = $(this).attr('data-id');
        const oemName = $(this).attr('data-name');

        $('#deleteOemMsg').text('Are you sure you want to delete OEM "' + oemName + '"?');
        $('#deleteOemError').addClass('d-none').text('');
        $('#confirmDeleteOemBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
        openModal('deleteOemModal');
    });

    $(document).on('click', '#confirmDeleteOemBtn', function() {
        if (!targetDeleteOemId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

        $.ajax({
            url: '<?= base_url("oem/deleteOem"); ?>',
            type: 'POST',
            data: { id: targetDeleteOemId, s_no: targetDeleteOemId },
            dataType: 'json',
            success: function(data) {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                    $('#deleteOemError').removeClass('d-none').text(data.message || 'Failed to delete OEM vendor.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete');
                $('#deleteOemError').removeClass('d-none').text('Server communication error. Please try again.');
            }
        });
    });
});
</script>
