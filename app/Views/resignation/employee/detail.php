<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<style>
.timeline-wrap { position:relative; padding-left:36px; }
.timeline-wrap::before { content:''; position:absolute; left:14px; top:0; bottom:0; width:2px; background:#E5E7EB; }
.tl-item { position:relative; margin-bottom:20px; }
.tl-dot  { position:absolute; left:-29px; top:4px; width:18px; height:18px; border-radius:50%; background:#E66136; border:3px solid #fff; box-shadow:0 0 0 2px #E66136; display:flex; align-items:center; justify-content:center; }
.tl-dot i { font-size:8px; color:#fff; }
.tl-body { background:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; padding:12px 16px; }
.tl-time { font-size:.72rem; color:#9CA3AF; }
.status-badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:.75rem; font-weight:700; text-transform:uppercase; }
.s-submitted { background:#FEF3C7;color:#92400E; } .s-manager_approved { background:#DBEAFE;color:#1E40AF; }
.s-manager_rejected,.s-hr_rejected { background:#FEE2E2;color:#991B1B; }
.s-notice_period,.s-hr_approved { background:#D1FAE5;color:#065F46; }
.s-handover { background:#EDE9FE;color:#5B21B6; } .s-clearance { background:#FEF3C7;color:#92400E; }
.s-fnf { background:#E0F2FE;color:#075985; } .s-relieved { background:#D1FAE5;color:#065F46; }
.s-withdrawn,.s-pending { background:#F3F4F6;color:#374151; }
.s-approved { background:#D1FAE5;color:#065F46; } .s-rejected { background:#FEE2E2;color:#991B1B; }
.info-card { border-radius:12px; border:1px solid #E5E7EB; background:#fff; padding:20px 24px; margin-bottom:16px; }
.info-card h6 { font-weight:700; color:#E66136; margin-bottom:12px; border-bottom:1px solid #F3F4F6; padding-bottom:8px; }
</style>

<div class="container-fluid">
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="<?= base_url('/resignation') ?>" class="btn btn-sm" style="background:#E66136;color:#fff;border-radius:20px;padding:4px 16px;"><i class="mdi mdi-arrow-left me-1"></i>Back</a>
    <h4 class="mb-0 fw-bold">Resignation Detail</h4>
    <span class="status-badge s-<?= $resignation['status'] ?>"><?= ucfirst(str_replace('_',' ', $resignation['status'])) ?></span>
  </div>

  <div class="row g-4">
    <!-- LEFT: Info -->
    <div class="col-lg-8">
      <div class="info-card">
        <h6><i class="mdi mdi-account me-2"></i>Resignation Info</h6>
        <div class="row g-2">
          <div class="col-sm-4"><small class="text-muted">Resignation Date</small><p class="fw-semibold mb-0"><?= $resignation['resignation_date'] ?></p></div>
          <div class="col-sm-4"><small class="text-muted">Requested LWD</small><p class="fw-semibold mb-0"><?= $resignation['requested_lwd'] ?? '—' ?></p></div>
          <div class="col-sm-4"><small class="text-muted">Final LWD</small><p class="fw-semibold mb-0"><?= $resignation['final_lwd'] ?? 'TBD' ?></p></div>
          <div class="col-sm-4"><small class="text-muted">Notice Period</small><p class="fw-semibold mb-0"><?= $resignation['notice_days'] ?> days</p></div>
          <div class="col-sm-4"><small class="text-muted">Shortfall Days</small><p class="fw-semibold mb-0"><?= $resignation['notice_shortfall_days'] ?></p></div>
          <div class="col-sm-4"><small class="text-muted">Waived</small><p class="fw-semibold mb-0"><?= $resignation['notice_waived'] ? 'Yes' : 'No' ?></p></div>
          <div class="col-12"><small class="text-muted">Reason</small><p class="mb-0"><?= nl2br(esc($resignation['reason'] ?? '—')) ?></p></div>
        </div>
      </div>

      <!-- Approvals -->
      <div class="info-card">
        <h6><i class="mdi mdi-check-decagram me-2"></i>Approvals</h6>
        <div class="row g-2">
          <div class="col-md-6">
            <div class="p-3 rounded" style="background:#F9FAFB">
              <small class="text-muted d-block">Manager</small>
              <strong><?= $resignation['manager_name'] ?? '—' ?></strong>
              <?php if ($resignation['manager_action_at']): ?>
                <span class="status-badge s-<?= strpos($resignation['status'],'manager_rejected') !== false ? 'manager_rejected' : 'manager_approved' ?> ms-2">
                  <?= strpos($resignation['status'],'manager_rejected') !== false ? 'Rejected' : 'Approved' ?>
                </span>
                <p class="text-muted mb-0 mt-1 small"><?= $resignation['manager_remarks'] ?></p>
              <?php else: ?><span class="badge bg-warning text-dark ms-2">Pending</span><?php endif; ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded" style="background:#F9FAFB">
              <small class="text-muted d-block">HR</small>
              <strong><?= $resignation['hr_name'] ?? '—' ?></strong>
              <?php if ($resignation['hr_action_at']): ?>
                <span class="status-badge s-<?= strpos($resignation['status'],'hr_rejected') !== false ? 'hr_rejected' : 'notice_period' ?> ms-2">
                  <?= strpos($resignation['status'],'hr_rejected') !== false ? 'Rejected' : 'Approved' ?>
                </span>
                <p class="text-muted mb-0 mt-1 small"><?= $resignation['hr_remarks'] ?></p>
              <?php else: ?><span class="badge bg-secondary ms-2">Pending</span><?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Clearance -->
      <?php if (!empty($clearance)): ?>
      <div class="info-card">
        <h6><i class="mdi mdi-check-circle-outline me-2"></i>Clearance Status</h6>
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Department</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>
              <?php foreach ($clearance as $c): ?>
              <tr>
                <td><?= $c['department'] ?></td>
                <td><span class="status-badge s-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                <td><?= esc($c['remarks'] ?? '—') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: Timeline -->
    <div class="col-lg-4">
      <div class="info-card">
        <h6><i class="mdi mdi-timeline me-2"></i>Audit Timeline</h6>
        <?php if (empty($auditLogs)): ?>
          <p class="text-muted mb-0">No activity yet.</p>
        <?php else: ?>
        <div class="timeline-wrap">
          <?php foreach ($auditLogs as $log): ?>
          <div class="tl-item">
            <div class="tl-dot"><i class="mdi mdi-circle"></i></div>
            <div class="tl-body">
              <div class="fw-semibold text-capitalize"><?= str_replace('_',' ', $log['action']) ?></div>
              <div class="small text-muted"><?= $log['actor_name'] ?? 'System' ?></div>
              <?php if ($log['remarks']): ?><div class="small mt-1"><?= esc($log['remarks']) ?></div><?php endif; ?>
              <div class="tl-time mt-1"><?= $log['created_at'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
