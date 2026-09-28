<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-cog text-primary me-2"></i>Branch Rules
              <?php if (isset($branch)): ?>
                — <strong><?= htmlspecialchars($branch['name']) ?></strong>
              <?php endif; ?>
            </h4>
            <p class="text-muted mb-0">Configure attendance, payroll, and leave rules for this branch.</p>
          </div>
          <a href="<?= base_url('/branches') ?>" class="btn hr-btnbg">
            <i class="mdi mdi-arrow-left me-1"></i> Back
          </a>
        </div>

        <?php if (isset($allBranches) && count($allBranches) > 1): ?>
        <div class="mb-4">
          <label class="form-label fw-semibold">Select Branch</label>
          <select id="branchSelector" class="form-select w-auto" onchange="location.href='/branch-rules/edit/'+this.value">
            <?php foreach ($allBranches as $b): ?>
              <option value="<?= $b['id'] ?>" <?= ($branch['id'] ?? 0) == $b['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['name']) ?> (<?= $b['code'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <div id="alertBox"></div>

        <form id="branchRulesForm">
          <input type="hidden" id="branchId" value="<?= $branch['id'] ?? '' ?>">

          <!-- Attendance Times -->
          <div class="card mb-3 border-0 bg-light">
            <div class="card-body">
              <h6 class="fw-semibold mb-3"><i class="mdi mdi-clock-outline me-2"></i>Attendance Times</h6>
              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">Start Time</label>
                  <input type="time" id="start_time" class="form-control"
                         value="<?= htmlspecialchars(substr($rules['start_time'] ?? '09:00:00', 0, 5)) ?>">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">End Time</label>
                  <input type="time" id="end_time" class="form-control"
                         value="<?= htmlspecialchars(substr($rules['end_time'] ?? '18:00:00', 0, 5)) ?>">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Lunch Break</label>
                  <input type="time" id="lunch_break" class="form-control"
                         value="<?= htmlspecialchars(substr($rules['lunch_break'] ?? '01:00:00', 0, 5)) ?>">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Grace Period (mins)</label>
                  <input type="number" id="grace_period" class="form-control" min="0" max="60"
                         value="<?= (int)($rules['grace_minutes'] ?? $rules['grace_period'] ?? 10) ?>">
                </div>
              </div>
            </div>
          </div>

          <!-- Working Hours -->
          <div class="card mb-3 border-0 bg-light">
            <div class="card-body">
              <h6 class="fw-semibold mb-3"><i class="mdi mdi-briefcase-clock-outline me-2"></i>Working Hours</h6>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Full Day Hours</label>
                  <input type="number" id="working_hours_per_day" class="form-control" min="1" max="24" step="0.5"
                         value="<?= (float)($rules['working_hours_per_day'] ?? 8) ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Half Day Hours</label>
                  <input type="number" id="half_day_hours" class="form-control" min="1" max="12" step="0.5"
                         value="<?= (float)($rules['half_day_hours'] ?? 4) ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Payroll Type</label>
                  <select id="payroll_type" class="form-select">
                    <option value="monthly"  <?= ($rules['payroll_type'] ?? 'monthly') === 'monthly'  ? 'selected' : '' ?>>Monthly</option>
                    <option value="daily"    <?= ($rules['payroll_type'] ?? '') === 'daily'    ? 'selected' : '' ?>>Daily</option>
                    <option value="hourly"   <?= ($rules['payroll_type'] ?? '') === 'hourly'   ? 'selected' : '' ?>>Hourly</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Weekly Off -->
          <div class="card mb-3 border-0 bg-light">
            <div class="card-body">
              <h6 class="fw-semibold mb-3"><i class="mdi mdi-calendar-week me-2"></i>Weekly Off</h6>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <div class="d-flex align-items-center">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" id="sunday_off" <?= ($rules['sunday_off'] ?? 1) ? 'checked' : '' ?>>
                    </div>
                    <label class="mb-0 ms-2 cursor-pointer" for="sunday_off">Sunday Off</label>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <div class="d-flex align-items-center">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" id="saturday_off_enabled" <?= ($rules['saturday_off_enabled'] ?? 0) ? 'checked' : '' ?>>
                    </div>
                    <label class="mb-0 ms-2 cursor-pointer" for="saturday_off_enabled">Saturday Off Enabled</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Overtime -->
          <div class="card mb-3 border-0 bg-light">
            <div class="card-body">
              <h6 class="fw-semibold mb-3"><i class="mdi mdi-timer-sand me-2"></i>Overtime</h6>
              <div class="row align-items-center">
                <div class="col-md-4 mb-3">
                  <div class="d-flex align-items-center">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" id="enable_overtime" <?= ($rules['enable_overtime'] ?? 0) ? 'checked' : '' ?>>
                    </div>
                    <label class="mb-0 ms-2 cursor-pointer" for="enable_overtime">Enable Overtime</label>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Multiplier</label>
                  <input type="number" id="overtime_multiplier" class="form-control" step="0.1" min="1"
                         value="<?= (float)($rules['overtime_multiplier'] ?? 1.5) ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Min Overtime (mins)</label>
                  <input type="number" id="min_overtime_count_in_minutes" class="form-control" min="0"
                         value="<?= (int)($rules['min_overtime_count_in_minutes'] ?? 30) ?>">
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn hr-btnbg" id="saveBtn">
              <i class="mdi mdi-content-save me-1"></i>Save Rules
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
document.getElementById('branchRulesForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('saveBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

  const payload = {
    branch_id:                    document.getElementById('branchId').value,
    start_time:                   document.getElementById('start_time').value + ':00',
    end_time:                     document.getElementById('end_time').value + ':00',
    lunch_break:                  document.getElementById('lunch_break').value + ':00',
    grace_period:                 parseInt(document.getElementById('grace_period').value),
    grace_minutes:                parseInt(document.getElementById('grace_period').value),
    working_hours_per_day:        parseFloat(document.getElementById('working_hours_per_day').value),
    half_day_hours:               parseFloat(document.getElementById('half_day_hours').value),
    payroll_type:                 document.getElementById('payroll_type').value,
    sunday_off:                   document.getElementById('sunday_off').checked,
    saturday_off_enabled:         document.getElementById('saturday_off_enabled').checked,
    enable_overtime:              document.getElementById('enable_overtime').checked,
    overtime_multiplier:          parseFloat(document.getElementById('overtime_multiplier').value),
    min_overtime_count_in_minutes: parseInt(document.getElementById('min_overtime_count_in_minutes').value),
  };

  try {
    const res  = await fetch('/api/branch-rules/store', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    document.getElementById('alertBox').innerHTML =
      `<div class="alert alert-${data.status === 'success' ? 'success' : 'danger'} alert-dismissible fade show">
        ${data.message || 'Saved.'}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
  } catch {
    document.getElementById('alertBox').innerHTML =
      '<div class="alert alert-danger">Network error. Please try again.</div>';
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="mdi mdi-content-save me-1"></i>Save Rules';
});
</script>
<?php $this->endSection(); ?>
