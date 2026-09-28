<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-office-building text-primary me-2"></i>
              Assign Branch Manager: <strong><?= htmlspecialchars($branch['name']) ?></strong> (<?= htmlspecialchars($branch['code']) ?>)
            </h4>
            <p class="text-muted mb-0">Select an employee or manager to assign as the Branch Admin for this branch.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= base_url('/branch-managers') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-account-group me-1"></i> All Managers
            </a>
            <a href="<?= base_url('/branches') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
          </div>
        </div>

        <div id="alertBox"></div>

        <!-- Current Branch Manager Card -->
        <div class="p-3 mb-4 rounded border bg-light">
          <h6 class="text-muted text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">Current Branch Manager</h6>
          <?php if (!empty($currentManager)): ?>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-weight: 700; font-size: 18px;">
                  <?= strtoupper(substr($currentManager['firstname'] ?? 'M', 0, 1)) ?>
                </div>
                <div>
                  <h5 class="mb-0 fw-bold"><?= htmlspecialchars(($currentManager['firstname'] ?? '') . ' ' . ($currentManager['lastname'] ?? '')) ?></h5>
                  <div class="small text-muted">
                    <span class="badge bg-primary text-white me-2">Branch Admin</span>
                    <i class="mdi mdi-email-outline me-1"></i><?= htmlspecialchars($currentManager['email'] ?? '—') ?>
                    <?php if (!empty($currentManager['contact_number'])): ?>
                      <span class="ms-2"><i class="mdi mdi-phone me-1"></i><?= htmlspecialchars($currentManager['contact_number']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <span class="badge bg-success p-2"><i class="mdi mdi-check-circle me-1"></i>Active Manager</span>
            </div>
          <?php else: ?>
            <div class="text-muted small py-2">
              <i class="mdi mdi-alert-circle-outline text-warning me-1 fs-5"></i>
              No Branch Manager is currently assigned to this branch. Choose a user below to assign.
            </div>
          <?php endif; ?>
        </div>

        <!-- Search Candidates -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-0" style="font-size: 14px;">Select User to Assign as Branch Manager</h5>
          <div style="width: 250px;">
            <input type="text" id="candidateSearch" class="form-control form-control-sm" placeholder="Search staff by name/email...">
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover" id="candidatesTable">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Staff Name</th>
                <th>Email</th>
                <th>Current Role</th>
                <th>Current Branch</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody id="candidatesTableBody">
              <?php if (empty($candidates)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No eligible staff members found.</td></tr>
              <?php else: ?>
                <?php foreach ($candidates as $i => $u): ?>
                  <?php 
                    $isThisManager = !empty($currentManager) && (int)$currentManager['id'] === (int)$u['id']; 
                    $roleLabel = ucwords(str_replace('_', ' ', $u['role']));
                    $roleBadge = 'bg-light text-dark border';
                    if ($u['role'] === 'branch_admin') $roleBadge = 'bg-primary text-white';
                    elseif ($u['role'] === 'department_manager') $roleBadge = 'bg-warning text-dark';
                  ?>
                  <tr class="candidate-row" data-name="<?= strtolower(htmlspecialchars(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? ''))) ?>" data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>">
                    <td><?= $i + 1 ?></td>
                    <td>
                      <strong><?= htmlspecialchars(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? '')) ?></strong>
                      <?php if (!empty($u['employee_id'])): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($u['employee_id']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $roleBadge ?>" style="font-size: 11px;"><?= $roleLabel ?></span></td>
                    <td><?= htmlspecialchars($u['branch_name'] ?? 'Unassigned') ?></td>
                    <td class="text-end">
                      <?php if ($isThisManager): ?>
                        <span class="text-success fw-bold small"><i class="mdi mdi-check-circle me-1"></i>Current Manager</span>
                      <?php else: ?>
                        <button class="btn btn-sm hr-btnbg" onclick="assignBranchManager(<?= (int)$u['id'] ?>, '<?= addslashes(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? '')) ?>')">
                          <i class="mdi mdi-account-arrow-right me-1"></i> Assign as Manager
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
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
const branchId = <?= (int)$branch['id'] ?>;
const branchName = <?= json_encode($branch['name']) ?>;

function assignBranchManager(userId, userName) {
  if (!confirm(`Are you sure you want to assign "${userName}" as Branch Manager for "${branchName}"?`)) return;

  fetch('/api/branches/assign-manager', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
    credentials: 'same-origin',
    body: JSON.stringify({ user_id: userId, branch_id: branchId })
  })
  .then(r => r.json())
  .then(d => {
    showAlert(d.message || 'Updated.', d.status === 'success' ? 'success' : 'danger');
    if (d.status === 'success') {
      setTimeout(() => location.reload(), 1200);
    }
  })
  .catch(() => {
    showAlert('Failed to update branch manager.', 'danger');
  });
}

// Client-side search filter
document.getElementById('candidateSearch')?.addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase().trim();
  document.querySelectorAll('#candidatesTableBody tr.candidate-row').forEach(row => {
    const name = row.getAttribute('data-name') || '';
    const email = row.getAttribute('data-email') || '';
    row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
  });
});

function showAlert(msg, type) {
  document.getElementById('alertBox').innerHTML =
    `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${msg}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}
</script>
<?php $this->endSection(); ?>
