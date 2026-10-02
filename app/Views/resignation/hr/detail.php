<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.status-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;}
.s-submitted{background:#FEF3C7;color:#92400E;} .s-manager_approved{background:#DBEAFE;color:#1E40AF;}
.s-manager_rejected,.s-hr_rejected{background:#FEE2E2;color:#991B1B;}
.s-notice_period,.s-hr_approved{background:#D1FAE5;color:#065F46;}
.s-handover{background:#EDE9FE;color:#5B21B6;} .s-clearance{background:#FEF3C7;color:#92400E;}
.s-fnf{background:#E0F2FE;color:#075985;} .s-relieved{background:#D1FAE5;color:#065F46;}
.s-withdrawn{background:#F3F4F6;color:#374151;}
.s-pending{background:#FEF3C7;color:#92400E;} .s-approved{background:#D1FAE5;color:#065F46;} .s-rejected{background:#FEE2E2;color:#991B1B;}
.s-draft{background:#F3F4F6;color:#374151;} .s-hr_prepared{background:#DBEAFE;color:#1E40AF;} .s-finance_approved{background:#D1FAE5;color:#065F46;} .s-paid{background:#065F46;color:#fff;}
.timeline-wrap{position:relative;padding-left:32px;}
.timeline-wrap::before{content:'';position:absolute;left:12px;top:0;bottom:0;width:2px;background:#E5E7EB;}
.tl-item{position:relative;margin-bottom:16px;}
.tl-dot{position:absolute;left:-25px;top:4px;width:16px;height:16px;border-radius:50%;background:#E66136;border:3px solid #fff;box-shadow:0 0 0 2px #E66136;}
.tl-body{background:#F9FAFB;border:1px solid #E5E7EB;border-radius:10px;padding:10px 14px;}
.tab-btn{border:none;background:none;padding:10px 20px;border-bottom:3px solid transparent;font-weight:600;color:#6B7280;cursor:pointer;transition:.2s;}
.tab-btn.active{border-bottom-color:#E66136;color:#E66136;}
.tab-pane{display:none;} .tab-pane.show{display:block;}
.fnf-table tfoot td { font-weight: 700; background: #F9FAFB; }
</style>

<div class="container-fluid">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="mdi mdi-check-circle me-2"></i><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="mdi mdi-alert me-2"></i><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= base_url('/resignation/hr') ?>" class="btn btn-sm" style="background:#E66136;color:#fff;border-radius:20px;padding:4px 16px;"><i class="mdi mdi-arrow-left me-1"></i>Back</a>
    <div>
      <h4 class="mb-0 fw-bold"><?= esc($resignation['firstname'].' '.$resignation['lastname']) ?></h4>
      <small class="text-muted"><?= esc($resignation['emp_code'] ?? '') ?> · <?= esc($resignation['email'] ?? '') ?></small>
    </div>
    <span class="status-badge s-<?= $resignation['status'] ?>"><?= ucfirst(str_replace('_',' ',$resignation['status'])) ?></span>
  </div>

  <!-- Quick Info Bar -->
  <div class="row g-2 mb-4">
    <?php
      $infoCards = [
        ['label'=>'Resignation Date','value'=>$resignation['resignation_date'],'icon'=>'mdi-calendar-check','color'=>'#E66136'],
        ['label'=>'Requested LWD','value'=>$resignation['requested_lwd']??'—','icon'=>'mdi-calendar-remove','color'=>'#F59E0B'],
        ['label'=>'Final LWD','value'=>$resignation['final_lwd']??'TBD','icon'=>'mdi-calendar-star','color'=>'#10B981'],
        ['label'=>'Notice Days','value'=>$resignation['notice_days'].' days','icon'=>'mdi-timer-sand','color'=>'#6366F1'],
        ['label'=>'Shortfall Days','value'=>$resignation['notice_shortfall_days'],'icon'=>'mdi-delta','color'=>'#EF4444'],
      ];
    ?>
    <?php foreach ($infoCards as $ic): ?>
    <div class="col-6 col-md-2">
      <div class="card border-0 shadow-sm h-100" style="border-left:4px solid <?= $ic['color'] ?>!important;">
        <div class="card-body py-2 px-3">
          <small class="text-muted d-block"><?= $ic['label'] ?></small>
          <strong class="d-block"><?= $ic['value'] ?></strong>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Tab Navigation -->
  <div style="border-bottom:1px solid #E5E7EB;margin-bottom:24px;">
    <button class="tab-btn active" data-tab="info">Info & Approvals</button>
    <button class="tab-btn" data-tab="notice">Notice Period</button>
    <button class="tab-btn" data-tab="handover">Handover</button>
    <button class="tab-btn" data-tab="clearance">Clearance</button>
    <button class="tab-btn" data-tab="fnf">F&F Settlement</button>
    <button class="tab-btn" data-tab="audit">Timeline</button>
  </div>

  <!-- TAB: Info & Approvals -->
  <div class="tab-pane show" id="tab-info">
    <div class="row g-4">
      <div class="col-md-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-3" style="color:#E66136;"><i class="mdi mdi-text me-2"></i>Reason for Resignation</h6>
            <p class="mb-0"><?= nl2br(esc($resignation['reason'] ?? '—')) ?></p>
          </div>
        </div>

        <?php if ($resignation['status'] === 'manager_approved'): ?>
        <!-- HR Approval Form -->
        <div class="card border-0 shadow-sm mt-3">
          <div class="card-body">
            <h6 class="fw-bold mb-3" style="color:#E66136;"><i class="mdi mdi-check-decagram me-2"></i>HR Decision</h6>
            <form action="<?= base_url('/resignation/hr/action/'.$resignation['id']) ?>" method="POST">
              <?= csrf_field() ?>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Final LWD <span class="text-danger">*</span></label>
                  <input type="date" name="final_lwd" class="form-control" required
                    value="<?= $resignation['final_lwd'] ?? date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Notice Period (days)</label>
                  <input type="number" name="notice_days" class="form-control" value="<?= $resignation['notice_days'] ?>">
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">HR Remarks</label>
                  <textarea name="remarks" class="form-control" rows="3" placeholder="Add remarks..."></textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                  <button type="submit" name="action" value="approve" class="btn fw-bold" style="background:#10B981;color:#fff;">
                    <i class="mdi mdi-check me-1"></i>Approve
                  </button>
                  <button type="submit" name="action" value="reject" class="btn fw-bold btn-danger">
                    <i class="mdi mdi-close me-1"></i>Reject
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <!-- Manager Approval -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-2" style="color:#E66136;"><i class="mdi mdi-account-check me-2"></i>Manager</h6>
            <p class="mb-1"><strong><?= esc($resignation['manager_name'] ?? '—') ?></strong></p>
            <?php if ($resignation['manager_action_at']): ?>
              <span class="status-badge s-<?= strpos($resignation['status'],'manager_rejected')!==false ? 'manager_rejected':'manager_approved' ?>">
                <?= strpos($resignation['status'],'manager_rejected')!==false ? 'Rejected':'Approved' ?>
              </span>
              <p class="small text-muted mt-1"><?= esc($resignation['manager_remarks'] ?? '') ?></p>
              <small class="text-muted"><?= $resignation['manager_action_at'] ?></small>
            <?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?>
          </div>
        </div>
        <!-- HR Approval -->
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h6 class="fw-bold mb-2" style="color:#E66136;"><i class="mdi mdi-account-star me-2"></i>HR</h6>
            <p class="mb-1"><strong><?= esc($resignation['hr_name'] ?? '—') ?></strong></p>
            <?php if ($resignation['hr_action_at']): ?>
              <span class="status-badge s-<?= strpos($resignation['status'],'hr_rejected')!==false ? 'hr_rejected':'notice_period' ?>">
                <?= strpos($resignation['status'],'hr_rejected')!==false ? 'Rejected':'Approved' ?>
              </span>
              <p class="small text-muted mt-1"><?= esc($resignation['hr_remarks'] ?? '') ?></p>
              <small class="text-muted"><?= $resignation['hr_action_at'] ?></small>
            <?php else: ?><span class="badge bg-secondary">Pending</span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB: Notice Period -->
  <div class="tab-pane" id="tab-notice">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3" style="color:#E66136;"><i class="mdi mdi-timer-sand me-2"></i>Notice Period Details</h6>
        <?php
          $startDate = $resignation['resignation_date'];
          $finalLwd  = $resignation['final_lwd'];
          $noticeDays= (int)$resignation['notice_days'];
          $todayTs   = time();
          $lwdTs     = $finalLwd ? strtotime($finalLwd) : 0;
          $startTs   = strtotime($startDate);
          $served    = max(0, min($noticeDays, (int)(($todayTs - $startTs) / 86400)));
          $remaining = max(0, $lwdTs ? (int)(($lwdTs - $todayTs) / 86400) : $noticeDays);
        ?>
        <div class="row g-3 mb-4">
          <div class="col-md-3"><div class="p-3 rounded" style="background:#EFF6FF;"><small class="text-muted d-block">Start Date</small><strong><?= $startDate ?></strong></div></div>
          <div class="col-md-3"><div class="p-3 rounded" style="background:#F0FDF4;"><small class="text-muted d-block">Final LWD</small><strong><?= $finalLwd ?? 'TBD' ?></strong></div></div>
          <div class="col-md-3"><div class="p-3 rounded" style="background:#FFF7ED;"><small class="text-muted d-block">Days Served</small><strong><?= $served ?> / <?= $noticeDays ?></strong></div></div>
          <div class="col-md-3"><div class="p-3 rounded" style="background:<?= $remaining > 7 ? '#F0FDF4' : '#FEF2F2' ?>;"><small class="text-muted d-block">Days Remaining</small><strong><?= $remaining ?></strong></div></div>
        </div>

        <!-- Progress bar -->
        <div class="mb-4">
          <div class="d-flex justify-content-between mb-1"><small>Notice Period Progress</small><small><?= $noticeDays > 0 ? round($served/$noticeDays*100) : 0 ?>%</small></div>
          <div class="progress" style="height:10px;border-radius:8px;">
            <div class="progress-bar" style="width:<?= $noticeDays > 0 ? round($served/$noticeDays*100) : 0 ?>%;background:#E66136;border-radius:8px;"></div>
          </div>
        </div>

        <?php if (in_array($resignation['status'], ['notice_period','handover','clearance','fnf'])): ?>
        <!-- Update Notice Period -->
        <form action="<?= base_url('/resignation/hr/notice/'.$resignation['id']) ?>" method="POST" class="border-top pt-3">
          <?= csrf_field() ?>
          <h6 class="fw-semibold mb-3">Update Notice Period</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">New Final LWD</label>
              <input type="date" name="final_lwd" class="form-control" value="<?= $finalLwd ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Notice Days</label>
              <input type="number" name="notice_days" class="form-control" value="<?= $noticeDays ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Notice Waived/Buyout</label>
              <select name="notice_waived" class="form-select">
                <option value="0" <?= !$resignation['notice_waived'] ? 'selected' : '' ?>>No</option>
                <option value="1" <?= $resignation['notice_waived']  ? 'selected' : '' ?>>Yes (Waived)</option>
              </select>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-sm fw-bold" style="background:#E66136;color:#fff;">
                <i class="mdi mdi-content-save me-1"></i>Save Changes
              </button>
            </div>
          </div>
        </form>

        <?php if ($resignation['status'] === 'notice_period'): ?>
        <div class="border-top pt-3 mt-3">
          <form action="<?= base_url('/resignation/hr/advance-handover/'.$resignation['id']) ?>" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Advance to Handover phase?')">
              <i class="mdi mdi-arrow-right me-1"></i>Advance to Handover
            </button>
          </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TAB: Handover -->
  <div class="tab-pane" id="tab-handover">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3" style="color:#E66136;"><i class="mdi mdi-swap-horizontal me-2"></i>Handover Tasks</h6>
        <?php if (empty($handover)): ?>
          <p class="text-muted">No handover tasks yet.</p>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover table-sm mb-0">
            <thead class="table-light"><tr><th>Task</th><th>Handover To</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($handover as $t): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($t['task']) ?></div><small class="text-muted"><?= esc($t['description'] ?? '') ?></small></td>
                <td><?= esc($t['handover_to_name'] ?? '—') ?></td>
                <td><?= $t['due_date'] ?? '—' ?></td>
                <td><span class="status-badge s-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
        <a href="<?= base_url('/resignation/handover/'.$resignation['id']) ?>" class="btn btn-sm btn-outline-primary mt-3">
          <i class="mdi mdi-open-in-new me-1"></i>Manage Handover Tasks
        </a>
      </div>
    </div>
  </div>

  <!-- TAB: Clearance -->
  <div class="tab-pane" id="tab-clearance">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3" style="color:#E66136;"><i class="mdi mdi-check-all me-2"></i>Clearance Status</h6>
        <?php if (empty($clearance)): ?>
          <p class="text-muted">Clearance not started yet.</p>
        <?php else: ?>
        <div class="row g-3">
          <?php foreach ($clearance as $c):
            $icon = ['Manager'=>'mdi-account-star','IT'=>'mdi-laptop','Admin'=>'mdi-office-building','Finance'=>'mdi-currency-inr','HR'=>'mdi-account-group'][$c['department']] ?? 'mdi-check';
          ?>
          <div class="col-md-4">
            <div class="p-3 rounded border" style="border-left:4px solid <?= $c['status']==='approved'?'#10B981':($c['status']==='rejected'?'#EF4444':'#F59E0B') ?>!important;">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <div><i class="mdi <?= $icon ?> me-2" style="color:#E66136;"></i><strong><?= $c['department'] ?></strong></div>
                <span class="status-badge s-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
              </div>
              <?php if ($c['approver_name']): ?><small class="text-muted">By: <?= esc($c['approver_name']) ?></small><br><?php endif; ?>
              <?php if ($c['remarks']): ?><small class="text-muted">"<?= esc($c['remarks']) ?>"</small><?php endif; ?>
              <?php if ($c['approved_at']): ?><br><small class="text-muted"><?= $c['approved_at'] ?></small><?php endif; ?>
              <?php if ($c['status'] === 'pending' && in_array($user->role, ['admin','hr','branch_admin','department_manager'])): ?>
              <div class="mt-2 d-flex gap-1">
                <button class="btn btn-xs btn-outline-success clearance-btn" style="font-size:.72rem;padding:2px 8px;" data-id="<?= $c['id'] ?>" data-action="approve">Approve</button>
                <button class="btn btn-xs btn-outline-danger clearance-btn"  style="font-size:.72rem;padding:2px 8px;" data-id="<?= $c['id'] ?>" data-action="reject">Reject</button>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TAB: F&F Settlement -->
  <div class="tab-pane" id="tab-fnf">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0" style="color:#E66136;"><i class="mdi mdi-currency-inr me-2"></i>Full & Final Settlement</h6>
          <?php if ($fnf): ?><span class="status-badge s-<?= $fnf['status'] ?>"><?= ucfirst(str_replace('_',' ',$fnf['status'])) ?></span><?php endif; ?>
        </div>

        <?php if ($resignation['status'] === 'fnf' && in_array($user->role, ['admin','hr'])): ?>
        <!-- F&F Preparation Form -->
        <?php if (!$fnf || $fnf['status'] === 'draft'): ?>
        <form action="<?= base_url('/resignation/hr/fnf/prepare/'.$resignation['id']) ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <h6 class="text-success fw-bold"><i class="mdi mdi-plus-circle me-1"></i>Earnings</h6>
              <div id="earningsContainer">
                <?php
                  $defaultEarnings = [['title'=>'Pending Salary till LWD','amount'=>''],['title'=>'Leave Encashment','amount'=>''],['title'=>'Bonus','amount'=>''],['title'=>'Gratuity (if 5+ yrs)','amount'=>''],['title'=>'Other Earnings','amount'=>'']];
                  $earningItems = (!empty($fnfItems) ? array_filter($fnfItems, fn($i)=>$i['type']==='earning') : $defaultEarnings);
                  foreach ($earningItems as $i => $e):
                ?>
                <div class="d-flex gap-2 mb-2">
                  <input type="text" name="earnings[<?= $i ?>][title]" class="form-control form-control-sm" placeholder="Title" value="<?= esc($e['title'] ?? '') ?>">
                  <input type="number" step="0.01" name="earnings[<?= $i ?>][amount]" class="form-control form-control-sm" placeholder="₹0.00" value="<?= $e['amount'] ?? '' ?>">
                  <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="mdi mdi-close"></i></button>
                </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="btn btn-sm btn-outline-success mt-1 add-row" data-target="earningsContainer" data-type="earnings">+ Add Row</button>
            </div>
            <div class="col-md-6">
              <h6 class="text-danger fw-bold"><i class="mdi mdi-minus-circle me-1"></i>Deductions</h6>
              <div id="deductionsContainer">
                <?php
                  $defaultDeductions = [['title'=>'Notice Period Shortfall Recovery','amount'=>$resignation['notice_shortfall_days']>0 ? '' : ''],['title'=>'Loan / Advance Recovery','amount'=>''],['title'=>'Gadget Damage Cost','amount'=>''],['title'=>'Other Deductions','amount'=>'']];
                  $deductionItems = (!empty($fnfItems) ? array_filter($fnfItems, fn($i)=>$i['type']==='deduction') : $defaultDeductions);
                  $di = 0;
                  foreach ($deductionItems as $d):
                ?>
                <div class="d-flex gap-2 mb-2">
                  <input type="text" name="deductions[<?= $di ?>][title]" class="form-control form-control-sm" placeholder="Title" value="<?= esc($d['title'] ?? '') ?>">
                  <input type="number" step="0.01" name="deductions[<?= $di ?>][amount]" class="form-control form-control-sm" placeholder="₹0.00" value="<?= $d['amount'] ?? '' ?>">
                  <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="mdi mdi-close"></i></button>
                </div>
                <?php $di++; endforeach; ?>
              </div>
              <button type="button" class="btn btn-sm btn-outline-danger mt-1 add-row" data-target="deductionsContainer" data-type="deductions">+ Add Row</button>
            </div>
          </div>
          <div class="border-top mt-3 pt-3">
            <button type="submit" class="btn fw-bold" style="background:#E66136;color:#fff;">
              <i class="mdi mdi-send me-2"></i>Submit F&F to Finance
            </button>
          </div>
        </form>
        <?php endif; ?>
        <?php endif; ?>

        <!-- F&F Summary (when exists) -->
        <?php if ($fnf && $fnf['status'] !== 'draft'): ?>
        <div class="table-responsive mt-3">
          <table class="table table-sm fnf-table">
            <thead class="table-dark"><tr><th>Type</th><th>Item</th><th class="text-end">Amount (₹)</th></tr></thead>
            <tbody>
              <?php foreach ($fnfItems as $item): ?>
              <tr>
                <td><span class="badge <?= $item['type']==='earning'?'bg-success':'bg-danger' ?>"><?= ucfirst($item['type']) ?></span></td>
                <td><?= esc($item['title']) ?></td>
                <td class="text-end"><?= number_format($item['amount'],2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr><td colspan="2" class="text-end text-success">Total Earnings</td><td class="text-end text-success">₹<?= number_format($fnf['total_earnings'],2) ?></td></tr>
              <tr><td colspan="2" class="text-end text-danger">Total Deductions</td><td class="text-end text-danger">₹<?= number_format($fnf['total_deductions'],2) ?></td></tr>
              <tr style="background:#E66136;color:#fff;"><td colspan="2" class="text-end">Net Payable</td><td class="text-end">₹<?= number_format($fnf['net_payable'],2) ?></td></tr>
            </tfoot>
          </table>
        </div>

        <!-- Finance Approve / Mark Paid -->
        <?php if ($fnf['status'] === 'hr_prepared' && in_array($user->role, ['admin','hr'])): ?>
        <button class="btn btn-sm btn-outline-success" onclick="fnfFinanceApprove(<?= $fnf['id'] ?>)">
          <i class="mdi mdi-check-decagram me-1"></i>Finance Approve
        </button>
        <?php endif; ?>
        <?php if ($fnf['status'] === 'finance_approved' && in_array($user->role, ['admin','hr'])): ?>
        <button class="btn btn-sm" style="background:#10B981;color:#fff;" onclick="fnfMarkPaid(<?= $fnf['id'] ?>)">
          <i class="mdi mdi-cash-check me-1"></i>Mark as Paid
        </button>
        <?php endif; ?>
        <?php if ($fnf['status'] === 'paid'): ?>
        <div class="alert alert-success mt-2 mb-0"><i class="mdi mdi-check-all me-2"></i>Paid on <?= $fnf['paid_date'] ?> · Ref: <?= esc($fnf['payment_ref'] ?? 'N/A') ?></div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TAB: Timeline -->
  <div class="tab-pane" id="tab-audit">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold mb-3" style="color:#E66136;"><i class="mdi mdi-timeline me-2"></i>Audit Timeline</h6>
        <?php if (empty($auditLogs)): ?><p class="text-muted">No activity yet.</p>
        <?php else: ?>
        <div class="timeline-wrap">
          <?php foreach ($auditLogs as $log): ?>
          <div class="tl-item">
            <div class="tl-dot"></div>
            <div class="tl-body">
              <div class="fw-semibold text-capitalize"><?= str_replace('_',' ', $log['action']) ?></div>
              <small class="text-muted"><?= esc($log['actor_name'] ?? 'System') ?></small>
              <?php if ($log['remarks']): ?><p class="small mb-0 mt-1"><?= esc($log['remarks']) ?></p><?php endif; ?>
              <?php if ($log['from_status'] && $log['to_status']): ?>
              <small class="text-muted"><?= str_replace('_',' ',$log['from_status']) ?> → <?= str_replace('_',' ',$log['to_status']) ?></small>
              <?php endif; ?>
              <div class="text-muted" style="font-size:.72rem;margin-top:4px;"><?= $log['created_at'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Finance Approve Modal -->
<div class="modal fade" id="financeModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#E66136;"><h5 class="modal-title text-white">Finance Approval</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label fw-semibold">Remarks</label>
      <textarea id="financeRemarks" class="form-control" rows="3"></textarea>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button type="button" id="financeApproveBtn" class="btn fw-bold" style="background:#10B981;color:#fff;">Approve</button>
    </div>
  </div></div>
</div>

<!-- Mark Paid Modal -->
<div class="modal fade" id="paidModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#E66136;"><h5 class="modal-title text-white">Mark F&F as Paid</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label fw-semibold">Payment Date</label><input type="date" id="paidDate" class="form-control" value="<?= date('Y-m-d') ?>"></div>
      <div class="mb-3"><label class="form-label fw-semibold">Payment Reference No.</label><input type="text" id="paymentRef" class="form-control" placeholder="Bank/UPI Ref"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button type="button" id="markPaidBtn" class="btn fw-bold" style="background:#E66136;color:#fff;">Mark as Paid</button>
    </div>
  </div></div>
</div>

<?= $this->section('scripts') ?>
<script>
// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show'));
    this.classList.add('active');
    document.getElementById('tab-' + this.dataset.tab).classList.add('show');
  });
});

// Select tab from URL if present
const urlParams = new URLSearchParams(window.location.search);
const activeTab = urlParams.get('tab');
if (activeTab) {
  const targetBtn = document.querySelector(`.tab-btn[data-tab="${activeTab}"]`);
  if (targetBtn) targetBtn.click();
}

// Add/remove F&F rows
let earningIdx  = <?= count(array_filter($fnfItems ?? [], fn($i) => ($i['type'] ?? '') === 'earning')) ?: 5 ?>;
let deductionIdx= <?= count(array_filter($fnfItems ?? [], fn($i) => ($i['type'] ?? '') === 'deduction')) ?: 4 ?>;

document.querySelectorAll('.add-row').forEach(btn => {
  btn.addEventListener('click', function() {
    const target = document.getElementById(this.dataset.target);
    const type   = this.dataset.type;
    const idx    = type === 'earnings' ? earningIdx++ : deductionIdx++;
    const div    = document.createElement('div');
    div.className = 'd-flex gap-2 mb-2';
    div.innerHTML = `<input type="text" name="${type}[${idx}][title]" class="form-control form-control-sm" placeholder="Title">
                     <input type="number" step="0.01" name="${type}[${idx}][amount]" class="form-control form-control-sm" placeholder="₹0.00">
                     <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="mdi mdi-close"></i></button>`;
    target.appendChild(div);
    div.querySelector('.remove-row').addEventListener('click', () => div.remove());
  });
});
document.querySelectorAll('.remove-row').forEach(btn => btn.addEventListener('click', () => btn.closest('.d-flex').remove()));

// Clearance actions
document.querySelectorAll('.clearance-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const id = this.dataset.id, action = this.dataset.action;
    const remarks = prompt(`Remarks for ${action}:`);
    if (remarks === null) return;
    const fd = new FormData();
    fd.append('action', action); fd.append('remarks', remarks);
    fetch(`<?= base_url('/api/resignation/clearance/') ?>${id}`, { method: 'POST', body: fd })
      .then(r => r.json()).then(res => {
        if (res.status === 'success') location.reload();
        else alert(res.message);
      });
  });
});

// F&F Finance Approve
let currentFnfId = null;
function fnfFinanceApprove(fnfId) { currentFnfId = fnfId; new bootstrap.Modal(document.getElementById('financeModal')).show(); }
document.getElementById('financeApproveBtn')?.addEventListener('click', function() {
  const fd = new FormData();
  fd.append('remarks', document.getElementById('financeRemarks').value);
  fetch(`<?= base_url('/api/resignation/fnf/finance-approve/') ?>${currentFnfId}`, { method: 'POST', body: fd })
    .then(r => r.json()).then(res => { if (res.status === 'success') location.reload(); else alert(res.message); });
});

// F&F Mark Paid
function fnfMarkPaid(fnfId) { currentFnfId = fnfId; new bootstrap.Modal(document.getElementById('paidModal')).show(); }
document.getElementById('markPaidBtn')?.addEventListener('click', function() {
  const fd = new FormData();
  fd.append('paid_date',   document.getElementById('paidDate').value);
  fd.append('payment_ref', document.getElementById('paymentRef').value);
  fetch(`<?= base_url('/api/resignation/fnf/mark-paid/') ?>${currentFnfId}`, { method: 'POST', body: fd })
    .then(r => r.json()).then(res => { if (res.status === 'success') location.reload(); else alert(res.message); });
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
