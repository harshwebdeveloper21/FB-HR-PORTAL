<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    .form-control {
        height: 2.3rem;
    }
    .employee-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .employee-row:hover {
        background-color: #f8f9fa;
    }

    .employee-row.selected {
        background-color: #e3f2fd;
    }

    .history-row {
        display: none;
        background-color: #f8f9fa;
    }

    .history-row.show {
        display: table-row;
    }

    .history-content {
        padding: 16px 20px;
    }

    .history-table {
        width: 100%;
        margin-top: 10px;
    }

    .history-table th {
        background-color: #e9ecef;
        padding: 8px;
        text-align: left;
        font-weight: 600;
        font-size: 12.5px;
    }

    .history-table td {
        padding: 8px;
        border-bottom: 1px solid #dee2e6;
        font-size: 12.5px;
    }

    .status-badge {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-present {
        background-color: #dcfce7;
        color: #15803d;
    }

    .status-absent {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .status-half-day {
        background-color: #fef3c7;
        color: #b45309;
    }

    .status-leave {
        background-color: #ffedd5;
        color: #c2410c;
    }

    .status-holiday {
        background-color: #ede9fe;
        color: #6d28d9;
    }

    .status-weekoff, .status-week_off {
        background-color: #f1f5f9;
        color: #475569;
    }

    .loading-spinner {
        text-align: center;
        padding: 20px;
    }

    /* Column Widths & Desktop Non-Scroll Enforcements */
    .desktop-only-col {
        display: table-cell;
    }

    .mobile-expand-col {
        display: none;
    }

    @media (min-width: 768px) {
        .attendance-summary-wrapper {
            overflow-x: hidden !important;
            overflow-y: visible !important;
            width: 100% !important;
        }

        #employee-attendance-table {
            width: 100% !important;
            table-layout: fixed !important;
            font-size: 12.5px;
            margin-bottom: 0;
        }

        #employee-attendance-table th,
        #employee-attendance-table td {
            padding: 9px 4px !important;
            vertical-align: middle !important;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #employee-attendance-table th:first-child,
        #employee-attendance-table td:first-child {
            text-align: left !important;
            padding-left: 8px !important;
        }

        /* Proportional Desktop Column Widths (Sum = 100%) */
        #col-emp { width: 23%; }
        #col-total { width: 8%; }
        #col-present { width: 10%; }
        #col-work { width: 11%; }
        #col-ot { width: 10%; }
        #col-late { width: 10%; }
        #col-leave { width: 9%; }
        #col-absent { width: 8%; }
        #col-action { width: 11%; }

        .stat-pill {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            min-width: 28px;
            line-height: 1.3;
        }
    }

    /* Mobile Expand Styles */
    .expanded-details {
        display: none;
        margin-top: 12px;
        padding: 12px;
        background-color: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid #E66136;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
    }

    .detail-value {
        color: #212529;
        font-size: 13px;
        text-align: right;
    }

    .detail-actions {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 2px solid #dee2e6;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .detail-actions .btn {
        flex: 1;
        min-width: 80px;
    }

    .expand-toggle i {
        transition: transform 0.3s ease;
    }

    .expand-toggle.active i {
        transform: rotate(180deg);
    }

    @media (max-width: 767px) {
        .desktop-only-col {
            display: none !important;
        }

        .mobile-expand-col {
            display: table-cell !important;
        }

        .expanded-details.show {
            display: block;
        }

        .attendance-filter-container {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            width: 100% !important;
        }

        .attendance-filter-container #month-selector,
        .attendance-filter-container #year-selector {
            flex: 1 1 calc(50% - 4px) !important;
            width: calc(50% - 4px) !important;
            min-width: 0 !important;
        }

        .attendance-filter-container a.btn {
            flex: 1 1 100% !important;
            width: 100% !important;
            justify-content: center !important;
            margin-top: 4px !important;
        }

        .history-table {
            font-size: 11px;
        }

        .history-table th,
        .history-table td {
            padding: 6px 4px;
        }

        /* Prevent row click on mobile */
        .employee-row {
            cursor: default;
        }
    }
</style>

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class="d-md-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title fw-bolder mb-1">Employee Attendance Summary</h4>
                <div class="d-md-flex gap-2 align-items-center attendance-filter-container">
                    <button type="button" id="btnExportAttendance" class="btn hr-btnbg attendenceall text-nowrap font-13">
                        <i class="mdi mdi-file-excel iconfontsize me-1"></i> Export Excel
                    </button>
                    <select id="month-selector" class="form-select font-13" style="min-width: 140px; width: auto;">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <select id="year-selector" class="form-select font-13" style="min-width: 110px; width: auto;">
                        <!-- Will be populated by JS -->
                    </select>
                    <a href="/view" class="btn hr-btnbg attendenceall text-nowrap font-13">
                        <i class="mdi mdi-calendar-clock iconfontsize me-1"></i> All Attendance
                    </a>
                </div>
            </div>

            <div class="table-responsive attendance-summary-wrapper">
                <table class="table table-hover align-middle" id="employee-attendance-table">
                    <thead>
                        <tr>
                            <th id="col-emp">Employee</th>
                            <th id="col-total" class="desktop-only-col text-center" title="Total Working Days">Total</th>
                            <th id="col-present" class="desktop-only-col text-center" title="Present Days">Present</th>
                            <th id="col-work" class="desktop-only-col text-center" title="Total Work Hours">Work Hrs</th>
                            <th id="col-ot" class="desktop-only-col text-center" title="Overtime Hours">Overtime</th>
                            <th id="col-late" class="desktop-only-col text-center" title="Late Hours">Late Hrs</th>
                            <th id="col-leave" class="desktop-only-col text-center" title="Leave Days">Leaves</th>
                            <th id="col-absent" class="desktop-only-col text-center" title="Absent Days">Absent</th>
                            <th id="col-action" class="desktop-only-col text-center">Action</th>
                            <th class="mobile-expand-col" style="width: 50px;">Details</th>
                        </tr>
                    </thead>
                    <tbody id="employee-attendance-body">
                        <tr>
                            <td colspan="10"><div class="d-flex justify-content-center align-items-center w-100 py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading attendance summary...</div></td>
                        </tr>
                    </tbody>
                </table>
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

        const defaultImagePath = "<?= base_url('assets/images/default_avatar.png') ?>";

        // Initialize month and year selectors
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();

        document.getElementById('month-selector').value = currentMonth;

        // Populate year selector (last 5 years)
        const yearSelector = document.getElementById('year-selector');
        for (let i = 0; i < 5; i++) {
            const year = currentYear - i;
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearSelector.appendChild(option);
        }

        // Load attendance data
        const loadAttendanceData = () => {
            const month = document.getElementById('month-selector').value;
            const year = document.getElementById('year-selector').value;

            document.getElementById('employee-attendance-body').innerHTML = 
                '<tr><td colspan="10"><div class="d-flex justify-content-center align-items-center w-100 py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading attendance summary...</div></td></tr>';

            const branchSelect = document.getElementById('navbarBranchSelect');
            const branchId = branchSelect ? branchSelect.value : '';
            let url = `/api/attendance/getAttendance/${month}/${year}`;
            if (branchId) {
                url += `?branch_id=${encodeURIComponent(branchId)}`;
            }

            fetch(url, { headers, credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderEmployeeTable(data.data, month, year);
                    } else {
                        document.getElementById('employee-attendance-body').innerHTML =
                            '<tr><td colspan="10"><div class="d-flex justify-content-center align-items-center w-100 py-4 text-muted">No attendance data found for this period.</div></td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading attendance:', error);
                    document.getElementById('employee-attendance-body').innerHTML =
                        '<tr><td colspan="10"><div class="d-flex justify-content-center align-items-center w-100 py-4 text-danger">Error loading data. Please try again.</div></td></tr>';
                });
        };

        const renderEmployeeTable = (attendanceData, month, year) => {
            const tbody = document.getElementById('employee-attendance-body');
            tbody.innerHTML = '';

            const users = attendanceData.users || [];
            const holidays = attendanceData.holidays || [];
            const saturdayOffDates = attendanceData.saturdayOffDates || [];

            // Calculate total working days (excluding future dates for current month/year)
            const totalDaysInMonth = new Date(year, month, 0).getDate();
            let workingDays = 0;
            const todayStr = new Date().toISOString().split('T')[0];
            const now = new Date();
            const currentMonthNow = now.getMonth() + 1;
            const currentYearNow = now.getFullYear();
            const isCurrentMonthYear = parseInt(month) === currentMonthNow && parseInt(year) === currentYearNow;

            for (let day = 1; day <= totalDaysInMonth; day++) {
                const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                // For the current month/year, do not count future dates as working days
                if (isCurrentMonthYear && date > todayStr) {
                    continue;
                }
                const dayOfWeek = new Date(date).getDay();
                const isHoliday = holidays.some(h => h.holiday_date === date);
                const isSaturdayOff = saturdayOffDates.includes(date);
                if (attendanceData.isIncludedHoliday == "1") {
                    workingDays++;
                } else if (dayOfWeek !== 0 && !isHoliday && !isSaturdayOff) {
                    workingDays++;
                }
            }

            users.forEach(user => {
                const attendance = user.attendance || [];
                const userStatus = (user.status || 'Active').toLowerCase();
                const isInactive = ['inactive', 'resigned', 'fired', 'removed'].includes(userStatus);
                const startOfMonthStr = `${year}-${String(month).padStart(2, '0')}-01`;
                const endOfMonthStr = `${year}-${String(month).padStart(2, '0')}-${String(totalDaysInMonth).padStart(2, '0')}`;

                // Calculate employment boundaries for this user
                let lwd = null;
                if (isInactive) {
                    if (user.last_working_day) {
                        lwd = user.last_working_day;
                    } else {
                        const punches = attendance.filter(a => {
                            const st = (a.status || '').toLowerCase();
                            return ['present', 'half-day'].includes(st) || (a.check_in_time && a.check_in_time !== '00:00:00');
                        });
                        if (punches.length > 0) {
                            punches.sort((a, b) => (b.date || '').localeCompare(a.date || ''));
                            lwd = punches[0].date.substring(0, 10);
                        }
                    }
                }
                let jd = user.joining_date || null;

                const hasAnyPunch = attendance.some(a => {
                    const st = (a.status || '').toLowerCase();
                    return ['present', 'half-day'].includes(st) || (a.check_in_time && a.check_in_time !== '00:00:00');
                });

                // Skip if joined after this month and had no activity
                if (jd && jd > endOfMonthStr && !hasAnyPunch) {
                    return;
                }

                // Skip if resigned before this month began and had no activity
                if (lwd && lwd < startOfMonthStr && !hasAnyPunch) {
                    return;
                }

                // Calculate total working days for this user specifically
                let userWorkingDays = 0;
                for (let day = 1; day <= totalDaysInMonth; day++) {
                    const dStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    if (isCurrentMonthYear && dStr > todayStr) continue;
                    if (lwd && dStr > lwd) continue;
                    if (jd && dStr < jd) continue;

                    const dayOfWeek = new Date(dStr).getDay();
                    const isHoliday = holidays.some(h => h.holiday_date === dStr);
                    const isSaturdayOff = saturdayOffDates.includes(dStr);
                    if (attendanceData.isIncludedHoliday == "1") {
                        userWorkingDays++;
                    } else if (dayOfWeek !== 0 && !isHoliday && !isSaturdayOff) {
                        userWorkingDays++;
                    }
                }

                // For the current month/year, ignore future dates and dates outside employment period in stats
                const effectiveAttendance = attendance.filter(a => {
                    const d = (a.date || '').substring(0, 10);
                    if (!d) return false;
                    if (isCurrentMonthYear && d > todayStr) return false;
                    if (lwd && d > lwd) return false;
                    if (jd && d < jd) return false;
                    return true;
                });

                // Group records by date to handle multiple check-ins on the same day
                const recordsByDate = {};
                effectiveAttendance.forEach(a => {
                    const date = a.date.substring(0, 10);
                    if (!recordsByDate[date]) {
                        recordsByDate[date] = [];
                    }
                    recordsByDate[date].push(a);
                });

                // Determine final status for each date based on API response
                let presentDays = 0;
                let halfDays = 0;
                let absentDays = 0;
                let leaveDays = 0;

                Object.values(recordsByDate).forEach(dateRecords => {
                    let hasLeave = false;
                    let hasHoliday = false;
                    let hasWeekOff = false;
                    let hasPresent = false;
                    let hasHalfDay = false;

                    dateRecords.forEach(record => {
                        const status = record.status ? record.status.toLowerCase() : '';
                        if (status === 'leave') {
                            hasLeave = true;
                        } else if (status === 'holiday') {
                            hasHoliday = true;
                        } else if (status === 'week off' || status === 'week_off') {
                            hasWeekOff = true;
                        } else if (status === 'present') {
                            hasPresent = true;
                        } else if (status === 'half-day') {
                            hasHalfDay = true;
                        }
                    });

                    if (hasLeave) {
                        leaveDays++;
                    } else if (hasHoliday || hasWeekOff) {
                        presentDays++;
                    } else if (hasPresent) {
                        presentDays++;
                    } else if (hasHalfDay) {
                        halfDays++;
                    } else {
                        absentDays++;
                    }
                });

                // Update absent days calculation
                absentDays = userWorkingDays - presentDays - halfDays - leaveDays;
                if (absentDays < 0) absentDays = 0;

                // Calculate total work hours
                let totalSeconds = 0;
                let totalOvertimeSeconds = 0;
                let totalLateMinutes = 0;

                attendance.forEach(record => {
                    if (record.work_hours && record.work_hours !== '00:00:00') {
                        totalSeconds += timeToSeconds(record.work_hours);
                    } else if (record.check_in_time && record.check_out_time) {
                        const workHours = calculateWorkHours(record);
                        totalSeconds += workHours;
                    }

                    if (record.overtime) {
                        totalOvertimeSeconds += timeToSeconds(record.overtime);
                    }

                    if (record.is_late && record.late_minutes) {
                        totalLateMinutes += parseInt(record.late_minutes);
                    }
                });

                const totalWorkHours = formatSeconds(totalSeconds);
                const totalOvertime = formatSeconds(totalOvertimeSeconds);
                const totalLateTime = formatMinutesToHours(totalLateMinutes);

                const profileImage = user.profile_image
                    ? `/upload/${user.profile_image}`
                    : defaultImagePath;

                const row = document.createElement('tr');
                row.className = 'employee-row';
                row.dataset.userId = user.user_id;
                row.innerHTML = `
                    <td class="py-2">
                        <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                            <img src="${profileImage}" alt="Profile"
                                 style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <div class="text-truncate" style="min-width: 0;">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold text-dark font-13 text-truncate d-block" title="${user.employee_name}">${user.employee_name}</span>
                                    ${isInactive ? `<span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px; padding: 2px 5px;">Inactive</span>` : ''}
                                </div>
                                ${isInactive && lwd ? `<small class="text-muted font-11 d-block">Left: ${lwd}</small>` : ''}
                            </div>
                        </div>
                        <div class="expanded-details" id="employee-details-${user.user_id}" onclick="event.stopPropagation();">
                            <div class="detail-row">
                                <span class="detail-label">Total Days:</span>
                                <span class="detail-value">${userWorkingDays}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Present Days:</span>
                                <span class="detail-value">${presentDays + (halfDays * 0.5)}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Work Hours:</span>
                                <span class="detail-value">${totalWorkHours}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Overtime:</span>
                                <span class="detail-value">${totalOvertime || '0h 0m'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Late Hours:</span>
                                <span class="detail-value">${totalLateTime || '0h 0m'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Leaves:</span>
                                <span class="detail-value">${leaveDays}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Absent:</span>
                                <span class="detail-value">${absentDays > 0 ? absentDays : 0}</span>
                            </div>
                            <div class="detail-actions">
                                <button class="btn btn-sm btn-info view-history-btn-mobile text-white" data-user-id="${user.user_id}">
                                    <i class="mdi mdi-history"></i> View History
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="desktop-only-col text-center fw-bold text-secondary">${userWorkingDays}</td>
                    <td class="desktop-only-col text-center">
                        <span class="stat-pill text-success bg-success-subtle">${presentDays + (halfDays * 0.5)}</span>
                    </td>
                    <td class="desktop-only-col text-center font-12 fw-semibold text-dark">${totalWorkHours}</td>
                    <td class="desktop-only-col text-center font-12 text-muted">${totalOvertime || '0h 0m'}</td>
                    <td class="desktop-only-col text-center font-12 text-muted">${totalLateTime || '0h 0m'}</td>
                    <td class="desktop-only-col text-center">
                        <span class="stat-pill text-warning bg-warning-subtle">${leaveDays}</span>
                    </td>
                    <td class="desktop-only-col text-center">
                        <span class="stat-pill text-danger bg-danger-subtle">${absentDays > 0 ? absentDays : 0}</span>
                    </td>
                    <td class="desktop-only-col text-center">
                        <button class="btn btn-sm hr-btnbg px-2 py-1 view-history-btn" data-user-id="${user.user_id}" style="font-size: 11.5px; border-radius: 6px;">
                            <i class="mdi mdi-history"></i> History
                        </button>
                    </td>
                    <td class="mobile-expand-col text-center">
                        <button type="button" class="expand-toggle" data-target="employee-details-${user.user_id}" aria-label="Expand details"></button>
                    </td>
                `;

                // History row (hidden by default)
                const historyRow = document.createElement('tr');
                historyRow.className = 'history-row';
                historyRow.id = `history-${user.user_id}`;
                historyRow.innerHTML = `
                    <td colspan="10">
                        <div class="history-content">
                            <h6 class="fw-bold text-dark mb-2"><i class="mdi mdi-calendar-clock text-primary me-1"></i> Check-in History for ${user.employee_name}</h6>
                            <div id="history-data-${user.user_id}">
                                <div class="loading-spinner">Loading history...</div>
                            </div>
                        </div>
                    </td>
                `;

                tbody.appendChild(row);
                tbody.appendChild(historyRow);
            });

            if (tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No attendance records found for this period</td></tr>';
            }

            // Add click event listeners for desktop view history buttons
            document.querySelectorAll('.view-history-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const userId = btn.dataset.userId;
                    toggleHistory(userId, month, year);
                });
            });

            // Add click event listeners for mobile view history buttons
            document.querySelectorAll('.view-history-btn-mobile').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const userId = btn.dataset.userId;
                    toggleHistory(userId, month, year);
                });
            });

            // Desktop row click for history (only on desktop)
            document.querySelectorAll('.employee-row').forEach(row => {
                row.addEventListener('click', (e) => {
                    if (!e.target.closest('.expand-toggle') && 
                        !e.target.closest('.view-history-btn-mobile') && 
                        !e.target.closest('.view-history-btn') &&
                        window.innerWidth >= 768) {
                        const userId = row.dataset.userId;
                        toggleHistory(userId, month, year);
                    }
                });
            });
        };

        const toggleHistory = (userId, month, year) => {
            const historyRow = document.getElementById(`history-${userId}`);
            const employeeRow = document.querySelector(`tr[data-user-id="${userId}"]`);

            // Close other open histories
            document.querySelectorAll('.history-row').forEach(row => {
                if (row.id !== `history-${userId}`) {
                    row.classList.remove('show');
                }
            });
            document.querySelectorAll('.employee-row').forEach(row => {
                if (row.dataset.userId !== userId) {
                    row.classList.remove('selected');
                }
            });

            // Toggle current history
            if (historyRow.classList.contains('show')) {
                historyRow.classList.remove('show');
                employeeRow.classList.remove('selected');
            } else {
                historyRow.classList.add('show');
                employeeRow.classList.add('selected');
                loadEmployeeHistory(userId, month, year);
            }
        };

        const loadEmployeeHistory = (userId, month, year) => {
            const historyContainer = document.getElementById(`history-data-${userId}`);

            fetch(`/api/attendance/getAttendance/${month}/${year}`, { headers })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const user = data.data.users.find(u => u.user_id == userId);
                        if (user) {
                            renderHistory(historyContainer, user.attendance, user);
                        }
                    }
                })
                .catch(error => {
                    historyContainer.innerHTML = '<p class="text-danger">Error loading history</p>';
                });
        };

        const renderHistory = (container, attendance, user) => {
            if (!attendance || attendance.length === 0) {
                container.innerHTML = '<p class="text-muted">No attendance records found</p>';
                return;
            }

            let lwd = null;
            if (user && user.status && user.status.toLowerCase() !== 'active') {
                if (user.last_working_day) {
                    lwd = user.last_working_day;
                } else {
                    const punches = attendance.filter(a => {
                        const st = (a.status || '').toLowerCase();
                        return ['present', 'half-day'].includes(st) || (a.check_in_time && a.check_in_time !== '00:00:00');
                    });
                    if (punches.length > 0) {
                        punches.sort((a, b) => (b.date || '').localeCompare(a.date || ''));
                        lwd = punches[0].date.substring(0, 10);
                    }
                }
            }
            let jd = (user && user.joining_date) ? user.joining_date : null;

            const todayStr = new Date().toISOString().split('T')[0];
            const validAttendance = attendance.filter(r => {
                const d = (r.date || '').substring(0, 10);
                if (!d) return false;
                if (d > todayStr) return false;
                if (lwd && d > lwd) return false;
                if (jd && d < jd) return false;
                return true;
            });

            if (validAttendance.length === 0) {
                container.innerHTML = '<p class="text-muted">No attendance records found</p>';
                return;
            }

            validAttendance.sort((a, b) => new Date(b.date) - new Date(a.date));

            let html = '<table class="history-table"><thead><tr>';
            html += '<th>Date</th><th>Check In</th><th>Check Out</th><th>Work Hours</th><th>Overtime</th><th>Late</th><th>Status</th>';
            html += '</tr></thead><tbody>';

            validAttendance.forEach(record => {
                const statusClean = (record.status || '').toLowerCase().replace(/\s+/g, '-');
                const statusClass = `status-${statusClean}`;
                const lateInfo = record.is_late ? formatMinutesToHours(record.late_minutes) : '-';
                const overtime = record.overtime ? formatTimeString(record.overtime) : '-';
                const workHours = calculateWorkHoursDisplay(record);

                html += `<tr>
                    <td>${formatDate(record.date)}</td>
                    <td>${record.check_in_time || '-'}</td>
                    <td>${record.check_out_time || '-'}</td>
                    <td>${workHours}</td>
                    <td>${overtime}</td>
                    <td>${lateInfo}</td>
                    <td><span class="status-badge ${statusClass}">${record.status}</span></td>
                </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        };

        // Helper functions
        const calculateWorkHours = (record) => {
            if (!record.check_in_time || !record.check_out_time) return 0;
            const checkIn = new Date(`2000-01-01 ${record.check_in_time}`);
            const checkOut = new Date(`2000-01-01 ${record.check_out_time}`);
            return (checkOut - checkIn) / 1000;
        };

        const formatSeconds = (seconds) => {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        };

        const formatMinutesToHours = (minutes) => {
            if (!minutes || minutes === 0) return '0h 0m';
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours}h ${mins}m`;
        };

        const timeToSeconds = (timeString) => {
            if (!timeString) return 0;
            const parts = timeString.split(':');
            const hours = parseInt(parts[0]) || 0;
            const minutes = parseInt(parts[1]) || 0;
            const seconds = parseInt(parts[2]) || 0;
            return (hours * 3600) + (minutes * 60) + seconds;
        };

        const formatTimeString = (timeString) => {
            if (!timeString || timeString === '00:00:00') return '0h 0m';
            const parts = timeString.split(':');
            const hours = parseInt(parts[0]) || 0;
            const minutes = parseInt(parts[1]) || 0;
            return `${hours}h ${minutes}m`;
        };

        const calculateWorkHoursDisplay = (record) => {
            if (!record.check_in_time || !record.check_out_time) return '-';
            const checkIn = new Date(`2000-01-01 ${record.check_in_time}`);
            const checkOut = new Date(`2000-01-01 ${record.check_out_time}`);
            const seconds = (checkOut - checkIn) / 1000;
            return formatSeconds(seconds);
        };

        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        };

        // Event listeners for month/year change
        document.getElementById('month-selector').addEventListener('change', loadAttendanceData);
        document.getElementById('year-selector').addEventListener('change', loadAttendanceData);

        // Export Attendance to Excel handler
        document.getElementById('btnExportAttendance')?.addEventListener('click', function () {
            const month = document.getElementById('month-selector').value;
            const year = document.getElementById('year-selector').value;
            const branchSelect = document.getElementById('navbarBranchSelect');
            const branchId = branchSelect ? branchSelect.value : '';

            let exportUrl = `/api/attendance/export?month=${month}&year=${year}`;
            if (branchId) {
                exportUrl += `&branch_id=${encodeURIComponent(branchId)}`;
            }

            const token = localStorage.getItem('token');
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Exporting...';

            fetch(exportUrl, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                credentials: 'same-origin'
            })
            .then(async response => {
                if (!response.ok) {
                    throw new Error('Export failed');
                }
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                const monthNames = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const monthName = monthNames[parseInt(month)] || month;
                a.download = `Attendance_Summary_${monthName}_${year}.xlsx`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(err => {
                console.error('Export error:', err);
                alert('Failed to export attendance data. Please try again.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Initial load
        loadAttendanceData();
    });
</script>

<?= $this->endSection() ?>
