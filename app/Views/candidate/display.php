<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    .capitalize-text {
        text-transform: capitalize;
    }
    @media (max-width: 767px) {
        .interviewsmbtn {
            font-size: 10px !important;
            padding: 8px !important;
            margin-top: 10px !important;
        }
        .font-size-candidate {
            font-size: 11px !important;
        }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="<?= base_url("assets/css/jobs.css") ?>">
<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h2 class="mb-0 fw-bold text-dark">
            <i class="fas fa-user-graduate" style="color: #E66136;"></i> Candidate Details
        </h2>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <button type="button" id="btnConvertToEmployee" class="btn btn-success interviewsmbtn text-white fw-bold" style="display: none;" onclick="openConvertModal()">
                <i class="mdi mdi-account-arrow-right me-1"></i> Convert to Employee
            </button>
            <span id="convertedEmployeeBadge" style="display: none;">
                <span class="badge bg-success p-2 text-white fs-6"><i class="mdi mdi-account-check me-1"></i> Converted Employee</span>
            </span>
            <a href="<?= base_url("/candidateview") ?>" class="btn hr-btnbg interviewsmbtn">
                <i class="mdi mdi-arrow-left me-1 iconfontsize"></i> Back
            </a>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body job-det-info job-widget">
                    <h4 class="mb-3 page-title">Personal Information</h4>
                    <hr>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-account-circle me-1" style="color: #E66136;"></i> Candidate Name:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value capitalize-text" id="candidate_id"></p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-email me-1" style="color: #E66136;"></i> Email:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value" id="email"></p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-phone me-1" style="color: #E66136;"></i> Phone:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value capitalize-text" id="phone"></p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-calendar me-1" style="color: #E66136;"></i> Date of Birth:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value" id="disp_dob">-</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-gender-male-female me-1" style="color: #E66136;"></i> Gender:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value capitalize-text" id="disp_gender">-</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-earth me-1" style="color: #E66136;"></i> Location:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value capitalize-text" id="disp_location">-</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-home-map-marker me-1" style="color: #E66136;"></i> Address:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value" id="disp_address">-</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-check-circle me-1" style="color: #E66136;"></i> Description / Notes:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p class="info-value capitalize-text" id="description"></p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <h4 class="col-sm-6 info-label font-size-candidate col-6">
                            <i class="mdi mdi-file-document me-1" style="color: #E66136;"></i> Resume:
                        </h4>
                        <div class="col-sm-6 col-6">
                            <p><span id="resume" class="text-muted capitalize-text"></span></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <div class="job-det-info job-widget">
                <h4 class="page-title">Job Information</h4>
                <hr>
                <div class="info-list">
                    <span><i class="mdi mdi-clipboard-text" style="color: #E66136;"></i></span>
                    <h5>Job Title:</h5>
                    <p id="job_type" class="capitalize-text"></p>
                </div>
                <div class="info-list">
                    <span><i class="mdi mdi-domain" style="color: #E66136;"></i></span>
                    <h5>Department:</h5>
                    <p id="dept_name" class="capitalize-text"></p>
                </div>
                <div class="info-list">
                    <span><i class="mdi mdi-calendar" style="color: #E66136;"></i></span>
                    <h5>Application Date:</h5>
                    <p id="post_date" class="capitalize-text"></p>
                </div>
                <div class="info-list">
                    <span><i class="mdi mdi-progress-check" style="color: #E66136;"></i></span>
                    <h5>Status:</h5>
                    <p id="statuss" class="capitalize-text"></p>
                </div>
                <div class="info-list" id="emp_id_box" style="display: none;">
                    <span><i class="mdi mdi-badge-account-horizontal" style="color: #28a745;"></i></span>
                    <h5>Employee ID:</h5>
                    <p id="emp_id_text" class="fw-bold text-success"></p>
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

<!-- AJAX Script -->
<script>
    let currentCandidateData = null;
    let convertDataCache = null;

    function getCandidateIdFromUrl() {
        const segments = window.location.pathname.split('/').filter(Boolean);
        return segments[segments.length - 1];
    }

    function loadCandidateDetails() {
        const token = localStorage.getItem('token');
        const candidateId = getCandidateIdFromUrl();

        if (!candidateId || isNaN(candidateId)) {
            $('#job_type').text("Error: Invalid candidate ID.");
            return;
        }

        $.ajax({
            url: `/api/candidate/${candidateId}`,
            type: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            success: function(response) {
                if (response.status === 'success') {
                    const candidate = response.data;
                    currentCandidateData = candidate;

                    // Populate personal details
                    $('#candidate_id').text(candidate.candidate_name || '-');
                    $('#email').text(candidate.email || '-');
                    $('#phone').text(candidate.phone_number || '-');
                    $('#disp_dob').text(candidate.date_of_birth || '-');
                    $('#disp_gender').text(candidate.gender || '-');
                    
                    const locParts = [candidate.city_name || candidate.city, candidate.state_name, candidate.country_name].filter(Boolean);
                    $('#disp_location').text(locParts.length > 0 ? locParts.join(', ') : '-');
                    $('#disp_address').text(candidate.current_address || '-');
                    $('#description').text(candidate.notes || 'N/A');

                    // Populate job details
                    $('#job_type').text(candidate.job_title || 'N/A');
                    $('#dept_name').text(candidate.department_name || 'N/A');
                    $('#post_date').text(candidate.post_date || 'N/A');
                    $('#statuss').text(candidate.candidate_status || candidate.status || 'N/A');

                    // Check if already employee
                    const isConverted = (candidate.user_role === 'employee' || (candidate.status || '').toLowerCase() === 'hired');
                    if (isConverted) {
                        $('#btnConvertToEmployee').hide();
                        $('#convertedEmployeeBadge').show();
                        if (candidate.current_emp_id) {
                            $('#emp_id_box').show();
                            $('#emp_id_text').text(candidate.current_emp_id);
                        }
                    } else {
                        $('#btnConvertToEmployee').show();
                        $('#convertedEmployeeBadge').hide();
                        $('#emp_id_box').hide();
                    }

                    if (candidate.resume) {
                        let resumeUrl = candidate.resume_url || ("<?= base_url("api/candidate/download-resume/") ?>" + candidate.id);
                        $('#resume').html(
                            `<a href="${resumeUrl}" class="bn-download" style="color: #E66136;" download><i class="mdi mdi-download me-1"></i>Download Resume</a>`
                        );
                    } else {
                        $('#resume').html('<p class="text-muted">No Resume Available</p>');
                    }
                }
            },
            error: function(xhr) {
                $('#job_type').text("Error fetching candidate details.");
                console.error("Error:", xhr.responseText);
            }
        });
    }

    window.openConvertModal = function() {
        const candidateId = getCandidateIdFromUrl();
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
                        cancelButtonText: 'Stay on Candidate'
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = '/empview';
                        } else {
                            loadCandidateDetails();
                        }
                    });
                } else {
                    alert(res.message);
                    loadCandidateDetails();
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

    $(document).ready(function() {
        loadCandidateDetails();
    });
</script>

<?= $this->endSection() ?>
