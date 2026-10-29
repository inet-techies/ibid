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

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs mb-4" id="projectTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="add-project-tab" data-bs-toggle="tab" data-bs-target="#addProjectPane" type="button" role="tab">
                    <i class="feather-plus-circle me-1"></i> Add Project & BOM
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="project-list-tab" data-bs-toggle="tab" data-bs-target="#projectListPane" type="button" role="tab">
                    <i class="feather-folder me-1"></i> Project Directory (<?= !empty($projects) ? count($projects) : 0; ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="projectTabContent">
            <!-- TAB 1: ADD PROJECT -->
            <div class="tab-pane fade show active" id="addProjectPane" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-file-text text-primary me-2"></i>Create New Project & Tender Entry
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('oem/project'); ?>" method="post" id="createProjectForm" autocomplete="off">
                            
                            <!-- Section 1: Location & Department -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold fs-12 mb-3 border-bottom pb-2">
                                    <i class="feather-map-pin me-1"></i> Authority & Location
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">State / Region <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-map"></i></div>
                                            <input type="text" class="form-control" name="state" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Department / Ministry <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                            <input type="text" class="form-control" name="department" required />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Project & Tender Details -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold fs-12 mb-3 border-bottom pb-2">
                                    <i class="feather-file me-1"></i> Project & Tender Identification
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Project Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-file-text"></i></div>
                                            <input type="text" class="form-control" name="project_name" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tender Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-hash"></i></div>
                                            <input type="text" class="form-control" name="tender_number" required />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Portal Link</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-link"></i></div>
                                            <input type="url" class="form-control" name="portal_link" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Milestones & Classification -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold fs-12 mb-3 border-bottom pb-2">
                                    <i class="feather-calendar me-1"></i> Timeline, Classification & Status
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Service / Bid OEM</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-award"></i></div>
                                            <input type="text" class="form-control" name="service_bid" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">RFB Published Date</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input type="date" class="form-control" name="rfb_published_date" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">RFB Submission Date</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-calendar"></i></div>
                                            <input type="date" class="form-control" name="rfb_submission_date" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status" required>
                                            <option value="1">Ongoing / Active</option>
                                            <option value="2">Upcoming</option>
                                            <option value="3">Completed</option>
                                            <option value="0">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Project Awarded (Vendor / OEM)</label>
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="feather-check-circle"></i></div>
                                            <input type="text" class="form-control" name="project_awarded" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Bill of Materials Section -->
                            <div class="border-top pt-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="feather-list text-primary me-2"></i>Bill of Materials (Required Equipment / Items)
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addMaterialRowBtn">
                                        <i class="feather-plus me-1"></i> Add Equipment Row
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-nowrap wrap-no-text" id="materialsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product / Equipment <span class="text-danger">*</span></th>
                                                <th style="width: 200px;">Required Quantity <span class="text-danger">*</span></th>
                                                <th style="width: 180px;">Unit</th>
                                                <th class="text-center" style="width: 70px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="materialsBody">
                                            <tr>
                                                <td>
                                                    <select name="material_product[]" class="form-select" required>
                                                        <option value="">-- Select Product / Equipment --</option>
                                                        <?php if (!empty($products)): ?>
                                                            <?php foreach ($products as $prod): ?>
                                                                <option value="<?= $prod['PId']; ?>">
                                                                    <?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                                    <?= !empty($prod['CategoryList']) ? ' ('.htmlspecialchars($prod['CategoryList']).')' : ''; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0.01" name="material_quantity[]" class="form-control" value="1" required />
                                                </td>
                                                <td>
                                                    <select name="material_unit[]" class="form-select">
                                                        <option value="Nos">Nos</option>
                                                        <option value="Units">Units</option>
                                                        <option value="Sets">Sets</option>
                                                        <option value="Meters">Meters</option>
                                                        <option value="Kg">Kg</option>
                                                        <option value="Lot">Lot</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" disabled>
                                                        <i class="feather-trash-2"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="reset" class="btn btn-md bg-danger text-white">
                                    <i class="feather-x me-1"></i> Clear
                                </button>
                                <button type="submit" class="btn btn-md btn-primary">
                                    <i class="feather-check me-1"></i> Save Project & Bill of Materials
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PROJECT DIRECTORY -->
            <div class="tab-pane fade" id="projectListPane" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">
                            <i class="feather-folder text-primary me-2"></i>Project Directory
                            <span class="badge bg-primary ms-2 fs-11"><?= !empty($projects) ? count($projects) : 0; ?> Total</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap wrap-no-text" id="projectDirectoryTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Project Title</th>
                                        <th>Tender Number</th>
                                        <th>State / Dept</th>
                                        <th>Submission Date</th>
                                        <th style="width: 140px;">Status</th>
                                        <th class="text-end" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($projects)): ?>
                                        <?php $i = 1; foreach ($projects as $pro): ?>
                                            <tr class="single-item">
                                                <td><?= $i++; ?></td>
                                                <td class="fw-semibold text-dark">
                                                    <a href="<?= base_url('oem/projectDetails/'.$pro['SNo']); ?>" class="text-primary text-decoration-none">
                                                        <?= htmlspecialchars($pro['ProjectName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <?php if (!empty($pro['PortalLink'])): ?>
                                                        <a href="<?= htmlspecialchars($pro['PortalLink']); ?>" target="_blank" class="ms-1 text-muted" title="Open Portal Link">
                                                            <i class="feather-external-link fs-11"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?= htmlspecialchars($pro['TenderNumber'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="d-block fw-medium text-dark"><?= htmlspecialchars($pro['State'] ?? ''); ?></span>
                                                    <span class="fs-12 text-muted"><?= htmlspecialchars($pro['Department'] ?? ''); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-muted fs-12">
                                                        <?= !empty($pro['RfbSubmissionDate']) ? date('d M Y', strtotime($pro['RfbSubmissionDate'])) : '-'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $st = (int)($pro['Status'] ?? 1);
                                                        if ($st === 1) echo '<span class="badge bg-success">Active / Ongoing</span>';
                                                        elseif ($st === 2) echo '<span class="badge bg-info">Upcoming</span>';
                                                        elseif ($st === 3) echo '<span class="badge bg-primary">Completed</span>';
                                                        else echo '<span class="badge bg-danger">Cancelled</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-2 justify-content-end">
                                                        <a href="<?= base_url('oem/projectDetails/'.$pro['SNo']); ?>"
                                                            class="avatar-text avatar-md"
                                                            title="View Project Details & Quotes">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                        <a href="javascript:void(0)"
                                                            class="avatar-text avatar-md edit-proj-btn"
                                                            title="Edit Project"
                                                            data-sno="<?= $pro['SNo']; ?>"
                                                            data-name="<?= htmlspecialchars($pro['ProjectName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-tender="<?= htmlspecialchars($pro['TenderNumber'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-state="<?= htmlspecialchars($pro['State'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-dept="<?= htmlspecialchars($pro['Department'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-portal="<?= htmlspecialchars($pro['PortalLink'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-service="<?= htmlspecialchars($pro['ServiceBidOem'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-pub="<?= $pro['RfbPublishedDate'] ?? ''; ?>"
                                                            data-sub="<?= $pro['RfbSubmissionDate'] ?? ''; ?>"
                                                            data-status="<?= $pro['Status'] ?? 1; ?>"
                                                            data-awarded="<?= htmlspecialchars($pro['ProjectAwarded'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="feather-edit-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0)"
                                                            class="avatar-text avatar-md delete-proj-btn text-danger"
                                                            title="Delete Project & Bill of Materials"
                                                            data-sno="<?= $pro['SNo']; ?>"
                                                            data-name="<?= htmlspecialchars($pro['ProjectName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
    </div>
</div>

<!-- Edit Project Right-Side Drawer Modal (Matching Bid Style) -->
<div class="right-form right-form-lg" id="editProjectModal">
    <div class="right-form-header">
        <h5 class="fw-bold mb-0">
            <i class="feather-edit-2 text-primary me-2"></i>Edit Project Information
        </h5>
        <button type="button" class="close-btn close-right-form" aria-label="Close">&times;</button>
    </div>
    <div class="right-form-body">
        <form action="<?= base_url('oem/updateProject'); ?>" method="post" autocomplete="off">
            <input type="hidden" name="s_no" id="edit_s_no" />
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">State / Region <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-map"></i></div>
                        <input type="text" class="form-control" name="state" id="edit_state" required />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-briefcase"></i></div>
                        <input type="text" class="form-control" name="department" id="edit_department" required />
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Project Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-file-text"></i></div>
                        <input type="text" class="form-control" name="project_name" id="edit_project_name" required />
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Tender Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-hash"></i></div>
                        <input type="text" class="form-control" name="tender_number" id="edit_tender_number" required />
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Portal Link</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-link"></i></div>
                        <input type="url" class="form-control" name="portal_link" id="edit_portal_link" />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service / Bid OEM</label>
                    <input type="text" class="form-control" name="service_bid" id="edit_service_bid" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" name="status" id="edit_status">
                        <option value="1">Active / Ongoing</option>
                        <option value="2">Upcoming</option>
                        <option value="3">Completed</option>
                        <option value="0">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Published Date</label>
                    <input type="date" class="form-control" name="rfb_published_date" id="edit_pub_date" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Submission Date</label>
                    <input type="date" class="form-control" name="rfb_submission_date" id="edit_sub_date" />
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Project Awarded</label>
                    <input type="text" class="form-control" name="project_awarded" id="edit_project_awarded" />
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-light close-right-form">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="feather-check me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- Project Delete Confirmation Modal -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
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
                <p id="deleteProjectMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">This will delete the project and all associated BOM records. This action cannot be undone.</p>
                <div id="deleteProjectError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteProjectBtn">
                    <i class="feather-trash me-1"></i> Delete Project
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
    if (window.jQuery && typeof $.fn.DataTable === 'function' && $('#projectDirectoryTable').length) {
        $('#projectDirectoryTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[0, 'asc']],
            language: {
                emptyTable: 'No records found',
                zeroRecords: 'No matching records found'
            }
        });
    }

    // Dynamic Material Rows in Add Project
    const addRowBtn = document.getElementById('addMaterialRowBtn');
    const materialsBody = document.getElementById('materialsBody');

    if (addRowBtn && materialsBody) {
        addRowBtn.addEventListener('click', function() {
            const firstRow = materialsBody.querySelector('tr');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '1');
            newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            newRow.querySelector('.remove-row-btn').disabled = false;
            materialsBody.appendChild(newRow);
            updateRemoveButtons();
        });

        materialsBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row-btn')) {
                const row = e.target.closest('tr');
                if (materialsBody.querySelectorAll('tr').length > 1) {
                    row.remove();
                    updateRemoveButtons();
                }
            }
        });

        function updateRemoveButtons() {
            const rows = materialsBody.querySelectorAll('tr');
            rows.forEach((r, idx) => {
                const btn = r.querySelector('.remove-row-btn');
                btn.disabled = (rows.length === 1);
            });
        }
    }

    if (window.jQuery) {
        $(document).on('click', '.edit-proj-btn', function() {
            const btn = $(this);
            $('#edit_s_no').val(btn.attr('data-sno') || '');
            $('#edit_project_name').val(btn.attr('data-name') || '');
            $('#edit_tender_number').val(btn.attr('data-tender') || '');
            $('#edit_state').val(btn.attr('data-state') || '');
            $('#edit_department').val(btn.attr('data-dept') || '');
            $('#edit_portal_link').val(btn.attr('data-portal') || '');
            $('#edit_service_bid').val(btn.attr('data-service') || '');
            $('#edit_pub_date').val(btn.attr('data-pub') || '');
            $('#edit_sub_date').val(btn.attr('data-sub') || '');
            $('#edit_status').val(btn.attr('data-status') || '1');
            $('#edit_project_awarded').val(btn.attr('data-awarded') || '');
            openModal('editProjectModal');
        });

        // Delete Project Handler - Delegated handler
        let targetDeleteProjSno = null;
        $(document).on('click', '.delete-proj-btn', function() {
            targetDeleteProjSno = $(this).attr('data-sno');
            const projName = $(this).attr('data-name');

            $('#deleteProjectMsg').text(`Are you sure you want to delete project "${projName}"?`);
            $('#deleteProjectError').addClass('d-none').text('');
            $('#confirmDeleteProjectBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete Project');
            openModal('deleteProjectModal');
        });

        $(document).on('click', '#confirmDeleteProjectBtn', function() {
            if (!targetDeleteProjSno) return;
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

            const formData = new FormData();
            formData.append('id', targetDeleteProjSno);
            formData.append('s_no', targetDeleteProjSno);

            fetch('<?= base_url("oem/deleteProject"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status || data.success) {
                    location.reload();
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete Project');
                    $('#deleteProjectError').removeClass('d-none').text(data.message || 'Failed to delete project.');
                }
            })
            .catch(() => {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete Project');
                $('#deleteProjectError').removeClass('d-none').text('Server communication error. Please try again.');
            });
        });
    }
});
</script>
