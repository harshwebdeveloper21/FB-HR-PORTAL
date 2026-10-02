<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<style>
.task-card { border:1px solid #E5E7EB; border-radius:10px; padding:16px; background:#fff; margin-bottom:12px; transition:box-shadow .2s; }
.task-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
.status-badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:.75rem; font-weight:700; text-transform:uppercase; }
.s-pending   { background:#FEF3C7;color:#92400E; }
.s-accepted  { background:#DBEAFE;color:#1E40AF; }
.s-completed { background:#D1FAE5;color:#065F46; }
</style>

<div class="container-fluid">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="mdi mdi-check-circle me-2"></i><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="mdi mdi-alert me-2"></i><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-swap-horizontal text-primary me-2"></i>My Handover Tasks
            </h4>
            <p class="text-muted mb-0">View and manage handover tasks assigned to you or across the company.</p>
          </div>
          <div class="d-flex gap-2">
            <?php if (in_array($user->role, ['hr', 'admin', 'branch_admin'])): ?>
            <button class="btn hr-btnbg" data-bs-toggle="modal" data-bs-target="#addTaskModal">
              <i class="mdi mdi-plus me-1"></i> Add Task
            </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover w-100" id="myHandoverTable">
            <thead>
              <tr>
              <th>Task</th>
              <th>Description</th>
              <th>From Employee</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Remarks</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
            <tr>
              <td class="fw-bold"><?= esc($t['task']) ?></td>
              <td><?= esc($t['description'] ?? '') ?></td>
              <td><?= esc($t['from_employee'] ?? 'N/A') ?></td>
              <td><?= $t['due_date'] ?? '—' ?></td>
              <td><span class="status-badge s-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
              <td><?= esc($t['acceptor_remarks'] ?? '—') ?></td>
              <td>
                <div class="d-flex gap-2">
                  <?php if ($t['status'] === 'pending' && $t['handover_to'] == $user->sub): ?>
                    <button class="btn btn-sm btn-outline-primary task-action-btn" data-id="<?= $t['id'] ?>" data-status="accepted">
                      <i class="mdi mdi-thumb-up me-1"></i>Accept
                    </button>
                  <?php endif; ?>
                  
                  <?php if (in_array($user->role, ['admin','hr','branch_admin']) || $t['handover_to'] == $user->sub): ?>
                    <?php if ($t['status'] === 'accepted' || $t['status'] === 'pending'): ?>
                      <button class="btn btn-sm btn-outline-success task-action-btn" data-id="<?= $t['id'] ?>" data-status="completed">
                        <i class="mdi mdi-check-all me-1"></i>Approve & Complete
                      </button>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div>
</div>
</div>

<?php if (in_array($user->role, ['hr', 'admin', 'branch_admin'])): ?>
<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#E66136;">
        <h5 class="modal-title text-white"><i class="mdi mdi-plus-circle me-2"></i>Add Handover Task</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Resigning Employee <span class="text-danger">*</span></label>
          <select id="resignationId" class="form-select">
            <option value="">— Select Employee —</option>
            <?php foreach ($activeResignations as $r): ?>
            <option value="<?= $r['id'] ?>"><?= esc($r['emp_name']) ?> (Resignation #<?= $r['id'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Task Name <span class="text-danger">*</span></label>
          <input type="text" id="taskName" class="form-control" placeholder="e.g. Hand over project files">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Description</label>
          <textarea id="taskDesc" class="form-control" rows="3" placeholder="Details..."></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Handover To <span class="text-danger">*</span></label>
          <select id="handoverTo" class="form-select">
            <option value="">— Select Employee —</option>
            <?php foreach ($employees as $e): ?>
            <option value="<?= $e['id'] ?>"><?= esc($e['username'] ?? 'User #'.$e['id']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Due Date</label>
          <input type="date" id="dueDate" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="addTaskBtn" class="btn fw-bold" style="background:#E66136;color:#fff;">Add Task</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#myHandoverTable').DataTable({
        "paging": false,
        "searching": false,
        "info": false,
        "ordering": false,
        "language": {
            "emptyTable": "No pending handover tasks."
        }
    });
});

document.querySelectorAll('.task-action-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const id     = this.dataset.id;
    const status = this.dataset.status;
    let remarks = '';
    
    if (status === 'completed') {
        const userRemarks = prompt('Add any remarks/notes (optional):');
        if (userRemarks === null) return; // cancelled
        remarks = userRemarks;
    }
    
    const data = new FormData();
    data.append('status',  status);
    data.append('remarks', remarks);

    fetch(`<?= base_url('/api/resignation/handover/update/') ?>${id}`, {
      method: 'POST', body: data
    }).then(r => r.json()).then(res => {
      if (res.status === 'success') { location.reload(); }
      else { alert(res.message); }
    });
  });
});

document.getElementById('addTaskBtn')?.addEventListener('click', function() {
  const resignationId = document.getElementById('resignationId').value;
  const taskName = document.getElementById('taskName').value.trim();
  const handoverTo = document.getElementById('handoverTo').value;

  if (!resignationId) return alert('Please select a resigning employee.');
  if (!taskName) return alert('Task Name is required.');
  if (!handoverTo) return alert('Please select an employee to handover to.');

  const data = new FormData();
  data.append('resignation_id', resignationId);
  data.append('task', taskName);
  data.append('description', document.getElementById('taskDesc').value.trim());
  data.append('handover_to', handoverTo);
  data.append('due_date', document.getElementById('dueDate').value);

  this.disabled = true;
  this.textContent = 'Adding...';

  fetch(`<?= base_url('/api/resignation/handover/add') ?>`, {
    method: 'POST', body: data
  }).then(r => r.json()).then(res => {
    if (res.status === 'success') {
      location.reload();
    } else {
      alert(res.message);
      this.disabled = false;
      this.textContent = 'Add Task';
    }
  });
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
