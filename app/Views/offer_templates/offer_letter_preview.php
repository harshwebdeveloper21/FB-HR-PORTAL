<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JOINING LETTER</title>
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
            line-height: 1.28;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        <?php
        // Load Segoe UI Symbol (Windows system font) for full Dingbats coverage.
        // ➢ (U+27A2) and other Dingbats are NOT in DejaVu Sans but ARE in Segoe UI Symbol.
        // Dompdf has isRemoteEnabled=true so it can load local file:// font sources.
        $segoeSymFont = 'C:\\Windows\\Fonts\\seguisym.ttf';
        if (PHP_OS_FAMILY === 'Windows' && file_exists($segoeSymFont)):
        ?>
        @font-face {
            font-family: 'Segoe UI Symbol';
            src: url('file:///C:/Windows/Fonts/seguisym.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        <?php endif; ?>

        /* Fixed Header on every page with increased height */
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
            width: 70%;
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

        /* Fixed Footer on every page (if banner uploaded) */
        footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
        }

        .footer-banner-img {
            width: 100%;
            max-height: 30px;
            object-fit: contain;
            display: block;
        }

        /* Main Content */
        .content {
            margin-top: 5px;
        }

        .template-title-wrapper {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 18px;
        }

        .template-title-wrapper h3 {
            font-family: Calibri, 'DejaVu Sans', Arial, sans-serif;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            color: #000000;
            display: inline-block;
        }

        .body-content {
            font-size: 12pt;
            line-height: 1.28;
            color: #000000;
            text-align: left;
        }

        .body-content p {
            margin: 0 0 8px 0;
            line-height: 1.28;
        }

        .body-content strong, .body-content b {
            font-weight: bold;
            color: #000000;
        }

        /* Custom Bullet Styles — table-cell layout (Dompdf 3.x, confirmed) */
        /* PDF engine: dompdf/dompdf ^3.1 — flexbox NOT supported, use table-cell */
        .body-content ul.custom-bullet-list {
            list-style: none !important;
            padding-left: 0 !important;
            margin: 6px 0 10px 0 !important;
            width: 100% !important;
        }

        .body-content ul.custom-bullet-list li {
            display: table !important;
            width: 100% !important;
            list-style: none !important;
            margin-bottom: 8px !important;
            line-height: 1.28 !important;
        }

        /* Column 1: bullet icon — width:1% forces minimum width, nowrap prevents expansion */
        .body-content span.custom-bullet-char {
            display: table-cell !important;
            width: 1% !important;
            white-space: nowrap !important;
            padding-right: 8px !important;
            vertical-align: top !important;
            line-height: 1 !important;
            font-family: 'Segoe UI Symbol', 'DejaVu Sans', sans-serif !important;
            color: #000000 !important;
            font-size: 14pt !important;
        }

        /* Column 2: text takes all remaining width, flows from top */
        .body-content span.custom-bullet-text {
            display: table-cell !important;
            vertical-align: top !important;
            line-height: 1.28 !important;
            font-size: 12pt !important;
            padding-top: 2pt !important;
        }

        /* Numbered list for submitted documents */
        .body-content ol {
            margin: 4px 0 4px 18px !important;
            padding-left: 4px !important;
            list-style-type: decimal !important;
        }

        .body-content ol li {
            margin-bottom: 6px !important;
            line-height: 1.28 !important;
            list-style-type: decimal !important;
        }

        /* Ensure injected variables & code blocks inherit exact body font */
        code, tt, pre, samp, kbd {
            font-family: inherit !important;
            font-size: inherit !important;
            font-style: inherit !important;
            font-weight: inherit !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            color: inherit !important;
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
                            <img src="<?= $logo_src ?>" alt="Logo">
                        <?php elseif (!empty($company_name)): ?>
                            <h2 style="margin: 0; font-size: 16px; font-weight: bold; color: #000;"><?= esc($company_name) ?></h2>
                        <?php endif; ?>
                    </td>
                    <td class="header-address-cell">
                        <?= esc($template_header ?? 'Fablead Developers Technolab, Surat , Gujarat , India') ?>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </header>

    <!-- Footer (repeats on each page if uploaded) -->
    <?php if (!empty($footer_img_src)): ?>
        <footer>
            <img src="<?= $footer_img_src ?>" class="footer-banner-img" alt="Footer">
        </footer>
    <?php endif; ?>

    <?php if (!empty($parsed_pages) && is_array($parsed_pages)): ?>
        <?php foreach ($parsed_pages as $idx => $pageHtml): ?>
            <?php if ($idx > 0): ?>
                <div style="page-break-before: always;"></div>
            <?php endif; ?>
            <div class="content" style="<?= $idx > 0 ? 'padding-top: 5px;' : '' ?>">
                <?php if ($idx === 0): ?>
                    <div class="template-title-wrapper">
                        <h3><u>JOINING LETTER</u></h3>
                    </div>
                <?php endif; ?>
                <div class="body-content">
                    <?= $pageHtml ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Main Content (Page 1) -->
        <div class="content">
            <div class="template-title-wrapper">
                <h3><u>JOINING LETTER</u></h3>
            </div>

            <div class="body-content">
                <?= $content ?>
            </div>
        </div>

        <!-- Main Content (Page 2 if provided) -->
        <?php if (!empty($content_page2)): ?>
            <div style="page-break-before: always;"></div>
            <div class="content" style="padding-top: 5px;">
                <div class="body-content">
                    <?= $content_page2 ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>
