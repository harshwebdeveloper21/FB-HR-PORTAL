<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-account-multiple-plus text-primary me-2"></i>
              Assign HR to Branch: <strong><?= htmlspecialchars($branch['name']) ?></strong>
            </h4>
            <p class="text-muted mb-0">Toggle which HR users are assigned to this branch.</p>
          </div>
          <a href="<?= base_url('/branches') ?>" class="btn hr-btnbg">
            <i class="mdi mdi-arrow-left me-1"></i> Back
          </a>
        </div>

        <div id="alertBox"></div>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>HR Name</th>
                <th>Email</th>
                <th>Current Branch</th>
                <th>Can Transfer Staff</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($hrUsers)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No HR users found.</td></tr>
              <?php else: ?>
                <?php foreach ($hrUsers as $i => $hr): ?>
                  <?php $isAssigned = (int)$hr['branch_id'] === (int)$branch['id']; ?>
                  <tr id="hr-row-<?= $hr['id'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars(($hr['firstname'] ?? '') . ' ' . ($hr['lastname'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($hr['email']) ?></td>
                    <td>
                      <?php if ($isAssigned): ?>
                        <span class="badge bg-success"><i class="mdi mdi-check me-1"></i>This Branch</span>
                      <?php elseif (!empty($hr['branch_id'])): ?>
                        <span class="badge bg-secondary">Other Branch (ID: <?= $hr['branch_id'] ?>)</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark">Unassigned</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <button class="btn btn-sm <?= $hr['can_transfer_staff'] ? 'btn-success' : 'btn-outline-secondary' ?>"
                              onclick="toggleTransfer(<?= $hr['id'] ?>, this)"
                              title="<?= $hr['can_transfer_staff'] ? 'Revoke transfer permission' : 'Grant transfer permission' ?>">
                        <?= $hr['can_transfer_staff'] ? '<i class="mdi mdi-check-circle"></i> Allowed' : '<i class="mdi mdi-close-circle"></i> Not Allowed' ?>
                      </button>
                    </td>
                    <td>
                      <?php if (!$isAssigned): ?>
                        <button class="btn btn-sm hr-btnbg"
                                onclick="assignHr(<?= $hr['id'] ?>, <?= $branch['id'] ?>)">
                          <i class="mdi mdi-account-arrow-right me-1"></i>Assign Here
                        </button>
                      <?php else: ?>
                        <span class="text-success"><i class="mdi mdi-check-circle me-1"></i>Already here</span>
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
function assignHr(userId, branchId) {
  if (!confirm('Assign this HR to this branch?')) return;
  fetch('/api/branches/assign-hr', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    body: JSON.stringify({ user_id: userId, branch_id: branchId })
  })
  .then(r => r.json())
  .then(d => {
    showAlert(d.message, d.status === 'success' ? 'success' : 'danger');
    if (d.status === 'success') setTimeout(() => location.reload(), 1200);
  });
}

function toggleTransfer(userId, btn) {
  fetch('/api/branches/toggle-transfer-permission', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    body: JSON.stringify({ user_id: userId })
  })
  .then(r => r.json())
  .then(d => {
    showAlert(d.message, d.status === 'success' ? 'success' : 'danger');
    if (d.status === 'success') {
      const allowed = d.can_transfer_staff === 1;
      btn.className = allowed ? 'btn btn-sm btn-success' : 'btn btn-sm btn-outline-secondary';
      btn.innerHTML = allowed
        ? '<i class="mdi mdi-check-circle"></i> Allowed'
        : '<i class="mdi mdi-close-circle"></i> Not Allowed';
    }
  });
}

function showAlert(msg, type) {
  document.getElementById('alertBox').innerHTML =
    `<div class="alert alert-${type} alert-dismissible fade show">
      ${msg}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}
</script>
<?php $this->endSection(); ?>
