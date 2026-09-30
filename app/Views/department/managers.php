<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-account-multiple-outline text-primary me-2"></i>Department Managers Directory
            </h4>
            <p class="text-muted mb-0">Overview of all company departments across branches and their assigned Department Managers.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= base_url('/departmentview') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-briefcase me-1"></i> All Departments
            </a>
            <a href="<?= base_url('/branch-managers') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-office-building me-1"></i> Branch Managers
            </a>
          </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Total Departments</div>
              <h3 class="fw-bold mb-0 text-dark" id="statTotalDept">—</h3>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Assigned Managers</div>
              <h3 class="fw-bold mb-0 text-success" id="statAssignedDeptMgr">—</h3>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Unassigned Depts</div>
              <h3 class="fw-bold mb-0 text-warning" id="statUnassignedDeptMgr">—</h3>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Total Department Staff</div>
              <h3 class="fw-bold mb-0 text-primary" id="statTotalDeptStaff">—</h3>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
              <input type="text" id="deptManagerSearch" class="form-control" placeholder="Search department, manager, branch...">
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-hover" id="deptManagersTable">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Department Name</th>
                <th>Branch</th>
                <th>Department Manager</th>
                <th class="text-center">Staff Count</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody id="deptManagersTableBody">
              <tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
let allDeptManagers = [];

function loadDepartmentManagers() {
  fetch('/api/department-managers', {
    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success' && res.data) {
      allDeptManagers = res.data;
      updateStats(allDeptManagers);
      renderTable(allDeptManagers);
    } else {
      document.getElementById('deptManagersTableBody').innerHTML =
        '<tr><td colspan="6" class="text-center py-4 text-muted">No department manager data found.</td></tr>';
    }
  })
  .catch(() => {
    document.getElementById('deptManagersTableBody').innerHTML =
      '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load department managers.</td></tr>';
  });
}

function updateStats(data) {
  const total = data.length;
  let assigned = 0;
  let unassigned = 0;
  let staff = 0;

  data.forEach(d => {
    if (d.manager_id) assigned++;
    else unassigned++;
    staff += parseInt(d.staff_count || 0, 10);
  });

  document.getElementById('statTotalDept').textContent = total;
  document.getElementById('statAssignedDeptMgr').textContent = assigned;
  document.getElementById('statUnassignedDeptMgr').textContent = unassigned;
  document.getElementById('statTotalDeptStaff').textContent = staff;
}

function renderTable(data) {
  const tbody = document.getElementById('deptManagersTableBody');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No matching department managers.</td></tr>';
    return;
  }

  tbody.innerHTML = data.map((d, i) => {
    const hasManager = !!d.manager_id;
    const initial = hasManager ? (d.manager_name || '?').charAt(0).toUpperCase() : '?';

    const managerCell = hasManager
      ? `<div class="d-flex align-items-center gap-2">
           <div class="avatar bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: 600; font-size: 13px;">
             ${initial}
           </div>
           <div>
             <div class="fw-bold">${escHtml(d.manager_name)}</div>
             <div class="small text-muted">${escHtml(d.manager_email || '')} ${d.manager_phone ? '• ' + escHtml(d.manager_phone) : ''}</div>
           </div>
         </div>`
      : `<span class="badge bg-warning text-dark"><i class="mdi mdi-alert-circle-outline me-1"></i>No Manager Assigned</span>`;

    const branchBadge = d.branch_name
      ? `<span class="badge bg-light text-dark border"><i class="mdi mdi-office-building me-1"></i>${escHtml(d.branch_name)}</span>`
      : `<span class="text-muted small">All Branches / Unassigned</span>`;

    return `
      <tr>
        <td>${i + 1}</td>
        <td><strong>${escHtml(d.department_name)}</strong></td>
        <td>${branchBadge}</td>
        <td>${managerCell}</td>
        <td class="text-center"><span class="badge bg-primary">${d.staff_count || 0}</span></td>
        <td class="text-end">
          <a href="/department?id=${d.id}" class="btn btn-sm hr-btnbg" title="Assign or change department manager">
            <i class="mdi mdi-account-cog me-1"></i> ${hasManager ? 'Change Manager' : 'Assign Manager'}
          </a>
        </td>
      </tr>`;
  }).join('');
}

document.getElementById('deptManagerSearch')?.addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase().trim();
  const filtered = allDeptManagers.filter(d => {
    const dName = (d.department_name || '').toLowerCase();
    const bName = (d.branch_name || '').toLowerCase();
    const mName = (d.manager_name || '').toLowerCase();
    const mMail = (d.manager_email || '').toLowerCase();
    return dName.includes(query) || bName.includes(query) || mName.includes(query) || mMail.includes(query);
  });
  renderTable(filtered);
});

function escHtml(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadDepartmentManagers();
</script>
<?php $this->endSection(); ?>
