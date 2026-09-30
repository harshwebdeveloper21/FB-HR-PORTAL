<!-- Group Payroll Management -->
<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
  .capitalize-text {
    text-transform: capitalize;
  }

  .salary-manage {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  @media (max-width: 767px) {
    .cart-sm-title {
      font-size: 12px !important;
      margin-bottom: 5px !important;
    }

    .attendenceall {
      font-size: 9px !important;
      padding: 5.3px !important;
    }

    .iconfontsize {
      font-size: 11px !important;
    }
  }

  #deductionBreakdownModal .breakdown-section {
    border-left: 3px solid #E66136;
    padding-left: 0.75rem;
    margin-bottom: 1rem;
  }

  #deductionBreakdownModal .breakdown-list {
    max-height: 120px;
    overflow-y: auto;
  }

  #deductionBreakdownModal .summary-row {
    font-weight: 600;
  }
</style>
<!-- Deduction breakdown modal -->
<div class="modal fade" id="deductionBreakdownModal" tabindex="-1" aria-labelledby="deductionBreakdownModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#E66136;color:white;">
        <h5 class="modal-title" id="deductionBreakdownModalLabel">
          <i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted?
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="deductionBreakdownBody">
        <div class="text-center py-4" id="deductionBreakdownLoading">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 mb-0">Loading details...</p>
        </div>
        <div id="deductionBreakdownContent" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>
<!-- Extra Day info modal -->
<div class="modal fade" id="extraDayModal" tabindex="-1" aria-labelledby="extraDayModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
    <div class="modal-content">
      <div class="modal-header" style="background:#E66136;color:white;">
        <h5 class="modal-title" id="extraDayModalLabel">
          <i class="mdi mdi-calendar-plus me-1"></i> Extra Day Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="extraDayBody">
        <div class="text-center py-4" id="extraDayLoading">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 mb-0">Loading details...</p>
        </div>
        <div id="extraDayContent" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>
<!-- Remark and Adjustment Modal -->
<div class="modal fade" id="remarkModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background:#E66136;color:white;">
        <h5 class="modal-title" id="remarkModalLabel">
          <i class="mdi mdi-comment-text-outline me-1"></i> Salary Adjustment & Remark
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="remarkForm">
          <input type="hidden" id="modal_employee_id">
          <div class="mb-3 d-flex justify-content-between">
            <div>
                <label class="form-label fw-bold">Employee:</label>
                <span id="modal_employee_name" class="ms-1"></span>
            </div>
            <div>
                <label class="form-label fw-bold">Month:</label>
                <span id="modal_month_year" class="ms-1"></span>
            </div>
          </div>

          <div class="mb-3">
            <label for="adjustment_amount" class="form-label fw-bold">This Month Adjustment (₹)</label>
            <input type="number" class="form-control" id="adjustment_amount" placeholder="e.g. 500 or -200" step="0.01">
            <small class="text-muted">Enter positive for addition, negative for deduction.</small>
          </div>

          <div class="mb-3">
            <label for="adjustment_remark" class="form-label fw-bold">Reason/Remark</label>
            <textarea class="form-control" id="adjustment_remark" rows="3" placeholder="Reason for adjustment..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
          style="min-width:130px; height:38px;">Cancel</button>
        <button type="button" class="btn hr-btnbg" id="btnConfirmSaveSalary"
          style="background:#E66136; border-color:#E66136; color:white; min-width:130px; height:38px;">
          <i class="mdi mdi-content-save me-1"></i> Save Remark
        </button>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="salary-manage">
          <div>
            <h4 class="card-title mb-0">Manage Salary of Employee</h4>
          </div>

          <?php $role = session()->get("role"); ?>
          <?php if ($role !== "employee"): ?>
            <div class="d-flex align-items-center flex-wrap gap-2">
              <?php
              $currentMonth = date("Y-m");
              // Default to last month
              $lastMonth = date("Y-m", strtotime("first day of last month"));
              ?>
              <input type="month" id="salaryMonth" name="salaryMonth"
                class="form-control" value="<?= esc($month ?? $lastMonth) ?>"
                max="<?= $currentMonth ?>" style="width: auto; min-width: 140px;" />

              <input type="text" id="employeeSearch" class="form-control" placeholder="Search employee name..." style="width: auto; min-width: 180px;">

              <a href="/payrollview" class="btn hr-btnbg text-nowrap">
                <i class="mdi mdi-arrow-left iconfontsize me-1"></i> Back
              </a>

              <button type="submit" form="saveAllForm" class="btn hr-btnbg text-nowrap">
                <i class="mdi mdi-content-save-all-outline iconfontsize me-1"></i> Update All
              </button>
            </div>
          <?php endif; ?>
        </div>

        <!-- Save All Form -->
        <form action="<?= base_url(
          "/api/payroll/saveAll",
        ) ?>" method="post" id="saveAllForm" class="individual-save-form">
          <?= csrf_field() ?>
          <input type="hidden" name="month" value="<?= $month ?>">
          <div class="table-responsive">
            <table class="table table-striped slalary-detail" id="payroll-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Leaves / Half-Day<br><small class="text-muted" style="font-size: 10px; color:white !important;">(Leaves / Half-Day)</small></th>
                  <th>Used Leave<br><small class="text-muted" style="font-size: 10px; color:white !important;">(Paid / Sick)</small></th>
                  <th>Rem. Leave<br><small class="text-muted" style="font-size: 10px; color:white !important;">(Paid / Sick)</small></th>
                  <th>Per-Day<br>Salary</th>
                  <th>Extra Day</th>
                  <th>Tax</th>
                  <th>Salary<br>Deduction</th>
                  <th>Net Salary</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($employees as $index => $emp): ?>
                  <tr data-overtime-pay="<?= esc($emp['overtime_pay'] ?? 0) ?>"
                    data-late-deduction="<?= esc($emp['late_deduction'] ?? 0) ?>"
                    data-base-deduction="<?= esc($emp['base_deduction'] ?? $emp['salary_deduction']) ?>"
                    data-per-day="<?= esc($emp['per_day']) ?>">
                    <!-- Name: avatar + name & salary text below -->
                    <td>
                      <a href="/employee/profile/<?= $emp["id"] ?>" class="text-decoration-none text-dark">
                        <div style="display: flex; align-items: center; gap: 12px;">
                          <?php if (!empty($emp["profile_image"])) { ?>
                            <img src="/upload/<?= esc($emp["profile_image"]) ?>" alt="Profile" onerror="this.onerror=null;this.src='/assets/images/default_avatar.png';"
                              style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                          <?php } else { ?>
                            <img src="/assets/images/default_avatar.png" alt="Profile"
                              style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                          <?php } ?>
                          <div style="display: flex; flex-direction: column;">
                            <div class="d-flex align-items-center gap-1">
                              <?php $empFullName = trim(($emp["firstname"] ?? '') . ' ' . ($emp["lastname"] ?? '')) ?: ($emp["firstname"] ?? ''); ?>
                              <span class="capitalize-text fw-bold"><?= esc($empFullName) ?></span>
                              <?php 
                              $empStatus = strtolower(trim($emp['status'] ?? 'active'));
                              if (in_array($empStatus, ['inactive', 'resigned', 'fired', 'removed'])) { ?>
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px; padding: 1px 4px;">Inactive</span>
                              <?php } ?>
                            </div>
                            <small class="text-muted" style="font-size: 13px;">₹<?= number_format($emp["salary"], 0) ?></small>
                          </div>
                        </div>
                      </a>
                    </td>
                    <!-- Leaves / Half-Day: merged -->
                    <td>
                      <div style="display:flex; flex-direction: column; align-items: center;">
                        <div style="display:flex; gap:4px; align-items:center;">
                          <input type="number" name="leaves[]" class="form-control form-control-sm leave-input"
                            value="<?= $emp["leaves"] ?>" min="0" data-index="<?= $index ?>"
                            data-salary="<?= $emp["salary"] ?>" data-tax="<?= $emp["tax_amount"] ?? 0 ?>"
                            data-days="<?= $emp["days_in_month"] ?>" data-month="<?= date("Y-m", strtotime($month)) ?>"
                            <?php if (in_array(strtolower(trim($emp['status'] ?? 'active')), ['inactive', 'resigned', 'fired', 'removed'])) { ?>
                              title="Includes unworked days after employment ended"
                            <?php } ?>
                            style="width:58px;">
                          <!-- <span class="text-muted">/</span> -->
                          <input type="number" name="half_day[]" class="form-control form-control-sm half_day-input"
                            value="<?= $emp["half_days"] ?>" min="0" data-index="<?= $index ?>"
                            data-salary="<?= $emp["salary"] ?>" data-tax="<?= $emp["tax_amount"] ?? 0 ?>"
                            data-days="<?= $emp["days_in_month"] ?>" data-month="<?= date("Y-m", strtotime($month)) ?>"
                            style="width:58px;">
                        </div>
                        <!-- <small class="text-muted" style="font-size: 9px; margin-top: 2px;">Leaves / Half-Day</small> -->
                      </div>
                    </td>
                    <!-- Used Leave (Paid / Sick): merged -->
                    <td>
                      <div style="display:flex; flex-direction: column; align-items: center;">
                        <div style="display:flex; gap:4px; align-items:center;">
                          <input type="number" name="paid_leave[]" class="form-control form-control-sm paid-leave-input"
                            value="<?= $emp["used_paid_leaves"] ?? 0 ?>" min="0" step="0.5" data-index="<?= $index ?>"
                            data-salary="<?= $emp["salary"] ?>" data-tax="<?= $emp["tax_amount"] ?? 0 ?>"
                            data-allocated="<?= $emp['opening_paid_leaves'] ?? 0 ?>"
                            data-days="<?= $emp["days_in_month"] ?>" data-month="<?= date("Y-m", strtotime($month)) ?>"
                            style="width:58px;">
                          <!-- <span class="text-muted">/</span> -->
                          <input type="number" name="sick_leave[]" class="form-control form-control-sm sick-leave-input"
                            value="<?= $emp["used_sick_leaves"] ?? 0 ?>" min="0" step="0.5" data-index="<?= $index ?>"
                            data-salary="<?= $emp["salary"] ?>" data-tax="<?= $emp["tax_amount"] ?? 0 ?>"
                            data-allocated="<?= $emp['opening_casual_leaves'] ?? 0 ?>"
                            data-days="<?= $emp["days_in_month"] ?>" data-month="<?= date("Y-m", strtotime($month)) ?>"
                            style="width:58px;">
                        </div>
                        <!-- <small class="text-muted" style="font-size: 9px; margin-top: 2px;">Paid / Sick</small> -->
                      </div>
                    </td>
                    <!-- Rem. Leave (Paid / Sick): merged into one cell -->
                    <td>
                      <div style="display:flex; flex-direction: column; align-items: center;">
                        <div>
                          <span class="rem-paid-leave"><?= esc($emp['remaining_paid_leaves'] ?? 0) ?></span>
                          <span class="text-muted"> / </span>
                          <span class="rem-sick-leave"><?= esc($emp['remaining_casual_leaves'] ?? 0) ?></span>
                        </div>
                        <!-- <small class="text-muted" style="font-size: 9px; margin-top: 2px;">Paid / Sick</small> -->
                      </div>
                    </td>
                    <td>₹<?= number_format($emp["per_day"], 2) ?><br><small
                        class="text-muted">₹<?= number_format($emp["per_hour"] ?? ($emp["per_day"] / 8), 2) ?>/hr</small>
                    </td>
                    <td>
                      <div class="d-flex align-items-center justify-content-center">
                        <span class="fw-bold me-1"><?= esc($emp['extra_days'] ?? 0) ?></span>
                        <?php if (($emp['extra_days'] ?? 0) > 0): ?>
                          <button type="button" class="btn btn-sm btn-link p-0 ms-1 btn-extra-day-info"
                            title="View extra day attendance" data-user-id="<?= $emp["user_id"] ?>"
                            data-month="<?= esc($month) ?>" data-name="<?= esc($empFullName) ?>">
                            <i class="mdi mdi-information-outline text-primary" style="font-size:1.1rem;"></i>
                          </button>
                        <?php endif; ?>
                      </div>
                      <?php if (($emp['extra_day_pay'] ?? 0) > 0): ?>
                        <div class="d-flex align-items-center justify-content-center mt-1">
                          <small class="text-success extra-day-pay-display" style="font-size: 10px; white-space:nowrap;">₹<?= number_format($emp['extra_day_pay'], 2) ?></small>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td><?= esc($emp["tax"]) ?></td>
                    <td class="deduction-cell">
                      <span>₹<?= number_format($emp["salary_deduction"], 2) ?></span>
                      <button type="button" class="btn btn-sm btn-link p-0 ms-1 btn-deduction-info"
                        title="Why is this amount deducted?" data-user-id="<?= $emp["user_id"] ?>"
                        data-month="<?= esc($month) ?>" data-name="<?= esc($empFullName) ?>">
                        <i class="mdi mdi-information-outline text-primary" style="font-size:1.1rem;"></i>
                      </button>
                    </td>
                    <td class="net-salary-cell">₹<?= number_format(
                      $emp["net_salary"],
                      2,
                    ) ?></td>
                    <!-- Hidden Inputs -->
                    <input type="hidden" name="employee_id[]" value="<?= $emp[
                      "id"
                    ] ?>">
                    <input type="hidden" name="salary[]" value="<?= $emp[
                      "salary"
                    ] ?>">
                    <input type="hidden" name="deduction[]" class="deduction-input" value="<?= $emp[
                      "salary_deduction"
                    ] ?>">
                    <input type="hidden" name="net_salary[]" class="net-salary-input" value="<?= $emp[
                      "net_salary"
                    ] ?>">
                    <input type="hidden" name="overtime_pay[]" class="overtime-pay-input"
                      value="<?= $emp["overtime_pay"] ?? 0 ?>">
                    <input type="hidden" name="total_overtime_hours[]" class="total-overtime-hours-input"
                      value="<?= $emp["total_overtime_hours"] ?? 0 ?>">
                    <?php 
                      // Calculate base net salary to see if extra_day_pay is included in the saved net_salary
                      $baseNetSal = round($emp['salary'] - $emp['salary_deduction'] - $emp['tax_deduction'] + ($emp['overtime_pay'] ?? 0), 2);
                      $isExtraAdded = (isset($emp['net_salary']) && round($emp['net_salary'], 2) > $baseNetSal);
                    ?>
                    <input type="hidden" name="extra_day_pay[]" class="extra-day-pay-input"
                      value="<?= $isExtraAdded ? esc($emp['extra_day_pay']) : '0' ?>">
                    <!-- Per Row Save -->
                    <td>
                      <input type="hidden" class="single-employee-id" value="<?= $emp[
                        "id"
                      ] ?>">
                      <input type="hidden" class="single-salary" value="<?= $emp[
                        "salary"
                      ] ?>">
                      <input type="hidden" class="single-leave-input" value="<?= $emp[
                        "leaves"
                      ] ?>">
                      <input type="hidden" class="single-halfday-input" value="<?= $emp[
                        "half_days"
                      ] ?>">
                      <input type="hidden" class="single-paid-leave-input" value="<?= $emp[
                        "used_paid_leaves"
                      ] ?? 0 ?>">
                      <input type="hidden" class="single-sick-leave-input" value="<?= $emp[
                        "used_sick_leaves"
                      ] ?? 0 ?>">
                      <input type="hidden" class="single-deduction-input" value="<?= $emp[
                        "salary_deduction"
                      ] ?>">
                      <input type="hidden" class="single-net-salary-input" value="<?= $emp[
                        "net_salary"
                      ] ?>">
                      <input type="hidden" class="single-month" value="<?= $month ?>">
                      <div style="display:flex; gap:3px; align-items:center;">
                        <button type="button" class="btn btn-sm btn-open-remark" data-id="<?= $emp["user_id"] ?>"
                          title="Add Salary Adjustment & Remark"
                          style="background-color:#6c757d;color:white;font-size:11px;padding:3px 7px;">
                          <i class="mdi mdi-comment-text-outline"></i>
                        </button>
                        <?php if (!empty($emp["is_saved"]) && !empty($emp["payroll_id"])): ?>
                          <a href="/payroll/<?= $emp['payroll_id'] ?>" title="Edit Payroll"
                            class="btn btn-sm"
                            style="background-color:#ffc107;color:white;font-size:11px;padding:3px 7px;">
                            <i class="mdi mdi-pencil"></i>
                          </a>
                        <?php endif; ?>
                        <?php if (!empty($emp["is_saved"])): ?>
                          <button type="button" class="btn btn-sm btn-save-single" data-id="<?= $emp["user_id"] ?>"
                            style="background-color:#28a745;color:white;font-size:11px;padding:3px 7px;">
                            <i class="mdi mdi-content-save"></i> Update
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-sm btn-save-single" data-id="<?= $emp["user_id"] ?>"
                            style="background-color:#E66136;color:white;font-size:11px;padding:3px 7px;">
                            <i class="mdi mdi-content-save"></i> Save
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>

  // Bulk Validation for "Update All"
  const form = document.querySelector('.individual-save-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      const invalidFields = form.querySelectorAll('.is-invalid');
      if (invalidFields.length > 0) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Used leaves exceed available balance. Please correct before saving.',
          toast: true,
          position: 'top-end',
          timer: 3000,
          showConfirmButton: false
        });
      }
    });
  }

  const now = new Date();
  // Set to last month by default
  const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
  const currentMonth = `${lastMonth.getFullYear()}-${String(lastMonth.getMonth() + 1).padStart(2, '0')}`;
  document.querySelectorAll('#payroll-table tbody tr').forEach(row => {
    ensureBaseDeduction(row);
    updateRowCalculations(row);
  });



  // Month filter event listener
  const salaryMonthInput = document.getElementById('salaryMonth');
  if (salaryMonthInput) {
    salaryMonthInput.addEventListener('change', function () {
      const selectedMonth = this.value;
      if (selectedMonth) {
        window.location.href = `<?= base_url(
          "/payroll/salary-details",
        ) ?>?month=${selectedMonth}`;
      }
    });
  }

  // Employee search functionality
  const employeeSearch = document.getElementById('employeeSearch');
  if (employeeSearch) {
    employeeSearch.addEventListener('input', function () {
      const searchTerm = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('#payroll-table tbody tr');

      rows.forEach(row => {
        const nameElement = row.querySelector('td:first-child');
        const name = nameElement ? nameElement.textContent.toLowerCase() : '';
        
        if (name.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  const leaveInputs = document.querySelectorAll('.leave-input');
  const halfDayInputs = document.querySelectorAll('.half_day-input');
  const paidLeaveInputs = document.querySelectorAll('.paid-leave-input');
  const sickLeaveInputs = document.querySelectorAll('.sick-leave-input');

  function ensureBaseDeduction(row) {
    if (row.dataset.baseDeductionInitialized) return;
    const leaveInput = row.querySelector('.leave-input');
    const halfInput = row.querySelector('.half_day-input');
    const paidLeaveInput = row.querySelector('.paid-leave-input');
    const sickLeaveInput = row.querySelector('.sick-leave-input');
    const perDay = parseFloat(row.dataset.perDay) || (parseFloat(leaveInput.dataset.salary) / parseInt(leaveInput.dataset.days));
    const initialDeduction = parseFloat(row.querySelector('.deduction-input').value) || 0;
    const initialPaidLeaves = parseFloat(paidLeaveInput.value) || 0;
    const initialSickLeaves = parseFloat(sickLeaveInput.value) || 0;
    let baseDeduction = parseFloat(row.dataset.baseDeduction) || 0;
    if (!baseDeduction) {
      baseDeduction = initialDeduction + ((initialPaidLeaves + initialSickLeaves) * perDay);
    }
    row.dataset.baseDeduction = baseDeduction.toFixed(2);
    row.dataset.initialLeaves = (parseFloat(leaveInput.value) || 0).toString();
    row.dataset.initialHalfDays = (parseFloat(halfInput.value) || 0).toString();
    row.dataset.baseDeductionInitialized = '1';
  }

  function updateRowCalculations(row) {
    const leaveInput = row.querySelector('.leave-input');
    const halfInput = row.querySelector('.half_day-input');
    const paidLeaveInput = row.querySelector('.paid-leave-input');
    const sickLeaveInput = row.querySelector('.sick-leave-input');

    const leaves = parseFloat(leaveInput.value) || 0;
    const halfDays = parseFloat(halfInput.value) || 0;
    const usedPaidLeaves = parseFloat(paidLeaveInput.value) || 0;
    const usedSickLeaves = parseFloat(sickLeaveInput.value) || 0;
    const salary = parseFloat(leaveInput.dataset.salary) || 0;
    const tax = parseFloat(leaveInput.dataset.tax) || 0;
    const daysInMonth = parseInt(leaveInput.dataset.days);
    const perDay = parseFloat(row.dataset.perDay) || (salary / daysInMonth);

    // Use backend-calculated base deduction when leaves/half-days are unchanged.
    ensureBaseDeduction(row);
    const initialLeaves = parseFloat(row.dataset.initialLeaves || '0') || 0;
    const initialHalfDays = parseFloat(row.dataset.initialHalfDays || '0') || 0;
    let baseDeduction = parseFloat(row.dataset.baseDeduction) || 0;
    const lateDeduction = parseFloat(row.dataset.lateDeduction) || 0;
    baseDeduction = (leaves * perDay) + (halfDays * (perDay / 2)) + lateDeduction;

    const paidLeaveCredit = (usedPaidLeaves + usedSickLeaves) * perDay;
    const salaryDeduction = Math.max(baseDeduction - paidLeaveCredit, 0);
    const overtimePay = parseFloat(row.dataset.overtimePay) || 0;
    const extraDayPay = parseFloat(row.querySelector('.extra-day-pay-input')?.value) || 0;
    const totalAdjustment = overtimePay + extraDayPay - salaryDeduction - tax;
    const netSalary = salary + totalAdjustment;

    // Update Remaining Balances and Validation
    const allocatedPaid = parseFloat(paidLeaveInput.dataset.allocated) || 0;
    const allocatedSick = parseFloat(sickLeaveInput.dataset.allocated) || 0;

    const remPaid = allocatedPaid - usedPaidLeaves;
    const remSick = allocatedSick - usedSickLeaves;

    const remPaidCell = row.querySelector('.rem-paid-leave');
    const remSickCell = row.querySelector('.rem-sick-leave');

    if (remPaidCell) remPaidCell.textContent = Math.max(0, remPaid);
    if (remSickCell) remSickCell.textContent = Math.max(0, remSick);

    const paidBalanceInvalid = remPaid < 0 || remPaid > allocatedPaid;
    const sickBalanceInvalid = remSick < 0 || remSick > allocatedSick;

    if (paidBalanceInvalid) {
      paidLeaveInput.classList.add('is-invalid');
    } else {
      paidLeaveInput.classList.remove('is-invalid');
    }

    if (sickBalanceInvalid) {
      sickLeaveInput.classList.add('is-invalid');
    } else {
      sickLeaveInput.classList.remove('is-invalid');
    }

    const deductionSpan = row.querySelector('.deduction-cell span');
    if (deductionSpan) deductionSpan.textContent = '₹' + salaryDeduction.toFixed(2);

    row.querySelector('.net-salary-cell').textContent = '₹' + netSalary.toFixed(2);
    row.querySelector('.deduction-input').value = salaryDeduction.toFixed(2);
    row.querySelector('.net-salary-input').value = netSalary.toFixed(2);
    const opInput = row.querySelector('.overtime-pay-input');
    if (opInput) opInput.value = overtimePay.toFixed(2);
    row.querySelector('.single-leave-input').value = leaves;
    row.querySelector('.single-halfday-input').value = halfDays;
    row.querySelector('.single-paid-leave-input').value = usedPaidLeaves;
    row.querySelector('.single-sick-leave-input').value = usedSickLeaves;
    row.querySelector('.single-deduction-input').value = salaryDeduction.toFixed(2);
    row.querySelector('.single-net-salary-input').value = netSalary.toFixed(2);
  }

  leaveInputs.forEach(input => {
    const row = input.closest('tr');
    // if (input.dataset.month !== currentMonth) input.disabled = true;
    input.addEventListener('input', () => updateRowCalculations(row));
  });

  halfDayInputs.forEach(input => {
    const row = input.closest('tr');
    // if (input.dataset.month !== currentMonth) input.disabled = true;
    input.addEventListener('input', () => updateRowCalculations(row));
  });

  paidLeaveInputs.forEach(input => {
    const row = input.closest('tr');
    // if (input.dataset.month !== currentMonth) input.disabled = true;
    input.addEventListener('input', () => updateRowCalculations(row));
  });

  sickLeaveInputs.forEach(input => {
    const row = input.closest('tr');
    input.addEventListener('input', () => updateRowCalculations(row));
  });

  let currentSavingRow = null;
  let currentSavingBtn = null;

  // Helper: perform the actual save API call
  function doSavePayroll(row, saveBtn, adjustmentAmount, adjustmentRemark) {
    const employeeId = saveBtn.dataset.id;
    const salary = row.querySelector('input[name="salary[]"]').value;
    const leaves = row.querySelector('.leave-input').value;
    const halfDay = row.querySelector('.half_day-input').value;
    const paidLeave = row.querySelector('.paid-leave-input').value || 0;
    const sickLeave = row.querySelector('.sick-leave-input').value || 0;
    const deduction = row.querySelector('.deduction-input').value;
    const netSalary = (parseFloat(row.querySelector('.net-salary-input').value) + parseFloat(adjustmentAmount || 0)).toFixed(2);
    const overtimePay = (row.querySelector('.overtime-pay-input') && row.querySelector('.overtime-pay-input').value) || 0;
    const extraDayPay = (row.querySelector('.extra-day-pay-input') && row.querySelector('.extra-day-pay-input').value) || 0;
    const totalOvertimeHours = (row.querySelector('.total-overtime-hours-input') && row.querySelector('.total-overtime-hours-input').value) || 0;
    const month = row.querySelector('.single-month').value;

    fetch("<?= base_url("/api/payroll/save") ?>", {
      method: "POST",
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': "<?= csrf_hash() ?>"
      },
      body: new URLSearchParams({
        "<?= csrf_token() ?>": "<?= csrf_hash() ?>",
        employee_id: employeeId,
        month: month,
        salary: salary,
        leaves: leaves,
        half_day: halfDay,
        paid_leave: paidLeave,
        sick_leave: sickLeave,
        deduction: deduction,
        net_salary: netSalary,
        overtime_pay: overtimePay,
        extra_day_pay: extraDayPay,
        total_overtime_hours: totalOvertimeHours,
        adjustment_amount: adjustmentAmount || 0,
        adjustment_remark: adjustmentRemark || ''
      })
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          // Close modal if open
          const modalElement = document.getElementById('remarkModal');
          const modalInstance = bootstrap.Modal.getInstance(modalElement);
          if (modalInstance) modalInstance.hide();

          const badge = document.createElement('span');
          badge.className = 'badge bg-success btn-sm rounded';
          badge.textContent = 'Saved';
          if (saveBtn.parentNode) {
            saveBtn.parentNode.replaceChild(badge, saveBtn);
          }

          // Update the UI net salary cell to reflect adjustment
          row.querySelector('.net-salary-cell').textContent = '₹' + netSalary;

          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data.message || 'Saved successfully!',
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Failed to save salary details.',
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
          });
        }
      })
      .catch(err => {
        console.error(err);
        Swal.fire({
          icon: 'error',
          title: 'System Error',
          text: 'An unexpected error occurred. Please refresh the page and try again.',
          toast: true,
          position: 'top-end',
          timer: 4000,
          showConfirmButton: false
        });
      });
  }

  // Save button: directly saves without opening modal
  document.querySelectorAll('.btn-save-single').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = this.closest('tr');

      // Validation Check
      if (row.querySelector('.is-invalid')) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Used leaves exceed the available balance for this employee.',
          toast: true,
          position: 'top-end',
          timer: 3000,
          showConfirmButton: false
        });
        return;
      }

      doSavePayroll(row, this, 0, '');
    });
  });

  // Remark button: opens the modal for adjustment & remark
  document.querySelectorAll('.btn-open-remark').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = this.closest('tr');
      const employeeId = this.dataset.id;
      const employeeName = row.querySelector('.capitalize-text')?.textContent || 'Employee';
      const monthYear = row.querySelector('.single-month').value;

      currentSavingRow = row;
      // Point to the Save button (sibling) for saving after modal confirm
      currentSavingBtn = row.querySelector('.btn-save-single');

      // Populate Modal
      document.getElementById('modal_employee_id').value = employeeId;
      document.getElementById('modal_employee_name').textContent = employeeName;
      document.getElementById('modal_month_year').textContent = monthYear;
      document.getElementById('adjustment_amount').value = '';
      document.getElementById('adjustment_remark').value = '';

      const remarkModal = new bootstrap.Modal(document.getElementById('remarkModal'));
      remarkModal.show();
    });
  });

  // Modal "Save Salary" button: saves with adjustment & remark from modal
  document.getElementById('btnConfirmSaveSalary').addEventListener('click', function () {
    if (!currentSavingRow || !currentSavingBtn) return;

    const adjustmentAmount = parseFloat(document.getElementById('adjustment_amount').value) || 0;
    const adjustmentRemark = document.getElementById('adjustment_remark').value;

    doSavePayroll(currentSavingRow, currentSavingBtn, adjustmentAmount, adjustmentRemark);
  });

  // Deduction info modal – event delegation + touchend for mobile
  function openSalaryDetailsDeductionModal(btn) {
    const userId = btn.dataset.userId;
    const month = btn.dataset.month;
    const name = btn.dataset.name || 'Employee';
    const modal = document.getElementById('deductionBreakdownModal');
    const loading = document.getElementById('deductionBreakdownLoading');
    const content = document.getElementById('deductionBreakdownContent');
    if (!modal || !userId || !month) return;
    document.getElementById('deductionBreakdownModalLabel').innerHTML = '<i class="mdi mdi-information-outline me-1"></i> Why was this amount deducted? – ' + name;
    loading.style.display = 'block';
    content.style.display = 'none';
    content.innerHTML = '';
    if (modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    fetch("<?= base_url('api/payroll/get-deduction-breakdown') ?>", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': "<?= csrf_hash() ?>"
      },
      body: new URLSearchParams({ user_id: userId, month: month })
    })
      .then(r => r.json())
      .then(res => {
        loading.style.display = 'none';
        if (res.status !== 'success' || !res.data) {
          content.innerHTML = '<p class="text-danger">Could not load deduction details.</p>';
          content.style.display = 'block';
          return;
        }
        const d = res.data;
        let html = '<p class="text-muted small mb-3">' + d.employee_name + ' – ' + d.month_label + '</p>';
        if (d.leaves && (d.leaves.count > 0 || (d.leaves.dates && d.leaves.dates.length))) {
          html += '<div class="breakdown-section"><strong class="text-danger">Leaves (' + (d.leaves.count || 0) + ' day(s))</strong>';
          if (d.leaves.dates && d.leaves.dates.length) {
            html += '<ul class="breakdown-list list-unstyled small mb-1">';
            d.leaves.dates.forEach(l => { html += '<li>' + (l.label || l.date) + (l.reason ? ' – ' + l.reason : '') + '</li>'; });
            html += '</ul>';
          }
          html += '<span class="text-danger">Deduction: ₹' + (d.leaves.deduction_amount || 0).toFixed(2) + '</span></div>';
        }
        if (d.absent && d.absent.dates && d.absent.dates.length) {
          html += '<div class="breakdown-section"><strong class="text-warning">Absent (' + d.absent.dates.length + ' day(s))</strong><ul class="breakdown-list list-unstyled small">';
          d.absent.dates.forEach(a => { html += '<li>' + (a.label || a.date) + '</li>'; });
          html += '</ul></div>';
        }
        if (d.half_day && (d.half_day.count > 0 || (d.half_day.dates && d.half_day.dates.length))) {
          html += '<div class="breakdown-section"><strong>Half-day (' + (d.half_day.count || 0) + ')</strong>';
          if (d.half_day.dates && d.half_day.dates.length) {
            html += '<ul class="breakdown-list list-unstyled small mb-1">';
            d.half_day.dates.forEach(h => {
              const baseLabel = (h.label || h.date);
              const worked = h.worked_text ? (' – Worked: ' + h.worked_text) : '';
              const missing = h.missing_text ? (' – Deduct: ' + h.missing_text) : '';
              html += '<li>' + baseLabel + worked + missing + '</li>';
            });
            html += '</ul>';
          }
          html += '<span class="text-danger">Deduction: ₹' + (d.half_day.deduction_amount || 0).toFixed(2) + '</span></div>';
        }
        if (d.late && d.late.list && d.late.list.length) {
          html += '<div class="breakdown-section"><strong>Late arrival</strong><ul class="breakdown-list list-unstyled small">';
          d.late.list.forEach(l => { html += '<li>' + (l.label || l.date) + ' – ' + (l.late_text || l.late_minutes + ' min') + '</li>'; });
          html += '</ul><span class="text-danger">Deduction: ₹' + (d.late.deduction_amount || 0).toFixed(2) + '</span></div>';
        }
        if (d.overtime && d.overtime.list && d.overtime.list.length) {
          html += '<div class="breakdown-section"><strong class="text-success">Overtime</strong><ul class="breakdown-list list-unstyled small">';
          d.overtime.list.forEach(o => { html += '<li>' + (o.label || o.date) + ' – ' + (o.overtime_text || o.overtime_hours + 'h') + '</li>'; });
          html += '</ul><span class="text-success">Added to salary: ₹' + (d.overtime.pay_amount || 0).toFixed(2) + '</span></div>';
        }
        if (d.summary) {
          html += '<hr><div class="summary-row"><span>Total deduction (leaves + half-day + late):</span> <span class="text-danger">₹' + (d.summary.total_deduction || 0).toFixed(2) + '</span></div>';
          if (d.summary.overtime_added > 0) html += '<div class="summary-row"><span>Overtime added:</span> <span class="text-success">₹' + d.summary.overtime_added.toFixed(2) + '</span></div>';
        }
        if (!d.leaves?.count && !d.absent?.dates?.length && !d.half_day?.count && !d.late?.list?.length && !d.overtime?.list?.length) {
          html += '<p class="text-muted">No leaves, absent, late, or overtime in this month.</p>';
        }
        content.innerHTML = html;
        content.style.display = 'block';
      })
      .catch(() => {
        loading.style.display = 'none';
        content.innerHTML = '<p class="text-danger">Failed to load details.</p>';
        content.style.display = 'block';
      });
  }

  var salaryDetailsDeductionLastTouch = 0;
  document.addEventListener('touchend', function (e) {
    const btn = e.target.closest('.btn-deduction-info');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    salaryDetailsDeductionLastTouch = Date.now();
    openSalaryDetailsDeductionModal(btn);
  }, { passive: false });
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-deduction-info');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    if (Date.now() - salaryDetailsDeductionLastTouch < 400) return;
    openSalaryDetailsDeductionModal(btn);
  });

  // Extra Day info modal – event delegation
  function openExtraDayModal(btn) {
    const userId = btn.dataset.userId;
    const month = btn.dataset.month;
    const name = btn.dataset.name || 'Employee';
    
    const modalEl = document.getElementById('extraDayModal');
    const loading = document.getElementById('extraDayLoading');
    const content = document.getElementById('extraDayContent');
    
    if (!modalEl || !userId || !month) return;
    
    document.getElementById('extraDayModalLabel').innerHTML = '<i class="mdi mdi-calendar-plus me-1"></i> Extra Day Details – ' + name;
    loading.style.display = 'block';
    content.style.display = 'none';
    content.innerHTML = '';
    
    if (modalEl.parentNode !== document.body) {
      document.body.appendChild(modalEl);
    }
    
    const modalInstance = new bootstrap.Modal(modalEl);
    modalInstance.show();
    
    fetch("<?= base_url('api/payroll/get-extra-day-details') ?>", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': "<?= csrf_hash() ?>"
      },
      body: new URLSearchParams({ user_id: userId, month: month })
    })
    .then(r => r.json())
    .then(res => {
      loading.style.display = 'none';
      if (res.status !== 'success' || !res.data) {
        content.innerHTML = '<p class="text-danger">Could not load extra day details.</p>';
        content.style.display = 'block';
        return;
      }
      
      const d = res.data;
      let html = '<p class="text-muted small mb-3">' + d.employee_name + ' – ' + d.month_label + '</p>';
      
      html += '<div class="alert alert-info py-2 mb-3">';
      html += '<strong>Total Extra Days: </strong>' + (d.extra_days || 0) + '<br>';
      html += '<strong>Extra Day Pay: </strong>₹<span id="modal-total-extra-pay">' + (d.extra_day_pay || 0).toFixed(2) + '</span>';
      html += '</div>';
      
      if (d.details && d.details.length > 0) {
        html += '<div class="table-responsive"><table class="table table-bordered table-sm">';
        html += '<thead class="table-light"><tr><th>Date</th><th>Day</th><th>Check-In</th><th>Check-Out</th><th>Worked Hrs</th><th>Type</th><th>Amount</th><th>Action</th></tr></thead><tbody>';
        
        d.details.forEach(item => {
          const credit     = item.extra_credit || 1.0;
          const isHalf     = item.is_half_extra || false;
          const workedHrs  = (item.worked_hours || 0);
          const rowAmount  = (d.per_day || 0) * credit;
          const typeLabel  = isHalf
            ? '<span class="badge" style="background:#f0a500;color:#fff;">Half Extra (0.5)</span>'
            : '<span class="badge" style="background:#28a745;color:#fff;">Full Extra (1.0)</span>';
          const workedHrsLabel = workedHrs > 0 ? workedHrs.toFixed(2) + ' hr' : '--';

          html += '<tr>';
          html += '<td>' + item.formatted_date + '</td>';
          html += '<td>' + item.day_name + '</td>';
          html += '<td>' + (item.check_in_time || '--') + '</td>';
          html += '<td>' + (item.check_out_time || '--') + '</td>';
          html += '<td>' + workedHrsLabel + '</td>';
          html += '<td>' + typeLabel + '</td>';
          html += '<td><input type="number" class="form-control form-control-sm modal-extra-day-amount" value="' + rowAmount.toFixed(2) + '" step="0.01" style="width: 90px;"></td>';
          html += '<td><button type="button" class="btn btn-sm btn-success btn-save-modal-extra-day" data-userid="'+userId+'">Save</button></td>';
          html += '</tr>';
        });
        
        html += '</tbody></table></div>';
      } else {
        html += '<p class="text-muted">No Saturday (Week Off) or Sunday attendance records found for this month.</p>';
      }
      
      content.innerHTML = html;
      content.style.display = 'block';
    })
    .catch(() => {
      loading.style.display = 'none';
      content.innerHTML = '<p class="text-danger">Failed to load details.</p>';
      content.style.display = 'block';
    });
  }

  var extraDayLastTouch = 0;
  document.addEventListener('touchend', function (e) {
    const btn = e.target.closest('.btn-extra-day-info');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    extraDayLastTouch = Date.now();
    openExtraDayModal(btn);
  }, { passive: false });
  
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-extra-day-info');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    if (Date.now() - extraDayLastTouch < 400) return;
    openExtraDayModal(btn);
  });

  // Handle Save inside Extra Day Modal
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-save-modal-extra-day')) {
      e.preventDefault();
      const btn = e.target;
      const userId = btn.dataset.userid;
      
      // Calculate new total from all inputs in the modal
      const modal = document.getElementById('extraDayModal');
      const inputs = modal.querySelectorAll('.modal-extra-day-amount');
      let newTotal = 0;
      inputs.forEach(input => {
        newTotal += parseFloat(input.value) || 0;
      });
      
      // Update modal display
      document.getElementById('modal-total-extra-pay').textContent = newTotal.toFixed(2);
      
      // Update main table row if found
      const mainTableBtn = document.querySelector('.btn-extra-day-info[data-user-id="'+userId+'"]');
      if (mainTableBtn) {
        const row = mainTableBtn.closest('tr');
        const payDisplay = row.querySelector('.extra-day-pay-display'); // the text showing amount
        
        if (payDisplay) {
          payDisplay.textContent = '₹' + newTotal.toFixed(2);
        }
        
        // Add to net salary instantly
        const input = row.querySelector('.extra-day-pay-input');
        if (input) {
          input.value = newTotal;
          updateRowCalculations(row);
        }
      }
      
      // Visual feedback
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="mdi mdi-check"></i> Saved';
      setTimeout(() => {
        btn.innerHTML = originalText;
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
          modalInstance.hide();
        }
      }, 600); // Close after 600ms
    }
  });

</script>

<?= $this->endSection() ?>
