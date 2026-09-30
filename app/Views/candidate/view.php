<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    @media (max-width: 767px) {
        .attendenceall {
            font-size: 9px !important;
            padding: 5.2px !important;
        }

        .iconfontsize {
            font-size: 11px !important;
        }

        .cart-sm-title {
            font-size: 12px !important;
            margin-bottom: 5px !important;
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
            /* margin-left: -5rem !important;  */
        }

        .col-sm-12.col-md-6 {
            flex: 0 0 25%;
            max-width: 26%;
        }

        .dataTables_filter label:before {
            content: "" !important;
        }

        #candidates-Table_length label {
            display: flex;
            align-items: center;
        }

        /* Hide the text inside the label */
        #candidates-Table_length label::first-text,
        #candidates-Table_length label::before {
            display: none !important;
        }

        /* Or a simpler and reliable trick */
        #candidates-Table_length label {
            font-size: 0;
            /* hide text */
        }

        #candidates-Table_length label input {
            font-size: 10px;
            /* reset font size for input */
        }

   #candidates-Tabl_filter label {
    font-size: 0;
  }
  #candidates-Tabl_filter input {
    font-size: 14px; /* Keep input font size normal */
  }
        #candidates-Table_length label {
            font-size: 0;
            /* hide all text inside the label */
        }

        #candidates-Table_length label select {
            font-size: 14px;
            /* restore font size for the dropdown */
        }

        /* .form-control {
            height: 0px !important;
        } */
         div.dataTables_wrapper div.dataTables_filter input {
    margin-left: 0.5em;
    display: inline-block;
      width: 212px !important;
    height:29px !important

}
 .custom-select{
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
        <div class="card">
            <div class="card-body">


                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                    <h4 class="card-title mb-0">Manage Candidates</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="btnExportCandidates" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-file-excel iconfontsize"></i> Export
                        </button>
                        <a href="<?= base_url(
                            "/candidate",
                        ) ?>" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-plus iconfontsize"></i> Add Candidate
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped w-100" id="candidates-Table">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th class="desktop-only-col">Email</th>
                                <th class="desktop-only-col">Job Title</th>
                                <th class="desktop-only-col">Status</th>
                                <th class="desktop-only-col action-column" style="width: 100px;">Action</th>
                                <th class="mobile-expand-col" style="width: 50px;">Details</th>
                            </tr>
                        </thead>
                        <tbody id="candidates-Table-Body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Convert Candidate to Employee Modal -->
<div class="modal fade" id="convertEmployeeModal" tabindex="-1" aria-labelledby="convertEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#E66136; color:#fff;">
        <h5 class="modal-title fw-bold" id="convertEmployeeModalLabel">
          <i class="mdi mdi-account-arrow-right me-2"></i>Convert Candidate to Employee
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="convertEmployeeForm">
        <input type="hidden" id="convert_candidate_id" name="candidate_id">
        <div class="modal-body p-4">
          <!-- Candidate Brief Summary Card -->
          <div class="card bg-light border-0 mb-3">
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-md-6">
                  <small class="text-muted d-block">Candidate Name</small>
                  <strong id="convert_summary_name" class="fs-6 text-dark">-</strong>
                </div>
                <div class="col-md-6">
                  <small class="text-muted d-block">Email</small>
                  <strong id="convert_summary_email" class="text-dark">-</strong>
                </div>
                <div class="col-md-6">
                  <small class="text-muted d-block">Phone Number</small>
                  <span id="convert_summary_phone" class="text-dark">-</span>
                </div>
                <div class="col-md-6">
                  <small class="text-muted d-block">Applied Job Position</small>
                  <span id="convert_summary_job" class="badge bg-secondary">-</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Conversion Form Fields -->
          <h6 class="fw-bold mb-3" style="color: #E66136;">
            <i class="mdi mdi-briefcase-check me-1"></i>Employee Assignment Details
          </h6>

          <div class="row g-3">
            <!-- Department Selection -->
            <div class="col-md-6">
              <label for="convert_department_id" class="form-label fw-bold">
                Department <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="convert_department_id" name="department_id" required onchange="onDepartmentChanged()">
                <option value="">-- Select Department --</option>
              </select>
              <small class="text-muted">Choose the department where the employee will work.</small>
            </div>

            <!-- Designation Selection -->
            <div class="col-md-6">
              <label for="convert_designation_id" class="form-label fw-bold">
                Designation
              </label>
              <select class="form-select" id="convert_designation_id" name="designation_id">
                <option value="">-- Select Designation --</option>
              </select>
              <small class="text-muted">Filtered by department, or choose from designations.</small>
            </div>

            <!-- Employee ID -->
            <div class="col-md-4">
              <label for="convert_employee_id" class="form-label fw-bold">
                Employee ID <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="convert_employee_id" name="employee_id" required placeholder="EMP-001">
              <small class="text-muted">Auto-suggested unique ID.</small>
            </div>

            <!-- Joining Date -->
            <div class="col-md-4">
              <label for="convert_joining_date" class="form-label fw-bold">
                Joining Date <span class="text-danger">*</span>
              </label>
              <input type="date" class="form-control" id="convert_joining_date" name="joining_date" required>
            </div>

            <!-- Role -->
            <div class="col-md-4">
              <label for="convert_role" class="form-label fw-bold">Role</label>
              <select class="form-select" id="convert_role" name="role">
                <option value="employee" selected>Employee</option>
                <option value="department_manager">Department Manager</option>
              </select>
            </div>

            <!-- Starting Salary -->
            <div class="col-md-6">
              <label for="convert_salary" class="form-label fw-bold">Starting Salary (Monthly)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-currency-inr"></i></span>
                <input type="number" step="0.01" class="form-control" id="convert_salary" name="salary" placeholder="0.00">
              </div>
            </div>

            <!-- Working Location -->
            <div class="col-md-6">
              <label for="convert_working_location" class="form-label fw-bold">Working Location</label>
              <select class="form-select" id="convert_working_location" name="working_location">
                <option value="On-Site" selected>On-Site</option>
                <option value="Remote">Remote</option>
                <option value="Hybrid">Hybrid</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn text-white fw-bold" id="btnSubmitConvert" style="background:#E66136;">
            <i class="mdi mdi-check-circle me-1"></i>Confirm & Convert to Employee
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    let convertDataCache = null;

    function loadCandidates() {
        const token = localStorage.getItem('token'); // JWT token from login

        // Fetch candidates when the page loads
        fetch('/api/candidate', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            })
            .then((response) => response.json())
            .then((responseData) => {
                if (responseData.status === 'success') {
                    const candidates = responseData.data;
                    let tableRows = '';

                    candidates.forEach((candidate, index) => {
                        const isConverted = (candidate.user_role === 'employee' || (candidate.status || '').toLowerCase() === 'hired');
                        const empBadge = isConverted 
                            ? `<span class="badge bg-success text-white" style="font-size: 11px;" title="Active Employee (${candidate.current_emp_id || ''})"><i class="mdi mdi-account-check me-1"></i>Employee</span>`
                            : `<span class="badge badge-outline-secondary" style="font-size: 11px;">${candidate.status || 'N/A'}</span>`;

                        const convertActionDesktop = isConverted
                            ? `<span class="text-success fs-5" title="Already converted to Employee (${candidate.current_emp_id || ''})"><i class="mdi mdi-check-circle"></i></span>`
                            : `<a href="javascript:void(0);" class="text-success fs-5" title="Convert to Employee" onclick="openConvertModal(${candidate.id})"><i class="mdi mdi-account-arrow-right"></i></a>`;

                        const convertActionMobile = isConverted
                            ? `<span class="badge bg-success p-2 text-white"><i class="mdi mdi-account-check me-1"></i> Converted (${candidate.current_emp_id || ''})</span>`
                            : `<button type="button" class="btn btn-sm btn-success text-white" onclick="openConvertModal(${candidate.id})"><i class="mdi mdi-account-arrow-right"></i> Convert to Employee</button>`;

                        tableRows += `
                        <tr data-id="${candidate.id}">
                            <td class="capitalize-text">
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <div style="flex: 1;">
                                        <a href="/candidate/display/${candidate.id}" class="text-decoration-none text-dark fw-bold">
                                            ${candidate.candidate_name}
                                        </a>
                                        <div class="expanded-details" id="candidate-details-${candidate.id}">
                                            <div class="detail-row">
                                                <span class="detail-label">Email:</span>
                                                <span class="detail-value">${candidate.email || 'N/A'}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Job:</span>
                                                <span class="detail-value">${candidate.job_title || 'N/A'}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Department:</span>
                                                <span class="detail-value">${candidate.department_name || 'N/A'}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Status:</span>
                                                <span class="detail-value">${candidate.status || 'N/A'}</span>
                                            </div>
                                            <div class="detail-actions">
                                                ${convertActionMobile}
                                                <a href="/candidate/display/${candidate.id}" class="btn btn-sm btn-info text-white"><i class="mdi mdi-eye"></i> View</a>
                                                <a href="/candidate/${candidate.id}" class="btn btn-sm btn-warning"><i class="mdi mdi-pencil"></i> Edit</a>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCandidateById(${candidate.id}, '${candidate.status || ''}')"><i class="mdi mdi-delete"></i> Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="desktop-only-col">${candidate.email || 'N/A'}</td>
                            <td class="desktop-only-col capitalize-text">${candidate.job_title || 'N/A'}</td>
                            <td class="desktop-only-col capitalize-text">
                                ${empBadge}
                            </td>
                            <td class="desktop-only-col">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    ${convertActionDesktop}
                                    <a href="/candidate/display/${candidate.id}" class="text-primary fs-5" title="View"><i class="mdi mdi-eye"></i></a>
                                    <a href="/candidate/${candidate.id}" class="text-warning fs-5" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                    <a href="javascript:void(0);" class="text-danger fs-5" title="Delete"
                                       onclick="deleteCandidateById(${candidate.id}, '${candidate.status || ''}')">
                                       <i class="mdi mdi-delete"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="mobile-expand-col text-center">
                                <button type="button" class="expand-toggle" data-target="candidate-details-${candidate.id}" aria-label="Expand details"></button>
                            </td>
                        </tr>
                    `;
                    });

                    if ($.fn.DataTable.isDataTable('#candidates-Table')) {
                        $('#candidates-Table').DataTable().clear().destroy();
                    }
                    document.getElementById('candidates-Table-Body').innerHTML = tableRows;

                    const dt = $('#candidates-Table').DataTable({
                        columnDefs: [
                            {
                                targets: [4, 5],
                                orderable: false,
                                searchable: false
                            }
                        ],
                        language: {
                            search: "",
                            searchPlaceholder: "Search"
                        }
                    });

                    dt.on('draw', function() {
                        if (typeof applyMobileTableVisibility === 'function') {
                            applyMobileTableVisibility();
                        }
                    });

                    if (typeof applyMobileTableVisibility === 'function') {
                        applyMobileTableVisibility();
                    }
                } else {
                    console.error('Failed to fetch candidates:', responseData.message);
                }
            })
            .catch((error) => {
                console.error('Error fetching candidates:', error);
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        loadCandidates();
    });

    window.openConvertModal = function(candidateId) {
        if (!candidateId) return;
        const token = localStorage.getItem('token');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Loading Candidate Info...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch(`/api/candidate/convert-data/${candidateId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(res => {
            if (typeof Swal !== 'undefined') { Swal.close(); }

            if (res.status !== 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', res.message || 'Failed to fetch candidate details', 'error');
                } else {
                    alert(res.message || 'Failed to fetch candidate details');
                }
                return;
            }

            const data = res.data;
            convertDataCache = data;
            const candidate = data.candidate;

            document.getElementById('convert_candidate_id').value = candidate.id;
            document.getElementById('convert_summary_name').textContent = candidate.candidate_name || '—';
            document.getElementById('convert_summary_email').textContent = candidate.email || '—';
            document.getElementById('convert_summary_phone').textContent = candidate.phone_number || '—';
            document.getElementById('convert_summary_job').textContent = candidate.job_title || 'General';

            document.getElementById('convert_employee_id').value = data.suggested_emp_id || '';
            document.getElementById('convert_joining_date').value = data.default_date || new Date().toISOString().slice(0, 10);
            document.getElementById('convert_salary').value = '';
            document.getElementById('convert_role').value = 'employee';
            document.getElementById('convert_working_location').value = 'On-Site';

            // Populate Departments
            const deptSelect = document.getElementById('convert_department_id');
            deptSelect.innerHTML = '<option value="">-- Select Department --</option>';

            const matchedDeptId = candidate.job_department_id || '';

            (data.departments || []).forEach(dept => {
                const opt = document.createElement('option');
                opt.value = dept.id;
                opt.textContent = dept.department_name + (dept.branch_name ? ` (${dept.branch_name})` : '');
                if (matchedDeptId && String(dept.id) === String(matchedDeptId)) {
                    opt.selected = true;
                }
                deptSelect.appendChild(opt);
            });

            // Update designations based on selected department
            onDepartmentChanged();

            const modal = new bootstrap.Modal(document.getElementById('convertEmployeeModal'));
            modal.show();
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') { Swal.close(); }
            console.error(err);
            alert('Unable to load candidate details');
        });
    };

    window.onDepartmentChanged = function() {
        if (!convertDataCache) return;
        const selectedDeptId = document.getElementById('convert_department_id').value;
        const desigSelect = document.getElementById('convert_designation_id');
        desigSelect.innerHTML = '<option value="">-- Select Designation --</option>';

        const allDesignations = convertDataCache.designations || [];

        let matchingDesigs = selectedDeptId 
            ? allDesignations.filter(d => String(d.department_id) === String(selectedDeptId))
            : allDesignations;

        if (matchingDesigs.length === 0 && allDesignations.length > 0) {
            matchingDesigs = allDesignations;
        }

        matchingDesigs.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.designation_name + (d.department_name ? ` (${d.department_name})` : '');
            desigSelect.appendChild(opt);
        });
    };

    document.getElementById('convertEmployeeForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const token = localStorage.getItem('token');
        const submitBtn = document.getElementById('btnSubmitConvert');

        const candidateId = document.getElementById('convert_candidate_id').value;
        const deptId = document.getElementById('convert_department_id').value;
        const desigId = document.getElementById('convert_designation_id').value;
        const empId = document.getElementById('convert_employee_id').value;
        const joiningDate = document.getElementById('convert_joining_date').value;
        const salary = document.getElementById('convert_salary').value;
        const role = document.getElementById('convert_role').value;
        const location = document.getElementById('convert_working_location').value;

        if (!deptId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Department Required', 'Please select a department for the employee.', 'warning');
            } else {
                alert('Please select a department for the employee.');
            }
            return;
        }
        if (!empId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Employee ID Required', 'Please provide an Employee ID.', 'warning');
            } else {
                alert('Please provide an Employee ID.');
            }
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Converting...';

        const formData = new FormData();
        formData.append('candidate_id', candidateId);
        formData.append('department_id', deptId);
        formData.append('designation_id', desigId);
        formData.append('employee_id', empId);
        formData.append('joining_date', joiningDate);
        formData.append('salary', salary);
        formData.append('role', role);
        formData.append('working_location', location);

        fetch('/api/candidate/convert-to-employee', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-check-circle me-1"></i>Confirm & Convert to Employee';

            if (res.status === 'success') {
                const modalEl = document.getElementById('convertEmployeeModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Converted to Employee!',
                        text: res.message,
                        confirmButtonColor: '#E66136',
                        confirmButtonText: 'View in Employees',
                        showCancelButton: true,
                        cancelButtonText: 'Stay on Candidates'
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = '/empview';
                        } else {
                            loadCandidates();
                        }
                    });
                } else {
                    alert(res.message);
                    loadCandidates();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Conversion Error', res.message || 'Failed to convert candidate.', 'error');
                } else {
                    alert(res.message || 'Failed to convert candidate.');
                }
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="mdi mdi-check-circle me-1"></i>Confirm & Convert to Employee';
            console.error(err);
            alert('An unexpected error occurred during conversion.');
        });
    });

    // Delete candidate — warns if candidate has a completed interview or is hired
    window.deleteCandidateById = function(candidateId, status) {
        if (!candidateId) return;
        status = (status || '').toLowerCase();

        // High-risk statuses that warrant an extra warning
        const isHighRisk = ['hired', 'completed', 'scheduled'].includes(status);

        function performDelete() {
            $.ajax({
                url: `/api/candidate/${candidateId}`,
                type: 'POST',
                data: { _method: 'DELETE' },
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
                success: function(responseData) {
                    if (responseData.status === 'success') {
                        $(`tr[data-id="${candidateId}"]`).remove();
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The candidate has been deleted successfully.',
                            icon: 'success',
                            buttonsStyling: false,
                            customClass: { confirmButton: 'hr-btnbg' },
                            confirmButtonText: 'OK',
                        });
                    } else {
                        const msg = responseData.messages?.error
                            || responseData.message
                            || 'Failed to delete the candidate. They may have associated interviews.';
                        Swal.fire({
                            title: 'Cannot Delete',
                            text: msg,
                            icon: 'error',
                            buttonsStyling: false,
                            customClass: { confirmButton: 'hr-btnbg' },
                            confirmButtonText: 'OK',
                        });
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'An error occurred while deleting the candidate.', 'error');
                }
            });
        }

        if (isHighRisk) {
            // ⚠️ Strong warning for candidates with interviews / already hired
            Swal.fire({
                title: '⚠️ Warning: Active Candidate',
                html: `<p>This candidate's status is <strong>${status}</strong>.</p>
                       <p>They may have <strong>interview or onboarding records</strong> linked to their profile.</p>
                       <p>Deleting this candidate will <strong>not</strong> delete associated user accounts, but their recruitment history will be lost.</p>
                       <p><strong>Are you absolutely sure?</strong></p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete Candidate',
                cancelButtonText: 'No, Keep It',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn hr-btnbg'
                }
            }).then(result => {
                if (result.isConfirmed) performDelete();
            });
        } else {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This candidate and their application data will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'hr-btnbg',
                    cancelButton: 'hr-btnbg',
                }
            }).then(result => {
                if (result.isConfirmed) performDelete();
            });
        }
    };

    window.deleteCandidate = function(event) {
        if (event) event.preventDefault();
        const anchor = event.target.closest('a') || event.target.closest('button');
        if (anchor) {
            const candidateId = anchor.getAttribute('data-id');
            const status = anchor.getAttribute('data-status');
            deleteCandidateById(candidateId, status);
        }
    };

    // 📥 Export to Excel functionality
    document.getElementById('btnExportCandidates')?.addEventListener('click', function () {
        const btn = this;
        const search = $('#candidates-Table_filter input').val() || '';
        const token = localStorage.getItem('token');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...';

        const queryParams = new URLSearchParams({ search: search });

        fetch(`<?= base_url('api/candidate/export') ?>?${queryParams.toString()}`, {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(async response => {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-file-excel iconfontsize"></i> Export';
            if (!response.ok) {
                const err = await response.json().catch(() => ({ message: 'Export failed' }));
                throw new Error(err.message || 'Export failed');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const dateStr = new Date().toISOString().slice(0, 10);
            a.download = `Candidates_${dateStr}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                text: 'Candidate list exported to Excel successfully.',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-file-excel iconfontsize"></i> Export';
            Swal.fire('Export Error', error.message || 'Failed to export candidates', 'error');
        });
    });
</script>

<?= $this->endSection() ?>
