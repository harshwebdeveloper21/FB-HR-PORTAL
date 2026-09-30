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

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <h4 class="mb-0 fw-bold"><i class="mdi mdi-swap-horizontal me-2" style="color:#E66136"></i>My Handover Tasks</h4>
    </div>
  </div>

  <div class="row g-3">
    <?php if (empty($tasks)): ?>
    <div class="col-12">
      <div class="text-center py-5">
        <i class="mdi mdi-clipboard-check-outline" style="font-size:3rem;color:#D1D5DB;"></i>
        <p class="mt-2 text-muted">You have no pending handover tasks assigned to you.</p>
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
        <div class="d-flex flex-column gap-1 small mb-2">
          <span><i class="mdi mdi-account-arrow-left text-primary me-1"></i><strong>From:</strong> <?= esc($t['from_employee'] ?? 'N/A') ?></span>
          <span><i class="mdi mdi-calendar text-warning me-1"></i><strong>Due:</strong> <?= $t['due_date'] ?? '—' ?></span>
        </div>
        
        <?php if ($t['acceptor_remarks']): ?>
          <p class="small text-muted mt-2 mb-0 border-top pt-2"><em>Your remarks: "<?= esc($t['acceptor_remarks']) ?>"</em></p>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <?php if ($t['status'] === 'pending'): ?>
        <div class="mt-3 pt-2 border-top d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary task-action-btn" data-id="<?= $t['id'] ?>" data-status="accepted">
            <i class="mdi mdi-thumb-up me-1"></i>Accept Task
          </button>
        </div>
        <?php elseif ($t['status'] === 'accepted'): ?>
        <div class="mt-3 pt-2 border-top d-flex gap-2">
          <button class="btn btn-sm btn-outline-success task-action-btn" data-id="<?= $t['id'] ?>" data-status="completed">
            <i class="mdi mdi-check-all me-1"></i>Mark Completed
          </button>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?= $this->section('scripts') ?>
<script>
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
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
