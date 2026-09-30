<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>
<style>
    @media (max-width: 767px) {
        .interviewsmbtn{
            font-size: 10px !important;
            padding: 8px !important;
            margin-top: 10px !important;
        }
        .cart-sm-titles{
            font-size: 15px !important;
            margin-bottom: 5px !important;
        }
    }
    .letterhead-box {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-radius: 8px;
        padding: 40px;
        max-width: 850px;
        margin: 0 auto;
    }
    .letterhead-header {
        border-bottom: 2px solid #222222;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }
    .letterhead-header-banner img {
        width: 100%;
        max-height: 120px;
        object-fit: contain;
        display: block;
        margin-bottom: 20px;
    }
    .letterhead-footer-banner img {
        width: 100%;
        max-height: 50px;
        object-fit: contain;
        display: block;
        margin-top: 30px;
    }
    .letterhead-logo img {
        max-height: 85px;
        max-width: 240px;
        object-fit: contain;
    }
    .letterhead-address {
        font-size: 13px;
        font-weight: 600;
        color: #222222;
        line-height: 1.4;
    }
    .letterhead-title {
        text-align: center;
        margin: 20px 0;
    }
    .letterhead-title h3 {
        font-size: 17px;
        font-weight: bold;
        text-decoration: underline;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #000000;
        display: inline-block;
    }
    .letterhead-body {
        font-size: 14px;
        line-height: 1.65;
        color: #222222;
    }
    /* Custom bullet styles — flexbox for browser preview (matches Dompdf table-cell middle in PDF) */
    .letterhead-body ul.custom-bullet-list {
        list-style: none !important;
        padding-left: 0 !important;
        margin: 8px 0 14px 0 !important;
    }
    .letterhead-body ul.custom-bullet-list li {
        list-style: none !important;
        margin-bottom: 8px !important;
        position: relative !important;
        padding-left: 28px !important;
    }
    /* Bullet icon: positioned absolutely to the left of the list item */
    .custom-bullet-char {
        position: absolute !important;
        left: 0 !important;
        top: 2px !important;
        width: 24px !important;
        font-family: 'Segoe UI Symbol', 'DejaVu Sans', Arial, sans-serif !important;
        color: #000000 !important;
        font-size: 14pt !important;
        line-height: 1 !important;
    }
    .digital-signature-img {
        max-height: 55px;
        max-width: 160px;
        height: auto;
        object-fit: contain;
        vertical-align: middle;
        display: inline-block;
    }
    .company-stamp-img {
        max-height: 85px;
        max-width: 85px;
        height: auto;
        object-fit: contain;
        vertical-align: middle;
        display: inline-block;
    }
    .sig-stamp-table {
        border-collapse: collapse;
        border: none;
        margin-bottom: 6px;
    }
    .sig-stamp-table td {
        border: none;
        padding: 0 15px 0 0;
        vertical-align: bottom;
    }
</style>
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card">
            <div class="card-body">

                <!-- Header: Title + Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Offer Letter Template Preview</h4>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if (!empty($templates['id'])): ?>
                            <a href="<?= site_url('offer-templates/preview-pdf/' . $templates['id']) ?>" target="_blank" class="btn hr-btnbg interviewsmbtn" style="white-space:nowrap;">
                                <i class="mdi mdi-file-pdf-box cart-sm-titles"></i> Preview PDF
                            </a>
                            <a href="<?= site_url('template/' . $templates['id']) ?>" class="btn btn-warning interviewsmbtn text-dark" style="white-space:nowrap;">
                                <i class="mdi mdi-pencil cart-sm-titles"></i> Edit
                            </a>
                        <?php endif; ?>
                        <a href="/offer-templates-view" class="btn hr-btnbg interviewsmbtn" style="white-space:nowrap;">
                            <i class="mdi mdi-arrow-left cart-sm-titles"></i> Offer Letter Templates
                        </a>
                    </div>
                </div>

                <?php if (!empty($templates)): ?>
                    <!-- Template Letterhead Preview -->
                    <div class="letterhead-box">
                        <?php if (!empty($templates['template_header']) && file_exists(FCPATH . 'upload/' . $templates['template_header'])): ?>
                            <div class="letterhead-header-banner">
                                <img src="<?= base_url('upload/' . $templates['template_header']) ?>" alt="Header Banner">
                            </div>
                        <?php else: ?>
                            <div class="letterhead-header d-flex justify-content-between align-items-center">
                                <div class="letterhead-logo">
                                    <?php
                                    $compLogoUrl = '';
                                    if (!empty($templates['template_img']) && file_exists(FCPATH . 'upload/' . $templates['template_img'])) {
                                        $compLogoUrl = base_url('upload/' . $templates['template_img']);
                                    } else {
                                        $companyInfo = (new \App\Models\CompanyLogoModel())->first();
                                        if (!empty($companyInfo['logo_img']) && file_exists(FCPATH . 'upload/' . $companyInfo['logo_img'])) {
                                            $compLogoUrl = base_url('upload/' . $companyInfo['logo_img']);
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($compLogoUrl)): ?>
                                        <img src="<?= $compLogoUrl ?>" alt="Logo">
                                    <?php else: ?>
                                        <h5 class="fw-bold mb-0 text-dark">Fablead Developers Technolab</h5>
                                    <?php endif; ?>
                                </div>
                                <div class="letterhead-address text-end">
                                    Fablead Developers Technolab, Surat, Gujarat, India
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        $previewPages = [];
                        if (!empty($templates['content_pages'])) {
                            $decoded = json_decode($templates['content_pages'], true);
                            if (is_array($decoded) && count($decoded) > 0) {
                                $previewPages = $decoded;
                            }
                        }
                        if (empty($previewPages)) {
                            if (!empty($templates['content'])) $previewPages[] = $templates['content'];
                            if (!empty($templates['content_page2'])) $previewPages[] = $templates['content_page2'];
                        }
                        ?>

                        <?php foreach ($previewPages as $pIdx => $pContent): ?>
                            <?php if ($pIdx > 0): ?>
                                <hr class="my-4" style="border-top: 2px dashed #bbb;">
                                <div class="text-muted small mb-3 text-center fw-bold">--- Page <?= ($pIdx + 1) ?> Preview ---</div>
                            <?php endif; ?>
                            <?php if ($pIdx === 0): ?>
                                <div class="letterhead-title">
                                    <h3><u><?= esc($templates['title']) ?></u></h3>
                                </div>
                            <?php endif; ?>
                            <div class="letterhead-body">
                                <?= $pContent ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!empty($templates['template_footer']) && file_exists(FCPATH . 'upload/' . $templates['template_footer'])): ?>
                            <div class="letterhead-footer-banner">
                                <img src="<?= base_url('upload/' . $templates['template_footer']) ?>" alt="Footer Banner">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p>No template found.</p>
                <?php endif; ?>

                <div id="responseMessage" class="mt-3"></div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection(); ?>