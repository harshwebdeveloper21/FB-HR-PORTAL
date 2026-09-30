<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<style>
.sig-container {
    font-family: "Inter", sans-serif;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}
.sig-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 25px;
}
.canvas-wrapper {
    border: 2px dashed #ced4da;
    border-radius: 8px;
    background: #fafafa;
    position: relative;
    text-align: center;
    padding: 10px;
}
#signatureCanvas {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    cursor: crosshair;
    touch-action: none;
}
.preview-card-box {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    background: #fff;
    text-align: center;
}
.preview-img-box {
    max-height: 80px;
    max-width: 180px;
    object-fit: contain;
}
.badge-active {
    background-color: #d1e7dd;
    color: #0f5132;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.btn-sig-toggle {
    color: rgb(230, 97, 54);
    border-color: rgb(230, 97, 54);
    background-color: transparent;
}
.btn-sig-toggle:hover {
    color: #fff;
    background-color: rgb(230, 97, 54);
    border-color: rgb(230, 97, 54);
}
.btn-sig-toggle.active {
    color: #fff !important;
    background-color: rgb(230, 97, 54) !important;
    border-color: rgb(230, 97, 54) !important;
}
</style>

<div class="content-wrapper sig-container">
    <!-- Header -->
    <div class="sig-header">
        <div>
            <h2 class="mb-1" style="font-weight: 700;">Global Digital Signature & Stamp</h2>
            <p class="text-muted mb-0">Set up official authorized signatures and stamps for offer letters and HR documents.</p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-1"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <!-- Main Content: Create Signature Form -->
    <div class="card mb-4 border">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold"><i class="mdi mdi-pencil-outline me-1" style="color: #e75c25;"></i> Add Digital Signature & Stamp</h5>
        </div>
        <div class="card-body">
            <form action="<?= site_url('digital-signature/save') ?>" method="POST" enctype="multipart/form-data" id="sigForm" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="signature_drawn_data" id="signatureDrawnData">

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Signee Name <span class="text-danger">*</span></label>
                        <input type="text" name="signee_name" id="signee_name" class="form-control" placeholder="e.g. Harsing Chaudhari">
                        <div class="invalid-feedback text-danger mt-1" id="signee_name_error" style="display: none;">Please enter signee name.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Designation</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Head of HR">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Company / Department Name</label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Korvia Smart HR">
                    </div>
                </div>

                <div class="row g-4 mb-3">
                    <!-- Signature Option (Draw vs Upload) -->
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Digital Signature <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-sig-toggle active" id="btnModeDraw" onclick="switchSigMode('draw')"><i class="mdi mdi-gesture-tap-button"></i> Draw Signature</button>
                                <button type="button" class="btn btn-sig-toggle" id="btnModeUpload" onclick="switchSigMode('upload')"><i class="mdi mdi-upload"></i> Upload Image File</button>
                            </div>
                        </div>

                        <!-- Draw Canvas Box -->
                        <div id="drawSigBox" class="canvas-wrapper">
                            <canvas id="signatureCanvas" width="450" height="140"></canvas>
                            <div class="mt-2 d-flex justify-content-between align-items-center px-2">
                                <span class="text-muted" style="font-size: 12px;"><i class="mdi mdi-information-outline"></i> Draw inside the white box using mouse or touch.</span>
                                <button type="button" class="btn btn-sm btn-sig-toggle" onclick="clearCanvas()"><i class="mdi mdi-refresh"></i> Clear</button>
                            </div>
                        </div>

                        <!-- Upload File Box -->
                        <div id="uploadSigBox" class="p-3 border rounded bg-light" style="display: none;">
                            <label class="form-label" style="font-size: 13px;">Upload Signature Image (PNG, JPG, WEBP)</label>
                            <input type="file" name="signature_file" id="signature_file" class="form-control" accept=".png,.jpg,.jpeg,.webp">
                            <small class="text-muted d-block mt-1">Transparent PNG images recommended for best PDF rendering results.</small>
                        </div>
                        <div class="invalid-feedback text-danger mt-1" id="signature_error" style="display: none;">Please draw or upload a digital signature image.</div>
                    </div>

                    <!-- Company Stamp Option -->
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Company Stamp / Seal</label>
                        <div class="p-3 border rounded bg-light" style="min-height: 180px;">
                            <label class="form-label" style="font-size: 13px;">Upload Stamp Image (PNG, JPG, WEBP)</label>
                            <input type="file" name="stamp_file" class="form-control mb-2" accept=".png,.jpg,.jpeg,.webp">
                            <small class="text-muted d-block">Upload official round seal or authorized company stamp image.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end border-top pt-3">
                    <input type="hidden" name="is_default" value="1">
                    <button type="submit" class="btn text-white fw-bold px-4 py-2" style="background-color: #e75c25; border-color: #e75c25;" onclick="prepareSubmission()">
                        <i class="mdi mdi-content-save me-1"></i> Save Signature & Stamp
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Saved Signatures Table -->
    <div class="card border">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold"><i class="mdi mdi-format-list-bulleted me-1" style="color: #e75c25;"></i> Manage Signatures Library</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Signee & Designation</th>
                            <th class="text-center">Signature Preview</th>
                            <th class="text-center">Stamp Preview</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($signatures)): foreach ($signatures as $sig): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= esc($sig['signee_name']) ?></div>
                                    <small class="text-muted">
                                        <?= esc($sig['designation'] ?? 'N/A') ?> 
                                        <?= !empty($sig['company_name']) ? ' &bull; ' . esc($sig['company_name']) : '' ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($sig['signature_path']) && file_exists(FCPATH . $sig['signature_path'])): ?>
                                        <img src="<?= base_url(esc($sig['signature_path'])) ?>" alt="Signature" style="max-height: 45px; max-width: 140px; object-fit: contain;">
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($sig['stamp_path']) && file_exists(FCPATH . $sig['stamp_path'])): ?>
                                        <img src="<?= base_url(esc($sig['stamp_path'])) ?>" alt="Stamp" style="max-height: 45px; max-width: 100px; object-fit: contain;">
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($sig['is_default'] == 1): ?>
                                        <span class="badge badge-active"><i class="mdi mdi-check me-1"></i> Active Default</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($sig['is_default'] != 1): ?>
                                        <a href="<?= site_url('digital-signature/set-default/' . $sig['id']) ?>" class="btn btn-sm btn-outline-warning text-dark me-1" title="Set as Default">
                                            <i class="mdi mdi-star"></i> Make Default
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('digital-signature/delete/' . $sig['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this signature?');" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No digital signatures created yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Canvas Drawing Script -->
<script>
let canvas = document.getElementById('signatureCanvas');
let ctx = canvas.getContext('2d');
let isDrawing = false;
let hasDrawn = false;

// Set stroke styles
ctx.strokeStyle = "#000000";
ctx.lineWidth = 2.5;
ctx.lineCap = "round";
ctx.lineJoin = "round";

function getPos(e) {
    let rect = canvas.getBoundingClientRect();
    let clientX = e.clientX || (e.touches && e.touches[0].clientX);
    let clientY = e.clientY || (e.touches && e.touches[0].clientY);
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

function startDrawing(e) {
    isDrawing = true;
    let pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
}

function draw(e) {
    if (!isDrawing) return;
    e.preventDefault();
    let pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
    hasDrawn = true;
    let sigErr = document.getElementById('signature_error');
    if (sigErr) sigErr.style.display = 'none';
}

function stopDrawing() {
    isDrawing = false;
}

// Mouse events
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseleave', stopDrawing);

// Touch events for mobile
canvas.addEventListener('touchstart', startDrawing);
canvas.addEventListener('touchmove', draw);
canvas.addEventListener('touchend', stopDrawing);

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasDrawn = false;
    document.getElementById('signatureDrawnData').value = '';
}

function switchSigMode(mode) {
    let sigErr = document.getElementById('signature_error');
    if (sigErr) sigErr.style.display = 'none';
    
    if (mode === 'draw') {
        document.getElementById('drawSigBox').style.display = 'block';
        document.getElementById('uploadSigBox').style.display = 'none';
        document.getElementById('btnModeDraw').classList.add('active');
        document.getElementById('btnModeUpload').classList.remove('active');
    } else {
        document.getElementById('drawSigBox').style.display = 'none';
        document.getElementById('uploadSigBox').style.display = 'block';
        document.getElementById('btnModeDraw').classList.remove('active');
        document.getElementById('btnModeUpload').classList.add('active');
    }
}

function prepareSubmission() {
    if (hasDrawn && document.getElementById('drawSigBox').style.display !== 'none') {
        let dataURL = canvas.toDataURL('image/png');
        document.getElementById('signatureDrawnData').value = dataURL;
    }
}

// Real-time Input Validation Listeners
document.getElementById('signee_name').addEventListener('input', function() {
    if (this.value.trim()) {
        this.classList.remove('is-invalid');
        document.getElementById('signee_name_error').style.display = 'none';
    }
});

document.getElementById('signature_file').addEventListener('change', function() {
    if (this.files && this.files.length > 0) {
        document.getElementById('signature_error').style.display = 'none';
    }
});

// Form Submit Validation
document.getElementById('sigForm').addEventListener('submit', function(e) {
    prepareSubmission();

    let isValid = true;
    let signeeInput = document.getElementById('signee_name');
    let signeeError = document.getElementById('signee_name_error');

    if (!signeeInput.value.trim()) {
        signeeInput.classList.add('is-invalid');
        signeeError.style.display = 'block';
        isValid = false;
    } else {
        signeeInput.classList.remove('is-invalid');
        signeeError.style.display = 'none';
    }

    let drawBoxVisible = document.getElementById('drawSigBox').style.display !== 'none';
    let drawnData = document.getElementById('signatureDrawnData').value;
    let sigFileInput = document.getElementById('signature_file');
    let signatureError = document.getElementById('signature_error');

    let hasSig = false;
    if (drawBoxVisible) {
        if (hasDrawn && drawnData) {
            hasSig = true;
        }
    } else {
        if (sigFileInput && sigFileInput.files && sigFileInput.files.length > 0) {
            hasSig = true;
        }
    }

    if (!hasSig) {
        signatureError.textContent = drawBoxVisible
            ? 'Please draw a signature inside the canvas box before saving.'
            : 'Please select a signature image file to upload.';
        signatureError.style.display = 'block';
        isValid = false;
    } else {
        signatureError.style.display = 'none';
    }

    if (!isValid) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
});
</script>

<?= $this->endSection() ?>
