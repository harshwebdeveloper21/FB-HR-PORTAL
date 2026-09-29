<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>

<style>
    .capitalize-text {
        text-transform: capitalize;
    }

    body {
        background-color: #f8f9fa;
    }

    .profile-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .info-label {
        font-weight: 500;
        color: #555;
        margin-right: 5px;
    }

    .tab-content {
        padding-top: 20px;
        background-color: white;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 500;
    }

    .card-value {
        font-size: 0.95rem;
        color: #555;
    }

    .nav-tabs .nav-link:hover {
        background: #E66136;
        color: white;
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        background: #E66136;
        color: white;
    }

    .taskcard {
        background-color: #f4f5f7;
    }

    .lg-card-margin {
        margin-bottom: 10px;
    }

    .sm-profile-margin {
        margin-top: 0px !important;
    }

    @media (max-width: 767px) {
        .interviewsmbtn {
            font-size: 14px !important;
            padding: 8px !important;
            margin-top: 0px !important;
        }

        .ul-sm-fontsize {
            font-size: 11.2px !important;
        }

        .tab-content {
            overflow: hidden;
        }

        .tab-pane {
            overflow-x: auto;

        }

        .interviewsmbtn {
            font-size: 14px !important;
            padding: 4px !important;
            margin-top: 0px !important;
        }

        .iconfontsize {
            font-size: 12px !important;
        }

        select.form-select {

            border: 1px solid #dee2e6 !important;
        }

        .sm-comp-email {
            margin-top: 8px !important;
        }

        .interviebtn {
            margin-top: 12px !important;
        }

        .sm-margin-mb-profile {
            margin-bottom: 10px !important;
        }
    }
</style>

<!-- <link rel="stylesheet" href="assets/css/profile.css"> -->
<!-- state model  -->
<link rel="stylesheet" href="<?= base_url(env('ImagePath') . 'assets/css/profile.css'); ?>">
<div class="modal fade" id="addStateModal" tabindex="-1" aria-labelledby="addStateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStateModalLabel">Add State</h5>
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
                            <input type="text" id="state_name" name="state_name" class="form-control" placeholder="Enter State Name" />
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
                            <input type="text" class="form-control" name="city_name" id="city_name" placeholder="Enter city Name" />

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
                                <?php foreach ($countries as $country) : ?>
                                    <option value="<?= $country['id']; ?>"><?= $country['country_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="country_id_error" class="invalid-feedback d-block text-danger mt-1"></div>
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
                        <div id="country_name_error" class="invalid-feedback d-block text-danger mt-1"></div>
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
                        <div id="departmentError" class="text-danger mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address_details" class="form-label">Designation Name</label>
                        <div class="input-group">

                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="designation_name" id="designation_name" placeholder="Enter Designation Name" />

                        </div>
                        <div id="designationError" class="text-danger mt-1"></div>
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
                    <div class="mb-3">
                        <label for="country_name" class="form-label">Department Name</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="mdi mdi-calendar fs-5"></i></span>
                            </div>
                            <input type="text" class="form-control" name="department_name" id="department_name" placeholder="Enter Department Name" />
                        </div>
                        <div class="text-danger mt-1" id="department_name_error"></div>
                    </div>

                    <button type="submit" class="btn hr-btnbg float-end" id="submitBtnDepartment">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4 px-1">
            <div class="card p-3 lg-card-margin">
                <!-- <div class="text-center">
                    <label for="profile-image-input" style="cursor: pointer;">
                        <img src="" class="img-fluid avatar-xxl rounded-circle" alt="" id="profile-image" style="width: 160px; height: 160px;">
                    </label>
                    <input type="file" id="profile-image-input" accept="image/*" style="display: none;">
                    <input type="hidden" name="admin_id" id="admin_id">
                    <h5 class="mb-0 mt-2 capitalize-text" id="firstname1"></h5>
                </div> -->
                <div class="text-center position-relative">
                    <label for="profile-image-input" style="cursor: pointer;">
                        <img src="<?= getDefaultProfileImage() ?>" class="img-fluid avatar-xxl rounded-circle" alt="Profile Image" id="profile-image" style="width: 160px; height: 160px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= getDefaultProfileImage() ?>';">
                        <div class="profile-image-overlay">
                            <i class="mdi mdi-camera"></i>
                        </div>
                    </label>
                    <input type="file" id="profile-image-input" accept="image/*" style="display: none;">
                    <input type="hidden" name="admin_id" id="admin_id">

                    <!-- Add delete button for profile image -->
                    <button type="button" class="btn btn-danger btn-sm rounded-circle delete-profile-btn"
                        id="delete-profile-main" title="Remove profile image" style="position: absolute; top: 10px; right: 30px;">
                        <i class="mdi mdi-trash-can-outline"></i>
                    </button>

                    <h5 class="mb-0 mt-2 capitalize-text" id="firstname1"></h5>
                </div>

                <hr>
                <div>
                    <p class="section-title">About</p>
                    <p><i class="mdi mdi-phone me-2" style="color: #E66136;"></i><span class="info-label">Phone:</span><span id="profile-phone"></span></p>
                    <p><i class="mdi mdi-email-outline me-2" style="color: #E66136;"></i><span class="info-label">Email:</span><span id="profile-email"></span></p>
                </div>

                <hr>
                <div>
                    <p class="section-title">Address</p>
                    <p><i class="mdi mdi-map-marker me-2 capitalize-text" style="color: #E66136;"></i><span class="info-label">Address:</span><span id="address1">390 Market Street</span></p>
                    <p><i class="mdi mdi-city me-2 capitalize-text" style="color: #E66136;"></i><span class="info-label">City:</span><span id="city">San Francisco</span></p>
                    <p><i class="mdi mdi-numeric me-2 capitalize-text" style="color: #E66136;"></i><span class="info-label">Postcode:</span><span id="postcode1">94102</span></p>
                </div>

                <hr>
                <!-- Include MDI CSS -->
                <link href="https://cdn.materialdesignicons.com/7.2.96/css/materialdesignicons.min.css" rel="stylesheet">

                <?php if ($role == 'admin') : ?>

                    <p class="section-title">Company Details</p>

                    <div class="">
                        <p id="company_name1" class="mb-1 fs-6 text-muted capitalize-text">Abcd Company Technology</p>
                        <p id="company_address1" class="mb-1 text-muted capitalize-text">201, Surat Vedroad</p>
                        <p id="company_phone1" class="mb-1 text-muted capitalize-text">12345667</p>
                        <p id="company_email1" class="mb-1 text-muted capitalize-text">email@gmail.com</p>
                    </div>


                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-8 px-1">
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="profileTabs">

                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#overview"><i class="fa fa-info-circle" aria-hidden="true"></i> Overview</a>
                </li>
                <?php if ($role == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#compnydetail"><i class="fa fa-building-o" aria-hidden="true"></i> Company Details</a></li>
                <?php endif; ?>
                <?php if ($role !== 'admin' && $role !== 'hr') : ?>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#compensation">Tasks</a></li>

                    <!-- <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#performance">Performance</a></li> -->
                <?php endif; ?>
                <?php if ($role == 'employee' || $role == 'hr') : ?>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#emergency">Bank Details</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#editprofile"><i class="fa fa-user" aria-hidden="true"></i> Edit Profile</a></li>

            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->

                <div class="tab-pane fade show active" id="overview">
                    <div class="row w-100 mt-3">
                        <div class="col-12">
                            <h6 class="section-title">Overview</h6>

                            <div class="card-body">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="tdfont">
                                                <i class="mdi mdi-account-outline me-1 mdicon"></i> First Name
                                            </td>

                                            <td id="firstname2" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i> Last Name</td>
                                            <td id="lastname2" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-gender-male-female me-1 mdicon"></i> Gender</td>
                                            <td id="gender1" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-cake-variant-outline me-1 mdicon"></i> Date Of Birth</td>
                                            <td id="date_of_birth2" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"> <i class="mdi mdi-earth me-1 mdicon"></i>Country</td>
                                            <td id="country" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"> <i class="mdi mdi-map-marker me-1 mdicon"></i>State</td>
                                            <td id="state2" class="capitalize-text"></td>
                                        </tr>
                                        <?php if ($role !== 'admin') : ?>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-office-building me-1 mdicon"></i> Department</td>
                                                <td id="department" class="capitalize-text"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-map-marker me-1 mdicon"></i> Designation</td>
                                                <td id="designation" class="capitalize-text"></td>
                                            </tr>
                                            <tr>
                                                <td class="tdfont"><i class="mdi mdi-calendar-check-outline me-1 mdicon"></i> Hire Date</td>
                                                <td id="joining_date" class="capitalize-text"></td>
                                            </tr>
                                        <?php endif; ?>


                                    </tbody>
                                </table>

                            </div>
                        </div>


                    </div>
                </div>

                <?php if ($role == 'admin'): ?>
                    <div class="tab-pane fade" id="compnydetail">
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="section-title">Edit Company Information</h6>
                                <div class="card-body">

                                    <form method="POST" enctype="multipart/form-data" id="Updatecompany">
                                        <input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="company_name">Company Name</label>
                                                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Enter your company name" value="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 sm-profile-margin">
                                                <div class="form-group">
                                                    <label for="company_phone">Company Phone</label>
                                                    <input type="text" name="company_phone" id="company_phone" class="form-control" placeholder="Enter company phone number" value="">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">

                                            <div class="col-lg-6 col-md-6 sm-profile-margin">
                                                <div class="form-group">
                                                    <label for="company_email" class="sm-comp-email">Company Email</label>
                                                    <input type="email" name="company_email" id="company_email" class="form-control" placeholder="Enter company email" value="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 sm-profile-margin">
                                                <div class="form-group">
                                                    <label for="company_address" class="sm-comp-email">Company Address</label>
                                                    <textarea name="company_address" id="company_address" class="form-control" rows="3" placeholder="Enter company address"></textarea>
                                                </div>
                                            </div>
                                            <!-- New Fields End -->
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6">
                                                <div class="form-group">
                                                    <label for="logo_img">Upload Logo</label>
                                                    <input type="file" class="form-control" name="logo_img" id="logo_img" accept="image/*">
                                                    <small class="text-muted d-block mt-1">Main company logo (Website, Navbar, etc.)</small>
                                                </div>
                                                <div class="mt-2">
                                                    <img id="company_logo" src="<?= base_url(env('ImagePath') . 'upload/fab_logo.jpg') ?>" alt="Company Logo" class="img-fluid view-logo" width="150">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 sm-profile-margin">
                                                <div class="form-group">
                                                    <label for="pdf_logo">Upload PDF Logo</label>
                                                    <input type="file" class="form-control" name="pdf_logo" id="pdf_logo" accept="image/*">
                                                    <small class="text-muted d-block mt-1">Logo used in Salary Slips & generated PDFs</small>
                                                </div>
                                                <div class="mt-2">
                                                    <img id="company_pdf_logo" src="<?= function_exists('getCompanyPdfLogo') ? getCompanyPdfLogo() : base_url(env('ImagePath') . 'upload/fab_logo.jpg') ?>" alt="Company PDF Logo" class="img-fluid view-pdf-logo" width="150">
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group text-end">
                                            <button type="submit" id="companyprofile" class="btn hr-btnbg interviewsmbtn sm-margin-mb-profile">Update Profile</button>
                                        </div>
                                    </form>
                                </div>
                            </div>


                        </div>
                    </div>
                <?php endif; ?>

                <!-- Compensation Tab -->
                <div id="compensation" class="tab-pane fade">
                    <div class="mt-3">
                        <h6 class="section-title">Task</h6>
                        <div class="row g-3" id="task-container"></div>
                    </div>
                </div>

                <!-- Other Tabs Placeholder -->
                <div class="tab-pane fade" id="emergency">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">Bank Details</h6>
                            <a href="#" id="editBankBtn" class="btn btn-sm rounded" style="background-color: #E66136; color: white;">
                                <i class="mdi mdi-pencil-outline"></i> Edit Bank Details
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-domain me-1 mdicon"></i>Bank Name</td>
                                        <td class="bank_name capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-map-marker me-1 mdicon"></i>Account Number</td>
                                        <td class="acc_number capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-account-outline me-1 mdicon"></i>Account Holder Name</td>
                                        <td class="acc_in_name capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-phone me-1 mdicon"></i>Branch Name</td>
                                        <td class="branch_name capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-email-outline me-1 mdicon"></i>Branch Code</td>
                                        <td class="branch_code capitalize-text"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Edit Bank Details Modal -->
                <div class="modal fade" id="editBankModal" tabindex="-1" aria-labelledby="editBankModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="dashboardEditBankForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editBankModalLabel">Edit Bank Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="dash-bank-user-id" name="user_id">
                                    <div class="mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" id="dash-bank-name" name="bank_name">
                                        <div class="text-danger" id="dash-error-bank_name"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="dash-account-number" name="acc_number">
                                        <div class="text-danger" id="dash-error-acc_number"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Account Holder Name</label>
                                        <input type="text" class="form-control" id="dash-account-in-name" name="acc_in_name">
                                        <div class="text-danger" id="dash-error-acc_in_name"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Branch Name</label>
                                        <input type="text" class="form-control" id="dash-branch-name" name="branch_name">
                                        <div class="text-danger" id="dash-error-branch_name"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Branch Code</label>
                                        <input type="text" class="form-control" id="dash-branch-code" name="branch_code">
                                        <div class="text-danger" id="dash-error-branch_code"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn" style="background-color: #E66136; color: white;">Save</button>
                                    <button type="button" class="btn" style="background-color: #6c757d; color: white;" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END Edit Bank Details Modal -->

                <!-- <div class="tab-pane fade" id="performance">
                    <p class="mt-3">Performance history loading...</p>
                </div> -->
                <div class="tab-pane fade" id="editprofile">
                    <h4 class="card-title mb-4">Edit Profile</h4>

                    <div class="overflow-hidden w-100">
                        <div class="col-xl-12 w-100">
                            <div class="task-list-box" id="landing-task">
                                <form method="POST" enctype="multipart/form-data" id="UpdateForm">
                                    <input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label for="name">First Name</label>
                                                <input type="text" name="firstname" id="firstname" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Last Name</label>
                                                <input type="text" name="lastname" id="lastname" class="form-control" value="">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" name="email" id="email" class="form-control" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">

                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text" name="contact_number" id="contact_number" class="form-control" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6">

                                            <div class="form-group">
                                                <label for="address_1">Address1</label>
                                                <textarea name="address_1" id="address_1" class="form-control" rows="3"></textarea>
                                            </div>

                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label for="address_2">Address2</label>
                                                <textarea name="address_2" id="address_2" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 mt-2">
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

                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                                    <label class="interviebtn">Country</label>
                                                    <?php if ($role !== 'employee') : ?>
                                                        <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white interviewsmbtn" style="background-color: #E66136;font-size:14px" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                                                            <i class="mdi mdi-plus iconfontsize"></i> Add Country
                                                        </button>
                                                    <?php endif; ?>
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
                                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                                    <label for="address">State</label>
                                                    <?php if ($role !== 'employee') : ?>
                                                        <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white interviewsmbtn" style="background-color: #E66136;font-size:14px" data-bs-toggle="modal" data-bs-target="#addStateModal">
                                                            <i class="mdi mdi-plus iconfontsize"></i> Add State
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- <input type="text" name="state" id="state" class="form-control" value=""> -->
                                                <select class="form-select" name="state_id" id="state_id">
                                                    <option value="" disabled selected>Select your state</option>
                                                    <?php foreach ($states as $state) : ?>
                                                        <option value="<?= $state['id']; ?>" <?= (isset($user['state_id']) && $user['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                                            <?= $state['state_name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 mt-2 col-md-6">
                                            <div class="form-group">

                                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                                    <label class="interviebtn">City</label>
                                                    <?php if ($role !== 'employee') : ?>
                                                        <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white interviewsmbtn" style="background-color: #E66136;font-size:14px" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                                            <i class="mdi mdi-plus iconfontsize"></i> Add City
                                                        </button>
                                                    <?php endif; ?>
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
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label for="date_of_birth">Date of Birth</label>
                                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" placeholder="Enter your birth date" value="" />
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6">
                                            <div class="form-group">
                                                <label for="postcode">Postcode</label>
                                                <input type="text" class="form-control" name="postcode" id="postcode" placeholder="Enter your postcode/ZIP code" value="" />
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($role !== 'admin') : ?>

                                        <div class="row">
                                            <div class="col-lg-6 col-md-6">
                                                <div class="form-group">
                                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                                        <label class="interviebtn">Department</label>
                                                        <?php if ($role !== 'employee') : ?>
                                                            <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white interviewsmbtn" style="background-color: #E66136;font-size:14px;white-space:nowrap;" data-bs-toggle="modal" data-bs-target="#adddepartementModal">
                                                                <i class="mdi mdi-plus iconfontsize"></i> Add Department
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>

                                                    <select class="form-select" id="department_id" name="department_id">
                                                        <option value="" disabled>Select Department</option>
                                                        <?php foreach ($departments as $department) : ?>
                                                            <option value="<?= $department['id']; ?>"><?= $department['department_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6">
                                                <div class="form-group">
                                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                                        <label class="interviebtn">Designation</label>
                                                        <?php if ($role !== 'employee') : ?>
                                                            <button type="button" class="btn p-1 btn-sm d-flex align-items-center rounded addbtn-white interviewsmbtn" style="background-color: #E66136;font-size:14px;white-space:nowrap;" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
                                                                <i class="mdi mdi-plus iconfontsize"></i> Add Designation
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>

                                                    <select class="form-select" name="designation_id" id="designation_id" >
                                                        <option value="" disabled>Select Designation</option>
                                                        <?php foreach ($designations as $designation) : ?>
                                                            <option value="<?= $designation['id']; ?>"><?= $designation['designation_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>

                                    <!-- <div class="form-group">
                                        <label for="profile_image">Profile Image</label>
                                        <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    </div>
                                    <div class="">
                                        <img id="profile-image-preview" src="upload/1789966027_54c5a38ccda20f7c2bac.jpg" alt="Profile Image" class="img-fluid mb-2" width="80px" height="80px">
                                    </div> -->
                                    <div class="form-group">
                                        <label for="profile_image">Profile Image</label>
                                        <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    </div>
                                    <div class="position-relative d-inline-block">
                                        <img id="profile-image-preview" src="<?= getDefaultProfileImage() ?>" alt="Profile Image" class="img-fluid mb-2 rounded-circle" width="80px" height="80px" style="object-fit: cover;" onerror="this.onerror=null; this.src='<?= getDefaultProfileImage() ?>';">

                                        <!-- Add delete button for profile preview -->
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle delete-profile-btn"
                                            id="delete-profile-preview" title="Remove profile image" style="position: absolute; top: -5px; right: -5px;">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </div>


                                    <div class="form-group text-end">
                                        <button type="submit" id="interviewsmbtn12" class="btn hr-btnbg interviewsmbtn sm-margin-mb-profile" style="white-space:nowrap;">Update Profile</button>
                                    </div>
                                    <div id="responseMessage"></div>
                                </form>
                            </div><!-- end -->
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var role = '<?= $role ?>';
    $(document).ready(function() {
        
        const token = localStorage.getItem('token'); // JWT token from login
        $("#countryForm").submit(function(e) {
            e.preventDefault();

            const countryName = $("#country_name").val().trim();
            const errorContainer = $("#country_name_error");
            errorContainer.text("");

            if (countryName === "") {
                errorContainer.text("Please enter a country name.");
                return;
            }

            $.ajax({
                url: "<?= base_url('api/add-country') ?>",
                type: "POST",
                data: {
                    country_name: countryName
                },
                headers: {
                    Authorization: "Bearer " + token
                },
                success: function(response) {
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

                        $("#country_id_main, #countries_id_modal, #countries_id_state_modal").append(`<option value="${response.country.id}" selected>${response.country.country_name}</option>`);
                    } else {
                        // ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â¥ Handle duplicate error or other custom message
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: response.message || "Failed to add country."
                        });
                    }
                },
                error: function(xhr) {
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

    $(document).ready(function() {
        const token = localStorage.getItem('token'); // JWT token from login
        $("#cityForm").submit(function(e) {
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
                url: "<?= base_url('api/add-city') ?>",
                type: "POST",
                data: {
                    city_name: cityName,
                    country_id: countryId
                },
                headers: {
                    Authorization: "Bearer " + token // if required
                },
                success: function(response) {
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
                        // ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ Handle duplicate or general error message
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: response.message || "Failed to add city."
                        });
                    }
                },
                error: function(xhr) {
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

    $(document).ready(function() {
        const token = localStorage.getItem('token'); // JWT token from login
        
        $("#departmentForm").submit(function(e) {
            e.preventDefault();

            $('#department_name_error').text('');

            let departmentName = $("#department_name").val().trim();

            if (departmentName === "") {
                $('#department_name_error').text('Department Name is required.');
                return;
            }

            let formData = $(this).serialize();

            $.ajax({
                url: "<?= base_url('api/department/add'); ?>",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    $('#department_name_error').text('');

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
                        // ÃƒÆ’Ã‚Â¢Ãƒâ€šÃ‚ÂÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â Show SweetAlert for errors like duplicate
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

    $("#designationForm").submit(function(e) {
        const token = localStorage.getItem('token'); // JWT token from login
        e.preventDefault();
        var formData = $(this).serialize();

        // Clear previous errors
        $(".text-danger").text("");

        $.ajax({
            url: "<?= base_url('api/designation/add'); ?>",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
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
    $(document).ready(function() {
        $("#stateForm").submit(function(e) {
            const token = localStorage.getItem('token'); // JWT token from login
            e.preventDefault();

            let stateName = $("#state_name").val().trim();
            let errorContainer = $("#state_name_error");
            errorContainer.text("");

            if (stateName === "") {
                errorContainer.text("Please enter a state name.");
                return;
            }

            $.ajax({
                url: "<?= base_url('api/add-state') ?>",
                type: "POST",
                data: {
                    state_name: stateName
                },
                headers: {
                    Authorization: "Bearer " + token
                },
                success: function(response) {
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

                        $("#state_id, #state_id_modal").append(
                            `<option value="${response.country.id}" selected>${response.country.state_name}</option>`
                        ).val(response.country.id).trigger("change");

                    } else {
                        // ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ Show duplicate or custom error message
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: response.message || "Failed to add state."
                        });
                    }
                },
                error: function(xhr) {
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

    $(document).ready(function() {
        // Fetch user profile data using AJAX
        $.ajax({
            url: 'api/profile', // Ensure this is the correct URL for your API
            type: 'GET',
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
            success: function(data) {
                // Check if the response has the 'data' property and it contains the profile info
                if (data.status === 'success' && data.data) {
                    // Helper function to set default value
                    console.log(data.data);

                    function getValue(field) {
                        return field !== null && field !== undefined && String(field).trim() !== '' ? String(field) : 'N/A';
                    }
                    const firstName = getValue(data.data.firstname);
                    const formattedName = firstName.charAt(0).toUpperCase() + firstName.slice(1).toLowerCase();
                    // Update the profile page with the fetched data
                    $('#profile-name').text(getValue(data.data.firstname));
                    $('#profile-role').text(getValue(data.data.role));
                    $('#profile-email').text(getValue(data.data.email));
                    $('#profile-phone').text(getValue(data.data.contact_number));
                    // $('#profile-bio').text(getValue(data.data.firstname) + ' ' + getValue(data.data.lastname));
                    $('#profile-bio').text(getValue(data.data.firstname));
                    $('#firstname1').text(formattedName);
                    $('#firstname2').text(getValue(data.data.firstname));
                    $('#lastname2').text(getValue(data.data.lastname));
                    $('#address1').text(getValue(data.data.address_1));
                    $('#address2').text(getValue(data.data.address_2));
                    $('#gender1').text(getValue(data.data.gender));
                    $('#date_of_birth1').text(getValue(data.data.date_of_birth));
                    $('#date_of_birth2').text(getValue(data.data.date_of_birth));
                    $('#country').text(getValue(data.data.country_name));
                    $('#city').text(getValue(data.data.city_name));
                    $('#state1').text(getValue(data.data.state_name));
                    $('#state2').text(getValue(data.data.state_name));
                    $('#contact_number1').text(getValue(data.data.contact_number));
                    $('#postcode1').text(getValue(data.data.postcode));
                    $('#designation').text(getValue(data.data.designation_name));
                    $('#department').text(getValue(data.data.department_name));
                    $('#joining_date').text(getValue(data.data.joining_date));
                    $('.company_name').text(getValue(data.data.company_name));
                    $('.company_address').text(getValue(data.data.company_address));
                    $('.company_phone').text(getValue(data.data.company_phone));
                    $('.company_email').text(getValue(data.data.company_email));
                    $('.bank_name').text(getValue(data.data.bank_name));
                    $('.ifsc_code').text(getValue(data.data.ifsc_code));
                    $('.acc_in_name').text(getValue(data.data.acc_in_name));
                    $('.branch_name').text(getValue(data.data.branch_name));
                    $('.branch_code').text(getValue(data.data.branch_code));
                    $('.acc_number').text(getValue(data.data.acc_number));
                    $('#company_name1').text(getValue(data.data.company_name));
                    $('#company_address1').text(getValue(data.data.company_address));
                    $('#company_phone1').text(getValue(data.data.company_phone));
                    $('#company_email1').text(getValue(data.data.company_email));
                    $('#admin_id').val(data.data.user_id);
                    let html = '';

                    if (data.data.task && data.data.task.length > 0) {
                        data.data.task.forEach(task => {
                            html += `
                        <div class="col-md-6">
                            <div class="card p-3 taskcard shadow-sm">
                            <h5 class="card-title text-dark">${task.task_title}</h5>
                            <p class="card-text">${task.description || 'No description'}</p>
                            <p class="text-muted">Assigned: ${task.assigned_date} | Due: ${task.due_date}</p>
                        `;

                            if (task.subtasks && task.subtasks.length > 0) {
                                html += `<ul class="list-group mt-2">`;
                                task.subtasks.forEach(sub => {
                                    const statusClass = sub.subtask_status === 'completed' ? 'success' : 'warning';
                                    html += `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>${sub.subtask_title}</span>
                                            <span class="badge bg-${statusClass}">${sub.subtask_status.charAt(0).toUpperCase() + sub.subtask_status.slice(1)}</span>
                                        </li>`;
                                });
                                html += `</ul>`;
                            } else {
                                html += `<p class="text-muted">No subtasks available.</p>`;
                            }

                            html += `</div></div>`;
                        });
                    } else {
                        html += `<p class="text-muted">No tasks assigned.</p>`;
                    }
                    const IMAGE_BASE_URL = "<?= base_url(env('ImagePath')) ?>";
                    // console.log(IMAGE_BASE_URL);
                    $('#task-container').html(html);
                    // Update company logo
                    if (data.logo_img) {
                        $('#company_logo').attr('src', 'upload/' + response.data.logo_img);
                    } else {
                        $('#company_logo').attr('src', IMAGE_BASE_URL + 'upload/fab_logo.jpg'); // Default fallback
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
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    setTimeout(function(){
                        // Populate form fields
                        $('#firstname').val(data.firstname);
                        $('#lastname').val(data.lastname);
                        $('#email').val(data.email);
                        $('#contact_number').val(data.contact_number);
                        $('#address_1').val(data.address_1);
                        $('input[name="gender"][value="' + data.gender + '"]').prop('checked', true);
                        $('#date_of_birth').val(data.date_of_birth);
                        $('#address_2').val(data.address_2);
                        $('#state_id').val(data.state_id);
                        $('#postcode').val(data.postcode);
                        $('#city_id').val(data.city_id);
                        $('#country_id').val(data.country_id);
                        $('#country_id_main').val(data.country_id);                        
                        $('#designation_id').val(data.designation_id);
                        $('#department_id').val(data.department_id);

                        if (role === "employee") {
                            $('#department_id, #designation_id')
                                .css('pointer-events', 'none')
                                .css('background-color', '#eee');
                        }
                                                
                        $('#company_name').val(data.company_name);
                        $('#company_address').val(data.company_address);
                        $('#company_email').val(data.company_email);
                        $('#company_phone').val(data.company_phone);
                        $('#company_name').val(data.company_name);

                        let baseUrl = "<?= base_url(); ?>";
                        const IMAGE_BASE_URL = "<?= base_url(env('ImagePath')) ?>";
                        // Update company logo
                        if (data.logo_img) {
                            $('#company_logo').attr('src', baseUrl + '/upload/' + data.logo_img);
                        } else {
                            $('#company_logo').attr('src', IMAGE_BASE_URL + '/upload/fab_logo.jpg'); // Fallback image
                        }

                        // Update company PDF logo
                        if (data.pdf_logo) {
                            const pdfLogoSrc = data.pdf_logo.startsWith('http')
                                ? data.pdf_logo
                                : (data.pdf_logo.startsWith('upload/') ? baseUrl + '/' + data.pdf_logo : baseUrl + '/upload/' + data.pdf_logo);
                            $('#company_pdf_logo').attr('src', pdfLogoSrc);
                        } else if (data.logo_img) {
                            const logoSrc = data.logo_img.startsWith('http')
                                ? data.logo_img
                                : (data.logo_img.startsWith('upload/') ? baseUrl + '/' + data.logo_img : baseUrl + '/upload/' + data.logo_img);
                            $('#company_pdf_logo').attr('src', logoSrc);
                        } else {
                            $('#company_pdf_logo').attr('src', IMAGE_BASE_URL + '/upload/fab_logo.jpg');
                        }

                        // Update company favicon
                        if (data.favicon_icon) {
                            $('#company_favicon').attr('src', baseUrl + '/upload/' + data.favicon_icon);
                        } else {
                            $('#company_favicon').attr('src', baseUrl + '/favicon.ico');
                        }

                        // Update profile image preview
                        if (data.profile_image && data.profile_image !== '1789966027_54c5a38ccda20f7c2bac.jpg' && data.profile_image !== 'default_avatar.png') {
                            $('#profile-image-preview').attr('src', baseUrl + '/upload/' + data.profile_image);
                        } else {
                            $('#profile-image-preview').attr('src', '<?= getDefaultProfileImage() ?>'); // Fallback image
                        }
                    }, 2000);
                    
                } else {
                    console.error('Error fetching profile data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    // ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ Edit Bank Details ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬
    $(document).ready(function () {
        $('#editBankBtn').on('click', function (e) {
            e.preventDefault();
            const userId = $('#admin_id').val();
            if (!userId) {
                Swal.fire('Error', 'Could not identify user. Please refresh the page.', 'error');
                return;
            }

            $.ajax({
                url: '/api/get_user_bank_data/' + userId,
                type: 'GET',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
                success: function (response) {
                    if (response.success) {
                        const bank = response.data;
                        $('#dash-bank-user-id').val(bank.user_id);
                        $('#dash-bank-name').val(bank.bank_name);
                        $('#dash-account-number').val(bank.acc_number);
                        $('#dash-account-in-name').val(bank.acc_in_name);
                        $('#dash-branch-name').val(bank.branch_name);
                        $('#dash-branch-code').val(bank.branch_code);
                        $('#editBankModal').modal('show');
                    } else {
                        Swal.fire('Error', 'Could not load bank details.', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Server error while fetching bank details.', 'error');
                }
            });
        });

        $('#dashboardEditBankForm').on('submit', function (e) {
            e.preventDefault();
            // Clear previous errors
            $('[id^="dash-error-"]').text('');

            const formData = $(this).serialize();

            $.ajax({
                url: '/api/update_user_bank_data',
                type: 'POST',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
                data: formData,
                success: function (response) {
                    if (response.success) {
                        $('#editBankModal').modal('hide');
                        // Refresh displayed bank data
                        $('.bank_name').text($('#dash-bank-name').val());
                        $('.acc_number').text($('#dash-account-number').val());
                        $('.acc_in_name').text($('#dash-account-in-name').val());
                        $('.branch_name').text($('#dash-branch-name').val());
                        $('.branch_code').text($('#dash-branch-code').val());
                        Swal.fire('Success', response.message || 'Bank details updated successfully.', 'success');
                    } else if (response.errors) {
                        $.each(response.errors, function (field, msg) {
                            $('#dash-error-' + field).text(msg);
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Something went wrong.', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Server error while saving bank details.', 'error');
                }
            });
        });
    });
    // ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬


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

                    if (response.pdf_logo) {
                        $('#company_pdf_logo').attr('src', response.pdf_logo);
                    } else {
                        $('#company_pdf_logo').attr('src', response.logo_img);
                    }

                    // Populate company details form fields if they exist
                    if ($('#company_name').length) {
                        $('#company_name').val(response.company_name);
                        $('#company_address').val(response.company_address);
                        $('#company_email').val(response.company_email);
                        $('#company_phone').val(response.company_phone);
                    }
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
        const file = $('#logo_img')[0].files[0];

        if (!file) return; // Exit if no file selected

        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid File',
                text: 'Only JPG and PNG image files are allowed.',
            });
            $('#logo_img').val(''); // Reset the file input
            return;
        }

        let formData = new FormData();
        formData.append('logo_img', file);

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

    // Preview PDF Logo instantly on file select
    $('#pdf_logo').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid File',
                text: 'Only JPG, PNG, and WEBP image files are allowed.',
            });
            $(this).val('');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            $('#company_pdf_logo').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });


    $(document).ready(function() {
        $("#interviewsmbtn12").on("click", function(event) {
            event.preventDefault(); // Prevent default form submission

            $(".error-message").remove(); // Remove old error messages
            const form = $("#UpdateForm")[0];
            const formData = new FormData(form); // Collect form data including files
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            $('#loader').show();

            $.ajax({
                url: 'api/profile/update', // API endpoint for updating profile
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
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
        $("#delete-profile-main, #delete-profile-preview").on("click", function(event) {
            event.preventDefault();

            $(".error-message").remove();
            const formData = new FormData(); // Collect form data including files
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            $('#loader').show();

            $.ajax({
                url: 'api/profile/removeImage', // API endpoint for updating profile
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
                            title: 'Profile Removed!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
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
                        // Show general API error like "User info not found for update"
                        if (responseJSON.messages.error) {
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

    $(document).ready(function() {
        $("#companyprofile").on("click", function(event) {
            event.preventDefault(); // Prevent default form submission

            $(".error-message").remove(); // Remove old error messages

            const form = $("#Updatecompany")[0]; // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Get the form DOM element
            const formData = new FormData(form); // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ Create FormData from the form
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            $('#loader').show();

            $.ajax({
                url: 'api/company/update', // API endpoint for updating profile
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
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


                            if (errorMessages.company_name) {
                                $("#company_name").after(`<div class="text-danger error-message">${errorMessages.company_name}</div>`);
                            }

                            if (errorMessages.profile_image) {
                                $("#logo_img").after(`<div class="text-danger error-message">${errorMessages.profile_image}</div>`);
                            }

                            if (errorMessages.pdf_logo) {
                                $("#pdf_logo").after(`<div class="text-danger error-message">${errorMessages.pdf_logo}</div>`);
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
    $('#profile-image-input').on('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-image').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $('#profile-image-input').on('change', function(event) {
        const file = event.target.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('user_id', $('#admin_id').val());

        $.ajax({
            url: '/api/upload_profile_image_admin',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success && response.image_url) {
                    $('#profile-image').attr('src', response.image_url);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated!',
                        text: 'Profile Image updated successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });

                }
                location.reload();
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed!',
                    text: response.message || 'Image upload failed.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
</script>
<?= $this->endSection(); ?>
