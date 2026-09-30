<?= $this->extend("layout") ?>
<?= $this->section("content") ?>
<style>
    @media (max-width: 767px) {
        .interviewsmbtn {
            font-size: 10px !important;
            padding: 8px !important;
            margin-top: 10px !important;
        }
        .letterhead-paper {
            padding: 25px 15px !important;
        }
    }

    .letterhead-paper {
        background: #ffffff;
        border-radius: 6px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        max-width: 820px;
        margin: 0 auto;
        padding: 50px 60px;
        color: #000000;
        font-family: 'Times New Roman', Times, 'DejaVu Serif', serif;
        font-size: 16px;
        line-height: 1.45;
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
        font-family: Calibri, 'DejaVu Sans', Arial, sans-serif;
        font-size: 14px;
        font-weight: bold;
        color: #000000;
        text-align: right;
        line-height: 1.3;
    }

    .letterhead-body {
        min-height: 400px;
        text-align: left;
    }

    .letterhead-body p {
        margin-bottom: 16px;
        line-height: 1.45;
    }

    .letterhead-body strong, .letterhead-body b {
        font-weight: bold;
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
                        <h4 class="card-title mb-1"><?= esc($templates["title"] ?? 'Experience Letter Template') ?></h4>
                        <small class="text-muted">Template Preview</small>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= site_url('exprience-templates/preview-pdf/' . $templates['id']) ?>" target="_blank" class="btn btn-outline-danger interviewsmbtn">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Preview PDF
                        </a>
                        <a href="<?= site_url('exprience-templates-view') ?>" class="btn hr-btnbg interviewsmbtn">
                            <i class="mdi mdi-arrow-left me-1"></i> All Templates
                        </a>
                    </div>
                </div>

                <?php if (!empty($templates)): ?>
                    <div class="letterhead-paper">

                        <!-- Header Section -->
                        <div class="letterhead-header">
                            <?php if (!empty($templates['template_header']) && file_exists(FCPATH . 'upload/templates/' . $templates['template_header'])): ?>
                                <div class="letterhead-header-banner">
                                    <img src="<?= base_url('upload/templates/' . $templates['template_header']) ?>" alt="Header Banner">
                                </div>
                            <?php else: ?>
                                <div class="row align-items-center">
                                    <div class="col-sm-5 letterhead-logo">
                                        <?php if (!empty($company['logo_img']) && file_exists(FCPATH . 'upload/' . $company['logo_img'])): ?>
                                            <img src="<?= base_url('upload/' . $company['logo_img']) ?>" alt="Company Logo">
                                        <?php else: ?>
                                            <img src="<?= base_url('assets/images/fab_logo.png') ?>" alt="Company Logo">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-7 letterhead-address">
                                        <?= !empty($company['company_address']) ? esc($company['company_address']) : 'Fablead Developers Technolab, Surat , Gujarat , India' ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Content Section -->
                        <div class="letterhead-body">
                            <?= $templates["content"] ?>
                        </div>

                        <!-- Footer Section -->
                        <?php if (!empty($templates['template_footer']) && file_exists(FCPATH . 'upload/templates/' . $templates['template_footer'])): ?>
                            <div class="letterhead-footer-banner">
                                <img src="<?= base_url('upload/templates/' . $templates['template_footer']) ?>" alt="Footer Banner">
                            </div>
                        <?php endif; ?>

                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <p>No template found.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
