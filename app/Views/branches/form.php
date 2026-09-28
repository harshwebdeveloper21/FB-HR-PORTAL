<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<?php $isEdit = isset($branch); ?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">
            <i class="mdi mdi-office-building me-2 text-primary"></i>
            <?= $isEdit ? 'Edit Branch' : 'Create Branch' ?>
          </h4>
          <a href="<?= base_url('/branches') ?>" class="btn hr-btnbg">
            <i class="mdi mdi-arrow-left me-1"></i> Back
          </a>
        </div>

        <form id="branchForm">
          <?php if ($isEdit): ?>
            <input type="hidden" id="branchId" value="<?= $branch['id'] ?>">
          <?php endif; ?>

          <div class="row">
            <div class="col-md-8 mb-3">
              <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
              <input type="text" id="name" class="form-control"
                     value="<?= htmlspecialchars($branch['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Branch Code <span class="text-danger">*</span></label>
              <input type="text" id="code" class="form-control text-uppercase"
                     value="<?= htmlspecialchars($branch['code'] ?? '') ?>"
                     placeholder="e.g. MUM" maxlength="10" required>
              <div class="form-text">Unique 2-10 char code</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Address</label>
            <textarea id="address" class="form-control" rows="2"><?= htmlspecialchars($branch['address'] ?? '') ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">City</label>
              <input type="text" id="city" class="form-control"
                     value="<?= htmlspecialchars($branch['city'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Phone</label>
              <input type="text" id="phone" class="form-control"
                     value="<?= htmlspecialchars($branch['phone'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Status</label>
            <select id="status" class="form-select">
              <option value="active"   <?= ($branch['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= ($branch['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>

          <h5 class="mb-3 text-primary"><i class="mdi mdi-map-marker me-1"></i> Location Settings (For Geofencing)</h5>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Latitude</label>
              <input type="text" id="latitude" class="form-control"
                     value="<?= htmlspecialchars($branch['latitude'] ?? '') ?>" placeholder="e.g. 19.0760">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Longitude</label>
              <input type="text" id="longitude" class="form-control"
                     value="<?= htmlspecialchars($branch['longitude'] ?? '') ?>" placeholder="e.g. 72.8777">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">Radius (Meters)</label>
              <input type="number" id="radius" class="form-control"
                     value="<?= htmlspecialchars($branch['radius'] ?? '100') ?>">
            </div>
          </div>

          <div id="formError" class="alert alert-danger d-none"></div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn hr-btnbg" id="submitBtn">
              <i class="mdi mdi-content-save me-1"></i>
              <?= $isEdit ? 'Update Branch' : 'Create Branch' ?>
            </button>
            <a href="<?= base_url('/branches') ?>" class="btn hr-btnbg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
const isEdit   = <?= $isEdit ? 'true' : 'false' ?>;
const branchId = isEdit ? document.getElementById('branchId').value : null;

document.getElementById('code').addEventListener('input', function() {
  this.value = this.value.toUpperCase();
});

document.getElementById('branchForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

  const payload = {
    name:    document.getElementById('name').value.trim(),
    code:    document.getElementById('code').value.trim().toUpperCase(),
    address: document.getElementById('address').value.trim(),
    city:    document.getElementById('city').value.trim(),
    phone:   document.getElementById('phone').value.trim(),
    status:  document.getElementById('status').value,
    latitude: document.getElementById('latitude').value.trim(),
    longitude: document.getElementById('longitude').value.trim(),
    radius:  document.getElementById('radius').value.trim(),
  };

  const url    = isEdit ? `/api/branches/${branchId}` : '/api/branches';
  const method = isEdit ? 'PUT' : 'POST';

  try {
    const res  = await fetch(url, {
      method,
      headers: { 
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + localStorage.getItem('token')
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (data.status === 'success') {
      window.location.href = '/branches?success=1';
    } else {
      const errDiv = document.getElementById('formError');
      errDiv.classList.remove('d-none');
      errDiv.textContent = data.message || 'An error occurred.';
      if (data.errors) {
        errDiv.textContent = Object.values(data.errors).join('\n');
      }
      btn.disabled = false;
      btn.innerHTML = '<i class="mdi mdi-content-save me-1"></i>' + (isEdit ? 'Update Branch' : 'Create Branch');
    }
  } catch (err) {
    document.getElementById('formError').classList.remove('d-none');
    document.getElementById('formError').textContent = 'Network error. Please try again.';
    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-content-save me-1"></i>Save';
  }
});


</script>
<?php $this->endSection(); ?>
