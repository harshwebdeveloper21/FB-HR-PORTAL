<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<link rel="stylesheet" href="<?= base_url(
    env("ImagePath") . "assets/css/multistepform.css",
) ?>">
<style>
    @media (max-width: 767px) {

        .iconfontsize {
            font-size: 11px !important;
        }

        .cart-sm-title {
            font-size: 12px !important;
            margin-bottom: 5px !important;
        }

        .dataTables_length {
            margin-left: 1rem !important;
            margin-bottom: .5rem !important;
            font-size: 12px !important;
            float: left !important;
        }

        .dataTables_filter {
            font-size: 12px !important;
            float: left !important;
            /* margin-left: -5rem !important;  */
        }

        .interviewsmbtn {
            font-size: 12px !important;
            padding: 8px !important;
            margin-top: 10px !important;
        }
    }
</style>
<div class="modal fade" id="addStateModal" tabindex="-1" aria-labelledby="addStateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStateModalLabel">Add Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stateForm">
                    <div class="mb-3">
                        <label for="country_name" class="form-label">State Name</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                            </div>
                            <input type="text" id="state_name" name="state_name" class="form-control"
                                placeholder="Enter State Name" />
                        </div>
                        <div id="state_name_error" class="invalid-feedback d-block mt-1 text-danger"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="submitState">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- city modal -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCityModalLabel">Add City</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="cityForm">
                    <div class="mb-3">
                        <label for="city_name" class="form-label">City Name</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-city fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="city_name" id="city_name"
                                placeholder="Enter city Name" />

                        </div>
                        <div id="city_name_error" class="invalid-feedback d-block text-danger mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address_details" class="form-label">Country Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                            </div>
                            <select class="form-select" name="country_id" id="countries_id_modal">
                                <option value="">Select your country</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= $country["id"] ?>"><?= $country["country_name"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="country_id_error" class="invalid-feedback d-block text-danger mt-1"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- country modal -->
<div class="modal fade" id="addCountryModal" tabindex="-1" aria-labelledby="addCountryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCountryModalLabel">Add Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="countryForm">
                    <div class="mb-3">
                        <label for="country_name" class="form-label">Country Name</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                            </div>
                            <input type="text" id="country_name" name="country_name" class="form-control"
                                placeholder="Enter Country Name" />
                        </div>
                        <div id="country_name_error" class="invalid-feedback d-block text-danger mt-1"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="submitContry">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- designation -->
<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDesignationModalLabel">Add Designation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="designationForm">
                    <div class="mb-3">

                        <label for="address_details" class="form-label">Department Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-domain fs-5"></i></span>
                            </div>
                            <select class="form-select" name="department_id" id="department_id_modal">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= $department["id"] ?>"><?= $department["department_name"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="departmentError" class="text-danger mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address_details" class="form-label">Designation Name</label>
                        <div class="input-group">

                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="designation_name" id="designation_name"
                                placeholder="Enter Designation Name" />

                        </div>
                        <div id="designationError" class="text-danger mt-1"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- departement -->

<div class="modal fade" id="adddepartementModal" tabindex="-1" aria-labelledby="adddepartementModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adddepartementModalLabel">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="departmentForm">
                    <?php
                        $curUserRole = $currentUserRole ?? session()->get('role') ?? 'employee';
                        $isAdminOrHr = in_array($curUserRole, ['admin', 'hr']);
                        $isBranchAdminRole = ($curUserRole === 'branch_admin');
                        $userBranchId = $currentUserBranchId ?? session()->get('branch_id') ?? '';
                    ?>

                    <?php if ($isAdminOrHr): ?>
                    <!-- Branch selector for Admin / HR -->
                    <div class="mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-office-building fs-5"></i></span>
                            </div>
                            <select class="form-select" name="branch_id" id="dept_modal_branch_id">
                                <option value="">Select Branch</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['id'] ?>"><?= esc($branch['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="text-danger mt-1" id="dept_branch_error"></div>
                    </div>
                    <?php elseif ($isBranchAdminRole): ?>
                    <!-- Hidden branch_id for branch manager -->
                    <input type="hidden" name="branch_id" id="dept_modal_branch_id" value="<?= esc($userBranchId) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="department_name" class="form-label">Department Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-briefcase fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="department_name" id="department_name"
                                placeholder="Enter Department Name" />
                        </div>
                        <div class="text-danger mt-1" id="department_name_error"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">

                <div class="d-md-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Add Employee</h4>
                    <div class="d-flex">
                        <a href="<?= base_url(
                            "/empview",
                        ) ?>" class="btn hr-btnbg">
                            <i class="mdi mdi-list iconfontsize"></i> All Employee
                        </a>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="progress">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"></div>
                </div>

                <!-- Multi-step Form -->
                <form id="multistepForm" enctype="multipart/form-data">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step step active" id="step1">
                        <input type="hidden" id="id" name="id" value="<?= !empty($user['id']) ? esc($user['id']) : '' ?>" />

                        <h5>Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-account fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="firstname" id="firstname"
                                            placeholder="Enter your first name" />
                                    </div>
                                    <div class="error" id="firstname-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-account-multiple fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="lastname" id="lastname"
                                            placeholder="Enter your last name" />
                                    </div>
                                    <div class="error" id="lastname-Error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-email-outline fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="email" id="email"
                                            placeholder="Enter your email address" autocomplete="off" />
                                    </div>
                                    <div class="error" id="email-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password <span class="text-danger password-required-star">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-lock-outline fs-5"></i></span>
                                        </div>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Enter a secure password" autocomplete="off" />
                                    </div>
                                    <div class="error" id="password-Error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <div class="input-group border rounded-1">
                                        <span class="input-group-text bg-white"><i
                                                class="mdi mdi-gender-male-female"></i></span>
                                        <div class="d-flex flex-wrap align-items-center ms-3">
                                            <div class="form-check m-0 mx-4 p-0">
                                                <input class="form-check-input" type="radio" name="gender"
                                                    id="gender_male" value="male" checked>
                                                <label class="form-check-label mb-0" for="male">Male</label>
                                            </div>
                                            <div class="form-check m-0 mx-4 p-0">
                                                <input class="form-check-input" type="radio" name="gender"
                                                    id="gender_female" value="female">
                                                <label class="form-check-label mb-0" for="female">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="error" id="gender-Error"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-calendar-today fs-5"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                            placeholder="Enter your birth date" />
                                    </div>
                                    <div class="error" id="date_of_birth-Error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remaining Paid Leave</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-calendar-plus fs-5"></i></span>
                                        </div>
                                        <input type="number" step="0.5" class="form-control" id="remaining_paid_leave"
                                            name="remaining_paid_leave" placeholder="Enter remaining paid leave"
                                            value="0" />
                                    </div>
                                    <div class="error" id="remaining_paid_leave-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remaining Sick Leave</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-calendar-plus fs-5"></i></span>
                                        </div>
                                        <input type="number" step="0.5" class="form-control" id="remaining_sick_leave"
                                            name="remaining_sick_leave" placeholder="Enter remaining sick leave"
                                            value="0" />
                                    </div>
                                    <div class="error" id="remaining_sick_leave-Error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-end">

                            <button type="button" class="btn hr-btnbg next-step interviewsmbtn" id="next1">Next</button>

                        </div>
                    </div>

                    <!-- Step 2: Address -->
                    <div class="form-step step" id="step2">
                        <h5>Address & Contacts</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address 1</label>
                                    <div class="input-group">
                                        <textarea class="form-control" name="address_1" id="address_1"
                                            placeholder="Enter your primary address" rows="5"></textarea>
                                    </div>
                                    <div class="error" id="address_1-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address 2</label>
                                    <div class="input-group">
                                        <textarea class="form-control" name="address_2" id="address_2"
                                            placeholder="Enter your secondary address (optional)" rows="5"></textarea>
                                    </div>
                                    <div class="error" id="address_2-Error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <label>State</label>
                                        <button type="button"
                                            class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white"
                                            style="background-color: #E66136;font-size:14px" data-bs-toggle="modal"
                                            data-bs-target="#addStateModal">
                                            <i class="mdi mdi-plus"></i> Add State
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="state_id" id="state_id_main">
                                            <option value="">Select your state</option>
                                            <?php foreach ($state as $country): ?>
                                                <option value="<?= $country["id"] ?>"><?= $country["state_name"] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="error" id="state_id-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <label>Country</label>
                                        <button type="button"
                                            class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white"
                                            style="background-color: #E66136;font-size:14px" data-bs-toggle="modal"
                                            data-bs-target="#addCountryModal">
                                            <i class="mdi mdi-plus"></i> Add Country
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="country_id" id="country_id_main">
                                            <option value="">Select your country</option>
                                            <?php foreach (
                                                $countries
                                                as $country
                                            ): ?>
                                                <option value="<?= $country["id"] ?>"><?= $country["country_name"] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="error" id="country_id-Error"></div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <label>City</label>
                                        <button type="button"
                                            class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white"
                                            style="background-color: #E66136;font-size:14px" data-bs-toggle="modal"
                                            data-bs-target="#addCityModal">
                                            <i class="mdi mdi-plus"></i> Add City
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-city fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="city_id" id="city_id">
                                            <option value="">Select your city</option>
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?= $city["id"] ?>"><?= $city["city_name"] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="error" id="city_id-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Postcode</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-mailbox-outline fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="postcode" id="postcode"
                                            placeholder="Enter your postcode/ZIP code" />
                                    </div>
                                    <div class="error" id="postcode-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-phone-outline fs-5"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="contact_number"
                                            name="contact_number" placeholder="Enter your phone number" />
                                    </div>
                                    <div class="error" id="contact_number-Error"></div>
                                </div>
                            </div>
                            <?php 
                                $empUserRole = $currentUserRole ?? session()->get('role') ?? 'employee';
                                $isEmpAdminOrHr = in_array($empUserRole, ['admin', 'hr']);
                                $isEmpBranchAdmin = ($empUserRole === 'branch_admin');
                                $empBranchId = $currentUserBranchId ?? session()->get('branch_id') ?? '';
                                $branchesList = !empty($branches) ? $branches : (new \App\Models\BranchModel())->where('status', 'active')->orderBy('name', 'ASC')->findAll();
                            ?>
                            <?php if ($isEmpAdminOrHr): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Branch <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="mdi mdi-source-branch fs-5"></i></span>
                                            </div>
                                            <select class="form-select" name="branch_id" id="branch_id">
                                                <option value="">Select Branch</option>
                                                <?php foreach ($branchesList as $branch): ?>
                                                    <option value="<?= htmlspecialchars($branch['id']) ?>"><?= htmlspecialchars($branch['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="error" id="branch_id-Error"></div>
                                    </div>
                                </div>
                            <?php elseif ($isEmpBranchAdmin): ?>
                                <input type="hidden" name="branch_id" id="branch_id" value="<?= htmlspecialchars($empBranchId) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="form-group text-end">
                            <button type="button" class="btn btn-secondary prev-step interviewsmbtn me-2" id="prev2"
                                style="display: none;">Previous</button>
                            <button type="button" class="btn hr-btnbg next-step interviewsmbtn" id="next2"
                                style="display: none;">Next</button>

                        </div>
                    </div>

                    <!-- Step 3: Job Details -->
                    <div class="form-step step" id="step3">
                        <h5>Job Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Employee ID <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-badge-account-outline fs-5"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="employee_id" id="employee_id" placeholder="Enter Employee ID (e.g. EMP-101)" required />
                                    </div>
                                    <div class="error text-danger mt-1" id="employee_id-Error"></div>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <label>Department <span class="text-danger">*</span></label>
                                        <button type="button"
                                            class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white"
                                            style="background-color: #E66136;font-size:14px" data-bs-toggle="modal"
                                            data-bs-target="#adddepartementModal">
                                            <i class="mdi mdi-plus"></i> Add Department
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-domain fs-5"></i></span>
                                        </div>
                                        <select class="form-select" id="department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            <?php foreach ($departments as $department): ?>
                                                <?php 
                                                    if ($isEmpBranchAdmin) {
                                                        if ((int) ($department['branch_id'] ?? 0) !== (int) $empBranchId) {
                                                            continue;
                                                        }
                                                        $deptName = strtolower(trim($department["department_name"]));
                                                        if (in_array($deptName, ['hr', 'admin', 'superadmin', 'super admin', 'branch admin', 'branch manager', 'barnch manger'])) {
                                                            continue;
                                                        }
                                                    }
                                                ?>
                                                <option value="<?= $department["id"] ?>">
                                                    <?= $department["department_name"] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="error" id="department_id-Error"></div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <label>Designation</label>
                                        <button type="button"
                                            class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white"
                                            style="background-color: #E66136;font-size:14px" data-bs-toggle="modal"
                                            data-bs-target="#addDesignationModal">
                                            <i class="mdi mdi-plus"></i> Add Designation
                                        </button>
                                    </div>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-briefcase-outline fs-5"></i></span>
                                        </div>
                                        <select class="form-select" name="designation_id" id="designation_id">
                                            <option value="">Select Designation</option>
                                            <?php foreach ($designations as $designation): ?>
                                                <?php 
                                                    if ($isEmpBranchAdmin) {
                                                        $dName = strtolower(trim($designation["designation_name"]));
                                                        if (in_array($dName, ['hr', 'admin', 'superadmin', 'super admin', 'branch admin', 'manager'])) {
                                                            continue;
                                                        }
                                                    }
                                                ?>
                                                <option value="<?= $designation["id"] ?>">
                                                    <?= $designation["designation_name"] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="error" id="designation_id-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Joining Date</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-calendar-check-outline fs-5"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="joining_date" name="joining_date"
                                            placeholder="Select your Joining Date" />
                                    </div>
                                    <div class="error" id="joining_date-Error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Working Location</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-map-marker-outline fs-5"></i></span>
                                        </div>
                                        <select class="form-select" id="working_location" name="working_location">
                                            <option value="">Select Working Location</option>
                                            <option value="Remote">Remote</option>
                                            <option value="On-Site" selected>On-Site</option>
                                        </select>
                                    </div>
                                    <div class="error" id="working_location-Error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Salary <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="mdi mdi-currency-inr fs-5"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="salary" name="salary"
                                            placeholder="Enter your phone number" value="0" />
                                    </div>
                                    <div class="error" id="salary-Error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Employment Status</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-account-check-outline fs-5"></i></span>
                                        </div>
                                        <select class="form-select" id="status" name="status">
                                            <option value="Active" selected>Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Resigned">Resigned</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Working Day <small class="text-muted">(If Inactive/Resigned)</small></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-calendar-remove-outline fs-5"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="last_working_day" name="last_working_day" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="mdi mdi-account-cog-outline fs-5"></i></span>
                                        </div>
                                        <select class="form-select" id="role" name="role">
                                            <option value="">Select Role</option>
                                            <?php
                                                $curRole = $currentUserRole ?? 'employee';
                                                $editRole = isset($user) ? ($user['role'] ?? 'employee') : '';
                                            ?>
                                            <?php if ($curRole === 'admin'): ?>
                                                <option value="admin" <?= $editRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="hr" <?= $editRole === 'hr' ? 'selected' : '' ?>>HR</option>
                                                <option value="branch_admin" <?= $editRole === 'branch_admin' ? 'selected' : '' ?>>Branch Manager</option>
                                                <option value="department_manager" <?= $editRole === 'department_manager' ? 'selected' : '' ?>>Department Manager</option>
                                                <option value="employee" <?= ($editRole === 'employee' || $editRole === '') ? 'selected' : '' ?>>Employee</option>
                                                <option value="candidate" <?= $editRole === 'candidate' ? 'selected' : '' ?>>Candidate</option>
                                            <?php elseif ($curRole === 'hr'): ?>
                                                <option value="department_manager" <?= $editRole === 'department_manager' ? 'selected' : '' ?>>Department Manager</option>
                                                <option value="employee" <?= ($editRole === 'employee' || $editRole === '') ? 'selected' : '' ?>>Employee</option>
                                                <option value="candidate" <?= $editRole === 'candidate' ? 'selected' : '' ?>>Candidate</option>
                                            <?php elseif ($curRole === 'branch_admin'): ?>
                                                <option value="department_manager" <?= $editRole === 'department_manager' ? 'selected' : '' ?>>Department Manager</option>
                                                <option value="employee" <?= ($editRole === 'employee' || $editRole === '') ? 'selected' : '' ?>>Employee</option>
                                            <?php else: ?>
                                                <option value="employee" selected>Employee</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="error" id="role-Error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Profile Image</label>
                                    <input type="file" name="profile_image" class="file-upload-default profile_image"
                                        id="file-upload" style="display: none;" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled
                                            placeholder="Upload Image" id="file-name" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btnbg" type="button"
                                                onclick="document.getElementById('file-upload').click();">
                                                Upload
                                            </button>
                                        </span>
                                    </div>
                                    <img id="profile-preview" class="profile_image" src="" alt="Profile Image"
                                        style="max-width: 100px; display: none;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Face Photo <small class="text-muted">(For Biometric
                                            Attendance)</small></label>
                                    <input type="file" name="face_photo" class="file-upload-default"
                                        id="face-photo-upload" accept="image/*" style="display: none;" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info" disabled
                                            placeholder="Upload Face Photo" id="face-photo-name" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btnbg" type="button"
                                                onclick="document.getElementById('face-photo-upload').click();">
                                                Upload
                                            </button>
                                        </span>
                                    </div>
                                    <img id="face-photo-preview" src="" alt="Face Photo"
                                        style="max-width: 100px; display: none; border-radius: 8px;">
                                    <div class="error" id="face_photo-Error"></div>
                                    <small class="text-muted d-block mt-1">Upload a clear front-facing photo for face recognition check-in.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-end mt-4">
                            <button type="button" class="btn btn-secondary prev-step interviewsmbtn me-2" id="prev3"
                                style="display: none;">Previous</button>
                            <button type="submit" class="btn hr-btnbg submit-form interviewsmbtn" id="submitForm"
                                style="display: none;">Submit</button>
                        </div>
                    </div>
                </form>
                <div class="error" id="form_error" style="display: none;"></div>
                <div class="success text-success" id="form_success" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('file-upload').addEventListener('change', function (event) {
        let file = event.target.files[0];
        if (file) {
            document.getElementById('file-name').value = file.name; // Set file name in input field

            // Show preview
            let reader = new FileReader();
            reader.onload = function (e) {
                let imgPreview = document.getElementById('profile-preview');
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block'; // Show image preview
            };
            reader.readAsDataURL(file);
        }
    });

    // Face photo upload handler
    document.getElementById('face-photo-upload').addEventListener('change', function (event) {
        let file = event.target.files[0];
        if (file) {
            document.getElementById('face-photo-name').value = file.name;

            let reader = new FileReader();
            reader.onload = function (e) {
                let imgPreview = document.getElementById('face-photo-preview');
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Function to display existing image (for update case)
    function displayExistingImage(imageUrl) {
        if (imageUrl) {
            document.getElementById('profile-preview').src = imageUrl;
            document.getElementById('profile-preview').style.display = 'block';
        }
    }

    // Function to display existing face photo (for update case)
    function displayExistingFacePhoto(imageUrl) {
        if (imageUrl) {
            document.getElementById('face-photo-preview').src = imageUrl;
            document.getElementById('face-photo-preview').style.display = 'block';
        }
    }

    var existingImageUrl = ""; // Default to empty or set dynamically using PHP

    if (existingImageUrl) {
        displayExistingImage(existingImageUrl);
    }
</script>
<script>
    const token = localStorage.getItem('token'); // JWT token
    let currentStep = 1;
    let formData = new FormData();

    function showStep(step) {
        $('.step').removeClass('active');
        $('#step' + step).addClass('active');
        $('#next1, #next2, #prev2, #prev3, #submitForm').hide(); // Hide all buttons initially

        if (step === 1) {
            $('#next1').show();
        } else if (step === 2) {
            $('#prev2').show();
            $('#next2').show();
        } else if (step === 3) {
            $('#prev3').show();
            $('#submitForm').show();
        }
    }


    function updateProgress(step) {
        let progress = (step - 1) * 50;
        $('#progressBar').css('width', progress + '%');
    }

    function validateStep(step, callback) {
        let data = new FormData();
        data.append('current_step', step);
        let user_id = $('#id').val();
        if (user_id) {
            data.append('user_id', user_id);
        }

        if (step === 1) {
            console.log(user_id);

            if (user_id) {
                var fields = ['firstname', 'lastname', 'email', 'date_of_birth'];
            } else {
                var fields = ['firstname', 'lastname', 'email', 'password', 'date_of_birth'];
            }

            fields.forEach(field => {
                data.append(field, $('#' + field).val());
                formData.append(field, $('#' + field).val());

                let gender = $('input[name="gender"]:checked').val();
                if (!gender) {
                    callback(false);
                    return;
                } else {
                    data.append('gender', gender);
                    formData.append('gender', gender);
                }
            });
        } else if (step === 2) {
            let fields = ['address_1', 'address_2', 'state_id', 'postcode', 'city_id', 'country_id', 'contact_number', 'branch_id'];
            fields.forEach(field => {
                data.append(field, $('#' + field).val());
                formData.append(field, $('#' + field).val());
            });
            // Correct placement of country_id
            data.append('country_id', $('#country_id_main').val() || '');
            formData.append('country_id', $('#country_id_main').val() || '');
        } else if (step === 3) {
            let fields = ['employee_id', 'salary', 'designation_id', 'department_id', 'joining_date', 'working_location', 'role', 'remaining_paid_leave', 'remaining_sick_leave', 'status', 'last_working_day'];
            fields.forEach(field => {
                data.append(field, $('#' + field).val());
                formData.append(field, $('#' + field).val());
            });

            let fileInput = $('.profile_image')[0].files[0];
            if (fileInput) {
                data.append('profile_image', fileInput);
                formData.append('profile_image', fileInput);
            }
        }
        let csrfTokenName = '<?= csrf_token() ?>';
        let csrfTokenValue = $('#csrfToken').val();
        data.append(csrfTokenName, csrfTokenValue);

        $.ajax({
            url: '<?= base_url("api/employee/validateStep") ?>',
            type: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            data: data,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status) {
                    callback(true);
                } else {
                    $('.error').text('');
                    if (response.errors) {
                        for (let field in response.errors) {
                            $('#' + field + '-Error').text(response.errors[field]).show();
                            $('#' + field).addClass('has-error');
                        }
                    }
                    callback(false);
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'An error occurred during validation.',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'hr-btnbg',
                    }
                });
                callback(false);
            }
        });
    }


    $('#next1').click(function () {
        validateStep(1, function (isValid) {
            if (isValid) {
                currentStep++;
                showStep(currentStep);
                updateProgress(currentStep);
            }
        });
    });

    function fetchNextEmployeeId(callback) {
        if ($('#id').val()) return; // Don't overwrite in edit mode
        const currentVal = $('#employee_id').val();
        if (currentVal && currentVal.trim() !== '') {
            if (typeof callback === 'function') callback();
            return; // Don't overwrite what the user has already entered!
        }
        const token = localStorage.getItem('token');
        $.ajax({
            url: '<?= base_url("api/employee/lastEmployeeId") ?>',
            type: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            success: function (response) {
                if (response.status && response.formatted_id) {
                    if (!$('#employee_id').val() || $('#employee_id').val().trim() === '') {
                        $('#employee_id').val(response.formatted_id);
                        $('#employee_id_display').val(response.formatted_id);
                    }
                }
                if (typeof callback === 'function') callback(response);
            }
        });
    }

    $('#next2').click(function () {
        validateStep(2, function (isValid) {
            if (isValid) {
                currentStep++;
                showStep(currentStep);
                updateProgress(currentStep);
                fetchNextEmployeeId();
            }
        });
    });

    $('#prev2, #prev3').click(function () {
        currentStep--;
        showStep(currentStep);
        updateProgress(currentStep);
    });

    $(document).ready(function () {
        $(document).on('click', '#submitForm', function (e) {
            e.preventDefault();
            let myform = document.getElementById("multistepForm");

            if (myform) {
                // Remove EMP# prefix before FormData is created
                let rawEmpId = $('#employee_id').val();
                if (rawEmpId.startsWith('EMP#')) {
                    $('#employee_id_display').val($('#employee_id').val()); // Optional redundancy
                }
                let fd = new FormData(myform);
                let csrfTokenName = '<?= csrf_token() ?>';
                let csrfTokenValue = $('#csrfToken').val();
                fd.append(csrfTokenName, csrfTokenValue);

                const userId = $('#id').val();

                validateStep(3, function (isValid) {
                    if (isValid) {
                        $('#loader').show();

                        if (userId) {
                            // Update existing employee
                            $.ajax({
                                url: `<?= base_url("api/employee/update/") ?>${userId}`,
                                type: 'POST',
                                headers: {
                                    'Authorization': `Bearer ${token}`
                                },
                                data: fd,
                                contentType: false,
                                processData: false,
                                success: function (response) {
                                    $('#loader').hide();

                                    if (response.message) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Updated Successfully!',
                                            text: response.message,
                                            confirmButtonText: 'OK',
                                            buttonsStyling: false,
                                            customClass: {
                                                confirmButton: 'hr-btnbg',
                                            }
                                        }).then(() => {
                                            window.location.href = "/empview"; // Redirect after success
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Update Failed!',
                                            text: response.message || 'Error updating employee details.',
                                            confirmButtonText: 'OK',
                                            buttonsStyling: false,
                                            customClass: {
                                                confirmButton: 'hr-btnbg',
                                            }
                                        });
                                    }
                                },
                                error: function (xhr) {
                                    $('#loader').hide();
                                    let errorMsg = 'An error occurred during the update.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: errorMsg,
                                        confirmButtonText: 'OK',
                                        buttonsStyling: false,
                                        customClass: {
                                            confirmButton: 'hr-btnbg',
                                        }
                                    });
                                }
                            });
                        } else {
                            // Create new employee
                            $.ajax({
                                url: '<?= base_url("api/emp/create") ?>',
                                type: 'POST',
                                headers: {
                                    'Authorization': `Bearer ${token}`
                                },
                                data: fd,
                                contentType: false,
                                processData: false,
                                success: function (response) {
                                    $('#loader').hide();

                                    if (response.message) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: response.message,
                                            confirmButtonText: 'OK',
                                            buttonsStyling: false,
                                            customClass: {
                                                confirmButton: 'hr-btnbg',
                                            }
                                        }).then(() => {
                                            window.location.href = "/empview"; // Redirect after success
                                        });
                                    } else {
                                        if (response.errors) {
                                            $('.error').text('');
                                            for (let field in response.errors) {
                                                $('#' + field + '_error').text(response.errors[field]).show();
                                                $('#' + field).addClass('has-error');
                                            }
                                        }
                                    }
                                },
                                error: function (xhr) {
                                    $('#loader').hide();

                                    let response = xhr.responseJSON;
                                    if (response && response.messages) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: response.messages, // Display server error message
                                            confirmButtonText: 'OK',
                                            buttonsStyling: false,
                                            customClass: {
                                                confirmButton: 'hr-btnbg',
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: 'An error occurred during submission.',
                                            confirmButtonText: 'OK',
                                            buttonsStyling: false,
                                            customClass: {
                                                confirmButton: 'hr-btnbg',
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            } else {
                console.error('Form element not found');
            }
        });
    });

    $(document).ready(function () {
        showStep(currentStep);
    });

    $(document).ready(function () {
        const token = localStorage.getItem('token'); // JWT token from login
        const params = new URLSearchParams(window.location.search);
        let userId = params.get('id');

        // If not found, try extracting from the URL path
        if (!userId) {
            const pathParts = window.location.pathname.split('/');
            var temp_id = pathParts[pathParts.length - 1];
            if (!isNaN(temp_id) && !isNaN(parseFloat(temp_id))) {
                userId = temp_id;
            }
        }

        if (userId) {
            // ================= EDIT MODE =================
            $('h4.card-title').text('Edit Employee');
            $('.password-required-star').hide();
            $('#password').val('').attr('placeholder', 'Leave blank to keep current password');

            $.ajax({
                url: `<?= base_url("api/employee/") ?>${userId}`,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function (response) {
                    if (response.user && response.user_info) {
                        const user = response.user;
                        const userInfo = response.user_info;

                        // Populate form fields
                        $('#id').val(user.id);
                        $('#firstname').val(userInfo.firstname);
                        $('#lastname').val(userInfo.lastname);
                        $('#email').val(user.email);
                        $('#password').val(''); // Empty on edit
                        $('input[name="gender"][value="' + (userInfo.gender || 'male') + '"]').prop('checked', true);
                        $('#marital_status').val(userInfo.marital_status);
                        $('#date_of_birth').val(userInfo.date_of_birth);
                        $('#nationality').val(userInfo.nationality);
                        $('#address_1').val(userInfo.address_1);
                        $('#address_2').val(userInfo.address_2);
                        $('#state_id_main').val(userInfo.state_id);
                        $('#postcode').val(userInfo.postcode);
                        $('#city_id').val(userInfo.city_id);
                        $('#country_id_main').val(userInfo.country_id);
                        $('#contact_number').val(userInfo.contact_number);
                        $('#branch_id').val(user.branch_id);
                        $('#emergency_contact').val(userInfo.emergency_contact);

                        const empIdVal = userInfo.employee_id || ('EMP-' + String(user.id).padStart(3, '0'));
                        $('#employee_id').val(empIdVal);
                        $('#employee_id_display').val(empIdVal);

                        $('#designation_id').val(userInfo.designation_id);
                        $('#department_id').val(userInfo.department_id);
                        $('#joining_date').val(userInfo.joining_date);
                        $('#working_location').val(userInfo.working_location);
                        $('#emp_type').val(userInfo.emp_type);
                        $('#role').val(userInfo.role || 'employee');
                        $('#salary').val(userInfo.salary);
                        $('#remaining_paid_leave').val(userInfo.remaining_paid_leave);
                        $('#remaining_sick_leave').val(userInfo.remaining_sick_leave);

                        if (userInfo.profile_image) {
                            $('.profile_image').attr('src', userInfo.profile_image).show();
                        } else {
                            $('.profile_image').hide();
                        }

                        // Display face photo if exists
                        if (userInfo.face_photo) {
                            displayExistingFacePhoto(userInfo.face_photo);
                        }

                        // Set status and last_working_day
                        let empStatus = (userInfo.status || 'Active').trim();
                        empStatus = empStatus.charAt(0).toUpperCase() + empStatus.slice(1).toLowerCase();
                        if (!['Active', 'Inactive', 'Resigned'].includes(empStatus)) {
                            empStatus = 'Active';
                        }
                        $('#status').val(empStatus);
                        $('#last_working_day').val(userInfo.last_working_day || '');

                        showStep(1);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: response.message || 'Employee data not found',
                            confirmButtonText: 'OK',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',
                            }
                        });
                    }
                },
                error: function () {
                    $('#form_error').text('Error fetching employee data.').show();
                }
            });
        } else {
            // ================= CREATE MODE =================
            $('h4.card-title').text('Add Employee');
            $('.password-required-star').show();
            $('#password').val('').attr('placeholder', 'Enter a secure password');

            // Fetch the last employee ID for new employee
            $.ajax({
                url: '<?= base_url("api/employee/lastEmployeeId") ?>',
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                success: function (response) {
                    if (response.status) {
                        let formattedId = response.formatted_id || (response.next_employee_id ? `EMP-${String(response.next_employee_id).padStart(3, '0')}` : 'EMP-001');

                        if (!$('#employee_id').val() || $('#employee_id').val().trim() === '') {
                            $('#employee_id').val(formattedId);
                            $('#employee_id_display').val(formattedId);
                        }
                    }
                }
            });
            showStep(1);
        }
    });


    $(document).ready(function () {
        $("#countryForm").submit(function (e) {
            e.preventDefault();

            const countryName = $("#country_name").val().trim();
            const errorContainer = $("#country_name_error");
            errorContainer.text("");

            if (countryName === "") {
                errorContainer.text("Please enter a country name.");
                return;
            }

            $.ajax({
                url: "<?= base_url("api/add-country") ?>",
                type: "POST",
                data: {
                    country_name: countryName
                },
                headers: {
                    Authorization: "Bearer " + token
                },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Country added successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $("#countryForm")[0].reset();
                        $("#addCountryModal").modal("hide");

                        $("#country_id_main").append(
                            `<option value="${response.country.id}" selected>${response.country.country_name}</option>`
                        );
                        $("#countries_id_modal").append(
                            `<option value="${response.country.id}">${response.country.country_name}</option>`
                        );
                    } else {
                        // Ã°Å¸â€Â¥ Handle duplicate error or other custom message
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: response.message || "Failed to add country."
                        });
                    }
                },
                error: function (xhr) {
                    let res = xhr.responseJSON;
                    if (res?.errors?.country_name) {
                        errorContainer.text(res.errors.country_name);
                    } else if (res?.message) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.message
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Something went wrong. Please try again."
                        });
                    }
                }
            });
        });
    });

    $(document).ready(function () {
        $("#stateForm").submit(function (e) {
            e.preventDefault();

            let stateName = $("#state_name").val().trim();
            let errorContainer = $("#state_name_error");
            errorContainer.text("");

            if (stateName === "") {
                errorContainer.text("Please enter a state name.");
                return;
            }

            $.ajax({
                url: "<?= base_url("api/add-state") ?>",
                type: "POST",
                data: {
                    state_name: stateName
                },
                headers: {
                    Authorization: "Bearer " + token
                },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "State added successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $("#addStateModal").modal("hide");
                        $("#stateForm")[0].reset();
                        errorContainer.text("");

                        $("#state_id_main, #state_id_modal").append(
                            `<option value="${response.country.id}" selected>${response.country.state_name}</option>`
                        );
                    } else {
                        // Ã°Å¸â€˜â€¡ Show duplicate or custom error message
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: response.message || "Failed to add state."
                        });
                    }
                },
                error: function (xhr) {
                    const res = xhr.responseJSON;
                    if (res?.errors?.state_name) {
                        errorContainer.text(res.errors.state_name);
                    } else if (res?.message) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.message
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "An unexpected error occurred."
                        });
                    }
                }
            });
        });
    });

    $(document).ready(function () {
        $("#cityForm").submit(function (e) {
            e.preventDefault();

            let cityName = $("#city_name").val().trim();
            let countryId = $("#countries_id_modal").val();
            let cityError = $("#city_name_error");
            let countryError = $("#country_id_error");

            // Reset previous errors
            cityError.text("");
            countryError.text("");

            let isValid = true;

            if (cityName === "") {
                cityError.text("Please enter a city name.");
                isValid = false;
            }
            if (countryId === "") {
                countryError.text("Please select a country.");
                isValid = false;
            }

            if (!isValid) return;

            $.ajax({
                url: "<?= base_url("api/add-city") ?>",
                type: "POST",
                data: {
                    city_name: cityName,
                    country_id: countryId
                },
                headers: {
                    Authorization: "Bearer " + token // if required
                },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "City added successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Reset form and close modal
                        $("#cityForm")[0].reset();
                        $("#addCityModal").modal("hide");

                        // Add new city to dropdown
                        $("#city_id").append(
                            `<option value="${response.city.id}" selected>${response.city.city_name}</option>`
                        );
                    } else {
                        // Ã°Å¸â€˜â€¡ Handle duplicate or general error message
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: response.message || "Failed to add city."
                        });
                    }
                },
                error: function (xhr) {
                    const res = xhr.responseJSON;
                    if (res?.errors?.city_name) {
                        $("#city_name_error").text(res.errors.city_name);
                    }
                    if (res?.errors?.country_id) {
                        $("#country_id_error").text(res.errors.country_id);
                    }
                    if (res?.message) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.message
                        });
                    }
                }
            });
        });
    });

    $(document).ready(function () {
        $('#adddepartementModal').on('show.bs.modal', function () {
            let mainBranch = $('#branch_id').val();
            if (mainBranch && $('#dept_modal_branch_id').is('select')) {
                $('#dept_modal_branch_id').val(mainBranch);
            }
        });

        $("#departmentForm").submit(function (e) {
            e.preventDefault();

            $('#department_name_error').text('');
            $('#dept_branch_error').text('');

            let departmentName = $("#department_name").val().trim();
            let branchId = $("#dept_modal_branch_id").length ? $("#dept_modal_branch_id").val() : null;

            if (departmentName === "") {
                $('#department_name_error').text('Department Name is required.');
                return;
            }

            // For admin/hr: branch is required
            <?php if (in_array($currentUserRole ?? session()->get('role') ?? '', ['admin', 'hr'])): ?>
            if (!branchId) {
                $('#dept_branch_error').text('Please select a branch.');
                return;
            }
            <?php endif; ?>

            let formData = $(this).serialize();

            const empDeptToken = localStorage.getItem('token');
            $.ajax({
                url: "<?= base_url("api/department/add") ?>",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: { 'Authorization': 'Bearer ' + empDeptToken },
                success: function (response) {
                    $('#department_name_error').text('');
                    $('#dept_branch_error').text('');

                    if (response.success) {
                        let newOption = `<option value="${response.department.id}" selected>${response.department.department_name}</option>`;
                        $("#department_id").append(newOption);
                        // Remove previous selected and select new
                        $("#department_id option").removeAttr("selected");
                        $("#department_id").val(response.department.id);

                        // Also update edit modal department dropdown if present
                        if ($("#department_id_modal").length) {
                            $("#department_id_modal").append(newOption);
                        }

                        $("#department_name").val('');
                        if ($('#dept_modal_branch_id').is('select')) {
                            $('#dept_modal_branch_id').val('');
                        }
                        $("#adddepartementModal").modal("hide");

                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Department added successfully.",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: response.message,
                            showConfirmButton: true
                        });
                    }
                },
                error: function (xhr, status, error) {
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

    $("#designationForm").submit(function (e) {
        e.preventDefault();
        var formData = $(this).serialize();

        // Clear previous errors
        $(".text-danger").text("");

        $.ajax({
            url: "<?= base_url("api/designation/add") ?>",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $("#designation_id").append(
                        `<option value="${response.designation.id}" selected>
                        ${response.designation.designation_name}
                    </option>`
                    );

                    $("#designationForm")[0].reset();
                    $("#addDesignationModal").modal("hide");

                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Designation added successfully.",
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else if (response.errors) {
                    // Show field-specific errors
                    if (response.errors.department_id) {
                        $("#departmentError").text(response.errors.department_id);
                    }
                    if (response.errors.designation_name) {
                        $("#designationError").text(response.errors.designation_name);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
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

    $(document).ready(function () {
        const token = localStorage.getItem('token');

        // Check if we're creating a new employee (not editing)
        const params = new URLSearchParams(window.location.search);
        let userId = params.get('id');

        if (!userId) {
            const pathParts = window.location.pathname.split('/');
            var temp_id = pathParts[pathParts.length - 1];
            if (!isNaN(temp_id) && !isNaN(parseFloat(temp_id))) {
                userId = temp_id;
            }

        }

        // Only auto-populate for new employees
        if (!userId) {
            autoPopulateLocationAndDate();
            const today = new Date().toISOString().split('T')[0];
            $('#joining_date').val(today);
        }
    });

    function autoPopulateLocationAndDate() {
        const token = localStorage.getItem('token');

        $.ajax({
            url: '<?= base_url("api/location/detect") ?>',
            type: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            success: function (response) {
                if (response.status && response.data) {
                    const data = response.data;

                    // Set country, state, and city if found
                    if (data.matched_ids.country_id) {
                        $('#country_id_main').val(data.matched_ids.country_id).trigger('change');
                    }

                    if (data.matched_ids.state_id) {
                        $('#state_id_main').val(data.matched_ids.state_id).trigger('change');
                    }

                    if (data.matched_ids.city_id) {
                        $('#city_id').val(data.matched_ids.city_id).trigger('change');
                    }

                    // Show success notification
                    if (data.matched_ids.country_id || data.matched_ids.state_id || data.matched_ids.city_id) {
                    }
                }
            },
            error: function (xhr) {
                console.error('Location detection failed:', xhr);
            }
        });
    }
</script>

<?= $this->endSection() ?>
