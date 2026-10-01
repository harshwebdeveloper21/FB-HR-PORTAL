<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<style>
.res-hero { background: linear-gradient(135deg, #E66136 0%, #c44a1f 100%); border-radius: 16px; padding: 32px; color: #fff; margin-bottom: 24px; }
.res-hero h2 { font-size: 1.6rem; font-weight: 700; margin: 0; }
.res-hero p  { margin: 6px 0 0; opacity: .85; font-size: .95rem; }
.status-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
.s-submitted        { background:#FEF3C7; color:#92400E; }
.s-manager_approved { background:#DBEAFE; color:#1E40AF; }
.s-manager_rejected,.s-hr_rejected { background:#FEE2E2; color:#991B1B; }
.s-hr_approved,.s-notice_period    { background:#D1FAE5; color:#065F46; }
.s-handover         { background:#EDE9FE; color:#5B21B6; }
.s-clearance        { background:#FEF3C7; color:#92400E; }
.s-fnf              { background:#E0F2FE; color:#075985; }
.s-relieved         { background:#D1FAE5; color:#065F46; }
.s-withdrawn        { background:#F3F4F6; color:#374151; }
.step-timeline { display:flex; align-items:flex-start; gap:0; margin: 24px 0; overflow-x:auto; padding-bottom:8px; }
.step-item { flex:1; min-width:90px; text-align:center; position:relative; }
.step-item:not(:last-child)::after { content:''; position:absolute; top:18px; left:50%; width:100%; height:3px; background:#E5E7EB; z-index:0; }
.step-item.done::after { background:#E66136; }
.step-circle { width:36px; height:36px; border-radius:50%; border:3px solid #E5E7EB; background:#fff; display:flex; align-items:center; justify-content:center; margin:0 auto 6px; position:relative; z-index:1; font-size:.85rem; font-weight:700; color:#9CA3AF; }
.step-item.done .step-circle { background:#E66136; border-color:#E66136; color:#fff; }
.step-item.active .step-circle { border-color:#E66136; color:#E66136; }
.step-label { font-size:.7rem; color:#6B7280; font-weight:600; }
.step-item.done .step-label, .step-item.active .step-label { color:#E66136; }
</style>

<div class="container-fluid">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="mdi mdi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="mdi mdi-alert-circle me-2"></i><?= session()->getFlashdata('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="res-hero">
    <h2><i class="mdi mdi-file-sign me-2"></i>My Resignation</h2>
    <p>Submit, track, and manage your resignation and exit process.</p>
  </div>

  <?php if ($resignation && !in_array($resignation['status'], ['withdrawn','manager_rejected','hr_rejected','relieved'])): ?>

    <!-- Active Resignation: Status + Timeline -->
    <?php
      $steps = ['submitted'=>'Submitted','manager_approved'=>'Mgr Approval','notice_period'=>'Notice Period','handover'=>'Handover','clearance'=>'Clearance','fnf'=>'F&F','relieved'=>'Relieved'];
      $order = array_keys($steps);
      $curIdx = array_search($resignation['status'], $order) ?: 0;
    ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="mb-0 fw-bold"><i class="mdi mdi-information-outline me-2 text-primary"></i>Resignation Status</h5>
          <span class="status-badge s-<?= $resignation['status'] ?>"><?= ucfirst(str_replace('_',' ', $resignation['status'])) ?></span>
        </div>

        <div class="step-timeline">
          <?php foreach ($steps as $key => $label):
            $idx = array_search($key, $order);
            $class = $idx < $curIdx ? 'done' : ($idx === $curIdx ? 'active' : '');
          ?>
          <div class="step-item <?= $class ?>">
            <div class="step-circle">
              <?php if ($idx < $curIdx): ?><i class="mdi mdi-check"></i><?php else: ?><?= $idx+1 ?><?php endif; ?>
            </div>
            <div class="step-label"><?= $label ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="row mt-3 g-3">
          <div class="col-md-3"><small class="text-muted d-block">Resignation Date</small><strong><?= $resignation['resignation_date'] ?></strong></div>
          <div class="col-md-3"><small class="text-muted d-block">Requested LWD</small><strong><?= $resignation['requested_lwd'] ?? '—' ?></strong></div>
          <div class="col-md-3"><small class="text-muted d-block">Final LWD (HR)</small><strong><?= $resignation['final_lwd'] ?? 'Pending' ?></strong></div>
          <div class="col-md-3"><small class="text-muted d-block">Notice Days</small><strong><?= $resignation['notice_days'] ?> days</strong></div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <a href="<?= base_url('/resignation/detail/'.$resignation['id']) ?>" class="btn btn-sm btn-outline-primary">
            <i class="mdi mdi-eye me-1"></i>View Details
          </a>
          <?php if (in_array($resignation['status'], ['submitted','manager_approved'])): ?>
          <a href="<?= base_url('/resignation/withdraw/'.$resignation['id']) ?>"
             class="btn btn-sm btn-outline-danger"
             onclick="return confirm('Are you sure you want to withdraw your resignation?')">
            <i class="mdi mdi-undo me-1"></i>Withdraw
          </a>
          <?php endif; ?>
          <?php if (in_array($resignation['status'], ['manager_approved', 'notice_period', 'handover', 'clearance', 'fnf', 'relieved'])): ?>
          <a href="<?= base_url('/resignation/handover/'.$resignation['id']) ?>" class="btn btn-sm" style="background:#E66136;color:#fff;">
            <i class="mdi mdi-swap-horizontal me-1"></i>Manage Handover
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  <?php elseif (!$resignation || in_array($resignation['status'], ['withdrawn','manager_rejected','hr_rejected','relieved'])): ?>

    <!-- Submit new resignation -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-4"><i class="mdi mdi-send me-2" style="color:#E66136"></i>Submit Resignation</h5>
        <form action="<?= base_url('/resignation/submit') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Resignation Date <span class="text-danger">*</span></label>
              <input type="date" name="resignation_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Requested Last Working Day</label>
              <input type="date" name="requested_lwd" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Reason for Resignation <span class="text-danger">*</span></label>
              <textarea name="reason" class="form-control" rows="4" required placeholder="Please state your reason..."></textarea>
            </div>
            <div class="col-12">
              <div class="alert alert-warning py-2 mb-0">
                <i class="mdi mdi-information me-1"></i>
                Once submitted, your resignation will go to your reporting manager for approval. You may withdraw before HR approval.
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn px-4 py-2 fw-bold" style="background:#E66136;color:#fff;">
                <i class="mdi mdi-send me-2"></i>Submit Resignation
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

  <?php endif; ?>

  <?php if ($resignation && $resignation['status'] === 'relieved'): ?>
    <div class="alert alert-success mt-3">
      <i class="mdi mdi-check-circle me-2"></i>
      You have been <strong>relieved</strong> as of <strong><?= $resignation['final_lwd'] ?></strong>. Thank you for your contributions!
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
