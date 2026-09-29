<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
.doc-wizard { font-family: "Inter", sans-serif; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
.wizard-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.progress-container { width: 200px; text-align: right; }
.progress-text { font-size: 14px; font-weight: 500; margin-bottom: 5px; display: flex; justify-content: space-between;}
.progress-bar-custom { height: 8px; background-color: #e9ecef; border-radius: 4px; overflow: hidden; display: flex; }
.progress-bar-fill { height: 100%; background-color: #e75c25; transition: width 0.3s ease; }
.wizard-tabs { display: flex; border-bottom: 1px solid #eee; margin-bottom: 20px; gap: 20px;}
.wizard-tab { padding: 10px 0; cursor: pointer; color: #666; font-weight: 500; position: relative; }
.wizard-tab.active { color: #e75c25; }
.wizard-tab.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background-color: #e75c25; }
.tab-badge { background-color: #f1f3f5; color: #495057; font-size: 12px; padding: 2px 8px; border-radius: 12px; margin-left: 5px; font-weight: 600;}
.wizard-tab.active .tab-badge { background-color: #f1f3f5; color: #495057; }
.wizard-card { display: none; }
.wizard-card.active { display: block; }
.doc-item { padding: 20px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
.doc-info { flex: 1; }
.doc-info h5 { margin-bottom: 5px; font-size: 16px; font-weight: 600; color: #212529;}
.doc-info h5 span.text-danger { color: #e75c25 !important; }
.doc-info p { margin-bottom: 8px; font-size: 13px; color: #6c757d; }
.status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; margin-right: 10px; }
.status-approved { background-color: #d1e7dd; color: #0f5132; }
.status-rejected { background-color: #f8d7da; color: #842029; }
.status-not-uploaded { background-color: #f1f3f5; color: #6c757d; }
.file-name { font-size: 13px; color: #495057; }
.reject-reason { font-size: 13px; color: #dc3545; margin-top: 8px; }
.btn-upload { color: #e75c25; border-color: #e75c25; background: white; }
.btn-upload:hover { background-color: #e75c25; color: white; }
.btn-replace { color: #495057; border-color: #ced4da; background: white; }
.btn-replace:hover { background-color: #f8f9fa; }
.btn-remove { color: #495057; border-color: #ced4da; background: white; }
.btn-remove:hover { background-color: #f8f9fa; }
.footer-buttons { padding-top: 20px; border-top: 1px solid #eee; }
.btn-primary-custom { background-color: #e75c25; border-color: #e75c25; color: white; }
.btn-primary-custom:hover { background-color: #d05321; border-color: #d05321; color: white; }
.preview-container { margin-top: 0; display: none; flex-shrink: 0; }
.preview-container img { max-height: 80px; max-width: 150px; border-radius: 5px; border: 1px solid #ddd; object-fit: cover; }
</style>

<div class="content-wrapper doc-wizard">
    <div class="wizard-header">
        <div>
            <h2>My Documents</h2>
            <p class="text-muted">Upload your salary slips, experience letter and other required documents.</p>
        </div>
        <div class="progress-container">
            <div class="progress-text">
                <span>2 of 6 required</span>
                <span>33%</span>
            </div>
            <div class="progress-bar-custom">
                <div class="progress-bar-fill" style="width: 33%;"></div>
            </div>
        </div>
    </div>

    <div class="wizard-tabs">
        <div class="wizard-tab active" data-step="1">Salary slips <span class="tab-badge">2/3</span></div>
        <div class="wizard-tab" data-step="2">Previous company <span class="tab-badge">0/1</span></div>
        <div class="wizard-tab" data-step="3">ID and education <span class="tab-badge">0/2</span></div>
    </div>

    <form method="POST" action="/candidate-documents/upload" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <!-- Step 1: Salary slips -->
        <div class="wizard-card active" id="step-1">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <div class="doc-item">
                <div class="doc-info">
                    <h5>Salary slip, Month 1 <span class="text-danger">*</span></h5>
                    <p>August 2026</p>
                    <div class="d-flex align-items-center status-container">
                        <span class="status-badge status-approved">Approved</span>
                        <span class="file-name">salary_aug.pdf</span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="salary_1" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-replace upload-btn">Replace</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>

            <div class="doc-item">
                <div class="doc-info">
                    <h5>Salary slip, Month 2 <span class="text-danger">*</span></h5>
                    <p>July 2026</p>
                    <div class="d-flex align-items-center status-container">
                        <span class="status-badge status-rejected">Rejected</span>
                        <span class="file-name">salary_jul.jpg</span>
                    </div>
                    <div class="reject-reason">Image is blurry. Please upload a clear copy.</div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="salary_2" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-replace upload-btn">Replace</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn">Remove</button>
                </div>
            </div>

            <div class="doc-item" style="border-bottom: none;">
                <div class="doc-info">
                    <h5>Salary slip, Month 3 <span class="text-danger">*</span></h5>
                    <p>June 2026</p>
                    <div class="status-container">
                        <span class="status-badge status-not-uploaded">Not uploaded</span>
                        <span class="file-name d-none"></span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="salary_3" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-upload px-3 py-2 fw-bold upload-btn">Upload</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>
        </div>

        <!-- Step 2: Previous company -->
        <div class="wizard-card" id="step-2">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <div class="doc-item">
                <div class="doc-info">
                    <h5>Experience letter <span class="text-danger">*</span></h5>
                    <p>From your previous company</p>
                    <div class="status-container">
                        <span class="status-badge status-not-uploaded">Not uploaded</span>
                        <span class="file-name d-none"></span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="experience_letter" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-upload px-3 py-2 fw-bold upload-btn">Upload</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>

            <div class="doc-item" style="border-bottom: none;">
                <div class="doc-info">
                    <h5>Relieving letter</h5>
                    <p>If you have one</p>
                    <div class="status-container">
                        <span class="status-badge status-not-uploaded">Not uploaded</span>
                        <span class="file-name d-none"></span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="relieving_letter" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-upload px-3 py-2 fw-bold upload-btn">Upload</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>
        </div>

        <!-- Step 3: ID and education -->
        <div class="wizard-card" id="step-3">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <div class="doc-item">
                <div class="doc-info">
                    <h5>ID proof <span class="text-danger">*</span></h5>
                    <p>Aadhaar or PAN</p>
                    <div class="status-container">
                        <span class="status-badge status-not-uploaded">Not uploaded</span>
                        <span class="file-name d-none"></span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="id_proof" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-upload px-3 py-2 fw-bold upload-btn">Upload</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>

            <div class="doc-item">
                <div class="doc-info">
                    <h5>Educational certificates <span class="text-danger">*</span></h5>
                    <p>Highest qualification</p>
                    <div class="status-container">
                        <span class="status-badge status-not-uploaded">Not uploaded</span>
                        <span class="file-name d-none"></span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="edu_cert" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-upload px-3 py-2 fw-bold upload-btn">Upload</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>

            <div class="doc-item" style="border-bottom: none;">
                <div class="doc-info">
                    <h5>Other documents</h5>
                    <p>Any other related document</p>
                    <div class="status-container">
                        <span class="status-badge status-not-uploaded">Not uploaded</span>
                        <span class="file-name d-none"></span>
                    </div>
                </div>
                <div class="preview-container mx-3">
                    <img src="" alt="Preview">
                </div>
                <div class="doc-actions">
                    <input type="file" name="other_doc" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-upload px-3 py-2 fw-bold upload-btn">Upload</button>
                    <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="d-flex justify-content-between footer-buttons mt-4">
            <button type="button" class="btn btn-outline-secondary">Save draft</button>
            <div>
                <button type="button" class="btn btn-outline-secondary me-2" id="prevBtn" onclick="nextPrev(-1)" style="display:none;">Previous</button>
                <button type="button" class="btn btn-primary-custom" id="nextBtn" onclick="nextPrev(1)">Next</button>
            </div>
        </div>

    </form>
</div>

<script>
let currentTab = 1;
const totalTabs = 3;

$('.wizard-tab').click(function(){
    const step = $(this).data('step');
    showTab(step);
});

function showTab(n) {
    currentTab = n;
    $('.wizard-tab').removeClass('active');
    $('.wizard-card').removeClass('active');
    
    $('.wizard-tab[data-step="'+n+'"]').addClass('active');
    $('#step-'+n).addClass('active');

    if (n == 1) {
        $('#prevBtn').hide();
    } else {
        $('#prevBtn').show();
    }
    
    if (n == totalTabs) {
        $('#nextBtn').html('Submit for review');
    } else {
        $('#nextBtn').html('Next');
    }
}

function nextPrev(n) {
    if (n == 1 && currentTab == totalTabs) {
        // Submit form
        $('form').submit();
        return false;
    }
    currentTab = currentTab + n;
    showTab(currentTab);
}

// File Upload Logic
$('.upload-btn').click(function() {
    $(this).siblings('.file-input').click();
});

$('.file-input').change(function() {
    const file = this.files[0];
    const item = $(this).closest('.doc-item');
    const uploadBtn = item.find('.upload-btn');
    const removeBtn = item.find('.remove-btn');
    const badge = item.find('.status-badge');
    const fileNameSpan = item.find('.file-name');
    const previewContainer = item.find('.preview-container');
    const previewImg = previewContainer.find('img');
    const rejectReason = item.find('.reject-reason');

    if (file) {
        // Update UI
        fileNameSpan.text(file.name).removeClass('d-none');
        item.find('.status-container').addClass('d-flex align-items-center');
        
        badge.removeClass('status-not-uploaded status-rejected').addClass('status-approved').text('Selected');
        
        if (rejectReason.length) rejectReason.hide();
        
        // Change button style
        uploadBtn.removeClass('btn-upload px-3 py-2 fw-bold').addClass('btn-replace').text('Replace');
        removeBtn.show();

        // Image Preview
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.attr('src', e.target.result);
                previewContainer.show();
            }
            reader.readAsDataURL(file);
        } else {
            previewContainer.hide();
        }
    }
});

$('.remove-btn').click(function() {
    const item = $(this).closest('.doc-item');
    const fileInput = item.find('.file-input');
    const uploadBtn = item.find('.upload-btn');
    const removeBtn = item.find('.remove-btn');
    const badge = item.find('.status-badge');
    const fileNameSpan = item.find('.file-name');
    const previewContainer = item.find('.preview-container');
    const rejectReason = item.find('.reject-reason');

    // Reset file input
    fileInput.val('');
    
    // Reset UI
    fileNameSpan.text('').addClass('d-none');
    item.find('.status-container').removeClass('d-flex align-items-center');
    badge.removeClass('status-approved status-rejected').addClass('status-not-uploaded').text('Not uploaded');
    
    uploadBtn.removeClass('btn-replace').addClass('btn-upload px-3 py-2 fw-bold').text('Upload');
    removeBtn.hide();
    previewContainer.hide();
    if (rejectReason.length) rejectReason.show(); // Show if it existed originally, but maybe better to keep it hidden on clear.
});
</script>
<?= $this->endSection() ?>
