<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .capitalize-text {
        text-transform: capitalize;
    }

    .attendance-cell {
        cursor: pointer;
    }

    .bg-saturday-off {
        background-color: #fff3cd;
    }

    .calendar-container {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #dee2e6;
        border: 1px solid #dee2e6;
    }

    .calendar-day {
        background-color: white;
        min-height: 120px;
        max-height: 200px;
        padding: 8px;
        position: relative;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .calendar-day-header {
        font-weight: bold;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e9ecef;
    }

    .calendar-day-number {
        font-size: 14px;
        color: #333;
    }

    .calendar-day-name {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
    }

    .calendar-day.sunday {
        background-color: #f8d7da;
    }

    .calendar-day.saturday-off {
        background-color: #fff3cd;
    }

    .calendar-day.holiday {
        background-color: #d1ecf1;
    }

    .calendar-day.today {
        border: 2px solid #ff6b35;
    }

    .calendar-day.future {
        background-color: #f8f9fa;
    }

    .employee-attendance-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 2px 4px;
        margin-bottom: 4px;
        font-size: 11px;
        border-radius: 3px;
        background-color: #f8f9fa;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .employee-attendance-item img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: cover;
    }

    .attendance-status {
        font-weight: bold;
        font-size: 10px;
    }

    .attendance-status.present {
        color: #28a745;
    }

    .attendance-status.absent {
        color: #dc3545;
    }

    .attendance-status.half-day {
        color: #ffc107;
    }

    .attendance-status.working {
        color: #007bff;
    }

    .attendance-status.late {
        color: #6c757d;
    }

    .weekday-header {
        background-color: #000;
        color: white;
        padding: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
    }

    .holiday-label {
        background-color: #17a2b8;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
    }

    .saturday-off-label {
        background-color: #ffc107;
        color: #000;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
    }

    .sunday-label {
        background-color: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        margin-bottom: 4px;
        display: block;
    }

    .overtime-badge {
        background-color: #ffc107;
        color: #000;
        padding: 1px 3px;
        border-radius: 2px;
        font-size: 8px;
        margin-left: 4px;
    }

    .calendar-scroll {
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Mobile Horizontal Scroll Calendar Styles */
    .mobile-date-scroll {
        display: block;
        margin-bottom: 20px;
    }

    .date-scroll-container {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 15px 0;
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
        border-color: #007bff;
        background: #e7f3ff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
    }

    .date-card.today {
        border-color: #28a745;
    }

    .date-card.sunday {
        background-color: #ffe5e5;
    }

    .date-card.saturday-off {
        background-color: #fff8e1;
    }

    .date-card.holiday {
        background-color: #e0f7fa;
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

    /* .date-card-month {
        font-size: 11px;
        color: #888;
    } */

    .date-card-label {
        margin-top: 5px;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
    }

    .date-card-label.holiday {
        background-color: #17a2b8;
        color: white;
    }

    .date-card-label.saturday-off {
        background-color: #ffc107;
        color: #000;
    }

    .date-card-label.sunday {
        background-color: #dc3545;
        color: white;
    }

    .mobile-attendance-list {
        margin-top: 20px;
    }

    .mobile-employee-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mobile-employee-card img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }

    .mobile-employee-info {
        flex: 1;
    }

    .mobile-employee-name {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .mobile-employee-times {
        font-size: 12px;
        color: #666;
    }

    .mobile-employee-status {
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }

    .mobile-employee-status.present {
        background-color: #28a745;
        color: white;
    }

    .mobile-employee-status.absent {
        background-color: #dc3545;
        color: white;
    }

    .mobile-employee-status.half-day {
        background-color: #ffc107;
        color: #000;
    }

    .mobile-employee-status.working {
        background-color: #007bff;
        color: white;
    }

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

    /* Button Styles */
    .btn {
        font-size: 13px;
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 5px;
        text-align: center;
        display: inline-block;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        color: white;
    }

    .hr-btnbg {
        background-color: #E66136 !important;
        color: white !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        font-weight: 600 !important;
        border: none !important;
        box-shadow: 0 4px 6px -1px rgba(230, 97, 54, 0.2), 0 2px 4px -1px rgba(230, 97, 54, 0.1) !important;
        transition: all 0.2s ease !important;
    }

    .hr-btnbg:hover {
        background-color: #d45932 !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(230, 97, 54, 0.3), 0 4px 6px -2px rgba(230, 97, 54, 0.15) !important;
    }

    /* View Toggle Buttons */
    .view-toggle-container {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        justify-content: center;
    }

    .view-toggle-btn {
        padding: 8px 20px;
        border: 2px solid #ff6b35;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .view-toggle-btn.active {
        background: #ff6b35;
        color: white;
    }

    .view-toggle-btn:hover {
        background: #ff6b35;
        color: white;
        border-color: #ff6b35;
    }

    .calendar-view-section {
        margin-bottom: 20px;
    }

    .view-section-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .card-title {
            font-size: 18px !important;
        }

        .btn {
            font-size: 13px;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 5px;
            white-space: nowrap;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .hr-btnbg {
            background-color: #ff6b35;
            color: white;
        }

        .view-toggle-btn {
            padding: 6px 12px;
            font-size: 12px;
        }

        .view-toggle-btn i {
            font-size: 11px;
        }

        /* Hide Legend section on mobile */
        #legend-section,
        .collapsible-toggle[onclick*="legend"] {
            display: none !important;
        }

        /* Statistics Cards - Display in 2x2 grid on mobile */
        .row.mb-4 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* gap: 10px; */
            margin-bottom: 12px;
        }

        .row.mb-4 .col-md-3 {
            margin-bottom: 0;
            padding-left: 5px;
            padding-right: 5px;
        }

        .row.mb-4 .card {
            margin-bottom: 0;
        }

        .row.mb-4 .card-body {
            padding: 12px 10px;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .row.mb-4 .card-body h6 {
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .row.mb-4 .card-body h3 {
            font-size: 24px;
            margin-bottom: 0;
            font-weight: bold;
            line-height: 1.2;
        }

        /* Legend - Smaller text and wrap */
        .d-flex.flex-wrap.gap-3 {
            gap: 6px !important;
        }

        .d-flex.flex-wrap.gap-3 p {
            font-size: 10px;
            margin-bottom: 3px !important;
        }

        .d-flex.flex-wrap.gap-3 p .fw-bold {
            font-size: 9px;
        }

        /* Reduce spacing between sections on mobile */
        .row.mb-3 {
            margin-bottom: 10px !important;
        }

        /* Filter Section - Stack vertically on mobile */
        .row.mb-3 .col-md-4,
        .row.mb-3 .col-md-6 {
            margin-bottom: 10px;
        }

        .row.mb-3 .d-flex.gap-2 {
            flex-wrap: wrap;
        }

        .row.mb-3 label {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .row.mb-3 .form-control,
        .row.mb-3 select {
            font-size: 12px;
            padding: 6px;
        }
    }

    /* Tablet Styles */
    @media (min-width: 769px) and (max-width: 1024px) {
        .calendar-day {
            min-height: 100px;
            max-height: 150px;
            padding: 6px;
        }

        .calendar-day-number {
            font-size: 12px;
        }

        .calendar-day-name {
            font-size: 10px;
        }

        .employee-attendance-item {
            font-size: 10px;
            padding: 2px 3px;
        }

        .employee-attendance-item img {
            width: 18px;
            height: 18px;
        }

        .weekday-header {
            padding: 8px;
            font-size: 11px;
        }
    }

    /* Small Mobile (Portrait) */
    @media (max-width: 480px) {
        .card-body {
            padding: 10px;
        }

        /* Title and buttons container - stack vertically */
        .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }

        /* Buttons container - keep side by side */
        .d-flex.justify-content-between.align-items-center>div {
            width: 100%;
            display: flex;
            gap: 10px;
        }

        .d-flex.justify-content-between.align-items-center .btn {
            flex: 1;
            margin-bottom: 0;
            font-size: 13px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .d-flex.justify-content-between.align-items-center .btn.me-2 {
            margin-right: 0 !important;
        }

        /* Keep statistics cards in 2x2 grid on small mobile too */
        .row.mb-4 {
            grid-template-columns: repeat(2, 1fr);
            /* gap: 8px; */
        }

        .row.mb-4 .card-body {
            padding: 10px 8px;
            min-height: 75px;
        }

        .row.mb-4 .card-body h6 {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .row.mb-4 .card-body h3 {
            font-size: 22px;
        }

        /* Calendar extends to edges on small mobile too */
        .card>.card-body {
            padding-left: 10px;
            padding-right: 10px;
        }

        .calendar-scroll {
            width: calc(100% + 20px);
            margin-left: -10px;
            margin-right: -10px;
            padding: 0;
        }

        .calendar-container {
            width: 100%;
        }

        .calendar-day {
            min-height: 70px;
            max-height: 100px;
            padding: 1px;
        }

        .weekday-header {
            padding: 3px 1px;
            font-size: 8px;
        }

        .calendar-day-number {
            font-size: 9px;
        }

        .calendar-day-name {
            font-size: 7px;
        }

        .employee-attendance-item {
            font-size: 7px;
            padding: 1px;
        }

        .employee-attendance-item img {
            width: 10px;
            height: 10px;
        }

        .attendance-status {
            font-size: 7px;
        }

        .holiday-label,
        .saturday-off-label,
        .sunday-label {
            font-size: 6px;
            padding: 1px;
        }
    }

    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
        .calendar-container {
            grid-template-columns: repeat(7, 1fr);
        }

        .weekday-header {
            display: block;
            padding: 6px;
            font-size: 10px;
        }

        .calendar-day {
            min-height: 80px;
            max-height: 120px;
            padding: 4px;
        }

        .calendar-day-header {
            display: block;
            margin-bottom: 4px;
            padding-bottom: 4px;
        }

        .calendar-day-number {
            font-size: 12px;
        }

        .calendar-day-name {
            font-size: 9px;
        }

        .employee-attendance-item {
            font-size: 9px;
            padding: 2px 3px;
            margin-bottom: 2px;
        }

        .employee-attendance-item img {
            width: 16px;
            height: 16px;
        }
    }
    /* Location info inside employee card - inline style */
    .loc-info-block {
        margin-top: 2px;
        font-size: 12px;
        color: #555;
        line-height: 1.5;
    }

    .loc-info-block .loc-row {
        display: flex;
        align-items: flex-start;
        gap: 4px;
        margin-bottom: 1px;
    }

    .loc-info-block .loc-icon {
        flex-shrink: 0;
        width: 16px;
        text-align: center;
    }

    .loc-info-block .loc-text {
        word-break: break-word;
    }

    .loc-section-label {
        font-weight: 700;
        font-size: 10px;
        letter-spacing: 0.5px;
        margin-top: 4px;
        margin-bottom: 2px;
    }

    .loc-section-label.checkin-label  { color: #198754; }
    .loc-section-label.checkout-label { color: #dc3545; }

    /* Inline time + location line (matches screenshot) */
    .loc-inline-line {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-bottom: 1px;
        font-size: 12.5px;
        color: #333;
    }

    .loc-inline-line .loc-time {
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .loc-inline-line .loc-sep {
        color: #aaa;
        flex-shrink: 0;
    }

    .loc-inline-line .loc-addr {
        color: #555;
        font-size: 11.5px;
        flex: 1;
        word-break: break-word;
    }

    /* Status badge pill */
    .status-badge-pill {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .status-badge-pill.present      { background: #28a745; color: white; }
    .status-badge-pill.half-day     { background: #ffc107; color: #000; }
    .status-badge-pill.absent       { background: #dc3545; color: white; }
    .status-badge-pill.working      { background: #007bff; color: white; }
    .status-badge-pill.week-off-present {
        background: linear-gradient(135deg, #ff6b35 0%, #e53e3e 100%);
        color: white;
        font-size: 10px;
        padding: 4px 10px;
        box-shadow: 0 2px 6px rgba(229,62,62,0.35);
    }

    /* Week-Off Present banner inside date view */
    .weekoff-present-banner {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        border: 1.5px solid #ffb74d;
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #e65100;
    }
    .weekoff-present-banner .wop-icon { font-size: 18px; }
    .weekoff-present-banner .wop-count {
        background: #e65100;
        color: white;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 12px;
        margin-left: auto;
    }

    /* Calendar cell – week-off-present item */
    .employee-attendance-item.week-off-present-item {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        border-left: 3px solid #ff6b35;
    }
    .attendance-status.week-off-present { color: #e65100; font-weight: 800; }

    /* Attendance header & action button styling - fixes alignment, center-text & hover */
    .attendance-header-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        height: 36px !important;
        padding: 0 16px !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        border-radius: 4px !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        cursor: pointer !important;
        text-decoration: none !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease !important;
    }
    .attendance-header-btn.btn-secondary {
        background-color: #6c757d !important;
        border: 1px solid #6c757d !important;
        color: #ffffff !important;
    }
    .attendance-header-btn.btn-secondary:hover,
    .attendance-header-btn.btn-secondary:focus {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
        color: #ffffff !important;
    }
    .attendance-header-btn.hr-btnbg {
        background-color: #E66136 !important;
        border: 1px solid #E66136 !important;
        color: #ffffff !important;
    }
    .attendance-header-btn.hr-btnbg:hover,
    .attendance-header-btn.hr-btnbg:focus {
        background-color: #d24e23 !important;
        border-color: #d24e23 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(230, 97, 54, 0.35) !important;
    }

    /* Modal button hover consistency */
    .modal-footer .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        height: 36px !important;
        padding: 0 16px !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        line-height: 1 !important;
    }
    .modal-footer .btn.hr-btnbg:hover,
    .modal-footer .btn.hr-btnbg:focus {
        background-color: #d24e23 !important;
        border-color: #d24e23 !important;
        color: #ffffff !important;
    }

    /* Multi Attendance Employee Item Styles */
    .multi-emp-row {
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background-color: #ffffff;
    }
    .multi-emp-row:hover {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
    }
    .multi-emp-row.selected {
        background-color: #fff7f4 !important;
        border-color: #ffd2c2 !important;
    }
    .multi-emp-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #dee2e6;
        flex-shrink: 0;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="card-title mb-0">Attendance Calendar</h4>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="/view" class="btn btn-secondary attendance-header-btn">
                            List View
                        </a>
                        <?php if (isset($role) && in_array($role, ['hr', 'admin'])): ?>
                        <button type="button" class="btn hr-btnbg attendance-header-btn" onclick="openMultiAttendanceModal()">
                            <i class="fa fa-users me-1"></i> Multi Attendance
                        </button>
                        <?php endif; ?>
                        <a href="/attendence" class="btn hr-btnbg attendance-header-btn">
                            Manage Attendance
                        </a>
                    </div>
                </div>

                <!-- Statistics - Only show for HR/Admin users -->
                <?php if (isset($role) && ($role === 'hr' || $role === 'admin')): ?>
                    <div class="collapsible-toggle collapsed" onclick="toggleSection('statistics')">
                        <i class="fas fa-chevron-down"></i>
                        <strong>Statistics</strong>
                    </div>
                    <div id="statistics-section" class="collapsible-content collapsed">
                        <div class="row mb-4">
                            <!-- <div class="col-lg-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Total Employees</h6>
                                    <h3 id="total-employees"><?= $totalEmployees ?? 0 ?></h3>
                                </div>
                            </div>
                        </div> -->
                            <div class="col-lg-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6>Present Today</h6>
                                        <h3 id="present-employees"><?= $presentEmployees ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h6>Absent Today</h6>
                                        <h3 id="absent-employees"><?= $absentEmployees ?? 0 ?></h3>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-lg-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6>Working Days</h6>
                                    <h3><?= $workingDays ?? 0 ?></h3>
                                </div>
                            </div>
                        </div> -->
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Legend -->
                <div class="collapsible-toggle collapsed" onclick="toggleSection('legend')">
                    <i class="fas fa-chevron-down"></i>
                    <strong>Legend</strong>
                </div>
                <div id="legend-section" class="collapsible-content collapsed">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-3">
                                <p class="me-3 mb-2"><span
                                        class="text-success fw-bold">P</span><b>&nbsp;:&nbsp;Present</b></p>
                                <p class="me-3 mb-2"><span
                                        class="text-danger fw-bold">A</span><b>&nbsp;:&nbsp;Absent</b></p>
                                <p class="me-3 mb-2"><span
                                        class="text-warning fw-bold">H</span><b>&nbsp;:&nbsp;Half-day</b></p>
                                <p class="me-3 mb-2"><span class="text-primary fw-bold"
                                        style="cursor:pointer">W</span><b>&nbsp;:&nbsp;Working</b></p>
                                <p class="me-3 mb-2"><span class="text-success fw-bold">P (L)</span> OR
                                    <span class="text-warning fw-bold">H (L)</span> OR
                                    <span class="text-primary fw-bold">W (L)</span><b>&nbsp;:&nbsp;Late</b>
                                </p>
                                <p class="me-3 mb-2"><span class="text-danger fw-bold">Sunday</b></p>
                                <p class="me-3 mb-2"><span class="text-info fw-bold">Holiday</b></p>
                                <p class="me-3 mb-2"><span class="bg-saturday-off text-dark fw-bold">Saturday Off</b>
                                </p>
                                <p class="me-3 mb-2"><span class="badge bg-warning text-dark"
                                        style="font-size: 10px;">OT</span><b>&nbsp;:&nbsp;Overtime</b></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters - Collapsible Section -->
                <div class="collapsible-toggle collapsed" onclick="toggleSection('filters')">
                    <i class="fas fa-chevron-down"></i>
                    <strong>Filters</strong>
                </div>
                <div id="filters-section" class="collapsible-content collapsed">
                    <div class="row px-1 mb-3">

                        <!-- Month -->
                        <div class="col-12 col-md-2 mb-2">
                            <label class="form-label">Month</label>
                            <select id="month-select" class="form-select">
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

                        <!-- Year -->
                        <div class="col-12 col-md-2 mb-2">
                            <label class="form-label">Year</label>
                            <select id="year-select" class="form-select">
                                <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Employee -->
                        <?php if (isset($role) && ($role === 'hr' || $role === 'admin')): ?>
                            <div class="col-12 col-md-3 mb-2">
                                <label class="form-label">Employee</label>
                                <select id="user-filter" class="form-select">
                                    <option value="">Select Employee</option>
                                </select>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>


                <!-- View Toggle Buttons -->
                <div class="view-toggle-container">
                    <button class="view-toggle-btn active" onclick="toggleView('horizontal')">
                        <i class="fas fa-stream"></i> Date View
                    </button>
                    <button class="view-toggle-btn" onclick="toggleView('calendar')">
                        <i class="fas fa-calendar-alt"></i> Calendar View
                    </button>
                </div>

                <!-- Horizontal Date Scroll View -->
                <div id="horizontal-view" class="calendar-view-section mobile-date-scroll">
                    <div id="date-scroll-container" class="date-scroll-container">
                        <!-- Horizontal scrolling dates will be rendered here -->
                    </div>

                    <div id="mobile-attendance-list" class="mobile-attendance-list">
                        <!-- Employee attendance cards for selected date will be rendered here -->
                    </div>
                </div>

                <!-- Calendar Grid View -->
                <div id="calendar-view" class="calendar-view-section desktop-calendar" style="display: none;">
                    <div class="view-section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Calendar Grid View
                    </div>
                    <div class="calendar-scroll">
                        <div id="calendar-container" class="calendar-container">
                            <!-- Calendar will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">Attendance Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="absent">Absent</option>
                        <option value="present">Present</option>
                        <option value="half-day">Half Day</option>
                    </select>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Check In</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Duration</th>
                                    <th>Overtime</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-modal-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-end gap-2">
                <button class="btn hr-btnbg" data-bs-dismiss="modal">Back</button>
                <button class="btn hr-btnbg" onclick="saveAttendanceEdits()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceBulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Date Range -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">From Date</label>
                        <input type="date" id="from_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">To Date</label>
                        <input type="date" id="to_date" class="form-control">
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="fw-bold">Attendance Status</label>
                    <select id="bulk_status" class="form-select">
                        <option value="present">Present</option>
                        <option value="half-day">Half Day</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <!-- Time -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Check In Time</label>
                        <input type="time" id="check_in_time" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Check Out Time</label>
                        <input type="time" id="check_out_time" class="form-control">
                    </div>
                </div>

                <div class="alert alert-info small">
                    âš ï¸ This action will update attendance for all dates between selected range.
                </div>
            </div>

            <div class="modal-footer justify-content-end gap-2">
                <button class="btn hr-btnbg" data-bs-dismiss="modal">Back</button>
                <button class="btn hr-btnbg" onclick="saveBulkAttendance()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Multi Attendance Manage Modal -->
<div class="modal fade" id="multiAttendanceModal" tabindex="-1" aria-labelledby="multiAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-sm">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title fw-bold" id="multiAttendanceModalLabel" style="color: #2c3e50;">
                    <i class="fa fa-users text-primary me-2"></i>Multi Attendance Manage
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Date & Status Selection -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="multi_attendance_date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" id="multi_attendance_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="multi_attendance_status" class="form-label fw-bold">Attendance Status <span class="text-danger">*</span></label>
                        <select id="multi_attendance_status" class="form-select" onchange="toggleMultiAttendanceTimes()">
                            <option value="present" selected>Present</option>
                            <option value="half-day">Half Day</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>

                <!-- Time Inputs (Check In & Check Out) -->
                <div class="row g-3 mb-3" id="multi_time_fields_container">
                    <div class="col-md-6">
                        <label for="multi_check_in_time" class="form-label fw-bold">Check In Time</label>
                        <input type="time" id="multi_check_in_time" class="form-control" value="09:00">
                    </div>
                    <div class="col-md-6">
                        <label for="multi_check_out_time" class="form-label fw-bold">Check Out Time</label>
                        <input type="time" id="multi_check_out_time" class="form-control" value="18:00">
                    </div>
                </div>

                <!-- Employee Selection Section -->
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0" style="color: #333;">Select Employees</h6>
                            <span id="multi_selected_count_badge" class="badge bg-primary" style="font-size: 11px;">0 Selected</span>
                        </div>
                        <div style="width: 240px; max-width: 100%;">
                            <input type="text" id="multi_employee_search" class="form-control form-control-sm" placeholder="ðŸ” Search employee..." oninput="filterMultiEmployees()">
                        </div>
                    </div>

                    <!-- Select All Bar -->
                    <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded border mb-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="multi_select_all" onchange="toggleSelectAllMultiEmployees(this.checked)" style="cursor: pointer;">
                            <label class="form-check-label fw-semibold" for="multi_select_all" style="cursor: pointer; user-select: none;">
                                Select All (<span id="multi_total_emp_count">0</span> employees)
                            </label>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted" onclick="clearAllMultiEmployees()">Clear Selection</button>
                    </div>

                    <!-- Scrollable Employee List -->
                    <div id="multi_employee_list_container" style="max-height: 270px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; padding: 6px; background-color: #fbfcfd;">
                        <div class="text-center py-4 text-muted" id="multi_employee_loading">
                            <i class="fa fa-spinner fa-spin me-1"></i> Loading employees...
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3 py-2 px-3 small mb-0 d-flex align-items-center">
                    <i class="fa fa-info-circle me-2 flex-shrink-0" style="font-size: 16px;"></i>
                    <span>This action will update attendance records for all selected employees on the specified date.</span>
                </div>
            </div>

            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-secondary attendance-header-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn hr-btnbg attendance-header-btn" id="btnSaveMultiAttendance" onclick="saveMultiAttendance()">
                    <i class="fa fa-check me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.toggleSection = function (sectionName) {
        const section = document.getElementById(sectionName + '-section');
        const toggle = section.previousElementSibling;

        section.classList.toggle('collapsed');
        toggle.classList.toggle('collapsed');
    };

    window.toggleView = function (viewType) {
        const horizontalView = document.getElementById('horizontal-view');
        const calendarView = document.getElementById('calendar-view');
        const buttons = document.querySelectorAll('.view-toggle-btn');

        // Remove active class from all buttons
        buttons.forEach(btn => btn.classList.remove('active'));

        // Set active button based on the button clicked
        const clickedButton = event.target.closest('.view-toggle-btn');
        if (clickedButton) {
            clickedButton.classList.add('active');
        }

        if (viewType === 'horizontal') {
            horizontalView.style.display = 'block';
            calendarView.style.display = 'none';
        } else if (viewType === 'calendar') {
            horizontalView.style.display = 'none';
            calendarView.style.display = 'block';
            // Re-render calendar when switching to calendar view
            renderDesktopCalendar();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('token');
        const headers = {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        };

        let currentMonth = new Date().getMonth() + 1;
        let currentYear = new Date().getFullYear();
        let users = [];
        let holidays = [];
        let saturdayOffDates = [];
        let companyInfo = {};
        let isMobile = window.innerWidth <= 768;
        let selectedDate = new Date().toISOString().split('T')[0];

        // Set current month/year
        document.getElementById('month-select').value = String(currentMonth).padStart(2, '0');
        document.getElementById('year-select').value = currentYear;

        // Check if mobile on resize
        window.addEventListener('resize', () => {
            const wasMobile = isMobile;
            isMobile = window.innerWidth <= 768;
            if (wasMobile !== isMobile) {
                renderCalendar();
            }
        });

        const populateUserFilter = () => {
            const userFilter = document.getElementById('user-filter');
            if (!userFilter) return; // Filter doesn't exist for employee users

            userFilter.innerHTML = '<option value="">Select Employee</option>';

            const sortedUsers = [...users].sort((a, b) =>
                a.employee_name.localeCompare(b.employee_name)
            );

            sortedUsers.forEach(user => {
                const empStatus = (user.status || 'Active').toLowerCase();
                const option = document.createElement('option');
                option.value = user.user_id;
                option.textContent = empStatus !== 'active' ? `${user.employee_name} (${user.status})` : user.employee_name;
                userFilter.appendChild(option);
            });
        };

        const loadAttendance = async (month, year) => {
            try {
                const branchSelect = document.getElementById('navbarBranchSelect');
                const branchId = branchSelect ? branchSelect.value : '';
                let url = `/api/attendance/getAttendance/${month}/${year}`;
                if (branchId) {
                    url += `?branch_id=${encodeURIComponent(branchId)}`;
                }

                const response = await fetch(url, { headers, credentials: 'same-origin' });
                const data = await response.json();

                if (data.status === 'success') {
                    users = data.data.users || [];

                    holidays = data.data.holidays || [];
                    saturdayOffDates = data.data.saturdayOffDates || [];
                    companyInfo = data.data.company || {};
                    populateUserFilter();
                    renderCalendar();
                }
            } catch (error) {
                console.error('Error loading attendance:', error);
            }
        };

        const renderMobileDateScroll = () => {
            const todayDate = new Date().toISOString().split('T')[0];
            const selectedMonth = parseInt(document.getElementById('month-select').value);
            const selectedYear = parseInt(document.getElementById('year-select').value);
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            const dateScrollContainer = document.getElementById('date-scroll-container');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            dateScrollContainer.innerHTML = '';

            // Show 2 days before today, today (active), and 1 day after
            const today = new Date();
            const currentDay = today.getDate();
            const currentMonthNum = today.getMonth() + 1;
            const currentYearNum = today.getFullYear();

            // Determine which days to show
            let displayDays = [];

            if (selectedMonth === currentMonthNum && selectedYear === currentYearNum) {
                // Current month - show 2 days before today, today, and tomorrow
                for (let i = -2; i <= 1; i++) {
                    const targetDate = new Date(today);
                    targetDate.setDate(today.getDate() + i);
                    const day = targetDate.getDate();
                    const month = targetDate.getMonth() + 1;
                    const year = targetDate.getFullYear();

                    if (month === selectedMonth && year === selectedYear && day >= 1 && day <= daysInMonth) {
                        displayDays.push(day);
                    }
                }
            } else {
                // Other months - show first 4 days
                displayDays = [1, 2, 3, 4];
            }

            // If we have fewer than 4 days, add more from the start of the month
            if (displayDays.length < 4) {
                for (let day = 1; day <= daysInMonth && displayDays.length < 4; day++) {
                    if (!displayDays.includes(day)) {
                        displayDays.push(day);
                    }
                }
            }

            displayDays.sort((a, b) => a - b);

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(selectedYear, selectedMonth - 1, day);
                const dayOfWeek = dateObj.getDay();
                const isToday = dateStr === todayDate;
                const isFuture = new Date(dateStr) > new Date(todayDate);

                const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const holiday = holidays.find(h => h.holiday_date === dateStr);

                const dateCard = document.createElement('div');
                dateCard.className = 'date-card';

                if (dateStr === selectedDate) dateCard.classList.add('active');
                if (isToday) dateCard.classList.add('today');
                if (holiday) dateCard.classList.add('holiday');
                else if (saturdayOffDates.includes(dateStr)) dateCard.classList.add('saturday-off');
                else if (dayOfWeek === 0) dateCard.classList.add('sunday');

                dateCard.innerHTML = `
                <div class="date-card-day">${weekdays[dayOfWeek]}</div>
                <div class="date-card-number">${day}</div>
                <!-- <div class="date-card-month">${monthNames[selectedMonth - 1]}</div> -->
            `;

                if (holiday) {
                    const label = document.createElement('div');
                    label.className = 'date-card-label holiday';
                    label.textContent = 'Holiday';
                    dateCard.appendChild(label);
                } else if (saturdayOffDates.includes(dateStr)) {
                    const label = document.createElement('div');
                    label.className = 'date-card-label saturday-off';
                    label.textContent = 'Sat Off';
                    dateCard.appendChild(label);
                } else if (dayOfWeek === 0) {
                    const label = document.createElement('div');
                    label.className = 'date-card-label sunday';
                    label.textContent = 'Sunday';
                    dateCard.appendChild(label);
                }

                dateCard.addEventListener('click', () => {
                    selectedDate = dateStr;
                    renderMobileDateScroll();
                    renderMobileAttendanceList();
                });

                dateScrollContainer.appendChild(dateCard);
            }

            // Scroll to active date
            setTimeout(() => {
                const activeCard = dateScrollContainer.querySelector('.date-card.active');
                if (activeCard) {
                    activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }, 100);
        };

        const renderMobileAttendanceList = () => {
            const userFilterEl = document.getElementById('user-filter');
            const selectedUserId = userFilterEl ? userFilterEl.value : '';
            const mobileList = document.getElementById('mobile-attendance-list');
            const defaultImagePath = "<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>";
            const todayDate = new Date().toISOString().split('T')[0];

            // Filter users by dropdown selection
            let filteredUsers = users;
            if (selectedUserId) {
                filteredUsers = filteredUsers.filter(user => user.user_id == selectedUserId);
            }

            mobileList.innerHTML = '';

            const selectedDateObj = new Date(selectedDate);
            const dayOfWeek = selectedDateObj.getDay();
            const holiday = holidays.find(h => h.holiday_date === selectedDate);
            const isFuture = new Date(selectedDate) > new Date(todayDate);
            const isSaturdayOff = saturdayOffDates.includes(selectedDate);

            // For Sunday, hard holidays, or future dates â†’ return early with banner
            // Note: Sunday with actual check-ins is handled below in isSundayOff block
            if (holiday || isFuture) {
                mobileList.innerHTML = `
                <div class="alert alert-info text-center">
                    ${holiday ? `<strong>Holiday:</strong> ${holiday.title}` :
                        '<strong>Future Date</strong>'}
                </div>
            `;
                return;
            }

            // For Saturday Off / Sunday Off: show banner then render employees who checked in
            // with exactly the same card design as regular working days
            const isSundayOff = (dayOfWeek === 0);

            if (isSaturdayOff || isSundayOff) {
                // Employees who checked in on this off day
                const presentOnOff = filteredUsers.filter(user => {
                    const rec = user.attendance?.find(r => (r.date || '').substring(0, 10) === selectedDate);
                    return rec && rec.check_in_time;
                });

                // Top banner indicating it's an off day
                const offLabel = isSundayOff ? '<i class="mdi mdi-circle text-danger me-1"></i> Sunday Off' : '<i class="mdi mdi-circle text-warning me-1"></i> Saturday Off';
                const banner = document.createElement('div');
                banner.className = 'alert text-center fw-bold mb-3';
                banner.style.cssText = isSundayOff
                    ? 'background:#f8d7da;color:#842029;border:1px solid #f5c2c7;border-radius:8px;'
                    : 'background:#fff3cd;color:#664d03;border:1px solid #ffecb5;border-radius:8px;';
                banner.innerHTML = offLabel;
                mobileList.appendChild(banner);

                if (presentOnOff.length === 0) {
                    const noOne = document.createElement('div');
                    noOne.className = 'alert alert-secondary text-center';
                    noOne.textContent = 'No employees came to office today.';
                    mobileList.appendChild(noOne);
                    return;
                }

                // â”€â”€ Reuse the same fmtTime helper as regular days â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                function fmtTimeOff(t) {
                    if (!t || t === '-') return null;
                    const [h, m, s] = t.split(':');
                    const hh = parseInt(h);
                    const ampm = hh >= 12 ? 'PM' : 'AM';
                    const h12 = hh % 12 || 12;
                    return `${h12}:${m.padStart(2,'0')}:${(s||'00').padStart(2,'0')} ${ampm}`;
                }

                // â”€â”€ Same buildInlineLine helper as regular days â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                function buildInlineLineOff(prefix, time, locationName, locationStatus) {
                    const timeStr = fmtTimeOff(time);
                    if (!timeStr) {
                        return `<div class="loc-inline-line" style="color:#bbb;">
                            <span class="loc-time">${prefix}: –</span>
                        </div>`;
                    }
                    const isMissing = !locationName || locationName.trim() === ''
                        || locationStatus === 'not_captured'
                        || locationStatus === 'denied'
                        || locationStatus === 'failed';

                    let displayAddr, pinColor, titleAttr, addrStyle;
                    if (!isMissing) {
                        displayAddr = locationName;
                        pinColor    = '';
                        titleAttr   = '';
                        addrStyle   = '';
                    } else {
                        const fallback = (companyInfo.address && companyInfo.address.trim())
                            ? companyInfo.address.trim()
                            : 'Office';
                        displayAddr = fallback;
                        pinColor    = 'color: #6c757d;';
                        titleAttr   = ' title="Default Office Address"';
                        addrStyle   = 'color:#555;';
                    }
                    const pin = `<span class="loc-sep">|</span><span style="${pinColor}"${titleAttr}>&#128205;</span><span class="loc-addr" style="${addrStyle}">${displayAddr}</span>`;
                    return `<div class="loc-inline-line">
                        <span class="loc-time">${prefix}: ${timeStr}</span>
                        ${pin}
                    </div>`;
                }

                // â”€â”€ Render each present employee with the SAME card design â”€â”€â”€â”€â”€â”€â”€
                presentOnOff.forEach(user => {
                    const attendance = user.attendance?.find(r => (r.date || '').substring(0, 10) === selectedDate);
                    const profileImage = user.profile_image ? `/upload/${user.profile_image}` : defaultImagePath;

                    const isToday = selectedDate === todayDate;
                    const isWorking = isToday && attendance?.check_in_time && !attendance?.check_out_time;

                    // Status badge — "Week Off · Present" or "Week Off · Working"
                    const statusText = isWorking ? 'Week Off · Working' : 'Week Off · Present';
                    const statusClass = 'week-off-present';

                    const checkInLine  = buildInlineLineOff(
                        'In',
                        attendance?.check_in_time,
                        attendance?.check_in_location_name,
                        attendance?.check_in_location_status
                    );
                    const checkOutLine = buildInlineLineOff(
                        'Out',
                        attendance?.check_out_time  ?? null,
                        attendance?.check_out_location_name  ?? null,
                        attendance?.check_out_location_status ?? null
                    );

                    const employeeCard = document.createElement('div');
                    employeeCard.className = 'mobile-employee-card';
                    employeeCard.dataset.userId = user.user_id;
                    employeeCard.dataset.date   = selectedDate;

                    employeeCard.innerHTML = `
                        <img src="${profileImage}" alt="${user.employee_name}">
                        <div class="mobile-employee-info">
                            <div class="mobile-employee-name">${user.employee_name}</div>
                            <div class="loc-info-block">
                                ${checkInLine}
                                ${checkOutLine}
                            </div>
                        </div>
                        <div class="status-badge-pill ${statusClass}">${statusText}</div>
                    `;

                    <?php if (isset($role) && in_array($role, ['hr', 'admin'])): ?>
                        employeeCard.addEventListener('click', () => {
                            openAttendanceModal(user.user_id, selectedDate);
                        });
                    <?php endif; ?>

                    mobileList.appendChild(employeeCard);
                });
                return;
            }

            if (filteredUsers.length === 0) {
                mobileList.innerHTML = '<div class="alert alert-warning">No employees found</div>';
                return;
            }

            filteredUsers.forEach(user => {
                const attendance = user.attendance?.find(record => {
                    const recordDate = record.date ? record.date.substring(0, 10) : '';
                    return recordDate === selectedDate;
                });

                const profileImage = user.profile_image ? `/upload/${user.profile_image}` : defaultImagePath;

                let statusText = 'Absent';
                let statusClass = 'absent';
                const status = attendance?.status ? attendance.status.toLowerCase() : '';
                // Check if this date is past the employee's last working day
                const empStatus = (user.status || 'Active').toLowerCase();
                if (empStatus !== 'active') {
                    if (user.last_working_day) {
                        const lwd = new Date(user.last_working_day);
                        const currDate = new Date(selectedDate);
                        lwd.setHours(0,0,0,0);
                        currDate.setHours(0,0,0,0);
                        if (currDate > lwd) {
                            return; // Skip rendering employee for this date â€” outside employment period
                        }
                    } else {
                        // If no last working day is set, only show if they actually checked in
                        if (!attendance || (!attendance.check_in_time && status !== 'present' && status !== 'half-day')) {
                            return; 
                        }
                    }
                }


                const isToday = selectedDate === todayDate;
                if (isToday && attendance?.check_in_time && !attendance?.check_out_time) {
                    statusText = attendance.is_late == 1 ? 'Working (Late)' : 'Working';
                    statusClass = 'working';
                } else if (status === 'present') {
                    statusText = attendance.is_late == 1 ? 'Present (Late)' : 'Present';
                    statusClass = 'present';
                } else if (status === 'half-day') {
                    statusText = attendance.is_late == 1 ? 'Half Day (Late)' : 'Half Day';
                    statusClass = 'half-day';
                }

                // â”€â”€ Format time (HH:MM:SS â†’ h:mm:ss AM/PM) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                function fmtTime(t) {
                    if (!t || t === '-') return null;
                    const [h, m, s] = t.split(':');
                    const hh = parseInt(h);
                    const ampm = hh >= 12 ? 'PM' : 'AM';
                    const h12 = hh % 12 || 12;
                    return `${h12}:${m.padStart(2,'0')}:${(s||'00').padStart(2,'0')} ${ampm}`;
                }

                // ── Build inline time + pin location row ─────────────────────────
                // Rules:
                //   time == null  → show "Prefix: –" with NO pin/address
                //   time != null, location exists → show real GPS address
                //   time != null, location is null/empty → show default office address
                function buildInlineLine(prefix, time, locationName, locationStatus) {
                    const timeStr = fmtTime(time);

                    // Time is null → show a plain dash, NO location at all
                    if (!timeStr) {
                        return `<div class="loc-inline-line" style="color:#bbb;">
                            <span class="loc-time">${prefix}: –</span>
                        </div>`;
                    }

                    // Time exists - decide which address to show
                    const isMissing = !locationName || locationName.trim() === ''
                        || locationStatus === 'not_captured'
                        || locationStatus === 'denied'
                        || locationStatus === 'failed';

                    let displayAddr, pinColor, titleAttr, addrStyle;

                    if (!isMissing) {
                        // Real GPS location
                        displayAddr = locationName;
                        pinColor    = '';
                        titleAttr   = '';
                        addrStyle   = '';
                    } else {
                        // Fallback: company default address
                        const fallback = (companyInfo.address && companyInfo.address.trim())
                            ? companyInfo.address.trim()
                            : 'Office';
                        displayAddr = fallback;
                        pinColor    = 'color: #6c757d;';
                        titleAttr   = ' title="Default Office Address"';
                        addrStyle   = 'color:#555;';
                    }

                    const pin = `<span class="loc-sep">|</span><span style="${pinColor}"${titleAttr}>&#128205;</span><span class="loc-addr" style="${addrStyle}">${displayAddr}</span>`;

                    return `<div class="loc-inline-line">
                        <span class="loc-time">${prefix}: ${timeStr}</span>
                        ${pin}
                    </div>`;
                }

                const checkInLine  = buildInlineLine(
                    'In',
                    attendance?.check_in_time,
                    attendance?.check_in_location_name,
                    attendance?.check_in_location_status
                );
                const checkOutLine = buildInlineLine(
                    'Out',
                    attendance?.check_out_time  ?? null,
                    attendance?.check_out_location_name  ?? null,
                    attendance?.check_out_location_status ?? null
                );

                const employeeCard = document.createElement('div');
                employeeCard.className = 'mobile-employee-card';
                employeeCard.dataset.userId = user.user_id;
                employeeCard.dataset.date = selectedDate;

                employeeCard.innerHTML = `
                <img src="${profileImage}" alt="${user.employee_name}">
                <div class="mobile-employee-info">
                    <div class="mobile-employee-name">${user.employee_name}</div>
                    <div class="loc-info-block">
                        ${checkInLine}
                        ${checkOutLine}
                    </div>
                </div>
                <div class="status-badge-pill ${statusClass}">
                    ${statusText}
                </div>
            `;

                <?php if (isset($role) && in_array($role, ['hr', 'admin'])): ?>
                    employeeCard.addEventListener('click', () => {
                        openAttendanceModal(user.user_id, selectedDate);
                    });
                <?php endif; ?>

                mobileList.appendChild(employeeCard);
            });

            // Update statistics
            let presentCount = 0;
            let absentCount = 0;
            let activeTotal = 0;
            filteredUsers.forEach(user => {
                const empStatus = (user.status || 'Active').toLowerCase();
                let skipForToday = false;
                if (empStatus !== 'active') {
                    if (user.last_working_day) {
                        const lwd = new Date(user.last_working_day);
                        const todayD = new Date(todayDate);
                        lwd.setHours(0,0,0,0);
                        todayD.setHours(0,0,0,0);
                        if (todayD > lwd) skipForToday = true;
                    } else {
                        skipForToday = true;
                    }
                }
                
                const todayAttendance = user.attendance?.find(record => {
                    const recordDate = record.date ? record.date.substring(0, 10) : '';
                    return recordDate === todayDate;
                });
                
                if (todayAttendance && (todayAttendance.status === 'present' || todayAttendance.status === 'half-day' || todayAttendance.check_in_time)) {
                    presentCount++;
                    if (skipForToday) activeTotal++;
                } else if (!skipForToday) {
                    absentCount++;
                }
                
                if (!skipForToday) activeTotal++;
            });

            if (document.getElementById('total-employees')) {
                document.getElementById('total-employees').innerText = activeTotal;
            }
            if (document.getElementById('present-employees')) {
                document.getElementById('present-employees').innerText = presentCount;
            }
            if (document.getElementById('absent-employees')) {
                document.getElementById('absent-employees').innerText = absentCount;
            }
        };

        const renderCalendar = () => {
            // Always render horizontal date scroll by default
            renderMobileDateScroll();
            renderMobileAttendanceList();
            // Also render desktop calendar but it will be hidden initially
            renderDesktopCalendar();
        };

        const renderDesktopCalendar = () => {
            const todayDate = new Date().toISOString().split('T')[0];
            const selectedMonth = parseInt(document.getElementById('month-select').value);
            const selectedYear = parseInt(document.getElementById('year-select').value);
            const userFilterEl = document.getElementById('user-filter');
            const selectedUserId = userFilterEl ? userFilterEl.value : '';

            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            const firstDay = new Date(selectedYear, selectedMonth - 1, 1).getDay();
            const calendarContainer = document.getElementById('calendar-container');
            const defaultImagePath = "<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>";

            // Filter users by dropdown selection (only if filter exists)
            let filteredUsers = users;

            if (selectedUserId) {
                filteredUsers = filteredUsers.filter(user => user.user_id == selectedUserId);
            }

            // Clear container
            calendarContainer.innerHTML = '';

            // Weekday headers (always show for calendar grid)
            const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            weekdays.forEach(day => {
                const header = document.createElement('div');
                header.className = 'weekday-header';
                header.textContent = day;
                calendarContainer.appendChild(header);
            });

            // Empty cells for days before month starts (always show for grid layout)
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day';
                emptyDay.style.backgroundColor = '#f8f9fa';
                calendarContainer.appendChild(emptyDay);
            }

            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(selectedYear, selectedMonth - 1, day);
                const dayOfWeek = dateObj.getDay();
                const isToday = dateStr === todayDate;
                const isFuture = new Date(dateStr) > new Date(todayDate);

                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';

                if (isToday) dayDiv.classList.add('today');
                if (isFuture) dayDiv.classList.add('future');

                const holiday = holidays.find(h => h.holiday_date === dateStr);
                if (holiday) {
                    dayDiv.classList.add('holiday');
                } else if (saturdayOffDates.includes(dateStr)) {
                    dayDiv.classList.add('saturday-off');
                } else if (dayOfWeek === 0) {
                    dayDiv.classList.add('sunday');
                }

                // Day header
                const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day-header';
                dayHeader.innerHTML = `
                <div class="calendar-day-number">${day}</div>
                <div class="calendar-day-name">${weekdays[dayOfWeek]}</div>
            `;
                dayDiv.appendChild(dayHeader);

                // Holiday/Saturday Off/Sunday label
                if (holiday) {
                    const holidayLabel = document.createElement('span');
                    holidayLabel.className = 'holiday-label';
                    holidayLabel.textContent = holiday.title || 'Holiday';
                    dayDiv.appendChild(holidayLabel);
                } else if (saturdayOffDates.includes(dateStr)) {
                    const satLabel = document.createElement('span');
                    satLabel.className = 'saturday-off-label';
                    satLabel.textContent = 'Sat Off';
                    dayDiv.appendChild(satLabel);
                } else if (dayOfWeek === 0) {
                    const sunLabel = document.createElement('span');
                    sunLabel.className = 'sunday-label';
                    sunLabel.textContent = 'Sunday';
                    dayDiv.appendChild(sunLabel);
                }

                // For Saturday Off days - show employees who actually came in
                if (saturdayOffDates.includes(dateStr) && !holiday && !isFuture) {
                    filteredUsers.forEach(user => {
                        const att = user.attendance?.find(r => (r.date || '').substring(0, 10) === dateStr);
                        if (!att || !att.check_in_time) return; // only show those who checked in

                        const profileImage = user.profile_image ? `/upload/${user.profile_image}` : defaultImagePath;
                        const item = document.createElement('div');
                        item.className = 'employee-attendance-item attendance-cell week-off-present-item';
                        item.dataset.userId = user.user_id;
                        item.dataset.date = dateStr;
                        item.title = `${user.employee_name} – Check-in: ${att.check_in_time}`;
                        item.innerHTML = `
                            <img src="${profileImage}" alt="${user.employee_name}">
                            <span class="attendance-status week-off-present" title="Week Off – Present">WO</span>
                        `;
                        dayDiv.appendChild(item);
                    });
                }

                // Employee attendance for regular working days
                if (!holiday && !saturdayOffDates.includes(dateStr) && dayOfWeek !== 0 && !isFuture) {
                    filteredUsers.forEach(user => {
                        const attendance = user.attendance?.find(record => {
                            const recordDate = record.date ? record.date.substring(0, 10) : '';
                            return recordDate === dateStr;
                        });

                        if (!isFuture) {
                            const profileImage = user.profile_image ? `/upload/${user.profile_image}` : defaultImagePath;
                            const employeeItem = document.createElement('div');
                            employeeItem.className = 'employee-attendance-item attendance-cell';
                            employeeItem.dataset.userId = user.user_id;
                            employeeItem.dataset.date = dateStr;

                            let statusLetter = 'A';
                            let statusClass = 'absent';
                            const status = attendance?.status ? attendance.status.toLowerCase() : '';
                            // Check if this date is past the employee's last working day
                            const empStatus = (user.status || 'Active').toLowerCase();
                            if (empStatus !== 'active') {
                                if (user.last_working_day) {
                                    const lwd = new Date(user.last_working_day);
                                    const currDate = new Date(dateStr);
                                    lwd.setHours(0,0,0,0);
                                    currDate.setHours(0,0,0,0);
                                    if (currDate > lwd) {
                                        return; // Skip â€” date is outside employment period
                                    }
                                } else {
                                    if (!attendance || (!attendance.check_in_time && status !== 'present' && status !== 'half-day')) {
                                        return;
                                    }
                                }
                            }

                            if (isToday && attendance?.check_in_time && !attendance?.check_out_time) {
                                statusLetter = attendance.is_late == 1 ? 'W (L)' : 'W';
                                statusClass = 'working';
                            } else if (status === 'present') {
                                statusLetter = attendance.is_late == 1 ? 'P (L)' : 'P';
                                statusClass = 'present';
                            } else if (status === 'half-day') {
                                statusLetter = attendance.is_late == 1 ? 'H (L)' : 'H';
                                statusClass = 'half-day';
                            } else {
                                statusLetter = 'A';
                                statusClass = 'absent';
                            }

                            employeeItem.innerHTML = `
                            <img src="${profileImage}" alt="${user.employee_name}">
                            <span class="attendance-status ${statusClass}">${statusLetter}</span>
                            ${attendance?.overtime && attendance.overtime !== '00:00:00' ? '<span class="overtime-badge">OT</span>' : ''}
                        `;
                            dayDiv.appendChild(employeeItem);
                        }
                    });
                }

                calendarContainer.appendChild(dayDiv);
            }

            // Update statistics
            let presentCount = 0;
            let absentCount = 0;
            let activeTotal = 0;
            filteredUsers.forEach(user => {
                const empStatus = (user.status || 'Active').toLowerCase();
                let skipForToday = false;
                if (empStatus !== 'active') {
                    if (user.last_working_day) {
                        const lwd = new Date(user.last_working_day);
                        const todayD = new Date(todayDate);
                        lwd.setHours(0,0,0,0);
                        todayD.setHours(0,0,0,0);
                        if (todayD > lwd) skipForToday = true;
                    } else {
                        skipForToday = true;
                    }
                }
                
                const todayAttendance = user.attendance?.find(record => {
                    const recordDate = record.date ? record.date.substring(0, 10) : '';
                    return recordDate === todayDate;
                });
                
                if (todayAttendance && (todayAttendance.status === 'present' || todayAttendance.status === 'half-day' || todayAttendance.check_in_time)) {
                    presentCount++;
                    if (skipForToday) activeTotal++;
                } else if (!skipForToday) {
                    absentCount++;
                }
                
                if (!skipForToday) activeTotal++;
            });

            if (document.getElementById('total-employees')) {
                document.getElementById('total-employees').innerText = activeTotal;
            }
            if (document.getElementById('present-employees')) {
                document.getElementById('present-employees').innerText = presentCount;
            }
            if (document.getElementById('absent-employees')) {
                document.getElementById('absent-employees').innerText = absentCount;
            }
        };

        // Event listeners
        document.getElementById('month-select').addEventListener('change', () => {
            currentMonth = parseInt(document.getElementById('month-select').value);
            loadAttendance(currentMonth, currentYear);
        });

        document.getElementById('year-select').addEventListener('change', () => {
            currentYear = parseInt(document.getElementById('year-select').value);
            loadAttendance(currentMonth, currentYear);
        });

        const userFilterEl = document.getElementById('user-filter');
        if (userFilterEl) {
            userFilterEl.addEventListener('change', () => {
                if (isMobile) {
                    renderMobileAttendanceList();
                } else {
                    renderCalendar();
                }
            });
        }

        // Load initial data
        loadAttendance(currentMonth, currentYear);

        document.addEventListener('click', function (e) {
            const cell = e.target.closest('.attendance-cell');
            if (!cell) return;

            <?php if (!isset($role) || !in_array($role, ['hr', 'admin'])): ?>
                return;
            <?php endif; ?>

            const userId = cell.dataset.userId;
            const date = cell.dataset.date;

            openAttendanceModal(userId, date);
        });

        let currentEditUser = null;
        let currentEditDate = null;
        let currentAttendanceRecords = [];

        window.openAttendanceModal = function (userId, date) {
            currentEditUser = userId;
            currentEditDate = date;

            fetch(`/api/attendance/day-records?user_id=${userId}&date=${date}`, { headers })
                .then(res => res.json())
                .then(res => {
                    currentAttendanceRecords = res.data || [];
                    let rows = '';

                    if (currentAttendanceRecords.length === 0) {
                        rows = `
                        <tr>
                            <td>
                                <input type="time" class="form-control checkin" data-id="new">
                            </td>
                            <td>
                                <input type="time" class="form-control checkout" data-id="new">
                            </td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    `;
                    } else {
                        currentAttendanceRecords.forEach((r, i) => {
                            // Duration: use backend-computed duration field; null means still active
                            const durationDisplay = r.duration
                                ? r.duration
                                : '<span style="color:#E66136;font-weight:600;">Activeâ€¦</span>';

                            rows += `
                            <tr>
                                <td>
                                    <input type="time" class="form-control checkin"
                                        data-id="${r.id}"
                                        value="${r.check_in_time || ''}">
                                </td>
                                <td>
                                    <input type="time" class="form-control checkout"
                                        data-id="${r.id}"
                                        value="${r.check_out_time || ''}">
                                </td>
                                <td>${durationDisplay}</td>
                                <td>${r.overtime && r.overtime !== '00:00:00' ? r.overtime : '-'}</td>
                            </tr>
                        `;
                        });
                    }

                    document.getElementById('attendance-modal-body').innerHTML = rows;

                    const currentStatus = currentAttendanceRecords.length > 0 ?
                        (currentAttendanceRecords[0].status || 'absent') : 'absent';
                    document.getElementById('status').value = currentStatus;

                    new bootstrap.Modal(document.getElementById('attendanceEditModal')).show();
                });
        }

        window.saveAttendanceEdits = function () {
            const selectedStatus = document.getElementById('status').value;
            const records = [];

            document.querySelectorAll('#attendance-modal-body tr').forEach(tr => {
                const checkIn = tr.querySelector('.checkin');
                const checkOut = tr.querySelector('.checkout');
                const recordId = checkIn.dataset.id;

                records.push({
                    id: recordId,
                    check_in_time: checkIn.value,
                    check_out_time: checkOut.value
                });
            });

            fetch('/api/attendance/update-day-records', {
                method: 'POST',
                headers,
                body: JSON.stringify({
                    user_id: currentEditUser,
                    date: currentEditDate,
                    status: selectedStatus,
                    records
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadAttendance(currentMonth, currentYear);
                        bootstrap.Modal
                            .getInstance(document.getElementById('attendanceEditModal'))
                            .hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Attendance updated successfully!',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error updating attendance: ' + (data.message || 'Unknown error'),
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update attendance',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        window.saveBulkAttendance = function () {
            const fromDate = document.getElementById('from_date').value;
            const toDate = document.getElementById('to_date').value;
            const status = document.getElementById('bulk_status').value;
            const checkIn = document.getElementById('check_in_time').value;
            const checkOut = document.getElementById('check_out_time').value;

            if (!fromDate || !toDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please select date range',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            fetch('/api/attendance/bulk-update', {
                method: 'POST',
                headers,
                body: JSON.stringify({
                    user_id: currentEditUser,
                    from_date: fromDate,
                    to_date: toDate,
                    status,
                    check_in_time: checkIn,
                    check_out_time: checkOut
                })
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        loadAttendance(currentMonth, currentYear);
                        bootstrap.Modal
                            .getInstance(document.getElementById('attendanceBulkModal'))
                            .hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Attendance updated successfully',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message || 'Failed',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
        }

        // Multi Attendance Functions
        const defaultProfileImg = "<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>";

        window.toggleMultiAttendanceTimes = function () {
            const status = document.getElementById('multi_attendance_status').value;
            const container = document.getElementById('multi_time_fields_container');
            if (status === 'absent') {
                container.style.display = 'none';
            } else {
                container.style.display = 'flex';
            }
        };

        window.openMultiAttendanceModal = function () {
            // Set default date from calendar selection or today
            const dateInput = document.getElementById('multi_attendance_date');
            if (selectedDate) {
                dateInput.value = selectedDate;
            } else {
                dateInput.value = new Date().toISOString().split('T')[0];
            }

            // Set default status & times
            const statusSelect = document.getElementById('multi_attendance_status');
            statusSelect.value = 'present';
            toggleMultiAttendanceTimes();

            const checkInInput = document.getElementById('multi_check_in_time');
            const checkOutInput = document.getElementById('multi_check_out_time');
            if (companyInfo && companyInfo.start_time) {
                checkInInput.value = companyInfo.start_time.substring(0, 5);
            } else {
                checkInInput.value = '09:00';
            }
            if (companyInfo && companyInfo.end_time) {
                checkOutInput.value = companyInfo.end_time.substring(0, 5);
            } else {
                checkOutInput.value = '18:00';
            }

            // Reset search input
            document.getElementById('multi_employee_search').value = '';

            // Render employees list
            renderMultiAttendanceEmployeeList();

            // Show modal
            bootstrap.Modal.getOrCreateInstance(document.getElementById('multiAttendanceModal')).show();
        };

        window.renderMultiAttendanceEmployeeList = function () {
            const container = document.getElementById('multi_employee_list_container');
            if (!container) return;

            if (!users || users.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-muted">No employees found.</div>';
                document.getElementById('multi_total_emp_count').textContent = '0';
                updateMultiAttendanceCounts();
                return;
            }

            // Sort alphabetically by employee name
            const sortedUsers = [...users].sort((a, b) =>
                (a.employee_name || '').localeCompare(b.employee_name || '')
            );

            document.getElementById('multi_total_emp_count').textContent = sortedUsers.length;

            let html = '';
            sortedUsers.forEach(user => {
                const profileImg = user.profile_image ? `/upload/${user.profile_image}` : defaultProfileImg;
                const empName = user.employee_name || 'Employee';
                const designation = user.designation || '';
                const empStatus = (user.status || 'Active').toLowerCase();
                const statusBadge = empStatus !== 'active'
                    ? `<span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">${user.status}</span>`
                    : '';

                html += `
                    <div class="multi-emp-row d-flex align-items-center p-2 mb-1"
                         data-user-id="${user.user_id}"
                         data-user-name="${empName.toLowerCase()}"
                         data-designation="${designation.toLowerCase()}"
                         onclick="toggleMultiEmpRow(this, event)">
                        <input type="checkbox" class="form-check-input multi-emp-checkbox mt-0 me-2"
                               value="${user.user_id}"
                               onclick="event.stopPropagation(); updateMultiAttendanceCounts();">
                        <img src="${profileImg}" alt="${empName}" class="multi-emp-avatar me-2"
                             onerror="this.src='${defaultProfileImg}'">
                        <div class="flex-grow-1 text-truncate">
                            <div class="fw-semibold text-truncate" style="font-size: 13px; color: #2c3e50;">
                                ${empName} ${statusBadge}
                            </div>
                            <small class="text-muted text-truncate d-block" style="font-size: 11px;">
                                ${designation || 'Staff'}
                            </small>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            updateMultiAttendanceCounts();
        };

        window.toggleMultiEmpRow = function (rowEl, event) {
            const checkbox = rowEl.querySelector('.multi-emp-checkbox');
            if (!checkbox) return;
            checkbox.checked = !checkbox.checked;
            updateMultiAttendanceCounts();
        };

        window.filterMultiEmployees = function () {
            const query = (document.getElementById('multi_employee_search').value || '').trim().toLowerCase();
            const rows = document.querySelectorAll('#multi_employee_list_container .multi-emp-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.dataset.userName || '';
                const desig = row.dataset.designation || '';
                if (!query || name.includes(query) || desig.includes(query)) {
                    row.style.display = 'flex';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('multi_total_emp_count').textContent = visibleCount;
            updateMultiAttendanceCounts();
        };

        window.toggleSelectAllMultiEmployees = function (isChecked) {
            const rows = document.querySelectorAll('#multi_employee_list_container .multi-emp-row');
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cb = row.querySelector('.multi-emp-checkbox');
                    if (cb) cb.checked = isChecked;
                }
            });
            updateMultiAttendanceCounts();
        };

        window.clearAllMultiEmployees = function () {
            const checkboxes = document.querySelectorAll('#multi_employee_list_container .multi-emp-checkbox');
            checkboxes.forEach(cb => { cb.checked = false; });
            updateMultiAttendanceCounts();
        };

        window.updateMultiAttendanceCounts = function () {
            const rows = document.querySelectorAll('#multi_employee_list_container .multi-emp-row');
            let visibleCount = 0;
            let checkedVisibleCount = 0;
            let totalCheckedCount = 0;

            rows.forEach(row => {
                const cb = row.querySelector('.multi-emp-checkbox');
                const isChecked = cb && cb.checked;
                if (isChecked) {
                    totalCheckedCount++;
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }

                if (row.style.display !== 'none') {
                    visibleCount++;
                    if (isChecked) checkedVisibleCount++;
                }
            });

            // Update badge
            const badge = document.getElementById('multi_selected_count_badge');
            if (badge) {
                badge.textContent = `${totalCheckedCount} Selected`;
                badge.className = totalCheckedCount > 0 ? 'badge bg-primary' : 'badge bg-secondary';
            }

            // Update Select All checkbox state
            const selectAllCb = document.getElementById('multi_select_all');
            if (selectAllCb) {
                if (visibleCount > 0 && checkedVisibleCount === visibleCount) {
                    selectAllCb.checked = true;
                    selectAllCb.indeterminate = false;
                } else if (checkedVisibleCount > 0) {
                    selectAllCb.checked = false;
                    selectAllCb.indeterminate = true;
                } else {
                    selectAllCb.checked = false;
                    selectAllCb.indeterminate = false;
                }
            }
        };

        window.saveMultiAttendance = async function () {
            const date = document.getElementById('multi_attendance_date').value;
            const status = document.getElementById('multi_attendance_status').value;
            const checkIn = document.getElementById('multi_check_in_time').value;
            const checkOut = document.getElementById('multi_check_out_time').value;

            if (!date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Date Required',
                    text: 'Please select a valid date.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            const checkedBoxes = document.querySelectorAll('#multi_employee_list_container .multi-emp-checkbox:checked');
            const userIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value)).filter(id => id > 0);

            if (userIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Employee Selected',
                    text: 'Please select at least one employee using the checkboxes.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            const saveBtn = document.getElementById('btnSaveMultiAttendance');
            const originalHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Saving...';

            try {
                const response = await fetch('/api/attendance/multi-manage', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({
                        user_ids: userIds,
                        date: date,
                        status: status,
                        check_in_time: status !== 'absent' ? checkIn : null,
                        check_out_time: status !== 'absent' ? checkOut : null
                    })
                });

                const res = await response.json();

                if (res.status === 'success') {
                    // Close modal
                    const modalEl = document.getElementById('multiAttendanceModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    // Reload calendar data to reflect new attendance
                    loadAttendance(currentMonth, currentYear);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message || 'Attendance updated successfully.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: res.message || 'Error updating attendance.',
                        confirmButtonColor: '#d33'
                    });
                }
            } catch (err) {
                console.error('Error saving multi attendance:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred while saving attendance.',
                    confirmButtonColor: '#d33'
                });
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHtml;
            }
        };
    });
</script>

<?= $this->endSection(); ?>
