<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.assessment-wizard { font-family: "Inter", sans-serif; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
.wizard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.wizard-tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 20px; }
.wizard-tab { padding: 10px 20px; cursor: pointer; color: #666; font-weight: 500; }
.wizard-tab.active { border-bottom: 2px solid #e75c25; color: #e75c25; margin-bottom: -2px; }
.wizard-card { display: none; }
.wizard-card.active { display: block; }
.rating-btn { width: 40px; height: 40px; border: 1px solid #ccc; border-radius: 4px; background: white; margin-right: 5px; cursor: pointer; }
.rating-btn.active { border-color: #e75c25; color: #e75c25; font-weight: bold; background: #fff5f2; }
.overall-score-box { text-align: center; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; background: #fff; }
.score-number { font-size: 24px; font-weight: bold; }
.rec-btn { background-color: #fff; border: 1px solid #ced4da; color: #495057; padding: 8px 16px; border-radius: 6px; cursor: pointer; transition: all 0.2s ease-in-out; }
.rec-btn:hover { border-color: #aeb5bc; background-color: #f8f9fa; }
.btn-check:checked + .rec-btn { background-color: #fff !important; color: #e75c25 !important; border-color: #e75c25 !important; font-weight: bold; box-shadow: 0 0 0 1px #e75c25; }
</style>

<div class="content-wrapper assessment-wizard">
    <div class="wizard-header">
        <div>
            <h2>Interview assessment</h2>
            <p class="text-muted">Riya Patel - Sales Executive - Technical round</p>
        </div>
        <div class="overall-score-box">
            <div class="score-number" id="overall-score">0.0</div>
            <div class="text-muted" style="font-size: 12px;">Overall score / 5</div>
        </div>
    </div>

    <div class="wizard-tabs">
        <div class="wizard-tab active" data-step="1">Details</div>
        <div class="wizard-tab" data-step="2">Ratings</div>
        <div class="wizard-tab" data-step="3">Feedback</div>
        <div class="wizard-tab" data-step="4">Expectations</div>
        <div class="wizard-tab" data-step="5">Decision</div>
    </div>

    <form method="POST" action="<?= isset($assessment) ? '/assessment/update/' . $assessment['id'] : '/assessment/store' ?>" id="assessmentForm" novalidate>
        <?= csrf_field() ?>
        <!-- Step 1: Details -->
        <div class="wizard-card active" id="step-1">
            <h4>Interview details</h4>
            <p class="text-muted">Enter the details for this interview assessment.</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Candidate</label>
                    <select class="form-select" name="interview_id" id="interviewSelect" onchange="fillInterviewDetails()">
                        <option value="">Select Candidate...</option>
                        <?php if(!empty($interviews)): foreach($interviews as $inv): ?>
                            <option value="<?= $inv['id'] ?>" 
                                data-job="<?= htmlspecialchars((string)($inv['position_applied_for'] ?? '')) ?>"
                                data-dept="<?= htmlspecialchars((string)($inv['department_name'] ?? '')) ?>"
                                data-round="<?= htmlspecialchars((string)($inv['interview_round'] ?? '')) ?>"
                                data-date="<?= htmlspecialchars((string)($inv['interview_date'] ?? '')) ?>"
                                data-interviewer="<?= htmlspecialchars((string)($inv['interviewer_name'] ?? '')) ?>"
                                <?= (isset($assessment) && $assessment['interview_id'] == $inv['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($inv['candidate_name'] ?? $inv['full_name'] ?? 'Unknown Candidate')) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Job title</label>
                    <input type="text" class="form-control" name="job_title" id="jobTitle" placeholder="Enter job title" value="<?= htmlspecialchars((string)($assessment['job_title'] ?? '')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Department</label>
                    <select class="form-select" name="department" id="department">
                        <option value="">Select department...</option>
                        <?php if(isset($departments)): foreach($departments as $dept): ?>
                            <option value="<?= htmlspecialchars((string)($dept['department_name'] ?? '')) ?>"
                                <?= (isset($assessment) && $assessment['department'] == $dept['department_name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($dept['department_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Interview round</label>
                    <input type="text" class="form-control" name="interview_round" id="interviewRound" placeholder="e.g. Technical" value="<?= htmlspecialchars((string)($assessment['interview_round'] ?? '')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Interviewer</label>
                    <input type="text" class="form-control" name="interviewer_name" placeholder="Enter interviewer name" value="<?= htmlspecialchars((string)($assessment['interviewer_name'] ?? '')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Interview date *</label>
                    <input type="date" class="form-control" name="interview_date" id="interviewDate" value="<?= htmlspecialchars((string)($assessment['interview_date'] ?? '')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Interview mode</label>
                    <select class="form-select" name="mode">
                        <option <?= (($assessment['interview_mode'] ?? '') == 'In person') ? 'selected' : '' ?>>In person</option>
                        <option <?= (($assessment['interview_mode'] ?? '') == 'Phone') ? 'selected' : '' ?>>Phone</option>
                        <option <?= (($assessment['interview_mode'] ?? '') == 'Video call') ? 'selected' : '' ?>>Video call</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 2: Ratings -->
        <div class="wizard-card" id="step-2">
            <h4>Skill ratings</h4>
            <p class="text-muted">Rate each area from 1 to 5. Comments are optional.</p>

            <div id="ratings-container">
            <?php 
            // When editing, load saved ratings. Otherwise show default.
            $savedRatings = [];
            if (isset($assessment['ratings_data']) && !empty($assessment['ratings_data'])) {
                $savedRatings = is_array($assessment['ratings_data']) 
                    ? $assessment['ratings_data'] 
                    : json_decode($assessment['ratings_data'], true) ?? [];
            }
            if (empty($savedRatings)) {
                $savedRatings = [['title' => 'Technical / job knowledge', 'desc' => 'Skills needed for the role', 'rating' => 0, 'comment' => '']];
            }
            $i = 0;
            foreach ($savedRatings as $row): $i++;
            ?>
            <div class="row align-items-center border-bottom py-3 rating-row">
                <div class="col-md-4">
                    <input type="text" class="form-control border-0 fw-bold bg-transparent p-0 mb-1" name="criteria_title_<?= $i ?>" value="<?= htmlspecialchars((string)($row['title'] ?? '')) ?>">
                    <input type="text" class="form-control border-0 text-muted bg-transparent p-0" style="font-size: 12px;" name="criteria_desc_<?= $i ?>" value="<?= htmlspecialchars((string)($row['desc'] ?? '')) ?>">
                </div>
                <div class="col-md-4 d-flex">
                    <input type="hidden" name="rating_<?= $i ?>" class="rating-input" value="<?= (int)($row['rating'] ?? 0) ?>">
                    <?php for($r=1; $r<=5; $r++): ?>
                    <button type="button" class="rating-btn <?= ((int)($row['rating'] ?? 0) == $r) ? 'active' : '' ?>" onclick="setRating(this, <?= $i ?>, <?= $r ?>)"><?= $r ?></button>
                    <?php endfor; ?>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <input type="text" class="form-control me-2" name="comment_<?= $i ?>" placeholder="Comment (optional)" value="<?= htmlspecialchars((string)($row['comment'] ?? '')) ?>">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="$(this).closest('.rating-row').remove(); calculateScore();"><i class="mdi mdi-close"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
            
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="addRatingRow()"><i class="mdi mdi-plus"></i> Add new skill</button>
            </div>
            
            <p class="text-muted mt-3" style="font-size:12px;">1 Poor · 2 Below average · 3 Average · 4 Good · 5 Excellent</p>
        </div>

        <!-- Step 3: Feedback -->
        <div class="wizard-card" id="step-3">
            <h4>Written feedback</h4>
            <p class="text-muted">Keep it specific so the next interviewer can build on it.</p>
            <?php 
            // Parse feedback JSON if saved, else empty
            $feedbackData = [];
            if (isset($assessment['feedback']) && !empty($assessment['feedback'])) {
                $decoded = json_decode($assessment['feedback'], true);
                $feedbackData = is_array($decoded) ? $decoded : [];
            }
            ?>
            <div class="mb-3">
                <label>Strengths</label>
                <textarea class="form-control" name="strengths" rows="3" placeholder="What did the candidate do well?"><?= htmlspecialchars((string)($feedbackData['strengths'] ?? '')) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Weaknesses or concerns</label>
                <textarea class="form-control" name="weaknesses" rows="3" placeholder="Anything that worried you?"><?= htmlspecialchars((string)($feedbackData['weaknesses'] ?? '')) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Additional notes</label>
                <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars((string)($feedbackData['notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <!-- Step 4: Expectations -->
        <div class="wizard-card" id="step-4">
            <h4>Candidate expectations</h4>
            <p class="text-muted">Details HR needs for the offer stage.</p>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Current salary (per year)</label>
                    <input type="number" class="form-control" name="current_salary" placeholder="e.g. 4,80,000" value="<?= htmlspecialchars((string)($assessment['current_salary'] ?? '')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Expected salary (per year)</label>
                    <input type="number" class="form-control" name="expected_salary" placeholder="e.g. 6,00,000" value="<?= htmlspecialchars((string)($assessment['expected_salary'] ?? '')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Notice period (days)</label>
                    <input type="number" class="form-control" name="notice_period" placeholder="e.g. 30" value="<?= htmlspecialchars((string)($assessment['notice_period'] ?? '')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Available joining date</label>
                    <input type="date" class="form-control" name="joining_date" value="<?= htmlspecialchars((string)($assessment['joining_date'] ?? '')) ?>">
                </div>
            </div>
        </div>

        <!-- Step 5: Decision -->
        <div class="wizard-card" id="step-5">
            <h4>Decision</h4>
            <p class="text-muted">Your final recommendation for this round.</p>
            <div class="mb-4">
                <label class="mb-2">Recommendation *</label><br>
                <div role="group" aria-label="Recommendation options">
                    <input type="radio" class="btn-check" name="recommendation" id="recStrongHire" autocomplete="off" value="Strong hire" <?= (($assessment['recommendation'] ?? '') == 'Strong hire') ? 'checked' : '' ?>>
                    <label class="rec-btn me-2" for="recStrongHire">Strong hire</label>

                    <input type="radio" class="btn-check" name="recommendation" id="recHire" autocomplete="off" value="Hire" <?= (($assessment['recommendation'] ?? '') == 'Hire') ? 'checked' : '' ?>>
                    <label class="rec-btn me-2" for="recHire">Hire</label>

                    <input type="radio" class="btn-check" name="recommendation" id="recHold" autocomplete="off" value="Hold" <?= (($assessment['recommendation'] ?? '') == 'Hold') ? 'checked' : '' ?>>
                    <label class="rec-btn me-2" for="recHold">Hold</label>

                    <input type="radio" class="btn-check" name="recommendation" id="recReject" autocomplete="off" value="Reject" <?= (($assessment['recommendation'] ?? '') == 'Reject') ? 'checked' : '' ?>>
                    <label class="rec-btn" for="recReject">Reject</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Next step</label>
                    <select class="form-select" name="next_step" id="next_step">
                        <option value="">Select</option>
                        <option <?= (($assessment['next_step'] ?? '') == 'Move to next round') ? 'selected' : '' ?>>Move to next round</option>
                        <option <?= (($assessment['next_step'] ?? '') == 'Send offer') ? 'selected' : '' ?>>Send offer</option>
                        <option <?= (($assessment['next_step'] ?? '') == 'Keep in talent pool') ? 'selected' : '' ?>>Keep in talent pool</option>
                        <option <?= (($assessment['next_step'] ?? '') == 'Close') ? 'selected' : '' ?>>Close</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="next_round_date_box" style="display:none;">
                    <label>Next round date</label>
                    <input type="date" class="form-control" name="next_round_date">
                </div>
            </div>
            <div class="mb-3">
                <label>Final remarks</label>
                <textarea class="form-control" name="final_remarks" rows="3"><?= htmlspecialchars((string)($assessment['final_remarks'] ?? '')) ?></textarea>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="d-flex justify-content-between mt-4 border-top pt-3">
            <input type="hidden" name="overall_score" id="overall_score_input">
            <button type="button" class="btn btn-sm btn-secondary" onclick="prevStep()">Previous</button>
            <div>
                <button type="button" class="btn btn-sm btn-primary" style="background-color:#e75c25; border-color:#e75c25;" onclick="nextStep()" id="nextBtn">Next</button>
                <button type="submit" class="btn btn-sm btn-primary" style="background-color:#e75c25; border-color:#e75c25; display:none;" id="submitBtn">Submit Assessment</button>
            </div>
        </div>

    </form>
</div>

<script>
let currentStep = 1;
const totalSteps = 5;

$('.wizard-tab').click(function(){
    const step = $(this).data('step');
    goToStep(step);
});

function goToStep(step) {
    $('.wizard-tab').removeClass('active');
    $('.wizard-card').removeClass('active');
    
    $('.wizard-tab[data-step="'+step+'"]').addClass('active');
    $('#step-'+step).addClass('active');
    
    currentStep = step;
    
    if(step === totalSteps) {
        $('#nextBtn').hide();
        $('#submitBtn').show();
    } else {
        $('#nextBtn').show();
        $('#submitBtn').hide();
    }
}

function nextStep() {
    if(currentStep < totalSteps) goToStep(currentStep + 1);
}

function prevStep() {
    if(currentStep > 1) goToStep(currentStep - 1);
}

function setRating(btn, questionId, ratingValue) {
    // Remove active from siblings
    $(btn).siblings().removeClass("active");
    $(btn).addClass("active");
    // Set hidden input
    $(btn).siblings(".rating-input").val(ratingValue);
    
    calculateScore();
}

function calculateScore() {
    let total = 0;
    let count = 0;
    $('.rating-input').each(function() {
        let val = parseInt($(this).val());
        if(val > 0) {
            total += val;
            count++;
        }
    });
    
    let avg = count > 0 ? (total / count).toFixed(1) : "0.0";
    $('#overall-score').text(avg);
    $('#overall_score_input').val(avg);
}

$('#next_step').change(function(){
    if($(this).val() === "Move to next round") {
        $('#next_round_date_box').show();
    } else {
        $('#next_round_date_box').hide();
    }
});

let rowCount = <?= $i ?>;
function addRatingRow() {
    rowCount++;
    let html = `
    <div class="row align-items-center border-bottom py-3 rating-row">
        <div class="col-md-4">
            <input type="text" class="form-control border-0 fw-bold bg-transparent p-0 mb-1" name="criteria_title_${rowCount}" placeholder="Enter Skill Name">
            <input type="text" class="form-control border-0 text-muted bg-transparent p-0" style="font-size: 12px;" name="criteria_desc_${rowCount}" placeholder="Description (optional)">
        </div>
        <div class="col-md-4 d-flex">
            <input type="hidden" name="rating_${rowCount}" class="rating-input" value="0">
            <button type="button" class="rating-btn" onclick="setRating(this, ${rowCount}, 1)">1</button>
            <button type="button" class="rating-btn" onclick="setRating(this, ${rowCount}, 2)">2</button>
            <button type="button" class="rating-btn" onclick="setRating(this, ${rowCount}, 3)">3</button>
            <button type="button" class="rating-btn" onclick="setRating(this, ${rowCount}, 4)">4</button>
            <button type="button" class="rating-btn" onclick="setRating(this, ${rowCount}, 5)">5</button>
        </div>
        <div class="col-md-4 d-flex align-items-center">
            <input type="text" class="form-control me-2" name="comment_${rowCount}" placeholder="Comment (optional)">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="$(this).closest('.rating-row').remove(); calculateScore();"><i class="mdi mdi-close"></i></button>
        </div>
    </div>
    `;
    $('#ratings-container').append(html);
}

function fillInterviewDetails() {
    var selected = $('#interviewSelect').find('option:selected');
    if(selected.val()) {
        $('#jobTitle').val(selected.data('job'));
        $('#department').val(selected.data('dept'));
        $('#interviewRound').val(selected.data('round'));
        $('#interviewDate').val(selected.data('date'));
        $('input[name="interviewer_name"]').val(selected.data('interviewer'));
    } else {
        $('#jobTitle').val('');
        $('#department').val('');
        $('#interviewRound').val('');
        $('#interviewDate').val('');
        $('input[name="interviewer_name"]').val('');
    }
}
</script>
<?= $this->endSection() ?>
