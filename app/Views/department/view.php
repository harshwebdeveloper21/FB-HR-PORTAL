<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    @media (max-width: 767px) {
        /* .attendenceall {
            font-size: 12px !important;
            padding: 5.3px !important;
        } */

        .iconfontsize {
            font-size: 11px !important;
        }

        .cart-sm-title {
            font-size: 12px !important;
            margin-bottom: 5px !important;
        }

        .dataTables_length {
            margin-left: .1rem !important;
            margin-bottom: .5rem !important;
            font-size: 12px !important;
            float: left !important;
        }

        .dataTables_filter {
            font-size: 12px !important;
            float: left !important;
            /* margin-left: -5rem !important;  */
        }

        .col-sm-12.col-md-6 {
            flex: 0 0 25%;
            max-width: 18%;
        }

        .dataTables_filter label:before {
            content: "" !important;
        }

        #department-table_length label {
            display: flex;
            align-items: center;
            margin-top: 1px;
        }

        /* Hide the text inside the label */
        #department-table_length::first-text,
        #department-table_length label::before {
            display: none !important;
        }

        /* Or a simpler and reliable trick */
        #department-table_length label {
            font-size: 0;
            /* hide text */
        }

        #department-table_length label input {
            font-size: 10px;
            /* reset font size for input */
        }

        #department-table_length label {
            font-size: 0px;
            /* hide all text inside the label */
        }

        #department-table_filter label {
            font-size: 0;
        }

        #department-table_filter input {
            font-size: 14px;
            /* Keep input font size normal */
        }

        #department-table_lengthh label select {
            font-size: 14px;
            /* restore font size for the dropdown */
        }

        div.dataTables_wrapper div.dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
            width: 212px !important;
            height: 32px !important
        }

        .custom-select {
            height: 26px !important;
            width: 57px !important;
        }


    }
</style>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-md-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Manage Departments</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/department-managers" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-account-tie iconfontsize"></i> Department Managers
                        </a>
                        <button type="button" id="btnExportDepartment" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-file-excel iconfontsize"></i> Export
                        </button>
                        <a href="/department" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-plus iconfontsize"></i> Add Department
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="department-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Department</th>
                                <th>Branch</th>
                                <th>Department Manager</th>
                                <th class="text-center">Staff Count</th>
                                <th class="desktop-only-col">Action</th>
                                <th class="mobile-expand-col" style="width: 50px;">Details</th>
                            </tr>
                        </thead>
                        <tbody id="department-table-body">
                            <!-- Table rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const token = localStorage.getItem('token'); // JWT token

        // Function to fetch departments from the API
        function fetchDepartments() {
            $.ajax({
                url: '/api/department', // URL to get all departments
                type: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                success: function(response) {
                    if (response.departments) {
                        const departments = response.departments;
                        let tableRows = '';
                        departments.forEach((department, index) => {
                            const formattedName = department.department_name.charAt(0).toUpperCase() + department.department_name.slice(1).toLowerCase();
                            const branchName = department.branch_name ? department.branch_name : '<span class="text-muted small">All / Unassigned</span>';
                            const managerBadge = department.manager_name
                                ? `<span class="badge bg-warning text-dark"><i class="mdi mdi-account-tie me-1"></i>${department.manager_name}</span>`
                                : `<span class="badge bg-light text-muted border"><i class="mdi mdi-plus me-1"></i>Not Assigned</span>`;

                            tableRows += `
                        <tr data-id="${department.id}">
                            <td>${index + 1}</td>
                            <td>
                                <div style="flex: 1;">
                                    <strong>${formattedName}</strong>
                                    <div class="expanded-details" id="dept-details-${department.id}" onclick="event.stopPropagation();">
                                        <div class="detail-row mb-1">
                                            <span class="detail-label">Branch:</span>
                                            <span class="detail-value">${branchName}</span>
                                        </div>
                                        <div class="detail-row mb-2">
                                            <span class="detail-label">Manager:</span>
                                            <span class="detail-value">${managerBadge}</span>
                                        </div>
                                        <div class="detail-actions">
                                            <a href="/department?id=${department.id}" class="btn btn-sm btn-warning"><i class="mdi mdi-pencil"></i> Edit</a>
                                            <a href="#" class="btn btn-sm btn-danger delete-department" data-id="${department.id}"><i class="mdi mdi-delete"></i> Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>${branchName}</td>
                            <td>${managerBadge}</td>
                            <td class="text-center"><span class="badge bg-primary">${department.staff_count || 0}</span></td>
                            <td class="desktop-only-col" style="align-items: center; gap: 8px;">
                                <a href="/department?id=${department.id}" class="text-warning fs-5 me-2" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                <a href="#" class="text-danger fs-5 delete-department" data-id="${department.id}" title="Delete"><i class="mdi mdi-delete"></i></a>
                            </td>
                            <td class="mobile-expand-col text-center">
                                <button type="button" class="expand-toggle" data-target="dept-details-${department.id}" aria-label="Expand details"></button>
                            </td>
                        </tr>
                    `;
                        });
                        $('#department-table-body').html(tableRows);
                        if ($.fn.DataTable.isDataTable('#department-table')) {
                            $('#department-table').DataTable().clear().destroy();
                        }
                        $('#department-table').DataTable({
                            columnDefs: [
                                {
                                    targets: [5, 6], // Action and mobile expand columns
                                    orderable: false,
                                    searchable: false
                                }
                            ],
                            language: {
                                search: "",
                                searchPlaceholder: "Search"
                            }
                        });
                        // Apply mobile visibility
                        if (typeof applyMobileTableVisibility === 'function') {
                            applyMobileTableVisibility();
                        }
                        // $('#department-table').DataTable();
                    } else {
                        Swal.fire('Error', 'Failed to load departments', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Failed to fetch departments', 'error');
                }
            });
        }


        // Call the function to populate departments on page load
        fetchDepartments();

        // Handle the delete button click
        $(document).on('click', '.delete-department', function(e) {
            e.preventDefault(); // Prevent default anchor behavior

            const departmentId = $(this).data('id'); // Get the department ID
            // Confirm deletion using SweetAlert
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'hr-btnbg',
                    cancelButton: 'hr-btnbg',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send the DELETE request
                    $.ajax({
                        url: `/api/department/${departmentId}`, // URL to delete the department
                        type: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                        },
                        success: function(responseData) {
                            if (responseData.message === 'Department deleted successfully!') {
                                Swal.fire('Deleted!', 'The department has been deleted.', 'success')
                                    .then(() => {
                                        // Remove the deleted department's row from the table
                                        $(`tr[data-id="${departmentId}"]`).remove();
                                    });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    responseData.message, // Show the actual message from server
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'There was an error deleting the city. Please try again.';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message; // Get actual error message
                            }

                            Swal.fire(
                                'Error!',
                                errorMessage, // Show dynamic error message
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // 📥 Export to Excel functionality
        $('#btnExportDepartment').on('click', function () {
            const $btn = $(this);
            const search = $('#department-table_filter input').val() || '';
            const token = localStorage.getItem('token');

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...');

            const queryParams = new URLSearchParams({ search: search });

            fetch(`<?= base_url('api/department/export') ?>?${queryParams.toString()}`, {
                method: 'GET',
                headers: { 'Authorization': `Bearer ${token}` }
            })
            .then(async response => {
                $btn.prop('disabled', false).html('<i class="mdi mdi-file-excel iconfontsize"></i> Export');
                if (!response.ok) {
                    const err = await response.json().catch(() => ({ message: 'Export failed' }));
                    throw new Error(err.message || 'Export failed');
                }
                return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const dateStr = new Date().toISOString().slice(0, 10);
                a.download = `Departments_${dateStr}.xlsx`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
                Swal.fire({
                    icon: 'success',
                    title: 'Exported!',
                    text: 'Departments exported to Excel successfully.',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                $btn.prop('disabled', false).html('<i class="mdi mdi-file-excel iconfontsize"></i> Export');
                Swal.fire('Export Error', error.message || 'Failed to export departments', 'error');
            });
        });
    });
</script>

<?= $this->endSection() ?>
