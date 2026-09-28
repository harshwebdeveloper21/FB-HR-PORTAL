<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>

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
    #attendance-calendar-table {
        width: 100%;
    }
    #attendance-calendar-table thead th {
        position: sticky;
        top: 0;
        background: #000;
        z-index: 10;
        color: white;
        text-align: center;
        padding: 8px 4px;
        font-size: 12px;
    }
    #attendance-calendar-table tbody td {
        text-align: center;
        padding: 8px 4px;
        vertical-align: middle;
    }
    .employee-name {
        position: sticky;
        left: 0;
        background: #000 !important;
        z-index: 11;
        min-width: 200px;
        text-align: left !important;
    }
    #attendance-calendar-table tbody .employee-name {
        background: white !important;
        z-index: 5;
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
        background-color: #ff6b35;
        color: white;
    }
    
    .hr-btnbg:hover {
        background-color: #e55a2b;
        color: white;
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
        
        /* Title and buttons container - stack vertically */
        .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }
        
        /* Buttons container - keep side by side */
        .d-flex.justify-content-between.align-items-center > div {
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
        
        .d-flex.flex-wrap.gap-3 p b {
            font-size: 10px;
        }
        
        /* Reduce spacing between sections on mobile */
        .row.mb-3 {
            margin-bottom: 10px !important;
        }
        
        /* Filter Section - Stack vertically on mobile */
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
        
        /* Table responsive adjustments - Remove side padding */
        .card > .card-body {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .table-responsive {
            max-height: 500px;
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            width: calc(100% + 30px);
            margin-left: -15px;
            margin-right: -15px;
            padding: 0;
            box-sizing: border-box;
        }
        
        #attendance-calendar-table {
            font-size: 11px;
        }
        
        #attendance-calendar-table thead th {
            padding: 6px 3px;
            font-size: 10px;
            min-width: 35px;
            width: 35px;
        }
        
        #attendance-calendar-table tbody td {
            padding: 6px 3px;
            font-size: 10px;
            min-width: 35px;
            width: 35px;
        }
        
        .employee-name {
            min-width: 150px;
            padding: 6px 8px !important;
        }
        
        .employee-name img {
            width: 30px !important;
            height: 30px !important;
        }
        
        .employee-name span {
            font-size: 11px;
        }
        
        /* Attendance status badges smaller on mobile */
        .badge {
            font-size: 7px;
            padding: 1px 2px;
        }
        
        /* Card body padding */
        .card-body {
            padding: 15px;
        }
    }
    
    /* Small Mobile (Portrait) */
    @media (max-width: 480px) {
        .card-body {
            padding: 10px;
        }
        
        .card-title {
            font-size: 16px !important;
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
        .card > .card-body {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .table-responsive {
            width: calc(100% + 20px);
            margin-left: -10px;
            margin-right: -10px;
        }
        
        #attendance-calendar-table {
            font-size: 10px;
        }
        
        #attendance-calendar-table thead th {
            padding: 4px 2px;
            font-size: 9px;
            min-width: 30px;
            width: 30px;
        }
        
        #attendance-calendar-table tbody td {
            padding: 4px 2px;
            font-size: 9px;
            min-width: 30px;
            width: 30px;
        }
        
        .employee-name {
            min-width: 120px;
            padding: 4px 6px !important;
        }
        
        .employee-name img {
            width: 25px !important;
            height: 25px !important;
        }
        
        .employee-name span {
            font-size: 10px;
        }
        
        .d-flex.flex-wrap.gap-3 p {
            font-size: 10px;
        }
    }
    
    /* Tablet Styles */
    @media (min-width: 769px) and (max-width: 1024px) {
        #attendance-calendar-table {
            font-size: 11px;
        }
        
        #attendance-calendar-table thead th {
            padding: 6px 3px;
            font-size: 11px;
        }
        
        #attendance-calendar-table tbody td {
            padding: 6px 3px;
            font-size: 11px;
        }
        
        .employee-name {
            min-width: 180px;
        }
    }
    
    /* Landscape Mobile */
    @media (max-width: 768px) and (orientation: landscape) {
        .table-responsive {
            max-height: 400px;
        }
        
        #attendance-calendar-table thead th {
            padding: 4px 2px;
            font-size: 9px;
        }
        
        #attendance-calendar-table tbody td {
            padding: 4px 2px;
            font-size: 9px;
        }
        
        .employee-name {
            min-width: 120px;
            padding: 4px 6px !important;
        }
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">Attendance</h4>
                    <div>
                        <a href="/view-calendar" class="btn btn-secondary me-2">
                            Calendar View
                        </a>
                    <a href="/attendence" class="btn hr-btnbg">
                        Manage Attendance
                    </a>
                    </div>
                </div>

                <!-- Statistics - Only show for HR/Admin users -->
                <?php if (isset($role) && ($role === 'hr' || $role === 'admin')): ?>
                <div class="row mb-4">
                    <!-- <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Employees</h6>
                                <h3 id="total-employees"><?= $totalEmployees ?? 0 ?></h3>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Present Today</h6>
                                <h3 id="present-employees"><?= $presentEmployees ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>Absent Today</h6>
                                <h3 id="absent-employees"><?= $absentEmployees ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Working Days</h6>
                                <h3><?= $workingDays ?? 0 ?></h3>
                            </div>
                        </div>
                    </div> -->
                </div>
                <?php endif; ?>

                <!-- Legend -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="d-flex flex-wrap gap-3">
                            <p class="me-3 mb-2"><span class="text-success fw-bold">P</span><b>&nbsp;:&nbsp;Present</b></p>
                            <p class="me-3 mb-2"><span class="text-danger fw-bold">A</span><b>&nbsp;:&nbsp;Absent</b></p>
                            <p class="me-3 mb-2"><span class="text-warning fw-bold">H</span><b>&nbsp;:&nbsp;Half-day</b></p>
                            <p class="me-3 mb-2"><span class="text-primary fw-bold" style="cursor:pointer">W</span><b>&nbsp;:&nbsp;Working</b></p>
                            <p class="me-3 mb-2"><span class="text-success fw-bold">P (L)</span> OR
                                                <span class="text-warning fw-bold">H (L)</span> OR
                                                <span class="text-primary fw-bold">W (L)</span><b>&nbsp;:&nbsp;Late</b></p>
                            <p class="me-3 mb-2"><span class="text-danger fw-bold">Sunday</b></p>
                            <p class="me-3 mb-2"><span class="text-info fw-bold">Holiday</b></p>
                            <p class="me-3 mb-2"><span class="bg-saturday-off text-dark fw-bold">Saturday Off</b></p>
                            <p class="me-3 mb-2"><span class="badge bg-warning text-dark" style="font-size: 10px;">OT</span><b>&nbsp;:&nbsp;Overtime</b></p>
                        </div>
                    </div>
                </div>

                <!-- Month/Year Selector and Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="d-flex gap-2 align-items-center">
                            <label class="mb-0">Month:</label>
                            <select id="month-select" class="form-control" style="width: auto;">
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
                            <label class="mb-0">Year:</label>
                            <select id="year-select" class="form-control" style="width: auto;">
                                <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="name-search" class="form-control" placeholder="Name: Search">
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="table-responsive" style="max-height: 600px; overflow: auto;">
                    <table id="attendance-calendar-table" class="table table-bordered table-sm mb-0">
                        <thead id="table-header" style="position: sticky; top: 0; background: #000; z-index: 10;">
                            <tr>
                                <th class="employee-name" style="position: sticky; left: 0; background: #000; z-index: 11; color: white;">Employee Name</th>
                            </tr>
                        </thead>
                        <tbody id="table-data">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
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

        <!-- <div class="alert alert-info">
          <small><strong>Note:</strong> Changing status to "Absent" will set work hours to 0. For "Present" or "Half Day", ensure check-in and check-out times are set.</small>
        </div> -->
      </div>

      <div class="modal-footer">
        <button class="btn hr-btnbg" data-bs-dismiss="modal">Cancel</button>
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
            <i class="mdi mdi-alert-circle-outline me-1"></i> This action will update attendance for all dates between selected range.
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn hr-btnbg" data-bs-dismiss="modal">Cancel</button>
        <button class="btn hr-btnbg" onclick="saveBulkAttendance()">Save Changes</button>
      </div>
    </div>
  </div>
</div>




<script>
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

    // Set current month/year
    document.getElementById('month-select').value = String(currentMonth).padStart(2, '0');
    document.getElementById('year-select').value = currentYear;

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
                renderAttendanceTable();
            }
        } catch (error) {
            console.error('Error loading attendance:', error);
        }
    };

    const renderAttendanceTable = () => {
        const todayDate = new Date().toISOString().split('T')[0];
        const selectedMonth = parseInt(document.getElementById('month-select').value);
        const selectedYear = parseInt(document.getElementById('year-select').value);
        const searchTerm = document.getElementById('name-search').value.toLowerCase();
        
        const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
        const tableHeader = document.getElementById('table-header');
        const tableData = document.getElementById('table-data');
        const defaultImagePath = "<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>";
        
        // Filter users
        const startOfMonthStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-01`;
        const endOfMonthStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(daysInMonth).padStart(2, '0')}`;

        const filteredUsers = users.filter(user => {
            if (!user.employee_name.toLowerCase().includes(searchTerm)) {
                return false;
            }
            const userStatus = (user.status || 'Active').toLowerCase();
            const isInactive = ['inactive', 'resigned', 'fired', 'removed'].includes(userStatus);
            const hasAnyPunch = (user.attendance || []).some(a => {
                const st = (a.status || '').toLowerCase();
                return ['present', 'half-day'].includes(st) || (a.check_in_time && a.check_in_time !== '00:00:00');
            });
            if (user.joining_date && user.joining_date > endOfMonthStr && !hasAnyPunch) {
                return false;
            }
            if (isInactive && user.last_working_day && user.last_working_day < startOfMonthStr && !hasAnyPunch) {
                return false;
            }
            return true;
        });

        // Build header
        let headerHTML = '<tr><th class="employee-name" style="position: sticky; left: 0; background: #000; z-index: 11; color: white;">Employee Name</th>';
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dateObj = new Date(selectedYear, selectedMonth - 1, day);
            const dayOfWeek = dateObj.getDay();
            let className = '';
            let textColor = 'text-white';

            if (holidays.find(h => h.holiday_date === dateStr)) {
                className = 'bg-info';
                textColor = 'text-white';
            } else if (saturdayOffDates.includes(dateStr)) {
                className = 'bg-saturday-off';
                textColor = '';
            } else if (dayOfWeek === 0) {
                className = 'bg-danger';
                textColor = 'text-white';
            } else if (dayOfWeek === 6) {
                className = 'bg-light';
                textColor = 'text-muted';
            } else {
                textColor = 'text-white';
            }

            headerHTML += `<th class="${className} ${textColor}" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px;">${day}</th>`;
        }
        headerHTML += '</tr>';
        tableHeader.innerHTML = headerHTML;

        // Build rows
        let rows = '';
        let presentCount = 0;
        let absentCount = 0;

        filteredUsers.forEach(user => {
            let isPresent = false;
            const profileImage = user.profile_image ? `/upload/${user.profile_image}` : defaultImagePath;
            
            // rows += `<tr class="employee-row">
            //     <td class="employee-name">
            //         <a href="/employee/profile/${user.user_id}" class="text-decoration-none text-dark">
            //             <div style="display: flex; align-items: center; gap: 10px;">
            //                 <img src="${profileImage}" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            //                 <span class="capitalize-text">${user.employee_name}</span>
            //             </div>
            //         </a>
            //     </td>`;
            rows += `<tr class="employee-row">
                <td class="employee-name" data-user-id="${user.user_id}" style="cursor:pointer;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="${profileImage}" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <span class="capitalize-text">${user.employee_name}</span>
                    </div>
                </td>`;

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dateObj = new Date(selectedYear, selectedMonth - 1, day);
                const dayOfWeek = dateObj.getDay();
                let cellClass = '';

                if (dayOfWeek === 6) cellClass = 'bg-light text-muted';

                // Check holidays
                const holiday = holidays.find(h => h.holiday_date === dateStr);
                if (holiday) {
                    rows += `<td class="bg-info text-white fw-bold text-center holiday-cell" data-title="${holiday.title || 'Holiday'}" data-date="${dateStr}" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px; cursor:pointer;">${holiday.title || 'Holiday'}</td>`;
                    continue;
                }

                // Check Saturday off
                if (saturdayOffDates.includes(dateStr)) {
                    rows += `<td class="bg-saturday-off text-dark fw-bold text-center" title="Saturday Off" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px;">Sat Off</td>`;
                    continue;
                }

                // Check Sunday
                if (dayOfWeek === 0) {
                    rows += `<td class="bg-danger text-white text-center fw-bold" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px;">Sunday</td>`;
                    continue;
                }

                // Check attendance
                const attendance = user.attendance?.find(record => {
                    const recordDate = record.date ? record.date.substring(0, 10) : '';
                    return recordDate === dateStr;
                });

                const isFuture = new Date(dateStr) > new Date(todayDate);
                const isToday = dateStr === todayDate;

                let isOutsideEmployment = false;
                const empStatus = (user.status || 'Active').toLowerCase();
                const currDateObj = new Date(dateStr);
                currDateObj.setHours(0,0,0,0);
                
                if (empStatus !== 'active') {
                    let lwdStr = user.last_working_day;
                    if (!lwdStr) {
                        const punches = (user.attendance || []).filter(a => {
                            const st = (a.status || '').toLowerCase();
                            return ['present', 'half-day'].includes(st) || (a.check_in_time && a.check_in_time !== '00:00:00');
                        });
                        if (punches.length > 0) {
                            punches.sort((a, b) => (b.date || '').localeCompare(a.date || ''));
                            lwdStr = punches[0].date.substring(0, 10);
                        }
                    }
                    if (lwdStr) {
                        const lwd = new Date(lwdStr);
                        lwd.setHours(0,0,0,0);
                        if (currDateObj > lwd) {
                            isOutsideEmployment = true;
                        }
                    }
                }
                
                if (user.joining_date) {
                    const jd = new Date(user.joining_date);
                    jd.setHours(0,0,0,0);
                    if (currDateObj < jd) {
                        isOutsideEmployment = true;
                    }
                }

                if (isFuture || isOutsideEmployment) {
                    rows += `<td class="${cellClass}" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px;"><span class="text-muted">-</span></td>`;
                } else if (attendance) {
                    let statusLetter = 'A';
                    let statusColor = 'danger';
                    const status = attendance.status ? attendance.status.toLowerCase() : '';
                    const isLate = attendance.is_late;

                    // Show "W" only for TODAY if currently checked in
                    if (isToday && attendance.check_in_time && !attendance.check_out_time) {
                        if (isLate == 1) {
                            statusLetter = 'W (L)';
                            statusColor = 'primary';
                        } else {
                            statusLetter = 'W';
                            statusColor = 'primary';
                        }
                    } else if (status === 'present') {
                        if (isLate == 1) {
                            statusLetter = 'P (L)';
                            statusColor = 'success';
                        } else {
                            statusLetter = 'P';
                            statusColor = 'success';
                        }
                        isPresent = true;
                    } else if (status === 'half-day') {
                        if (isLate == 1) {
                            statusLetter = 'H (L)';
                            statusColor = 'warning';
                        } else {
                            statusLetter = 'H';
                            statusColor = 'warning';
                        }
                    } else {
                        statusLetter = 'A';
                        statusColor = 'danger';
                    }
                    rows += `<td class="${cellClass}" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px;">
                        
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                            <span 
                                class="text-${statusColor} fw-bold attendance-cell"
                                data-user-id="${user.user_id}"
                                data-date="${dateStr}"
                                style="cursor:pointer"
                            >
                                ${statusLetter}
                            </span>
                            ${attendance.overtime && attendance.overtime !== '00:00:00' ? '<span class="badge bg-warning text-dark" style="font-size: 8px; padding: 1px 3px; margin-top: 1px;">OT</span>' : ''}
                        </div>
                    </td>`;

                } else {
                    rows += `<td class="${cellClass}" style="min-width: 40px; width: 40px; text-align: center; padding: 8px 4px;">
                        <span class="text-danger fw-bold attendance-cell" data-user-id="${user.user_id}" data-date="${dateStr}" data-status="A" style="cursor:pointer">A</span>
                    </td>`;
                }
            }

            if (isPresent) presentCount++;
            else absentCount++;

            rows += `</tr>`;
        });

        tableData.innerHTML = rows;
        document.getElementById('total-employees').innerText = filteredUsers.length;
        document.getElementById('present-employees').innerText = presentCount;
        document.getElementById('absent-employees').innerText = absentCount;
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

    document.getElementById('name-search').addEventListener('input', () => {
        renderAttendanceTable();
    });

    // Load initial data
    loadAttendance(currentMonth, currentYear);

    document.addEventListener('click', function (e) {
        const cell = e.target.closest('.attendance-cell');
        if (!cell) return;

        // Only HR/Admin
        <?php if (!isset($role) || !in_array($role, ['hr','admin'])): ?>
            return;
        <?php endif; ?>

        const userId = cell.dataset.userId;
        const date   = cell.dataset.date;

        openAttendanceModal(userId, date);
    });

    let currentEditUser = null;
    let currentEditDate = null;
    let currentAttendanceRecords = [];

    window.openAttendanceModal = function(userId, date) {
        currentEditUser = userId;
        currentEditDate = date;

        fetch(`/api/attendance/day-records?user_id=${userId}&date=${date}`, { headers })
            .then(res => res.json())
            .then(res => {
                currentAttendanceRecords = res.data || [];
                let rows = '';

                if (currentAttendanceRecords.length === 0) {
                    // No records exist - create empty form
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
                                <td>${r.work_hours || '-'}</td>
                                <td>${r.overtime || '-'}</td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('attendance-modal-body').innerHTML = rows;
                
                // Set current status
                const currentStatus = currentAttendanceRecords.length > 0 ? 
                    (currentAttendanceRecords[0].status || 'absent') : 'absent';
                document.getElementById('status').value = currentStatus;

                new bootstrap.Modal(document.getElementById('attendanceEditModal')).show();
            });
    }

    window.saveAttendanceEdits = function() {
        const selectedStatus = document.getElementById('status').value;
        const records = [];

        document.querySelectorAll('#attendance-modal-body tr').forEach(tr => {
            const checkIn  = tr.querySelector('.checkin');
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

    document.addEventListener('click', function (e) {
        const row = e.target.closest('.employee-name');
        if (!row) return;

        <?php if (!isset($role) || !in_array($role, ['hr','admin'])): ?>
            return;
        <?php endif; ?>

        const userId = row.dataset.userId;
        if (!userId) return;

        openBulkAttendanceModal(userId);
    });

    function openBulkAttendanceModal(userId) {
        currentEditUser = userId;

        // Default date = today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('from_date').value = today;
        document.getElementById('to_date').value   = today;

        document.getElementById('check_in_time').value = '';
        document.getElementById('check_out_time').value = '';
        document.getElementById('status').value = 'present';

        new bootstrap.Modal(
            document.getElementById('attendanceBulkModal')
        ).show();
    }

    window.saveBulkAttendance = function () {

        const fromDate = document.getElementById('from_date').value;
        const toDate   = document.getElementById('to_date').value;
        const status   = document.getElementById('bulk_status').value;

        const checkIn  = document.getElementById('check_in_time').value;
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



});
</script>

<?= $this->endSection(); ?>
