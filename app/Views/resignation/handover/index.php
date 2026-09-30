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

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="<?= base_url('/resignation') ?>" class="btn btn-sm" style="background:#E66136;color:#fff;border-radius:20px;padding:4px 16px;"><i class="mdi mdi-arrow-left me-1"></i>Back</a>
      <div>
        <h4 class="mb-0 fw-bold">Handover Tasks</h4>
        <small class="text-muted">Resignation #<?= $resignation['id'] ?> — LWD: <strong><?= $resignation['final_lwd'] ?? 'TBD' ?></strong></small>
      </div>
    </div>
    <?php if ((int)$resignation['employee_id'] === (int)$user->sub): ?>
    <button class="btn btn-sm fw-bold" style="background:#E66136;color:#fff;" data-bs-toggle="modal" data-bs-target="#addTaskModal">
      <i class="mdi mdi-plus me-1"></i>Add Task
    </button>
    <?php endif; ?>
  </div>

  <div class="row g-3">
    <?php if (empty($tasks)): ?>
    <div class="col-12">
      <div class="text-center py-5">
        <i class="mdi mdi-clipboard-text-outline" style="font-size:3rem;color:#D1D5DB;"></i>
        <p class="mt-2 text-muted">No handover tasks added yet.</p>
      </div>
    </div>
    <?php else: ?>
    <?php foreach ($tasks as $t): ?>
    <div class="col-md-6">
      <div class="task-card">
        <div class="d-flex justify-content-between align-items-start">
          <h6 class="fw-bold mb-1"><?= esc($t['task']) ?></h6>
          <span class="status-badge s-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span>
        </div>
        <p class="text-muted small mb-2"><?= esc($t['description'] ?? '') ?></p>
        <div class="d-flex gap-3 small">
          <span><i class="mdi mdi-account-arrow-right text-primary me-1"></i><strong>To:</strong> <?= esc($t['handover_to_name'] ?? 'N/A') ?></span>
          <span><i class="mdi mdi-calendar text-warning me-1"></i><strong>Due:</strong> <?= $t['due_date'] ?? '—' ?></span>
        </div>
        <?php if ($t['acceptor_remarks']): ?>
          <p class="small text-muted mt-2 mb-0"><em>"<?= esc($t['acceptor_remarks']) ?>"</em></p>
        <?php endif; ?>
        <!-- Receiver actions -->
        <?php if ($t['handover_to'] == $user->sub && $t['status'] === 'pending'): ?>
        <div class="mt-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary task-action-btn" data-id="<?= $t['id'] ?>" data-status="accepted">
            <i class="mdi mdi-thumb-up me-1"></i>Accept
          </button>
        </div>
        <?php elseif ($t['handover_to'] == $user->sub && $t['status'] === 'accepted'): ?>
        <div class="mt-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-success task-action-btn" data-id="<?= $t['id'] ?>" data-status="completed">
            <i class="mdi mdi-check me-1"></i>Mark Completed
          </button>
        </div>
        <?php endif; ?>
        <!-- Manager can also mark completed -->
        <?php if (in_array($user->role, ['admin','hr','department_manager','branch_admin']) && $t['status'] !== 'completed'): ?>
        <div class="mt-2">
          <button class="btn btn-xs btn-outline-success task-action-btn" style="font-size:.75rem;padding:2px 8px;" data-id="<?= $t['id'] ?>" data-status="completed">
            <i class="mdi mdi-check me-1"></i>Force Complete
          </button>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

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

<?= $this->section('scripts') ?>
<script>
const RESIGNATION_ID = <?= $resignation['id'] ?>;

document.getElementById('addTaskBtn')?.addEventListener('click', function() {
  const data = new FormData();
  data.append('resignation_id', RESIGNATION_ID);
  data.append('task',        document.getElementById('taskName').value.trim());
  data.append('description', document.getElementById('taskDesc').value.trim());
  data.append('handover_to', document.getElementById('handoverTo').value);
  data.append('due_date',    document.getElementById('dueDate').value);

  if (!data.get('task') || !data.get('handover_to')) {
    alert('Task name and handover recipient are required.');
    return;
  }

  fetch('<?= base_url('/api/resignation/handover/add') ?>', {
    method: 'POST', body: data
  }).then(r => r.json()).then(res => {
    if (res.status === 'success') { location.reload(); }
    else { alert(res.message); }
  });
});

document.querySelectorAll('.task-action-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const id     = this.dataset.id;
    const status = this.dataset.status;
    const remarks= status === 'completed' ? (prompt('Remarks (optional):') ?? '') : '';
    const data   = new FormData();
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
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
