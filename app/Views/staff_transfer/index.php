<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
<style>
    @media (max-width: 767px) {
        .attendenceall {
            font-size: 9px !important;
            padding: 5.2px !important;
        }

        .iconfontsize {
            font-size: 11px !important;
        }

        .dataTables_length {
            margin-left: .1rem !important;
            margin-bottom: .5rem !important;
            font-size: 12px !important;
            float: left !important;
        }

        .dataTables_filter {
            font-size: 12px !important;
            float: left !important;
        }

        .dataTables_filter label:before {
            content: "" !important;
        }

        #transfers-Table_length label {
            display: flex;
            align-items: center;
        }

        #transfers-Table_length label::first-text,
        #transfers-Table_length label::before {
            display: none !important;
        }

        #transfers-Table_length label {
            font-size: 0;
        }

        #transfers-Table_length label input {
            font-size: 10px;
        }

        #transfers-Table_filter label {
            font-size: 0;
        }

        #transfers-Table_filter input {
            font-size: 14px;
        }

        #transfers-Table_length label select {
            font-size: 14px;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
            width: 212px !important;
            height: 29px !important;
        }

        .custom-select {
            height: 26px !important;
            width: 57px !important;
        }
    }

    .capitalize-text {
        text-transform: capitalize;
    }
</style>
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                    <h4 class="card-title mb-0">Staff Transfers</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn hr-btnbg attendenceall text-nowrap" data-bs-toggle="modal" data-bs-target="#transferModal">
                            <i class="mdi mdi-plus iconfontsize"></i> Transfer Staff
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Filter by Branch</label>
                        <select id="filterBranch" class="form-select">
                            <option value="">All Branches</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" id="filterStartDate" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">End Date</label>
                        <input type="date" id="filterEndDate" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2 d-flex align-items-end">
                        <div class="w-100">
                            <label class="form-label d-block text-white" style="user-select: none;">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button id="btnFilter" class="btn hr-btnbg text-white flex-grow-1">Apply</button>
                                <button id="btnClearFilter" class="btn btn-outline-secondary flex-grow-1">Clear</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped w-100" id="transfers-Table">
                        <thead class="table-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>From Branch</th>
                                <th>To Branch</th>
                                <th>Effective Date</th>
                                <th>Reason</th>
                                <th>Transferred By</th>
                                <th>Transferred On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="transfers-Table-Body">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #E8613A; color: white;">
                <h5 class="modal-title" id="transferModalLabel">Transfer Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Move an employee member to a different branch.</p>
                <div id="alertBox"></div>
                
                <form id="transferForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee Member <span class="text-danger">*</span></label>
                        <select id="staffSelect" name="staffSelect" class="form-select" required>
                            <option value="">Select employee member</option>
                            <?php if (!empty($staffList)): ?>
                                <?php foreach ($staffList as $s): ?>
                                    <option value="<?= $s['id'] ?>"
                                        data-branch="<?= htmlspecialchars($s['branch_id'] ?? '') ?>"
                                        data-branchname="<?= htmlspecialchars($s['branch_name'] ?? 'Unassigned') ?>">
                                        <?= htmlspecialchars(trim($s['firstname'] . ' ' . $s['lastname'])) ?>
                                        - <?= htmlspecialchars($s['branch_name'] ?? 'Unassigned') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No employees found</option>
                            <?php endif; ?>
                        </select>
                        <div id="currentBranch" class="form-text text-muted mt-1"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Transfer To Branch <span class="text-danger">*</span></label>
                        <select id="toBranchSelect" class="form-select" required>
                            <option value="">Select target branch</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?> (<?= $b['code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Effective Date</label>
                        <input type="date" id="effectiveDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Reason (optional)</label>
                        <textarea id="reason" class="form-control" rows="2" placeholder="Reason for transfer..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn hr-btnbg" id="transferBtn">
                            <i class="mdi mdi-swap-horizontal me-1"></i>Transfer Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
const token = localStorage.getItem('token') || '';
const headers = {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
};

let table;

$(document).ready(function() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        $('#staffSelect').select2({
            placeholder: 'Search employee member...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#transferModal')
        });
        $('#toBranchSelect').select2({
            placeholder: 'Select target branch',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#transferModal')
        });
    }

    // Initialize DataTable
    table = $('#transfers-Table').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "/api/staff-transfer/history",
            "type": "GET",
            "headers": {
                "Authorization": "Bearer " + token
            },
            "data": function ( d ) {
                d.filter_branch_id = $('#filterBranch').val();
                d.start_date = $('#filterStartDate').val();
                d.end_date = $('#filterEndDate').val();
            },
            "dataSrc": function (json) {
                if(json.status === 'success') {
                    return json.data;
                }
                return [];
            }
        },
        "columns": [
            { 
                "data": null, 
                "render": function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    let name = escHtml(row.firstname || '-') + ' ' + escHtml(row.lastname || '');
                    let empId = escHtml(row.employee_id || '-');
                    return `<div><strong>${name}</strong></div><div class="small text-muted">${empId}</div>`;
                }
            },
            { "data": "from_branch_name", "render": function(data) { return escHtml(data || '-'); } },
            { "data": "to_branch_name", "render": function(data) { return escHtml(data || '-'); } },
            { 
                "data": "effective_date",
                "render": function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-GB').replace(/\//g, '-'); // DD-MM-YYYY
                }
            },
            { "data": "reason", "render": function(data) { return escHtml(data || '-'); } },
            { 
                "data": null,
                "render": function(data, type, row) {
                    return escHtml(row.transferred_by_firstname || '-') + ' ' + escHtml(row.transferred_by_lastname || '');
                }
            },
            { 
                "data": "created_at",
                "render": function(data) {
                    if(!data) return '-';
                    const d = new Date(data);
                    return d.toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute:'2-digit', hour12: false });
                }
            },
            { 
                "data": "status",
                "render": function(data, type, row) {
                    // Default to Completed since we removed the pending flow
                    if(data === 'completed' || !data) {
                        return `<span class="badge bg-success">Completed</span>`;
                    }
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            }
        ],
        "language": {
            "emptyTable": "No transfer records found"
        },
        "order": [[7, "desc"]],
        "lengthMenu": [[10, 25, 50], [10, 25, 50]],
        "pageLength": 10
    });

    $('#btnFilter').click(function() {
        table.ajax.reload();
    });

    $('#btnClearFilter').click(function() {
        $('#filterBranch').val('');
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        table.search('');
        table.ajax.reload();
    });
});

// Show current branch when staff is selected
$(document).on('change', '#staffSelect', function() {
    const opt = this.options[this.selectedIndex];
    if (opt && this.value) {
        const info = document.getElementById('currentBranch');
        info.textContent = 'Current Branch: ' + (opt.dataset.branchname || 'Unassigned');
        
        // Exclude current branch from target branches
        const currentBranchId = opt.dataset.branch;
        $('#toBranchSelect option').each(function() {
            if ($(this).val() === currentBranchId) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
        $('#toBranchSelect').select2({
            placeholder: 'Select target branch',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#transferModal')
        });
    } else {
        document.getElementById('currentBranch').textContent = '';
        $('#toBranchSelect option').prop('disabled', false);
    }
});

// Transfer form submit
document.getElementById('transferForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('transferBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Transferring...';

    const payload = {
        user_id:        document.getElementById('staffSelect').value,
        to_branch_id:   document.getElementById('toBranchSelect').value,
        effective_date: document.getElementById('effectiveDate').value,
        reason:         document.getElementById('reason').value.trim(),
    };

    try {
        const res  = await fetch('/api/staff-transfer/initiate', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.status === 'success') {
            // Close modal, show toast, refresh table
            const modal = bootstrap.Modal.getInstance(document.getElementById('transferModal'));
            modal.hide();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Employee transferred successfully',
                    text: data.message || 'Done.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Employee transferred successfully');
            }
            
            document.getElementById('transferForm').reset();
            document.getElementById('effectiveDate').value = new Date().toISOString().split('T')[0];
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $('#staffSelect').val(null).trigger('change');
                $('#toBranchSelect').val(null).trigger('change');
            }
            table.ajax.reload();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Transfer Failed',
                    text: data.message || 'An error occurred.',
                });
            } else {
                document.getElementById('alertBox').innerHTML =
                    `<div class="alert alert-danger alert-dismissible fade show">
                        ${data.message || 'An error occurred.'}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
            }
        }
    } catch {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Network error. Please try again.',
            });
        } else {
            document.getElementById('alertBox').innerHTML = '<div class="alert alert-danger">Network error.</div>';
        }
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-swap-horizontal me-1"></i>Transfer Staff';
});

// Export functionality
document.getElementById('btnExportTransfers').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...';
    
    const search = $('#transfers-Table_filter input').val() || '';
    const filterBranchId = $('#filterBranch').val();
    const startDate = $('#filterStartDate').val();
    const endDate = $('#filterEndDate').val();
    
    const queryParams = new URLSearchParams({ 
        search: search,
        filter_branch_id: filterBranchId,
        start_date: startDate,
        end_date: endDate
    });

    fetch(`/api/staff-transfer/export?${queryParams.toString()}`, {
        method: 'GET',
        headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(async response => {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-file-excel iconfontsize"></i> Export';
        if (!response.ok) {
            throw new Error('Export failed');
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        const dateStr = new Date().toISOString().slice(0, 10);
        a.download = `Staff_Transfers_${dateStr}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                text: 'Staff transfers exported to CSV successfully.',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-file-excel iconfontsize"></i> Export';
        if (typeof Swal !== 'undefined') {
            Swal.fire('Export Error', error.message || 'Failed to export transfers', 'error');
        } else {
            alert(error.message || 'Failed to export transfers');
        }
    });
});

// Reset modal alert on close
$('#transferModal').on('hidden.bs.modal', function () {
    document.getElementById('alertBox').innerHTML = '';
});

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
<?php $this->endSection(); ?>