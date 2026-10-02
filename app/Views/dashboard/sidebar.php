<?php

use App\Services\AuthService;

$request = \Config\Services::request();
$authService = new AuthService($request);
$user = $authService->check();

$role = $user ? $user->role : null;
$isAdmin = ($role === 'admin');
$isHr = ($role === 'hr');
$isAdminOrHr = ($isAdmin || $isHr);
$isBranchAdmin = ($role === 'branch_admin');
$isDeptManager = ($role === 'department_manager');
$isEmployee = ($role === 'employee');
?>

<style>
.sidebar-mobile-header,
#sidebar .sidebar-mobile-header {
  display: none !important;
}
@media (max-width: 991px) {
  .sidebar .nav .nav-item .nav-link {
    padding: 8px 20px !important;
  }
  .sidebar .nav.sub-menu .nav-item .nav-link {
    padding: 6px 20px 6px 40px !important;
  }
  .navbar-toggler .mdi:before {
    font-size: larger;
  }
  .sidebar-mobile-header,
  #sidebar .sidebar-mobile-header {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
  .sidebar-mobile-header a,
  #sidebar .sidebar-mobile-header a {
    width: auto !important;
    max-width: none !important;
    flex: 0 0 auto !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .sidebar-mobile-header img,
  #sidebar .sidebar-mobile-header img {
    max-height: 35px !important;
    width: auto !important;
    max-width: 150px !important;
    object-fit: contain !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
  }
}
@media (min-width: 992px) {
  .sidebar-mobile-header,
  #sidebar .sidebar-mobile-header {
    display: none !important;
  }
}
</style>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-mobile-header justify-content-between align-items-center d-lg-none px-4 py-3" style="border-bottom: 1px solid #f3f3f3; background: #fff;">
    <img src="<?= getCompanyLogo(); ?>" alt="logo" style="max-height: 35px; width: auto; max-width: 150px; object-fit: contain; margin: 0; flex: 0 0 auto;" />
    <a href="javascript:void(0)" data-bs-toggle="offcanvas" class="text-secondary text-decoration-none" style="width: auto; margin: 0; padding: 0; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto;">
      <i class="mdi mdi-close fs-3 text-dark"></i>
    </a>
  </div>
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url('/dashboard') ?>">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <!-- ── Complaints & Announcements ───────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#complaintsMenu" aria-expanded="false" aria-controls="complaintsMenu">
          <i class="menu-icon mdi mdi-message-alert"></i>
          <span class="menu-title">Complaints & Feedback</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="complaintsMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('complaints/admin') ?>">Manage Complaints</a></li>
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('complaints/create') ?>">Add Complaint</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#announcementsMenu" aria-expanded="false" aria-controls="announcementsMenu">
          <i class="menu-icon mdi mdi-bullhorn"></i>
          <span class="menu-title">Announcements</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="announcementsMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('announcements/admin') ?>">Manage Announcements</a></li>
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('announcements/create') ?>">Add Announcement</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>
    <?php if ($isBranchAdmin || $isDeptManager || $isEmployee): ?>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('complaints') ?>">
          <i class="menu-icon mdi mdi-message-alert"></i>
          <span class="menu-title">Complaints & Feedback</span>
        </a>
      </li>
      <?php if (!$isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('announcements') ?>">
          <i class="menu-icon mdi mdi-bullhorn"></i>
          <span class="menu-title">Announcements</span>
        </a>
      </li>
      <?php endif; ?>
    <?php endif; ?>

    <!-- ── Employees Management ─────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
          <i class="menu-icon mdi mdi-account-multiple"></i>
          <span class="menu-title">Employees</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ui-basic">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/empview">Manage Employee</a></li>
            <li class="nav-item"> <a class="nav-link" href="/employee">Add Employee</a></li>
            <li class="nav-item"> <a class="nav-link" href="/gadget-issuance">Gadget Issuance</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Attendance ───────────────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
          <i class="menu-icon mdi mdi-card-text-outline"></i>
          <span class="menu-title">Attendance</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="form-elements">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="/view-calendar">Manage Attendance</a></li>
            <li class="nav-item"><a class="nav-link" href="/attendence">Attendance</a></li>
          </ul>
        </div>
      </li>
    <?php elseif ($isDeptManager): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
          <i class="menu-icon mdi mdi-card-text-outline"></i>
          <span class="menu-title">Attendance</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="form-elements">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="/view-calendar">Dept Attendance</a></li>
            <li class="nav-item"><a class="nav-link" href="/attendence">My Attendance</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Payroll (Admin & HR only) ────────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#tabs" aria-expanded="false" aria-controls="tabs">
          <i class="menu-icon mdi mdi-currency-inr"></i>
          <span class="menu-title">Payroll</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="tabs">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/payrollview">Manage Payroll</a></li>
            <li class="nav-item"> <a class="nav-link" href="/payroll">Add Payroll</a></li>
            <li class="nav-item"> <a class="nav-link" href="/account-detail-view">Manage Account Detail</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Leaves ───────────────────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#cha-rts" aria-expanded="false" aria-controls="cha-rts">
          <i class="menu-icon mdi mdi-calendar"></i>
          <span class="menu-title">Leaves</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="cha-rts">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/leaveview">Review Branch Leaves</a></li>
            <li class="nav-item"> <a class="nav-link" href="/addleave">Apply Leave</a></li>
            <li class="nav-item"> <a class="nav-link" href="/employee-live-request">Employee Leave Request</a></li>
          </ul>
        </div>
      </li>
    <?php elseif ($isDeptManager): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#cha-rts" aria-expanded="false" aria-controls="cha-rts">
          <i class="menu-icon mdi mdi-calendar"></i>
          <span class="menu-title">Leaves</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="cha-rts">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/leaveview">Review Team Leaves</a></li>
            <li class="nav-item"> <a class="nav-link" href="/addleave">Apply Leave</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Recruitment & Onboarding (Admin & HR) ────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
          <i class="menu-icon mdi mdi-table"></i>
          <span class="menu-title lh-base">Recruitment & <br> Onboarding</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="tables">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/jobview">Jobs</a></li>
            <li class="nav-item"> <a class="nav-link" href="/candidateview">Candidates</a></li>
            <li class="nav-item"> <a class="nav-link" href="/addinterview">Interviews Information</a></li>
            <li class="nav-item"> <a class="nav-link" href="/assessment">Interviews Assessments</a></li>
            <li class="nav-item"> <a class="nav-link" href="/candidate-documents">Candidate Documents</a></li>
            <li class="nav-item"> <a class="nav-link" href="/onboardingview">Employees Onboarding</a></li>
            <li class="nav-item"> <a class="nav-link" href="/offer-templates-view">Offer Letter Templates</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Performance ──────────────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#ico-nsss" aria-expanded="false" aria-controls="ico-nsss">
          <i class="menu-icon mdi mdi-gauge"></i>
          <span class="menu-title">Performance</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ico-nsss">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/performanceview">Manage Performance</a></li>
            <li class="nav-item"> <a class="nav-link" href="/performance">Add Reviews</a></li>
            <li class="nav-item"> <a class="nav-link" href="/all-empof-month">Employee of the Month</a></li>
          </ul>
        </div>
      </li>
    <?php elseif ($isDeptManager): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#ico-nsss" aria-expanded="false" aria-controls="ico-nsss">
          <i class="menu-icon mdi mdi-gauge"></i>
          <span class="menu-title">Performance</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ico-nsss">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/performanceview">Team Performance</a></li>
            <li class="nav-item"> <a class="nav-link" href="/performance">Add Review</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Training ─────────────────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
          <i class="menu-icon mdi mdi-account-circle-outline"></i>
          <span class="menu-title">Training</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="auth">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/trainingview">Manage Training</a></li>
            <li class="nav-item"> <a class="nav-link" href="/training">Add Training</a></li>
          </ul>
        </div>
      </li>
    <?php elseif ($isDeptManager): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
          <i class="menu-icon mdi mdi-account-circle-outline"></i>
          <span class="menu-title">Training</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="auth">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/trainingview">Team Training</a></li>
            <li class="nav-item"> <a class="nav-link" href="/training">Add Training</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Task ─────────────────────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin || $isDeptManager): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#task" aria-expanded="false" aria-controls="task">
          <i class="menu-icon mdi mdi-book-open"></i>
          <span class="menu-title">Task</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="task">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/taskview">Manage Task</a></li>
            <li class="nav-item"> <a class="nav-link" href="/task">Add Task</a></li>
            <li class="nav-item"> <a class="nav-link" href="/all_subtask">SubTask List</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Expense ──────────────────────────────────────────── -->
    <?php if ($isAdminOrHr || $isBranchAdmin): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#expense" aria-expanded="false" aria-controls="expense">
          <i class="menu-icon mdi mdi-wallet"></i>
          <span class="menu-title">Expense</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="expense">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('expenses') ?>">Expense List</a></li>
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('expenses/create') ?>">Add Expense</a></li>
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('expenses/categories') ?>">Expense Categories</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Settings (Admin & HR) ────────────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#Settings" aria-expanded="false" aria-controls="Settings">
          <i class="menu-icon mdi mdi-power-settings"></i>
          <span class="menu-title">Settings</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="Settings">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/locationview">Job Location</a></li>
            <li class="nav-item"> <a class="nav-link" href="/addressview">Job Addresses</a></li>
            <li class="nav-item"> <a class="nav-link" href="/SMTPemail">SMTP Mail</a></li>
            <li class="nav-item"> <a class="nav-link" href="/view-rules">Company Rules View</a></li>
            <li class="nav-item"> <a class="nav-link" href="/holidays">Holidays</a></li>
            <li class="nav-item"> <a class="nav-link" href="/notification-settings">Push Notifications</a></li>
            <li class="nav-item"> <a class="nav-link" href="/offer-templates-view">Templates</a></li>
            <li class="nav-item"> <a class="nav-link" href="/digital-signature">Digital Signature</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Branches (Admin & HR) ────────────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#branchesMenu" aria-expanded="false" aria-controls="branchesMenu">
          <i class="menu-icon mdi mdi-office-building"></i>
          <span class="menu-title">Branches</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="branchesMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('/branches') ?>">All Branches</a></li>
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('/branch-managers') ?>">Branch Managers</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Departments (Admin, HR) ─────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#departmentsMenu" aria-expanded="false" aria-controls="departmentsMenu">
          <i class="menu-icon mdi mdi-briefcase"></i>
          <span class="menu-title">Departments</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="departmentsMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('/departmentview') ?>">All Departments</a></li>
            <li class="nav-item"> <a class="nav-link" href="<?= base_url('/department-managers') ?>">Department Managers</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Staff Transfer (Admin & HR) ──────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('/staff-transfer') ?>">
          <i class="menu-icon mdi mdi-swap-horizontal"></i>
          <span class="menu-title">Staff Transfer</span>
        </a>
      </li>
    <?php endif; ?>

    <!-- ── EOM & Experience Letters (Admin, HR) ─────────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#eomletter" aria-expanded="false" aria-controls="eomletter">
          <i class="menu-icon mdi mdi-star-circle"></i>
          <span class="menu-title">EOM</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="eomletter">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/all-empof-month">All EOM</a></li>
            <li class="nav-item"> <a class="nav-link" href="/addemp-month-performance">EOM Generate</a></li>
            <li class="nav-item"> <a class="nav-link" href="/emp-month-view">EOM Templates</a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#exp" aria-expanded="false" aria-controls="exp">
          <i class="menu-icon mdi mdi-file-account-outline"></i>
          <span class="menu-title lh-base">Experience <br> Letter</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="exp">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/exprience-templates-view">Experience Template</a></li>
            <li class="nav-item"> <a class="nav-link" href="/generate-letter">Generate Letter</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Reports ──────────────────────────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#report" aria-expanded="false" aria-controls="report">
          <i class="menu-icon mdi mdi-image-filter-none"></i>
          <span class="menu-title">Report</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="report">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/empReport">Employee Report</a></li>
            <li class="nav-item"> <a class="nav-link" href="/leaveReport">Leave Report</a></li>
            <li class="nav-item"> <a class="nav-link" href="/salaryReport">Payrolls Report</a></li>
            <li class="nav-item"> <a class="nav-link" href="/attendanceReport">Attendance Report</a></li>
            <li class="nav-item"> <a class="nav-link" href="/performReport">Performance Report</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Masters ──────────────────────────────────────────── -->
    <?php if ($isAdminOrHr): ?>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#Masters" aria-expanded="false" aria-controls="Masters">
          <i class="menu-icon mdi mdi-database"></i>
          <span class="menu-title">Masters</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="Masters">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="/countryview">Country</a></li>
            <li class="nav-item"><a class="nav-link" href="/stateView">State</a></li>
            <li class="nav-item"><a class="nav-link" href="/cityview">City</a></li>
            <li class="nav-item"><a class="nav-link" href="/designationview">Designations</a></li>
            <li class="nav-item"><a class="nav-link" href="/leavetypeview">Leave Types</a></li>
          </ul>
        </div>
      </li>
    <?php endif; ?>

    <!-- ── Employee Portal (Regular Staff) ──────────────────── -->
    <?php if ($isEmployee): ?>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('/view-calendar') ?>">
          <i class="menu-icon mdi mdi-card-text-outline"></i>
          <span class="menu-title">Attendance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('/leaveview') ?>">
          <i class="menu-icon mdi mdi-calendar"></i>
          <span class="menu-title">Leaves</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/performanceview">
          <i class="menu-icon mdi mdi-gauge"></i>
          <span class="menu-title">Performance</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/trainingview">
          <i class="menu-icon mdi mdi-currency-usd"></i>
          <span class="menu-title">Training</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('expenses') ?>">
          <i class="menu-icon mdi mdi-wallet"></i>
          <span class="menu-title">Expense</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#subtasks" aria-expanded="false" aria-controls="subtasks">
          <i class="menu-icon mdi mdi-book-open"></i>
          <span class="menu-title">Task</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="subtasks">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="/taskview">Task</a></li>
            <li class="nav-item"> <a class="nav-link" href="/all_subtask">SubTask List</a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('/company-rules') ?>">
          <i class="menu-icon mdi mdi-file-document"></i>
          <span class="menu-title">Company Rule</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('/company-holidays') ?>">
          <i class="menu-icon mdi mdi-calendar-star"></i>
          <span class="menu-title">Company Holidays</span>
        </a>
      </li>
    <?php endif; ?>

    <!-- ── Resignation & Exit ──────────────────────────────── -->
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#resignationMenu" aria-expanded="false" aria-controls="resignationMenu">
        <i class="menu-icon mdi mdi-exit-to-app"></i>
        <span class="menu-title">Resignation & Exit</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="resignationMenu">
        <ul class="nav flex-column sub-menu">
          <!-- Every user can see My Resignation -->
          <li class="nav-item"><a class="nav-link" href="<?= base_url('/resignation') ?>">My Resignation</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('/resignation/my-handover') ?>">My Handover Tasks</a></li>
          <?php if ($isAdminOrHr || $isBranchAdmin || $isDeptManager): ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('/resignation/manager') ?>">Manager Approvals</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('/resignation/clearance') ?>">Clearance Approvals</a></li>
          <?php endif; ?>
          <?php if ($isAdminOrHr): ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('/resignation/hr') ?>">All Resignations (HR)</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </li>

    <!-- ── Geofence Testing ──────────────────────────────── -->
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url('/geofence/test') ?>">
        <i class="menu-icon mdi mdi-crosshairs-gps text-danger"></i>
        <span class="menu-title text-danger fw-bold">Geofence Test</span>
      </a>
    </li>

  </ul>
</nav>
