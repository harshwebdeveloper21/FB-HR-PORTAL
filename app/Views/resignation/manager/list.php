<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.status-badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:.75rem; font-weight:700; text-transform:uppercase; }
.s-submitted{background:#FEF3C7;color:#92400E;} .s-manager_approved{background:#DBEAFE;color:#1E40AF;}
.s-manager_rejected,.s-hr_rejected{background:#FEE2E2;color:#991B1B;}
.s-notice_period,.s-hr_approved{background:#D1FAE5;color:#065F46;}
.s-handover{background:#EDE9FE;color:#5B21B6;} .s-clearance{background:#FEF3C7;color:#92400E;}
.s-fnf{background:#E0F2FE;color:#075985;} .s-relieved{background:#D1FAE5;color:#065F46;}
.s-withdrawn{background:#F3F4F6;color:#374151;}
</style>
<div class="container-fluid">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="mdi mdi-check-circle me-2"></i><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="mdi mdi-alert me-2"></i><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold"><i class="mdi mdi-account-supervisor me-2" style="color:#E66136"></i>Pending Resignations</h4>
    <span class="badge rounded-pill" style="background:#E66136;font-size:.85rem;"><?= count($all) ?> Total</span>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead style="background:#1F2937;color:#fff;">
            <tr>
              <th class="py-3 px-4">Employee</th>
              <th>Resignation Date</th>
              <th>Requested LWD</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($all)): ?>
            <tr><td colspan="5" class="text-center py-5 text-muted">No resignations assigned to you.</td></tr>
            <?php else: ?>
            <?php foreach ($all as $r): ?>
            <tr>
              <td class="px-4">
                <div class="fw-semibold"><?= esc($r['employee_name'] ?? '—') ?></div>
                <small class="text-muted"><?= esc($r['emp_code'] ?? '') ?></small>
              </td>
              <td><?= $r['resignation_date'] ?></td>
              <td><?= $r['requested_lwd'] ?? '—' ?></td>
              <td><span class="status-badge s-<?= $r['status'] ?>"><?= $r['status'] === 'submitted' ? 'Pending' : ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
              <td>
                <?php if ($r['status'] === 'submitted'): ?>
                <button class="btn btn-sm btn-outline-info me-1" onclick="openViewModal(`<?= htmlspecialchars($r['employee_name']) ?>`, `<?= htmlspecialchars($r['resignation_date']) ?>`, `<?= htmlspecialchars($r['requested_lwd'] ?? 'TBD') ?>`, `<?= htmlspecialchars(nl2br($r['reason'] ?? '')) ?>`)">
                  <i class="mdi mdi-eye"></i> View
                </button>
                <button class="btn btn-sm btn-outline-success me-1" onclick="openActionModal(<?= $r['id'] ?>, 'approve')">
                  <i class="mdi mdi-check"></i> Approve
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="openActionModal(<?= $r['id'] ?>, 'reject')">
                  <i class="mdi mdi-close"></i> Reject
                </button>
                <?php else: ?>
                <button class="btn btn-sm btn-outline-info me-1" onclick="openViewModal(`<?= htmlspecialchars($r['employee_name']) ?>`, `<?= htmlspecialchars($r['resignation_date']) ?>`, `<?= htmlspecialchars($r['requested_lwd'] ?? 'TBD') ?>`, `<?= htmlspecialchars(nl2br($r['reason'] ?? '')) ?>`)">
                  <i class="mdi mdi-eye"></i> View
                </button>
                <span class="text-muted small"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span>
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

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="actionForm" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="actionInput">
        <div class="modal-header" style="background:#E66136;">
          <h5 class="modal-title text-white" id="actionModalTitle">Resignation Action</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3" placeholder="Add your remarks..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn fw-bold" style="background:#E66136;color:#fff;" id="actionSubmitBtn">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#E66136;">
        <h5 class="modal-title text-white"><i class="mdi mdi-information-outline me-2"></i>Resignation Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h6 class="fw-bold mb-1 text-primary" id="viewEmpName"></h6>
        <div class="d-flex gap-3 text-muted small border-bottom pb-2 mb-3">
          <span><i class="mdi mdi-calendar-check me-1"></i>Date: <strong id="viewDate"></strong></span>
          <span><i class="mdi mdi-calendar-remove me-1"></i>Req LWD: <strong id="viewLwd"></strong></span>
        </div>
        <h6 class="fw-semibold">Reason for Resignation:</h6>
        <div class="p-3 bg-light rounded" id="viewReason" style="font-size:0.9rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?= $this->section('scripts') ?>
<script>
function openActionModal(id, action) {
  document.getElementById('actionInput').value = action;
  document.getElementById('actionModalTitle').textContent = action === 'approve' ? '✅ Approve Resignation' : '❌ Reject Resignation';
  document.getElementById('actionForm').action = `<?= base_url('/resignation/manager/action/') ?>${id}`;
  new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function openViewModal(empName, date, lwd, reason) {
  document.getElementById('viewEmpName').textContent = empName;
  document.getElementById('viewDate').textContent = date;
  document.getElementById('viewLwd').textContent = lwd;
  document.getElementById('viewReason').innerHTML = reason;
  new bootstrap.Modal(document.getElementById('viewModal')).show();
}
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
