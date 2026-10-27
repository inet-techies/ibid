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

        <!-- Price Entry Form Card -->
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="fw-bold mb-0">
                    <i class="feather-dollar-sign text-primary me-2"></i>Record OEM Price Quotes
                </h5>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('oem/priceHistory'); ?>" class="btn btn-outline-secondary btn-md">
                        <i class="feather-clock me-1"></i> Price History
                    </a>
                    <a href="<?= base_url('oem/priceComparison'); ?>" class="btn btn-outline-primary btn-md">
                        <i class="feather-bar-chart-2 me-1"></i> Price Comparison
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('oem/priceEntries'); ?>" method="post" id="priceEntriesForm" autocomplete="off">
                    <!-- Project & Tender Selection -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="project_select" class="form-label fw-semibold">
                                Select Project <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-briefcase"></i></div>
                                <select name="project_name" id="project_select" class="form-select" required>
                                    <option value="">-- Choose Project --</option>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $proj): ?>
                                            <option value="<?= $proj['SNo']; ?>" data-tender="<?= htmlspecialchars($proj['TenderNumber'] ?? ''); ?>">
                                                <?= htmlspecialchars($proj['ProjectName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                <?= !empty($proj['TenderNumber']) ? ' ('.htmlspecialchars($proj['TenderNumber']).')' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tender_display" class="form-label fw-semibold">Tender Number</label>
                            <div class="input-group">
                                <div class="input-group-text"><i class="feather-hash"></i></div>
                                <input type="text" class="form-control bg-light" id="tender_display" readonly />
                                <input type="hidden" name="tender_number" id="tender_hidden" />
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Quotation Items Table -->
                    <div class="border-top pt-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="feather-list text-primary me-2"></i>OEM Quotation Lines
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addQuoteRowBtn">
                                <i class="feather-plus me-1"></i> Add Quote Row
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="quotesInputTable" style="min-width: 1100px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 180px; min-width: 180px;">Category <span class="text-danger">*</span></th>
                                        <th style="width: 220px; min-width: 220px;">Product / Equipment <span class="text-danger">*</span></th>
                                        <th style="width: 190px; min-width: 190px;">OEM Vendor <span class="text-danger">*</span></th>
                                        <th style="width: 170px; min-width: 170px;">Quoted Price (₹) <span class="text-danger">*</span></th>
                                        <th style="width: 110px; min-width: 110px;">Unit</th>
                                        <th style="width: 170px; min-width: 170px;">Reason / Remarks</th>
                                        <th class="text-center" style="width: 60px; min-width: 60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="quotesBody">
                                    <tr>
                                        <td>
                                            <select name="inputCategory[]" class="form-select cat-select" required>
                                                <option value="">-- Category --</option>
                                                <?php if (!empty($categories)): ?>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?= $cat['CId']; ?>">
                                                            <?= htmlspecialchars($cat['CategoryList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="inputProduct[]" class="form-select prod-select" required>
                                                <option value="">-- Product --</option>
                                                <?php if (!empty($products)): ?>
                                                    <?php foreach ($products as $prod): ?>
                                                        <option value="<?= $prod['PId']; ?>" data-cat="<?= $prod['CategoryId']; ?>">
                                                            <?= htmlspecialchars($prod['ProductList'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="inputOEM[]" class="form-select" required>
                                                <option value="">-- OEM Vendor --</option>
                                                <?php if (!empty($oems)): ?>
                                                    <?php foreach ($oems as $oem): ?>
                                                        <option value="<?= $oem['SNo']; ?>">
                                                            <?= htmlspecialchars($oem['OemName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text fw-bold text-primary">₹</span>
                                                <input type="number" step="0.01" min="0" name="inputPrice[]" class="form-control text-end fw-bold text-dark" required />
                                            </div>
                                        </td>
                                        <td>
                                            <select name="inputUnit[]" class="form-select">
                                                <option value="Nos">Nos</option>
                                                <option value="Units">Units</option>
                                                <option value="Sets">Sets</option>
                                                <option value="Meters">Meters</option>
                                                <option value="Kg">Kg</option>
                                                <option value="Lot">Lot</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="inputchange_reason[]" class="form-control" />
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-quote-btn" disabled>
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
                            <i class="feather-check me-1"></i> Save Price Entries
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Recorded Quotes Card for Selected Project -->
        <div class="card mb-4" id="existingQuotesSection">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0">
                    <i class="feather-list text-primary me-2"></i>Recorded Quotes for Selected Project
                </h5>
                <span class="badge bg-secondary text-white fs-11" id="existingCountBadge">Select a project</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="existingQuotesTable" style="min-width: 980px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 140px;">Category</th>
                                <th style="width: 180px;">Product</th>
                                <th style="width: 160px;">OEM Vendor</th>
                                <th style="width: 140px;">Price (₹)</th>
                                <th style="width: 90px;">Unit</th>
                                <th style="min-width: 140px;">Change Reason</th>
                                <th style="width: 160px;">Recorded Date</th>
                                <th class="text-end" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="existingQuotesBody">
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="feather-info me-1"></i> Select a project above to view already recorded price quotes.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Price Right-Side Drawer Modal (Matching Bid Style) -->
<div class="right-form" id="editPriceModal">
    <div class="right-form-header">
        <h5 class="fw-bold mb-0">
            <i class="feather-edit-2 text-primary me-2"></i>Update OEM Quoted Price
        </h5>
        <button type="button" class="close-btn close-right-form" aria-label="Close">&times;</button>
    </div>
    <div class="right-form-body">
        <input type="hidden" id="modal_price_sno" />
        <div class="mb-3">
            <label class="form-label fw-semibold">Product & OEM</label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-box"></i></div>
                <input type="text" class="form-control bg-light" id="modal_product_oem" readonly />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">New Price (₹) <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text fw-bold text-primary">₹</span>
                <input type="number" step="0.01" min="0" class="form-control fw-bold text-dark" id="modal_new_price" required />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Reason for Price Change <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-text"><i class="feather-message-square"></i></div>
                <textarea class="form-control" id="modal_change_reason" rows="3" required></textarea>
            </div>
            <div id="editPriceModalError" class="alert alert-danger d-none mt-2 small mb-0"></div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button type="button" class="btn btn-light close-right-form">Cancel</button>
            <button type="button" class="btn btn-primary" id="savePriceUpdateBtn">
                <i class="feather-check me-1"></i> Save Price Update
            </button>
        </div>
    </div>
</div>

<div id="rightFormOverlay"></div>

<!-- Quote Delete Confirmation Modal -->
<div class="modal fade" id="deleteQuoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="feather-alert-triangle me-1"></i> Confirm Delete Quotation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <i class="feather-trash-2 fs-1 text-danger mb-3 d-block"></i>
                <p id="deleteQuoteMsg" class="mb-2 fs-15 text-dark fw-medium"></p>
                <p class="text-muted small mb-0">This price quotation entry will be deleted. This action cannot be undone.</p>
                <div id="deleteQuoteError" class="alert alert-danger d-none mt-3 text-start small mb-0"></div>
            </div>
            <div class="modal-footer border-top-0 justify-content-center pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteQuoteBtn">
                    <i class="feather-trash me-1"></i> Delete Quotation
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const projectSelect = document.getElementById('project_select');
    const tenderDisplay = document.getElementById('tender_display');
    const tenderHidden  = document.getElementById('tender_hidden');
    const existingBody  = document.getElementById('existingQuotesBody');
    const countBadge    = document.getElementById('existingCountBadge');

    projectSelect.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const tenderNo = selectedOpt.getAttribute('data-tender') || '';
        tenderDisplay.value = tenderNo;
        tenderHidden.value  = tenderNo;

        if (this.value) {
            loadExistingQuotes(this.value, tenderNo);
        } else {
            existingBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4"><i class="feather-info me-1"></i> Select a project above to view recorded price quotes.</td></tr>';
            countBadge.innerText = 'Select a project';
            countBadge.className = 'badge bg-secondary text-white fs-11';
        }
    });

    function loadExistingQuotes(projectId, tenderNo) {
        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('tender_no', tenderNo || '');

        fetch('<?= base_url("oem/getExistingPriceEntries"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(entries => {
            if (!entries || entries.length === 0) {
                existingBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4"><i class="feather-info me-1"></i> No recorded price quotes found for this project yet.</td></tr>';
                countBadge.innerText = '0 Quotes';
                countBadge.className = 'badge bg-secondary text-white fs-11';
                return;
            }

            countBadge.innerText = `${entries.length} Quotes Recorded`;
            countBadge.className = 'badge bg-success text-white fs-11';

            let rowsHtml = '';
            entries.forEach((item, idx) => {
                const formattedPrice = parseFloat(item.Price || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const dateStr = item.CreatedAt ? new Date(item.CreatedAt).toLocaleDateString('en-GB', {
                    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                }) : '-';

                rowsHtml += `
                    <tr class="single-item">
                        <td>${idx + 1}</td>
                        <td><span class="badge bg-light text-dark border">${item.CategoryList || '-'}</span></td>
                        <td class="fw-semibold text-dark">${item.ProductList || '-'}</td>
                        <td class="fw-semibold text-dark">${item.OemName || '-'}</td>
                        <td><span class="fw-bold text-primary fs-14">₹ ${formattedPrice}</span></td>
                        <td><span class="badge bg-light text-dark border">${item.Unit || 'Nos'}</span></td>
                        <td class="text-muted fs-13">${item.ChangeReason || '-'}</td>
                        <td class="text-muted fs-12">${dateStr}</td>
                        <td class="text-end">
                            <div class="hstack gap-2 justify-content-end">
                                <a href="javascript:void(0)"
                                    class="avatar-text avatar-md edit-price-btn"
                                    title="Edit Price"
                                    data-sno="${item.SNo}"
                                    data-info="${item.ProductList} - ${item.OemName}"
                                    data-price="${item.Price}"
                                    data-reason="${item.ChangeReason || ''}">
                                    <i class="feather-edit-2"></i>
                                </a>
                                <a href="javascript:void(0)"
                                    class="avatar-text avatar-md delete-price-btn text-danger"
                                    title="Delete Price Entry"
                                    data-sno="${item.SNo}"
                                    data-info="${item.ProductList} - ${item.OemName}">
                                    <i class="feather-trash-2"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            });
            existingBody.innerHTML = rowsHtml;
        });
    }

    // Dynamic Quote Rows in Form
    const addQuoteBtn = document.getElementById('addQuoteRowBtn');
    const quotesBody = document.getElementById('quotesBody');

    addQuoteBtn.addEventListener('click', function() {
        const firstRow = quotesBody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        newRow.querySelector('.remove-quote-btn').disabled = false;
        quotesBody.appendChild(newRow);
        updateRemoveButtons();
    });

    quotesBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-quote-btn')) {
            const row = e.target.closest('tr');
            if (quotesBody.querySelectorAll('tr').length > 1) {
                row.remove();
                updateRemoveButtons();
            }
        }
    });

    function updateRemoveButtons() {
        const rows = quotesBody.querySelectorAll('tr');
        rows.forEach((r, idx) => {
            const btn = r.querySelector('.remove-quote-btn');
            btn.disabled = (rows.length === 1);
        });
    }

    // Category -> Product cascade in quotes table
    quotesBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('cat-select')) {
            const catId = e.target.value;
            const row = e.target.closest('tr');
            const prodSelect = row.querySelector('.prod-select');

            if (!catId) {
                prodSelect.innerHTML = '<option value="">-- Product --</option>';
                return;
            }

            const formData = new FormData();
            formData.append('category_id', catId);

            fetch('<?= base_url("oem/getProductsByCategory"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(products => {
                prodSelect.innerHTML = '<option value="">-- Product --</option>';
                products.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.PId;
                    opt.textContent = p.ProductList;
                    prodSelect.appendChild(opt);
                });
            });
        }
    });

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

    // Relocate modal to body to avoid container filter blur / backdrop traps
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

        $(document).on('click', '.edit-price-btn', function() {
            const btn = $(this);
            $('#modal_price_sno').val(btn.attr('data-sno') || '');
            $('#modal_product_oem').val(btn.attr('data-info') || '');
            $('#modal_new_price').val(btn.attr('data-price') || '');
            $('#modal_change_reason').val(btn.attr('data-reason') || '');
            openModal('editPriceModal');
        });

        $('#savePriceUpdateBtn').on('click', function() {
            const sNo = $('#modal_price_sno').val();
            const newPrice = $('#modal_new_price').val();
            const reason = $('#modal_change_reason').val();
            const btn = $(this);

            if (!newPrice || !reason) {
                $('#editPriceModalError').removeClass('d-none').text('Please enter both the new price and justification reason.');
                return;
            }
            $('#editPriceModalError').addClass('d-none').text('');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            const formData = new FormData();
            formData.append('s_no', sNo);
            formData.append('price', newPrice);
            formData.append('change_reason', reason);

            fetch('<?= base_url("oem/updatePriceEntry"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.prop('disabled', false).html('<i class="feather-check me-1"></i> Save Price Update');
                if (data.status || data.success) {
                    closeModal('editPriceModal');
                    const projId = projectSelect.value;
                    const tenderNo = tenderHidden.value;
                    loadExistingQuotes(projId, tenderNo);
                } else {
                    $('#editPriceModalError').removeClass('d-none').text(data.message || 'Update failed.');
                }
            })
            .catch(() => {
                btn.prop('disabled', false).html('<i class="feather-check me-1"></i> Save Price Update');
                $('#editPriceModalError').removeClass('d-none').text('Server communication error. Please try again.');
            });
        });

        // Delete Price Entry Handler - Modal Based
        let targetDeleteQuoteSno = null;
        $(document).on('click', '.delete-price-btn', function() {
            const delBtn = $(this);
            targetDeleteQuoteSno = delBtn.attr('data-sno');
            const info = delBtn.attr('data-info');

            $('#deleteQuoteMsg').text(`Delete the price quotation for "${info}"?`);
            $('#deleteQuoteError').addClass('d-none').text('');
            $('#confirmDeleteQuoteBtn').prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete Quotation');
            openModal('deleteQuoteModal');
        });

        $(document).on('click', '#confirmDeleteQuoteBtn', function() {
            if (!targetDeleteQuoteSno) return;
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

            const formData = new FormData();
            formData.append('id', targetDeleteQuoteSno);
            formData.append('s_no', targetDeleteQuoteSno);

            fetch('<?= base_url("oem/deletePriceEntry"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status || data.success) {
                    closeModal('deleteQuoteModal');
                    const projId = projectSelect.value;
                    const tenderNo = tenderHidden.value;
                    loadExistingQuotes(projId, tenderNo);
                } else {
                    btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete Quotation');
                    $('#deleteQuoteError').removeClass('d-none').text(data.message || 'Failed to delete quotation.');
                }
            })
            .catch(() => {
                btn.prop('disabled', false).html('<i class="feather-trash me-1"></i> Delete Quotation');
                $('#deleteQuoteError').removeClass('d-none').text('Server communication error. Please try again.');
            });
        });
    }
});
</script>
