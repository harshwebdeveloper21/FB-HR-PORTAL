<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
@media (max-width: 767px) {
    .addsmbtnres {
        font-size: 10px !important;
        padding: 8px !important;
    }
}
.form-group label {
    font-weight: 500;
}
.required-star {
    color: #dc3545;
}
</style>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0" id="candidateFormTitle">Add Candidate</h4>
                    <a href="/candidateview" class="btn hr-btnbg addsmbtnres">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Candidates
                    </a>
                </div>
                <hr class="mb-4">

                <form class="form-sample" method="POST" action="" id="candidateForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="candidate_id">

                    <!-- Row 1: Candidate Name & Email -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Candidate Name <span class="required-star">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-account-circle fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="candidate_name" id="candidate_name" placeholder="Enter full name" required />
                                    </div>
                                    <div class="invalid-feedback d-block text-danger small" id="err_candidate_name"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Email <span class="required-star">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-email fs-5"></i></span>
                                        </div>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter email address" required />
                                    </div>
                                    <div class="invalid-feedback d-block text-danger small" id="err_email"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Job & Phone Number -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Job Position <span class="required-star">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-office-building fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="job_id" id="job_id" required>
                                            <option value="" disabled selected>Select Job Position</option>
                                            <?php if (!empty($jobs)): foreach ($jobs as $job): ?>
                                                <option value="<?= $job["id"] ?>"><?= esc($job["job_title"]) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <button class="btn hr-btnbg px-3" type="button" data-bs-toggle="modal" data-bs-target="#quickJobModal" title="Quick Add Job">
                                            <i class="mdi mdi-plus text-white"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback d-block text-danger small" id="err_job_id"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Phone Number <span class="required-star">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-phone fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="phone_number" id="phone_number" placeholder="Enter phone number" required />
                                    </div>
                                    <div class="invalid-feedback d-block text-danger small" id="err_phone_number"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Date of Birth & Gender -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Date of Birth</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                                        </div>
                                        <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Gender</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-gender-male-female fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="gender" id="gender">
                                            <option value="" selected>Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Country & State (Master Modules) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">Country</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="country_id" id="country_id">
                                            <option value="">Select Country</option>
                                            <?php if (!empty($countries)): foreach ($countries as $country): ?>
                                                <option value="<?= $country['id'] ?>"><?= esc($country['country_name']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">State</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-map-marker fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="state_id" id="state_id">
                                            <option value="">Select State</option>
                                            <?php if (!empty($states)): foreach ($states as $st): ?>
                                                <option value="<?= $st['id'] ?>"><?= esc($st['state_name']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5: City (Master Module Dropdown) & Address -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3 col-form-label">City</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-city fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="city_id" id="city_id">
                                            <option value="">Select City</option>
                                            <?php if (!empty($cities)): foreach ($cities as $ct): ?>
                                                <option value="<?= $ct['id'] ?>" data-country="<?= $ct['country_id'] ?? '' ?>"><?= esc($ct['city_name']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <small class="text-muted">Master city module</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Address</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="current_address" id="current_address" placeholder="Enter candidate address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 6: Resume & Notes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">
                                    Resume <span class="required-star" id="resumeRequiredStar">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted d-block mt-1">Allowed formats: PDF, DOC, DOCX (Max 2MB)</small>
                                    <div id="resume-error" class="text-danger small mt-1 fw-bold" style="display:none;"></div>
                                    <div id="currentResume" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Notes</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="notes" id="notes" placeholder="Enter notes or remarks" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-end mt-4">
                        <a href="/candidateview" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn hr-btnbg addsmbtnres px-4" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                            <span id="submitBtnText">Submit</span>
                        </button>
                    </div>
                    <div id="responseMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Job Modal -->
<div class="modal fade" id="quickJobModal" tabindex="-1" aria-labelledby="quickJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#E66136; color:#fff;">
                <h5 class="modal-title fw-bold" id="quickJobModalLabel"><i class="mdi mdi-plus-circle me-1"></i>Quick Add Job</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickJobForm">
                <div class="modal-body p-3">
                    <div class="row">
                        <!-- Job Title -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Job Title <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="job_title" placeholder="e.g. Backend Developer" required>
                        </div>
                        <!-- Department -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Department <span class="required-star">*</span></label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php if(isset($departments)): foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= esc($dept['department_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <!-- Location -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Location <span class="required-star">*</span></label>
                            <select class="form-select" name="locations_id" required>
                                <option value="">Select Location</option>
                                <?php if(isset($locations)): foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['location_id'] ?? $loc['id'] ?>"><?= esc($loc['job_location'] ?? $loc['location_name'] ?? 'Location') ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <!-- Job Type -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Job Type <span class="required-star">*</span></label>
                            <select class="form-select" name="job_type" required>
                                <option value="full">Full Time</option>
                                <option value="part">Part Time</option>
                            </select>
                        </div>
                        <!-- Experience -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Experience (Years) <span class="required-star">*</span></label>
                            <input type="number" class="form-control" name="experience" min="0" value="1" required>
                        </div>
                        <!-- Age Required -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Age Range <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="age" placeholder="e.g. 18-65" value="18-65" required>
                        </div>
                        <!-- Salary Range -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Salary Range <span class="required-star">*</span></label>
                            <input type="text" class="form-control" name="salary_range" placeholder="e.g. 10000-50000" value="10000-50000" required>
                        </div>
                        <!-- Close Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Close Date <span class="required-star">*</span></label>
                            <input type="date" class="form-control" name="close_date" required>
                        </div>
                        <!-- Gender -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Gender <span class="required-star">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="both">Both</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <input type="hidden" name="status" value="open">
                        <input type="hidden" name="addresses_id" value="1">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn hr-btnbg" id="quickJobSubmitBtn">Save Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const token = localStorage.getItem('token');
        let isEditMode = false;
        let candidateId = null;

        // Cache all cities for dependent filtering
        const allCities = <?= json_encode($cities ?? []) ?>;

        // Country change -> Filter Cities
        $('#country_id').on('change', function() {
            const selectedCountryId = $(this).val();
            const citySelect = $('#city_id');
            const currentSelectedCity = citySelect.val();
            citySelect.html('<option value="">Select City</option>');

            let filtered = allCities;
            if (selectedCountryId) {
                const matched = allCities.filter(c => String(c.country_id) === String(selectedCountryId));
                if (matched.length > 0) {
                    filtered = matched;
                }
            }

            filtered.forEach(c => {
                citySelect.append(`<option value="${c.id}" data-country="${c.country_id || ''}">${c.city_name}</option>`);
            });

            if (currentSelectedCity && filtered.some(c => String(c.id) === String(currentSelectedCity))) {
                citySelect.val(currentSelectedCity);
            }
        });

        // Resume file input change validation
        $('#resume').on('change', function() {
            $('#resume-error').hide().text('');
            $(this).removeClass('is-invalid');
            const file = this.files[0];
            if (file) {
                const allowedExtensions = ['pdf', 'doc', 'docx'];
                const ext = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(ext)) {
                    $('#resume').addClass('is-invalid');
                    $('#resume-error').text('Invalid format. Only PDF, DOC, or DOCX files are allowed.').show();
                    $(this).val('');
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    $('#resume').addClass('is-invalid');
                    $('#resume-error').text('File size exceeds 2MB limit.').show();
                    $(this).val('');
                    return;
                }
            }
        });

        // Form Submit
        $('#candidateForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#resume-error').hide().text('');

            // Validate resume on Add mode
            if (!isEditMode) {
                const resumeInput = document.getElementById('resume');
                if (!resumeInput.files || resumeInput.files.length === 0) {
                    $('#resume').addClass('is-invalid');
                    $('#resume-error').text('Resume is required. Please upload a PDF, DOC, or DOCX file.').show();
                    resumeInput.focus();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Resume Required',
                            text: 'Please upload a resume file (PDF, DOC, DOCX) before submitting.',
                            confirmButtonColor: '#E66136'
                        });
                    } else {
                        alert('Resume is required. Please upload a resume file.');
                    }
                    return false;
                }
            }

            $('#submitBtn').attr('disabled', true);
            $('#submitSpinner').removeClass('d-none');

            let formData = new FormData(this);
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const csrfName = $('meta[name="csrf-name"]').attr('content') || $('meta[name="csrf-token"]').attr('data-name');
            if (csrfName && csrfToken) {
                formData.append(csrfName, csrfToken);
            }

            const baseUrl = "<?= base_url() ?>";
            const url = candidateId ? `${baseUrl}api/candidate/${candidateId}` : `${baseUrl}api/candidate`;

            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#submitBtn').attr('disabled', false);
                    $('#submitSpinner').addClass('d-none');

                    if (response.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Candidate saved successfully!',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "/candidateview";
                            });
                        } else {
                            alert(response.message || 'Candidate saved successfully!');
                            window.location.href = "/candidateview";
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message || 'Failed to save candidate.'
                            });
                        } else {
                            alert(response.message || 'Failed to save candidate.');
                        }
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').attr('disabled', false);
                    $('#submitSpinner').addClass('d-none');

                    const res = xhr.responseJSON;
                    const errors = res ? (res.errors || res.messages || {}) : {};
                    let errorMessage = res ? (res.message || 'Validation failed. Please check form fields.') : 'An unexpected error occurred.';

                    if (errors.resume) {
                        $('#resume').addClass('is-invalid');
                        $('#resume-error').text(errors.resume).show();
                        errorMessage = errors.resume;
                    }
                    if (errors.candidate_name) {
                        $('#candidate_name').addClass('is-invalid');
                        $('#err_candidate_name').text(errors.candidate_name);
                    }
                    if (errors.email) {
                        $('#email').addClass('is-invalid');
                        $('#err_email').text(errors.email);
                    }
                    if (errors.phone_number) {
                        $('#phone_number').addClass('is-invalid');
                        $('#err_phone_number').text(errors.phone_number);
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMessage,
                            confirmButtonColor: '#E66136'
                        });
                    } else {
                        alert(errorMessage);
                    }
                }
            });
        });

        // Quick Job Form Submit
        $('#quickJobForm').on('submit', function(e) {
            e.preventDefault();
            $('#quickJobSubmitBtn').attr('disabled', true).text('Saving...');
            let formData = new FormData(this);

            $.ajax({
                url: '/api/job',
                type: 'POST',
                headers: { 'Authorization': `Bearer ${token}` },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        let jobId = response.data ? response.data.id : (response.job_id || null);
                        let jobTitle = formData.get("job_title");
                        if (jobId) {
                            $('#job_id').append(`<option value="${jobId}" selected>${jobTitle}</option>`);
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Job Added',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        $('#quickJobModal').modal('hide');
                        $('#quickJobForm')[0].reset();
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Failed', text: response.message });
                        }
                    }
                },
                error: function(xhr) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Validation failed or missing fields.' });
                    }
                },
                complete: function() {
                    $('#quickJobSubmitBtn').attr('disabled', false).text('Save Job');
                }
            });
        });

        // Check if editing candidate (from URL query or path)
        const params = new URLSearchParams(window.location.search);
        let urlId = params.get('id');
        if (!urlId) {
            const pathParts = window.location.pathname.split('/').filter(Boolean);
            const lastPart = pathParts[pathParts.length - 1];
            if (!isNaN(lastPart) && !isNaN(parseFloat(lastPart))) {
                urlId = lastPart;
            }
        }

        if (urlId) {
            candidateId = urlId;
            fetchUserData(urlId);
        }

        function fetchUserData(id) {
            $.ajax({
                url: `/api/candidateedit/${id}`,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function(responseData) {
                    if (responseData.status === 'success') {
                        const candidate = responseData.data;
                        $('#candidate_name').val(candidate.candidate_name || '');
                        $('#email').val(candidate.email || '');
                        $('#job_id').val(candidate.job_id || '');
                        $('#phone_number').val(candidate.phone_number || '');
                        $('#date_of_birth').val(candidate.date_of_birth || '');
                        $('#gender').val(candidate.gender || '');
                        $('#current_address').val(candidate.current_address || '');
                        $('#notes').val(candidate.notes || '');
                        $('#candidate_id').val(candidate.id);

                        // Location fields
                        if (candidate.country_id) {
                            $('#country_id').val(candidate.country_id).trigger('change');
                        }
                        if (candidate.state_id) {
                            $('#state_id').val(candidate.state_id);
                        }
                        if (candidate.city_id) {
                            $('#city_id').val(candidate.city_id);
                        } else if (candidate.city) {
                            // Match by city name if city_id was empty
                            $("#city_id option").filter(function() {
                                return $(this).text().trim().toLowerCase() === String(candidate.city).trim().toLowerCase();
                            }).prop('selected', true);
                        }

                        // Resume field
                        if (candidate.resume) {
                            $('#currentResume').html(
                                `<a href="${candidate.resume}" target="_blank" class="fw-bold" style="color:#E66136;">
                                    <i class="mdi mdi-file-pdf-box me-1 fs-5 align-middle"></i>View Current Resume
                                </a>`
                            );
                            $('#resume').removeAttr('required');
                            $('#resumeRequiredStar').hide();
                        } else {
                            $('#currentResume').html('<p class="text-muted small">No resume currently uploaded</p>');
                            $('#resume').attr('required', 'required');
                            $('#resumeRequiredStar').show();
                        }

                        $('#submitBtnText').text('Update Candidate');
                        $('#candidateFormTitle').text('Edit Candidate');
                        isEditMode = true;
                    } else {
                        $('#responseMessage').html('<p class="text-danger">Candidate not found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching candidate:', error);
                    $('#responseMessage').html('<p class="text-danger">Error fetching candidate details.</p>');
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>
