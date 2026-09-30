<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    .locationmar{
        margin-top: 8px !important;
    }
    @media (max-width: 767px) {
    .interviewsmbtn{
     font-size: 10px !important;
    padding: 8px !important;
    margin-top: 10px !important;
}
    }

</style>
<div class="modal fade" id="adddepartementModal" tabindex="-1" aria-labelledby="adddepartementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adddepartementModalLabel">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="departmentForm">
                    <?php
                        $curUserRole = $currentUserRole ?? session()->get('role') ?? '';
                        $isAdminOrHr = in_array($curUserRole, ['admin', 'hr']);
                        $isBranchAdminRole = ($curUserRole === 'branch_admin');
                        $userBranchId = $currentUserBranchId ?? session()->get('branch_id') ?? '';
                        $branchesList = $branches ?? (new \App\Models\BranchModel())->getActiveBranches();
                    ?>
                    <?php if ($isAdminOrHr): ?>
                    <div class="mb-3">
                        <label for="dept_modal_branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                        <select class="form-select" name="branch_id" id="dept_modal_branch_id" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($branchesList as $branch): ?>
                                <option value="<?= esc($branch['id']) ?>"><?= esc($branch['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="text-danger mt-1" id="dept_branch_error"></div>
                    </div>
                    <?php elseif ($isBranchAdminRole): ?>
                    <!-- Hidden branch_id auto-filled for branch_admin -->
                    <input type="hidden" name="branch_id" id="dept_modal_branch_id" value="<?= esc($userBranchId) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="department_name" class="form-label">Department Name</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-briefcase fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="department_name" id="department_name" placeholder="Enter Department Name" />
                        </div>
                        <div class="text-danger mt-1" id="department_name_error"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="departmentSubmitBtn">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Training</h4>
                <form class="form-sample" method="POST" id="trainingForm">
    <input type="hidden" name="id" id="id" value=""> <!-- Hidden input for ID -->

    <!-- Row 1: Training Title & Employee Name -->
    <div class="row gy-3">
        <div class="col-md-6">
            <label class="form-label leave-sm-emp">Training Title</label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-pen fs-5"></i></span>
                <input type="text" class="form-control" name="training_title" id="training_title" placeholder="Enter Training Title" />
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label leave-sm-emp">Employee Name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-account fs-5"></i></span>
                <select class="form-select" name="user_id" id="user_id">
                    <option value="" disabled selected>Select Employee Name</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee["id"] ?>"><?= esc(
    $employee["username"],
) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Row 2: Department & Location -->
    <div class="row gy-3 mt-1">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label leave-sm-emp mb-0">Department</label>
                <button type="button" class="btn btn-sm p-1 d-flex align-items-center rounded addbtn-white attendenceall"
                    style="background-color: #E66136; font-size: 14px;" data-bs-toggle="modal" data-bs-target="#adddepartementModal">
                    <i class="mdi mdi-plus iconfontsize me-1"></i> Add Department
                </button>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-briefcase fs-5"></i></span>
                <select class="form-select" name="department_id" id="department_id">
                    <option value="" disabled selected>Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department[
                            "id"
                        ] ?>"><?= $department["department_name"] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label leave-sm-emp">Location</label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-map-marker fs-5"></i></span>
                <input type="text" class="form-control" name="location" id="location" placeholder="Enter Location" />
            </div>
        </div>
    </div>

    <!-- Row 3: Start Date & End Date -->
    <div class="row gy-3 mt-1">
        <div class="col-md-6">
            <label class="form-label leave-sm-emp">Start Date</label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                <input type="date" class="form-control" name="start_date" id="start_date" />
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label leave-sm-emp">End Date</label>
            <div class="input-group">
                <span class="input-group-text"><i class="mdi mdi-calendar-check fs-5"></i></span>
                <input type="date" class="form-control" name="end_date" id="end_date" />
            </div>
        </div>
    </div>

    <!-- Row 4: Description -->
    <div class="row gy-3 mt-1">
        <div class="col-md-12">
            <label class="form-label leave-sm-emp">Description</label>
            <textarea class="form-control" name="description" id="description" placeholder="Enter Description" rows="4"></textarea>
        </div>
    </div>

    <!-- Form Buttons -->
    <div class="text-end mt-4">
        <a href="<?= base_url(
            "/trainingview",
        ) ?>" class="btn hr-btnbg interviewsmbtn me-2">Back</a>
        <button type="submit" class="btn hr-btnbg interviewsmbtn" id="trainingSubmitBtn">Submit</button>
    </div>

    <div id="responseMessage"></div>
</form>

            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        const token = localStorage.getItem('token');
        let isEditMode = false; // Flag to track whether we're in edit mode
        let trainingId = null; // To store the training ID for updating

        // Handle form submission
        $('#trainingForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous validation messages
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');

            let url = `<?= base_url(
                "api/training/creates",
            ) ?>`; // Default URL for insert
            let method = 'POST'; // Default method for insert
            let formData = new FormData();

            // Manually gather form data (without serialize)
            $('#trainingForm').find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (name && value) {
                    formData.append(name, value);
                }
            });

            if (isEditMode) {
                url = `<?= base_url(
                    "api/training/update/",
                ) ?>${trainingId}`; // URL for update
                method = 'POST'; // Method for update
            }
            $('#loader').show();

            $.ajax({
                url: url,
                type: method,
                headers: {
                    'Authorization': `Bearer ${token}`,
                },
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Prevent jQuery from setting the content-type
                success: function(response) {
                    $('#loader').hide();

                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',

                            }
                        }).then(() => {
                            window.location.href = "/trainingview"; // Redirect after success message
                        });

                        $('#trainingForm')[0].reset(); // Reset the form
                        if (isEditMode) {
                            $('#trainingSubmitBtn').text('Submit'); // Reset button text after update
                            isEditMode = false;
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',

                            }
                        });
                    }
                },
                error: function(xhr) {
                    $('#loader').hide();

                    let message = xhr.responseJSON.message;

                    if (typeof displayValidationErrors === 'function') displayValidationErrors(message);

                    // Swal.fire({
                    //     icon: 'error',
                    //     title: 'Validation Error!',
                    //     text: 'Please correct the highlighted fields.'
                    // });
                }
            });
        });

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
            console.log('Id', Id);
            trainingId = Id;
            fetchTrainingData(Id);
        }

        function fetchTrainingData(Id) {
            if (Id) {
                $.ajax({
                    url: `<?= base_url("api/training/get/") ?>${Id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                    success: function(responseData) {
                        console.log(responseData);
                        // Handle success
                        if (responseData.status === 'success' && responseData.data.length > 0) {
                            const training = responseData.data[0]; // Extract the first record

                            // Ensure form fields are correctly mapped
                            $('#user_id').val(training.user_id);
                            $('#department_id').val(training.department_id);
                            $('#training_title').val(training.training_title);
                            $('#designation_id').val(training.designation_id);
                            $('#description').val(training.description);
                            $('#start_date').val(training.start_date);
                            $('#end_date').val(training.end_date);
                            $('#location').val(training.location);

                            $('#id').val(training.id); // Set hidden input field for ID
                            $('#trainingSubmitBtn').text('Update'); // Change button text
                            $('.card-title').text('Edit Training');
                            isEditMode = true; // Enable edit mode
                        } else {
                            $('#responseMessage').html('<p class="text-danger">Training record not found.</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching training:', error);
                        $('#responseMessage').html('<p class="text-danger">Error fetching training.</p>');
                    }
                });
            } else {
                console.error('Invalid ID');
                $('#responseMessage').html('<p class="text-danger">Invalid ID provided.</p>');
            }
        }
    });

    $(document).ready(function() {
        const departmentToken = localStorage.getItem('token');

        $("#departmentForm").submit(function(e) {
            e.preventDefault();

            $('#department_name_error').text('');
            $('#dept_branch_error').text('');

            let departmentName = $("#department_name").val().trim();

            if (departmentName === "") {
                $('#department_name_error').text('Department Name is required.');
                return;
            }

            <?php if ($isAdminOrHr): ?>
            if (!$("#dept_modal_branch_id").val()) {
                $('#dept_branch_error').text('Please select a branch.');
                return;
            }
            <?php endif; ?>

            // let formData = $(this).serialize();
            let formData = new FormData(this);
            const csrfName = $('meta[name="csrf-token"]').attr('data-name');
            const csrfHash = $('meta[name="csrf-token"]').attr('content');
            if (csrfName && csrfHash) {
                formData.append(csrfName, csrfHash);
            }
            let reqHeaders = {};
            if (departmentToken && departmentToken !== 'null' && departmentToken !== 'undefined') {
                reqHeaders['Authorization'] = 'Bearer ' + departmentToken;
            }
            $.ajax({
                url: "<?= base_url("api/department/add") ?>",
                type: "POST",
                headers: reqHeaders,
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    $('#department_name_error').text('');
                    $('#dept_branch_error').text('');

                    if (response.success) {
                        $("#department_id").append(new Option(
                            response.department.department_name,
                            response.department.id,
                            true,
                            true
                        ));
                        if ($("#department_id_modal").length) {
                            $("#department_id_modal").append(new Option(
                                response.department.department_name,
                                response.department.id,
                                true,
                                true
                            ));
                        }

                        $("#departmentForm")[0].reset();
                        $("#adddepartementModal").modal("hide");

                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Department added successfully.",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        // ❗ Show SweetAlert for errors like duplicate
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: response.message,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + error);
                    let errorMessage = "Something went wrong while adding the department.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: "error",
                        title: "AJAX Error",
                        text: errorMessage,
                        showConfirmButton: true
                    });
                }
            });
        });
    });

</script>
<?= $this->endSection() ?>
