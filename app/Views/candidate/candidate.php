<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
@media (max-width: 767px) {
.addsmbtnres{
    font-size: 10px !important;
    padding: 8px !important;
}
}
</style>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Candidate</h4>
                <form class="form-sample" method="POST" action="" id="candidateForm" enctype="multipart/form-data">
                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Candidate Name</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-account-circle fs-5"></i></span>
                                        </div>
                                        <input type="hidden" name="id" id="candidate_id">
                                        <input type="text" class="form-control" name="candidate_name" id="candidate_name" placeholder="Enter your name" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-email fs-5"></i></span>
                                        </div>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Job -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Job</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-office-building fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="job_id" id="job_id">
                                            <option value="" disabled selected>Select job Title</option>
                                            <?php foreach ($jobs as $job): ?>
                                                <option value="<?= $job["id"] ?>"><?= $job["job_title"] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn hr-btnbg px-3" type="button" data-bs-toggle="modal" data-bs-target="#quickJobModal" title="Quick Add Job"><i class="mdi mdi-plus text-white"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Phone Number</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-phone fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="phone_number" id="phone_number" placeholder="Enter your phone number" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Date of Birth -->
                        <div class="col-md-6">
                            <div class="form-group row">
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

                        <!-- Gender -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Gender</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-gender-male-female fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="gender" id="gender">
                                            <option value="" disabled selected>Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- City -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">City</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-city fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="city" id="city" placeholder="Enter City" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Address</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="current_address" id="current_address" placeholder="Enter Address" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Phone Number -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Resume</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control" id="resume" name="resume">
                                    <div id="currentResume"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Notes</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <!-- <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-pencil fs-5"></i></span>
                                        </div> -->
                                        <textarea class="form-control" name="notes" id="notes" placeholder="Enter Description"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="text-end">
                        <a href="/candidateview" class="btn hr-btnbg addsmbtnres">
                            Back
                        </a>
                        <button type="submit" class="btn hr-btnbg addsmbtnres" id="submitBtn"><span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner" role="status" aria-hidden="true"></span>Submit</button>
                    </div>
                    <div id="responseMessage"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Job Modal -->
<div class="modal fade" id="quickJobModal" tabindex="-1" aria-labelledby="quickJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickJobModalLabel">Quick Add Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickJobForm">
                <div class="modal-body">
                    <div class="row">
                        <!-- Job Title -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="job_title" required>
                        </div>
                        <!-- Department -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php if(isset($departments)): foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= $dept['department_name'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <!-- Location -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <select class="form-select" name="locations_id" required>
                                <option value="">Select Location</option>
                                <?php if(isset($locations)): foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['location_id'] ?? $loc['id'] ?>"><?= $loc['job_location'] ?? $loc['location_name'] ?? 'Location' ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <!-- Job Type -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="job_type" required>
                                <option value="full">Full Time</option>
                                <option value="part">Part Time</option>
                            </select>
                        </div>
                        <!-- Experience -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience (Years) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="experience" min="0" required>
                        </div>
                        <!-- Age Required -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Age Range <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="age" placeholder="e.g. 18-65" value="18-65" required>
                        </div>
                        <!-- Salary Range -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Range <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="salary_range" placeholder="e.g. 10000-50000" value="10000-50000" required>
                        </div>
                        <!-- Close Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Close Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="close_date" required>
                        </div>
                        <!-- Gender -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="both">Both</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <!-- Status is open by default -->
                        <input type="hidden" name="status" value="open">
                        <input type="hidden" name="addresses_id" value="1">
                    </div>
                </div>
                <div class="modal-footer">
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
        let isEditMode = false; // Flag to track whether we're in edit mode
        let candidateId = null; // To store the country ID for updating

        $('#candidateForm').on('submit', function(e) {
            e.preventDefault();
            $('#submitBtn').attr('disabled', true);                // Disable the button
             $('#submitSpinner').removeClass('d-none');
            let formData = new FormData(this);
             const csrfName = $('meta[name="csrf-token"]').attr('data-name');
            const csrfHash = $('meta[name="csrf-token"]').attr('content');
            formData.append(csrfName, csrfHash);
            // Clear previous validation messages
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');
            const baseUrl = "<?= base_url() ?>"; // This will generate the base URL dynamically from PHP

            const url = candidateId ? `${baseUrl}api/candidate/${candidateId}` : `${baseUrl}api/candidate`;
            const method = isEditMode ? 'POST' : 'POST'; // Method for both actions
            // $('#loader').show();

            $.ajax({
                url: url,
                type: method,
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // $('#loader').hide(); // Hide loader

                    if (response.status === 'success') {
                        // Show success alert using SweetAlert
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000, // Auto-close after 2 seconds
                            showConfirmButton: false,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',

                            }
                        }).then(() => {
                            // Redirect to candidate view page after the alert
                            window.location.href = "/candidateview";
                        });

                        // Reset form
                        $('#candidateForm')[0].reset();
                        if (isEditMode) {
                            $('#submitBtn').text('Submit');
                            isEditMode = false;
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',

                            }
                        });
                    }
                },
                error: function(xhr) {
                    // $('#loader').hide(); // Hide loader on error

                    let errors = xhr.responseJSON.errors;

                    if (typeof displayValidationErrors === 'function') displayValidationErrors(errors);
                },
                  complete: function() {
            $('#submitSpinner').addClass('d-none');         // Hide spinner
            $('#submitBtn').attr('disabled', false);        // Re-enable button
        }
            });

        });

        // Handle Quick Job Add
        $('#quickJobForm').on('submit', function(e) {
            e.preventDefault();
            $('#quickJobSubmitBtn').attr('disabled', true).text('Saving...');
            let formData = new FormData(this);
            const csrfName = $('meta[name="csrf-token"]').attr('data-name');
            const csrfHash = $('meta[name="csrf-token"]').attr('content');
            formData.append(csrfName, csrfHash);
            
            $.ajax({
                url: "<?= base_url('api/job') ?>",
                type: "POST",
                headers: { 'Authorization': `Bearer ${token}` },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        // Add new option to Job select dropdown
                        let jobId = response.data ? response.data.id : (response.job_id || null);
                        let jobTitle = formData.get("job_title");
                        if(jobId) {
                            $('#job_id').append(`<option value="${jobId}" selected>${jobTitle}</option>`);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Job Added',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#quickJobModal').modal('hide');
                        $('#quickJobForm')[0].reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Validation failed or missing fields.'
                    });
                },
                complete: function() {
                    $('#quickJobSubmitBtn').attr('disabled', false).text('Save Job');
                }
            });
        });
        // const params = new URLSearchParams(window.location.search);
        // let Id = params.get('id');



        //     if (!Id) {
        //     const pathParts = window.location.pathname.split('/');
        //     var temp_id = pathParts[pathParts.length - 1];
        //     if (!isNaN(temp_id) && !isNaN(parseFloat(temp_id))) {
        //         Id = temp_id;
        //     }
        // }
        const params = new URLSearchParams(window.location.search);
        let Id = params.get('id');

        // If not found, try extracting from the URL path
        if (!Id) {
            const pathParts = window.location.pathname.split('/');
            var temp_id = pathParts[pathParts.length - 1];
            if (!isNaN(temp_id) && !isNaN(parseFloat(temp_id))) {
                Id = temp_id;
            }
        }

        if (Id) {
            candidateId = Id;
            fetchUserData(Id);
        }

        function fetchUserData(Id) {
            // alert("hi..");
            $.ajax({
                url: `/api/candidateedit/${Id}`,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,

                },
                success: function(responseData) {
                    if (responseData.status === 'success') {
                        const candidate = responseData.data;
                        $('#candidate_name').val(candidate.candidate_name);
                        $('#email').val(candidate.email);
                        $('#job_id').val(candidate.job_id);
                        $('#notes').val(candidate.notes);
                        $('#phone_number').val(candidate.phone_number);
                        $('#status').val(candidate.status);
                        // $('#notes').val(candidate.notes);

                        $('#id').val(candidate.id); //Set the hidden ID field for updating
                        if (candidate.resume) {
                            // Check if the resume exists, and if it does, display it with a clickable link
                            $('#currentResume').html(
                                `<a href="${candidate.resume}" target="_blank"><p style="color:#E66136; list-style:none;">View Current Resume</p></a>`
                            );
                        } else {
                            // If there's no resume, display a message saying no resume is uploaded
                            $('#currentResume').html('<p class="text-muted">No resume uploaded</p>');
                        }

                        $('#submitBtn').text('Update'); // Change button text to "Update"
                        $('.card-title').text('Edit Candidate');
                        candidateId = candidate.id; // Set the department ID for future reference
                        isEditMode = true; // Set edit mode flag
                    } else {
                        $('#responseMessage').html('<p class="text-danger">cnadidate not found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching country:', error);
                    $('#responseMessage').html('<p class="text-danger">Error fetching cnadidate.</p>');
                }
            });

        }

        // function populateForm(data) {
        //     $("#id").val(data.id);
        //     console.log(data);
        //     $('#job_title').val(data.job_title);
        //                 $('#description').val(data.description);
        //                 $('#department_id').val(data.department_id);
        //                 $('#status').val(data.status);
        //                 $('#location').val(data.location);
        //                 $('#age').val(data.age);
        //                 $('#job_type').val(data.job_type);
        //                 $('#experience').val(data.experience);
        //                 $('#salary_range').val(data.salary_range);
        //                 $('#post_date').val(data.post_date);
        //                 $('#close_date').val(data.close_date); // Populate the form fields
        //              //Set the hidden ID field for updating
        // }


    });
</script>

<?= $this->endSection() ?>
