<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    .perviedisplay {
        display: flex;
        justify-content: end;
        margin-bottom: 2px;

    }
 @media (max-width: 767px) {
        .interviewsmbtn {
            font-size: 12px !important;
            padding: 8px !important;
            margin-top: 10px !important;
        }

        .perviedisplay {
            display: block !important;
            justify-content: unset !important;
            margin-bottom: 0px !important;
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
                      <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">
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

                    <button type="submit" class="btn hr-btnbg float-end" id="submitBtn">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Add Task</h4>
                </div>
                <form class="form-sample" method="POST" id="taskForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="id" />

                    <div class="row gy-4">
                        <!-- Employee Name -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Employee Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-account fs-5"></i></span>
                                <select class="form-select" name="user_id" id="user_id">
                                    <option value="" disabled selected>Select Employee Name</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?= $employee[
                                            "id"
                                        ] ?>"><?= esc(
    $employee["username"],
) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Task Title -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Task Title</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-pencil fs-5"></i></span>
                                <input type="text" class="form-control" name="task_title" id="task_title" placeholder="Enter Task Title" />
                            </div>
                        </div>

                        <!-- Department -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label leave-sm-emp mb-0">Department</label>
                                <button type="button" class="btn btn-sm d-flex align-items-center rounded addbtn-white" style="background-color: #E66136; font-size: 14px" data-bs-toggle="modal" data-bs-target="#adddepartementModal">
                                    <i class="mdi mdi-plus iconfontsize me-1"></i> Add Department
                                </button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-briefcase fs-5"></i></span>
                                <select class="form-select" name="department_id" id="department_id">
                                    <option value="">Select Department</option>
                                    <?php foreach (
                                        $departments
                                        as $department
                                    ): ?>
                                        <option value="<?= $department[
                                            "id"
                                        ] ?>"><?= $department[
    "department_name"
] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Task Status -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Status</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-check-circle fs-5"></i></span>
                                <select class="form-select" name="task_status" id="task_status">
                                    <option value="" disabled selected>Select Task Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="In-Progress">In-Progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Assigned Date -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Assigned Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                                <input type="date" class="form-control" name="assigned_date" id="assigned_date" />
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Due Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-calendar-check fs-5"></i></span>
                                <input type="date" class="form-control" name="due_date" id="due_date" />
                            </div>
                        </div>

                        <!-- Document Upload -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Document</label>
                            <input type="file" class="form-control" id="document" name="document[]" multiple>
                            <div class="invalid-feedback"></div>
                            <div id="currentdocument"></div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <label class="form-label leave-sm-emp">Description</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Enter Task Description" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="text-end mt-4">
                        <a href="<?= base_url(
                            "/taskview",
                        ) ?>" class="btn hr-btnbg interviewsmbtn me-2">Back</a>
                        <button type="submit" class="btn hr-btnbg interviewsmbtn" id="submitBtn">Submit</button>
                    </div>
                </form>

                <div id="responseMessage"></div>
            </div>
        </div>
    </div>
</div>
<template id="subtaskTemplate">
    <div class="subtask-item border rounded p-3 mt-3 position-relative bg-light-subtle">
        <button type="button" class="btn-close position-absolute top-0 end-0 remove-subtask" aria-label="Close"></button>

        <div class="row">
            <div class="col-md-3">
                <label>Subtask Title</label>
                <input type="text" class="form-control" name="subtasks[][title]" placeholder="Enter subtask title" required>
            </div>
            <div class="col-md-3">
                <label>Assigned Date</label>
                <input type="date" class="form-control" name="subtasks[][assigned_date]">
            </div>
            <div class="col-md-3">
                <label>Due Date</label>
                <input type="date" class="form-control" name="subtasks[][due_date]">
            </div>
            <div class="col-md-3">
                <label>Subtask Status</label>
                <select class="form-select" name="subtasks[][subtask_status]">
                    <option value="" disabled selected>Select Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In-Progress">In-Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
    </div>
</template>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        const token = localStorage.getItem('token');
        let isEditMode = false; // Flag to track edit mode
        let taskId = null; // Store task ID for updating

        // Handle form submission
        $('#taskForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous validation messages
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');

            let url = `<?= base_url(
                "api/task/create",
            ) ?>`; // Default insert URL
            let method = 'POST'; // Default method for insert
             const formData = new FormData(this);
        const csrfTokenName = '<?= csrf_token() ?>';
        const csrfTokenValue = $('#csrfToken').val();
        formData.append(csrfTokenName, csrfTokenValue);
            // var formData = new FormData();
            formData.append('user_id', $('#user_id').val());
            formData.append('task_title', $('#task_title').val());
            formData.append('department_id', $('#department_id').val());
            formData.append('assigned_date', $('#assigned_date').val());
            formData.append('due_date', $('#due_date').val());
            formData.append('task_status', $('#task_status').val() || ''); // <--- important!
            // Manually gather form data
            $('#taskForm').find('input, select, textarea').each(function() {
                const name = $(this).attr('name');

                if (!name) return;

                if ($(this).attr('type') === 'file') {
                    const files = $(this)[0].files;
                    for (let i = 0; i < files.length; i++) {
                        formData.append(name, files[i]); // Keep same name "document[]"
                    }
                } else {
                    formData.append(name, $(this).val());
                }
            });

            if (isEditMode) {
                url = `<?= base_url(
                    "api/task/update/",
                ) ?>${taskId}`; // Update URL
                method = 'POST'; // Method remains POST
            }
            $('#loader').show();

            $.ajax({
                url: url,
                type: method,
                headers: {
                    'Authorization': `Bearer ${token}`,
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#loader').hide();

                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',

                            }
                        }).then(() => {
                            window.location.href = "/taskview"; // Redirect after success
                        });

                        $('#taskForm')[0].reset(); // Reset the form
                        if (isEditMode) {
                            $('#submitBtn').text('Submit'); // Reset button text
                            isEditMode = false;
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
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
                 }
            });
        });

        // Fetch task ID from URL for editing
        const params = new URLSearchParams(window.location.search);
        let Id = params.get('id');

        if (!Id) {
            const pathParts = window.location.pathname.split('/');
            var temp_id = pathParts[pathParts.length - 1];
            if (!isNaN(temp_id) && !isNaN(parseFloat(temp_id))) {
                Id = temp_id;
            }
        }

        if (Id) {
            taskId = Id;
            fetchTaskData(Id);
        }

        // Function to fetch task data for editing
        function fetchTaskData(Id) {
            $.ajax({
                url: `<?= base_url("api/task/") ?>${Id}`,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                },
                success: function(responseData) {
                    if (responseData.status === 'success' && responseData.data.length > 0) {
                        const task = responseData.data[0];

                        $('#user_id').val(task.user_id);
                        $('#task_title').val(task.task_title);
                        $('#task_status').val(task.task_status);
                        $('#assigned_date').val(task.assigned_date);
                        $('#description').val(task.description);
                        $('#due_date').val(task.due_date);
                        $('#department_id').val(task.department_id);

                        $('#id').val(task.id); // Set hidden input for ID

                        // ✅ Show existing uploaded documents
                        if (task.document) {
                            try {
                                const docs = JSON.parse(task.document);
                                let html = '<ul class="list-group mt-2">';
                                docs.forEach(doc => {
                                    html += `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${doc.original}
                                        <a href="/upload/document/${doc.stored}" target="_blank" class="btn btn-sm" style="color: #E66136;">View</a>
                                    </li>`;
                                });
                                html += '</ul>';
                                $('#currentdocument').html(html);
                            } catch (err) {
                                // console.error("Failed to parse document JSON", err);
                                $('#currentdocument').html('<p class="text-danger">Failed to load documents</p>');
                            }
                        } else {
                            $('#currentdocument').html('');
                        }

                        $('#submitBtn').text('Update');
                        $('.card-title').text('Edit Task');
                        isEditMode = true;
                    } else {
                        $('#responseMessage').html('<p class="text-danger">Task record not found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch task details.',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'hr-btnbg',

                        }
                    });
                }
            });
        }
    });
    $(document).ready(function() {
        const taskDeptToken = localStorage.getItem('token');
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

            let formData = $(this).serialize();

            let reqHeaders = {};
            if (taskDeptToken && taskDeptToken !== 'null' && taskDeptToken !== 'undefined') {
                reqHeaders['Authorization'] = 'Bearer ' + taskDeptToken;
            }

            $.ajax({
                url: "<?= base_url("api/department/add") ?>",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: reqHeaders,
                success: function(response) {
                    $('#department_name_error').text('');
                    $('#dept_branch_error').text('');

                    if (response.success) {
                        let newOption = `<option value="${response.department.id}" selected>${response.department.department_name}</option>`;
                        $("#department_id").append(newOption);
                        $("#department_id_modal").append(newOption);

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
