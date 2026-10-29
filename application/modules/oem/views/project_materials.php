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

        <!-- Visual Flow Banner Card -->
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="feather-layers text-primary me-2"></i>Project Materials & Bill of Materials Directory
                    </h5>
                    <p class="text-muted fs-12 mb-0">Cross-project overview of equipment, categories, quantities and units required across all active tenders</p>
                </div>
                <a href="<?= base_url('oem/project'); ?>" class="btn btn-primary btn-md">
                    <i class="feather-plus me-1"></i> Add / Edit Project BOM
                </a>
            </div>
            
            <!-- Flow Step Breadcrumb Visual -->
            <div class="card-body bg-light border-bottom py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 fs-12 text-muted fw-semibold">
                    <span class="badge bg-primary text-white px-3 py-2">1. Project</span>
                    <i class="feather-arrow-right text-muted"></i>
                    <span class="badge bg-white text-dark border px-3 py-2">2. Category</span>
                    <i class="feather-arrow-right text-muted"></i>
                    <span class="badge bg-white text-dark border px-3 py-2">3. Product</span>
                    <i class="feather-arrow-right text-muted"></i>
                    <span class="badge bg-white text-dark border px-3 py-2">4. Material Specification</span>
                    <i class="feather-arrow-right text-muted"></i>
                    <span class="badge bg-success text-white px-3 py-2">5. Quantity & Unit</span>
                </div>
            </div>

            <!-- Table Body -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap wrap-no-text" id="projectMaterialsTable" style="min-width: 950px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Project Name</th>
                                <th>Tender Number</th>
                                <th>Product / Equipment</th>
                                <th>Category</th>
                                <th style="width: 160px;">Required Quantity</th>
                                <th style="width: 120px;">Unit</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($projectMaterials)): ?>
                                <?php $i = 1; foreach ($projectMaterials as $pm): ?>
                                    <tr class="single-item">
                                        <td><?= $i++; ?></td>
                                        <td class="fw-semibold text-dark">
                                            <a href="<?= base_url('oem/projectDetails/'.$pm['ProjectId']); ?>" class="text-primary text-decoration-none">
                                                <?= htmlspecialchars($pm['ProjectName'] ?? 'Project', ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($pm['TenderNumber'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($pm['ProductList'] ?? 'Item', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($pm['MaterialName'])): ?>
                                                <span class="d-block text-muted fs-12"><?= htmlspecialchars($pm['MaterialName']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="feather-tag me-1"></i>
                                                <?= htmlspecialchars($pm['CategoryList'] ?? 'General', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-dark fs-14"><?= $pm['Quantity']; ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($pm['Unit'] ?? 'Nos'); ?></span></td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="<?= base_url('oem/projectDetails/'.$pm['ProjectId']); ?>"
                                                    class="avatar-text avatar-md"
                                                    title="View Full Project Details & Quotes">
                                                    <i class="feather-eye"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                    class="avatar-text avatar-md delete-pm-btn text-danger"
                                                    title="Remove Equipment from Project"
                                                    data-project-id="<?= $pm['ProjectId']; ?>"
                                                    data-pid="<?= $pm['PId']; ?>"
                                                    data-name="<?= htmlspecialchars($pm['ProductList'] ?? 'Item', ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Delete Project Material Modal -->
<div class="modal fade" id="deleteProjectMaterialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="feather-alert-triangle me-1"></i> Confirm Remove Equipment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <i class="feather-trash-2 fs-1 text-danger mb-3 d-block"></i>
                <p id="deletePmMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">Are you sure you want to remove this equipment from this project BOM?</p>
                <div id="deletePmError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeletePmBtn">
                    <i class="feather-trash me-1"></i> Remove
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

    if (window.jQuery) {
        $('.modal').appendTo('body');
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            const m = $(this).closest('.modal');
            if (m.length) closeModal(m.attr('id'));
        });
    }

    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#projectMaterialsTable').length) {
        $('#projectMaterialsTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            language: {
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found'
            }
        });
    }

    // Delete Project Material Handler - Delegated
    let targetPmProjectId = null;
    let targetPmPid = null;

    if (window.jQuery) {
        $(document).on('click', '.delete-pm-btn', function() {
            const btn = $(this);
            targetPmProjectId = btn.attr('data-project-id');
            targetPmPid = btn.attr('data-pid');
            const name = btn.attr('data-name');

            $('#deletePmMsg').text(`Remove "${name}" from this project?`);
            $('#deletePmError').addClass('d-none').text('');
            $('#confirmDeletePmBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Remove');
            openModal('deleteProjectMaterialModal');
        });

        $(document).on('click', '#confirmDeletePmBtn', function() {
            if (!targetPmProjectId || !targetPmPid) return;
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Removing...');

            const formData = new FormData();
            formData.append('project_id', targetPmProjectId);
            formData.append('p_id', targetPmPid);

            fetch('<?= base_url("oem/deleteProjectMaterial"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Remove');
                    $('#deletePmError').removeClass('d-none').text(data.message || 'Failed to remove equipment from project.');
                }
            })
            .catch(() => {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Remove');
                $('#deletePmError').removeClass('d-none').text('Server communication error. Please try again.');
            });
        });
    }
});
</script>
