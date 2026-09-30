<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    .capitalize-text {
        text-transform: capitalize;
    }

    @media (max-width: 767px) {
        .attendenceall {
            font-size: 9px !important;
            padding: 5.2px !important;
        }

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
            max-width: 26%;
        }

        .dataTables_filter label:before {
            content: "" !important;
        }

        #interviews-Table_length label {
            display: flex;
            align-items: center;
        }

        /* Hide the text inside the label */
        #interviews-Table_length label::first-text,
        #interviews-Table_length label::before {
            display: none !important;
        }

        /* Or a simpler and reliable trick */
        #interviews-Table_length label {
            font-size: 0;
            /* hide text */
        }

        #interviews-Table_length label input {
            font-size: 10px;
            /* reset font size for input */
        }

        #interviews-Table_filter label {
            font-size: 0;
        }

        #interviews-Table_filter input {
            font-size: 14px;
            /* Keep input font size normal */
        }

        #interviews-Table_length label {
            font-size: 0;
            /* hide all text inside the label */
        }

        #interviews-Table_length label select {
            font-size: 14px;
            /* restore font size for the dropdown */
        }

        /* .form-control {
            height: 0px !important;
        } */

        div.dataTables_wrapper div.dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
            width: 212px !important;
            height: 29px !important
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


                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                    <h4 class="card-title mb-0">Manage Interviews</h4>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="btnExportInterviews" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-file-excel iconfontsize"></i> Export
                        </button>
                        <a href="<?= base_url(
                            "/interviews",
                        ) ?>" class="btn hr-btnbg attendenceall text-nowrap">
                            <i class="mdi mdi-plus iconfontsize"></i> Add Interview
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped w-100" id="interviews-Table">
                        <thead class="table-light">
                            <tr>
                                <th>Candidate Name</th>
                                <th class="desktop-only-col">Job Title</th>
                                <th class="desktop-only-col">Schedule Date</th>
                                <th class="desktop-only-col">Convert To Employee</th>
                                <th class="desktop-only-col">Status</th>
                                <th class="desktop-only-col action-column" style="width: 100px;">Action</th>
                                <th class="mobile-expand-col" style="width: 50px;">Details</th>
                            </tr>
                        </thead>
                        <tbody id="interviews-Table-Body">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const token = localStorage.getItem('token'); // JWT token from login

        // Fetch interviews when the page loads
        fetch('/api/interviews', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        })
            .then((response) => response.json())
            .then((responseData) => {
                if (responseData.status === 'success') {
                    const interviews = responseData.data;
                    let tableRows = '';

                    interviews.forEach((interview, index) => {
                        tableRows += `
                     <tr data-id="${interview.id}">
                        <td class="capitalize-text">
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <div style="flex: 1;">
                                    <a href="/interview/display/${interview.id}" class="text-decoration-none text-dark fw-bold">${interview.candidate_name || 'Candidate'}</a>
                                    <div class="expanded-details" id="interview-details-${interview.id}">
                                        <div class="detail-row">
                                            <span class="detail-label">Job Title:</span>
                                            <span class="detail-value">${interview.job_title || 'N/A'}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Schedule Date:</span>
                                            <span class="detail-value">${interview.schedule_date || 'N/A'}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Status:</span>
                                            <span class="detail-value capitalize-text">${interview.status || 'N/A'}</span>
                                        </div>
                                        <div class="detail-actions">
                                            <a href="/interview/display/${interview.id}" class="btn btn-sm btn-info text-white"><i class="mdi mdi-eye"></i> View</a>
                                            <a href="/interviews/${interview.id}" class="btn btn-sm btn-success text-white"><i class="mdi mdi-pencil"></i> Edit</a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteInterviewById(${interview.id}, '${interview.status || ''}')"><i class="mdi mdi-delete"></i> Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="desktop-only-col capitalize-text">${interview.job_title || 'N/A'}</td>
                        <td class="desktop-only-col">${interview.schedule_date || 'N/A'}</td>
                        <td class="desktop-only-col py-1">
                            <select class="form-select convert-select shadow-none" data-id="${interview.id}" style="height: 32px; font-size: 12px; padding: 2px 8px;">
                                <option value="0" ${interview.convert_to_employee != 1 ? 'selected' : ''}>No</option>
                                <option value="1" ${interview.convert_to_employee == 1 ? 'selected' : ''}>Yes</option>
                            </select>
                        </td>
                        <td class="desktop-only-col py-1">
                            <select class="form-select status-select capitalize-text shadow-none" data-id="${interview.id}" style="height: 32px; font-size: 12px; padding: 2px 8px;">
                                <option value="scheduled" ${interview.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                <option value="completed" ${interview.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${interview.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </td>
                        <td class="desktop-only-col">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <a href="/interview/display/${interview.id}" class="text-primary fs-5" title="View"><i class="mdi mdi-eye"></i></a>
                                <a href="/interviews/${interview.id}" class="text-success fs-5" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                <a href="javascript:void(0);" class="text-danger fs-5" title="Delete"
                                   onclick="deleteInterviewById(${interview.id}, '${interview.status || ''}')">
                                   <i class="mdi mdi-delete"></i>
                                </a>
                            </div>
                        </td>
                        <td class="mobile-expand-col text-center">
                            <button type="button" class="expand-toggle" data-target="interview-details-${interview.id}" aria-label="Expand details"></button>
                        </td>
                    </tr>
                    `;
                    });
                    $('#interviews-Table-Body').html(tableRows);
                    if ($.fn.DataTable.isDataTable('#interviews-Table')) {
                        $('#interviews-Table').DataTable().clear().destroy();
                    }
                    const dt = $('#interviews-Table').DataTable({
                        order: [[2, 'desc']],
                        columnDefs: [
                            {
                                targets: [4, 5],
                                orderable: false,
                                searchable: false
                            }
                        ],
                        language: {
                            search: "",
                            searchPlaceholder: "Search"
                        }
                    });

                    dt.on('draw', function() {
                        if (typeof applyMobileTableVisibility === 'function') {
                            applyMobileTableVisibility();
                        }
                    });

                    if (typeof applyMobileTableVisibility === 'function') {
                        applyMobileTableVisibility();
                    }

                } else {
                    console.error('Failed to fetch leave types:', responseData.message);
                }
            })
            .catch((error) => {
                console.error('Error fetching leave types:', error);
            });
    });
    $(document).on('change', '.status-select', function () {
        const token = localStorage.getItem('token'); // JWT token from login
        const interviewId = $(this).data('id');
        const newStatus = $(this).val();
        const $select = $(this);
        const prevStatus = $select.data('prev-status') || $select.find('option[selected]').val(); // Store previous status

        // If the previous status was already "completed", prevent changes
        if (prevStatus === 'completed') {
            Swal.fire({
                icon: 'warning',
                title: 'Already Completed!',
                text: 'This interview has already been marked as completed and cannot be changed.',
                timer: 3000,
                showConfirmButton: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'hr-btnbg',

                }
            });

            // Revert dropdown to "completed"
            $select.val('completed');
            return;
        }

        // If changing status to "completed", show confirmation popup (only first time)
        if (newStatus === 'completed') {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to mark this interview as completed? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete it!',
                cancelButtonText: 'No, cancel',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'hr-btnbg',
                    cancelButton: 'hr-btnbg',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with status update
                    updateInterviewStatus(interviewId, newStatus, token, $select);
                } else {
                    // Revert dropdown back to previous value if cancelled
                    $select.val(prevStatus);
                }
            });
        } else {
            // Directly update status for other values
            updateInterviewStatus(interviewId, newStatus, token, $select);
        }
    });

    // Function to update interview status
    function updateInterviewStatus(interviewId, newStatus, token, $select) {
        const csrfName = $('meta[name="csrf-token"]').attr('data-name');
        const csrfHash = $('meta[name="csrf-token"]').attr('content');
        const payload = {
            status: newStatus
        };
        payload[csrfName] = csrfHash; // Add CSRF token to body
        fetch(`/api/interviews/${interviewId}/status`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Interview status updated successfully!',
                        timer: 2000,
                        showConfirmButton: false,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'hr-btnbg',

                        }
                    });

                    // If status is changed to "completed", prevent further changes
                    if (newStatus === 'completed') {
                        setTimeout(() => location.reload(), 2000); // Reload page after success
                    }

                    // Store the new status as the previous status
                    $select.data('prev-status', newStatus);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to update status'
                    });

                    // Revert to previous status in case of failure
                    $select.val($select.data('prev-status'));
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while updating the status.'
                });

                // Revert to previous status in case of error
                $select.val($select.data('prev-status'));
            });
    }

    $(document).on('change', '.convert-select', function () {
        const token = localStorage.getItem('token');
        const interviewId = $(this).data('id');
        const newVal = $(this).val();
        
        const csrfName = $('meta[name="csrf-token"]').attr('data-name');
        const csrfHash = $('meta[name="csrf-token"]').attr('content');
        const payload = {
            convert_to_employee: newVal
        };
        payload[csrfName] = csrfHash;

        fetch(`/api/interviews/${interviewId}/convert`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Conversion status updated successfully!',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        confirmButton: 'hr-btnbg',
                    }
                });
            } else {
                Swal.fire('Error!', data.message || 'Failed to update.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error!', 'An unexpected error occurred.', 'error');
        });
    });



    // Function to handle interview delete — shows extra warning for completed interviews
    window.deleteInterviewById = function(interviewId, status) {
        if (!interviewId) return;
        status = (status || '').toLowerCase();
        const csrfName = $('meta[name="csrf-token"]').attr('data-name') || 'csrf_test_name';
        const csrfHash = $('meta[name="csrf-token"]').attr('content') || '';

        // Helper: actually call the delete API
        function performDelete() {
            $.ajax({
                url: `/api/interviews/${interviewId}`,
                type: 'DELETE',
                data: JSON.stringify({ [csrfName]: csrfHash }),
                contentType: 'application/json',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'X-CSRF-TOKEN': csrfHash
                },
                success: function(responseData) {
                    if (responseData.status === 'success') {
                        $(`tr[data-id="${interviewId}"]`).remove();
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The interview has been deleted successfully.',
                            icon: 'success',
                            buttonsStyling: false,
                            customClass: { confirmButton: 'btn hr-btnbg' }
                        });
                    } else {
                        const errorMsg = responseData.message || responseData.messages?.error || 'Only admins can delete interviews.';
                        Swal.fire({
                            title: 'Cannot Delete',
                            text: errorMsg,
                            icon: 'error',
                            buttonsStyling: false,
                            customClass: { confirmButton: 'btn hr-btnbg' }
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting the interview.',
                        icon: 'error',
                        buttonsStyling: false,
                        customClass: { confirmButton: 'btn hr-btnbg' }
                    });
                }
            });
        }

        if (status === 'completed') {
            // ⚠️ Completed interview — show strong two-step warning
            Swal.fire({
                title: '⚠️ Warning: Completed Interview',
                html: `<p>This interview has already been <strong>completed</strong>.</p>
                       <p>Deleting it may affect onboarding and candidate records linked to this interview.</p>
                       <p><strong>Are you absolutely sure you want to proceed?</strong></p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, I understand — Delete',
                cancelButtonText: 'No, Keep It',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn hr-btnbg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete();
                }
            });
        } else {
            // Standard confirmation for scheduled / cancelled
            Swal.fire({
                title: 'Are you sure?',
                text: 'This interview record will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn hr-btnbg me-2',
                    cancelButton: 'btn hr-btnbg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete();
                }
            });
        }
    };

    window.deleteInterview = function(event) {
        if (event) event.preventDefault();
        const anchor = event.target.closest('a') || event.target.closest('button');
        if (anchor) {
            const interviewId = anchor.getAttribute('data-id');
            const status = anchor.getAttribute('data-status');
            deleteInterviewById(interviewId, status);
        }
    };

    // 📥 Export to Excel functionality
    document.getElementById('btnExportInterviews')?.addEventListener('click', function () {
        const btn = this;
        const search = $('#interviews-Table_filter input').val() || '';
        const token = localStorage.getItem('token');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Exporting...';

        const queryParams = new URLSearchParams({ search: search });

        fetch(`<?= base_url('api/interview/export') ?>?${queryParams.toString()}`, {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}` }
        })
        .then(async response => {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-file-excel iconfontsize"></i> Export';
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
            a.download = `Interviews_${dateStr}.xlsx`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                text: 'Interview list exported to Excel successfully.',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-file-excel iconfontsize"></i> Export';
            Swal.fire('Export Error', error.message || 'Failed to export interviews', 'error');
        });
    });
</script>
<?= $this->endSection() ?>