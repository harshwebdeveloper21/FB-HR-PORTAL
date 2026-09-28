<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title mb-1">
              <i class="mdi mdi-office-building text-primary me-2"></i>Branch Management
            </h4>
            <p class="text-muted mb-0">Manage company branches, assign HR, and configure rules.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= base_url('/branch-managers') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-account-group me-1"></i> Branch Managers
            </a>
            <a href="<?= base_url('/branches/create') ?>" class="btn hr-btnbg">
              <i class="mdi mdi-plus me-1"></i> Add Branch
            </a>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
              <input type="text" id="branchSearch" class="form-control" placeholder="Search by name, code, city...">
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-hover" id="branchTable">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Branch Name</th>
                <th>Code</th>
                <th>City</th>
                <th>Branch Manager</th>
                <th>Departments</th>
                <th>Staff Count</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="branchTableBody">
              <tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div id="branchPaginationInfo" class="text-muted small"></div>
          <nav><ul class="pagination pagination-sm mb-0" id="branchPagination"></ul></nav>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
let currentPage = 1;
const perPage   = 10;
let searchTimer;

function loadBranches(page = 1) {
  const search = document.getElementById('branchSearch').value.trim();
  currentPage  = page;

  fetch(`/api/branches?page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}`, {
    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
    credentials: 'same-origin'
  })
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById('branchTableBody');
      if (!data.data || !data.data.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No branches found.</td></tr>';
        document.getElementById('branchPaginationInfo').textContent = '';
        document.getElementById('branchPagination').innerHTML = '';
        return;
      }

      tbody.innerHTML = data.data.map((b, i) => {
        const managerHtml = b.branch_admin_name
          ? `<span class="badge bg-primary text-white" title="${escHtml(b.branch_admin_email || '')}"><i class="mdi mdi-account-tie me-1"></i>${escHtml(b.branch_admin_name)}</span>`
          : `<a href="/branches/assign-manager/${b.id}" class="badge bg-light text-muted border text-decoration-none" title="Assign a Branch Manager"><i class="mdi mdi-plus me-1"></i>Assign Manager</a>`;

        return `
        <tr>
          <td>${(page - 1) * perPage + i + 1}</td>
          <td><strong>${escHtml(b.name)}</strong></td>
          <td><span class="badge bg-secondary">${escHtml(b.code)}</span></td>
          <td>${escHtml(b.city || '—')}</td>
          <td>${managerHtml}</td>
          <td><span class="badge bg-info text-dark">${b.dept_count || 0}</span></td>
          <td><span class="badge bg-primary">${b.staff_count || 0}</span></td>
          <td>${b.status === 'active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>'}</td>
          <td>
            <a href="/branches/edit/${b.id}" title="Edit Branch" class="text-warning me-2"><i class="mdi mdi-pencil fs-5"></i></a>
            <a href="/branches/assign-manager/${b.id}" title="Assign/Change Branch Manager" class="text-primary me-2"><i class="mdi mdi-account-cog fs-5"></i></a>
            <a href="/branch-rules/edit/${b.id}" title="Rules" class="text-success me-2"><i class="mdi mdi-cog fs-5"></i></a>
            <button onclick="deleteBranch(${b.id},'${escHtml(b.name)}')" title="Delete" class="btn btn-sm btn-link text-danger p-0"><i class="mdi mdi-trash-can fs-5"></i></button>
          </td>
        </tr>`;
      }).join('');

      // Pagination info
      const total   = data.total;
      const pages   = Math.ceil(total / perPage);
      document.getElementById('branchPaginationInfo').textContent =
        `Showing ${(page-1)*perPage+1}–${Math.min(page*perPage, total)} of ${total}`;

      const ul = document.getElementById('branchPagination');
      ul.innerHTML = '';
      for (let p = 1; p <= pages; p++) {
        ul.innerHTML += `<li class="page-item ${p===page?'active':''}">
          <button class="page-link" onclick="loadBranches(${p})">${p}</button></li>`;
      }
    })
    .catch(() => {
      document.getElementById('branchTableBody').innerHTML =
        '<tr><td colspan="9" class="text-center text-danger">Failed to load branches.</td></tr>';
    });
}

function deleteBranch(id, name) {
  if (!confirm(`Delete branch "${name}"?\n\nNote: You cannot delete a branch that has active HR or staff.`)) return;
  fetch(`/api/branches/${id}`, { 
    method: 'DELETE',
    headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') },
    credentials: 'same-origin'
  })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        showToast(res.message, 'success');
        loadBranches(currentPage);
      } else {
        showToast(res.message || 'Delete failed.', 'danger');
      }
    });
}

function escHtml(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type = 'success') {
  const id = 'toast_' + Date.now();
  document.body.insertAdjacentHTML('beforeend',
    `<div id="${id}" class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" style="z-index:9999" role="alert">
      <div class="d-flex"><div class="toast-body">${msg}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
    </div>`);
  const t = new bootstrap.Toast(document.getElementById(id), {delay: 4000});
  t.show();
  setTimeout(() => document.getElementById(id)?.remove(), 5000);
}

// Search with debounce
document.getElementById('branchSearch').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => loadBranches(1), 400);
});

loadBranches();
</script>
<?php $this->endSection(); ?>
