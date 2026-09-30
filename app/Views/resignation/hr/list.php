<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.status-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:700;text-transform:uppercase;}
.s-submitted{background:#FEF3C7;color:#92400E;} .s-manager_approved{background:#DBEAFE;color:#1E40AF;}
.s-manager_rejected,.s-hr_rejected{background:#FEE2E2;color:#991B1B;}
.s-notice_period,.s-hr_approved{background:#D1FAE5;color:#065F46;}
.s-handover{background:#EDE9FE;color:#5B21B6;} .s-clearance{background:#FEF3C7;color:#92400E;}
.s-fnf{background:#E0F2FE;color:#075985;} .s-relieved{background:#D1FAE5;color:#065F46;}
.s-withdrawn{background:#F3F4F6;color:#374151;}
.filter-btn { border: 1px solid #E5E7EB; background: #fff; color: #4B5563; border-radius: 20px; padding: 6px 16px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; cursor: pointer; }
.filter-btn:hover { border-color: #E66136; color: #E66136; background: #FFF1F0; }
.filter-btn.active { background: #E66136; color: #fff; border-color: #E66136; box-shadow: 0 4px 6px -1px rgba(230, 97, 54, 0.2); }
</style>
<div class="container-fluid">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="mdi mdi-check-circle me-2"></i><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold"><i class="mdi mdi-account-group me-2" style="color:#E66136"></i>All Resignations</h4>
    <span class="badge rounded-pill" style="background:#E66136;font-size:.85rem;"><?= count($resignations) ?> Total</span>
  </div>

  <!-- Filter pills -->
  <div class="d-flex gap-2 flex-wrap mb-4">
    <button class="filter-btn active" data-filter="all">All</button>
    <?php foreach(['submitted','manager_approved','notice_period','handover','clearance','fnf','relieved'] as $s): ?>
    <button class="filter-btn" data-filter="<?= $s ?>"><?= $s === 'submitted' ? 'Pending' : ucfirst(str_replace('_',' ',$s)) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="hrResTable">
          <thead style="background:#1F2937;color:#fff;">
            <tr>
              <th class="py-3 px-4">Employee</th>
              <th>Emp Code</th>
              <th>Resignation Date</th>
              <th>Final LWD</th>
              <th>Notice Days</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($resignations)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No resignations found.</td></tr>
            <?php else: ?>
            <?php foreach ($resignations as $r): ?>
            <tr data-status="<?= $r['status'] ?>">
              <td class="px-4">
                <div class="fw-semibold"><?= esc($r['employee_name'] ?? '—') ?></div>
                <small class="text-muted"><?= esc($r['email'] ?? '') ?></small>
              </td>
              <td><span class="badge bg-light text-dark"><?= esc($r['emp_code'] ?? '—') ?></span></td>
              <td><?= $r['resignation_date'] ?></td>
              <td><?= $r['final_lwd'] ?? '<span class="text-muted">TBD</span>' ?></td>
              <td><?= $r['notice_days'] ?> d<?= $r['notice_waived'] ? ' <span class="badge bg-info text-dark">Waived</span>' : '' ?></td>
              <td><span class="status-badge s-<?= $r['status'] ?>"><?= $r['status'] === 'submitted' ? 'Pending' : ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
              <td>
                <a href="<?= base_url('/resignation/hr/detail/'.$r['id']) ?>" class="btn btn-sm" style="background:#E66136;color:#fff;">
                  <i class="mdi mdi-eye me-1"></i>View
                </a>
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

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    const filter = this.dataset.filter;
    document.querySelectorAll('#hrResTable tbody tr').forEach(row => {
      row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
    });
  });
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
