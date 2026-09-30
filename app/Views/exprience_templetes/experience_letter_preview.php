<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Experience Letter') ?></title>
    <style>
        @page {
            margin-top: 120px;
            margin-bottom: 45px;
            margin-left: 70pt;
            margin-right: 70pt;
        }

        body {
            font-family: 'Times New Roman', Times, 'DejaVu Serif', serif;
            font-size: 12pt;
            line-height: 1.35;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Fixed Header on every page */
        header {
            position: fixed;
            top: -115px;
            left: 0;
            right: 0;
            height: 110px;
        }

        .header-banner-img {
            width: 100%;
            max-height: 110px;
            object-fit: contain;
            display: block;
        }

        .header-table {
            width: 100%;
            height: 110px;
            border-collapse: collapse;
            border: none;
        }

        .header-logo-cell {
            width: 35%;
            vertical-align: middle;
            text-align: left;
            padding: 0;
            border: none;
        }

        .header-logo-cell img {
            max-height: 88px;
            max-width: 240px;
            height: auto;
            display: block;
        }

        .header-address-cell {
            width: 65%;
            vertical-align: middle;
            text-align: right;
            font-family: Calibri, 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.3;
            white-space: nowrap;
            padding: 0;
            border: none;
        }

        /* Fixed Footer on every page (if banner provided) */
        footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
        }

        .footer-banner-img {
            width: 100%;
            max-height: 40px;
            object-fit: contain;
            display: block;
        }

        .content {
            margin-top: 25px;
        }

        .body-content {
            font-size: 12pt;
            line-height: 1.35;
            color: #000000;
            text-align: left;
        }

        .body-content p {
            margin: 0 0 16px 0;
            line-height: 1.35;
        }

        .body-content strong, .body-content b {
            font-weight: bold;
            color: #000000;
        }

        .body-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            table-layout: fixed;
        }

        .body-content table td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .no-break {
            page-break-inside: avoid;
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
            border-collapse: collapse !important;
            border: none !important;
            margin-bottom: 6px !important;
            width: auto !important;
        }

        .sig-stamp-table td {
            border: none !important;
            padding: 0 15px 0 0 !important;
            vertical-align: bottom !important;
        }
    </style>
</head>

<body>

    <!-- Header (repeats on each page) -->
    <header>
        <?php if (!empty($header_img_src)): ?>
            <div style="width: 100%; text-align: center;">
                <img src="<?= $header_img_src ?>" class="header-banner-img" alt="Header">
            </div>
        <?php else: ?>
            <table class="header-table">
                <tr>
                    <td class="header-logo-cell">
                        <?php if (!empty($logo_src)): ?>
                            <img src="<?= $logo_src ?>" alt="Company Logo">
                        <?php endif; ?>
                    </td>
                    <td class="header-address-cell">
                        <?= !empty($company_address) ? esc($company_address) : 'Fablead Developers Technolab, Surat , Gujarat , India' ?>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </header>

    <!-- Footer (if banner uploaded) -->
    <?php if (!empty($footer_img_src)): ?>
        <footer>
            <img src="<?= $footer_img_src ?>" class="footer-banner-img" alt="Footer">
        </footer>
    <?php endif; ?>

    <!-- Main content -->
    <div class="content">
        <div class="body-content">
            <?= $content ?>
        </div>
    </div>

</body>

</html>