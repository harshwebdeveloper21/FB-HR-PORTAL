<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-account-tie text-primary me-2"></i>Branch Managers Directory
            </h4>
            <p class="text-muted mb-0">Overview of all company branches and their appointed Branch Admins.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= base_url('/branches') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-office-building me-1"></i> Manage Branches
            </a>
            <a href="<?= base_url('/department-managers') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-account-multiple me-1"></i> Department Managers
            </a>
          </div>
        </div>

        <!-- Quick Summary Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Total Branches</div>
              <h3 class="fw-bold mb-0 text-dark" id="statTotalBranches">—</h3>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Assigned Managers</div>
              <h3 class="fw-bold mb-0 text-success" id="statAssignedManagers">—</h3>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Unassigned Branches</div>
              <h3 class="fw-bold mb-0 text-warning" id="statUnassignedBranches">—</h3>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-3 border rounded bg-light text-center">
              <div class="text-muted small mb-1">Total Branch Staff</div>
              <h3 class="fw-bold mb-0 text-primary" id="statTotalStaff">—</h3>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
              <input type="text" id="managerSearch" class="form-control" placeholder="Search branch, manager, city...">
            </div>
          </div>
        </div>

        <!-- Branch Managers Table -->
        <div class="table-responsive">
          <table class="table table-hover" id="branchManagersTable">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Branch</th>
                <th>Location</th>
                <th>Branch Manager (Branch Admin)</th>
                <th class="text-center">Departments</th>
                <th class="text-center">Staff Count</th>
                <th>Branch Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody id="branchManagersTableBody">
              <tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
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
let allBranchManagers = [];

function loadBranchManagers() {
  fetch('/api/branch-managers', {
    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success' && res.data) {
      allBranchManagers = res.data;
      updateStats(allBranchManagers);
      renderTable(allBranchManagers);
    } else {
      document.getElementById('branchManagersTableBody').innerHTML =
        '<tr><td colspan="8" class="text-center py-4 text-muted">No branch manager data found.</td></tr>';
    }
  })
  .catch(() => {
    document.getElementById('branchManagersTableBody').innerHTML =
      '<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load branch managers.</td></tr>';
  });
}

function updateStats(data) {
  const total = data.length;
  let assigned = 0;
  let unassigned = 0;
  let staff = 0;

  data.forEach(b => {
    if (b.manager_id) assigned++;
    else unassigned++;
    staff += parseInt(b.staff_count || 0, 10);
  });

  document.getElementById('statTotalBranches').textContent = total;
  document.getElementById('statAssignedManagers').textContent = assigned;
  document.getElementById('statUnassignedBranches').textContent = unassigned;
  document.getElementById('statTotalStaff').textContent = staff;
}

function renderTable(data) {
  const tbody = document.getElementById('branchManagersTableBody');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No matching branch managers.</td></tr>';
    return;
  }

  tbody.innerHTML = data.map((b, i) => {
    const hasManager = !!b.manager_id;
    const managerName = hasManager ? `${b.firstname || ''} ${b.lastname || ''}`.trim() || b.manager_email : null;
    const initial = managerName ? managerName.charAt(0).toUpperCase() : '?';

    const managerCell = hasManager
      ? `<div class="d-flex align-items-center gap-2">
           <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-weight: 600; font-size: 13px;">
             ${initial}
           </div>
           <div>
             <div class="fw-bold">${escHtml(managerName)}</div>
             <div class="small text-muted">${escHtml(b.manager_email || '')} ${b.contact_number ? '• ' + escHtml(b.contact_number) : ''}</div>
           </div>
         </div>`
      : `<span class="badge bg-warning text-dark"><i class="mdi mdi-alert-circle-outline me-1"></i>No Manager Assigned</span>`;

    return `
      <tr>
        <td>${i + 1}</td>
        <td>
          <strong>${escHtml(b.branch_name)}</strong>
          <br><span class="badge bg-secondary" style="font-size: 10px;">${escHtml(b.branch_code)}</span>
        </td>
        <td>${escHtml(b.branch_city || '—')}</td>
        <td>${managerCell}</td>
        <td class="text-center"><span class="badge bg-info text-dark">${b.department_count || 0}</span></td>
        <td class="text-center"><span class="badge bg-primary">${b.staff_count || 0}</span></td>
        <td>
          ${b.branch_status === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>'}
        </td>
        <td class="text-end">
          <a href="/branches/assign-manager/${b.branch_id}" class="btn btn-sm hr-btnbg" title="Assign or change branch manager">
            <i class="mdi mdi-account-cog me-1"></i> ${hasManager ? 'Change Manager' : 'Assign Manager'}
          </a>
        </td>
      </tr>`;
  }).join('');
}

document.getElementById('managerSearch')?.addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase().trim();
  const filtered = allBranchManagers.filter(b => {
    const bName = (b.branch_name || '').toLowerCase();
    const bCode = (b.branch_code || '').toLowerCase();
    const city  = (b.branch_city || '').toLowerCase();
    const mName = `${b.firstname || ''} ${b.lastname || ''}`.toLowerCase();
    const mMail = (b.manager_email || '').toLowerCase();
    return bName.includes(query) || bCode.includes(query) || city.includes(query) || mName.includes(query) || mMail.includes(query);
  });
  renderTable(filtered);
});

function escHtml(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadBranchManagers();
</script>
<?php $this->endSection(); ?>
