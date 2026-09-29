<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<style>
.assessment-view-container {
    font-family: "Inter", sans-serif;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}
.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #eee;
    padding-bottom: 20px;
    margin-bottom: 25px;
}
.overall-score-card {
    text-align: center;
    border: 2px solid #e75c25;
    padding: 10px 25px;
    border-radius: 10px;
    background: #fff5f2;
}
.score-value {
    font-size: 28px;
    font-weight: 700;
    color: #e75c25;
}
.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 8px;
}
.info-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #888;
    font-weight: 600;
    margin-bottom: 3px;
}
.info-value {
    font-size: 14px;
    font-weight: 500;
    color: #222;
}
.rating-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
}
.badge-rec {
    font-size: 13px;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-strong-hire { background-color: #28a745; color: #fff; }
.badge-hire { background-color: #17a2b8; color: #fff; }
.badge-hold { background-color: #ffc107; color: #000; }
.badge-reject { background-color: #dc3545; color: #fff; }
</style>

<div class="content-wrapper assessment-view-container">
    <!-- View Header -->
    <div class="view-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="/assessment" class="btn btn-sm text-white fw-bold me-2" style="background-color: #e75c25; border-color: #e75c25;">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
                <h2 class="mb-0" style="font-weight: 700;">Interview Assessment</h2>
            </div>
            <p class="text-muted mb-0 ms-1">
                <?= htmlspecialchars((string)($assessment['candidate_name'] ?? 'Candidate')) ?> &bull; 
                <?= htmlspecialchars((string)($assessment['job_title'] ?? 'N/A')) ?> &bull; 
                <?= htmlspecialchars((string)($assessment['interview_round'] ?? 'N/A')) ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="overall-score-card">
                <div class="score-value"><?= htmlspecialchars((string)($assessment['overall_score'] ?? '0.0')) ?> / 5</div>
                <div class="text-muted" style="font-size: 11px; font-weight: 600;">OVERALL SCORE</div>
            </div>
            <a href="/assessment/edit/<?= $assessment['id'] ?>" class="btn text-white fw-bold px-3 py-2" style="background-color: #e75c25; border-color: #e75c25;">
                <i class="mdi mdi-pencil me-1"></i> Edit Assessment
            </a>
        </div>
    </div>

    <!-- Section 1: Details -->
    <div class="mb-4">
        <div class="section-title">
            <i class="mdi mdi-account-card-details text-primary" style="color: #e75c25 !important;"></i> Interview Details
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="info-label">Candidate Name</div>
                <div class="info-value"><?= htmlspecialchars((string)($assessment['candidate_name'] ?? 'N/A')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Job Title</div>
                <div class="info-value"><?= htmlspecialchars((string)($assessment['job_title'] ?? 'N/A')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Department</div>
                <div class="info-value"><?= htmlspecialchars((string)($assessment['department'] ?? 'N/A')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Interview Round</div>
                <div class="info-value"><?= htmlspecialchars((string)($assessment['interview_round'] ?? 'N/A')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Interviewer</div>
                <div class="info-value"><?= htmlspecialchars((string)($assessment['interviewer_name'] ?? 'N/A')) ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Interview Date & Mode</div>
                <div class="info-value">
                    <?= !empty($assessment['interview_date']) ? date('M d, Y', strtotime($assessment['interview_date'])) : 'N/A' ?>
                    <?php if (!empty($assessment['interview_mode'])): ?>
                        <span class="badge bg-light text-dark ms-2 border"><?= htmlspecialchars($assessment['interview_mode']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Skill Ratings -->
    <div class="mb-4">
        <div class="section-title">
            <i class="mdi mdi-star-half-full" style="color: #e75c25 !important;"></i> Skill Ratings
        </div>
        <?php if (!empty($assessment['ratings_data']) && is_array($assessment['ratings_data'])): ?>
            <div class="row g-3">
                <?php foreach ($assessment['ratings_data'] as $item): ?>
                    <div class="col-md-6">
                        <div class="rating-item border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark"><?= htmlspecialchars((string)($item['title'] ?? 'Criteria')) ?></span>
                                <span class="badge" style="background-color: #e75c25; font-size: 13px;"><?= (int)($item['rating'] ?? 0) ?> / 5</span>
                            </div>
                            <?php if (!empty($item['desc'])): ?>
                                <p class="text-muted mb-2" style="font-size: 12px;"><?= htmlspecialchars((string)$item['desc']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['comment'])): ?>
                                <div class="bg-white p-2 rounded border-start border-3 border-warning" style="font-size: 13px;">
                                    <i class="mdi mdi-comment-text-outline text-muted me-1"></i>
                                    <em><?= htmlspecialchars((string)$item['comment']) ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No skill ratings recorded.</p>
        <?php endif; ?>
    </div>

    <!-- Section 3: Written Feedback -->
    <div class="mb-4">
        <div class="section-title">
            <i class="mdi mdi-comment-text-multiple-outline" style="color: #e75c25 !important;"></i> Written Feedback
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 border rounded bg-light">
                    <div class="info-label text-success mb-2"><i class="mdi mdi-thumb-up me-1"></i> Strengths</div>
                    <div class="info-value" style="white-space: pre-line;">
                        <?= !empty($assessment['feedback']['strengths']) ? htmlspecialchars($assessment['feedback']['strengths']) : '<span class="text-muted">None specified</span>' ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 border rounded bg-light">
                    <div class="info-label text-danger mb-2"><i class="mdi mdi-thumb-down me-1"></i> Weaknesses or Concerns</div>
                    <div class="info-value" style="white-space: pre-line;">
                        <?= !empty($assessment['feedback']['weaknesses']) ? htmlspecialchars($assessment['feedback']['weaknesses']) : '<span class="text-muted">None specified</span>' ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($assessment['feedback']['notes'])): ?>
            <div class="col-12">
                <div class="p-3 border rounded bg-light">
                    <div class="info-label text-info mb-2"><i class="mdi mdi-note-text me-1"></i> Additional Notes</div>
                    <div class="info-value" style="white-space: pre-line;"><?= htmlspecialchars($assessment['feedback']['notes']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 4: Expectations -->
    <div class="mb-4">
        <div class="section-title">
            <i class="mdi mdi-currency-usd" style="color: #e75c25 !important;"></i> Candidate Expectations
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="info-label">Current Salary</div>
                <div class="info-value"><?= !empty($assessment['current_salary']) ? htmlspecialchars((string)$assessment['current_salary']) : 'N/A' ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Expected Salary</div>
                <div class="info-value"><?= !empty($assessment['expected_salary']) ? htmlspecialchars((string)$assessment['expected_salary']) : 'N/A' ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Notice Period</div>
                <div class="info-value"><?= !empty($assessment['notice_period']) ? htmlspecialchars((string)$assessment['notice_period']) . ' days' : 'N/A' ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Available Joining Date</div>
                <div class="info-value"><?= !empty($assessment['joining_date']) ? date('M d, Y', strtotime($assessment['joining_date'])) : 'N/A' ?></div>
            </div>
            <div class="col-md-4">
                <div class="info-label">Probation Period</div>
                <div class="info-value"><?= !empty($assessment['probation_period']) ? htmlspecialchars((string)$assessment['probation_period']) . ' months' : 'N/A' ?></div>
            </div>
        </div>
    </div>

    <!-- Section 5: Decision & Recommendation -->
    <div class="mb-4">
        <div class="section-title">
            <i class="mdi mdi-gavel" style="color: #e75c25 !important;"></i> Decision & Recommendation
        </div>
        <div class="p-3 border rounded" style="background: #fafafa;">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="info-label mb-2">Recommendation</div>
                    <?php 
                    $rec = $assessment['recommendation'] ?? 'N/A';
                    $badgeClass = 'bg-secondary';
                    if ($rec === 'Strong hire') $badgeClass = 'badge-strong-hire';
                    elseif ($rec === 'Hire') $badgeClass = 'badge-hire';
                    elseif ($rec === 'Hold') $badgeClass = 'badge-hold';
                    elseif ($rec === 'Reject') $badgeClass = 'badge-reject';
                    ?>
                    <span class="badge-rec <?= $badgeClass ?>"><?= htmlspecialchars($rec) ?></span>
                </div>
                <div class="col-md-4">
                    <div class="info-label mb-1">Next Step</div>
                    <div class="info-value"><?= htmlspecialchars((string)($assessment['next_step'] ?? 'N/A')) ?></div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="info-label mb-1">Final Remarks</div>
                    <div class="info-value" style="white-space: pre-line;">
                        <?= !empty($assessment['final_remarks']) ? htmlspecialchars($assessment['final_remarks']) : '<span class="text-muted">No final remarks provided.</span>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
