<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    .wizard-container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
    }
    .wizard-header {
        margin-bottom: 20px;
    }
    .wizard-header h3 {
        font-size: 24px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 5px;
    }
    .wizard-header p {
        font-size: 14px;
        color: #6b7280;
    }
    .wizard-tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 25px;
        overflow-x: auto;
    }
    .wizard-tab {
        flex: 1;
        text-align: left;
        padding: 12px 10px;
        color: #6b7280;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    .wizard-tab.active {
        color: #111827;
        border-bottom: 3px solid #E66136;
    }
    .wizard-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 24px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .wizard-card h4 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #111827;
    }
    .wizard-card p.subtitle {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 24px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #d1d5db;
        padding: 10px 12px;
        font-size: 14px;
        color: #111827;
    }
    .form-control:focus, .form-select:focus {
        border-color: #E66136;
        box-shadow: 0 0 0 2px rgba(230, 97, 54, 0.2);
    }
    .step-pane {
        display: none;
    }
    .step-pane.active {
        display: block;
    }
    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .wizard-footer {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    #submitBtn { display: none; }
    #prevBtn   { display: none; }
    
    .custom-orange-switch:focus {
        border-color: #E66136 !important;
        box-shadow: 0 0 0 2px rgba(230, 97, 54, 0.2) !important;
    }
    .custom-orange-switch:checked {
        background-color: #E66136 !important;
        border-color: #E66136 !important;
    }
</style>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="wizard-container">
            <div class="wizard-header">
                <h3 id="page-main-title">Add candidate</h3>
                <p>Fill in each step. Your progress is kept as you move between steps.</p>
            </div>
            
            <div class="wizard-tabs">
                <div class="wizard-tab active" data-step="1">Candidate</div>
                <div class="wizard-tab" data-step="2">Job</div>
                <div class="wizard-tab" data-step="3">Education</div>
                <div class="wizard-tab" data-step="4">Experience</div>
                <div class="wizard-tab" data-step="5">Skills</div>
                <div class="wizard-tab" data-step="6">Interview</div>
            </div>

            <form class="form-sample" method="POST" action="" id="interviewForm" novalidate>
                <input type="hidden" id="id" name="id" value="">
                <input type="hidden" id="candidate_id" name="candidate_id" value="">
                <input type="hidden" id="job_id" name="job_id" value="">
                
                <!-- Step 1: Candidate -->
                <div class="step-pane active w-100" id="step-1">
                    <div class="wizard-card w-100">
                        <h4>Candidate details</h4>
                        <p class="subtitle">Who is applying, and how can we reach them?</p>
                        
                        <div class="row" id="candidate-autofill-row">
                            <div class="col-md-12 mb-4 pb-2 border-bottom">
                                <label class="form-label fw-bold text-primary">Select Existing Candidate (Auto-fill)</label>
                                <select class="form-select border-primary" id="auto_fill_candidate">
                                    <option value="">-- Manual Entry --</option>
                                    <?php if(isset($candidates)): foreach ($candidates as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= $c['candidate_name'] ?> (<?= $c['email'] ?>)</option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full name *</label>
                            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="e.g. John Doe" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="e.g. email@example.com" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="text" class="form-control" name="mobile_number" id="mobile_number" placeholder="e.g. 07990181591" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of birth</label>
                            <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" placeholder="Date of birth" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" id="gender">
                                <option value="" disabled selected>Select</option>
                                <option value="Male">Male</option>
<option value="Female">Female</option>
<option value="Other">Other</option>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" id="city" placeholder="City" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="current_address" id="current_address" placeholder="Enter full address" rows="3"></textarea>
                        </div>

                        </div>
                    </div>
                </div>

                <!-- Step 2: Job -->
                <div class="step-pane w-100" id="step-2">
                    <div class="wizard-card w-100">
                        <h4>Job details</h4>
                        <p class="subtitle">Which role is this candidate interviewing for?</p>
                        <div class="row" id="job-autofill-row">
                            <div class="col-md-12 mb-4 pb-2 border-bottom">
                                <label class="form-label fw-bold text-primary">Select Existing Job (Auto-fill)</label>
                                <select class="form-select border-primary" id="auto_fill_job">
                                    <option value="">-- Manual Entry --</option>
                                    <?php if(isset($jobs)): foreach ($jobs as $j): ?>
                                        <option value="<?= $j['id'] ?>"><?= $j['job_title'] ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job title *</label>
                            <input type="text" class="form-control" name="job_title" id="job_title" placeholder="e.g. Account manager" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department_id" id="department_id" placeholder="e.g. Finance" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job type</label>
                            <select class="form-select" name="job_type" id="job_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Full-time">Full-time</option>
<option value="Part-time">Part-time</option>
<option value="Contract">Contract</option>
<option value="Internship">Internship</option>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position Applied For</label>
                            <input type="text" class="form-control" name="position_applied_for" id="position_applied_for" placeholder="e.g. Senior Dev" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience needed</label>
                            <input type="text" class="form-control" name="experience_type" id="experience_type" placeholder="e.g. 3 years" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary range</label>
                            <input type="text" class="form-control" name="expected_salary" id="expected_salary" placeholder="e.g. 4 to 6 lakh" />
                        </div>

                        </div>
                    </div>
                </div>

                <!-- Step 3: Education -->
                <div class="step-pane w-100" id="step-3">
                    <div class="wizard-card w-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h4>Education</h4>
                                <p class="subtitle mb-0">Start with the highest qualification.</p>
                            </div>
                            <button type="button" class="btn btn-sm text-white" style="background:#E66136;" id="addEduBtn">
                                <i class="mdi mdi-plus"></i> Add More
                            </button>
                        </div>
                        
                        <div id="eduContainer" class="mt-3 w-100">
                            <div class="edu-block mb-4 position-relative w-100">
                                <h6 class="fw-bold text-primary mb-3 edu-heading">Education #1</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Degree</label>
                                        <input type="text" class="form-control req-edu-degree" name="education[0][degree]" placeholder="e.g. B.Tech, MBA" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Course / Specialization</label>
                                        <input type="text" class="form-control" name="education[0][course]" placeholder="e.g. Computer Science" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">University / school</label>
                                        <input type="text" class="form-control" name="education[0][university]" placeholder="Institution name" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Passing year</label>
                                        <input type="number" class="form-control" name="education[0][passing_year]" placeholder="e.g. 2022" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Percentage / CGPA</label>
                                        <input type="text" class="form-control" name="education[0][percentage]" placeholder="e.g. 8.2 CGPA" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Experience -->
                <div class="step-pane w-100" id="step-4">
                    <div class="wizard-card w-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h4>Work experience</h4>
                                <p class="subtitle mb-0">Add the most recent job first. Skip if this is a fresher.</p>
                            </div>
                            <button type="button" class="btn btn-sm text-white" style="background:#E66136;" id="addExpBtn">
                                <i class="mdi mdi-plus"></i> Add More
                            </button>
                        </div>

                        <div id="expContainer" class="mt-3 w-100">
                            <div class="exp-block mb-4 position-relative w-100">
                                <h6 class="fw-bold text-primary mb-3 exp-heading">Experience #1</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company</label>
                                        <input type="text" class="form-control" name="experience[0][company]" placeholder="Company name" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" name="experience[0][role]" placeholder="e.g. Sales executive" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Total Experience</label>
                                        <input type="text" class="form-control" name="experience[0][total_experience]" placeholder="e.g. 2 Years 3 Months" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last salary (per year)</label>
                                        <input type="text" class="form-control" name="experience[0][last_salary]" placeholder="e.g. 4,80,000" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Notice Period</label>
                                        <input type="text" class="form-control" name="experience[0][notice_period]" placeholder="e.g. 30 Days" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Reason for leaving</label>
                                        <textarea class="form-control" name="experience[0][reason_for_leaving]" placeholder="Reason for leaving" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Skills -->
                <div class="step-pane w-100" id="step-5">
                    <div class="wizard-card w-100">
                        <h4>Skills and evaluation</h4>
                        <p class="subtitle">Enter candidate skills and your assessment.</p>
                        <div class="row">

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Technical Skills</label>
                            <input type="text" class="form-control" name="technical_skills" id="technical_skills" placeholder="Type skills separated by comma" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Communication Skills</label>
                            <input type="text" class="form-control" name="communication_skills" id="communication_skills" placeholder="e.g. Fluent, Good, Average" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Computer Skills</label>
                            <input type="text" class="form-control" name="computer_skills" id="computer_skills" placeholder="e.g. MS Office, Tally" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Key Strengths</label>
                            <textarea class="form-control" name="key_strengths" id="key_strengths" placeholder="Key Strengths" rows="3"></textarea>
                        </div>

                        </div>
                    </div>
                </div>

                <!-- Step 6: Interview & Selection -->
                <div class="step-pane w-100" id="step-6">
                    <div class="wizard-card w-100">
                        <h4>Interview & Selection Details</h4>
                        <p class="subtitle">Schedule and evaluate the interview.</p>
                        <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Date</label>
                            <input type="date" class="form-control" name="interview_date" id="interview_date" placeholder="Interview Date" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Time</label>
                            <input type="time" class="form-control" name="interview_time" id="interview_time" placeholder="Interview Time" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Interview Type</label>
                            <select class="form-select" name="interview_type" id="interview_type">
                                <option value="" disabled selected>Select</option>
                                <option value="In-Person">In-Person</option>
<option value="Video Call">Video Call</option>
<option value="Phone">Phone</option>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location/Link</label>
                            <input type="text" class="form-control" name="location" id="location" placeholder="Location/Link" />
                        </div>

                        <div class="col-12 mb-4 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <h6 class="fw-bold text-primary mb-0">Interview Rounds</h6>
                                <button type="button" class="btn btn-sm text-white" style="background:#E66136;" id="addRoundBtn">
                                    <i class="mdi mdi-plus"></i> Add More
                                </button>
                            </div>
                            <div id="roundContainer" class="w-100">
                                <div class="round-block position-relative mb-4 w-100 border-bottom pb-3">
                                    <h6 class="fw-bold text-primary mb-3 mt-2 round-heading">Round #1</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Interviewer *</label>
                                            <select class="form-select req-interviewer" name="rounds[0][interviewer_id]">
                                                <option value="" disabled selected>Select Interviewer</option>
                                                <?php foreach ($interviewers as $interviewer): ?>
                                                    <option value="<?= $interviewer['id'] ?>"><?= htmlspecialchars($interviewer['username'] ?? $interviewer['first_name'] . ' ' . $interviewer['last_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Interview Round</label>
                                            <input type="text" class="form-control" name="rounds[0][interview_round]" placeholder="Interview Round" />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Interview Score (Out of 10)</label>
                                            <input type="number" class="form-control round-score" name="rounds[0][interview_score]" placeholder="Score" max="10" min="0" />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Interview Status</label>
                                            <select class="form-select round-status" name="rounds[0][interview_status]">
                                                <option value="" disabled selected>Select</option>
                                                <option value="Scheduled">Scheduled</option>
                                                <option value="In Progress">In Progress</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-center mt-2 border-top pt-3">
                                <div class="col-md-6 text-md-end fw-bold">
                                    Total Score:
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control fw-bold text-primary bg-light" name="total_score" id="total_score_display" readonly value="0">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selection Status</label>
                            <select class="form-select" name="selection_status" id="selection_status">
                                <option value="" disabled selected>Select</option>
                                <option value="Selected">Selected</option>
<option value="Rejected">Rejected</option>
<option value="On Hold">On Hold</option>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Offered Salary</label>
                            <input type="text" class="form-control" name="offered_salary" id="offered_salary" placeholder="Offered Salary" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date</label>
                            <input type="date" class="form-control" name="joining_date" id="joining_date" placeholder="Joining Date" />
                        </div>
                        
                        <div class="col-md-6 mb-3 mt-3 mt-md-0">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch d-flex align-items-center gap-2" style="min-height:38px; padding-left: 2.5em;">
                                <input class="form-check-input m-0 custom-orange-switch" type="checkbox" role="switch" id="convert_to_employee" name="convert_to_employee" value="1" style="transform: scale(1.2);">
                                <label class="form-check-label fw-semibold m-0" for="convert_to_employee" style="color:#E66136;">Convert to Employee Directly</label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">HR Remarks</label>
                            <textarea class="form-control" name="hr_remarks" id="hr_remarks" placeholder="HR Remarks" rows="3"></textarea>
                        </div>

                        </div>
                    </div>
                </div>

                <!-- Hidden fields for Convert to Employee -->
                <input type="hidden" name="branch_id" id="form_branch_id">
                <input type="hidden" name="department_id" id="form_department_id">

                <!-- Footer Buttons: all in one row -->
                <div class="wizard-footer">
                    <div>
                        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url("/addinterview") ?>" class="btn btn-light" id="cancelBtn">Cancel</a>
                        <button type="button" class="btn hr-btnbg" id="nextBtn" onclick="nextPrev(1)">Next</button>
                        <button type="submit" class="btn hr-btnbg" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner" role="status" aria-hidden="true"></span>Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Convert To Employee Modal -->
<div class="modal fade" id="convertToEmployeeModal" tabindex="-1" aria-labelledby="convertToEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convertToEmployeeModalLabel">Convert to Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Branch *</label>
                    <select class="form-select shadow-none" id="modal_branch_id" required>
                        <option value="">Select Branch</option>
                        <?php if(!empty($branches)): foreach ($branches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department *</label>
                    <select class="form-select shadow-none" id="modal_department_id" required>
                        <option value="">Select Department</option>
                        <?php if(!empty($departments)): foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn hr-btnbg text-white" id="confirmConvertBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentTab = 1;
    const totalTabs = 6;

    // Step validation rules
    const stepRequirements = {
        1: [
            { id: 'full_name', message: 'Full name is required' },
            { id: 'email', message: 'Email is required', type: 'email' },
            { id: 'mobile_number', message: 'Phone number is required', pattern: /^\d{10}$/, patternMessage: 'Phone must be 10 digits' }
        ],
        2: [
            { id: 'job_title', message: 'Job title is required' }
        ],
        3: [
            // { id: 'highest_qualification', message: 'Degree is required' }
        ],
        4: [],
        5: [],
        6: []
    };

    function showError(field, message) {
        field.addClass('is-invalid');
        if (field.next('.invalid-feedback').length === 0) {
            field.after('<div class="invalid-feedback">' + message + '</div>');
        } else {
            field.next('.invalid-feedback').text(message);
        }
    }

    function clearError() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    }

    $('input, select, textarea').on('input change', clearError);

    function validateStep(step) {
        let isValid = true;
        const reqs = stepRequirements[step] || [];

        reqs.forEach(function(req) {
            const field = $('#' + req.id);
            const val = field.val();
            let hasError = false;
            let errorMsg = '';

            if (!val || val.trim() === '') {
                hasError = true;
                errorMsg = req.message;
            } else if (req.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                hasError = true;
                errorMsg = 'Please enter a valid email address';
            } else if (req.pattern && !req.pattern.test(val)) {
                hasError = true;
                errorMsg = req.patternMessage || 'Invalid format';
            }

            if (hasError) {
                isValid = false;
                showError(field, errorMsg);
            }
        });

        // Dynamic validation for Step 6 rounds
        if (step === 6) {
            $('.req-interviewer').each(function() {
                const val = $(this).val();
                if (!val || val.trim() === '') {
                    isValid = false;
                    showError($(this), 'Interviewer is required');
                }
            });
        }

        if (!isValid) {
            const firstErr = $('#step-' + step + ' .is-invalid').first();
            if (firstErr.length) {
                $('html, body').animate({ scrollTop: firstErr.offset().top - 100 }, 200);
                firstErr.focus();
            }
        }
        return isValid;
    }

    function forceHide(el)  { el[0].style.setProperty('display', 'none',  'important'); }
    function forceShow(el)  { el[0].style.setProperty('display', 'inline-block', 'important'); }

    function showTab(n) {
        n = parseInt(n);

        // Show only the active pane
        $('.step-pane').each(function() { forceHide($(this)); });
        forceShow($('#step-' + n));

        // Active tab highlight
        $('.wizard-tab').removeClass('active');
        $('.wizard-tab[data-step="' + n + '"]').addClass('active');

        // Previous button
        if (n == 1) { forceHide($('#prevBtn')); }
        else        { forceShow($('#prevBtn')); }

        // Next / Submit
        if (n == totalTabs) {
            forceHide($('#nextBtn'));
            forceShow($('#submitBtn'));
        } else {
            forceShow($('#nextBtn'));
            forceHide($('#submitBtn'));
        }
    }

    // Expose nextPrev globally for onclick attributes
    window.nextPrev = function(n) {
        if (n === 1 && !validateStep(currentTab)) {
            return false;
        }
        currentTab += n;
        if (currentTab < 1) currentTab = 1;
        if (currentTab > totalTabs) currentTab = totalTabs;
        showTab(currentTab);
    };

    // Tab click handler
    $('.wizard-tab').on('click', function() {
        const targetStep = parseInt($(this).data('step'));

        // Go back freely
        if (targetStep < currentTab) {
            currentTab = targetStep;
            showTab(currentTab);
            return;
        }

        // Going forward: validate all steps in between
        for (let i = currentTab; i < targetStep; i++) {
            if (!validateStep(i)) {
                currentTab = i;
                showTab(currentTab);
                return;
            }
        }
        currentTab = targetStep;
        showTab(currentTab);
    });

    // Initial load
    showTab(currentTab);

    // Edit mode: detect ID in URL
    const token = localStorage.getItem('token');
    let isEditMode = false;
    let interviewId = null;

    const params = new URLSearchParams(window.location.search);
    let id = params.get('id');
    if (!id) {
        const pathParts = window.location.pathname.split('/');
        const last = pathParts[pathParts.length - 1];
        if (last && !isNaN(last)) id = last;
    }

    if (id) {
        isEditMode = true;
        interviewId = id;
        $('#submitBtn').html('<span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner" role="status" aria-hidden="true"></span>Update');
        $('.card-title.fw-bold').text('Edit candidate');
        $('#page-main-title').text('Edit candidate');
        $('#candidate-autofill-row').hide();
        $('#job-autofill-row').hide();

        $.ajax({
            url: '/api/interviews/' + id,
            type: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    
                    if (data.candidate_id) {
                        $('#candidate_id').val(data.candidate_id);
                        $('#auto_fill_candidate').val(data.candidate_id);
                    }
                    if (data.job_id) {
                        $('#job_id').val(data.job_id);
                        $('#auto_fill_job').val(data.job_id);
                    }
                    if (data.position_applied_for) {
                        $('#job_title').val(data.position_applied_for);
                    }

                    for (const key in data) {
                        if (key === 'educations' || key === 'experiences' || key === 'rounds') continue;
                        if ($('#' + key).length) {
                            if ($('#' + key).attr('type') === 'checkbox') {
                                $('#' + key).prop('checked', data[key] == 1 || data[key] === '1');
                            } else {
                                $('#' + key).val(data[key]);
                            }
                        }
                    }

                    if (data.educations && data.educations.length > 0) {
                        for(let i=1; i<data.educations.length; i++) { $('#addEduBtn').click(); }
                        data.educations.forEach((edu, i) => {
                            $(`[name="education[${i}][degree]"]`).val(edu.degree);
                            $(`[name="education[${i}][course]"]`).val(edu.course);
                            $(`[name="education[${i}][university]"]`).val(edu.university);
                            $(`[name="education[${i}][passing_year]"]`).val(edu.passing_year);
                            $(`[name="education[${i}][percentage]"]`).val(edu.percentage);
                        });
                    }

                    if (data.experiences && data.experiences.length > 0) {
                        for(let i=1; i<data.experiences.length; i++) { $('#addExpBtn').click(); }
                        data.experiences.forEach((exp, i) => {
                            $(`[name="experience[${i}][company]"]`).val(exp.company);
                            $(`[name="experience[${i}][role]"]`).val(exp.role);
                            $(`[name="experience[${i}][total_experience]"]`).val(exp.total_experience);
                            $(`[name="experience[${i}][last_salary]"]`).val(exp.last_salary);
                            $(`[name="experience[${i}][notice_period]"]`).val(exp.notice_period);
                            $(`[name="experience[${i}][reason_for_leaving]"]`).val(exp.reason_for_leaving);
                        });
                    }

                    if (data.rounds && data.rounds.length > 0) {
                        for(let i=1; i<data.rounds.length; i++) { $('#addRoundBtn').click(); }
                        data.rounds.forEach((round, i) => {
                            $(`[name="rounds[${i}][interviewer_id]"]`).val(round.interviewer_id);
                            $(`[name="rounds[${i}][interview_round]"]`).val(round.interview_round);
                            $(`[name="rounds[${i}][interview_score]"]`).val(round.interview_score);
                            $(`[name="rounds[${i}][interview_status]"]`).val(round.interview_status);
                        });
                        calculateTotalScore();
                    }
                }
            }
        });
    }

    // Form submit
    $('#interviewForm').on('submit', function(e) {
        e.preventDefault();

        // Validate all steps
        let firstInvalidStep = null;
        for (let i = 1; i <= totalTabs; i++) {
            if (!validateStep(i)) {
                if (firstInvalidStep === null) firstInvalidStep = i;
            }
        }

        if (firstInvalidStep !== null) {
            currentTab = firstInvalidStep;
            showTab(currentTab);
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields.',
                customClass: { confirmButton: 'hr-btnbg' }
            });
            return false;
        }

        $('#submitBtn').attr('disabled', true);
        $('#submitSpinner').removeClass('d-none');

        const formData = new FormData(this);
        const csrfName = $('meta[name="csrf-token"]').attr('data-name');
        const csrfHash = $('meta[name="csrf-token"]').attr('content');
        if (csrfName && csrfHash) formData.append(csrfName, csrfHash);

        $('.invalid-feedback').remove();
        $('.is-invalid').removeClass('is-invalid');

        const url = isEditMode ? '/api/interviews/' + interviewId : '/api/interviews';

        $.ajax({
            url: url,
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Saved successfully',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { confirmButton: 'hr-btnbg' }
                }).then(function() {
                    window.location.href = '/addinterview';
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    let firstErrorTab = null;
                    for (const field in response.errors) {
                        const el = $('#' + field);
                        el.addClass('is-invalid');
                        el.after('<div class="invalid-feedback">' + response.errors[field] + '</div>');
                        if (firstErrorTab === null) {
                            const pane = el.closest('.step-pane');
                            if (pane.length) {
                                firstErrorTab = parseInt(pane.attr('id').replace('step-', ''));
                            }
                        }
                    }
                    if (firstErrorTab !== null) {
                        currentTab = firstErrorTab;
                        showTab(currentTab);
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fix the highlighted errors.',
                        customClass: { confirmButton: 'hr-btnbg' }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: (response && response.message) ? response.message : 'Something went wrong!',
                        customClass: { confirmButton: 'hr-btnbg' }
                    });
                }
            },
            complete: function() {
                $('#submitSpinner').addClass('d-none');
                $('#submitBtn').attr('disabled', false);
            }
        });
    });

    // Handle convert switch
    $('#convert_to_employee').on('change', function() {
        if ($(this).is(':checked')) {
            $('#convertToEmployeeModal').modal('show');
        } else {
            $('#form_branch_id').val('');
            $('#form_department_id').val('');
        }
    });

    // Handle modal confirmation
    $('#confirmConvertBtn').on('click', function() {
        const branch = $('#modal_branch_id').val();
        const dept = $('#modal_department_id').val();
        if (!branch || !dept) {
            Swal.fire('Error', 'Please select both Branch and Department.', 'error');
            return;
        }
        $('#form_branch_id').val(branch);
        $('#form_department_id').val(dept);
        $('#convertToEmployeeModal').modal('hide');
        
        Swal.fire({
            icon: 'success',
            title: 'Set to Convert',
            text: 'Employee will be converted upon form submission.',
            timer: 2000,
            showConfirmButton: false,
            customClass: { confirmButton: 'hr-btnbg' }
        });
    });

    // Handle modal cancel (uncheck switch if they don't confirm)
    $('#convertToEmployeeModal').on('hidden.bs.modal', function () {
        if (!$('#form_branch_id').val() || !$('#form_department_id').val()) {
            $('#convert_to_employee').prop('checked', false);
        }
    });


    // Auto-fill Logic
    const candidatesData = <?= isset($candidates) ? json_encode($candidates) : '[]' ?>;
    const jobsData = <?= isset($jobs) ? json_encode($jobs) : '[]' ?>;
    const departmentsData = <?= isset($departments) ? json_encode($departments) : '[]' ?>;

    $('#auto_fill_candidate').on('change', function() {
        const id = $(this).val();
        $('#candidate_id').val(id);
        if (!id) {
            $('#full_name').val('');
            $('#email').val('');
            $('#mobile_number').val('');
            $('#date_of_birth').val('');
            $('#gender').val('');
            $('#city').val('');
            $('#current_address').val('');
            return;
        }
        const candidate = candidatesData.find(c => c.id == id);
        if (candidate) {
            $('#full_name').val(candidate.candidate_name);
            $('#email').val(candidate.email);
            $('#mobile_number').val(candidate.phone_number);
            $('#date_of_birth').val(candidate.date_of_birth || candidate.dob || '');
            $('#gender').val(candidate.gender || '');
            $('#city').val(candidate.city || '');
            $('#current_address').val(candidate.current_address || candidate.address || '');
            
            // Auto-select the associated job if available
            if (candidate.job_id) {
                $('#auto_fill_job').val(candidate.job_id).trigger('change');
            }
        }
    });

    $('#auto_fill_job').on('change', function() {
        const id = $(this).val();
        $('#job_id').val(id);
        if (!id) {
            $('#job_title').val('');
            $('#department_id').val('');
            $('#job_type').val('');
            $('#experience_type').val('');
            $('#expected_salary').val('');
            $('#position_applied_for').val('');
            return;
        }
        const job = jobsData.find(j => j.id == id);
        if (job) {
            $('#job_title').val(job.job_title);
            let dept = departmentsData.find(d => d.id == job.department_id);
            $('#department_id').val(dept ? dept.department_name : job.department_id);
            
            let jobTypeVal = job.job_type === 'full' ? 'Full-time' : (job.job_type === 'part' ? 'Part-time' : job.job_type);
            $('#job_type').val(jobTypeVal).trigger('change');
            
            $('#position_applied_for').val(job.job_title);
            $('#experience_type').val(job.experience ? job.experience + ' years' : '');
            $('#expected_salary').val(job.salary_range);
        }
    });

    // Add More Education Logic
    $('#addEduBtn').on('click', function() {
        if ($('.edu-block').length >= 10) return;
        const newBlock = $('.edu-block').first().clone();
        newBlock.find('input').val('');
        newBlock.find('.is-invalid').removeClass('is-invalid');
        newBlock.find('.invalid-feedback').remove();
        
        const removeBtnHtml = '<button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-3 remove-edu-btn"><i class="mdi mdi-delete"></i></button>';
        if (newBlock.find('.remove-edu-btn').length === 0) {
            newBlock.append(removeBtnHtml);
        }
        $('#eduContainer').append(newBlock);
        reindexBlocks('.edu-block', 'education');
        if ($('.edu-block').length >= 10) $(this).prop('disabled', true);
    });

    $(document).on('click', '.remove-edu-btn', function() {
        $(this).closest('.edu-block').remove();
        reindexBlocks('.edu-block', 'education');
        $('#addEduBtn').prop('disabled', false);
    });

    // Add More Experience Logic
    $('#addExpBtn').on('click', function() {
        if ($('.exp-block').length >= 10) return;
        const newBlock = $('.exp-block').first().clone();
        newBlock.find('input, textarea').val('');
        newBlock.find('.is-invalid').removeClass('is-invalid');
        newBlock.find('.invalid-feedback').remove();
        
        const removeBtnHtml = '<button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-3 remove-exp-btn"><i class="mdi mdi-delete"></i></button>';
        if (newBlock.find('.remove-exp-btn').length === 0) {
            newBlock.append(removeBtnHtml);
        }
        $('#expContainer').append(newBlock);
        reindexBlocks('.exp-block', 'experience');
        if ($('.exp-block').length >= 10) $(this).prop('disabled', true);
    });

    $(document).on('click', '.remove-exp-btn', function() {
        $(this).closest('.exp-block').remove();
        reindexBlocks('.exp-block', 'experience');
        $('#addExpBtn').prop('disabled', false);
    });

    function reindexBlocks(selector, prefix) {
        $(selector).each(function(index) {
            const heading = $(this).find('h6');
            if (heading.length) {
                heading.text(prefix.charAt(0).toUpperCase() + prefix.slice(1) + ' #' + (index + 1));
            }
            $(this).find('input, textarea, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Add More Round Logic
    $('#addRoundBtn').on('click', function() {
        if ($('.round-block').length >= 10) return;
        const newBlock = $('.round-block').first().clone();
        newBlock.find('input, select').val('');
        newBlock.find('.is-invalid').removeClass('is-invalid');
        newBlock.find('.invalid-feedback').remove();
        
        const removeBtnHtml = '<button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-3 remove-round-btn"><i class="mdi mdi-delete"></i></button>';
        if (newBlock.find('.remove-round-btn').length === 0) {
            newBlock.append(removeBtnHtml);
        }
        $('#roundContainer').append(newBlock);
        reindexBlocks('.round-block', 'rounds');
        if ($('.round-block').length >= 10) $(this).prop('disabled', true);
        calculateTotalScore();
    });

    $(document).on('click', '.remove-round-btn', function() {
        $(this).closest('.round-block').remove();
        reindexBlocks('.round-block', 'rounds');
        $('#addRoundBtn').prop('disabled', false);
        calculateTotalScore();
    });

    $(document).on('input', '.round-score', function() {
        calculateTotalScore();
    });

    function calculateTotalScore() {
        let total = 0;
        $('.round-score').each(function() {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) {
                total += val;
            }
        });
        $('#total_score_display').val(total);
    }

});
</script>
<?= $this->endSection() ?>
