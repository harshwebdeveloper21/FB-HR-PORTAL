<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>
<style>
    .cke_notifications_area{
    display: none !important;
}
</style>
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Template</h4>

                <!-- <form id="offerTemplateForm" method="post"> -->
                <form id="offerTemplateForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrfToken">
                    <input type="hidden" name="id" id="id" /> <!-- For editing -->

                    <!-- Title -->
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Template Title</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="mdi mdi-note-text-outline fs-5"></i></span>
                                </div>
                                <input type="text" class="form-control" name="title" id="title"
                                    placeholder="Enter template title (e.g. JOINING LETTER, Offer Letter)" />
                            </div>
                            <small id="title-error" class="text-danger"></small>
                        </div>
                    </div>



                    <!-- Template Header File Upload -->
                    <div class="form-group row mt-3">
                        <label class="col-sm-2 col-form-label">Template Header</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="template_header" id="template_header"
                                accept="image/*" />
                            <small class="text-muted d-block mt-1"><i class="mdi mdi-information-outline"></i> Upload header banner/image to display at the top of every page in the generated PDF.</small>
                            <small id="template_header-error" class="text-danger"></small>

                            <!-- Preview the uploaded header here -->
                            <div class="mt-2">
                                <img id="existingHeaderPreview" src="" alt="Template Header" class="img-fluid rounded border p-1"
                                    style="max-height: 80px; display: none;" onerror="this.style.display='none';" />
                            </div>
                        </div>
                    </div>

                    <!-- Template Footer File Upload -->
                    <div class="form-group row mt-3">
                        <label class="col-sm-2 col-form-label">Template Footer</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="template_footer" id="template_footer"
                                accept="image/*" />
                            <small class="text-muted d-block mt-1"><i class="mdi mdi-information-outline"></i> Upload footer banner/image to display at the bottom of every page in the generated PDF.</small>
                            <small id="template_footer-error" class="text-danger"></small>

                            <!-- Preview the uploaded footer here -->
                            <div class="mt-2">
                                <img id="existingFooterPreview" src="" alt="Template Footer" class="img-fluid rounded border p-1"
                                    style="max-height: 80px; display: none;" onerror="this.style.display='none';" />
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Template Pages Container -->
                    <div class="form-group row mt-4">
                        <label class="col-sm-2 col-form-label">
                            <strong>Design Template</strong>
                            <small class="d-block text-muted">Page-wise document editor</small>
                        </label>
                        <div class="col-sm-10">
                            <div id="template-pages-container">
                                <!-- Dynamic pages will be populated here -->
                            </div>

                            <!-- Add More Page Button -->
                            <div class="mt-2 mb-4">
                                <button type="button" class="btn btn-outline-primary fw-bold d-inline-flex align-items-center gap-2 py-2 px-3" id="addPageBtn" style="border-radius: 8px;">
                                    <i class="mdi mdi-plus-circle-outline fs-5"></i> Add More Page
                                </button>
                                <span class="text-muted small ms-2">Click to add Page 3, Page 4 or more pages to the document</span>
                            </div>
                        </div>
                    </div>

                    <!-- Display dynamic fields below the content -->
                    <div class="form-group row mt-4">
                        <label class="col-sm-2 col-form-label"></label>
                        <div class="col-sm-10">
                            <div class="alert alert-info mt-4">
                                <strong>Note:</strong> You can use dynamic tags (placeholders) inside the content. These tags will be replaced with actual employee/candidate data when the letter is generated.
                                <ul class="mb-0 mt-2">
                                    <li><code>{{candidate_name}}</code> or <code>{{employee_name}}</code> → Candidate's full name</li>
                                    <li><code>{{job_title}}</code> or <code>{{designation}}</code> → Job title / designation</li>
                                    <li><code>{{department_name}}</code> or <code>{{department}}</code> → Department name</li>
                                    <li><code>{{start_date}}</code> or <code>{{joining_date}}</code> → Candidate's start date from onboarding</li>
                                    <li><code>{{salary}}</code> → Salary / compensation terms</li>
                                    <li><code>{{docu_submitted}}</code> → Submitted documents list from onboarding</li>
                                    <li><code>{{reporting_to}}</code> or <code>{{supervisor}}</code> → Reporting manager / supervisor</li>
                                    <li><code>{{working_hours}}</code> → Working hours (e.g. 09:30 AM till 06:15 PM)</li>
                                    <li><code>{{today_date}}</code> or <code>{{current_date}}</code> → Current date</li>
                                    <li><code>{{company_name}}</code> → Company name</li>
                                    <li><code>{{company_address}}</code> → Company address</li>
                                    <li><code>{{company_phone}}</code> / <code>{{company_email}}</code> → Company contact details</li>
                                    <li><code>{{created_by}}</code> or <code>{{signer_name}}</code> → Signer / HR / Creator name</li>
                                    <li><code>{{creator_designation}}</code> or <code>{{signer_designation}}</code> → Signer designation</li>
                                    <li><code>{{digital_signature}}</code> → Authorized digital signature image</li>
                                    <li><code>{{stamp}}</code> or <code>{{company_stamp}}</code> → Official company stamp / seal image</li>
                                    <li><code>{{signature_and_stamp}}</code> → Both digital signature & company stamp together</li>
                                    <li><code>{{email}}</code> / <code>{{phone_number}}</code> → Candidate's email & phone</li>
                                </ul>
                                ✨ Example usage: <br>
                                <em>"We are pleased to offer <code>{{candidate_name}}</code> the role of <code>{{job_title}}</code> starting from <code>{{start_date}}</code>."</em>
                            </div>
                        </div>
                    </div>
                    <!-- Submit -->
                    <div class="text-end mt-4">
                        <a href="<?= site_url('offer-templates-view') ?>" class="btn hr-btnbg">Back</a>
                        <button type="submit" class="btn hr-btnbg" id="submitBtn">Submit</button>
                    </div>
                </form>

                <div id="responseMessage" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    const templateId = <?= json_encode($id) ?>;

    $(document).ready(function () {
        if (!CKEDITOR.stylesSet.get('custom_offer_styles')) {
            CKEDITOR.stylesSet.add('custom_offer_styles', [
                { name: 'Document Title', element: 'h3', styles: { 'text-decoration': 'underline', 'text-transform': 'uppercase' } }
            ]);
        }

        if (!CKEDITOR.plugins.get('bulletDropdown')) {
            CKEDITOR.plugins.add('bulletDropdown', {
                requires: 'richcombo',
                init: function(editor) {
                    editor.ui.addRichCombo('BulletDropdown', {
                        label: 'Bullets ▼',
                        title: 'Select Bullet Style',
                        toolbar: 'insert',
                        panel: {
                            css: [ CKEDITOR.skin.getPath('editor') ].concat( editor.config.contentsCss ),
                            multiSelect: false
                        },
                        init: function() {
                            this.startGroup('Standard Bullets');
                            this.add('disc',   '● Filled Circle',   'Disc (●)');
                            this.add('circle', '○ Empty Circle',    'Circle (○)');
                            this.add('square', '■ Filled Square',   'Square (■)');
                            this.startGroup('Custom Icons');
                            this.add('◆', '◆ Diamond',          'Diamond (◆)');
                            this.add('➢', '➢ 3D Arrow',         '3D Arrow (➢)');
                            this.add('➤', '➤ Right Arrow',      'Right Arrow (➤)');
                            this.add('✓', '✓ Checkmark',        'Checkmark (✓)');
                            this.add('★', '★ Star',             'Star (★)');
                            this.add('▪', '▪ Small Square',     'Small Square (▪)');
                        },
                        onClick: function(value) {
                            var isStandard = (value === 'disc' || value === 'circle' || value === 'square');

                            // CKEditor 4: the dropdown panel steals focus from the editor.
                            // We must restore focus + the exact selection before doing anything.
                            editor.focus();

                            var applyBullet = function() {
                                editor.fire('saveSnapshot');

                                var selection = editor.getSelection();
                                var startEl = selection ? selection.getStartElement() : null;

                                if (!startEl) return;

                                // Find closest <ul> ancestor (handles cursor inside li, span, etc.)
                                var list = startEl.getAscendant('ul', true) ||
                                           startEl.getAscendant('ol', true);

                                var doInject = function(list) {
                                    if (!list) return;

                                    if (isStandard) {
                                        list.setStyle('list-style-type', value);
                                        list.removeAttribute('data-bullet-char');
                                        var items = list.find('li');
                                        for (var i = 0; i < items.count(); i++) {
                                            var spans = items.getItem(i).find('span.custom-bullet-char');
                                            for (var s = 0; s < spans.count(); s++) {
                                                spans.getItem(s).remove();
                                            }
                                        }
                                    } else {
                                        // Force list-style: none with !important via native DOM
                                        // (CKEditor's setStyle cannot add !important)
                                        list.$.style.setProperty('list-style', 'none', 'important');
                                        list.$.style.setProperty('padding-left', '0', 'important');
                                        list.$.style.setProperty('margin-left', '0', 'important');
                                        
                                        // Add helper class for CSS backup
                                        var ulClass = (list.getAttribute('class') || '').replace(/\bcustom-bullet-list\b/g, '').trim();
                                        list.setAttribute('class', ulClass ? ulClass + ' custom-bullet-list' : 'custom-bullet-list');

                                        var items = list.find('li');
                                        for (var i = 0; i < items.count(); i++) {
                                            var li = items.getItem(i);

                                            // Remove any previous bullet span
                                            var old = li.find('span.custom-bullet-char');
                                            for (var s = 0; s < old.count(); s++) {
                                                old.getItem(s).remove();
                                            }

                                            // Force list-style: none on each LI too
                                            li.$.style.setProperty('list-style', 'none', 'important');
                                            li.$.style.setProperty('margin-bottom', '6px', 'important');

                                            var span = editor.document.createElement('span');
                                            span.setAttribute('class', 'custom-bullet-char');
                                            span.setAttribute('style',
                                                'display:inline-block; margin-right:6px; ' +
                                                'font-size:1.4em; line-height:1; vertical-align:top; ' +
                                                'font-family:"Segoe UI Symbol","DejaVu Sans",Arial,sans-serif; color:#000;');
                                            span.setHtml(value);
                                            li.$.insertBefore(span.$, li.$.firstChild);
                                        }
                                    }
                                    editor.fire('saveSnapshot');
                                };

                                if (!list || list.getName() === 'ol') {
                                    // Selection is on plain text — convert to list first
                                    editor.execCommand('bulletedlist');
                                    // Wait one tick for the DOM to be updated by execCommand
                                    setTimeout(function() {
                                        var sel2 = editor.getSelection();
                                        var el2 = sel2 ? sel2.getStartElement() : null;
                                        var newList = el2 ? (el2.getAscendant('ul', true) || null) : null;
                                        doInject(newList);
                                    }, 50);
                                } else {
                                    doInject(list);
                                }
                            };

                            // Give focus a moment to settle before reading the selection
                            setTimeout(applyBullet, 10);
                        }
                    });
                }
            });
        }

        const toolbarConfig = [
            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
            { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
            { name: 'links', items: ['Link', 'Unlink'] },
            { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'BulletDropdown', 'PageBreak'] },
            '/',
            { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'tools', items: ['Maximize', 'ShowBlocks', 'Source'] }
        ];

        let pageCounter = 0;

        function createPageBlock(pageNumber, initialContent) {
            pageCounter++;
            const pageId = pageCounter;
            const isFirstPage = (pageNumber === 1);
            const textareaId = 'page_editor_' + pageId;

            const blockHtml = `
                <div class="template-page-card mb-4 p-3 border rounded bg-white shadow-sm" id="page-card-${pageId}">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="mdi mdi-file-document-outline me-1 text-primary"></i> Design Template Page <span class="page-num-text">${pageNumber}</span>
                        </h6>
                        ${!isFirstPage ? `
                            <button type="button" class="btn btn-sm btn-outline-danger remove-page-btn" data-page-id="${pageId}">
                                <i class="mdi mdi-delete-outline"></i> Remove Page
                            </button>
                        ` : '<span class="badge bg-light text-muted border">Page 1 (Main)</span>'}
                    </div>
                    <div>
                        <textarea id="${textareaId}" class="page-editor-textarea form-control"></textarea>
                    </div>
                </div>
            `;

            $('#template-pages-container').append(blockHtml);

            // Initialize CKEditor
            CKEDITOR.replace(textareaId, {
                height: 380,
                removePlugins: 'elementspath',
                removeButtons: '',
                extraPlugins: 'bulletDropdown',
                resize_enabled: false,
                allowedContent: true,
                entities: false,
                basicEntities: false,
                stylesSet: 'custom_offer_styles',
                toolbar: toolbarConfig,
                // contentsCss ensures our bullet rules survive setData() reloads
                contentsCss: [
                    'https://cdn.ckeditor.com/4.22.1/standard/contents.css',
                    'data:text/css,' + encodeURIComponent([
                        'body { font-family: "Times New Roman", Times, serif; font-size: 15px; line-height: 1.45; color: #000; }',
                        'ul.custom-bullet-list { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }',
                        'ul.custom-bullet-list li { list-style: none !important; margin-bottom: 6px !important; }',
                        'span.custom-bullet-char { display: inline-block !important; margin-right: 6px !important; font-size: 1.4em !important; line-height: 1 !important; vertical-align: top !important; font-family: "Segoe UI Symbol", "DejaVu Sans", Arial, sans-serif !important; color: #000 !important; }'
                    ].join(' '))
                ],
                on: {
                    instanceReady: function (evt) {
                        if (initialContent) {
                            evt.editor.setData(initialContent);
                        }
                    }
                }
            });

            renumberPages();
            return textareaId;
        }

        function renumberPages() {
            $('#template-pages-container .template-page-card').each(function (index) {
                $(this).find('.page-num-text').text(index + 1);
            });
        }

        // Remove page click
        $(document).on('click', '.remove-page-btn', function () {
            const pageId = $(this).data('page-id');
            const textareaId = 'page_editor_' + pageId;
            if (confirm('Are you sure you want to remove this page?')) {
                if (CKEDITOR.instances[textareaId]) {
                    CKEDITOR.instances[textareaId].destroy();
                }
                $('#page-card-' + pageId).remove();
                renumberPages();
            }
        });

        // Add More Page click
        $('#addPageBtn').on('click', function () {
            const nextNum = $('#template-pages-container .template-page-card').length + 1;
            createPageBlock(nextNum, '');
        });

        // Load existing template data via AJAX
        $.ajax({
            url: "<?= site_url('api/offer-templates/get-template') ?>/" + templateId,
            method: "GET",
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    $('#id').val(templateId);
                    $('#title').val(response.data.title);

                    // Determine pages
                    let pages = response.data.pages;
                    if (!pages || !pages.length) {
                        pages = [];
                        if (response.data.content) pages.push(response.data.content);
                        if (response.data.content_page2) pages.push(response.data.content_page2);
                    }

                    // Default to at least Page 1 and Page 2
                    if (pages.length === 0) {
                        pages = ['', ''];
                    } else if (pages.length === 1) {
                        pages.push('');
                    }

                    // Populate each page
                    $('#template-pages-container').empty();
                    pages.forEach(function (pageContent, idx) {
                        createPageBlock(idx + 1, pageContent);
                    });

                    // Previews
                    if (response.data.template_header_url) {
                        $('#existingHeaderPreview').attr('src', response.data.template_header_url).show();
                    } else {
                        $('#existingHeaderPreview').hide();
                    }

                    if (response.data.template_footer_url) {
                        $('#existingFooterPreview').attr('src', response.data.template_footer_url).show();
                    } else {
                        $('#existingFooterPreview').hide();
                    }
                } else {
                    $('#responseMessage').html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            },
            error: function () {
                $('#responseMessage').html(`<div class="alert alert-danger">Error fetching template data.</div>`);
            }
        });

        // ✅ Form Submit (Update Template)
        $('#offerTemplateForm').on('submit', function (e) {
            e.preventDefault();

            // Sync CKEditor content to textarea
            for (const instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }

            const formData = new FormData(this);
            const csrfTokenName = '<?= csrf_token() ?>';
            const csrfTokenValue = $('#csrfToken').val();
            formData.append(csrfTokenName, csrfTokenValue);

            // Collect all dynamic pages in order
            const pages = [];
            $('#template-pages-container .template-page-card').each(function () {
                const editorId = $(this).find('.page-editor-textarea').attr('id');
                if (CKEDITOR.instances[editorId]) {
                    pages.push(CKEDITOR.instances[editorId].getData());
                }
            });

            // Append pages array
            pages.forEach(function (pageContent, index) {
                formData.append('pages[' + index + ']', pageContent);
            });
            formData.set('content', pages[0] || '');
            formData.set('content_page2', pages[1] || '');

            $('#submitBtn').text('Submitting...').prop('disabled', true);
            $('#loader').show();

            $.ajax({
                url: '<?= site_url("api/offer-template/update-template") ?>/' + templateId,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function () {
                    $('#loader').hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Offer letter template updated successfully!',
                        timer: 1500,
                        showConfirmButton: false,
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'hr-btnbg',
                        }
                    });

                    setTimeout(() => {
                        window.location.href = '<?= site_url("offer-templates-view") ?>';
                    }, 1600);
                },
                error: function (xhr) {
                    $('#loader').hide();
                    $('#submitBtn').text('Submit').prop('disabled', false);

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        $('#title-error, #template_img-error, #template_header-error, #template_footer-error, #content-error').text('');

                        $.each(errors, function (key, val) {
                            $('#' + key + '-error').text(val);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'Something went wrong. Please try again.',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg'
                            }
                        });
                    }
                }
            });
        });
    });
</script>





<?= $this->endSection(); ?>