<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.status-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;}
.s-pending{background:#FEF3C7;color:#92400E;} .s-approved{background:#D1FAE5;color:#065F46;} .s-rejected{background:#FEE2E2;color:#991B1B;}
.dept-icon { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.1rem; flex-shrink:0; }
</style>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-check-circle-outline text-primary me-2"></i>Clearance Approvals
            </h4>
            <p class="text-muted mb-0">Pending clearance requests across departments.</p>
          </div>
          <span class="badge rounded-pill hr-btnbg" style="font-size:14px; padding: 6px 12px;"><?= count($items) ?> Pending</span>
        </div>

        <div class="table-responsive">
          <table class="table table-striped w-100" id="clearanceTable">
            <thead class="table-light">
              <tr>
                <th>Department</th>
                <th>Employee</th>
                <th>LWD</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr>
                <td class="fw-bold"><?= esc($item['department']) ?> Clearance</td>
                <td><?= esc($item['employee_name'] ?? '—') ?> <small class="text-muted">(<?= esc($item['emp_code'] ?? '') ?>)</small></td>
                <td><?= $item['final_lwd'] ?? 'TBD' ?></td>
                <td><span class="status-badge s-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                <td>
                  <div class="d-flex gap-2">
                    <?php if ($item['status'] === 'pending'): ?>
                      <button class="btn btn-sm btn-success clearance-btn" data-id="<?= $item['id'] ?>" data-action="approve">
                        <i class="mdi mdi-check me-1"></i>Approve
                      </button>
                      <button class="btn btn-sm btn-danger clearance-btn" data-id="<?= $item['id'] ?>" data-action="reject">
                        <i class="mdi mdi-close me-1"></i>Reject
                      </button>
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
$(document).ready(function() {
    $('#clearanceTable').DataTable({
        "language": {
            "emptyTable": "No pending clearances!"
        }
    });
});

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
