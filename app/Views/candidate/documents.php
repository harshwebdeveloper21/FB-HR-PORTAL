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
    <?php if (!isset($selectedCandidate)): ?>
<style>
    /* DataTable mobile styles */
    @media (max-width: 767px) {
        .dataTables_length, .dataTables_filter { font-size: 12px !important; float: left !important; }
        div.dataTables_wrapper div.dataTables_filter input { width: 212px !important; height: 29px !important; }
    }
</style>
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                    <h4 class="card-title mb-0">Candidate Documents</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn hr-btnbg text-nowrap" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                            <i class="mdi mdi-plus iconfontsize"></i> Add Document
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped w-100" id="candidates-Table">
                        <thead class="table-light">
                            <tr>
                                <th>Candidate Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($candidates) && is_array($candidates)): ?>
                                <?php foreach($candidates as $c): ?>
                                    <tr>
                                        <td class="capitalize-text fw-bold"><?= esc($c['candidate_name']) ?></td>
                                        <td><?= esc($c['email']) ?></td>
                                        <td><?= esc($c['phone_number'] ?? 'N/A') ?></td>
                                        <td>
                                            <a href="/candidate-documents/<?= $c['id'] ?>" class="btn btn-sm text-white" style="background-color: rgb(230, 97, 54); border-color: rgb(230, 97, 54);"><i class="mdi mdi-eye"></i> View / Upload</a>
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
</div>

<!-- Add Document Modal -->
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDocumentModalLabel">Select Candidate to Add Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label for="candidate_id_select_modal" class="form-label fw-bold">Select Employee / Candidate <span class="text-danger">*</span></label>
                    <select id="candidate_id_select_modal" class="form-select" onchange="if(this.value) window.location.href='/candidate-documents/'+this.value;">
                        <option value="">-- Select --</option>
                        <?php if (isset($candidates) && is_array($candidates)): ?>
                            <?php foreach($candidates as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['candidate_name']) ?> (<?= esc($c['email']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Initialize DataTable -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if ($.fn.DataTable) {
            $('#candidates-Table').DataTable({
                language: {
                    search: "",
                    searchPlaceholder: "Search"
                }
            });
        }
    });
</script>
    <?php else: ?>
        <div class="mb-3">
            <a href="/candidate-documents" class="btn btn-sm btn-outline-secondary">&larr; Back to Selection</a>
        </div>
        <div class="wizard-header">
            <div>
                <h2>Documents for <?= esc($selectedCandidate['candidate_name']) ?></h2>
                <p class="text-muted">Upload salary slips, experience letter and other required documents.</p>
            </div>
            <?php
            $requiredDocs = ['salary_1', 'salary_2', 'salary_3', 'experience_letter', 'id_proof', 'edu_cert'];
            $uploadedRequiredCount = 0;
            if (isset($docsMap)) {
                foreach ($requiredDocs as $reqDoc) {
                    if (isset($docsMap[$reqDoc]) && in_array($docsMap[$reqDoc]['status'], ['pending', 'approved'])) {
                        $uploadedRequiredCount++;
                    }
                }
            }
            $totalRequired = count($requiredDocs);
            $progressPercent = $totalRequired > 0 ? round(($uploadedRequiredCount / $totalRequired) * 100) : 0;
            ?>
            <div class="progress-container">
                <div class="progress-text">
                    <span><?= $uploadedRequiredCount ?> of <?= $totalRequired ?> required</span>
                    <span><?= $progressPercent ?>%</span>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width: <?= $progressPercent ?>%;"></div>
                </div>
            </div>
        </div>

        <?php
        $salaryCount = 0;
        foreach (['salary_1', 'salary_2', 'salary_3'] as $k) {
            if (isset($docsMap[$k]) && in_array($docsMap[$k]['status'], ['pending', 'approved'])) {
                $salaryCount++;
            }
        }
        $prevCompCount = 0;
        foreach (['experience_letter'] as $k) { // relieving letter is not required, but if they want to count required only or total? Let's count required. The badge says 0/1.
            if (isset($docsMap[$k]) && in_array($docsMap[$k]['status'], ['pending', 'approved'])) {
                $prevCompCount++;
            }
        }
        $idEduCount = 0;
        foreach (['id_proof', 'edu_cert'] as $k) {
            if (isset($docsMap[$k]) && in_array($docsMap[$k]['status'], ['pending', 'approved'])) {
                $idEduCount++;
            }
        }
        $otherCount = 0;
        foreach (['other_doc', 'other_doc_2'] as $k) {
            if (isset($docsMap[$k]) && in_array($docsMap[$k]['status'], ['pending', 'approved'])) {
                $otherCount++;
            }
        }
        ?>
        <div class="wizard-tabs">
            <div class="wizard-tab active" data-step="1">Salary slips <span class="tab-badge"><?= $salaryCount ?>/3</span></div>
            <div class="wizard-tab" data-step="2">Previous company <span class="tab-badge"><?= $prevCompCount ?>/1</span></div>
            <div class="wizard-tab" data-step="3">ID and education <span class="tab-badge"><?= $idEduCount ?>/2</span></div>
            <div class="wizard-tab" data-step="4">Other documents <span class="tab-badge"><?= $otherCount ?>/2</span></div>
        </div>

        <form id="docsUploadForm" method="POST" action="/candidate-documents/upload" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="candidate_id" value="<?= esc($selectedCandidate['id']) ?>">
            
            <?php
            if (!function_exists('renderDocItem')) {
                function renderDocItem($docKey, $defaultTitle, $defaultSubtitle, $isRequired, $docsMap, $isMonthPicker = false) {
                    $isUploaded = isset($docsMap[$docKey]) && !empty($docsMap[$docKey]['file_name']);
                    $status = $isUploaded ? $docsMap[$docKey]['status'] : 'not-uploaded';
                    $fileName = $isUploaded ? $docsMap[$docKey]['file_name'] : '';
                    $filePath = $isUploaded ? '/' . $docsMap[$docKey]['file_path'] : '';
                    $ext = $isUploaded ? strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) : '';
                    
                    $savedTitle = (isset($docsMap[$docKey]) && !empty($docsMap[$docKey]['doc_title']))
                        ? $docsMap[$docKey]['doc_title']
                        : $defaultTitle;

                    $savedSubtitle = (isset($docsMap[$docKey]) && !empty($docsMap[$docKey]['doc_subtitle']))
                        ? $docsMap[$docKey]['doc_subtitle']
                        : $defaultSubtitle;

                    $btnClass = $isUploaded ? 'btn-replace' : 'btn-upload px-3 py-2 fw-bold';
                    $btnText = $isUploaded ? 'Replace' : 'Upload';
                    $removeDisplay = $isUploaded ? '' : 'display:none;';

                    // Preview logic
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                    $previewStyle = $isUploaded ? 'display:flex; justify-content:center; align-items:center;' : 'display:none;';
                    $imgStyle = $isImage ? 'display:block;' : 'display:none;';
                    $imgSrc = $isImage ? $filePath : '';
                    
                    $fileHtml = '';
                    if ($isUploaded && !$isImage) {
                        $fileHtml = '<div class="file-preview-icon text-center"><i class="mdi mdi-file-document-outline" style="font-size: 30px; color: #6c757d;"></i><br><a href="'.$filePath.'" target="_blank" class="text-decoration-none" style="font-size: 11px;">View</a></div>';
                    }

                    $titleHtml = '
                    <div class="mb-1" style="max-width: 320px;">
                        <input type="text" name="doc_title_'.$docKey.'" class="form-control form-control-sm fw-bold border" style="font-size: 14px;" value="'.esc($savedTitle).'" placeholder="Enter document name...">
                    </div>';

                    if ($isMonthPicker) {
                        $monthVal = '';
                        if (!empty($savedSubtitle)) {
                            if (preg_match('/^\d{4}-\d{2}$/', trim($savedSubtitle))) {
                                $monthVal = trim($savedSubtitle);
                            } else {
                                $ts = strtotime($savedSubtitle);
                                if ($ts !== false) {
                                    $monthVal = date('Y-m', $ts);
                                }
                            }
                        }
                        $subtitleHtml = '
                        <div class="input-group input-group-sm mt-1" style="max-width: 220px;">
                            <span class="input-group-text text-white me-0" style="background-color: #e75c25; border-color: #e75c25;">
                                <i class="mdi mdi-calendar text-white" style="font-size: 16px; color: #ffffff !important;"></i>
                            </span>
                            <input type="month" name="doc_subtitle_'.$docKey.'" class="form-control form-control-sm text-dark fw-bold" style="font-size: 13px; cursor: pointer;" value="'.esc($monthVal).'" title="Click to open month and year calendar">
                        </div>';
                    } else {
                        $subtitleHtml = '
                        <div class="mt-1" style="max-width: 320px;">
                            <input type="text" name="doc_subtitle_'.$docKey.'" class="form-control form-control-sm text-muted border" style="font-size: 13px;" value="'.esc($savedSubtitle).'" placeholder="Enter subtitle or description...">
                        </div>';
                    }

                    echo '
                    <div class="doc-item">
                        <div class="doc-info">
                            '.$titleHtml.'
                            '.$subtitleHtml.'
                        </div>
                        <div class="preview-container mx-3" style="'.$previewStyle.'">
                            <img src="'.$imgSrc.'" alt="Preview" class="zoomable-image" style="'.$imgStyle.' cursor: pointer;" title="Click to zoom">
                            '.$fileHtml.'
                        </div>
                        <div class="doc-actions">
                            <input type="file" name="'.$docKey.'" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
                            <button type="button" class="btn btn-sm upload-btn '.$btnClass.'">'.$btnText.'</button>
                            <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="'.$removeDisplay.'">Remove</button>
                        </div>
                    </div>';
                }
            }
            ?>
            
        <!-- Step 1: Salary slips -->
        <div class="wizard-card active" id="step-1">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <?php 
            renderDocItem('salary_1', 'Salary slip, Month 1', 'August 2026', true, $docsMap, true); 
            renderDocItem('salary_2', 'Salary slip, Month 2', 'July 2026', true, $docsMap, true); 
            renderDocItem('salary_3', 'Salary slip, Month 3', 'June 2026', true, $docsMap, true); 
            ?>
        </div>

        <!-- Step 2: Previous company -->
        <div class="wizard-card" id="step-2">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <?php 
            renderDocItem('experience_letter', 'Experience letter', 'From your previous company', true, $docsMap); 
            renderDocItem('relieving_letter', 'Relieving letter', 'If you have one', false, $docsMap); 
            ?>
        </div>

        <!-- Step 3: ID and education -->
        <div class="wizard-card" id="step-3">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <?php 
            renderDocItem('id_proof', 'ID proof', 'Aadhaar or PAN', true, $docsMap); 
            renderDocItem('edu_cert', 'Educational certificates', 'Highest qualification', true, $docsMap); 
            ?>
        </div>

        <!-- Step 4: Other documents -->
        <div class="wizard-card" id="step-4">
            <p class="text-muted" style="font-size: 13px;">Allowed: PDF, JPG, PNG. Maximum 5 MB per file. HR will review each document.</p>
            
            <div id="other-docs-container">
                <?php 
                renderDocItem('other_doc', 'Other document 1', 'Any other related document', false, $docsMap); 
                renderDocItem('other_doc_2', 'Other document 2', 'Additional document or certificate', false, $docsMap); 

                // Render any additional uploaded other_doc_X items
                $otherDocIndex = 3;
                while (isset($docsMap['other_doc_' . $otherDocIndex])) {
                    renderDocItem('other_doc_' . $otherDocIndex, 'Other document ' . $otherDocIndex, 'Additional document or certificate', false, $docsMap);
                    $otherDocIndex++;
                }
                ?>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-sm text-white fw-bold px-3 py-2" id="addMoreOtherDocBtn" style="background-color: #e75c25; border-color: #e75c25; border-radius: 6px;">
                    <i class="mdi mdi-plus me-1"></i> Add More Document
                </button>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="d-flex justify-content-end footer-buttons mt-4">
            <div>
                <button type="button" class="btn btn-secondary me-2 text-white" id="prevBtn" onclick="nextPrev(-1)" style="display:none;">Previous</button>
                <button type="button" class="btn btn-primary-custom" id="nextBtn" onclick="nextPrev(1)">Next</button>
            </div>
        </div>

    </form>
    <?php endif; ?>
</div>

<!-- Image Zoom Modal -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-labelledby="imageZoomModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
      <div class="modal-header border-0" style="padding: 0; position: absolute; right: 0; z-index: 1055;">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background-color: rgba(0,0,0,0.5); border-radius: 50%; padding: 10px; margin: 10px;"></button>
      </div>
      <div class="modal-body text-center p-0">
        <img id="zoomedImage" src="" alt="Zoomed Document" class="img-fluid rounded shadow" style="max-height: 90vh;">
      </div>
    </div>
  </div>
</div>

<script>
let currentTab = 1;
const totalTabs = 4;

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
        $('#docsUploadForm').submit();
        return false;
    }
    currentTab = currentTab + n;
    showTab(currentTab);
}

// Add More Document Logic
$('#addMoreOtherDocBtn').click(function() {
    const currentCount = $('#other-docs-container .doc-item').length + 1;
    const docKey = 'other_doc_' + currentCount;
    const title = 'Other document ' + currentCount;
    const subtitle = 'Additional document or certificate';

    const newDocHtml = `
    <div class="doc-item">
        <div class="doc-info">
            <div class="mb-1" style="max-width: 320px;">
                <input type="text" name="doc_title_${docKey}" class="form-control form-control-sm fw-bold border" style="font-size: 14px;" value="${title}" placeholder="Enter document name...">
            </div>
            <div class="mt-1" style="max-width: 320px;">
                <input type="text" name="doc_subtitle_${docKey}" class="form-control form-control-sm text-muted border" style="font-size: 13px;" value="${subtitle}" placeholder="Enter subtitle or description...">
            </div>
        </div>
        <div class="preview-container mx-3" style="display:none;">
            <img src="" alt="Preview" class="zoomable-image" style="display:none; cursor: pointer;" title="Click to zoom">
        </div>
        <div class="doc-actions">
            <input type="file" name="${docKey}" class="d-none file-input" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm upload-btn btn-upload px-3 py-2 fw-bold">Upload</button>
            <button type="button" class="btn btn-sm btn-remove ms-2 remove-btn" style="display:none;">Remove</button>
        </div>
    </div>`;

    $('#other-docs-container').append(newDocHtml);
});

// File Upload Logic with Delegation
$(document).on('click', '.upload-btn', function() {
    $(this).siblings('.file-input').click();
});

$(document).on('change', '.file-input', function() {
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
                previewImg.attr('src', e.target.result).show();
                previewImg.addClass('zoomable-image').css('cursor', 'pointer').attr('title', 'Click to zoom');
                previewContainer.find('.file-preview-icon').hide();
                previewContainer.show();
            }
            reader.readAsDataURL(file);
        } else {
            previewImg.hide();
            let iconHtml = previewContainer.find('.file-preview-icon');
            if (iconHtml.length === 0) {
                previewContainer.append('<div class="file-preview-icon text-center"><i class="mdi mdi-file-document-outline" style="font-size: 30px; color: #6c757d;"></i><br><span style="font-size: 11px;">Selected</span></div>');
            } else {
                iconHtml.show();
            }
            previewContainer.show();
        }
    }
});

$(document).on('click', '.remove-btn', function() {
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
    if (rejectReason.length) rejectReason.show();
});

// Image Zoom Handler
$(document).on('click', '.zoomable-image', function() {
    const imgSrc = $(this).attr('src');
    if (imgSrc) {
        $('#zoomedImage').attr('src', imgSrc);
        $('#imageZoomModal').modal('show');
    }
});
</script>
<?= $this->endSection() ?>
