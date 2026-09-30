<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>

<link rel="stylesheet" href="assets/css/profile.css">
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
                            <input type="text" class="form-control" name="city_name" id="city_name" placeholder="Enter city Name" />

                        </div>
                        <small id="city_name_error" class="text-danger"></small> <!-- Error message container -->
                    </div>
                    <div class="mb-3">
                        <label for="address_details" class="form-label">Country Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-earth fs-5"></i></span>
                            </div>
                            <select class="form-select" name="country_id" id="countries_id_modal">
                                <option value="">Select your country</option>
                                <?php foreach ($countries as $country) : ?>
                                    <option value="<?= $country['id']; ?>"><?= $country['country_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <small id="country_id_error" class="text-danger"></small> <!-- Error message container -->
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="   ">Submit</button>
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
                            <input type="text" id="country_name" name="country_name" class="form-control" placeholder="Enter Country Name" />
                        </div>
                        <small id="country_name_error" class="text-danger"></small> <!-- Error message container -->
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="submitContry">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- designation -->
<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
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
                                <?php foreach ($departments as $department) : ?>
                                    <option value="<?= $department['id']; ?>"><?= $department['department_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address_details" class="form-label">Designation Name</label>
                        <div class="input-group">

                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="designation_name" id="designation_name" placeholder="Enter Designation Name" />

                        </div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="submitBtnDesignation">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- departement -->
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
                        $curUserRole = $role ?? session()->get('role') ?? '';
                        $isAdminOrHr = in_array($curUserRole, ['admin', 'hr']);
                        $isBranchAdminRole = ($curUserRole === 'branch_admin');
                        $userBranchId = session()->get('branch_id') ?? '';
                        $branchesList = (new \App\Models\BranchModel())->getActiveBranches();
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
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="submitBtnDepartment">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-10 m-auto">
        <div class="card">
            <img src="<?= getCompanyLogo(); ?>" class="position-absolute top-0 end-0 rounded-start-2 profile-logo" alt="Company Logo" style="width: 70px; height: 70px; border: 1px solid #eeeeee;">

            <div class="card-body pb-0">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="text-center border-end">
                            <img src="<?= getDefaultProfileImage() ?>" class="img-fluid avatar-xxl rounded-circle" alt="Profile Image" id="profile-image" style="width: 160px; height: 160px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= getDefaultProfileImage() ?>';">
                            <!-- <h4 class="font-size-20 mt-3 mb-2" style="color: #E66136;" id="profile-name">Jansh Wells</h4> -->
                            <!-- <h5 class="text-muted font-size-13 mb-0 mt-2" id="profile-role">Web Designer</h5> -->
                        </div>
                    </div><!-- end col -->

                    <div class="col-md-9 position-relative">
                        <!-- Small Company Logo -->
                        <div class="ms-3">
                            <div class="row my-4">
                                <div class="col-md-12">
                                    <div>
                                        <p class="mb-2 text-muted fw-bold fs-5" id="profile-bio"></p>
                                        <h6 class="text-muted font-size-13 mb-2 mt-2" id="profile-role">Web Designer</h6>

                                        <p class="text-muted mb-2 fw-medium">
                                            <i class="mdi mdi-email-outline me-2"></i><span id="profile-email">john@example.com</span>
                                        </p>
                                        <p class="text-muted fw-medium mb-0">
                                            <i class="mdi mdi-phone-in-talk-outline me-2"></i><span id="profile-phone">418-955-4703</span>
                                        </p>
                                    </div>
                                </div><!-- end col -->
                            </div><!-- end row -->

                            <!-- <ul class="nav nav-tabs nav-tabs-custom border-bottom-0 mt-3 w-100">
                                <li class="nav-item w-25">
                                    <a class="nav-link active text-dark fw-semibold" data-bs-toggle="tab" href="#projects-tab">
                                        <i class="fas fa-info-circle me-1"></i> Profile Details
                                    </a>
                                </li>
                                <li class="nav-item w-25">
                                    <a class="nav-link text-dark fw-semibold" data-bs-toggle="tab" href="#profile-tab">
                                        <i class="mdi mdi-account-edit me-1"></i> Edit Profile
                                    </a>
                                </li>
                            </ul> -->
                            <ul class="nav nav-tabs nav-tabs-custom border-bottom-0 mt-3 w-100">
                                <li class="nav-item w-50 text-center">
                                    <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#projects-tab">
                                        <i class="mdi mdi-account-circle me-1"></i> Profile Details
                                    </a>
                                </li>
                                <li class="nav-item w-50 text-center">
                                    <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#profile-tab">
                                        <i class="mdi mdi-account-edit me-1"></i> Edit Profile
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- end card body -->

        </div><!-- end card -->

        <div class="card">
            <div class="tab-content p-4">
                <div class="tab-pane fade show active" id="projects-tab" role="tabpanel">
                    <div class="row">
                        <!-- Personal Details -->
                        <div class="col-xl-12">
                            <div class="card border rounded">
                                <div class="card-header text-dark p-3">
                                    <h4 class="mb-0">
                                        <i class="mdi mdi-account-circle me-2"></i>Personal Details
                                    </h4>
                                </div>

                                <div class="card-body">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="tdfont">
                                                    <i class="mdi mdi-account-outline me-1 mdicon"></i> First Name
                                                </td>

                                                <td id="firstname1"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i> Last Name</td>
                                                <td id="lastname2"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-gender-male-female me-1 mdicon"></i> Gender</td>
                                                <td id="gender1"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-cake-variant-outline me-1 mdicon"></i> Date Of Birth</td>
                                                <td id="date_of_birth1"></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Address & Contacts -->
                        <div class="col-xl-12">
                            <div class="card border rounded">
                                <div class="card-header text-dark p-3">
                                    <h4 class="mb-0">
                                        <i class="mdi mdi-home-map-marker me-2"></i>Address & Contacts
                                    </h4>
                                </div>

                                <div class="card-body">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-map-marker-outline me-1 mdicon"></i> Address 1</td>
                                                <td id="address1"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-map-marker me-1 mdicon"></i> Address 2</td>
                                                <td id="address2"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-flag-outline me-1 mdicon"></i> Country</td>
                                                <td id="country"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-city me-1 mdicon"></i> City</td>
                                                <td id="city"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-map me-1 mdicon"></i> State</td>
                                                <td id="state1"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-phone me-1 mdicon"></i> Contact Number</td>
                                                <td id="contact_number1"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-mailbox me-1 mdicon"></i> Postcode</td>
                                                <td id="postcode1"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php if ($role !== 'admin') : ?>
                            <!-- Job Details -->
                            <div class="col-xl-12">
                                <div class="card border rounded">
                                    <div class="card-header text-dark p-3">
                                        <h4 class="mb-0">
                                            <i class="mdi mdi-briefcase-outline me-2"></i>Job Details
                                        </h4>
                                    </div>

                                    <div class="card-body">
                                        <table class="table mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-briefcase-outline me-1 mdicon"></i> Designation</td>
                                                    <td id="designation"></td>
                                                </tr>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-office-building-outline me-1 mdicon"></i> Department</td>
                                                    <td id="department"></td>
                                                </tr>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-calendar-account-outline me-1 mdicon"></i> Joining Date</td>
                                                    <td id="joining_date"></td>
                                                </tr>
                                                <!-- <tr>
                                                <th scope="row"></th>
                                                <td id="employment_type1"></td>
                                            </tr> -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($role === 'admin') : ?>

                            <!-- Company Details -->
                            <div class="col-xl-12 mt-4">
                                <div class="card border rounded">
                                    <div class="card-header text-dark p-3">
                                        <h4 class="mb-0">
                                            <i class="mdi mdi-domain me-2"></i>Company Details
                                        </h4>
                                    </div>

                                    <div class="card-body">
                                        <table class="table mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-domain me-1 mdicon"></i> Company Name</td>
                                                    <td class="company_name"></td>
                                                </tr>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-map-marker me-1 mdicon"></i> Address</td>
                                                    <td class="company_address"></td>
                                                </tr>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-phone me-1 mdicon"></i> Phone</td>
                                                    <td class="company_phone"></td>
                                                </tr>
                                                <tr>
                                                    <td class="tdfont"><i class="mdi mdi-email-outline me-1 mdicon"></i> Email</td>
                                                    <td class="company_email"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="tab-pane fade" id="profile-tab" role="tabpanel">
                    <h4 class="card-title mb-4">Edit Profile</h4>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="task-list-box" id="landing-task">
                                <form method="POST" enctype="multipart/form-data" id="UpdateForm">
                                    <input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="name">First Name</label>
                                                <input type="text" name="firstname" id="firstname" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="name">Last Name</label>
                                                <input type="text" name="lastname" id="lastname" class="form-control" value="">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" name="email" id="email" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Gender</label><br>
                                                <label>
                                                    <input type="radio" name="gender" value="male"> Male
                                                </label>
                                                <label>
                                                    <input type="radio" name="gender" value="female"> Female
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text" name="contact_number" id="contact_number" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="address_1">Address1</label>
                                                <textarea name="address_1" id="address_1" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 mt-2">
                                            <div class="form-group">
                                                <label for="address_2">Address2</label>
                                                <textarea name="address_2" id="address_2" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                                    <label>country</label>
                                                    <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded-pill addbtn-white" style="background-color: #E66136;font-size:11px" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                                                        <i class="mdi mdi-plus"></i> ADD COUNTRY
                                                    </button>
                                                </div>

                                                <select class="form-select" name="country_id" id="country_id_main">
                                                    <option disabled>Select your country</option>
                                                    <?php foreach ($countries as $country) : ?>
                                                        <option value="<?= $country['id']; ?>"><?= $country['country_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 mt-2">
                                            <div class="form-group">
                                                <label for="address">State</label>
                                                <input type="text" name="state" id="state" class="form-control" value="">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                                    <label>City</label>
                                                    <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded-pill addbtn-white" style="background-color: #E66136;font-size:11px" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                                        <i class="mdi mdi-plus"></i> ADD CITY
                                                    </button>
                                                </div>

                                                <select class="form-select" name="city_id" id="city_id">
                                                    <option value="" disabled>Select your city</option>
                                                    <?php foreach ($cities as $city) : ?>
                                                        <option value="<?= $city['id']; ?>" <?= (isset($user['city_id']) && $user['city_id'] == $city['id']) ? 'selected' : ''; ?>>
                                                            <?= $city['city_name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="date_of_birth">Date of birth</label>
                                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" placeholder="Enter your birth date" value="" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="postcode">Postcode</label>
                                                <input type="text" class="form-control" name="postcode" id="postcode" placeholder="Enter your postcode/ZIP code" value="" />
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($role !== 'admin') : ?>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                                        <label>Department</label>
                                                        <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded-pill addbtn-white" style="background-color: #E66136;font-size:11px" data-bs-toggle="modal" data-bs-target="#adddepartementModal">
                                                            <i class="mdi mdi-plus"></i> ADD DEPARTMENT
                                                        </button>
                                                    </div>

                                                    <select class="form-select" id="department_id" name="department_id">
                                                        <option value="" disabled>Select Department</option>
                                                        <?php foreach ($departments as $department) : ?>
                                                            <option value="<?= $department['id']; ?>"><?= $department['department_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                                        <label>Designation</label>
                                                        <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded-pill addbtn-white" style="background-color: #E66136;font-size:11px" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
                                                            <i class="mdi mdi-plus"></i> ADD DESIGNATION
                                                        </button>
                                                    </div>

                                                    <select class="form-select" name="designation_id" id="designation_id">
                                                        <option value="" disabled>Select Designation</option>
                                                        <?php foreach ($designations as $designation) : ?>
                                                            <option value="<?= $designation['id']; ?>"><?= $designation['designation_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="profile_image">Profile Image</label>
                                        <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    </div>
                                    <div class="">
                                        <img id="profile-image-preview" src="<?= getDefaultProfileImage() ?>" alt="Profile Image" class="img-fluid mb-2 rounded-circle" width="80px" height="80px" style="object-fit: cover;" onerror="this.onerror=null; this.src='<?= getDefaultProfileImage() ?>';">
                                    </div>
                                    <?php if ($role === 'admin') : ?>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="company_name">Company Name</label>
                                                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Enter your company name" value="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="logo_img">Upload Logo</label>
                                                    <input type="file" class="form-control" name="logo_img" id="logo_img" accept="image/*">
                                                </div>
                                                <div class="mt-2">
                                                    <img id="company_logo" src="<?= base_url('upload/fab_logo.jpg') ?>" alt="Company Logo" class="img-fluid view-logo" width="150">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">

                                            <!-- New Fields Start -->
                                            <div class="col-lg-6 mt-3">
                                                <div class="form-group">
                                                    <label for="company_address">Company Address</label>
                                                    <textarea name="company_address" id="company_address" class="form-control" rows="3" placeholder="Enter company address"></textarea>
                                                </div>
                                            </div>

                                            <div class="col-lg-6 mt-3">
                                                <div class="form-group">
                                                    <label for="company_phone">Company Phone</label>
                                                    <input type="text" name="company_phone" id="company_phone" class="form-control" placeholder="Enter company phone number" value="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="col-lg-6 mt-3">
                                                <div class="form-group">
                                                    <label for="company_email">Company Email</label>
                                                    <input type="email" name="company_email" id="company_email" class="form-control" placeholder="Enter company email" value="">
                                                </div>
                                            </div>
                                            <!-- New Fields End -->
                                        </div>

                                    <?php endif; ?>

                                    <div class="form-group">
                                        <button type="submit" class="btn hr-btnbg">Update Profile</button>
                                    </div>
                                    <div id="responseMessage"></div>
                                </form>
                            </div><!-- end -->
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div><!-- end tab pane -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- end tab pane -->
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $("#countryForm").submit(function(e) {
            e.preventDefault();
            let countryName = $("#country_name").val();
            let errorContainer = $("#country_name_error");

            // Reset error message
            errorContainer.text("");

            if (countryName === "") {
                errorContainer.text("Please enter a country name.").css("color", "red");
                return;
            }

            $.ajax({
                url: "<?= base_url('api/add-country') ?>",
                type: "POST",
                data: {
                    country_name: countryName
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        // Show SweetAlert for success
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Country added successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $("#addCountryModal").modal("hide");
                        $("#country_name").val("");

                        // Clear existing dropdown and add only the new country
                        $("#country_id_main, #countries_id_modal").html(
                            `<option value="${response.country.id}">${response.country.country_name}</option>`
                        );
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: "Failed to add country."
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error adding country."
                    });
                },
            });
        });
    });

    $(document).ready(function() {
        $("#cityForm").submit(function(e) {
            e.preventDefault();
            let cityName = $("#city_name").val();
            let countryId = $("#countries_id_modal").val();
            let cityError = $("#city_name_error");
            let countryError = $("#country_id_error");

            // Reset error messages
            cityError.text("");
            countryError.text("");

            let isValid = true;

            if (cityName === "") {
                cityError.text("Please enter a city name.").css("color", "red");
                isValid = false;
            }
            if (countryId === "") {
                countryError.text("Please select a country.").css("color", "red");
                isValid = false;
            }

            if (!isValid) return;

            $.ajax({
                url: "<?= base_url('api/add-city') ?>",
                type: "POST",
                data: {
                    city_name: cityName,
                    country_id: countryId
                },
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        // Show SweetAlert success message
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "City added successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $("#addCityModal").modal("hide");
                        $("#city_name").val("");

                        // Clear existing dropdown and add only the new city
                        $("#city_id").html(
                            `<option value="${response.city.id}">${response.city.city_name}</option>`
                        );
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: "Failed to add city."
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error adding city."
                    });
                },
            });
        });
    });

    $(document).ready(function() {
        $("#departmentForm").submit(function(e) {
            e.preventDefault(); // Prevent form submission

            $('#dept_branch_error').text('');

            <?php if ($isAdminOrHr): ?>
            if (!$("#dept_modal_branch_id").val()) {
                $('#dept_branch_error').text('Please select a branch.');
                return;
            }
            <?php endif; ?>

            var formData = $(this).serialize();

            const profilesDeptToken = localStorage.getItem('token');
            let reqHeaders = {};
            if (profilesDeptToken && profilesDeptToken !== 'null' && profilesDeptToken !== 'undefined') {
                reqHeaders['Authorization'] = 'Bearer ' + profilesDeptToken;
            }

            $.ajax({
                url: "<?= base_url('api/department/add'); ?>", // API for adding department
                type: "POST",
                headers: reqHeaders,
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Append to department dropdowns in both the main form and the add designation modal
                        var newOption = `<option value="${response.department.id}" selected>${response.department.department_name}</option>`;

                        $("#department_id").append(newOption); // Update department dropdown in main form
                        $("#department_id_modal").append(newOption); // Update department dropdown in designation modal

                        // Reset form and close modal
                        $("#departmentForm")[0].reset();
                        $("#adddepartementModal").modal("hide");

                        // Success message
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
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + error);
                }
            });
        });
    });

    $(document).ready(function() {
        $("#designationForm").submit(function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "<?= base_url('api/designation/add'); ?>", // API for adding designation
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Append to designation dropdown
                        $("#designation_id").append(
                            `<option value="${response.designation.id}" selected>
                            ${response.designation.designation_name}
                        </option>`
                        );

                        // Reset form and close modal
                        $("#designationForm")[0].reset();
                        $("#addDesignationModal").modal("hide");

                        // Success message
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Designation added successfully.",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: " + error);
                }
            });
        });
    });

    $(document).ready(function() {
        // Fetch user profile data using AJAX
        $.ajax({
            url: 'api/profile', // Ensure this is the correct URL for your API
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Check if the response has the 'data' property and it contains the profile info
                if (data.status === 'success' && data.data) {
                    // Helper function to set default value
                    function getValue(field) {
                        return field && field.trim() !== '' ? field : 'N/A';
                    }

                    // Update the profile page with the fetched data
                    $('#profile-name').text(getValue(data.data.firstname));
                    $('#profile-role').text(getValue(data.data.role));
                    $('#profile-email').text(getValue(data.data.email));
                    $('#profile-phone').text(getValue(data.data.contact_number));
                    // $('#profile-bio').text(getValue(data.data.firstname) + ' ' + getValue(data.data.lastname));
                    $('#profile-bio').text(getValue(data.data.firstname));
                    $('#firstname1').text(getValue(data.data.firstname));
                    $('#lastname2').text(getValue(data.data.lastname));
                    $('#address1').text(getValue(data.data.address_1));
                    $('#address2').text(getValue(data.data.address_2));
                    $('#gender1').text(getValue(data.data.gender));
                    $('#date_of_birth1').text(getValue(data.data.date_of_birth));
                    $('#country').text(getValue(data.data.country_name));
                    $('#city').text(getValue(data.data.city_name));
                    $('#state1').text(getValue(data.data.state));
                    $('#contact_number1').text(getValue(data.data.contact_number));
                    $('#postcode1').text(getValue(data.data.postcode));
                    $('#designation').text(getValue(data.data.designation_name));
                    $('#department').text(getValue(data.data.department_name));
                    $('#joining_date').text(getValue(data.data.joining_date));
                    $('.company_name').text(getValue(data.data.company_name));
                    $('.company_address').text(getValue(data.data.company_address));
                    $('.company_phone').text(getValue(data.data.company_phone));
                    $('.company_email').text(getValue(data.data.company_email));

                    // Update company logo
                    if (data.logo_img) {
                        $('#company_logo').attr('src', 'upload/' + response.data.logo_img);
                    } else {
                        $('#company_logo').attr('src', 'upload/fab_logo.jpg'); // Default fallback
                    }

                    // Update profile image if present
                    if (data.data.profile_image && data.data.profile_image !== '1789966027_54c5a38ccda20f7c2bac.jpg' && data.data.profile_image !== 'default_avatar.png') {
                        $('#profile-image').attr('src', 'upload/' + data.data.profile_image);
                    } else {
                        $('#profile-image').attr('src', '<?= getDefaultProfileImage() ?>');
                    }
                } else {
                    console.error('Error: Missing profile data');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching profile data: ", error);
            }
        });
    });

    $(document).ready(function() {
        // Fetch user profile data using AJAX
        $.ajax({
            url: 'api/editprofile', // Your API endpoint for fetching profile
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    // Populate form fields
                    $('#firstname').val(data.firstname);
                    $('#lastname').val(data.lastname);
                    $('#email').val(data.email);
                    $('#contact_number').val(data.contact_number);
                    $('#address_1').val(data.address_1);
                    $('input[name="gender"][value="' + data.gender + '"]').prop('checked', true);
                    $('#date_of_birth').val(data.date_of_birth);
                    $('#address_2').val(data.address_2);
                    $('#state').val(data.state);
                    $('#postcode').val(data.postcode);
                    $('#city_id').val(data.city_id);
                    $('#country_id').val(data.country_id);
                    $('#designation_id').val(data.designation_id);
                    $('#department_id').val(data.department_id);
                    $('#company_name').val(data.company_name);
                    $('#company_address').val(data.company_address);
                    $('#company_email').val(data.company_email);
                    $('#company_phone').val(data.company_phone);
                    $('#company_name').val(data.company_name);

                    let baseUrl = "<?= base_url(); ?>";

                    // Update company logo
                    if (data.logo_img) {
                        $('#company_logo').attr('src', baseUrl + '/upload/' + data.logo_img);
                    } else {
                        $('#company_logo').attr('src', baseUrl + '/upload/fab_logo.jpg'); // Fallback image
                    }

                    // Update profile image preview
                    if (data.profile_image && data.profile_image !== '1789966027_54c5a38ccda20f7c2bac.jpg' && data.profile_image !== 'default_avatar.png') {
                        $('#profile-image-preview').attr('src', baseUrl + '/upload/' + data.profile_image);
                    } else {
                        $('#profile-image-preview').attr('src', '<?= getDefaultProfileImage() ?>'); // Fallback image
                    }
                } else {
                    console.error('Error fetching profile data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    function fetchCompanyLogo() {
        $.ajax({
            url: 'api/getCompanyLogo', // API endpoint to get latest logo
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Dynamically update logo across all pages
                    $('.sidebar-logo, .navbar-logo, .login-logo, .offer-letter-logo, .profile-logo, .title-logo , .view-logo, .forgot-logo, .reset-logo, .welcome-mail-logo')
                        .attr('src', response.logo_img);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching company logo:', error);
            }
        });
    }

    // Call function on page load
    $(document).ready(function() {
        fetchCompanyLogo();
    });

    $('#logo_img').change(function(event) {
        let formData = new FormData();
        formData.append('logo_img', $('#logo_img')[0].files[0]);

        $.ajax({
            url: 'api/updateLogo',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let timestamp = new Date().getTime();
                    $('.sidebar-logo, .navbar-logo, .login-logo, .offer-letter-logo, .profile-logo, .title-logo, .view-logo, .forgot-logo, .reset-logo, .welcome-mail-logo')
                        .attr('src', response.logo_img + "?t=" + timestamp); // Prevent cache issue

                    Swal.fire({
                        icon: 'success',
                        title: 'Logo Updated!',
                        text: 'Company logo updated successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed!',
                        text: response.message || 'Error updating logo. Try again.',
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = "Failed to update logo. Please try again later.";
                if (xhr.responseJSON && xhr.responseJSON.messages) {
                    errorMessage = Object.values(xhr.responseJSON.messages).join("\n");
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                });
            }
        });
    });

    $(document).ready(function() {
        $("#UpdateForm").submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            $(".error-message").remove(); // Remove old error messages

            const formData = new FormData(this); // Collect form data including files
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            $('#loader').show();

            $.ajax({
                url: 'api/profile/update', // API endpoint for updating profile
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    $('#loader').hide();

                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile Updated!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $("#navbarSection").load(location.href + " #navbarSection"); // Reload only the navbar
                            fetchCompanyLogo(); // Fetch latest logo after profile update
                            location.reload(); // Refresh page if necessary
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed!',
                            text: response.message,
                        });
                    }
                },
                error: function(response) {
                    $('#loader').hide();

                    let responseJSON = response.responseJSON;

                    $('.error-message').remove(); // Clear old error messages

                    if (responseJSON && responseJSON.messages) {
                        // Check if it's a validation error response
                        if (responseJSON.messages.errors) {
                            let errorMessages = responseJSON.messages.errors;

                            if (errorMessages.firstname) {
                                $("#firstname").after(`<div class="text-danger error-message">${errorMessages.firstname}</div>`);
                            }

                            if (errorMessages.email) {
                                $("#email").after(`<div class="text-danger error-message">${errorMessages.email}</div>`);
                            }

                            if (errorMessages.company_name) {
                                $("#company_name").after(`<div class="text-danger error-message">${errorMessages.company_name}</div>`);
                            }

                            if (errorMessages.profile_image) {
                                $("#logo_img").after(`<div class="text-danger error-message">${errorMessages.profile_image}</div>`);
                            }

                            if (errorMessages.company_address) {
                                $("#company_address").after(`<div class="text-danger error-message">${errorMessages.company_address}</div>`);
                            }

                            if (errorMessages.company_phone) {
                                $("#company_phone").after(`<div class="text-danger error-message">${errorMessages.company_phone}</div>`);
                            }

                            if (errorMessages.company_email) {
                                $("#company_email").after(`<div class="text-danger error-message">${errorMessages.company_email}</div>`);
                            }
                        } else if (responseJSON.messages.error) {
                            // Show general API error like "User info not found for update"
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: responseJSON.messages.error,
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unexpected Error!',
                            text: 'Something went wrong. Please try again.',
                        });
                    }
                }

            });
        });
    });
</script>

<?= $this->endSection(); ?>
