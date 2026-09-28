<?php

use App\Services\AuthService;

$request = \Config\Services::request();
$authService = new AuthService($request);
$user = $authService->check();

$role = $user ? $user->role : null;

$branchesList = [];
$branchModel = new \App\Models\BranchModel();
$branchesList = $branchModel->getActiveBranches();
?>
<style>   
    .check-in-out-container:not(.role-admin) #check-out-btn {
        border-radius: 18px;
        background: linear-gradient(135deg, #e66136, #ff7b4a) !important;
        color: white !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .check-in-out-container:not(.role-admin) #check-in-btn {
        border-radius: 18px;
        background: linear-gradient(135deg, #e66136, #ff7b4a) !important;
        color: white !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* Desktop Logo Styling (>= 992px) */
    @media (min-width: 992px) {
        .navbar .navbar-brand-wrapper .navbar-brand img,
        .sidebar-logo {
            max-width: 195px !important;
            max-height: 47px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            border-radius: 10px !important;
            transition: max-width 0.25s ease, max-height 0.25s ease;
        }

        /* On Scroll Logo Sizing (Compact Mode) */
        .navbar.headerLight .navbar-brand-wrapper .navbar-brand img,
        .navbar.headerLight .sidebar-logo,
        .navbar.is-scrolled .navbar-brand-wrapper .navbar-brand img,
        .navbar.is-scrolled .sidebar-logo {
            max-width: 130px !important;
            max-height: 40px !important;
        }

        .navbar .navbar-brand-wrapper {
            width: 241px !important;
            min-width: 241px !important;
            background: transparent !important;
            border: none !important;
            border-right: 1px solid rgba(255, 255, 255, 0.25) !important;
            padding-right: 15px !important;
        }
        .navbar .navbar-brand-wrapper .brand-logo {
            display: inline-block !important;
        }
        .navbar .navbar-brand-wrapper .brand-logo-mini {
            display: none !important;
        }
        .sidebar-icon-only .navbar .navbar-brand-wrapper {
            width: 70px !important;
            min-width: 70px !important;
            padding-right: 0 !important;
            border-right: none !important;
            justify-content: center !important;
        }
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo,
        .sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo-mini {
            display: none !important;
        }
        .sidebar-icon-only .navbar .navbar-brand-wrapper .me-3 {
            margin-right: 0 !important;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .sidebar-icon-only .navbar .navbar-menu-wrapper {
            width: calc(100% - 70px) !important;
        }
    }

    /* Mobile & Tablet View (< 992px) - Constant Fixed 62px Header On Load & Scroll */
    @media (max-width: 991.98px) {
        .navbar.default-layout,
        .navbar.headerLight {
            height: 62px !important;
            min-height: 62px !important;
            max-height: 62px !important;
            padding: 0 10px !important;
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        .navbar .navbar-brand-wrapper,
        .navbar.headerLight .navbar-brand-wrapper {
            height: 62px !important;
            min-height: 62px !important;
            max-height: 62px !important;
            width: auto !important;
            min-width: auto !important;
            max-width: none !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            background: transparent !important;
            border: none !important;
            border-right: none !important;
            flex: 0 0 auto !important;
        }
        .navbar .navbar-brand-wrapper .brand-logo {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .navbar .navbar-brand-wrapper .brand-logo-mini {
            display: none !important;
        }
        .navbar .navbar-brand-wrapper .sidebar-logo,
        .sidebar-logo {
            max-width: 110px !important;
            max-height: 38px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            border-radius: 8px !important;
        }
        .navbar .navbar-menu-wrapper,
        .navbar.headerLight .navbar-menu-wrapper {
            height: 62px !important;
            min-height: 62px !important;
            max-height: 62px !important;
            width: auto !important;
            min-width: auto !important;
            flex: 1 1 auto !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
        }
    }

    @media (max-width: 768px) {
        .navbar .navbar-menu-wrapper {
            width: auto !important;
            background: transparent !important;
        }
        .navbar {
            background: linear-gradient(135deg, #e66136, #ff7b4a) !important;
        }

        .sidebar-logo {
            max-width: 110px !important;
            max-height: 38px !important;
        }

        .check-in-out-container:not(.role-admin) {
            display: flex !important;
        }

        .check-in-out-container.role-admin {
            display: none !important;
        }

        .check-in-out-container:not(.role-admin) #check-out-btn {
            align-items: center;
            padding: 6px 12px !important;
            font-size: 14px !important;
            height: 36px;
            white-space: nowrap;
            border-radius: 18px;
            background: linear-gradient(135deg, #e66136, #ff7b4a) !important;
            color: white !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .check-in-out-container:not(.role-admin) #check-out-btn i {
            font-size: 16px !important;
            margin-right: 5px !important;
        }

        .navbar-nav.ms-auto {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            margin-left: -10px !important;
        }

        .navbar-nav.ms-auto .nav-item {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .notification-dropdown {
            margin-left: 0 !important;
        }

        .user-dropdown.nav-profile {
            margin-left: 0 !important;
        }

        .nav-link-profile {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 4px !important;
        }

        .nav-link.count-indicator {
            padding: 4px !important;
        }

        .navbar-menu-wrapper .navbar-nav.ms-auto>li:not(:last-child) {
            margin-right: 0 !important;
        }
    }

    /* =======================
   TABLET VIEW (768px-1024px)
   ======================= */
    @media (min-width: 768px) and (max-width: 1024px) {

        /* Let flexbox handle width */
        .navbar-menu-wrapper {
            width: auto !important;
            flex: 1 1 auto;
            padding-right: 8px;
            background: transparent !important;
        }

        /* Reduce logo size slightly */
        .sidebar-logo {
            max-width: 110px !important;
            height: auto !important;
        }

        /* Compact greeting font on tablet/laptop */
        .welcome-text {
            font-size: 1.15rem !important;
        }

        /* Check-out button compact */
        .check-in-out-container:not(.role-admin) #check-out-btn {
            align-items: center;
            padding: 6px 12px !important;
            font-size: 14px !important;
            height: 34px;
            border-radius: 18px;
            white-space: nowrap;
        }

        .check-in-out-container i {
            font-size: 16px !important;
            margin-right: 4px;
        }

        /* Right side icons spacing */
        .navbar-nav.ms-auto {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto !important;
        }

        .nav-link-profile,
        .nav-link.count-indicator {
            padding: 6px !important;
        }

        /* DateTime optional: hide if crowded */
        #datetime-display {
            display: none !important;
        }

        .navbar .navbar-brand-wrapper .navbar-toggler {
            font-size: 2rem !important;
        }

        .navbar .navbar-brand-wrapper {
            width: auto !important;
            background: transparent !important;
        }

        .sidebar-offcanvas.active {
            left: 0 !important;
        }

        .sidebar-offcanvas {
            left: -240px !important;
        }

        .navbar .navbar-menu-wrapper .navbar-nav .nav-item.dropdown .navbar-dropdown {
            left: auto;
            width: calc(35% - 40px);
        }
    }


    @media (min-width: 320px) and (max-width: 667px) {
        .check-in-out-container:not(.role-admin) {
            display: flex !important;
        }

        .check-in-out-container.role-admin {
            display: none !important;
        }

        .check-in-out-container:not(.role-admin) #check-out-btn {
            align-items: center;
            padding: 4px 8px !important;
            font-size: 14px !important;
            white-space: nowrap;
        }

        .check-in-out-container:not(.role-admin) #check-out-btn i {
            font-size: 18px !important;
            margin-right: 4px !important;
        }

        .navbar-all-sm {
            padding: 8px 4px !important;
        }

        .navbar-nav.ms-auto {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            margin-left: -10px !important;
        }

        .navbar-nav.ms-auto .nav-item {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .notification-dropdown {
            margin-left: 0 !important;
        }

        .user-dropdown.nav-profile {
            margin-left: 0 !important;
        }

        .nav-link-profile {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 4px !important;
        }

        .nav-link.count-indicator {
            padding: 4px !important;
        }

        .navbar .navbar-brand-wrapper .navbar-toggler {
            font-size: 2rem !important;
        }

        .sidebar-offcanvas.active {
            left: 0 !important;
        }

        .sidebar-offcanvas {
            left: -240px !important;
        }
        .navbar .navbar-brand-wrapper{
            background: transparent !important;
        }
    }
</style>
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-center flex-row">

    <div class="navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3 d-none d-lg-block">
            <button class="navbar-toggler navbar-toggler align-self-center border-0 bg-transparent text-white" type="button" data-bs-toggle="minimize">
                <span class="icon-menu text-white"></span>
            </button>
        </div>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center p-1 me-2 border-0 bg-transparent text-white" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu text-white fs-4"></span>
        </button>
        <div class="d-none d-lg-flex align-items-center">
            <a class="navbar-brand brand-logo m-0 p-0" href="/dashboard">
                <img src="<?= getCompanyLogo(); ?>" alt="logo" class="sidebar-logo" />
            </a>
            <a class="navbar-brand brand-logo-mini m-0 p-0" href="/dashboard">
                <img src="<?= getCompanyLogo(); ?>" alt="logo" class="sidebar-logo" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center navbar-all-sm justify-content-end">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text" style="color: white;">
                    <?php
                    // Set your timezone here (very important!)
                    date_default_timezone_set('Asia/Kolkata'); // Change this to your correct timezone if needed

                    // Fetch user info
                    $username = ucfirst(strtolower(session()->get('username') ?? 'GuestUser'));

                    // Get the hour in 24-hour format
                    $hour = date('H');

                    // Set greeting
                    if ($hour >= 5 && $hour < 12) {
                        echo "Good Morning, <span class='text-capitalize fw-bold' style='color: white; !important' >{$username}</span>";
                    } elseif ($hour >= 12 && $hour < 17) {
                        echo "Good Afternoon, <span class='text-capitalize fw-bold' style='color: white; !important' >{$username}</span>";
                    } elseif ($hour >= 17 && $hour < 21) {
                        echo "Good Evening, <span class='text-capitalize fw-bold' style='color: white;'!important>{$username}</span>";
                    } else {
                        echo "Good Night, <span class='text-capitalize fw-bold' style='color: white;' !important>{$username}</span>";
                    }
                    ?>
                </h1>

            </li>
        </ul>

        <ul class="navbar-nav ms-auto">

            <!-- Check-In Buttons Container -->
            <li class="nav-item d-flex align-items-center gap-2 check-in-out-container <?= $role === 'admin' ? 'role-admin' : '' ?>">
                <!-- Face Check In Button (Hidden - auto modal instead) -->
                <!--
                <button id="face-check-in-btn" class="btn d-none align-items-center" data-bs-toggle="modal" data-bs-target="#faceCheckInModal"
                    style="background: linear-gradient(135deg, #e66136, #ff7b4a); color: white; border: none; border-radius: 20px; padding: 6px 14px; font-size: 13px; font-weight: 500; box-shadow: 0 2px 8px rgba(230,97,54,0.3);">
                    <i class="mdi mdi-face-recognition me-1" style="font-size: 18px;"></i>
                    <span class="d-none d-md-inline">Face</span> Check In
                </button>
                -->

                <!-- Regular Check In Button (Hidden - using face check-in) -->
                <button id="check-in-btn" class="btn chekbtnsm px-2 py-1" style="display: none;">
                    <i class="mdi mdi-alarm-check me-2 fs-5"></i> Check In
                </button>

                <!-- Check Out Button -->
                <button id="check-out-btn" class="btn border-0 chekbtnsm px-2 py-1" style="display: none;">
                    <!-- <i class="mdi mdi-alarm-off me-2 fs-2" style="color: #e66136;"></i> Check Out -->
                    <i class="mdi mdi-alarm-off me-2 fs-5"></i> Check Out
                </button>
            </li>
            <!-- Branch Dropdown -->
            <?php if (!empty($branchesList) && $role === 'admin'): ?>
            <?php $activeBranch = session()->get('admin_active_branch'); ?>
            <li class="nav-item d-flex align-items-center">
                <select class="form-select shadow-none text-muted fw-semibold" id="navbarBranchSelect" style="background: #ffffff; height: 40px !important;  border: 1px solid #DFDFDF; border-radius: 6px; cursor: pointer; font-family: 'Manrope', sans-serif; font-size: 14px; padding-top: 0; padding-bottom: 0; margin: 0;">
                    <option value="">All Branches</option>
                    <?php foreach ($branchesList as $br): ?>
                        <option value="<?= htmlspecialchars($br['id']) ?>" <?= ($activeBranch == $br['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($br['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <script>
                document.getElementById('navbarBranchSelect').addEventListener('change', function(e) {
                    const branchId = e.target.value;
                    fetch('/api/branches/set-active', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ branch_id: branchId })
                    })
                    .then(r => r.json())
                    .then(() => {
                        window.location.reload();
                    })
                    .catch(console.error);
                });
                </script>
            </li>
            <?php endif; ?>

            <li class="nav-item d-none d-lg-block d-block" id="datetime-display">
                <div class="input-group date datepicker navbar-date-picker">
                    <span class="nav-link text-muted fw-semibold" id="currentDateTime" style="white-space: nowrap;"></span>
                </div>
            </li>
            <!-- Notification -->
            <li class="nav-item dropdown  d-lg-block d-block notification-dropdown" id="navbarSection">
                <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"></i>
                    <!-- <span class="count"></span> -->
                    <span class="count">0</span> <!-- Updated dynamically -->
                </a>

                <div class="dropdown-menu notifications-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="notificationDropdown">
                    <a class="dropdown-item py-3 border-bottom">
                        <p class="mb-0 fw-medium float-start">You have 4 new notifications </p>
                        <span class="badge badge-pill float-end" style="white-space: nowrap;">View all</span>
                    </a>
                    <a class="dropdown-item py-3 text-center">Loading...</a>
                </div>
            </li>
            <!-- User Dropdown -->
            <li class="nav-item dropdown d-lg-block d-block user-dropdown nav-profile">
                <a class="nav-link nav-link-profile" id="UserDropdown" href="#" aria-expanded="false">

                    <!-- Check if userInfo is stored in session -->
                    <?php if (session()->has('userInfo') && !empty(session()->get('userInfo')['profile_image'])) : ?>
                        <img class="img-xs rounded-circle" src="<?= base_url('upload/' . session()->get('userInfo')['profile_image']) ?>" alt="Profile image">
                    <?php else : ?>
                        <img class="img-xs rounded-circle" src="<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>" alt="Profile image">
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <!-- Check if userInfo is stored in session -->
                        <?php if (session()->has('userInfo') && !empty(session()->get('userInfo')['profile_image'])) : ?>
                            <img class="img-fluid rounded-circle" src="<?= base_url('upload/' . session()->get('userInfo')['profile_image']) ?>" alt="Profile image" style="width: 50px; height: 50px;">
                        <?php else : ?>
                            <img class="img-md rounded-circle" src="<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>" alt="Profile image" style="width: 50px; height: 50px;">
                        <?php endif; ?>

                        <!-- Display username -->

                    </div>
                    <a href="/profile" class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline me-2"></i> My Profile</a>
                    <a href="/change_password" class="dropdown-item"><i class="dropdown-item-icon mdi mdi-lock me-2"></i> Change Password</a>
                    <a href="#" class="dropdown-item logout-link" id="logoutBtn"><i class="dropdown-item-icon mdi mdi-power me-2"></i>Sign Out</a>
                </div>
            </li>
        </ul>
        <!-- <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button> -->
    </div>
</nav>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    function loadNotifications() {
        $.ajax({
            url: '<?= base_url('api/notifications/getNotifications') ?>',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token') // JWT from local storage
            },
            success: function(response) {
                $('.count').text(response.count);

                let dropdown = '';
                if (response.notifications.length > 0) {
                    dropdown += `<div class="dropdown-item py-3 border-bottom d-flex justify-content-between align-items-center">
                     <p class="mb-0 fw-medium float-start">You have ${response.count} new notifications</p>
                        <a href="<?= base_url('/notifications') ?>" class="fw-medium text-decoration-none p-2 rounded" style="background:#E66136;color:white;white-space: nowrap;">View All</a>
                        <a href="#" class="fw-medium text-decoration-none clear-all" style="color:#E66136">Clear All</a>
                    </div>`;
                    response.notifications.forEach(function(notification) {
                        const data = JSON.parse(notification.data);
                        let message = '';
                        let icon = 'mdi-bell-ring'; // default icon

                        switch (data.type) {
                            case 'leave':
                                message = `${data.username} applied for leave`;
                                icon = 'mdi-calendar-remove';
                                break;
                            case 'task':
                                message = `New task assigned to ${data.employee} by ${data.username}`;
                                icon = 'mdi-clipboard-text';
                                break;
                            case 'subtask':
                                message = `New Subtask assigned to ${data.employee} by ${data.username}`;
                                icon = 'mdi-format-list-checkbox'; // suitable for subtask
                                break;
                            case 'subtask_update':
                                message = data.message;;
                                icon = 'mdi-briefcase-plus'; // better for "new task"
                                break;
                            case 'candidate':
                                message = data.message;
                                icon = 'mdi-account-star';
                                break;
                            case 'training':
                                message = `New training assigned to ${data.employee} by ${data.username}`;
                                icon = 'mdi-school';
                                break;
                            case 'interview':
                                message = `Interview scheduled for ${data.candidate_name} by ${data.username}`;
                                icon = 'mdi-calendar-check';
                                break;
                            case 'birthday':
                                message = `Wish ${data.username} a happy birthday ðŸŽ‰`;
                                icon = 'mdi-cake-variant';
                                break;
                            case 'employee':
                                if (data.role === 'hr') {
                                    message = `${data.username} was added as a new HR`;
                                    icon = 'mdi-account-plus'; // you can change icon if you want
                                } else if (data.role === 'employee') {
                                    message = `${data.username} was added as a new Employee`;
                                    icon = 'mdi-account-plus'; // same icon for both, or different if you prefer
                                }
                                break;
                            case 'job':
                                message = data.message;
                                icon = 'mdi-briefcase'; // example icon for job
                                break;
                            case 'onboarding':
                                message = `Onboarding started for ${data.candidate_name} by ${data.username}`;
                                icon = 'mdi-briefcase-check';
                                break;
                            case 'payroll':
                                message = `Payroll processed for ${data.employee} by ${data.username}`;
                                icon = 'mdi-cash-multiple';
                                break;
                            case 'performance':
                                message = `Performance reviewed for ${data.employee} by ${data.username}`;
                                icon = 'mdi-chart-line';
                                break;
                            case 'announcement':
                                message = `New Announcement: ${data.title}`;
                                icon = 'mdi-bullhorn';
                                break;
                            case 'complaint':
                            case 'Complaint':
                                message = `<strong>${data.username}</strong> submitted a Complaint: ${data.subject}`;
                                icon = 'mdi-alert-circle';
                                break;
                            case 'feedback':
                            case 'Feedback':
                                message = `<strong>${data.username}</strong> submitted a Feedback: ${data.subject}`;
                                icon = 'mdi-comment-text-outline';
                                break;
                            case 'leave_status': {
                                // Message is already pre-built on the backend
                                const st = (data.status || '').toLowerCase();
                                message = data.message || `Your leave request has been ${data.status}.`;
                                icon = st === 'approved'
                                    ? 'mdi-calendar-check'       // green-ish check
                                    : st === 'rejected'
                                        ? 'mdi-calendar-remove'  // red-ish remove
                                        : 'mdi-calendar-clock';  // pending
                                break;
                            }
                            default:
                                message = 'New notification';
                                icon = 'mdi-bell-ring';
                        }

                        dropdown += `
                            <a class="dropdown-item preview-item py-3 notification-link"
                            data-id="${notification.id}"
                            data-type="${data.type}"
                            data-task-id="${data.task_id ?? ''}"
                            data-subtask-id="${data.subtask_id ?? ''}"
                            data-user-id="${data.user_id ?? ''}"
                            data-leave-id="${data.leave_id ?? ''}"
                            data-candidate-id="${data.candidate_id ?? ''}">
                                <div class="preview-thumbnail">
                                    <i class="mdi ${icon} m-auto text-dark"></i>
                                </div>
                                <div class="preview-item-content">
                                    <h6 class="preview-subject fw-normal text-dark mb-1">${message}</h6>
                                    <p class="fw-light small-text mb-0">${new Date(notification.created_at).toLocaleString()}</p>
                                </div>
                            </a>
                            `;

                    });

                } else {
                    dropdown += `<a class="dropdown-item py-3 text-center text-muted">No new notifications</a>`;
                }

                $('.notifications-menu').html(dropdown);
            }
        });
    }
    $(document).on('click', '.notification-link', function(e) {
        e.preventDefault(); // prevent default behavior just in case

        const notificationId = $(this).data('id');
        const type = $(this).data('type');
        const leaveId = $(this).data('leave-id');
        const userId = $(this).data('user-id');

        // First mark as read
        $.ajax({
            url: '<?= base_url('api/notifications/markAsRead') ?>/' + notificationId,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            success: function() {
                // Then redirect based on type
                switch (type) {
                    case 'task':
                        window.location.href = '<?= base_url('/taskview') ?>';
                        break;
                    case 'subtask':
                        window.location.href = '<?= base_url('/all_subtask') ?>';
                        break;
                    case 'subtask_update':
                        window.location.href = '<?= base_url('/all_subtask') ?>';
                        break;
                    case 'payroll':
                        window.location.href = '<?= base_url('/payrollview') ?>';
                        break;
                    case 'interview':
                        window.location.href = '<?= base_url('/addinterview') ?>';
                        break;
                    case 'training':
                        window.location.href = '<?= base_url('/trainingview') ?>';
                        break;
                    case 'onboarding':
                        window.location.href = '<?= base_url('/onboardingview') ?>';
                        break;
                    case 'candidate':
                        window.location.href = '<?= base_url('/candidateview') ?>';
                        break;
                        case 'performance':
                            window.location.href = '<?= base_url('/performanceview') ?>';
                            break;
                        case 'announcement':
                            window.location.href = '<?= base_url('/announcements') ?>';
                            break;
                        case 'complaint':
                        case 'feedback':
                            window.location.href = '<?= base_url('/complaints/admin') ?>';
                            break;
                        case 'employee':
                        window.location.href = '<?= base_url('/empview') ?>';
                        break;
                    case 'leave':
                    case 'leave_status':
                        let url = '<?= base_url('/leaveview') ?>';
                        let params = new URLSearchParams();
                        if (userId) params.append('user_id', userId);
                        if (leaveId) params.append('leave_id', leaveId);
                        if (params.toString()) {
                            url += '?' + params.toString();
                        }
                        window.location.href = url;
                        break;
                        <?php if ($role !== 'employee') : ?>

                        case 'birthday':
                            window.location.href = '<?= base_url('/empview') ?>';
                            break;
                        <?php endif; ?>

                    default:
                        window.location.href = '<?= base_url('/dashboard') ?>';
                }
            }
        });
    });


    // Load on page load and every 30 seconds
    $(document).ready(function() {
        loadNotifications();
        setInterval(loadNotifications, 30000);

        // Initialize push notifications for admin users and employees (for checkout reminders)
        <?php if (in_array($role, ['admin', 'employee', 'hr'])) : ?>

            // Initialize immediately on login
            initializePushNotifications().then(() => {
                console.log('âœ… initializePushNotifications completed');
            }).catch(error => {
                console.error('âŒ initializePushNotifications failed:', error);
            });

            // Also initialize on visibility change (for desktop browsers)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    console.log('ðŸ“± Page visible, checking subscription...');
                    initializePushNotifications().catch(console.error);
                }
            });

        <?php else : ?>
            // Other users: Do NOT subscribe to push notifications
            console.log('ðŸ‘¤ User role: <?= $role ?> - Push notifications only for admin, employee, and HR');
        <?php endif; ?>
    });
</script>

<script>
    function checkTokenExpiration(response) {
        if (response.status === 401) {
            // Token has expired or is invalid, redirect to login
            localStorage.removeItem('token'); // Clear the expired token from localStorage
            window.location.href = '/login'; // Redirect to the login page
        }
    }

    // Define logout function - wait for API response before redirecting
    function logout() {
        console.log('Logout function called');

        // Immediately disable the link and show loading state
        const logoutLinks = document.querySelectorAll('.logout-link');
        logoutLinks.forEach(link => {
            link.style.pointerEvents = 'none';
            link.style.opacity = '0.5';
            link.innerHTML = '<i class="mdi mdi-loading mdi-spin me-2"></i>Signing Out...';
        });

        const token = localStorage.getItem('token');
        localStorage.removeItem('token');

        const redirectToLogin = () => {
            console.log('Redirecting to login...');
            window.location.href = '/login';
        };

        if (token) {
            // Call logout API and wait for it
            fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(() => {
                console.log('Logout API success');
                redirectToLogin();
            })
            .catch((err) => {
                console.error('Logout API error:', err);
                redirectToLogin(); // Still redirect even if API fails
            });
            
            // Safety timeout: redirect anyway if API takes too long (> 2 seconds)
            setTimeout(redirectToLogin, 2000);
        } else {
            redirectToLogin();
        }

        return false;
    }

    // Make it globally accessible
    window.logout = logout;

    // Add event listeners using event delegation (works even if elements are added dynamically)
    document.addEventListener('click', function(e) {
        // Check if clicked element or its parent has logout-link class
        const logoutLink = e.target.closest('.logout-link');
        if (logoutLink) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('Logout link clicked via event delegation');
            logout();
            return false;
        }
    }, true); // Use capture phase to catch it early

    // Also add direct listeners as backup
    function attachLogoutListeners() {
        document.querySelectorAll('.logout-link').forEach(function(link) {
            // Remove any existing listeners by cloning
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);

            newLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                console.log('Logout link clicked via direct listener');
                logout();
                return false;
            }, true);

            // Also handle mousedown as backup
            newLink.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Logout link mousedown');
                logout();
                return false;
            }, true);
        });
    }

    // Attach listeners when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachLogoutListeners);
    } else {
        attachLogoutListeners();
    }

    // Also try after a delay to catch any dynamically added elements
    setTimeout(attachLogoutListeners, 500);
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token'); // JWT token from login

        const headers = {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        };

        // Function to check attendance status and update buttons
        const updateAttendanceStatus = () => {
            fetch('/api/attendance/status', {
                    headers
                })
                .then(response => response.json())
                .then(data => {
                    const checkInBtn = document.getElementById('check-in-btn');
                    const checkOutBtn = document.getElementById('check-out-btn');

                    if (data.role === 'hr' || data.role === 'employee') {
                        if (data.data === 'not_checked_in') {
                            // User has NOT checked in - open mandatory face check-in modal
                            checkInBtn.style.display = 'none';
                            checkOutBtn.style.display = 'none';

                            if (data.is_remote) {
                                // Remote worker - skip face scan, show normal button
                                checkInBtn.style.display = 'flex';
                                checkInBtn.style.alignItems = 'center';
                            } else if (data.has_face_photo) {
                                // Auto-open face check-in modal if user has face photo
                                openMandatoryFaceCheckIn();
                            } else {
                                // No face photo - show regular check-in
                                checkInBtn.style.display = 'flex';
                                checkInBtn.style.alignItems = 'center';
                            }
                        } else if (data.data === 'checked_in') {
                            // Already checked in - show check out button
                            checkInBtn.style.display = 'none';
                            checkOutBtn.style.display = 'flex';
                            checkOutBtn.style.alignItems = 'center';
                        } else if (data.data === 'checked_out') {
                            // Checked out - need to check in again
                            checkInBtn.style.display = 'none';
                            checkOutBtn.style.display = 'none';

                            if (data.is_remote) {
                                // Remote worker - skip face scan, show normal button
                                checkInBtn.style.display = 'flex';
                                checkInBtn.style.alignItems = 'center';
                            } else if (data.has_face_photo) {
                                openMandatoryFaceCheckIn();
                            } else {
                                checkInBtn.style.display = 'flex';
                                checkInBtn.style.alignItems = 'center';
                            }
                        }
                    } else {
                        // Admin - no check-in required
                        checkInBtn.style.display = 'none';
                        checkOutBtn.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error('Attendance status error:', err);
                });
        };

        // Open mandatory face check-in modal
        function openMandatoryFaceCheckIn() {
            // Add page blocker overlay
            if (!document.getElementById('checkin-blocker')) {
                const blocker = document.createElement('div');
                blocker.id = 'checkin-blocker';
                blocker.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1040; display: flex; align-items: center; justify-content: center;';
                blocker.innerHTML = '<div style="text-align: center; color: white;"><i class="mdi mdi-lock" style="font-size: 48px; margin-bottom: 10px;"></i><h5>Please complete Face Check-In to continue</h5></div>';
                document.body.appendChild(blocker);
            }

            const modal = new bootstrap.Modal(document.getElementById('faceCheckInModal'), {
                backdrop: 'static', // Cannot close by clicking outside
                keyboard: false // Cannot close with ESC key
            });
            modal.show();
        }

        // Remove blocker after successful check-in
        function removeCheckInBlocker() {
            const blocker = document.getElementById('checkin-blocker');
            if (blocker) blocker.remove();
        }

        // Make updateAttendanceStatus available globally
        window.updateAttendanceStatus = updateAttendanceStatus;

        function fetchIpLocation(callback) {
            fetch('https://ipapi.co/json/')
                .then(response => response.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        callback(data.latitude, data.longitude, 'ip');
                    } else {
                        callback(null, null, 'failed');
                    }
                })
                .catch(() => callback(null, null, 'failed'));
        }

        function getGPSLocationWithRetryAndFallback(callback, retriesLeft = 3) {
            if (!navigator.geolocation) {
                fetchIpLocation(callback);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                pos => {
                    callback(pos.coords.latitude, pos.coords.longitude, 'gps');
                },
                error => {
                    if (error.code === error.PERMISSION_DENIED) {
                        Swal.fire({
                            title: 'Location Required',
                            text: 'Please enable location permissions to punch attendance. If denied, your punch will be marked as location missing.',
                            icon: 'warning'
                        });
                        // Save with not_captured
                        callback(null, null, 'not_captured');
                        return;
                    }

                    if (retriesLeft > 0) {
                        console.log('Retrying GPS fetch... attempts left:', retriesLeft);
                        setTimeout(() => getGPSLocationWithRetryAndFallback(callback, retriesLeft - 1), 1000);
                    } else {
                        // Fallback to IP
                        console.log('GPS failed, falling back to IP location');
                        fetchIpLocation(callback);
                    }
                },
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
            );
        }

        function getCurrentTime() {
            const now = new Date();
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            return now.toLocaleTimeString('en-US', options); // Returns something like "14:25:30"
        }

        // Event listener for check-in
        document.getElementById('check-in-btn').addEventListener('click', function() {
            const btn = this;
            if (btn.disabled) return;

            Swal.fire({
                title: 'Check-in Confirmation',
                text: `Do you want to confirm check-in?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Check In',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'hr-btnbg',
                    cancelButton: 'hr-btnbg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;

                    // â”€â”€ Capture GPS, then POST to check-in API â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                    const doCheckIn = (lat, lng, status) => {
                        const payload = {};
                        if (lat !== null && lng !== null) {
                            payload.latitude  = lat;
                            payload.longitude = lng;
                        }
                        if (status) {
                            payload.location_status = status;
                        }

                        fetch('/api/attendance/checkin', {
                            method: 'POST',
                            headers: headers,
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                title: 'Success',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: { confirmButton: 'hr-btnbg' }
                            });
                            updateAttendanceStatus();
                        })
                        .catch(err => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Unable to check in.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: { confirmButton: 'hr-btnbg' }
                            });
                        })
                        .finally(() => { btn.disabled = false; });
                    };

                    // Try to get GPS location
                    getGPSLocationWithRetryAndFallback((lat, lng, status) => {
                        doCheckIn(lat, lng, status);
                    });
                }
            });
        });

        document.getElementById('check-out-btn').addEventListener('click', function() {
            const btn = this;
            const checkInBtn = document.getElementById('check-in-btn');
            if (btn.disabled) return;

            Swal.fire({
                title: 'Checkout Confirmation',
                text: `Do you want to confirm checkout?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Check Out',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'hr-btnbg',
                    cancelButton: 'hr-btnbg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;

                    // â”€â”€ Capture GPS, then POST to check-out API â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                    const doCheckOut = (lat, lng, status) => {
                        const payload = {};
                        if (lat !== null && lng !== null) {
                            payload.latitude  = lat;
                            payload.longitude = lng;
                        }
                        if (status) {
                            payload.location_status = status;
                        }

                        fetch('/api/attendance/checkout', {
                            method: 'POST',
                            headers: headers,
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Checkout response:', data);

                            const apiStatus  = data.status  || (data.data && data.data.status);
                            const message = data.message || (data.data && data.data.message) || '';

                            if (apiStatus === 'success') {
                                btn.style.display = 'none';
                                btn.disabled = false;
                                if (checkInBtn) {
                                    checkInBtn.style.display = 'flex';
                                    checkInBtn.style.alignItems = 'center';
                                }
                                Swal.fire({
                                    title: 'Checked Out!',
                                    text: message || 'You have been checked out successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: { confirmButton: 'hr-btnbg' }
                                });
                                setTimeout(() => updateAttendanceStatus(), 1000);

                            } else if (message.toLowerCase().includes('no active') || message.toLowerCase().includes('no check-in')) {
                                btn.style.display = 'none';
                                btn.disabled = false;
                                if (checkInBtn) {
                                    checkInBtn.style.display = 'flex';
                                    checkInBtn.style.alignItems = 'center';
                                }
                                Swal.fire({
                                    title: 'Already Checked Out',
                                    text: 'You have already checked out for this session. Please check in again to start a new session.',
                                    icon: 'info',
                                    confirmButtonText: 'OK',
                                    customClass: { confirmButton: 'hr-btnbg' }
                                });
                                updateAttendanceStatus();

                            } else {
                                Swal.fire({
                                    title: 'Checked Out!',
                                    text: message || 'You have been checked out successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: { confirmButton: 'hr-btnbg' }
                                });
                                setTimeout(() => updateAttendanceStatus(), 1000);
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            console.error('Checkout network error:', err);
                            Swal.fire({
                                title: 'Network Error',
                                text: 'Could not reach the server. Please check your connection and try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: { confirmButton: 'hr-btnbg' }
                            });
                        });
                    };

                    // Try to get GPS location
                    getGPSLocationWithRetryAndFallback((lat, lng, status) => {
                        doCheckOut(lat, lng, status);
                    });
                }
            });
        });

        // Initial status update on page load
        updateAttendanceStatus();
    });
</script>
<script>
    setInterval(function() {
        $.ajax({
            url: '<?= base_url('api/getCompanyLogo') ?>',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('.sidebar-logo, .navbar-logo, .login-logo').attr('src', response.logo_img);
                }
            }
        });
    }, 30000); // Refresh every 30 seconds
</script>
<script>
    function updateDateTime() {
        const now = new Date();
        const options = {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const formattedDateTime = now.toLocaleString('en-US', options);
        document.getElementById('currentDateTime').textContent = formattedDateTime;
    }

    // Update every second
    setInterval(updateDateTime, 1000);
    updateDateTime(); // initial call


    $(document).on('click', '.clear-all', function(e) {
        e.preventDefault();

        $.ajax({
            url: '<?= base_url('notifications/clearAll') ?>',
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            success: function(response) {
                loadNotifications(); // Reload notifications after clearing
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to clear notifications',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileToggle = document.getElementById('UserDropdown');
        if (!profileToggle) return;

        const dropdownRoot = profileToggle.closest('.dropdown');
        const dropdownMenu = dropdownRoot ? dropdownRoot.querySelector('.dropdown-menu') : null;
        if (!dropdownMenu) return;

        profileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (window.bootstrap && bootstrap.Dropdown) {
                bootstrap.Dropdown.getOrCreateInstance(profileToggle).toggle();
                return;
            }

            const isOpen = dropdownMenu.classList.toggle('show');
            profileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function(e) {
            if (!dropdownRoot.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                profileToggle.setAttribute('aria-expanded', 'false');
                if (window.bootstrap && bootstrap.Dropdown) {
                    const instance = bootstrap.Dropdown.getInstance(profileToggle);
                    if (instance) instance.hide();
                }
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Append overlay manually
        if (!$('.sidebar-overlay').length) {
            $('body').append('<div class="sidebar-overlay"></div>');
        }

        // Toggle offcanvas manually
        $(document).on('click', '#mobile_btn', function() {
            $('.offcanvas').addClass('show');
            $('body').addClass('offcanvas-opened');
            $('.sidebar-overlay').addClass('opened');
            return false;
        });

        // Hide when overlay clicked
        $(document).on('click', '.sidebar-overlay', function() {
            $('.offcanvas').removeClass('show');
            $('body').removeClass('offcanvas-opened');
            $(this).removeClass('opened');
        });
    });
</script>

<!-- Face Check-In Modal -->
<div class="modal fade" id="faceCheckInModal" tabindex="-1" aria-labelledby="faceCheckInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
            <!-- Header -->
            <div class="modal-header border-0 py-3" style="background: linear-gradient(135deg, #e66136 0%, #ff7b4a 100%);">
                <div class="d-flex align-items-center text-white">
                    <div class="me-3" style="background: rgba(255,255,255,0.2); border-radius: 10px; padding: 8px;">
                        <i class="mdi mdi-face-recognition" style="font-size: 22px;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="faceCheckInModalLabel">Face Check-In</h6>
                        <small style="opacity: 0.85; font-size: 11px;">Mandatory Attendance Verification</small>
                    </div>
                </div>
                <!-- Close button -->
                <button type="button" id="face-modal-close-btn" onclick="closeFaceCheckInModal()" aria-label="Close"
                    style="margin-left: auto; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 18px; line-height: 1;">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body p-3" style="background: #f8f9fa;">
                <!-- Mandatory Check-in Notice -->
                <div class="alert alert-warning mb-3 d-flex align-items-center" style="border-radius: 10px; border: none; font-size: 12px; padding: 10px 14px;">
                    <i class="mdi mdi-shield-lock me-2" style="font-size: 18px;"></i>
                    <span><strong>Mandatory:</strong> You must verify your face to access the system.</span>
                </div>

                <!-- Status Message -->
                <div id="face-status" class="alert alert-info mb-3 d-flex align-items-center" style="border-radius: 10px; border: none; font-size: 13px; padding: 10px 14px;">
                    <i class="mdi mdi-information-outline me-2" style="font-size: 18px;"></i>
                    <span id="face-status-text">Initializing camera...</span>
                </div>

                <!-- Camera Container -->
                <div class="text-center">
                    <div id="camera-container" style="position: relative; display: inline-block; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.15); background: #000;">
                        <video id="face-video" width="380" height="285" autoplay muted playsinline style="display: block; transform: scaleX(-1);"></video>
                        <canvas id="face-canvas" width="380" height="285" style="position: absolute; top: 0; left: 0; transform: scaleX(-1);"></canvas>

                        <!-- Face Overlay Guide -->
                        <div id="face-guide" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 150px; height: 190px; border: 2px dashed rgba(255,255,255,0.5); border-radius: 50%; pointer-events: none;"></div>

                        <!-- Bottom overlay text -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.6)); padding: 12px; text-align: center;">
                            <small class="text-white" style="font-size: 11px;"><i class="mdi mdi-crosshairs me-1"></i>Position face in oval</small>
                        </div>
                    </div>
                </div>

                <!-- Match Result -->
                <div id="face-match-result" class="mt-3 p-2 text-center" style="display: none; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center justify-content-between mb-1 px-2">
                        <small class="text-muted">Match Score</small>
                        <span id="match-percentage" class="fw-bold" style="color: #e66136; font-size: 14px;">0%</span>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 6px; background: #eee;">
                        <div id="match-progress" class="progress-bar" role="progressbar" style="width: 0%; border-radius: 6px; transition: all 0.3s;"></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 justify-content-between gap-2 py-3" style="background: white;">
                <button type="button" id="cancel-face-btn" class="btn btn-light px-4" onclick="closeFaceCheckInModal()" style="border-radius: 20px; font-size: 13px;">
                    <i class="mdi mdi-close me-1"></i>Cancel
                </button>
                <a href="#" class="btn btn-light px-4 logout-link"><i class="dropdown-item-icon mdi mdi-power me-2"></i>Sign Out</a>
                <button type="button" id="capture-face-btn" class="btn px-4 text-white" disabled
                    style="border-radius: 20px; font-size: 13px; background: linear-gradient(135deg, #e66136, #ff7b4a); border: none;">
                    <i class="mdi mdi-check-circle me-1"></i>Verify & Check In
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Face Recognition Check-In with Verification -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    (function() {
        let video = null;
        let stream = null;
        let canvas = null;
        let ctx = null;
        let registeredFaceDescriptor = null;
        let faceVerified = false;
        let detectionInterval = null;
        let hasError = false; // Flag to prevent status updates when error occurs

        // When modal opens
        document.getElementById('faceCheckInModal')?.addEventListener('shown.bs.modal', async function() {
            hasError = false; // Reset error flag when modal opens
            faceVerified = false;
            document.getElementById('capture-face-btn').disabled = true;
            await initFaceRecognition();
        });

        // When modal closes (covers all close paths)
        document.getElementById('faceCheckInModal')?.addEventListener('hidden.bs.modal', function() {
            stopCamera();
            // Remove blocker overlay if it exists
            const blocker = document.getElementById('checkin-blocker');
            if (blocker) blocker.remove();
        });

        // Global helper: close modal + stop camera immediately (no confirmation)
        window.closeFaceCheckInModal = function() {
            stopCamera();
            const blocker = document.getElementById('checkin-blocker');
            if (blocker) blocker.remove();
            const modalEl = document.getElementById('faceCheckInModal');
            const bsModal = bootstrap.Modal.getInstance(modalEl) ||
                            new bootstrap.Modal(modalEl);
            bsModal.hide();
        };

        async function initFaceRecognition() {
            video = document.getElementById('face-video');
            canvas = document.getElementById('face-canvas');
            ctx = canvas.getContext('2d');

            updateStatus('Starting camera...', 'info');

            // Start camera first
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: 380,
                        height: 285
                    },
                    audio: false
                });
                video.srcObject = stream;
                await video.play();
            } catch (error) {
                updateStatus('âŒ Camera access denied. Please allow camera.', 'danger');
                return;
            }

            updateStatus('Loading face recognition AI (please wait)...', 'info');

            // Load face-api models
            try {
                const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            } catch (err) {
                updateStatus('âŒ Failed to load AI. Please refresh and try again.', 'danger');
                return;
            }

            updateStatus('Loading your registered face photo...', 'info');

            // Load registered face
            const loaded = await loadRegisteredFace();
            if (!loaded) {
                return;
            }

            // Start face detection loop
            updateStatus('Look at the camera. Verifying your face...', 'info');
            startFaceDetection();
        }

        async function loadRegisteredFace() {
            try {
                const response = await fetch('/api/attendance/get-face-photo', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();

                if (data.status !== 'success' || !data.face_photo) {
                    updateStatus('âŒ No face photo registered. Ask admin to upload your photo first.', 'danger');
                    return false;
                }

                // Load the registered photo and get face descriptor
                const img = await faceapi.fetchImage(data.face_photo);
                const detection = await faceapi.detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    updateStatus('âŒ Cannot detect face in registered photo. Ask admin to re-upload clearer photo.', 'danger');
                    return false;
                }

                registeredFaceDescriptor = detection.descriptor;
                return true;

            } catch (error) {
                console.error('Error loading registered face:', error);
                updateStatus('âŒ Error loading your face data.', 'danger');
                return false;
            }
        }

        function startFaceDetection() {
            // Clear any existing interval
            if (detectionInterval) {
                clearInterval(detectionInterval);
            }

            detectionInterval = setInterval(async () => {
                // Don't update status if there's an error
                if (hasError) return;

                if (!video || video.paused || video.ended || !registeredFaceDescriptor) return;

                try {
                    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    if (detection) {
                        // Draw face box
                        const box = detection.detection.box;
                        ctx.strokeStyle = '#28a745';
                        ctx.lineWidth = 3;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);

                        // Compare faces
                        const distance = faceapi.euclideanDistance(registeredFaceDescriptor, detection.descriptor);
                        const matchPercent = Math.round(Math.max(0, (1 - distance) * 100));

                        // Show match percentage
                        document.getElementById('face-match-result').style.display = 'block';
                        document.getElementById('match-progress').style.width = matchPercent + '%';
                        document.getElementById('match-percentage').textContent = 'Match: ' + matchPercent + '%';

                        // Check if face matches (threshold: 45%)
                        if (matchPercent >= 45) {
                            document.getElementById('match-progress').style.backgroundColor = '#28a745';
                            document.getElementById('capture-face-btn').disabled = false;
                            faceVerified = true;
                            // Only update status if no error
                            if (!hasError) {
                                updateStatus('âœ“ Face verified! (' + matchPercent + '% match) Click to check in.', 'success');
                            }
                        } else {
                            document.getElementById('match-progress').style.backgroundColor = '#dc3545';
                            document.getElementById('capture-face-btn').disabled = true;
                            faceVerified = false;
                            // Only update status if no error
                            if (!hasError) {
                                updateStatus('Face not matched (' + matchPercent + '%). Position face properly.', 'warning');
                            }
                        }
                    } else {
                        document.getElementById('face-match-result').style.display = 'none';
                        document.getElementById('capture-face-btn').disabled = true;
                        faceVerified = false;
                        // Only update status if no error
                        if (!hasError) {
                            updateStatus('No face detected. Look at the camera.', 'warning');
                        }
                    }
                } catch (err) {
                    console.error('Detection error:', err);
                }
            }, 500);
        }

        function stopCamera() {
            if (detectionInterval) {
                clearInterval(detectionInterval);
                detectionInterval = null;
            }
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            if (video) video.srcObject = null;
            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('capture-face-btn').disabled = true;
            faceVerified = false;
        }

        function updateStatus(message, type, persist = false) {
            const statusDiv = document.getElementById('face-status');
            const icons = {
                'info': 'mdi-information-outline',
                'success': 'mdi-check-circle-outline',
                'warning': 'mdi-alert-outline',
                'danger': 'mdi-alert-circle-outline'
            };

            // If there's already an error and this is not an error message, don't update
            if (hasError && type !== 'danger') {
                return;
            }

            // Set error flag for danger messages
            if (type === 'danger') {
                hasError = true;
                // Stop face detection interval to prevent overwriting error
                if (detectionInterval) {
                    clearInterval(detectionInterval);
                    detectionInterval = null;
                }
            } else {
                hasError = false;
            }

            statusDiv.className = 'alert alert-' + type + ' mb-3';
            statusDiv.innerHTML = '<i class="mdi ' + icons[type] + ' me-2"></i>' + message;

            // For error messages, make them more prominent and persistent
            if (type === 'danger') {
                statusDiv.style.border = '2px solid #dc3545';
                statusDiv.style.fontWeight = '600';
                statusDiv.style.animation = 'none'; // Remove any animations
            } else {
                statusDiv.style.border = 'none';
                statusDiv.style.fontWeight = 'normal';
            }
        }

        // Function to get user's current location with improved accuracy for desktop
        function getUserLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation is not supported by your browser'));
                    return;
                }

                // For better accuracy on desktop, try to get multiple readings
                let positions = [];
                let watchId = null;
                let timeoutId = null;
                let isResolved = false;

                const cleanup = () => {
                    if (watchId !== null) {
                        navigator.geolocation.clearWatch(watchId);
                    }
                    if (timeoutId !== null) {
                        clearTimeout(timeoutId);
                    }
                };

                // Set overall timeout (30 seconds)
                timeoutId = setTimeout(() => {
                    if (!isResolved) {
                        cleanup();
                        if (positions.length > 0) {
                            // Use the most accurate position we got
                            positions.sort((a, b) => (a.coords.accuracy || Infinity) - (b.coords.accuracy || Infinity));
                            const bestPosition = positions[0];
                            isResolved = true;
                            resolve({
                                latitude: bestPosition.coords.latitude,
                                longitude: bestPosition.coords.longitude,
                                accuracy: bestPosition.coords.accuracy
                            });
                        } else {
                            isResolved = true;
                            reject(new Error('Location request timed out. Please ensure location services are enabled.'));
                        }
                    }
                }, 30000);

                // Detect if desktop (no touch support or larger screen)
                const isDesktop = !('ontouchstart' in window) || window.innerWidth > 1024;

                // For desktop, use more aggressive settings to force actual location services
                const geoOptions = {
                    enableHighAccuracy: true,
                    timeout: isDesktop ? 30000 : 15000, // Longer timeout for desktop
                    maximumAge: 0 // Always get fresh location, don't use cached
                };

                // Try getCurrentPosition first (faster for mobile)
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        // If accuracy is good (within 100m), use it immediately
                        if (position.coords.accuracy && position.coords.accuracy <= 100) {
                            cleanup();
                            if (!isResolved) {
                                isResolved = true;
                                resolve({
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude,
                                    accuracy: position.coords.accuracy
                                });
                            }
                        } else {
                            // Accuracy is poor, start watching for better readings
                            positions.push(position);
                            startWatching();
                        }
                    },
                    (error) => {
                        // If getCurrentPosition fails, try watchPosition
                        startWatching();
                    },
                    geoOptions
                );

                function startWatching() {
                    const isDesktop = !('ontouchstart' in window) || window.innerWidth > 1024;

                    // Watch position to get multiple readings and improve accuracy
                    // For desktop, wait longer and collect more readings to get better location
                    watchId = navigator.geolocation.watchPosition(
                        (position) => {
                            positions.push(position);

                            // If we get a high accuracy reading (within 50m), use it
                            if (position.coords.accuracy && position.coords.accuracy <= 50) {
                                cleanup();
                                if (!isResolved) {
                                    isResolved = true;
                                    resolve({
                                        latitude: position.coords.latitude,
                                        longitude: position.coords.longitude,
                                        accuracy: position.coords.accuracy
                                    });
                                }
                            }

                            // For desktop, collect more readings (5-7) to improve accuracy
                            // For mobile, 3 readings is usually enough
                            const minReadings = isDesktop ? 5 : 3;

                            if (positions.length >= minReadings) {
                                cleanup();
                                if (!isResolved) {
                                    // Sort by accuracy and use the best one
                                    positions.sort((a, b) => (a.coords.accuracy || Infinity) - (b.coords.accuracy || Infinity));
                                    const bestPosition = positions[0];

                                    // For desktop with poor accuracy, try to average the best positions
                                    if (isDesktop && bestPosition.coords.accuracy > 500) {
                                        // Average the top 3 most accurate positions
                                        const topPositions = positions.slice(0, Math.min(3, positions.length));
                                        const avgLat = topPositions.reduce((sum, p) => sum + p.coords.latitude, 0) / topPositions.length;
                                        const avgLng = topPositions.reduce((sum, p) => sum + p.coords.longitude, 0) / topPositions.length;
                                        const avgAccuracy = topPositions.reduce((sum, p) => sum + (p.coords.accuracy || 0), 0) / topPositions.length;

                                        isResolved = true;
                                        resolve({
                                            latitude: avgLat,
                                            longitude: avgLng,
                                            accuracy: avgAccuracy
                                        });
                                    } else {
                                        isResolved = true;
                                        resolve({
                                            latitude: bestPosition.coords.latitude,
                                            longitude: bestPosition.coords.longitude,
                                            accuracy: bestPosition.coords.accuracy
                                        });
                                    }
                                }
                            }
                        },
                        (error) => {
                            cleanup();
                            if (!isResolved) {
                                let errorMessage = 'Unable to get your location. ';
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage += 'Location permission denied. Please enable location access in your browser settings.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage += 'Location information unavailable. On desktop, ensure WiFi is enabled for better location accuracy.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage += 'Location request timed out. Please ensure location services are enabled and try again.';
                                        break;
                                    default:
                                        errorMessage += 'An unknown error occurred.';
                                        break;
                                }
                                isResolved = true;
                                reject(new Error(errorMessage));
                            }
                        }, {
                            enableHighAccuracy: true,
                            timeout: isDesktop ? 30000 : 20000, // Longer timeout for desktop
                            maximumAge: 0 // Always get fresh location
                        }
                    );
                }
            });
        }

        // Check-in button - only works if face is verified
        document.getElementById('capture-face-btn')?.addEventListener('click', async function() {
            if (!faceVerified) {
                updateStatus('âŒ Face not verified. Cannot check in.', 'danger');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking in...';

            try {
                // Get user's location first
                updateStatus('Getting your location (this may take a moment on desktop)...', 'info');
                let userLocation = null;
                try {
                    userLocation = await getUserLocation();

                    // Show accuracy information
                    if (userLocation.accuracy) {
                        const accuracyMeters = Math.round(userLocation.accuracy);
                        if (accuracyMeters > 500) {
                            updateStatus(`Location accuracy: Â±${accuracyMeters}m (may be less accurate on desktop)`, 'warning');
                        } else {
                            updateStatus(`Location accuracy: Â±${accuracyMeters}m`, 'info');
                        }
                    }
                } catch (locationError) {
                    // If location is not available, still try to check in (backend will handle it)
                    console.warn('Location error:', locationError.message);
                    updateStatus('Location not available. Attempting check-in...', 'warning');
                }

                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || 380;
                canvas.height = video.videoHeight || 285;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = canvas.toDataURL('image/jpeg', 0.8);

                const now = new Date();
                const fullDateTime = now.toISOString().slice(0, 19).replace('T', ' ');

                updateStatus('Submitting check-in...', 'info');

                // Prepare request body with location if available
                const requestBody = {
                    face_image: imageData,
                    check_in_time: fullDateTime
                };

                if (userLocation) {
                    requestBody.latitude = userLocation.latitude;
                    requestBody.longitude = userLocation.longitude;
                    // Include accuracy if available (helps backend handle desktop browsers with poor GPS)
                    if (userLocation.accuracy !== undefined && userLocation.accuracy !== null) {
                        requestBody.location_accuracy = userLocation.accuracy;
                    }
                }

                // Timeout so we never stay stuck on "Checking in..." (e.g. slow server or hang)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 60000);

                const response = await fetch('/api/attendance/face-checkin', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestBody),
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                if (!response.ok) {
                    let errMsg = 'Check-in failed. Please try again.';
                    try {
                        const errBody = await response.json();
                        if (errBody && errBody.message) errMsg = errBody.message;
                    } catch (_) {}
                    updateStatus(errMsg, 'danger', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="mdi mdi-camera me-1"></i>Verify & Check In';
                    return;
                }

                let data;
                try {
                    data = await response.json();
                } catch (parseErr) {
                    console.error('Parse error:', parseErr);
                    updateStatus('Invalid response from server. Try again.', 'danger', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="mdi mdi-camera me-1"></i>Verify & Check In';
                    return;
                }

                if (data.status === 'success') {
                    updateStatus('âœ“ Check-in successful! Redirecting...', 'success');
                    btn.innerHTML = '<i class="mdi mdi-check-circle me-1"></i>Done';
                    stopCamera();

                    const blocker = document.getElementById('checkin-blocker');
                    if (blocker) blocker.remove();

                    const modalEl = document.getElementById('faceCheckInModal');
                    if (bootstrap.Modal.getInstance(modalEl)) bootstrap.Modal.getInstance(modalEl).hide();

                    if (typeof window.updateAttendanceStatus === 'function') window.updateAttendanceStatus();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success! âœ“',
                            text: data.message || 'Face verified and checked in!',
                            icon: 'success',
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).catch(function() {});
                    }

                    setTimeout(() => location.reload(), 800);
                } else {
                    const errorMessage = (data && data.message) ? data.message : 'Check-in failed.';
                    updateStatus(errorMessage, 'danger', true);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="mdi mdi-camera me-1"></i>Verify & Check In';
                }
            } catch (error) {
                console.error('Error:', error);
                const isTimeout = error.name === 'AbortError';
                updateStatus(isTimeout ? 'Request timed out. Please try again.' : 'Network error. Try again.', 'danger', true);
                btn.disabled = false;
                btn.innerHTML = '<i class="mdi mdi-camera me-1"></i>Verify & Check In';
            }
        });
    })();
</script>

<!-- Push Notification Registration Script -->
<script>
    // Initialize Push Notifications
    async function initializePushNotifications() {

        // Check if browser supports service workers and push notifications
        if (!('serviceWorker' in navigator)) {
            console.error('âŒ Service Worker not supported in this browser');
            return;
        }

        if (!('PushManager' in window)) {
            console.error('âŒ Push Manager not supported in this browser');
            return;
        }

        // Check notification permission first
        if (!('Notification' in window)) {
            console.error('âŒ Notifications are not supported in this browser');
            return;
        }

        let permission = Notification.permission;

        // If permission is not granted, request it
        if (permission === 'default') {
            try {
                permission = await Notification.requestPermission();
            } catch (error) {
                console.error('âŒ Error requesting notification permission:', error);
                return;
            }
        }

        // If permission is denied, don't proceed
        if (permission !== 'granted') {
            console.error('âŒ Notification permission denied. Permission:', permission);
            // Show a message to user
            showNotificationPermissionMessage();
            return;
        }

        try {
            // Register service worker with proper scope
            const swPath = '<?= base_url("service-worker.js") ?>';

            const registration = await navigator.serviceWorker.register(swPath, {
                scope: '/'
            });

            // Wait for service worker to be ready
            await navigator.serviceWorker.ready;

            // Check notification permission status

            // Check if already subscribed
            let subscription = await registration.pushManager.getSubscription();

            // If not subscribed, subscribe
            if (!subscription) {

                // Get VAPID public key from server
                try {
                    const response = await fetch('/api/push/public-key', {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('token')
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to get public key: ' + response.status);
                    }

                    const data = await response.json();

                    if (data.status === 'success' && data.publicKey) {
                        subscription = await subscribeUser(registration, data.publicKey);
                        if (subscription) {
                            hideNotificationPermissionMessage();
                        } else {
                            console.error('âŒ Failed to create subscription');
                        }
                    } else {
                        console.error('âŒ Invalid public key response:', data);
                    }
                } catch (error) {
                    console.error('âŒ Error getting public key:', error);
                    console.error('Error details:', error.message, error.stack);
                }
            } else {
                const verified = await verifySubscription(subscription);
                if (verified) {} else {
                    await sendSubscriptionToServer(subscription);
                }
                hideNotificationPermissionMessage();
            }
        } catch (error) {
            console.error('Error initializing push notifications:', error);
            console.error('Error details:', error.message, error.stack);
        }
    }

    function showNotificationPermissionMessage() {
        if (document.getElementById('notification-permission-message')) {
            return;
        }

        const isMobile = window.innerWidth <= 768;
        const messageDiv = document.createElement('div');
        messageDiv.id = 'notification-permission-message';
        messageDiv.className = 'notification-permission-toast';
        messageDiv.style.cssText = isMobile 
            ? 'position: fixed; bottom: 75px; left: 15px; right: 15px; background: #e66136; color: white; padding: 12px 14px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); z-index: 10000; max-width: calc(100% - 30px); margin: 0 auto;'
            : 'position: fixed; bottom: 25px; right: 25px; background: #e66136; color: white; padding: 14px 18px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.2); z-index: 10000; max-width: 320px;';
        
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px; position: relative;">
                <i class="mdi mdi-bell-ring-outline" style="font-size: 22px; flex-shrink: 0;"></i>
                <div style="flex: 1; padding-right: 18px;">
                    <strong style="font-size: 13px; display: block; line-height: 1.2;">Enable Notifications</strong>
                    <small style="font-size: 11px; opacity: 0.9; line-height: 1.2; display: inline-block;">Click to enable push notifications for check-in/out alerts</small>
                </div>
                <button onclick="requestNotificationPermission()" style="background: white; color: #e66136; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 12px; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    Enable
                </button>
                <button type="button" onclick="hideNotificationPermissionMessage()" title="Close" style="background: transparent; border: none; color: white; position: absolute; top: -6px; right: -6px; font-size: 18px; cursor: pointer; line-height: 1; opacity: 0.85; padding: 2px;">
                    &times;
                </button>
            </div>
        `;
        document.body.appendChild(messageDiv);
    }

    // Hide notification permission message
    function hideNotificationPermissionMessage() {
        const messageDiv = document.getElementById('notification-permission-message');
        if (messageDiv) {
            messageDiv.remove();
        }
    }

    // Subscribe user to push notifications
    async function subscribeUser(registration, publicKey) {
        try {
            if (Notification.permission !== 'granted') {
                return null;
            }

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey)
            });

            const saved = await sendSubscriptionToServer(subscription);

            if (saved) {
                return subscription;
            } else {
                console.error('âŒâŒâŒ FAILED to save subscription to server!');
                console.error('âŒâŒâŒ This is why database is empty!');
                console.error('âŒ Check network tab for API errors');
                return null;
            }
        } catch (error) {
            console.error('Error subscribing to push notifications:', error);
            console.error('Error details:', error.message);

            // If subscription fails due to permission, show message
            if (error.message && error.message.includes('permission')) {
                showNotificationPermissionMessage();
            }

            return null;
        }
    }

    // Convert VAPID key from base64 to Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Send subscription to server
    async function sendSubscriptionToServer(subscription) {
        try {
            const p256dhKey = subscription.getKey('p256dh');
            const authKey = subscription.getKey('auth');

            if (!p256dhKey || !authKey) {
                console.error('Subscription keys are missing');
                return false;
            }

            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: arrayBufferToBase64(p256dhKey),
                        auth: arrayBufferToBase64(authKey)
                    }
                })
            });

            if (!response.ok) {
                throw new Error('Server responded with status: ' + response.status);
            }

            const data = await response.json();

            if (data.status === 'success') {

                setTimeout(async () => {
                    try {
                        const statusResponse = await fetch('/api/push/status', {
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('token')
                            }
                        });
                        const statusData = await statusResponse.json();

                        if (statusData.count === 0) {
                            console.error('âŒ WARNING: Subscription was not saved to database!');
                            console.error('Response was:', data);
                        } else {}
                    } catch (error) {
                        console.error('Error verifying subscription:', error);
                    }
                }, 1000);

                return true;
            } else {
                console.error('âŒ Failed to save subscription:', data.message);
                console.error('Response data:', data);
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                }
                return false;
            }
        } catch (error) {
            console.error('Error sending subscription to server:', error);
            console.error('Error details:', error.message);
            return false;
        }
    }

    // Verify existing subscription with server
    async function verifySubscription(subscription) {
        try {
            await sendSubscriptionToServer(subscription);
        } catch (error) {
            console.error('Error verifying subscription:', error);
        }
    }

    // Convert ArrayBuffer to Base64
    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    // Request notification permission (called when user clicks enable button)
    async function requestNotificationPermission() {
        if (!('Notification' in window)) {
            Swal.fire({
                icon: 'warning',
                title: 'Not Supported',
                text: 'Notifications are not supported in this browser',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        try {
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                hideNotificationPermissionMessage();

                // Now initialize push notifications
                await initializePushNotifications();

                // Show success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Notifications Enabled!',
                        text: 'You will now receive push notifications for employee check-ins and check-outs.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'hr-btnbg'
                        }
                    });
                }
            } else if (permission === 'denied') {
                Swal.fire({
                    icon: 'error',
                    title: 'Permission Denied',
                    text: 'Notification permission was denied. Please enable it in your browser settings to receive push notifications.',
                    confirmButtonColor: '#d33'
                });
            } else {
                console.log('Notification permission dismissed');
            }
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error requesting notification permission. Please try again.',
                confirmButtonColor: '#d33'
            });
        }
    }

    // Make function globally available
    window.requestNotificationPermission = requestNotificationPermission;

    // Test desktop notification directly (without push)
    window.testDesktopNotification = function() {
        if (Notification.permission === 'granted') {
            const notif = new Notification('Desktop Test Notification', {
                body: 'If you see this, desktop notifications are working!',
                icon: '/favicon.ico',
                tag: 'test-desktop'
            });
            console.log('âœ… Desktop notification sent');
            setTimeout(() => notif.close(), 5000);
        } else {
            console.error('âŒ Permission not granted. Run: Notification.requestPermission()');
        }
    };

    // Test notification function (for debugging)
    window.testPushNotification = async function() {
        try {
            const response = await fetch('/api/push/test', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            });
            const data = await response.json();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: data.status === 'success' ? 'Test Sent!' : 'Error',
                    text: data.message || JSON.stringify(data.result),
                    icon: data.status === 'success' ? 'success' : 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'hr-btnbg'
                    }
                });
            }
        } catch (error) {
            console.error('Error testing notification:', error);
        }
    };

    // Dynamic Navbar Scroll Detection for Brand Logo Sizing
    function handleNavbarScroll() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;
        if (window.scrollY > 20) {
            navbar.classList.add('is-scrolled');
        } else {
            navbar.classList.remove('is-scrolled');
        }
    }
    window.addEventListener('scroll', handleNavbarScroll, { passive: true });
    handleNavbarScroll();
</script>
