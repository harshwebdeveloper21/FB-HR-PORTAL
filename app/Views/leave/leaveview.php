<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<link rel="stylesheet" href="<?= base_url(
    env("ImagePath") . "assets/css/calender.css?HI",
) ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .fc-event-time {
        display: none;
    }

    .capitalize-text {
        text-transform: capitalize;
    }

    .fc-event-title {
        text-transform: capitalize;
    }

    /* Active employee highlighting */
    .employee-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .employee-item:hover {
        background-color: #f8f9fa;
    }

    .employee-item.active {
        background-color: #e3f2fd;
        border-left: 3px solid #2196F3;
        font-weight: 600;
    }

    .employee-item.active .profile-pic {
        border: 2px solid #2196F3;
    }

    /* Loading state */
    .calendar-loading {
        position: relative;
        min-height: 400px;
    }

    .calendar-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    /* Skeleton loading for employees */
    .skeleton {
        animation: skeleton-loading 1s linear infinite alternate;
    }

    @keyframes skeleton-loading {
        0% {
            background-color: hsl(200, 20%, 80%);
        }

        100% {
            background-color: hsl(200, 20%, 95%);
        }
    }

    .skeleton-item {
        height: 60px;
        margin-bottom: 8px;
        border-radius: 4px;
    }

    /* View Mode Styles */
    .view-mode-toggle {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
    }

    .view-mode-btn {
        flex: 1;
        padding: 8px 16px;
        border: 2px solid #dee2e6;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 500;
    }

    .view-mode-btn:hover {
        border-color: #e66136;
        background: #f8f9fa;
    }

    .view-mode-btn.active {
        border-color: #e66136;
        background: #e66136;
        color: white;
    }

    .view-mode-btn i {
        font-size: 16px;
    }

    /* Unified View Containers */
    .calendar-view-container {
        display: none;
    }

    .calendar-view-container.active {
        display: block;
    }

    .datewise-view-container {
        display: none;
    }

    .datewise-view-container.active {
        display: block;
    }

    /* Employee Sidebar */
    .employee-sidebar {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .employee-sidebar h5 {
        font-size: 16px;
        margin-bottom: 15px;
        color: #333;
    }

    /* Unified Filter Section */
    .unified-filters {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .filter-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-col {
        flex: 1;
        min-width: 150px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #666;
        margin-bottom: 5px;
        display: block;
    }

    /* Date Scroll Container */
    .date-scroll-wrapper {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .date-scroll-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .date-scroll-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    .date-nav-buttons {
        display: flex;
        gap: 5px;
    }

    .date-nav-btn {
        padding: 4px 8px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .date-nav-btn:hover {
        background: #f8f9fa;
        border-color: #e66136;
    }

    .date-scroll-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 0;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .date-scroll-container::-webkit-scrollbar {
        height: 6px;
    }

    .date-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .date-scroll-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .date-card {
        min-width: 60px;
        flex-shrink: 0;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 9px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .date-card.active {
        border-color: #e66136;
        background: #e7f3ff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
    }

    .date-card.today {
        border-color: #28a745;
    }

    .date-card.has-leave {
        background-color: #fff8e1;
    }

    .date-card-day {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .date-card-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .date-card-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin: 3px auto 0;
    }

    /* Mobile Leave Cards */
    .mobile-leave-list {
        margin-top: 20px;
    }

    .mobile-leave-card {
        background: white;
        border: 1px solid #dee2e6;
        border-left: 4px solid;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .mobile-leave-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .mobile-leave-card.sick-leave {
        border-left-color: #28a745;
    }

    .mobile-leave-card.vacation-leave {
        border-left-color: #d3c75e;
    }

    .mobile-leave-card.casual-leave {
        border-left-color: #b97058ff;
    }

    .mobile-leave-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .mobile-leave-employee {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mobile-leave-employee img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .mobile-leave-name {
        font-weight: bold;
        font-size: 14px;
    }

    .mobile-leave-type {
        font-size: 12px;
        /* color: #666; */
    }

    .mobile-leave-status {
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: bold;
        text-transform: capitalize;
    }

    .mobile-leave-status.pending {
        background-color: #ffc107;
        color: #000;
    }

    .mobile-leave-status.approved {
        background-color: #28a745;
        color: white;
    }

    .mobile-leave-status.rejected {
        background-color: #dc3545;
        color: white;
    }

    .mobile-leave-status.cancelled {
        background-color: #dc3545;
        /* Red background for cancelled */
        color: white;
    }

    .mobile-leave-info {
        font-size: 12px;
        /* color: #666; */
        margin-top: 5px;
    }

    .mobile-leave-reason {
        font-size: 13px;
        /* color: #333; */
        margin-top: 5px;
        font-style: italic;
    }

    /* Tablet Styles (768px - 1024px) */
    @media (max-width: 1024px) and (min-width: 769px) {
        .employee-sidebar {
            max-width: 250px;
        }

        .filter-col {
            min-width: 120px;
        }

        .date-card {
            min-width: 55px;
        }
    }

    /* Mobile Styles (max-width: 768px) */
    @media (max-width: 768px) {
        .card-body {
            padding: 12px;
        }

        .card-body .d-md-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }

        .card-body .d-md-flex.justify-content-between .card-title {
            font-size: 18px !important;
        }

        .card-body .d-md-flex.justify-content-between a {
            width: 100%;
        }

        .view-mode-btn {
            font-size: 13px;
            padding: 6px 12px;
        }

        .filter-row {
            gap: 8px;
        }

        .filter-col {
            min-width: 100%;
        }

        .employee-sidebar {
            padding: 12px;
        }

        .date-card {
            min-width: 50px;
            padding: 6px;
        }

        .date-card-number {
            font-size: 20px;
        }
    }

    /* Small Mobile Styles (max-width: 480px) */
    @media (max-width: 480px) {
        .view-mode-btn {
            font-size: 12px;
            padding: 5px 8px;
        }

        .view-mode-btn span {
            display: none;
        }

        .date-card {
            min-width: 45px;
            padding: 5px;
        }

        .date-card-day {
            font-size: 10px;
        }

        .date-card-number {
            font-size: 18px;
        }
    }

    /* Collapsible Toggle Styles */
    .collapsible-toggle {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .collapsible-toggle i {
        transition: transform 0.3s ease;
    }

    .collapsible-toggle.collapsed i {
        transform: rotate(-90deg);
    }

    .collapsible-content {
        max-height: 1000px;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.3s ease;
        opacity: 1;
    }

    .collapsible-content.collapsed {
        max-height: 0;
        opacity: 0;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-md-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Leave Calendar</h4>
                    <div class="d-md-flex gap-2 align-items-center">
                        <button type="button" id="btnExportLeaves" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-file-excel iconfontsize"></i> Export
                        </button>
                        <a href="/addleave" class="btn hr-btnbg">
                            <i class="mdi mdi-plus icon-leave-size"></i>Add Leave
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- View Mode Toggle -->
                    <div class="col-12 view-mode-toggle">
                        <button class="view-mode-btn active" data-view="datewise" id="datewise-view-btn">
                            <i class="mdi mdi-view-list"></i>
                            <span>Date-wise View</span>
                        </button>
                        <button class="view-mode-btn" data-view="calendar" id="calendar-view-btn">
                            <i class="mdi mdi-calendar"></i>
                            <span>Calendar View</span>
                        </button>
                        <button class="view-mode-btn" data-view="cancelled" id="cancelled-view-btn">
                            <i class="mdi mdi-cancel"></i>
                            <span>Cancelled View</span>
                        </button>
                    </div>

                    <!-- Unified Filters (for Date-wise view) - Collapsible -->
                    <div class="collapsible-toggle collapsed" onclick="toggleSection('leave-filters')">
                        <i class="fas fa-chevron-down"></i>
                        <strong>Filters</strong>
                    </div>
                    <div id="leave-filters-section" class="collapsible-content collapsed">
                        <div class="unified-filters" id="datewise-filters">
                            <div class="filter-row">
                                <div class="filter-col">
                                    <label class="filter-label">Month:</label>
                                    <select id="unified-month-select" class="form-select form-select-sm">
                                        <option value="01">January</option>
                                        <option value="02">February</option>
                                        <option value="03">March</option>
                                        <option value="04">April</option>
                                        <option value="05">May</option>
                                        <option value="06">June</option>
                                        <option value="07">July</option>
                                        <option value="08">August</option>
                                        <option value="09">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                                <div class="filter-col">
                                    <label class="filter-label">Year:</label>
                                    <select id="unified-year-select" class="form-select form-select-sm">
                                        <!-- Years will be populated by JS -->
                                    </select>
                                </div>
                                <div class="filter-col" id="unified-employee-filter-container" style="display: none;">
                                    <label class="filter-label">Employee:</label>
                                    <select id="unified-employee-filter" class="form-select form-select-sm">
                                        <option value="">All Employees</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar View Container -->
                    <div class="calendar-view-container" id="calendar-view">
                        <div class="row">
                            <!-- Left Side: Employee Sidebar -->
                            <div class="col-12 col-md-3 mb-3">
                                <div class="employee-sidebar">
                                    <h5>Team Employees</h5>
                                    <hr style="border-top: 1px solid #c9c4c1; width: 100%;">

                                    <div class="scrollable-container">
                                        <ul class="list-group" id="employee-list">
                                            <!-- Loading skeleton -->
                                            <li class="skeleton skeleton-item"></li>
                                            <li class="skeleton skeleton-item"></li>
                                            <li class="skeleton skeleton-item"></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Calendar -->
                            <div class="col-12 col-md-9">
                                <!-- Leave Legend -->
                                <div class="row g-2 mb-3 leave-legend sm-all-leave-top">
                                    <div class="col-6 col-md-3">
                                        <div class="legend-item d-flex align-items-center">
                                            <span class="legend-box sick-leave me-2"></span>
                                            <span class="legend-label">Sick Leave</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="legend-item d-flex align-items-center">
                                            <span class="legend-box paid-leave me-2"></span>
                                            <span class="legend-label">Paid Leave</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="legend-item d-flex align-items-center">
                                            <span class="legend-box vacation-leave me-2"></span>
                                            <span class="legend-label">Vacation Leave</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="legend-item d-flex align-items-center">
                                            <span class="legend-box casual-leave me-2"></span>
                                            <span class="legend-label">Casual Leave</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendar -->
                                <div class="team-members team-margin">
                                    <div id="calendar" class="full-calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date-wise View Container -->
                    <div class="datewise-view-container active" id="datewise-view">
                        <div class="row">
                            <!-- Employee Sidebar (for larger screens in date-wise view) -->
                            <div class="col-12 col-lg-3 mb-3 d-none d-lg-block">
                                <div class="employee-sidebar">
                                    <h5>Team Employees</h5>
                                    <hr style="border-top: 1px solid #c9c4c1; width: 100%;">

                                    <div class="scrollable-container">
                                        <ul class="list-group" id="datewise-employee-list">
                                            <!-- Will be populated by JS -->
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Scroll and Leave List -->
                            <div class="col-12 col-lg-9">
                                <!-- Date Scroll -->
                                <div class="date-scroll-wrapper">
                                    <div class="date-scroll-header">
                                        <div class="date-scroll-title" id="date-scroll-month-title">January 2026</div>
                                        <div class="date-nav-buttons">
                                            <button class="date-nav-btn" id="date-nav-prev" title="Previous Week">
                                                <i class="mdi mdi-chevron-left"></i>
                                            </button>
                                            <button class="date-nav-btn" id="date-nav-today" title="Today">
                                                <i class="mdi mdi-calendar-today"></i>
                                            </button>
                                            <button class="date-nav-btn" id="date-nav-next" title="Next Week">
                                                <i class="mdi mdi-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="unified-date-scroll" class="date-scroll-container">
                                        <!-- Dates will be rendered here -->
                                    </div>
                                </div>

                                <!-- Leave List -->
                                <div id="unified-leave-list" class="mobile-leave-list">
                                    <!-- Leave cards will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Leaves View Container -->
                    <div class="cancelled-view-container" id="cancelled-view" style="display: none;">
                        <div class="row">
                            <!-- Employee Sidebar (for larger screens in cancelled view) -->
                            <div class="col-12 col-lg-3 mb-3 d-none d-lg-block">
                                <div class="employee-sidebar">
                                    <h5>Team Employees</h5>
                                    <hr style="border-top: 1px solid #c9c4c1; width: 100%;">
                                    <div class="scrollable-container">
                                        <ul class="list-group" id="cancelled-employee-list">
                                            <!-- Will be populated by JS -->
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancelled Leave List -->
                            <div class="col-12 col-lg-9">
                                <h5 class="mb-3 mt-2"><i class="mdi mdi-cancel text-danger"></i> Cancelled Leaves</h5>
                                <div id="cancelled-leave-list" class="mobile-leave-list">
                                    <!-- Leave cards will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Details & Approval Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveModalLabel">Leave Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="csrf_token_name" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <input type="hidden" id="leaveId">
                <p><strong>Employee:</strong> <span id="leaveUser" class="capitalize-text"></span></p>
                <p><strong>Start Date:</strong> <span id="leaveStartDate"></span></p>
                <p><strong>End Date:</strong> <span id="leaveEndDate"></span></p>
                <p><strong>Total Days:</strong> <span id="leaveTotalDays"></span></p>
                <p><strong>Reason:</strong> <span id="leaveReason" class="capitalize-text"></span></p>
                <p><strong>Created By:</strong> <span id="created_by" class="capitalize-text"></span></p>
                <div class="d-flex" id="statusContainer">
                    <p class="mt-2" style="margin-right: 5px;"><strong>Status:</strong></p>
                    <select id="leaveStatus" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <span id="leaveStatusText" class="capitalize-text fw-bold mt-2" style="display: none;"></span>
                </div>

                <!-- Date adjustment for employees (Pending only) -->
                <div id="employeeEditSection"
                    style="display: none; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                    <h6 class="text-primary mb-3"><i class="mdi mdi-calendar-edit me-1"></i> Update Dates</h6>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small">Start Date</label>
                            <input type="date" id="leaveEditStart" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small">End Date</label>
                            <input type="date" id="leaveEditEnd" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                <!-- Admin/HR Button -->
                <button type="button" id="updateLeaveStatus" class="btn hr-btnbg" style="display: none;">Update
                    Status</button>

                <!-- Employee Buttons -->
                <button type="button" id="cancelLeaveBtn" class="btn btn-danger" style="display: none;">Cancel
                    Leave</button>
                <button type="button" id="saveLeaveChangesBtn" class="btn btn-primary" style="display: none;">Save
                    Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script>
    // Toggle function for collapsible sections
    function toggleSection(sectionName) {
        const section = document.getElementById(sectionName + '-section');
        const toggle = section.previousElementSibling;

        section.classList.toggle('collapsed');
        toggle.classList.toggle('collapsed');
    }

    let calendar;
    let userRole = null;
    let currentUserId = null;
    let cachedLeaveData = null;
    let currentEmployeeId = null;
    let isLoading = false;
    let selectedDate = new Date().toISOString().split('T')[0];
    let allLeaveEvents = [];
    let currentMonth = new Date().getMonth() + 1;
    let currentYear = new Date().getFullYear();
    let currentViewMode = 'datewise'; // 'calendar' or 'datewise'

    // View Mode Toggle Functions
    function switchViewMode(mode) {
        currentViewMode = mode;

        // Update button states
        document.querySelectorAll('.view-mode-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-view="${mode}"]`).classList.add('active');

        // Show/hide view containers
        document.getElementById('calendar-view').classList.remove('active');
        document.getElementById('datewise-view').classList.remove('active');
        if (document.getElementById('cancelled-view')) document.getElementById('cancelled-view').classList.remove('active');
        document.getElementById('datewise-filters').style.display = 'none';

        if (mode === 'calendar') {
            document.getElementById('calendar-view').classList.add('active');
            // Refresh calendar
            if (calendar) calendar.render();
        } else if (mode === 'cancelled') {
            if (document.getElementById('cancelled-view')) {
                document.getElementById('cancelled-view').classList.add('active');
                document.getElementById('cancelled-view').style.display = 'block';
            }
            document.getElementById('datewise-filters').style.display = 'block';
            renderCancelledView();
        } else {
            document.getElementById('datewise-view').classList.add('active');
            document.getElementById('datewise-filters').style.display = 'block';
            if (document.getElementById('cancelled-view')) document.getElementById('cancelled-view').style.display = 'none';
            renderDatewiseView();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            contentHeight: 'auto',
            locale: 'en',
            events: [],
            eventClick: function (info) {
                showLeaveModal(info.event);
            },
            eventDidMount: function (info) {
                const durationText = info.event.extendedProps.leave_duration === 'half_day' 
                    ? `0.5 Days (${info.event.extendedProps.half_day_type.replace('_', ' ')})` 
                    : `${info.event.extendedProps.no_of_day} Days`;

                tippy(info.el, {
                    content: `<strong>${info.event.title}</strong><br><small>${durationText}</small><br>${info.event.extendedProps.description}`,
                    placement: 'top',
                    animation: 'fade',
                    allowHTML: true
                });
            },
            loading: function (isLoading) {
                toggleCalendarLoading(isLoading);
            }
        });

        calendar.render();

        // Fetch data in parallel
        Promise.all([fetchLeaveData(), fetchEmployees()])
            .then(() => {
                const urlParams = new URLSearchParams(window.location.search);
                let userId = urlParams.get('user_id');
                const leaveId = urlParams.get('leave_id');

                if (!userId && leaveId) {
                    const fallbackEvent = allLeaveEvents.find(e => e.id == leaveId);
                    if (fallbackEvent && fallbackEvent.extendedProps && fallbackEvent.extendedProps.user_id) {
                        userId = String(fallbackEvent.extendedProps.user_id);
                    }
                }

                // Step 1: Select the employee (this re-filters allLeaveEvents for that user)
                if (userId) {
                    const empItem = $(`.employee-item[data-id="${userId}"]`);
                    if (empItem.length > 0) {
                        selectEmployee(empItem, userId);
                    }
                }

                // Step 2: After employee selection rebuilds events, find & navigate to the leave
                if (leaveId) {
                    // Give selectEmployee time to repopulate allLeaveEvents
                    setTimeout(() => {
                        const targetEventData = allLeaveEvents.find(e => e.id == leaveId);

                        if (targetEventData) {
                            selectedDate = targetEventData.start.substring(0, 10);
                            const d = new Date(selectedDate);
                            currentMonth = d.getMonth() + 1;
                            currentYear = d.getFullYear();

                            const monthSelect = document.getElementById('unified-month-select');
                            if (monthSelect) monthSelect.value = String(currentMonth).padStart(2, '0');
                            const yearSelect = document.getElementById('unified-year-select');
                            if (yearSelect) yearSelect.value = currentYear;

                            if (calendar) calendar.gotoDate(d);

                            // Step 3: Render date view for the specific date
                            if (currentViewMode === 'datewise') {
                                renderDatewiseView();
                            }
                        }

                        // Step 4: Open leave modal
                        setTimeout(() => {
                            const liveEvent = calendar.getEventById(leaveId);
                            if (liveEvent) {
                                showLeaveModal(liveEvent);
                            } else if (targetEventData) {
                                showLeaveModal({
                                    id: targetEventData.id,
                                    extendedProps: targetEventData.extendedProps,
                                    start: targetEventData.start,
                                    end: targetEventData.end
                                });
                            }
                        }, 300);
                    }, 200);
                }
            })
            .catch(error => console.error('Error loading initial data:', error));

        // Initialize view mode toggles
        document.getElementById('calendar-view-btn').addEventListener('click', () => switchViewMode('calendar'));
        document.getElementById('datewise-view-btn').addEventListener('click', () => switchViewMode('datewise'));
        document.getElementById('cancelled-view-btn').addEventListener('click', () => switchViewMode('cancelled'));

        // Initialize unified filters
        initializeUnifiedFilters();

        // Initialize date navigation
        initializeDateNavigation();
    });

    function initializeUnifiedFilters() {
        // Populate year dropdown
        const yearSelect = document.getElementById('unified-year-select');
        if (yearSelect) {
            for (let y = currentYear - 2; y <= currentYear + 1; y++) {
                const option = document.createElement('option');
                option.value = y;
                option.textContent = y;
                if (y === currentYear) option.selected = true;
                yearSelect.appendChild(option);
            }
        }

        // Set current month
        const monthSelect = document.getElementById('unified-month-select');
        if (monthSelect) {
            monthSelect.value = String(currentMonth).padStart(2, '0');
        }

        // Show employee filter only for HR/Admin (will be updated when role is fetched)
        updateEmployeeFilterVisibility();

        if (monthSelect) {
            monthSelect.addEventListener('change', (e) => {
                currentMonth = parseInt(e.target.value);
                if (calendar) calendar.gotoDate(new Date(currentYear, currentMonth - 1, 1));
                if (currentViewMode === 'datewise') {
                    renderDatewiseView();
                } else if (currentViewMode === 'cancelled') {
                    renderCancelledView();
                }
            });
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', (e) => {
                currentYear = parseInt(e.target.value);
                if (calendar) calendar.gotoDate(new Date(currentYear, currentMonth - 1, 1));
                if (currentViewMode === 'datewise') {
                    renderDatewiseView();
                } else if (currentViewMode === 'cancelled') {
                    renderCancelledView();
                }
            });
        }
    }

    function updateEmployeeFilterVisibility() {
        const filterContainer = document.getElementById('unified-employee-filter-container');
        if (filterContainer && (userRole === 'admin' || userRole === 'hr')) {
            filterContainer.style.display = 'block';
        }

        const manageLeavesBtn = document.getElementById('manage-leaves-btn');
        if (manageLeavesBtn && (userRole === 'admin' || userRole === 'hr')) {
            manageLeavesBtn.style.display = 'block';
        }
    }

    function initializeDateNavigation() {
        const prevBtn = document.getElementById('date-nav-prev');
        const nextBtn = document.getElementById('date-nav-next');
        const todayBtn = document.getElementById('date-nav-today');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                const container = document.getElementById('unified-date-scroll');
                if (container) {
                    container.scrollBy({ left: -300, behavior: 'smooth' });
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const container = document.getElementById('unified-date-scroll');
                if (container) {
                    container.scrollBy({ left: 300, behavior: 'smooth' });
                }
            });
        }

        if (todayBtn) {
            todayBtn.addEventListener('click', () => {
                selectedDate = new Date().toISOString().split('T')[0];
                renderDatewiseView();

                // Scroll to today's date
                setTimeout(() => {
                    const activeCard = document.querySelector('.date-card.active');
                    if (activeCard) {
                        activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                }, 100);
            });
        }
    }

    function showLeaveModal(event) {
        $('#leaveModal').modal('show');
        $('#leaveId').val(event.id);
        $('#leaveUser').text(event.extendedProps.user);

        // Set start and end dates
        const sDate = event.extendedProps.actual_start ||
            (typeof event.start === 'string'
                ? event.start.split('T')[0]
                : event.start.getFullYear() + '-' + String(event.start.getMonth() + 1).padStart(2, '0') + '-' + String(event.start.getDate()).padStart(2, '0')
            );

        const eDate = event.extendedProps.actual_end ||
            (typeof event.end === 'string'
                ? event.end.split('T')[0]
                : event.end.getFullYear() + '-' + String(event.end.getMonth() + 1).padStart(2, '0') + '-' + String(event.end.getDate()).padStart(2, '0')
            );
        $('#leaveStartDate').text(sDate);
        $('#leaveEndDate').text(eDate);
        
        // Set total days
        const totalDays = event.extendedProps.no_of_day;
        const leaveDuration = event.extendedProps.leave_duration;
        const halfDayType = event.extendedProps.half_day_type;
        
        let daysText = totalDays == '0.5' ? '0.5 Days' : (totalDays == 1 ? '1 Day' : totalDays + ' Days');
        if (leaveDuration === 'half_day' && halfDayType) {
            const halfText = halfDayType.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            daysText = `0.5 Days (${halfText})`;
        }
        $('#leaveTotalDays').text(daysText);

        $('#leaveReason').text(event.extendedProps.description);
        $('#created_by').text(event.extendedProps.created_by || 'Unknown');
        $('#leaveStatus').val(event.extendedProps.status);
        $('#updateLeaveStatus').data('event', event);

        // Show/hide status controls based on user role
        const isOwnLeave = String(event.extendedProps.user_id) === String(currentUserId);
        if (userRole === 'employee' || isOwnLeave) {
            $('#leaveStatus').hide();
            let status = event.extendedProps.status || 'pending';
            // Capitalize first letter for display
            status = status.charAt(0).toUpperCase() + status.slice(1);
            $('#leaveStatusText')
                .text(status)
                .show();
            $('#updateLeaveStatus').hide();

            // Handle Employee Self-Management (Pending only)
            if (event.extendedProps.status.toLowerCase() === 'pending') {
                $('#cancelLeaveBtn').show();
                $('#employeeEditSection').show();
                $('#saveLeaveChangesBtn').show();

                // Set initial date values for editing using the same robust logic as above
                $('#leaveEditStart').val(sDate);
                $('#leaveEditEnd').val(eDate);
            } else {
                $('#cancelLeaveBtn').hide();
                $('#employeeEditSection').hide();
                $('#saveLeaveChangesBtn').hide();
            }
        } else {
            // Build status options dynamically to ensure 'cancelled' only appears when appropriate
            let statusSelect = $('#leaveStatus');
            statusSelect.empty();
            statusSelect.append('<option value="pending">Pending</option>');
            statusSelect.append('<option value="approved">Approved</option>');
            statusSelect.append('<option value="rejected">Rejected</option>');

            // For HR/Admin, only show 'cancelled' if it was already cancelled
            if (event.extendedProps.status.toLowerCase() === 'cancelled') {
                statusSelect.append('<option value="cancelled">Cancelled</option>');
            }

            statusSelect.show();
            $('#leaveStatusText').hide();
            $('#leaveStatus').val(event.extendedProps.status || 'pending');
            $('#updateLeaveStatus').show();

            // Hide employee-specific actions for Admin/HR
            $('#cancelLeaveBtn').hide();
            $('#employeeEditSection').hide();
            $('#saveLeaveChangesBtn').hide();
        }
    }

    function toggleCalendarLoading(loading) {
        const calendarEl = document.getElementById('calendar');
        if (loading) {
            calendarEl.classList.add('calendar-loading');
        } else {
            calendarEl.classList.remove('calendar-loading');
        }
    }

    function fetchLeaveData(employeeId = null) {
        if (isLoading) return Promise.resolve();

        const token = localStorage.getItem('token');
        let apiUrl = '/api/leave';

        if (employeeId) {
            apiUrl += `?user_id=${employeeId}`;
        }

        isLoading = true;

        return $.ajax({
            url: apiUrl,
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` },
            success: function (response) {
                if (response.status === 'success') {
                    cachedLeaveData = response.data; // Cache the data
                    userRole = response.role;
                    currentUserId = response.current_user_id;
                    updateCalendarEvents(response.data);
                }
            },
            error: function (error) {
                console.error('Error fetching leaves:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load leave data',
                    confirmButtonText: 'OK'
                });
            },
            complete: function () {
                isLoading = false;
            }
        });
    }

    function updateCalendarEvents(leaveData) {
        // Clear existing events efficiently
        calendar.removeAllEvents();

        const events = leaveData.flatMap(leave =>
            leave.leaves.map(leaveItem => {
                let startDate = leaveItem.start_date || new Date().toISOString();
                let endDate = leaveItem.end_date || startDate;
                endDate = `${endDate}T23:59:59`;

                let backgroundColor = getLeaveTypeColor(leaveItem.leave_type);

                return {
                    id: leaveItem.id.toString(),
                    title: `${leave.firstname || leaveItem.firstname} - ${leaveItem.status.charAt(0).toUpperCase() + leaveItem.status.slice(1)}`,
                    start: startDate,
                    end: endDate,
                    backgroundColor: backgroundColor,
                    extendedProps: {
                        user: leave.firstname || leaveItem.firstname,
                        user_id: leave.user_id || leave.id,
                        profile_image: leave.profile_image,
                        description: leaveItem.reason || "No reason provided",
                        status: leaveItem.status,
                        created_by: leaveItem.created_by_username,
                        leave_type: leaveItem.leave_type,
                        actual_start: leaveItem.start_date,
                        actual_end: leaveItem.end_date,
                        no_of_day: leaveItem.no_of_day,
                        leave_duration: leaveItem.leave_duration,
                        half_day_type: leaveItem.half_day_type
                    }
                };
            })
        );

        allLeaveEvents = events;

        calendar.addEventSource(events);

        // Update view mode
        if (currentViewMode === 'datewise') {
            renderDatewiseView();
        } else if (currentViewMode === 'cancelled') {
            renderCancelledView();
        }
    }

    function getLeaveTypeColor(leaveType) {
        const colorMap = {
            'Sick Leave': '#28a745',
            'Paid Leave': '#ff6347',
            'Vacation Leave': '#d3c75e',
            'Casual Leave': '#17a2b8'
        };
        return colorMap[leaveType] || '#007bff';
    }

    function fetchEmployees() {
        const token = localStorage.getItem('token');

        return $.ajax({
            url: '/api/leave',
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` },
            success: function (response) {
                if (response.status === 'success') {
                    userRole = response.role;
                    currentUserId = response.current_user_id;
                    renderEmployeeList(response.data);
                    renderDatewiseEmployeeList(response.data);
                    renderCancelledEmployeeList(response.data);
                    updateEmployeeFilterVisibility();
                }
            },
            error: function (error) {
                console.error('Error fetching employees:', error);
            }
        });
    }

    function renderEmployeeList(employees) {
        let employeeList = $('#employee-list');
        employeeList.empty();
        const baseImagePath = "<?= base_url(env("ImagePath")) ?>";

        // Show "All Employees" option for admin/hr
        if (userRole === 'admin' || userRole === 'hr') {
            let allItem = $(`
                <li class="list-group-item employee-item active" data-id="">
                    <div class="team-member">
                        <img src="${baseImagePath}upload/group2.jpg" alt="All Employee" class="profile-pic bg-light">
                        <div><strong class="employee-name">All</strong></div>
                    </div>
                </li>
            `);
            allItem.on('click', function () {
                selectEmployee($(this), null);
            });
            employeeList.append(allItem);
        }

        // Render individual employees
        employees.forEach(function (employee) {
            let imageUrl = employee.profile_image
                ? `/upload/${employee.profile_image}`
                : `/assets/images/default_avatar.png`;

            let employeeItem = $(`
                <li class="list-group-item employee-item p-1" data-id="${employee.user_id}">
                    <div class="team-member capitalize-text">
                        <img src="${imageUrl}" alt="${employee.firstname}" class="profile-pic">
                        <div><strong class="employee-name">${employee.firstname}</strong></div>
                    </div>
                </li>
            `);

            employeeItem.on('click', function () {
                selectEmployee($(this), employee.user_id);
            });

            employeeList.append(employeeItem);
        });

        // Populate unified employee filter
        populateUnifiedEmployeeFilter(employees);
    }

    function renderDatewiseEmployeeList(employees) {
        const employeeList = document.getElementById('datewise-employee-list');
        if (!employeeList) return;

        employeeList.innerHTML = '';
        const baseImagePath = "<?= base_url(env('ImagePath')) ?>";

        // Show "All Employees" option for admin/hr
        if (userRole === 'admin' || userRole === 'hr') {
            const allItem = document.createElement('li');
            allItem.className = 'list-group-item employee-item active';
            allItem.dataset.id = '';
            allItem.innerHTML = `
                <div class="team-member">
                    <img src="${baseImagePath}upload/group2.jpg" alt="All Employee" class="profile-pic bg-light">
                    <div><strong class="employee-name">All</strong></div>
                </div>
            `;
            allItem.addEventListener('click', () => {
                selectDatewiseEmployee(allItem, null);
            });
            employeeList.appendChild(allItem);
        }

        // Render individual employees
        employees.forEach(employee => {
            const imageUrl = employee.profile_image
                ? `/upload/${employee.profile_image}`
                : `/assets/images/default_avatar.png`;

            const item = document.createElement('li');
            item.className = 'list-group-item employee-item p-1';
            item.dataset.id = employee.user_id;
            item.innerHTML = `
                <div class="team-member capitalize-text">
                    <img src="${imageUrl}" alt="${employee.firstname}" class="profile-pic">
                    <div><strong class="employee-name">${employee.firstname}</strong></div>
                </div>
            `;
            item.addEventListener('click', () => {
                selectDatewiseEmployee(item, employee.user_id);
            });
            employeeList.appendChild(item);
        });
    }

    function populateUnifiedEmployeeFilter(employees) {
        const unifiedFilter = document.getElementById('unified-employee-filter');
        if (!unifiedFilter) return;

        // Only populate if user is HR or Admin
        if (userRole !== 'admin' && userRole !== 'hr') {
            return;
        }

        unifiedFilter.innerHTML = '<option value="">All Employees</option>';

        employees.forEach(employee => {
            const option = document.createElement('option');
            option.value = employee.user_id;
            option.textContent = employee.firstname;
            unifiedFilter.appendChild(option);
        });

        // Add change event listener
        unifiedFilter.addEventListener('change', (e) => {
            applyEmployeeFilter(e.target.value || null);
        });
    }

    // Unified function to apply employee filter across all views and sidebars
    function applyEmployeeFilter(employeeId) {
        currentEmployeeId = employeeId;

        // 1. Update active state in all employee lists
        document.querySelectorAll('.employee-item').forEach(item => {
            if (item.dataset.id == (employeeId || '')) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // 2. Sync the unified filter dropdown
        const unifiedFilter = document.getElementById('unified-employee-filter');
        if (unifiedFilter) {
            unifiedFilter.value = employeeId || '';
        }

        // 3. Update the data source and refresh views
        if (cachedLeaveData) {
            if (!employeeId) {
                updateCalendarEvents(cachedLeaveData);
            } else {
                const filteredData = cachedLeaveData.filter(emp => emp.user_id == employeeId);
                updateCalendarEvents(filteredData);
            }
        } else {
            fetchLeaveData(employeeId);
        }
    }

    function selectDatewiseEmployee(element, employeeId) {
        applyEmployeeFilter(employeeId);
    }

    function selectEmployee($element, employeeId) {
        applyEmployeeFilter(employeeId);
    }

    function selectCancelledEmployee(element, employeeId) {
        applyEmployeeFilter(employeeId);
    }

    $('#updateLeaveStatus').click(function () {
        const token = localStorage.getItem('token');
        const leaveId = $('#leaveId').val();

        if (!leaveId) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Leave ID',
                text: 'Leave ID is required to update the leave status.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const newStatus = $('#leaveStatus').val();
        const event = $(this).data('event');
        const csrfTokenName = $('#csrf_token_name').attr('name');
        const csrfTokenValue = $('#csrf_token_name').val();

        const data = { status: newStatus };
        data[csrfTokenName] = csrfTokenValue;

        $.ajax({
            url: `/api/leave/update/${leaveId}`,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: "application/json",
            headers: { 'Authorization': `Bearer ${token}` },
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        text: 'The leave status has been successfully updated.',
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'hr-btnbg' }
                    }).then(() => {
                        // Refresh data based on current filter
                        cachedLeaveData = null; // Clear cache
                        fetchLeaveData(currentEmployeeId);
                        $('#leaveModal').modal('hide');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to update',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr) {
                console.error('Error updating leave status:', xhr);
                let errorMessage = 'There was an error. Check console for details.';
                if (xhr.responseJSON?.messages?.error) {
                    errorMessage = xhr.responseJSON.messages.error;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    $('#cancelLeaveBtn').click(function () {
        const leaveId = $('#leaveId').val();
        const token = localStorage.getItem('token');

        // Close the details modal immediately
        $('#leaveModal').modal('hide');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to cancel this leave request!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/leave/cancel/${leaveId}`,
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}` },
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire('Cancelled!', response.message, 'success').then(() => {
                                cachedLeaveData = null;
                                // Wait a tiny bit and fetch then re-render
                                fetchLeaveData(currentEmployeeId).then(() => {
                                    if (currentViewMode === 'datewise') {
                                        renderDatewiseView();
                                    }
                                });
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to cancel leave', 'error');
                    }
                });
            }
        });
    });

    // Save Date Changes Functionality
    $('#saveLeaveChangesBtn').click(function () {
        const leaveId = $('#leaveId').val();
        const token = localStorage.getItem('token');
        const startDate = $('#leaveEditStart').val();
        const endDate = $('#leaveEditEnd').val();

        if (!startDate || !endDate) {
            Swal.fire('Error', 'Dates are required', 'error');
            return;
        }

        // Close the details modal immediately
        $('#leaveModal').modal('hide');

        $.ajax({
            url: `/api/leave/update-dates/${leaveId}`,
            method: 'POST',
            data: JSON.stringify({ start_date: startDate, end_date: endDate }),
            contentType: "application/json",
            headers: { 'Authorization': `Bearer ${token}` },
            success: function (response) {
                if (response.status === 'success') {
                    $('#leaveModal').modal('hide');
                    Swal.fire('Updated!', response.message, 'success').then(() => {
                        cachedLeaveData = null;
                        fetchLeaveData(currentEmployeeId);
                    });
                }
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update leave dates', 'error');
            }
        });
    });

    // Date-wise view rendering functions
    function renderDatewiseView() {
        renderDatewiseDateScroll();
        renderDatewiseLeavesForDate();
    }

    function renderDatewiseDateScroll() {
        const container = document.getElementById('unified-date-scroll');
        if (!container) return;

        container.innerHTML = '';

        const today = new Date();
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        const tY = today.getFullYear();
        const tM = String(today.getMonth() + 1).padStart(2, '0');
        const tD = String(today.getDate()).padStart(2, '0');
        const todayDate = `${tY}-${tM}-${tD}`;

        // Update month title
        const monthTitle = document.getElementById('date-scroll-month-title');
        if (monthTitle) {
            monthTitle.textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
        }

        // Show all days of selected month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(currentYear, currentMonth - 1, day);

            const mStr = String(currentMonth).padStart(2, '0');
            const dStr = String(day).padStart(2, '0');
            const dateStr = `${currentYear}-${mStr}-${dStr}`;

            const dayOfWeek = date.getDay();

            // Check if there are leaves on this date
            const hasLeave = allLeaveEvents.some(event => {
                const eventStart = event.start.substring(0, 10);
                const eventEnd = event.end.substring(0, 10);
                return dateStr >= eventStart && dateStr <= eventEnd;
            });

            const dateCard = document.createElement('div');
            dateCard.className = 'date-card';
            if (dateStr === selectedDate) dateCard.classList.add('active');
            if (dateStr === todayDate) dateCard.classList.add('today');
            if (hasLeave) dateCard.classList.add('has-leave');

            dateCard.innerHTML = `
                <div class="date-card-day">${weekdays[dayOfWeek]}</div>
                <div class="date-card-number">${day}</div>
                ${hasLeave ? '<div class="date-card-indicator" style="background-color: #007bff;"></div>' : ''}
            `;

            dateCard.addEventListener('click', () => {
                selectedDate = dateStr;
                renderDatewiseDateScroll();
                renderDatewiseLeavesForDate();
            });

            container.appendChild(dateCard);
        }

        // Scroll to active card
        setTimeout(() => {
            const activeCard = container.querySelector('.date-card.active');
            if (activeCard) {
                activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }, 100);
    }

    function renderDatewiseLeavesForDate() {
        const container = document.getElementById('unified-leave-list');
        if (!container) return;

        container.innerHTML = '';

        // Filter events for selected date
        let filteredEvents = allLeaveEvents.filter(event => {
            const eventStart = event.start.substring(0, 10);
            const eventEnd = event.end.substring(0, 10);
            return selectedDate >= eventStart && selectedDate <= eventEnd;
        });

        // Filter by employee if selected
        if (currentEmployeeId) {
            filteredEvents = filteredEvents.filter(event => event.extendedProps.user_id == currentEmployeeId);
        }

        if (filteredEvents.length === 0) {
            container.innerHTML = '<div class="alert alert-info text-center">No leaves on this date</div>';
            return;
        }

        const baseImagePath = "<?= base_url(env('ImagePath')) ?>";

        filteredEvents.forEach(event => {
            const props = event.extendedProps;
            const imageUrl = props.profile_image
                ? `/upload/${props.profile_image}`
                : `/assets/images/default_avatar.png`;

            // Determine leave type class
            let leaveTypeClass = 'casual-leave';
            if (props.leave_type === 'Sick Leave') leaveTypeClass = 'sick-leave';
            else if (props.leave_type === 'Paid Leave') leaveTypeClass = 'paid-leave';
            else if (props.leave_type === 'Vacation Leave') leaveTypeClass = 'vacation-leave';

            const card = document.createElement('div');
            card.className = `mobile-leave-card ${leaveTypeClass}`;

            // Calculate duration
            const eventStartStr = event.start.substring(0, 10);
            const eventEndStr = event.end.substring(0, 10);

            // Format dates simply as DD/MM/YYYY
            const [sYear, sMonth, sDay] = eventStartStr.split('-');
            const [eYear, eMonth, eDay] = eventEndStr.split('-');
            const displayStart = `${sDay}/${sMonth}/${sYear}`;
            const displayEnd = `${eDay}/${eMonth}/${eYear}`;

            const startDate = new Date(event.start);
            const endDate = new Date(event.end);
            // Calculate duration
            const duration = props.leave_duration === 'half_day' ? 0.5 : (props.no_of_day || 1);
            let durationText = duration == 0.5 ? '0.5 days' : (duration == 1 ? '1 day' : `${duration} days`);

            if (props.leave_duration === 'half_day' && props.half_day_type) {
                const halfDayText = props.half_day_type === 'first_half' ? 'First Half' : 'Second Half';
                durationText = `0.5 days (${halfDayText})`;
            }

            card.innerHTML = `
                <div class="mobile-leave-header">
                    <div class="mobile-leave-employee">
                        <img src="${imageUrl}" alt="${props.user}">
                        <div>
                            <div class="mobile-leave-name capitalize-text">${props.user}</div>
                            <div class="mobile-leave-type">${props.leave_type}</div>
                        </div>
                    </div>
                    <div class="mobile-leave-status ${props.status}">${props.status}</div>
                </div>
                <div class="mobile-leave-info">
                    <strong>Duration:</strong> ${durationText} (${displayStart} - ${displayEnd})
                </div>
                <div class="mobile-leave-reason">
                    "${props.description}"
                </div>
            `;

            card.addEventListener('click', () => {
                showLeaveModal(event);
            });

            container.appendChild(card);
        });
    }

    function renderCancelledEmployeeList(employees) {
        const employeeList = document.getElementById('cancelled-employee-list');
        if (!employeeList) return;

        employeeList.innerHTML = '';
        const baseImagePath = "<?= base_url(env('ImagePath')) ?>";

        // Show "All Employees" option for admin/hr
        if (userRole === 'admin' || userRole === 'hr') {
            const allItem = document.createElement('li');
            allItem.className = 'list-group-item employee-item active';
            allItem.dataset.id = '';
            allItem.innerHTML = `
                <div class="team-member">
                    <img src="${baseImagePath}upload/group2.jpg" alt="All Employee" class="profile-pic bg-light">
                    <div><strong class="employee-name">All</strong></div>
                </div>
            `;
            allItem.addEventListener('click', () => {
                selectCancelledEmployee(allItem, null);
            });
            employeeList.appendChild(allItem);
        }

        // Render individual employees
        employees.forEach(employee => {
            const imageUrl = employee.profile_image
                ? `/upload/${employee.profile_image}`
                : `/assets/images/default_avatar.png`;

            const item = document.createElement('li');
            item.className = 'list-group-item employee-item p-1';
            item.dataset.id = employee.user_id;
            item.innerHTML = `
                <div class="team-member capitalize-text">
                    <img src="${imageUrl}" alt="${employee.firstname}" class="profile-pic">
                    <div><strong class="employee-name">${employee.firstname}</strong></div>
                </div>
            `;
            item.addEventListener('click', () => {
                selectCancelledEmployee(item, employee.user_id);
            });
            employeeList.appendChild(item);
        });
    }

    function selectCancelledEmployee(element, employeeId) {
        document.querySelectorAll('#cancelled-employee-list .employee-item').forEach(item => {
            item.classList.remove('active');
        });
        element.classList.add('active');
        currentEmployeeId = employeeId;
        renderCancelledView();
    }

    function renderCancelledView() {
        const container = document.getElementById('cancelled-leave-list');
        if (!container) return;

        container.innerHTML = '';

        // Filter events for selected month AND year
        let filteredEvents = allLeaveEvents.filter(event => {
            if (event.extendedProps.status.toLowerCase() !== 'cancelled') return false;

            const eventStartStr = event.start.substring(0, 10);
            const [sYear, sMonth, sDay] = eventStartStr.split('-');

            return parseInt(sMonth) === currentMonth && parseInt(sYear) === currentYear;
        });

        // Filter by employee if selected
        if (currentEmployeeId) {
            filteredEvents = filteredEvents.filter(event => event.extendedProps.user_id == currentEmployeeId);
        }

        if (filteredEvents.length === 0) {
            container.innerHTML = '<div class="alert alert-info text-center">No cancelled leaves found</div>';
            return;
        }

        const baseImagePath = "<?= base_url(env('ImagePath')) ?>";

        filteredEvents.forEach(event => {
            const props = event.extendedProps;
            const imageUrl = props.profile_image
                ? `/upload/${props.profile_image}`
                : `/assets/images/default_avatar.png`;

            const card = document.createElement('div');
            card.className = `mobile-leave-card cancelled-leave`;
            card.style.borderLeftColor = '#dc3545'; // Setting explicitly to a soft red for cancelled

            const eventStartStr = event.start.substring(0, 10);
            const eventEndStr = event.end.substring(0, 10);
            const [sYear, sMonth, sDay] = eventStartStr.split('-');
            const [eYear, eMonth, eDay] = eventEndStr.split('-');
            const displayStart = `${sDay}/${sMonth}/${sYear}`;
            const displayEnd = `${eDay}/${eMonth}/${eYear}`;

            card.innerHTML = `
                <div class="mobile-leave-header">
                    <div class="mobile-leave-employee">
                        <img src="${imageUrl}" alt="${props.user}">
                        <div>
                            <div class="mobile-leave-name capitalize-text">${props.user}</div>
                            <div class="mobile-leave-type">${props.leave_type}</div>
                        </div>
                    </div>
                    <div class="mobile-leave-status cancelled">Cancelled</div>
                </div>
                <div class="mobile-leave-info">
                    <strong>Duration:</strong> ${props.leave_duration === 'half_day' ? '0.5 days' : (props.no_of_day || '1 day')} 
                    ${props.leave_duration === 'half_day' && props.half_day_type ? `(${props.half_day_type.replace('_', ' ')})` : ''}
                    (${displayStart} - ${displayEnd})
                </div>
                <div class="mobile-leave-reason">
                    "${props.description}"
                </div>
            `;

            card.addEventListener('click', () => {
                showLeaveModal(event);
            });

            container.appendChild(card);
        });
    }

    // ðŸ“¥ Export Leaves to Excel
    $(document).on('click', '#btnExportLeaves', function () {
        const $btn = $(this);
        const month = $('#unified-month-select').val() || '';
        const year = $('#unified-year-select').val() || '';
        const employeeId = $('#unified-employee-filter').val() || '';
        const viewMode = $('.view-mode-btn.active').data('view') || 'datewise';
        const token = localStorage.getItem('token');

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...');

        const queryParams = new URLSearchParams({
            month: month,
            year: year,
            user_id: employeeId,
            view: viewMode
        });

        fetch(`<?= base_url('api/leave/export') ?>?${queryParams.toString()}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(async response => {
            $btn.prop('disabled', false).html('<i class="mdi mdi-file-excel iconfontsize"></i> Export');
            if (!response.ok) {
                const err = await response.json().catch(() => ({ message: 'Export failed' }));
                throw new Error(err.message || 'Export failed');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Leaves_Report_${new Date().toISOString().slice(0, 10)}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(err => {
            $btn.prop('disabled', false).html('<i class="mdi mdi-file-excel iconfontsize"></i> Export');
            Swal.fire({
                icon: 'error',
                title: 'Export Failed',
                text: err.message || 'Failed to export leaves data.',
                confirmButtonColor: '#d33'
            });
        });
    });
</script>

<?= $this->endSection() ?>
