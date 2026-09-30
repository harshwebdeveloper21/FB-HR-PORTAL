<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>
<style>
    @media (max-width: 767px) {
        .interviewsmbtn {
            font-size: 10px !important;
            padding: 8px !important;
            margin-top: 10px !important;
        }
    }

    .cke_notifications_area {
        display: none !important;
    }
</style>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Edit Experience Letter Template</h4>
                    <div class="d-flex gap-2">
                        <a href="<?= site_url('exprience-templates/preview-pdf/' . $id) ?>" target="_blank" class="btn btn-outline-danger interviewsmbtn">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Preview PDF
                        </a>
                        <a href="<?= site_url('exprience-templates-view') ?>" class="btn hr-btnbg interviewsmbtn">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Templates
                        </a>
                    </div>
                </div>

                <form id="offerTemplateForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">
                    <input type="hidden" name="id" id="id" value="<?= esc($id) ?>" />

                    <!-- Title -->
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label leave-sm-emp">Template Title <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="mdi mdi-note-text-outline fs-5"></i></span>
                                </div>
                                <input type="text" class="form-control" name="title" id="title" placeholder="Enter template title" required />
                            </div>
                            <small id="title-error" class="text-danger"></small>
                        </div>
                    </div>

                    <!-- Template Header Banner (Optional) -->
                    <div class="form-group row mt-3">
                        <label class="col-sm-2 col-form-label leave-sm-emp">Template Header (Optional Banner)</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="template_header" id="template_header" accept="image/*" />
                            <small class="text-muted d-block mt-1">If not uploaded, the company logo and address will automatically be used across the top of all pages.</small>
                            <small id="template_header-error" class="text-danger"></small>

                            <!-- Preview existing header -->
                            <div class="mt-2" id="headerPreviewContainer" style="display: none;">
                                <small class="text-muted d-block mb-1">Current Header Banner:</small>
                                <img id="existingHeaderPreview" src="" alt="Header Banner" class="img-fluid rounded border" style="max-height: 80px;" />
                            </div>
                        </div>
                    </div>

                    <!-- Template Footer Banner (Optional) -->
                    <div class="form-group row mt-3">
                        <label class="col-sm-2 col-form-label leave-sm-emp">Template Footer (Optional Banner)</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="template_footer" id="template_footer" accept="image/*" />
                            <small class="text-muted d-block mt-1">Optional banner to display at the bottom of pages.</small>
                            <small id="template_footer-error" class="text-danger"></small>

                            <!-- Preview existing footer -->
                            <div class="mt-2" id="footerPreviewContainer" style="display: none;">
                                <small class="text-muted d-block mb-1">Current Footer Banner:</small>
                                <img id="existingFooterPreview" src="" alt="Footer Banner" class="img-fluid rounded border" style="max-height: 50px;" />
                            </div>
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div class="form-group row mt-4">
                        <label class="col-sm-2 col-form-label leave-sm-emp">Letter Content <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <textarea id="content" name="content" class="d-none"></textarea>
                            <small id="content-error" class="text-danger"></small>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="text-end mt-4">
                        <a href="<?= site_url('exprience-templates-view') ?>" class="btn btn-secondary interviewsmbtn me-2">Cancel</a>
                        <button type="submit" class="btn hr-btnbg interviewsmbtn" id="submitBtn">
                            <i class="mdi mdi-check me-1"></i> Update Template
                        </button>
                    </div>
                </form>

                <!-- Dynamic Placeholders Helper Card -->
                <div class="card mt-4 border-info">
                    <div class="card-header bg-light-info py-2">
                        <strong class="text-primary"><i class="mdi mdi-information-outline me-1"></i> Available Dynamic Placeholders</strong>
                    </div>
                    <div class="card-body py-3">
                        <p class="small text-muted mb-2">These tags will automatically be replaced with actual employee and company details when generating letters:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled small mb-0">
                                    <li><code>{{salutation}}</code> → Mr. / Ms. (auto-detected from gender)</li>
                                    <li><code>{{employee_name}}</code> or <code>{{candidate_name}}</code> → Full name (e.g. Aryan Patel)</li>
                                    <li><code>{{title_employee_name}}</code> → Salutation + Name (e.g. Mr. Aryan Patel)</li>
                                    <li><code>{{designation}}</code> or <code>{{job_title}}</code> → Designation (e.g. Junior Web Developer)</li>
                                    <li><code>{{department}}</code> or <code>{{department_name}}</code> → Department name</li>
                                    <li><code>{{from_date}}</code> or <code>{{joining_date}}</code> → Start date (e.g. 3rd June 2024)</li>
                                    <li><code>{{address_1}}</code> or <code>{{employee_address}}</code> → Employee address</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled small mb-0">
                                    <li><code>{{to_date}}</code> or <code>{{leaving_date}}</code> → End date (e.g. 10th Dec 2025)</li>
                                    <li><code>{{his_her}}</code> → his / her (lowercase)</li>
                                    <li><code>{{he_she}}</code> → He / She</li>
                                    <li><code>{{him_her}}</code> → him / her</li>
                                    <li><code>{{signer_name}}</code> or <code>{{created_by}}</code> → Signer / HR name (e.g. Raj Singh)</li>
                                    <li><code>{{signer_designation}}</code> → Signer designation (e.g. Co-Founder / CTO / CEO)</li>
                                    <li><code>{{digital_signature}}</code> → Authorized digital signature image</li>
                                    <li><code>{{stamp}}</code> or <code>{{company_stamp}}</code> → Official company stamp / seal image</li>
                                    <li><code>{{signature_and_stamp}}</code> → Both digital signature & company stamp together</li>
                                    <li><code>{{company_name}}</code> / <code>{{company_address}}</code> → Company info</li>
                                    <li><code>{{current_date}}</code> or <code>{{today_date}}</code> → Today's date</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="responseMessage" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    const templateId = <?= json_encode($id) ?>;

    $(document).ready(function() {
        const toolbarConfig = [
            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
            { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
            { name: 'links', items: ['Link', 'Unlink'] },
            { name: 'insert', items: ['Table', 'HorizontalRule', 'SpecialChar'] },
            '/',
            { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'tools', items: ['Maximize', 'ShowBlocks', 'Source'] }
        ];

        const editorInstance = CKEDITOR.replace('content', {
            height: 380,
            removePlugins: 'elementspath',
            resize_enabled: false,
            toolbar: toolbarConfig,
            on: {
                instanceReady: function (evt) {
                    const customCss = `
                        body { font-family: "Times New Roman", Times, serif; font-size: 15px; line-height: 1.45; color: #000; }
                    `;
                    const styleEl = evt.editor.document.createElement('style');
                    styleEl.setAttribute('type', 'text/css');
                    styleEl.setText(customCss);
                    evt.editor.document.getHead().append(styleEl);
                }
            }
        });

        // Fetch existing template data
        $.ajax({
            url: "<?= site_url('api/exprience-templates/get-data') ?>/" + templateId,
            method: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    $('#title').val(data.title);
                    if (editorInstance) {
                        editorInstance.setData(data.content);
                    } else {
                        $('#content').val(data.content);
                    }

                    if (data.template_header) {
                        $('#existingHeaderPreview').attr('src', '<?= base_url("upload/templates/") ?>/' + data.template_header);
                        $('#headerPreviewContainer').show();
                    }
                    if (data.template_footer) {
                        $('#existingFooterPreview').attr('src', '<?= base_url("upload/templates/") ?>/' + data.template_footer);
                        $('#footerPreviewContainer').show();
                    }
                }
            }
        });

        $('#offerTemplateForm').on('submit', function(e) {
            e.preventDefault();

            for (let instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }

            $('.text-danger').text('');
            const formData = new FormData(this);

            const $btn = $('#submitBtn');
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

            $.ajax({
                url: "<?= site_url('api/exprience-templates/update-data') ?>/" + templateId,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Template updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '<?= site_url("exprience-templates-view") ?>';
                        });
                    } else if (response.errors) {
                        for (const [key, msg] of Object.entries(response.errors)) {
                            $('#' + key + '-error').text(msg);
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        for (const [key, msg] of Object.entries(xhr.responseJSON.errors)) {
                            $('#' + key + '-error').text(msg);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to update template.'
                        });
                    }
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>