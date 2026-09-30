<?php
$content = file_get_contents('app/Views/interview/view.php');

$searchHtml = "</div>

<script>";

$replaceHtml = "</div>

<!-- Convert To Employee Modal -->
<div class=\"modal fade\" id=\"convertToEmployeeModal\" tabindex=\"-1\" aria-labelledby=\"convertToEmployeeModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <form id=\"convertToEmployeeForm\">
                <div class=\"modal-header\">
                    <h5 class=\"modal-title\" id=\"convertToEmployeeModalLabel\">Convert to Employee</h5>
                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                </div>
                <div class=\"modal-body\">
                    <input type=\"hidden\" id=\"convert_interview_id\" name=\"interview_id\">
                    <div class=\"mb-3\">
                        <label class=\"form-label\">Branch *</label>
                        <select class=\"form-select shadow-none\" id=\"convert_branch_id\" name=\"branch_id\" required>
                            <option value=\"\">Select Branch</option>
                            <?php if(!empty(\$branches)): foreach (\$branches as \$b): ?>
                                <option value=\"<?= \$b['id'] ?>\"><?= htmlspecialchars(\$b['branch_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class=\"mb-3\">
                        <label class=\"form-label\">Department *</label>
                        <select class=\"form-select shadow-none\" id=\"convert_department_id\" name=\"department_id\" required>
                            <option value=\"\">Select Department</option>
                            <?php if(!empty(\$departments)): foreach (\$departments as \$d): ?>
                                <option value=\"<?= \$d['id'] ?>\"><?= htmlspecialchars(\$d['department_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                <div class=\"modal-footer\">
                    <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Cancel</button>
                    <button type=\"submit\" class=\"btn hr-btnbg text-white\" id=\"convertSubmitBtn\">Convert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>";

$content = str_replace($searchHtml, $replaceHtml, $content);


$searchJs = "    $(document).on('change', '.convert-select', function () {
        const token = localStorage.getItem('token');
        const interviewId = \$(this).data('id');
        const newVal = \$(this).val();
        
        const csrfName = \$('meta[name=\"csrf-token\"]').attr('data-name');
        const csrfHash = \$('meta[name=\"csrf-token\"]').attr('content');
        const payload = {
            convert_to_employee: newVal
        };
        payload[csrfName] = csrfHash;

        fetch(`/api/interviews/\${interviewId}/convert`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer \${token}`,
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
    });";


$replaceJs = "    $(document).on('change', '.convert-select', function () {
        const interviewId = \$(this).data('id');
        const newVal = \$(this).val();
        const \$select = \$(this);
        
        if (newVal == '1') {
            // Open modal to get branch and department
            \$('#convert_interview_id').val(interviewId);
            \$('#convert_branch_id').val('');
            \$('#convert_department_id').val('');
            \$('#convertToEmployeeModal').modal('show');

            // Store reference to select so we can revert it if cancelled
            \$('#convertToEmployeeModal').data('select', \$select);
            \$('#convertToEmployeeModal').data('prev', '0');
        } else {
            // Un-convert
            submitConversion(interviewId, '0', null, null, \$select);
        }
    });

    // Revert select if modal is closed without saving
    \$('#convertToEmployeeModal').on('hidden.bs.modal', function () {
        const \$select = \$(this).data('select');
        if (\$select && \$select.val() == '1') { // If it wasn't successfully saved
            \$select.val(\$(this).data('prev'));
        }
    });

    \$('#convertToEmployeeForm').on('submit', function(e) {
        e.preventDefault();
        const interviewId = \$('#convert_interview_id').val();
        const branchId = \$('#convert_branch_id').val();
        const departmentId = \$('#convert_department_id').val();
        const \$select = \$('#convertToEmployeeModal').data('select');

        if(!branchId || !departmentId) {
            Swal.fire('Error!', 'Please select both branch and department.', 'error');
            return;
        }

        submitConversion(interviewId, '1', branchId, departmentId, \$select);
    });

    function submitConversion(interviewId, convertVal, branchId, departmentId, \$select) {
        const token = localStorage.getItem('token');
        const csrfName = \$('meta[name=\"csrf-token\"]').attr('data-name');
        const csrfHash = \$('meta[name=\"csrf-token\"]').attr('content');
        const payload = {
            convert_to_employee: convertVal,
            branch_id: branchId,
            department_id: departmentId
        };
        payload[csrfName] = csrfHash;

        fetch(`/api/interviews/\${interviewId}/convert`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer \${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                \$('#convertToEmployeeModal').modal('hide');
                \$('#convertToEmployeeModal').data('select', null); // clear so it doesn't revert
                
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
                if(\$select) \$select.val(convertVal == '1' ? '0' : '1');
            }
        })
        .catch(error => {
            Swal.fire('Error!', 'An unexpected error occurred.', 'error');
            if(\$select) \$select.val(convertVal == '1' ? '0' : '1');
        });
    }";

$content = str_replace($searchJs, $replaceJs, $content);
file_put_contents('app/Views/interview/view.php', $content);
echo "view.php updated\n";
