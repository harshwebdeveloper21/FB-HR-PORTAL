<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
.gadget-container {
    font-family: "Inter", sans-serif;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}
.gadget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
.btn-theme {
    background-color: rgb(230, 97, 54) !important;
    border-color: rgb(230, 97, 54) !important;
    color: #fff !important;
}
.btn-theme:hover {
    background-color: rgb(210, 77, 34) !important;
    border-color: rgb(210, 77, 34) !important;
    color: #fff !important;
}
.badge-issued {
    background-color: #e3f2fd;
    color: #0d6efd;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 600;
}
.badge-returned {
    background-color: #d1e7dd;
    color: #0f5132;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 600;
}
.badge-damaged {
    background-color: #fff3cd;
    color: #664d03;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 600;
}
.badge-lost {
    background-color: #f8d7da;
    color: #842029;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 600;
}
.select2-container--default .select2-selection--single {
    height: 38px !important;
    padding: 4px 8px !important;
    border: 1px solid #ced4da !important;
    border-radius: 6px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
    color: #212529 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
.select2-dropdown {
    z-index: 1065 !important;
    border-color: rgb(230, 97, 54) !important;
    border-radius: 6px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: rgb(230, 97, 54) !important;
    color: #fff !important;
}
.select2-search__field {
    border-radius: 4px !important;
    border: 1px solid #ced4da !important;
}
.select2-search__field:focus {
    border-color: rgb(230, 97, 54) !important;
    outline: none !important;
}
</style>

<div class="content-wrapper gadget-container">
    <!-- Header -->
    <div class="gadget-header">
        <div>
            <h3 class="mb-1 fw-bold"><i class="mdi mdi-laptop text-primary me-2"></i> Gadget Issuance Management</h3>
            <p class="text-muted mb-0">Issue, track, and manage official company gadgets and assets assigned to employees.</p>
        </div>
        <button type="button" class="btn btn-theme font-13 fw-bold px-3 py-2" id="btnAddGadget">
            <i class="mdi mdi-plus-circle me-1"></i> Add Gadget Issuance
        </button>
    </div>

    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                <input type="text" id="gadgetSearchInput" class="form-control border-start-0" placeholder="Search by employee, gadget, serial...">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="gadgetTable">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Employee</th>
                    <th>Gadget Name & Type</th>
                    <th>Serial / Model No.</th>
                    <th>Issuance Date</th>
                    <th>Return Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="gadgetTableBody">
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading gadget issuances...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="gadgetFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: rgb(230, 97, 54);">
                <h5 class="modal-title fw-bold" id="modalFormTitle"><i class="mdi mdi-plus-circle me-1"></i> Add Gadget Issuance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="gadgetForm" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="gadget_id">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Employee <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-select select2 w-100">
                                <option value="">Select Employee...</option>
                                <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>">
                                        <?= esc(trim(($emp['firstname'] ?? '') . ' ' . ($emp['lastname'] ?? ''))) ?: esc($emp['username']) ?> 
                                        <?= !empty($emp['employee_id']) ? ' (' . esc($emp['employee_id']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <div class="invalid-feedback text-danger mt-1" id="user_id_error" style="display: none;">Please select an employee.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gadget Name <span class="text-danger">*</span></label>
                            <input type="text" name="gadget_name" id="gadget_name" class="form-control" placeholder="e.g. MacBook Pro, Dell Monitor">
                            <div class="invalid-feedback text-danger mt-1" id="gadget_name_error" style="display: none;">Please enter gadget name.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0">Gadget Type</label>
                                <button type="button" class="btn btn-sm p-0 fw-bold border-0 bg-transparent font-12" id="btnAddGadgetType" style="color: rgb(230, 97, 54);" title="Add New Gadget Type">
                                    <i class="mdi mdi-plus-circle font-14 me-1"></i>+ Add New Type
                                </button>
                            </div>
                            <select name="gadget_type" id="gadget_type" class="form-select">
                                <option value="Laptop">Laptop</option>
                                <option value="Mobile / Smartphone">Mobile / Smartphone</option>
                                <option value="Monitor / Display">Monitor / Display</option>
                                <option value="Tablet / iPad">Tablet / iPad</option>
                                <option value="Accessory (Headset, Mouse, etc.)">Accessory (Headset, Mouse, etc.)</option>
                                <option value="Peripheral / Hardware">Peripheral / Hardware</option>
                                <option value="Other Asset">Other Asset</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Issued">Issued</option>
                                <option value="Returned">Returned</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Serial Number</label>
                            <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="e.g. SN-987654321">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Model Number</label>
                            <input type="text" name="model_number" id="model_number" class="form-control" placeholder="e.g. A2442, XPS 15">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Issuance Date <span class="text-danger">*</span></label>
                            <input type="date" name="issuance_date" id="issuance_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            <div class="invalid-feedback text-danger mt-1" id="issuance_date_error" style="display: none;">Please select issuance date.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Return Date <span class="text-danger" id="return_date_asterisk" style="display: none;">*</span></label>
                            <input type="date" name="return_date" id="return_date" class="form-control">
                            <div class="invalid-feedback text-danger mt-1" id="return_date_error" style="display: none;">Please select return date.</div>
                        </div>
                    </div>

                    <!-- Return Condition & Damage Report Section -->
                    <div class="p-3 bg-light rounded border mb-3" id="damageConditionSection" style="display: none; border-left: 4px solid rgb(230, 97, 54) !important;">
                        <h6 class="fw-bold text-dark mb-3"><i class="mdi mdi-alert-circle-outline text-warning me-1"></i> Return Condition & Damage Assessment</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gadget Condition</label>
                                <select name="gadget_condition" id="gadget_condition" class="form-select">
                                    <option value="Good / Normal">Good / Normal</option>
                                    <option value="Minor Scratch / Wear">Minor Scratch / Wear</option>
                                    <option value="Screen / Display Damaged">Screen / Display Damaged</option>
                                    <option value="Hardware / Body Damaged">Hardware / Body Damaged</option>
                                    <option value="Technical Fault / Non-Working">Technical Fault / Non-Working</option>
                                    <option value="Severe Damage / Broken">Severe Damage / Broken</option>
                                    <option value="Lost / Missing">Lost / Missing</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Damage / Repair Cost ($)</label>
                                <input type="number" step="0.01" name="damage_cost" id="damage_cost" class="form-control" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Damage / Condition Details</label>
                            <textarea name="damage_details" id="damage_details" class="form-control" rows="2" placeholder="Describe any damage, physical defects, or issues upon return..."></textarea>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold">Remarks / Notes</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="e.g. Issued with charger and laptop bag."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme fw-bold px-4">
                        <i class="mdi mdi-content-save me-1"></i> Save Gadget Issuance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="gadgetViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 680px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: rgb(230, 97, 54);">
                <h5 class="modal-title fw-bold"><i class="mdi mdi-information-outline me-2"></i> Gadget Issuance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Employee Info Card -->
                <div class="p-3 mb-3 rounded bg-light border-start border-4" style="border-left-color: rgb(230, 97, 54) !important;">
                    <small class="text-muted text-uppercase fw-bold font-11 d-block mb-1"><i class="mdi mdi-account me-1"></i> Assigned Employee</small>
                    <div id="view_emp_name" class="fw-bold fs-6 text-dark"></div>
                </div>

                <div class="row g-3">
                    <!-- Row 1: Gadget Name & Type -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-laptop text-primary me-1"></i> Gadget Name</small>
                            <span id="view_gadget_name" class="fw-bold text-dark font-14"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-devices text-primary me-1"></i> Gadget Type</small>
                            <span id="view_gadget_type" class="fw-semibold text-dark font-14"></span>
                        </div>
                    </div>

                    <!-- Row 2: Serial Number & Model Number -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-barcode-scan me-1"></i> Serial Number</small>
                            <span id="view_serial_number" class="fw-semibold text-dark font-14"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-tag-outline me-1"></i> Model Number</small>
                            <span id="view_model_number" class="fw-semibold text-dark font-14"></span>
                        </div>
                    </div>

                    <!-- Row 3: Issuance Date & Return Date -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-calendar-import me-1"></i> Issuance Date</small>
                            <span id="view_issuance_date" class="fw-bold text-dark font-14"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-calendar-check me-1"></i> Return Date</small>
                            <span id="view_return_date" class="fw-bold text-dark font-14"></span>
                        </div>
                    </div>

                    <!-- Row 4: Status & Condition -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-flag-outline me-1"></i> Status</small>
                            <span id="view_status" class="fw-bold"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-shield-check-outline me-1"></i> Gadget Condition</small>
                            <span id="view_gadget_condition" class="fw-semibold text-dark font-14"></span>
                        </div>
                    </div>

                    <!-- Row 5: Damage Cost & Details -->
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-currency-usd me-1"></i> Damage / Repair Cost</small>
                            <span id="view_damage_cost" class="fw-bold text-danger font-14"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-alert-outline me-1"></i> Damage Details</small>
                            <span id="view_damage_details" class="text-muted font-14"></span>
                        </div>
                    </div>

                    <!-- Row 6: Remarks / Notes -->
                    <div class="col-12">
                        <div class="border rounded p-3 bg-white">
                            <small class="text-muted text-uppercase fw-semibold font-11 d-block mb-1"><i class="mdi mdi-note-text-outline me-1"></i> Remarks / Notes</small>
                            <span id="view_remarks" class="text-dark font-14"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Gadget Type -->
<div class="modal fade" id="addGadgetTypeModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background-color: rgb(230, 97, 54);">
                <h6 class="modal-title fw-bold mb-0"><i class="mdi mdi-plus-circle me-1"></i> Add New Gadget Type</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-2">
                    <label class="form-label fw-bold font-13">New Gadget Type Name <span class="text-danger">*</span></label>
                    <input type="text" id="new_gadget_type_input" class="form-control" placeholder="e.g. Smartwatch, Projector, Printer">
                    <div class="invalid-feedback text-danger mt-1" id="new_gadget_type_error" style="display: none;">Please enter gadget type name.</div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-theme btn-sm fw-bold px-3" id="btnSaveNewGadgetType">
                    <i class="mdi mdi-check me-1"></i> Add & Select
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let allGadgetData = [];

function initEmployeeSelect2() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        if ($('#user_id').hasClass('select2-hidden-accessible')) {
            $('#user_id').select2('destroy');
        }
        $('#user_id').select2({
            placeholder: 'Select Employee...',
            allowClear: true,
            dropdownParent: $('#gadgetFormModal'),
            width: '100%'
        });
    }
}

function loadGadgetTypes(selectValue = null) {
    fetch('/api/gadget-issuance/types')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.types) {
                const sel = document.getElementById('gadget_type');
                const currentVal = selectValue || sel.value;
                sel.innerHTML = '';

                const uniqueTypes = Array.from(new Set(res.types));
                uniqueTypes.forEach(t => {
                    if (t) {
                        const opt = document.createElement('option');
                        opt.value = t;
                        opt.textContent = t;
                        sel.appendChild(opt);
                    }
                });

                if (currentVal && sel.querySelector(`option[value="${CSS.escape(currentVal)}"]`)) {
                    sel.value = currentVal;
                }
            }
        })
        .catch(err => console.error(err));
}

function loadGadgetIssuances() {
    fetch('/api/gadget-issuance')
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                allGadgetData = res.data || [];
                renderGadgetTable(allGadgetData);
            } else {
                document.getElementById('gadgetTableBody').innerHTML = 
                    '<tr><td colspan="8" class="text-center py-4 text-muted">No gadget issuances found.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('gadgetTableBody').innerHTML = 
                '<tr><td colspan="8" class="text-center py-4 text-danger">Error loading gadget issuances.</td></tr>';
        });
}

function renderGadgetTable(data) {
    const tbody = document.getElementById('gadgetTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No gadget issuances found.</td></tr>';
        return;
    }

    tbody.innerHTML = data.map((item, index) => {
        let badgeClass = 'badge-issued';
        let st = (item.status || '').toLowerCase();
        if (st === 'returned') badgeClass = 'badge-returned';
        else if (st === 'damaged') badgeClass = 'badge-damaged';
        else if (st === 'lost') badgeClass = 'badge-lost';

        let conditionSub = '';
        if (item.gadget_condition && item.gadget_condition !== 'Good / Normal') {
            conditionSub = `<small class="text-danger d-block font-11 fw-semibold"><i class="mdi mdi-alert"></i> ${escapeHtml(item.gadget_condition)}</small>`;
        }

        return `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="fw-bold text-dark">${escapeHtml(item.employee_name || 'N/A')}</div>
                    ${item.emp_code ? `<small class="text-muted">(${escapeHtml(item.emp_code)})</small>` : ''}
                </td>
                <td>
                    <div class="fw-semibold">${escapeHtml(item.gadget_name)}</div>
                    <small class="text-muted">${escapeHtml(item.gadget_type || 'N/A')}</small>
                </td>
                <td>
                    <div>SN: ${escapeHtml(item.serial_number || 'N/A')}</div>
                    <small class="text-muted">Model: ${escapeHtml(item.model_number || 'N/A')}</small>
                </td>
                <td>${formatDisplayDate(item.issuance_date)}</td>
                <td>${item.return_date ? formatDisplayDate(item.return_date) : '<span class="text-muted">-</span>'}</td>
                <td class="text-center">
                    <span class="${badgeClass}">${escapeHtml(item.status || 'Issued')}</span>
                    ${conditionSub}
                </td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewGadget(${item.id})" title="View Details">
                        <i class="mdi mdi-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm text-white me-1" style="background-color: rgb(230, 97, 54); border-color: rgb(230, 97, 54);" onclick="editGadget(${item.id})" title="Edit">
                        <i class="mdi mdi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteGadget(${item.id})" title="Delete">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function formatDisplayDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Toggle condition & damage section & return date requirement on status change
function toggleDamageConditionSection() {
    const statusVal = document.getElementById('status').value;
    const sec = document.getElementById('damageConditionSection');
    const reqAsterisk = document.getElementById('return_date_asterisk');
    const returnDateInput = document.getElementById('return_date');

    if (statusVal === 'Returned' || statusVal === 'Damaged') {
        sec.style.display = 'block';
    } else {
        sec.style.display = 'none';
    }

    if (statusVal === 'Returned') {
        reqAsterisk.style.display = 'inline';
        if (!returnDateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            returnDateInput.value = today;
        }
    } else {
        reqAsterisk.style.display = 'none';
    }
}
document.getElementById('status').addEventListener('change', toggleDamageConditionSection);

// Add button handler
document.getElementById('btnAddGadget').addEventListener('click', function() {
    document.getElementById('gadgetForm').reset();
    document.getElementById('gadget_id').value = '';
    document.getElementById('status').value = 'Issued';
    document.getElementById('gadget_condition').value = 'Good / Normal';
    document.getElementById('damage_details').value = '';
    document.getElementById('damage_cost').value = '';
    toggleDamageConditionSection();

    document.getElementById('modalFormTitle').innerHTML = '<i class="mdi mdi-plus-circle me-1"></i> Add Gadget Issuance';
    clearValidationErrors();
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        $('#user_id').val('').trigger('change.select2');
    }
    const modal = new bootstrap.Modal(document.getElementById('gadgetFormModal'));
    modal.show();
});

// Real-time validation clear
document.getElementById('user_id').addEventListener('change', function() {
    if (this.value) {
        this.classList.remove('is-invalid');
        if (typeof jQuery !== 'undefined') {
            $(this).next('.select2-container').find('.select2-selection').css('border-color', '#ced4da');
        }
        document.getElementById('user_id_error').style.display = 'none';
    }
});
document.getElementById('gadget_name').addEventListener('input', function() {
    if (this.value.trim()) {
        this.classList.remove('is-invalid');
        document.getElementById('gadget_name_error').style.display = 'none';
    }
});
document.getElementById('issuance_date').addEventListener('change', function() {
    if (this.value) {
        this.classList.remove('is-invalid');
        document.getElementById('issuance_date_error').style.display = 'none';
    }
});
document.getElementById('return_date').addEventListener('change', function() {
    if (this.value) {
        this.classList.remove('is-invalid');
        document.getElementById('return_date_error').style.display = 'none';
    }
});

function clearValidationErrors() {
    ['user_id', 'gadget_name', 'issuance_date', 'return_date'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
        const errEl = document.getElementById(id + '_error');
        if (errEl) errEl.style.display = 'none';
    });
    if (typeof jQuery !== 'undefined') {
        $('#user_id').next('.select2-container').find('.select2-selection').css('border-color', '#ced4da');
    }
}

// Form submit handler (Save / Update)
document.getElementById('gadgetForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const userId = document.getElementById('user_id').value;
    const gadgetName = document.getElementById('gadget_name').value.trim();
    const issuanceDate = document.getElementById('issuance_date').value;
    const statusVal = document.getElementById('status').value;
    const returnDate = document.getElementById('return_date').value;

    let isValid = true;
    if (!userId) {
        document.getElementById('user_id').classList.add('is-invalid');
        if (typeof jQuery !== 'undefined') {
            $('#user_id').next('.select2-container').find('.select2-selection').css('border-color', '#dc3545');
        }
        document.getElementById('user_id_error').style.display = 'block';
        isValid = false;
    }
    if (!gadgetName) {
        document.getElementById('gadget_name').classList.add('is-invalid');
        document.getElementById('gadget_name_error').style.display = 'block';
        isValid = false;
    }
    if (!issuanceDate) {
        document.getElementById('issuance_date').classList.add('is-invalid');
        document.getElementById('issuance_date_error').style.display = 'block';
        isValid = false;
    }
    if (statusVal === 'Returned' && !returnDate) {
        document.getElementById('return_date').classList.add('is-invalid');
        document.getElementById('return_date_error').style.display = 'block';
        isValid = false;
    }

    if (!isValid) return;

    const formData = new FormData(this);

    fetch('/api/gadget-issuance/save', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('gadgetFormModal')).hide();
            loadGadgetIssuances();
            if (window.Swal) {
                Swal.fire('Success', res.message, 'success');
            } else {
                alert(res.message);
            }
        } else {
            alert(res.message || 'Error saving gadget issuance.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Server error occurred. Please try again.');
    });
});

// Edit function
function editGadget(id) {
    fetch(`/api/gadget-issuance/get/${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                document.getElementById('gadget_id').value = data.id;
                document.getElementById('gadget_name').value = data.gadget_name || '';
                document.getElementById('gadget_type').value = data.gadget_type || 'Laptop';
                document.getElementById('serial_number').value = data.serial_number || '';
                document.getElementById('model_number').value = data.model_number || '';
                document.getElementById('issuance_date').value = data.issuance_date || '';
                document.getElementById('return_date').value = data.return_date || '';
                document.getElementById('status').value = data.status || 'Issued';
                document.getElementById('gadget_condition').value = data.gadget_condition || 'Good / Normal';
                document.getElementById('damage_details').value = data.damage_details || '';
                document.getElementById('damage_cost').value = data.damage_cost ? parseFloat(data.damage_cost).toFixed(2) : '';
                document.getElementById('remarks').value = data.remarks || '';

                toggleDamageConditionSection();

                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    $('#user_id').val(data.user_id).trigger('change.select2');
                } else {
                    document.getElementById('user_id').value = data.user_id;
                }

                document.getElementById('modalFormTitle').innerHTML = '<i class="mdi mdi-pencil me-1"></i> Edit Gadget Issuance';
                clearValidationErrors();
                const modal = new bootstrap.Modal(document.getElementById('gadgetFormModal'));
                modal.show();
            } else {
                alert('Record not found.');
            }
        })
        .catch(err => console.error(err));
}

// View function
function viewGadget(id) {
    fetch(`/api/gadget-issuance/get/${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                document.getElementById('view_emp_name').textContent = d.employee_name + (d.emp_code ? ` (${d.emp_code})` : '');
                document.getElementById('view_gadget_name').textContent = d.gadget_name || '-';
                document.getElementById('view_gadget_type').textContent = d.gadget_type || '-';
                document.getElementById('view_serial_number').textContent = d.serial_number || '-';
                document.getElementById('view_model_number').textContent = d.model_number || '-';
                document.getElementById('view_issuance_date').textContent = formatDisplayDate(d.issuance_date);
                document.getElementById('view_return_date').textContent = d.return_date ? formatDisplayDate(d.return_date) : '-';
                
                let badgeClass = 'badge-issued';
                let st = (d.status || '').toLowerCase();
                if (st === 'returned') badgeClass = 'badge-returned';
                else if (st === 'damaged') badgeClass = 'badge-damaged';
                else if (st === 'lost') badgeClass = 'badge-lost';

                document.getElementById('view_status').innerHTML = `<span class="${badgeClass}">${escapeHtml(d.status || 'Issued')}</span>`;
                document.getElementById('view_gadget_condition').textContent = d.gadget_condition || 'Good / Normal';
                document.getElementById('view_damage_cost').textContent = d.damage_cost ? `$${parseFloat(d.damage_cost).toFixed(2)}` : '$0.00';
                document.getElementById('view_damage_details').textContent = d.damage_details || '-';
                document.getElementById('view_remarks').textContent = d.remarks || '-';

                const modal = new bootstrap.Modal(document.getElementById('gadgetViewModal'));
                modal.show();
            }
        });
}

// Delete function
function deleteGadget(id) {
    const doDelete = () => {
        fetch(`/api/gadget-issuance/delete/${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    loadGadgetIssuances();
                    if (window.Swal) {
                        Swal.fire('Deleted', res.message, 'success');
                    } else {
                        alert(res.message);
                    }
                } else {
                    alert(res.message || 'Failed to delete record.');
                }
            })
            .catch(err => console.error(err));
    };

    if (window.Swal) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete this gadget issuance record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then(result => {
            if (result.isConfirmed) {
                doDelete();
            }
        });
    } else if (confirm('Are you sure you want to delete this gadget issuance record?')) {
        doDelete();
    }
}

// Client-side search filter
document.getElementById('gadgetSearchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase().trim();
    if (!term) {
        renderGadgetTable(allGadgetData);
        return;
    }
    const filtered = allGadgetData.filter(item => {
        const emp = (item.employee_name || '').toLowerCase();
        const code = (item.emp_code || '').toLowerCase();
        const gName = (item.gadget_name || '').toLowerCase();
        const gType = (item.gadget_type || '').toLowerCase();
        const sn = (item.serial_number || '').toLowerCase();
        return emp.includes(term) || code.includes(term) || gName.includes(term) || gType.includes(term) || sn.includes(term);
    });
    renderGadgetTable(filtered);
});

// Add custom gadget type handlers
document.getElementById('btnAddGadgetType').addEventListener('click', function() {
    document.getElementById('new_gadget_type_input').value = '';
    document.getElementById('new_gadget_type_input').classList.remove('is-invalid');
    document.getElementById('new_gadget_type_error').style.display = 'none';
    const modal = new bootstrap.Modal(document.getElementById('addGadgetTypeModal'));
    modal.show();
    setTimeout(() => document.getElementById('new_gadget_type_input').focus(), 350);
});

document.getElementById('btnSaveNewGadgetType').addEventListener('click', saveCustomGadgetType);
document.getElementById('new_gadget_type_input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        saveCustomGadgetType();
    }
});

function saveCustomGadgetType() {
    const input = document.getElementById('new_gadget_type_input');
    const val = input.value.trim();
    if (!val) {
        input.classList.add('is-invalid');
        document.getElementById('new_gadget_type_error').style.display = 'block';
        return;
    }

    const formData = new FormData();
    formData.append('name', val);

    fetch('/api/gadget-issuance/add-type', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            const addedName = res.name || val;
            loadGadgetTypes(addedName);

            const modalEl = document.getElementById('addGadgetTypeModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        } else {
            alert(res.message || 'Failed to save gadget type.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error saving gadget type to database.');
    });
}

// Initial load
$(document).ready(function() {
    initEmployeeSelect2();
    loadGadgetTypes();
    loadGadgetIssuances();
});

$('#gadgetFormModal').on('shown.bs.modal', function() {
    initEmployeeSelect2();
});
</script>

<?= $this->endSection() ?>
