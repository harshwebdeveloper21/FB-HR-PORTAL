<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.status-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;}
.s-pending{background:#FEF3C7;color:#92400E;} .s-approved{background:#D1FAE5;color:#065F46;} .s-rejected{background:#FEE2E2;color:#991B1B;}
.dept-icon { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.1rem; flex-shrink:0; }
</style>
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold"><i class="mdi mdi-check-circle-outline me-2" style="color:#E66136"></i>Clearance Approvals</h4>
    <span class="badge rounded-pill" style="background:#E66136;font-size:.85rem;"><?= count($items) ?> Pending</span>
  </div>

  <?php if (empty($items)): ?>
  <div class="text-center py-5">
    <i class="mdi mdi-check-all" style="font-size:4rem;color:#D1FAE5;"></i>
    <h5 class="mt-3 text-muted">No pending clearances!</h5>
    <p class="text-muted">All clearance items are processed.</p>
  </div>
  <?php else: ?>
  <div class="row g-3">
    <?php
      $deptColors = ['Manager'=>'#6366F1','IT'=>'#0EA5E9','Admin'=>'#F59E0B','Finance'=>'#10B981','HR'=>'#E66136'];
      $deptIcons  = ['Manager'=>'mdi-account-star','IT'=>'mdi-laptop','Admin'=>'mdi-office-building','Finance'=>'mdi-currency-inr','HR'=>'mdi-account-group'];
    ?>
    <?php foreach ($items as $item):
      $color = $deptColors[$item['department']] ?? '#6B7280';
      $icon  = $deptIcons[$item['department']]  ?? 'mdi-check';
    ?>
    <div class="col-md-6 col-xl-4">
      <div class="card border-0 shadow-sm h-100" style="border-left:4px solid <?= $color ?>!important;">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="dept-icon" style="background:<?= $color ?>;">
              <i class="mdi <?= $icon ?>"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-0"><?= $item['department'] ?> Clearance</h6>
              <small class="text-muted"><?= esc($item['employee_name'] ?? '—') ?> · <?= esc($item['emp_code'] ?? '') ?></small>
            </div>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <small class="text-muted">LWD</small>
            <strong><?= $item['final_lwd'] ?? 'TBD' ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <small class="text-muted">Status</small>
            <span class="status-badge s-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
          </div>
          <?php if ($item['status'] === 'pending'): ?>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-success flex-fill clearance-btn" data-id="<?= $item['id'] ?>" data-action="approve">
              <i class="mdi mdi-check me-1"></i>Approve
            </button>
            <button class="btn btn-sm btn-danger flex-fill clearance-btn" data-id="<?= $item['id'] ?>" data-action="reject">
              <i class="mdi mdi-close me-1"></i>Reject
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Clearance Action Modal -->
<div class="modal fade" id="clearanceModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#E66136;">
      <h5 class="modal-title text-white" id="clearanceModalTitle">Clearance Action</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <label class="form-label fw-semibold">Remarks</label>
      <textarea id="clearanceRemarks" class="form-control" rows="3" placeholder="Add any notes or dues..."></textarea>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button type="button" id="clearanceConfirmBtn" class="btn fw-bold" style="background:#E66136;color:#fff;">Confirm</button>
    </div>
  </div></div>
</div>

<?= $this->section('scripts') ?>
<script>
let pendingAction = null, pendingId = null;
document.querySelectorAll('.clearance-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    pendingId     = this.dataset.id;
    pendingAction = this.dataset.action;
    document.getElementById('clearanceModalTitle').textContent = pendingAction === 'approve' ? '✅ Approve Clearance' : '❌ Reject Clearance';
    document.getElementById('clearanceRemarks').value = '';
    new bootstrap.Modal(document.getElementById('clearanceModal')).show();
  });
});
document.getElementById('clearanceConfirmBtn').addEventListener('click', function() {
  const fd = new FormData();
  fd.append('action',  pendingAction);
  fd.append('remarks', document.getElementById('clearanceRemarks').value);
  fetch(`<?= base_url('/api/resignation/clearance/') ?>${pendingId}`, { method:'POST', body:fd })
    .then(r => r.json()).then(res => {
      if (res.status === 'success') location.reload();
      else alert(res.message);
    });
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
