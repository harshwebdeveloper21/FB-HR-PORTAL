<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>
<style>
    .lg-card-margin {
        margin-bottom: 10px;
    }

    @media (max-width: 767px) {
        .interviewsmbtn {
            font-size: 12px !important;
            padding: 8px !important;
            margin-top: 10px !important;
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
    }

    .capitalize-text {
        text-transform: capitalize;
    }
</style>
<link rel="stylesheet" href="<?= base_url(env('ImagePath') . 'assets/css/profile.css?v=' . time()) ?>">
<div class="container-fluid py-3 px-lg-4">
    <!-- Top Action Bar -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="#" id="editOverviewBtn"
            class="btn btn-sm d-inline-flex align-items-center"
            style="background: linear-gradient(135deg, #E66136 0%, #f05929 100%); color: #fff; border-radius: 8px; padding: 7px 14px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 10px rgba(230, 97, 54, 0.25);">
            <i class="mdi mdi-pencil-outline me-1"></i> Edit Profile
        </a>
    </div>

    <div class="row g-3">
        <!-- Sidebar Profile Card -->
        <div class="col-lg-4 col-xl-3">
            <div class="profile-sidebar-card mb-3">
                <div class="text-center position-relative">
                    <input type="file" id="profile_image_input" name="profile_image" accept="image/*" style="display: none;" />
                    <input type="hidden" name="id_change_image" id="id_change_image">
                    
                    <div class="profile-avatar-wrapper">
                        <label for="profile_image_input" style="cursor: pointer;" title="Click to change profile picture">
                            <img id="profile_image"
                                src="<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg'); ?>"
                                alt="Profile Avatar"
                                onerror="this.onerror=null; this.src='<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg'); ?>';" />
                            <div class="avatar-edit-badge" title="Upload new photo">
                                <i class="mdi mdi-camera"></i>
                            </div>
                        </label>
                    </div>

                    <h5 class="fw-bold mb-1 mt-3 capitalize-text" id="user_info-firstname">Employee Name</h5>
                    <div class="d-flex justify-content-center align-items-center gap-1 flex-wrap mb-2">
                        <span id="profile-role-badge" class="badge bg-light text-secondary border" style="font-size: 11px;">Employee</span>
                        <span id="profile-status-badge" class="badge bg-success-subtle text-success" style="font-size: 11px;">Active</span>
                    </div>
                    <div class="text-muted small" id="user_info-designation-top"></div>
                </div>

                <hr class="my-3" style="opacity: 0.1;">

                <div>
                    <p class="section-title mb-2" style="font-size: 14px;"><i class="mdi mdi-account-outline text-primary me-1"></i> About</p>
                    <div class="mb-2 small d-flex align-items-center">
                        <i class="mdi mdi-phone me-2 text-muted fs-6"></i>
                        <span class="info-label">Phone:</span>
                        <span id="profile-phone" class="fw-semibold text-dark">N/A</span>
                    </div>
                    <div class="mb-2 small d-flex align-items-center">
                        <i class="mdi mdi-email-outline me-2 text-muted fs-6"></i>
                        <span class="info-label">Email:</span>
                        <span id="profile-email" class="fw-semibold text-dark text-truncate" style="max-width: 170px;">N/A</span>
                    </div>
                </div>

                <hr class="my-3" style="opacity: 0.1;">

                <div>
                    <p class="section-title mb-2" style="font-size: 14px;"><i class="mdi mdi-map-marker-outline text-primary me-1"></i> Address</p>
                    <div class="mb-2 small d-flex align-items-start">
                        <i class="mdi mdi-map-marker me-2 text-muted fs-6 mt-1"></i>
                        <div>
                            <span id="address1" class="capitalize-text fw-semibold text-dark">N/A</span>
                        </div>
                    </div>
                    <div class="mb-2 small d-flex align-items-center">
                        <i class="mdi mdi-city me-2 text-muted fs-6"></i>
                        <span class="info-label">City:</span>
                        <span id="city1" class="capitalize-text fw-semibold text-dark">N/A</span>
                    </div>
                    <div class="mb-1 small d-flex align-items-center">
                        <i class="mdi mdi-numeric me-2 text-muted fs-6"></i>
                        <span class="info-label">Postcode:</span>
                        <span id="postcode1" class="capitalize-text fw-semibold text-dark">N/A</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-8 col-xl-9">
            <div class="profile-content-card">
                
                <!-- Modern Segmented Pill Tabs -->
                <ul class="nav nav-pills profile-tabs-nav" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#compnydetail" type="button" role="tab">
                            <i class="fa fa-address-card" aria-hidden="true"></i> Address & Contacts
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#compensation" type="button" role="tab">
                            <i class="fa fa-tasks" aria-hidden="true"></i> Job Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#emergency" type="button" role="tab">
                            <i class="fa fa-bank"></i> Bank Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#leaveHistoryTabPane" id="leaveHistoryTab" type="button" role="tab">
                            <i class="mdi mdi-calendar-clock"></i> Leave History
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#incrementHistory" id="incrementHistoryTab" type="button" role="tab">
                            <i class="mdi mdi-cash-plus"></i> Increment History
                        </button>
                    </li>
                </ul>

                <!-- Tab Content Panes -->
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="section-title mb-0">Overview</h6>
                                <a href="#" id="editOverview"
                                    class="d-flex align-items-center justify-content-center rounded btn btn-sm mt-3"
                                    style="background-color: #E66136; color: white;">
                                    <i class="mdi mdi-pencil-outline fs-7"></i> Edit Overview
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="tdfont">
                                                <input type="hidden" name="users-id" id="users-id">
                                                <i class="mdi mdi-account-outline me-1 mdicon"></i> First Name
                                            </td>

                                            <td id="users-username" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i> Last Name</td>
                                            <td id="user_info-lastname" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i>Email</td>
                                            <td id="users-email"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-gender-male-female me-1 mdicon"></i> Gender</td>
                                            <td id="user_info-gender" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-cake-variant-outline me-1 mdicon"></i> Date Of Birth</td>
                                            <td id="user_info-date_of_birth"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="editForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit Profile</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <input type="hidden" name="edit-id" id="edit-id">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="edit-firstname">
                                        <div class="text-danger" id="error-firstname"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="edit-lastname">
                                        <div class="text-danger" id="error-lastname"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" id="edit-email">
                                        <div class="text-danger" id="error-email"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Gender</label><br>
                                        <div class="d-flex ms-lg-4">
                                            <div class="form-check form-check-inline me-5">
                                                <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male">
                                                <label class="form-check-label" for="genderMale">Male</label>
                                            </div>
                                            <div class="form-check form-check-inline me-5">
                                                <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female">
                                                <label class="form-check-label" for="genderFemale">Female</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="genderOther" value="other">
                                                <label class="form-check-label" for="genderOther">Other</label>
                                            </div><br>
                                            <div class="text-danger" id="error-gender"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="edit-dob">
                                        <div class="text-danger" id="error-dob"></div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn" style="background-color: #E66136; color: white;">Save Changes</button>
                                    <button type="button" class="btn" style="background-color: #E66136; color: white;" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="compnydetail">
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="section-title">Address & Contacts Information</h6>
                                <a href="#" id="editAddress"
                                    class="d-flex align-items-center justify-content-center rounded btn btn-sm mt-3"
                                    style="background-color: #E66136; color: white;">
                                    <i class="mdi mdi-pencil-outline fs-7"></i> Edit Address
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <input type="hidden" name="user-id" id="user-id">
                                            <td class="tdfont">
                                                <i class="mdi mdi-account-outline me-1 mdicon"></i>Address1
                                            </td>

                                            <td id="user_info-address_1" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i>Address2</td>
                                            <td id="user_info-address_2" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i>Country</td>
                                            <td id="user_info-country_name" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-gender-male-female me-1 mdicon"></i>State</td>
                                            <td id="user_info-state_name" class="capitalize-text"></td>
                                        </tr>

                                        <tr>
                                            <td class="tdfont"> <i class="mdi mdi-earth me-1 mdicon"></i>City</td>
                                            <td id="user_info-city_name" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"> <i class="mdi mdi-map-marker me-1 mdicon"></i>Postcode</td>
                                            <td id="user_info-postcode" class="capitalize-text"></td>
                                        </tr>
                                        <tr>
                                            <td class="tdfont"><i class="mdi mdi-domain me-1 mdicon"></i>Contact Number</td>
                                            <td id="user_info-contact_number" class="capitalize-text"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="editAddressForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editAddressModalLabel">Edit Address & Contact Info</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">

                                    <input type="hidden" id="edit-user-id">

                                    <div class="mb-3">
                                        <label class="form-label">Address 1</label>
                                        <input type="text" class="form-control" id="edit-address1" name="address_1">
                                        <div class="text-danger" id="error-address_1"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Address 2</label>
                                        <input type="text" class="form-control" id="edit-address2" name="address_2">
                                        <div class="text-danger" id="error-address_2"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Country</label>
                                        <select class="form-control" id="edit-country" name="country_id">
                                            <option value="">Select Country</option>
                                        </select>
                                        <div class="text-danger" id="error-country_id"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">State</label>
                                        <select class="form-control" id="edit-state" name="state_id">
                                            <option value="">Select State</option>
                                        </select>
                                        <div class="text-danger" id="error-state_id"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">City</label>
                                        <select class="form-control" id="edit-city" name="city_id">
                                            <option value="">Select City</option>
                                        </select>
                                        <div class="text-danger" id="error-city_id"></div>
                                    </div>


                                    <div class="mb-3">
                                        <label class="form-label">Postcode</label>
                                        <input type="text" class="form-control" id="edit-postcode" name="postcode">
                                        <div class="text-danger" id="error-postcode"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="edit-contact" name="contact_number">
                                        <div class="text-danger" id="error-contact_number"></div>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn" style="background-color: #E66136; color: white;">Save Changes</button>
                                    <button type="button" class="btn" style="background-color: #E66136; color: white;" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Compensation Tab -->
                <div class="tab-pane fade" id="compensation">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title">Job Details</h6>
                            <a href="#" id="openEditJobModal"
                                class="d-flex align-items-center justify-content-center rounded btn btn-sm"
                                style="background-color: #E66136; color: white;">
                                <i class="mdi mdi-pencil-outline fs-7"></i> Edit Job
                            </a>

                        </div>
                        <div class="card-body">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="tdfont">
                                            <input type="hidden" name="user-lg" id="user-lg">
                                            <i class="mdi mdi-account-outline me-1 mdicon"></i>Employee ID
                                        </td>

                                        <td id="user_info-employee_id"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i>Department</td>
                                        <td id="user_info-department_name" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-account-circle-outline me-1 mdicon"></i>Designation</td>
                                        <td id="user_info-designation_name" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-gender-male-female me-1 mdicon"></i>Joining Date</td>
                                        <td id="user_info-joining_date" class="capitalize-text"></td>
                                    </tr>

                                    <tr>
                                        <td class="tdfont"> <i class="mdi mdi-earth me-1 mdicon"></i>Working Location</td>
                                        <td id="user_info-working_location" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"> <i class="mdi mdi-map-marker me-1 mdicon"></i>Postcode</td>
                                        <td id="user_info-postcode1" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-domain me-1 mdicon"></i>Salary</td>
                                        <td id="user_info-salary" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-domain me-1 mdicon"></i>Role</td>
                                        <td id="user_info-role" class="capitalize-text"></td>
                                    </tr>


                                </tbody>
                            </table>



                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editJobModal" tabindex="-1" aria-labelledby="editJobModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="jobForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Job Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <input type="hidden" name="user_id" id="job-user-id">

                                    <div class="mb-3">
                                        <label class="form-label">Employee ID</label>
                                        <input type="text" class="form-control" name="employee_id" id="job-employee-id">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <select class="form-control" name="department_id" id="job-department">
                                            <option value="">Select Department</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Designation</label>
                                        <select class="form-control" name="designation_id" id="job-designation">
                                            <option value="">Select Designation</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Joining Date</label>
                                        <input type="date" class="form-control" name="joining_date" id="job-joining-date">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Working Location</label>
                                        <select class="form-control" name="working_location" id="job-working-location">
                                            <option value="">Select Working Location</option>
                                            <option value="Remote">Remote</option>
                                            <option value="On-Site">On-Site</option>
                                        </select>
                                        <!-- <input type="text" class="form-control" name="working_location" id="job-working-location"> -->
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Postcode</label>
                                        <input type="text" class="form-control" name="postcode" id="job-postcode">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Salary</label>
                                        <input type="number" class="form-control" name="salary" id="job-salary">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select class="form-control" name="role" id="job-role">
                                            <option value="">Select Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="employee">Employee</option>
                                            <option value="hr">Hr</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn" style="background-color: #E66136; color: white;">Save Changes</button>
                                    <button type="button" class="btn" style="background-color: #E66136; color: white;" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Other Tabs Placeholder -->
                <div class="tab-pane fade" id="emergency">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title">Bank Details</h6>
                            <a href="#" id="editBankBtn" class="btn btn-sm rounded" style="background-color: #E66136; color: white;">
                                <i class="mdi mdi-pencil-outline"></i> Edit Bank Details
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <input type="hidden" name="user-lg-id" id="user-lg-id">
                                        <td class="tdfont"><i class="mdi mdi-domain me-1 mdicon"></i>Bank Name</td>
                                        <td id="bank_name" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-map-marker me-1 mdicon"></i>Account Number</td>
                                        <td id="acc_number" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-account-outline me-1 mdicon"></i>Account Holder Name</td>
                                        <td id="acc_in_name" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-phone me-1 mdicon"></i>Branch Name</td>
                                        <td id="branch_name" class="capitalize-text"></td>
                                    </tr>
                                    <tr>
                                        <td class="tdfont"><i class="mdi mdi-email-outline me-1 mdicon"></i>Branch Code</td>
                                        <td id="branch_code" class="capitalize-text"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="editBankModal" tabindex="-1" aria-labelledby="editBankModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="editBankForm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Bank Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <input type="hidden" id="bank-user-id" name="user_id">

                                    <div class="mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" id="bank-name" name="bank_name">
                                        <div class="text-danger" id="error-bank_name"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" class="form-control" id="account-number" name="acc_number">
                                        <div class="text-danger" id="error-acc_number"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Account Holder Name</label>
                                        <input type="text" class="form-control" id="account-in-name" name="acc_in_name">
                                        <div class="text-danger" id="error-acc_in_name"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Branch Name</label>
                                        <input type="text" class="form-control" id="branch-name" name="branch_name">
                                        <div class="text-danger" id="error-branch_name"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Branch Code</label>
                                        <input type="text" class="form-control" id="branch-code" name="branch_code">
                                        <div class="text-danger" id="error-branch_code"></div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn" style="background-color: #E66136; color: white;">Save</button>
                                    <button type="button" class="btn" style="background-color: #E66136; color: white;" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Increment History Tab -->
                <div class="tab-pane fade" id="incrementHistory" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="section-title mb-1">Salary & Increment Management</h6>
                                <p class="text-muted small mb-0">Track and manage salary increments for this employee</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm d-inline-flex align-items-center btn-outline-secondary" style="border-radius: 8px; font-weight: 600; padding: 7px 14px;" id="btnOpenAddHistoryModal">
                                    <i class="mdi mdi-history me-1"></i> Add History Only
                                </button>
                                <button type="button" class="btn btn-sm d-inline-flex align-items-center" style="background: linear-gradient(135deg, #E66136 0%, #f05929 100%); color: #fff; border-radius: 8px; font-weight: 600; padding: 7px 14px; box-shadow: 0 4px 10px rgba(230, 97, 54, 0.25);" id="btnOpenAddIncrementModal">
                                    <i class="mdi mdi-cash-plus me-1"></i> Add Increment
                                </button>
                            </div>
                        </div>

                        <!-- KPI Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4">
                                <div class="kpi-metric-card">
                                    <div class="kpi-metric-title">Current Salary</div>
                                    <h4 class="kpi-metric-value text-dark" id="prof-current-salary">&#8377;0</h4>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="kpi-metric-card">
                                    <div class="kpi-metric-title">Total Increments</div>
                                    <h4 class="kpi-metric-value text-primary" id="prof-total-increments">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="kpi-metric-card">
                                    <div class="kpi-metric-title">Latest Increment</div>
                                    <h4 class="kpi-metric-value text-success" id="prof-latest-increment">&#8377;0</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div id="incrementHistoryLoading" class="text-center py-4" style="display:none;">
                            <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
                        </div>

                        <!-- Empty State -->
                        <div id="incrementHistoryEmpty" class="text-center py-4 text-muted" style="display:none;">
                            <i class="mdi mdi-information-outline fs-3 text-muted d-block mb-1"></i>
                            <p class="mb-0">No salary increment records found for this employee.</p>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive" id="incrementHistoryTableWrapper">
                            <table class="table table-bordered table-hover align-middle" id="incrementHistoryTable">
                                <thead style="background-color:#E66136; color:#fff;">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Increment Amount</th>
                                        <th>Previous Salary</th>
                                        <th>New Salary</th>
                                        <th>Effective Date</th>
                                        <th>Recorded On</th>
                                    </tr>
                                </thead>
                                <tbody id="incrementHistoryBody">
                                    <tr><td colspan="6" class="text-center text-muted">Click the tab to load history.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Increment Modal -->
                <div class="modal fade" id="profileAddIncrementModal" tabindex="-1" aria-labelledby="profileAddIncrementModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header text-white" id="profileAddIncModalHeader" style="background: linear-gradient(135deg, #E66136 0%, #f05929 100%);">
                                <h5 class="modal-title fs-6 fw-bold" id="profileAddIncrementModalLabel"><i class="mdi mdi-cash-plus me-1"></i> Add Salary Increment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="alert alert-light border mb-3 p-2 d-flex align-items-center justify-content-between">
                                    <span class="small text-muted">Employee:</span>
                                    <strong class="text-dark" id="modal_inc_emp_name">Employee</strong>
                                </div>

                                <!-- History Only Mode Toggle -->
                                <div class="mb-3 p-2 rounded border d-flex align-items-center justify-content-between" style="background-color: #f8f9fa;">
                                    <div>
                                        <label for="modal_inc_history_only" class="fw-semibold text-dark small d-block mb-0" style="cursor: pointer;">Record as History Only</label>
                                        <span class="text-muted" style="font-size: 11px;">Add past record without modifying current active salary</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="modal_inc_history_only" role="switch" style="cursor: pointer; width: 38px; height: 20px;">
                                    </div>
                                </div>

                                <div id="modal_inc_history_alert" class="alert alert-info py-2 px-3 small mb-3" style="display: none; font-size: 12px;">
                                    <i class="mdi mdi-information-outline me-1"></i> <strong>History Mode:</strong> This record will only be added to increment history. Current employee salary (<span id="modal_inc_history_current_sal" class="fw-bold"></span>) will <strong>NOT</strong> be changed.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold" id="modal_inc_salary_label">Current Salary (&#8377;)</label>
                                    <input type="number" step="0.01" class="form-control fw-bold text-dark" id="modal_inc_current_salary" readonly style="background:#e9ecef;">
                                    <div class="form-text small text-muted" id="modal_inc_salary_help" style="display: none;">Enter the starting/base salary before this historical increment.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Increment Amount (&#8377;) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="modal_inc_amount" placeholder="e.g. 5000" min="1" step="1">
                                    <div class="form-text small text-muted" id="modal_inc_amount_help">Enter the amount to add to current salary.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">New Salary Preview (&#8377;)</label>
                                    <input type="text" class="form-control bg-light fw-bold text-success" id="modal_inc_new_salary_preview" readonly value="&#8377; 0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Effective From Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="modal_inc_date">
                                </div>
                            </div>
                            <div class="modal-footer bg-light py-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-sm text-white" id="btnSaveProfileIncrement" style="background-color: #E66136;">
                                    <i class="mdi mdi-check me-1"></i> <span id="btnSaveProfileIncrementText">Save Increment</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave History Tab -->
                <div class="tab-pane fade" id="leaveHistoryTabPane">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">Leave History</h6>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="p-3 rounded bg-light border flex-fill">
                                <span class="text-muted d-block small">Remaining Paid Leave</span>
                                <strong id="prof-paid-leave" class="fs-4 text-success">0</strong>
                            </div>
                            <div class="p-3 rounded bg-light border flex-fill">
                                <span class="text-muted d-block small">Remaining Sick Leave</span>
                                <strong id="prof-sick-leave" class="fs-4 text-warning">0</strong>
                            </div>
                        </div>
                        <div id="profLeaveLoading" class="text-center py-4">
                            <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
                        </div>
                        <div id="profLeaveEmpty" class="text-center py-4" style="display:none;">
                            <p class="text-muted">No leave history records found.</p>
                        </div>
                        <div class="table-responsive" id="profLeaveTableWrapper" style="display:none;">
                            <table class="table table-bordered table-hover align-middle">
                                <thead style="background-color:#E66136; color:#fff;">
                                    <tr>
                                        <th>Month & Year</th>
                                        <th>Paid Leave Used</th>
                                        <th>Sick Leave Used</th>
                                        <th>Total Days</th>
                                    </tr>
                                </thead>
                                <tbody id="profLeaveBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="performance">
                    <p class="mt-3">Performance history loading...</p>
                </div>
                <div class="tab-pane fade" id="editprofile">
                    <h4 class="card-title mb-4">Edit Profile</h4>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="task-list-box" id="landing-task">

                            </div><!-- end -->
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div>
            </div>
            <div class="text-end mt-4">
                <a href="<?= base_url('/empview') ?>" class="btn hr-btnbg interviewsmbtn">
                    <i class="mdi mdi-arrow-left me-1 iconfontsize"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>


<!-- Error Message Container -->
<div id="error-message" class="alert alert-danger d-none"></div>

<script>
    // Global state variables for profile and increment management
    var currentEmployeeSalary = 0;
    var currentEmployeeUserId = '<?= $id; ?>';
    var currentEmployeeName = '';
    var currentEmployeeStatus = 'Active';

    document.addEventListener('DOMContentLoaded', function() {
        const editOverviewBtn = document.getElementById('editOverviewBtn');
        if (editOverviewBtn) {
            editOverviewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const profileEmployeeId = editOverviewBtn.getAttribute('data-id') || currentEmployeeUserId;
                window.location.href = `/employee/${profileEmployeeId}`;
            });
        }
    });
</script>

<script>
    $(document).ready(function() {
        const token = localStorage.getItem('token'); // JWT token
        const userId = '<?= $id; ?>'; // Employee ID passed from the controller

        function showError(message) {
            $('#error-message').text(message).removeClass('d-none'); // Show error message
        }

        if (userId) {
            $.ajax({
                url: `<?= site_url('employee/details'); ?>/${userId}`,
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#error-message').addClass('d-none'); // Hide error if successful

                        const user = data.data;
                        console.log(user);

                        function setText(selector, value) {
                            $(selector).text(value ? value : 'N/A');
                        }

                        setText('#users-username', user.username);
                        setText('#users-id', user.user_id);

                        setText('#users-email', user.email);
                        setText('#profile-email', user.email);
                        const fullName = $.trim((user.firstname || '') + ' ' + (user.lastname || '')) || user.username || 'Employee';
                        setText('#user_info-firstname', fullName);
                        setText('#user_info-gender', user.gender);
                        setText('#user_info-lastname', user.lastname);
                        setText('#user_info-date_of_birth', user.date_of_birth);
                        setText('#user_info-address_1', user.address_1);
                        setText('#address1', user.address_1);
                        setText('#user_info-address_2', user.address_2);
                        setText('#user_info-country_name', user.country_name);
                        setText('#user_info-state_name', user.state_name);
                        setText('#user_info-city_name', user.city_name);
                        setText('#city1', user.city_name);
                        setText('#user_info-postcode', user.postcode);
                        setText('#user_info-postcode1', user.postcode);
                        setText('#postcode1', user.postcode);
                        setText('#user_info-contact_number', user.contact_number);
                        setText('#number1', user.contact_number);
                        setText('#profile-phone', user.contact_number);
                        setText('#user_info-designation_name', user.designation_name);
                        setText('#user_info-designation-top', user.designation_name || user.department_name || '');
                        setText('#user_info-department_name', user.department_name);
                        
                        const empRole = user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : 'Employee';
                        $('#profile-role-badge').text(empRole);

                        const empStatus = (user.status || 'Active').trim();
                        const isInactive = ['inactive', 'resigned', 'fired', 'removed'].includes(empStatus.toLowerCase());
                        $('#profile-status-badge')
                            .attr('class', 'badge ' + (isInactive ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle'))
                            .text(empStatus);

                        const displayEmpId = user.employee_id && user.employee_id !== '0'
                            ? (String(user.employee_id).startsWith('EMP-') || String(user.employee_id).startsWith('EMP#') ? user.employee_id : 'EMP-' + String(user.employee_id).padStart(3, '0'))
                            : ('EMP-' + (user.user_id || '001'));
                        setText('#user_info-employee_id', displayEmpId);
                        setText('#user_info-joining_date', user.joining_date);
                        setText('#user_info-working_location', user.working_location);
                        setText('#user_info-role', user.role);

                        currentEmployeeSalary = parseFloat(user.salary || 0);
                        currentEmployeeUserId = user.user_id;
                        currentEmployeeName = fullName;
                        currentEmployeeStatus = empStatus;

                        setText('#user_info-salary', '₹ ' + currentEmployeeSalary.toLocaleString('en-IN'));
                        $('#prof-current-salary').html('&#8377; ' + currentEmployeeSalary.toLocaleString('en-IN'));

                        setText('#acc_number', user.acc_number);
                        setText('#bank_name', user.bank_name);
                        setText('#ifsc_code', user.ifsc_code);
                        setText('#acc_in_name', user.acc_in_name);
                        setText('#branch_name', user.branch_name);
                        setText('#branch_code', user.branch_code);
                        
                        $('#id_change_image').val(user.user_id);
                        $('#user-id').val(user.user_id);
                        $('#user-lg-id').val(user.user_id);
                        $('#user-lg').val(user.user_id);
                        $('#editOverviewBtn').attr('data-id', user.user_id);

                        let profileImage = user.profile_image ? user.profile_image : '<?= base_url(env('ImagePath') . "upload/1789966027_54c5a38ccda20f7c2bac.jpg"); ?>';
                        $('#profile_image').attr('src', profileImage);
                    } else {
                        showError(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    showError('Error fetching staff details: ' + error);
                }
            });
        } else {
            showError('No user ID provided.');
        }
    });
    // document.getElementById('profile_image_input').addEventListener('change', function(event) {
    //     const file = event.target.files[0];
    //     if (file) {
    //         const reader = new FileReader();
    //         reader.onload = function(e) {
    //             document.getElementById('profile_image').src = e.target.result;
    //         };
    //         reader.readAsDataURL(file);
    //     }
    // });
    document.getElementById('profile_image_input').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const id_change_image = document.getElementById('id_change_image').value;
        if (file) {
            // Preview the image
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile_image').src = e.target.result;
            };
            reader.readAsDataURL(file);

            // Upload the image using FormData
            const formData = new FormData();
            formData.append('profile_image', file);
            formData.append('id', id_change_image); // replace USER_ID with the logged-in user ID (JS variable or hardcoded)

            fetch('<?= base_url('/api/change_image') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                    } else {
                        Swal.fire('Error', response.message || 'Something went wrong', 'error');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    Swal.fire('Error', 'Image upload failed', 'error');
                });
        }
    });
    $(document).ready(function() {
        // Open modal and fill form
        $('#editOverview').on('click', function() {
            $('#edit-id').val($('#users-id').text().trim());
            $('#edit-firstname').val($('#users-username').text().trim());
            $('#edit-lastname').val($('#user_info-lastname').text().trim());
            $('#edit-email').val($('#users-email').text().trim());
            $('#edit-dob').val($('#user_info-date_of_birth').text().trim());

            // Set gender radio button (case-insensitive match)
            const gender = $('#user_info-gender').text().trim().toLowerCase();
            $('input[name="gender"]').prop('checked', false); // Clear any selected
            $('input[name="gender"]').each(function() {
                if ($(this).val().toLowerCase() === gender) {
                    $(this).prop('checked', true);
                }
            });

            $('#editModal').modal('show');
        });

        // Submit form and update table
        $('#editForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.text-danger').text('');

            // Collect form data
            const data = {
                id: $('#edit-id').val(),
                firstname: $('#edit-firstname').val(),
                lastname: $('#edit-lastname').val(),
                email: $('#edit-email').val(),
                gender: $('input[name="gender"]:checked').val(),
                dob: $('#edit-dob').val()
            };

            $.ajax({
                url: '/api/save_overview',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#users-username').text(data.firstname);
                        $('#user_info-lastname').text(data.lastname);
                        $('#users-email').text(data.email);
                        $('#user_info-gender').text(data.gender);
                        $('#user_info-date_of_birth').text(data.dob);

                        $('#editModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                    } else if (response.status === 'error' && response.errors) {
                        $.each(response.errors, function(field, message) {
                            $('#error-' + field).text(message);
                        });
                    } else {
                        Swal.fire(response.message || 'Something went wrong!');
                        // alert(response.message || 'Something went wrong!');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Server error: ' + xhr.responseText,
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        // Helper function to populate select options and set selected value
        function populateSelectOptions(selectId, dataList, selectedId) {
            const select = $(selectId);
            select.empty().append('<option value="">Select</option>');

            // Determine label key based on selectId
            let labelKey = 'name';
            if (selectId.includes('country')) labelKey = 'country_name';
            else if (selectId.includes('state')) labelKey = 'state_name';
            else if (selectId.includes('city')) labelKey = 'city_name';
            else if (selectId.includes('designation')) labelKey = 'designation_name';
            else if (selectId.includes('department')) labelKey = 'department_name';

            dataList.forEach(item => {
                const isSelected = item.id == selectedId ? 'selected' : '';
                select.append(`<option value="${item.id}" ${isSelected}>${item[labelKey]}</option>`);
            });
        }


        // On Edit Address button click
        $('#editAddress').on('click', function() {
            const user_id = $('#user-id').val(); // make sure this hidden input exists and has value

            $.ajax({
                url: '/api/get_user_address_data/' + user_id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const user = response.user;

                        $('#edit-user-id').val(user.user_id);
                        $('#edit-address1').val(user.address_1);
                        $('#edit-address2').val(user.address_2);
                        $('#edit-postcode').val(user.postcode);
                        $('#edit-contact').val(user.contact_number);

                        // Populate select fields
                        populateSelectOptions('#edit-country', response.country, user.country_id);
                        populateSelectOptions('#edit-state', response.state, user.state_id);
                        populateSelectOptions('#edit-city', response.city, user.city_id);

                        $('#editAddressModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not Found',
                            text: 'User not found',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error fetching address data.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });
        $('#editAddressForm').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                user_id: $('#edit-user-id').val(),
                address_1: $('#edit-address1').val(),
                address_2: $('#edit-address2').val(),
                country_id: $('#edit-country').val(),
                state_id: $('#edit-state').val(),
                city_id: $('#edit-city').val(),
                postcode: $('#edit-postcode').val(),
                contact_number: $('#edit-contact').val()
            };

            // Clear previous errors
            $('.text-danger').text('');

            $.ajax({
                url: '/api/update_user_address', // Define this route
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editAddressModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        location.reload();
                    } else if (response.errors) {
                        // Show validation errors
                        $.each(response.errors, function(field, msg) {
                            $('#error-' + field).text(msg);
                        });
                    } else {
                        Swal.fire(response.message || 'Something went wrong!');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Server error while updating address.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        $('#editBankBtn').on('click', function() {
            const user_id = $('#user-lg-id').val(); // Get from hidden input

            $.ajax({
                url: '/api/get_user_bank_data/' + user_id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const bank = response.data;

                        $('#bank-user-id').val(bank.user_id);
                        $('#bank-name').val(bank.bank_name);
                        $('#account-number').val(bank.acc_number);
                        $('#account-in-name').val(bank.acc_in_name);
                        $('#branch-name').val(bank.branch_name);
                        $('#branch-code').val(bank.branch_code);

                        $('#editBankModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not Found',
                            text: 'Bank data not found',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Server error fetching bank details',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });
        $('#editBankForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $('.text-danger').text('');

            $.ajax({
                url: '/api/update_user_bank_data',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editBankModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        location.reload(); // or refresh bank section
                    } else if (response.errors) {
                        $.each(response.errors, function(field, msg) {
                            $('#error-' + field).text(msg);
                        });
                    } else {
                        Swal.fire(response.message || 'Something went wrong!');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error submitting bank details',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        function populateSelectOptionsjob(selectId, dataList, selectedId) {
            const select = $(selectId);
            select.empty().append('<option value="">Select</option>');

            let labelKey = 'name';
            if (selectId.includes('department')) labelKey = 'department_name';
            else if (selectId.includes('designation')) labelKey = 'designation_name';

            dataList.forEach(item => {
                const isSelected = item.id == selectedId ? 'selected' : '';
                select.append(`<option value="${item.id}" ${isSelected}>${item[labelKey]}</option>`);
            });
        }

        // Call modal on button click
        $('#openEditJobModal').on('click', function() {
            const user_id = $('#user-lg').val(); // hidden input

            $.ajax({
                url: '/api/get_user_address_data/' + user_id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const user = response.user;
                        const departments = response.department || [];
                        const designations = response.designation || [];

                        $('#job-user-id').val(user.user_id);
                        // $('#job-employee-id').val(user.employee_id);
                        $('#job-joining-date').val(user.joining_date);
                        $('#job-working-location').val(user.working_location);
                        $('#job-postcode').val(user.postcode);
                        $('#job-salary').val(user.salary);
                        $('#job-role').val(user.role);
                        const cleanEmpId = user.employee_id && user.employee_id !== '0'
                            ? (String(user.employee_id).startsWith('EMP-') || String(user.employee_id).startsWith('EMP#') ? user.employee_id : 'EMP-' + String(user.employee_id).padStart(3, '0'))
                            : ('EMP-' + (user.user_id || '001'));
                        $('#job-employee-id').val(cleanEmpId);

                        populateSelectOptionsjob('#job-department', departments, user.department_id);
                        populateSelectOptionsjob('#job-designation', designations, user.designation_id);

                        $('#editJobModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Not Found',
                            text: 'User not found',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error fetching address data.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        $('#jobForm').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                user_id: $('#job-user-id').val(),
                employee_id: $('#job-employee-id').val(),
                department_id: $('#job-department').val(),
                designation_id: $('#job-designation').val(),
                joining_date: $('#job-joining-date').val(),
                working_location: $('#job-working-location').val(),
                postcode: $('#job-postcode').val(),
                salary: $('#job-salary').val(),
                role: $('#job-role').val()
            };

            $.ajax({
                url: '/api/update_user_job_data', // You need this API in your controller
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        location.reload();
                        $('#editJobModal').modal('hide');
                        // Optional: reload data on page
                    } else {
                        if (response.errors) {
                            // Show validation errors
                            console.error(response.errors);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Validation Error',
                                text: 'Please fill all required fields correctly.',
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            Swal.fire(response.message || 'Something went wrong!');;
                        }
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating job data.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        // -
        // Increment History Management & Modal
        // -
        let incrementHistoryLoaded = false;

        function loadIncrementHistory() {
            const token = localStorage.getItem('token');
            const targetUserId = currentEmployeeUserId || $('#users-id').text() || '<?= $id; ?>';
            const $tbody = $('#incrementHistoryBody');

            $('#incrementHistoryLoading').show();
            $('#incrementHistoryTableWrapper').hide();
            $('#incrementHistoryEmpty').hide();

            $.ajax({
                url: `/api/employee/increment-history-user/${targetUserId}`,
                type: 'GET',
                headers: { 'Authorization': 'Bearer ' + token },
                dataType: 'json',
                success: function (res) {
                    $('#incrementHistoryLoading').hide();
                    if (res.status === 'success' && res.history && res.history.length > 0) {
                        $('#prof-total-increments').text(res.history.length);
                        const latest = res.history[0];
                        const latestAmt = parseFloat(latest.increment_amount || 0);
                        $('#prof-latest-increment').html('+ &#8377; ' + latestAmt.toLocaleString('en-IN'));

                        // Always prioritize the official current_salary from user_info
                        if (res.current_salary !== undefined && res.current_salary !== null) {
                            currentEmployeeSalary = parseFloat(res.current_salary);
                        } else if (latest.new_salary) {
                            currentEmployeeSalary = parseFloat(latest.new_salary);
                        }
                        $('#prof-current-salary').html('&#8377; ' + currentEmployeeSalary.toLocaleString('en-IN'));
                        $('#user_info-salary').text('₹ ' + currentEmployeeSalary.toLocaleString('en-IN'));

                        let rows = '';
                        res.history.forEach(function (r, idx) {
                            const effDate = r.effective_from_date
                                ? new Date(r.effective_from_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
                                : '-';
                            const createdAt = r.created_at
                                ? new Date(r.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' })
                                : '-';

                            rows += `<tr>
                                <td class="text-muted fw-bold">${idx + 1}</td>
                                <td><span class="badge" style="background:#d4edda;color:#155724;font-size:13px;font-weight:600;">+ &#8377; ${parseFloat(r.increment_amount || 0).toLocaleString('en-IN')}</span></td>
                                <td>&#8377; ${parseFloat(r.previous_salary || 0).toLocaleString('en-IN')}</td>
                                <td class="fw-bold text-dark">&#8377; ${parseFloat(r.new_salary || 0).toLocaleString('en-IN')}</td>
                                <td><span class="text-dark">${effDate}</span></td>
                                <td class="text-muted small">${createdAt}</td>
                            </tr>`;
                        });
                        $tbody.html(rows);
                        $('#incrementHistoryTableWrapper').show();
                        incrementHistoryLoaded = true;
                    } else {
                        if (res.current_salary !== undefined && res.current_salary !== null) {
                            currentEmployeeSalary = parseFloat(res.current_salary);
                            $('#prof-current-salary').html('&#8377; ' + currentEmployeeSalary.toLocaleString('en-IN'));
                        }
                        $('#prof-total-increments').text('0');
                        $('#prof-latest-increment').text('None');
                        $('#incrementHistoryEmpty').show();
                        incrementHistoryLoaded = true;
                    }
                },
                error: function () {
                    $('#incrementHistoryLoading').hide();
                    $tbody.html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to load increment history. Please try again.</td></tr>');
                    $('#incrementHistoryTableWrapper').show();
                }
            });
        }

        $(document).on('shown.bs.tab', '#incrementHistoryTab, [data-bs-target="#incrementHistory"]', function () {
            loadIncrementHistory();
        });

        // Salary preview calculation
        function updateProfileSalaryPreview() {
            const isHistoryOnly = $('#modal_inc_history_only').is(':checked');
            const baseSalary = isHistoryOnly
                ? (parseFloat($('#modal_inc_current_salary').val()) || 0)
                : currentEmployeeSalary;
            const incAmt = parseFloat($('#modal_inc_amount').val()) || 0;
            const newSal = baseSalary + incAmt;
            $('#modal_inc_new_salary_preview').val('₹ ' + newSal.toLocaleString('en-IN'));
        }

        function setProfileIncrementModalMode(isHistoryOnly) {
            $('#modal_inc_history_only').prop('checked', isHistoryOnly);
            if (isHistoryOnly) {
                $('#profileAddIncrementModalLabel').html('<i class="mdi mdi-history me-1"></i> Add Increment History Record');
                $('#profileAddIncModalHeader').css('background', 'linear-gradient(135deg, #4b5563 0%, #374151 100%)');
                $('#modal_inc_history_alert').show();
                $('#modal_inc_history_current_sal').text('₹ ' + currentEmployeeSalary.toLocaleString('en-IN'));
                $('#modal_inc_salary_label').html('Previous / Base Salary (&#8377;) <span class="text-danger">*</span>');
                $('#modal_inc_current_salary').val(currentEmployeeSalary).prop('readonly', false).css('background-color', '#fff');
                $('#modal_inc_salary_help').show();
                $('#modal_inc_amount_help').text('Enter the increment amount for this historical record.');
                $('#btnSaveProfileIncrementText').text('Save History Record');
                $('#btnSaveProfileIncrement').css('background-color', '#374151');
            } else {
                $('#profileAddIncrementModalLabel').html('<i class="mdi mdi-cash-plus me-1"></i> Add Salary Increment');
                $('#profileAddIncModalHeader').css('background', 'linear-gradient(135deg, #E66136 0%, #f05929 100%)');
                $('#modal_inc_history_alert').hide();
                $('#modal_inc_salary_label').text('Current Salary (₹)');
                $('#modal_inc_current_salary').val(currentEmployeeSalary).prop('readonly', true).css('background-color', '#e9ecef');
                $('#modal_inc_salary_help').hide();
                $('#modal_inc_amount_help').text('Enter the amount to add to current salary.');
                $('#btnSaveProfileIncrementText').text('Save Increment');
                $('#btnSaveProfileIncrement').css('background-color', '#E66136');
            }
            updateProfileSalaryPreview();
        }

        $('#modal_inc_amount, #modal_inc_current_salary').on('input', function() {
            updateProfileSalaryPreview();
        });

        $('#modal_inc_history_only').on('change', function() {
            const isChecked = $(this).is(':checked');
            if (!isChecked && ['inactive', 'resigned', 'fired', 'removed'].includes(currentEmployeeStatus.toLowerCase())) {
                Swal.fire('Not Allowed', 'Cannot add active salary increment for an inactive or resigned employee.', 'warning');
                $(this).prop('checked', true);
                return;
            }
            setProfileIncrementModalMode(isChecked);
        });

        // Open Add Increment Modal (Standard)
        $('#btnOpenAddIncrementModal').on('click', function(e) {
            e.preventDefault();

            if (['inactive', 'resigned', 'fired', 'removed'].includes(currentEmployeeStatus.toLowerCase())) {
                Swal.fire('Not Allowed', 'Cannot add active salary increment for an inactive or resigned employee. Please use "Add History Only" to record past increments.', 'warning');
                return;
            }

            $('#modal_inc_emp_name').text(currentEmployeeName || 'Employee');
            $('#modal_inc_current_salary').val(currentEmployeeSalary);
            $('#modal_inc_amount').val('');
            $('#modal_inc_date').val(new Date().toISOString().split('T')[0]);
            setProfileIncrementModalMode(false);

            const modal = new bootstrap.Modal(document.getElementById('profileAddIncrementModal'));
            modal.show();
        });

        // Open Add History Only Modal
        $('#btnOpenAddHistoryModal').on('click', function(e) {
            e.preventDefault();

            $('#modal_inc_emp_name').text(currentEmployeeName || 'Employee');
            $('#modal_inc_current_salary').val(currentEmployeeSalary);
            $('#modal_inc_amount').val('');
            $('#modal_inc_date').val(new Date().toISOString().split('T')[0]);
            setProfileIncrementModalMode(true);

            const modal = new bootstrap.Modal(document.getElementById('profileAddIncrementModal'));
            modal.show();
        });

        // Save Increment or History
        $('#btnSaveProfileIncrement').on('click', function() {
            const $btn = $(this);
            const isHistoryOnly = $('#modal_inc_history_only').is(':checked');
            const incAmount = parseFloat($('#modal_inc_amount').val());
            const incDate = $('#modal_inc_date').val();
            const token = localStorage.getItem('token');
            const targetUserId = currentEmployeeUserId || $('#users-id').text() || '<?= $id; ?>';
            const rawSalary = $('#modal_inc_current_salary').val() || '0';
            const prevSal = isHistoryOnly
                ? parseFloat(rawSalary.toString().replace(/,/g, ''))
                : currentEmployeeSalary;

            if (!incAmount || incAmount <= 0) {
                Swal.fire({ icon: 'warning', title: 'Invalid Amount', text: 'Please enter a valid increment amount greater than 0.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                return;
            }

            if (isHistoryOnly && (isNaN(prevSal) || prevSal < 0)) {
                Swal.fire({ icon: 'warning', title: 'Invalid Salary', text: 'Please enter a valid base salary for this historical record.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                return;
            }

            if (!incDate) {
                Swal.fire({ icon: 'warning', title: 'Required Field', text: 'Please select an effective date.', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                return;
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

            $.ajax({
                url: '<?= base_url("/api/employee/increment-salary") ?>',
                type: 'POST',
                headers: { 'Authorization': 'Bearer ' + token },
                data: {
                    user_id: targetUserId,
                    increment_amount: incAmount,
                    increment_date: incDate,
                    is_history_only: isHistoryOnly ? 1 : 0,
                    previous_salary: prevSal
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="mdi mdi-check me-1"></i> <span id="btnSaveProfileIncrementText">' + (isHistoryOnly ? 'Save History Record' : 'Save Increment') + '</span>');
                    if (res.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('profileAddIncrementModal')).hide();
                        Swal.fire({
                            icon: 'success',
                            title: isHistoryOnly ? 'History Saved!' : 'Increment Added!',
                            text: res.message || 'Salary increment has been recorded successfully.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        loadIncrementHistory();
                    } else {
                        Swal.fire('Error', res.message || 'Failed to record increment.', 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="mdi mdi-check me-1"></i> <span id="btnSaveProfileIncrementText">' + (isHistoryOnly ? 'Save History Record' : 'Save Increment') + '</span>');
                    Swal.fire('Error', xhr.responseJSON?.message || 'Server error occurred while saving increment.', 'error');
                }
            });
        });

        // -
        // Leave History Tab
        // -
        let leaveHistoryLoaded = false;

        $(document).on('click shown.bs.tab', '#leaveHistoryTab, [data-bs-target="#leaveHistoryTabPane"]', function () {
            if (leaveHistoryLoaded) return;
            const token = localStorage.getItem('token');
            const targetUserId = currentEmployeeUserId || $('#users-id').text() || '<?= $id; ?>';

            $('#profLeaveLoading').show();
            $('#profLeaveEmpty').hide();
            $('#profLeaveTableWrapper').hide();

            $.ajax({
                url: '<?= base_url("api/employee/leaveHistoryMonthly/") ?>/' + targetUserId,
                type: 'GET',
                headers: { 'Authorization': 'Bearer ' + token },
                dataType: 'json',
                success: function (res) {
                    $('#profLeaveLoading').hide();
                    if (res.status && res.monthly_history) {
                        $('#prof-paid-leave').text(res.remaining_paid_leave || 0);
                        $('#prof-sick-leave').text(res.remaining_sick_leave || 0);

                        if (res.monthly_history.length === 0) {
                            $('#profLeaveEmpty').show();
                            leaveHistoryLoaded = true;
                            return;
                        }

                        let rows = '';
                        res.monthly_history.forEach(function (m) {
                            rows += `<tr>
                                <td class="fw-bold">${m.month_year}</td>
                                <td class="text-success fw-bold">${m.paid_used} days</td>
                                <td class="text-warning fw-bold">${m.sick_used} days</td>
                                <td class="fw-bold">${m.total_days} days</td>
                            </tr>`;
                        });
                        $('#profLeaveBody').html(rows);
                        $('#profLeaveTableWrapper').show();
                        leaveHistoryLoaded = true;
                    } else {
                        $('#profLeaveEmpty').show();
                        leaveHistoryLoaded = true;
                    }
                },
                error: function () {
                    $('#profLeaveLoading').hide();
                    $('#profLeaveEmpty').show();
                }
            });
        });

    }); // end $(document).ready
</script>


<?= $this->endSection(); ?>

