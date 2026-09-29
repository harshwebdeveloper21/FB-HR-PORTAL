<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HR Portal</title>
    <!-- plugins:css -->
    <?= $this->include('dashboard/header_link'); ?>
    <style>
        /* Deduction modals: always on top (fixes mobile popup not visible) */
        .modal-backdrop { z-index: 9998 !important; }
        .modal { z-index: 9999 !important; }
        @media (max-width: 768px) {
            .modal .modal-dialog { max-height: 90vh; overflow-y: auto; }

            /* Mobile view: padding 0 for main column containers (col-md-12, col-12, col-lg-12, stretch-card) */
            .content-wrapper > .row > .col-md-12,
            .content-wrapper > .row > .col-12,
            .content-wrapper > .row > .col-lg-12,
            .content-wrapper > .row > .col-lg-13,
            .col-lg-12.grid-margin.stretch-card,
            .col-12.grid-margin.stretch-card,
            .col-12.grid-margin {
                padding: 0 !important;
            }

            /* .col-lg-12.grid-margin.stretch-card > .card,
            .col-lg-12.grid-margin.stretch-card .card,
            .col-12.grid-margin.stretch-card > .card,
            .col-12.grid-margin.stretch-card .card {
                background: transparent !important;
                background-color: transparent !important;
                --bs-card-bg: transparent !important;
            } */

            /* Sidebar mobile header: keep logo and close button inline horizontally */
            .sidebar .sidebar-mobile-header,
            #sidebar .sidebar-mobile-header,
            #sidebar > div:first-child {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .sidebar .sidebar-mobile-header a,
            #sidebar .sidebar-mobile-header a,
            #sidebar > div:first-child a {
                width: auto !important;
                max-width: none !important;
                flex: 0 0 auto !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .sidebar .sidebar-mobile-header img,
            #sidebar .sidebar-mobile-header img,
            #sidebar > div:first-child img {
                max-height: 35px !important;
                width: auto !important;
                max-width: 150px !important;
                object-fit: contain !important;
                flex: 0 0 auto !important;
                margin: 0 !important;
            }
        }
        /* Global Uniform Button Styling */
        .btn-secondary, .modal-footer .btn-secondary, .btn-light-secondary {
            background-color: #6c757d !important;
            border: 2px solid #6c757d !important;
            color: #ffffff !important;
            border-radius: 4px !important;
            font-family: Poppins, sans-serif !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            padding: 6px 14px !important;
            min-height: 36px !important;
            line-height: 1.2 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.2s ease !important;
        }
        .btn-secondary:hover, .btn-secondary:focus {
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
            color: #ffffff !important;
        }

        .hr-btnbg, .btn-primary.hr-btnbg, button.hr-btnbg, a.hr-btnbg, .btn.hr-btnbg {
            background-color: #E66136 !important;
            border: 2px solid #F05929 !important;
            border-radius: 4px !important;
            color: #ffffff !important;
            font-family: Poppins, sans-serif !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            padding: 6px 14px !important;
            height: 36px !important;
            min-height: 36px !important;
            line-height: 1.2 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        .hr-btnbg:hover, .hr-btnbg:focus, .hr-btnbg:active,
        .btn.hr-btnbg:hover, .btn.hr-btnbg:focus, .btn.hr-btnbg:active {
            border: 2px solid #e66136 !important;
            background-color: #ffffff !important;
            color: #e66136 !important;
            text-decoration: none !important;
        }
        .hr-btnbg i, .btn.hr-btnbg i {
            color: #ffffff !important;
            font-size: 15px !important;
            line-height: 1 !important;
            transition: color 0.2s ease !important;
        }
        .hr-btnbg:hover i, .hr-btnbg:focus i, .hr-btnbg:active i,
        .btn.hr-btnbg:hover i, .btn.hr-btnbg:focus i, .btn.hr-btnbg:active i {
            color: #e66136 !important;
        }
        .hr-btnbg:disabled, .hr-btnbg[disabled] {
            background-color: #f0845a !important;
            border-color: #f0845a !important;
            color: #ffffff !important;
            opacity: 0.8 !important;
            cursor: not-allowed !important;
        }

        .interviewsmbtn {
            font-size: 14px !important;
            padding: 6px 16px !important;
            min-height: 36px !important;
        }

        /* ══════════════════════════════════════════════════════
           Sidebar Sub-menu (Dropdown) Compact & Clean Hierarchy
           ══════════════════════════════════════════════════════ */
        .sidebar .nav.sub-menu {
            margin: 0 12px 6px 26px !important;
            padding: 4px 0 6px 0 !important;
            list-style: none !important;
            background: #fafafa !important;
            border-radius: 0 0 10px 10px !important;
            border-left: 2px solid rgba(230, 97, 54, 0.25) !important;
        }

        .sidebar .nav.sub-menu .nav-item {
            position: relative !important;
            margin: 1px 0 !important;
            padding: 0 !important;
        }

        .sidebar .nav.sub-menu .nav-item::before {
            content: "" !important;
            position: absolute !important;
            left: 12px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            width: 5px !important;
            height: 5px !important;
            border-radius: 50% !important;
            background: #94a3b8 !important;
            margin: 0 !important;
            transition: all 0.2s ease !important;
            z-index: 1 !important;
        }

        .sidebar .nav.sub-menu .nav-item:hover::before,
        .sidebar .nav.sub-menu .nav-item:has(.nav-link.active)::before {
            background: #E66136 !important;
            transform: translateY(-50%) scale(1.3) !important;
        }

        .sidebar .nav.sub-menu .nav-item .nav-link {
            display: flex !important;
            align-items: center !important;
            padding: 6px 12px 6px 26px !important;
            font-size: 13px !important;
            line-height: 1.35 !important;
            color: #475569 !important;
            font-weight: 500 !important;
            border-radius: 6px !important;
            height: auto !important;
            white-space: normal !important;
            transition: all 0.18s ease !important;
        }

        .sidebar .nav.sub-menu .nav-item .nav-link:hover {
            color: #E66136 !important;
            background: rgba(230, 97, 54, 0.08) !important;
        }

        .sidebar .nav.sub-menu .nav-item .nav-link.active {
            color: #E66136 !important;
            font-weight: 600 !important;
            background: rgba(230, 97, 54, 0.1) !important;
        }

        /* Submenu Flyout in Minimized/Icon-Only Sidebar Mode */
        body.sidebar-icon-only .sidebar .nav.sub-menu {
            margin: 0 !important;
            padding: 8px 0 !important;
            border-left: none !important;
            border-radius: 8px !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12) !important;
            background: #ffffff !important;
        }

        body.sidebar-icon-only .sidebar .nav.sub-menu .nav-item::before {
            display: none !important;
        }

        body.sidebar-icon-only .sidebar .nav.sub-menu .nav-item .nav-link {
            padding: 8px 18px !important;
        }

        /* ══════════════════════════════════════════════════════════════════════════
           Global Fix for Input Groups & Prepend/Append Icons Alignment
           ══════════════════════════════════════════════════════════════════════════ */
        .input-group {
            position: relative;
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            width: 100% !important;
        }

        .input-group > .input-group-prepend,
        .input-group > .input-group-append,
        .input-group-prepend,
        .input-group-append {
            display: flex !important;
            align-items: stretch !important;
            margin: 0 !important;
        }

        .input-group-prepend .input-group-text,
        .input-group-append .input-group-text,
        .input-group > .input-group-text {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: auto !important;
            min-height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            background-color: #E66136 !important;
            color: #ffffff !important;
            border: 1px solid #dee2e6 !important;
        }

        .input-group-prepend .input-group-text,
        .input-group > .input-group-text:first-child {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-top-left-radius: 4px !important;
            border-bottom-left-radius: 4px !important;
        }

        .input-group-append .input-group-text,
        .input-group > .input-group-text:last-child {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
        }

        .input-group > .input-group-prepend ~ .form-control,
        .input-group > .input-group-prepend ~ .form-select,
        .input-group > .input-group-text ~ .form-control,
        .input-group > .input-group-text ~ .form-select {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
            flex: 1 1 auto !important;
            width: 1% !important;
            min-width: 0 !important;
            height: auto !important;
            min-height: 38px !important;
        }

        .input-group > .form-control:not(:last-child),
        .input-group > .form-select:not(:last-child) {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        /* ══════════════════════════════════════════════════════
           Unified Table Action Icons Standard Across Entire Portal
           ══════════════════════════════════════════════════════ */
        table td a.text-primary i,
        table td a[title*="View" i] i,
        table td .text-primary i.mdi-eye {
            color: #2563eb !important;
            transition: color 0.15s ease, transform 0.15s ease;
        }
        table td a.text-primary:hover i,
        table td a[title*="View" i]:hover i {
            color: #1d4ed8 !important;
            transform: scale(1.15);
        }

        table td a.text-warning i,
        table td button.text-warning i,
        table td a[title*="Edit" i] i,
        table td .text-warning i.mdi-pencil {
            color: #f59e0b !important;
            transition: color 0.15s ease, transform 0.15s ease;
        }
        table td a.text-warning:hover i,
        table td button.text-warning:hover i,
        table td a[title*="Edit" i]:hover i {
            color: #d97706 !important;
            transform: scale(1.15);
        }

           /* ══════════════════════════════════════════════════════
           Unified Seamless Header Styles
           ══════════════════════════════════════════════════════ */
        .navbar.default-layout {
            background: linear-gradient(135deg, #e66136, #ff7b4a) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08) !important;
            border: none !important;
            transition: all 0.25s ease !important;
        }

        .navbar.default-layout .navbar-brand-wrapper,
        .navbar.default-layout .navbar-menu-wrapper {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        .navbar.headerLight .welcome-text,
        .with-welcome-text .navbar.headerLight .welcome-text {
            display: block !important;
        }

        /* ── DESKTOP VIEW (>= 992px): 97px on Top -> 62px on Scroll ── */
        @media (min-width: 992px) {
            .navbar.default-layout {
                height: 97px !important;
                min-height: 97px !important;
            }

            .navbar.default-layout .navbar-brand-wrapper,
            .navbar.default-layout .navbar-menu-wrapper {
                height: 97px !important;
                min-height: 97px !important;
                transition: height 0.25s ease !important;
            }

            .navbar.default-layout.headerLight,
            .navbar.headerLight {
                height: 62px !important;
                min-height: 62px !important;
                max-height: 62px !important;
            }

            .navbar.headerLight .navbar-brand-wrapper,
            .navbar.headerLight .navbar-menu-wrapper {
                height: 62px !important;
                min-height: 62px !important;
                max-height: 62px !important;
            }

            .navbar .navbar-brand-wrapper {
                width: 241px !important;
                min-width: 241px !important;
                max-width: 241px !important;
                border-right: 1px solid rgba(255, 255, 255, 0.25) !important;
                padding-right: 15px !important;
            }

            .page-body-wrapper {
                padding-top: 97px !important;
                display: flex !important;
                min-height: calc(100vh - 97px) !important;
                position: relative !important;
                transition: padding-top 0.25s ease !important;
            }

            .sidebar {
                position: fixed !important;
                top: 97px !important;
                left: 0 !important;
                bottom: 0 !important;
                width: 241px !important;
                height: calc(100vh - 97px) !important;
                max-height: calc(100vh - 97px) !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                overscroll-behavior: contain !important;
                -ms-scroll-chaining: none !important;
                -webkit-overflow-scrolling: touch !important;
                z-index: 100 !important;
                background: #ffffff !important;
                border-right: 1px solid #e9ecef !important;
                transition: width 0.25s ease, transform 0.25s ease, top 0.25s ease, height 0.25s ease !important;
            }

            /* Synced Sidebar Top Offset When Header Is Scrolled (Zero Gap) */
            body.has-scrolled .sidebar,
            .navbar.headerLight ~ .page-body-wrapper .sidebar {
                top: 62px !important;
                height: calc(100vh - 62px) !important;
                max-height: calc(100vh - 62px) !important;
            }

            .sidebar .nav {
                overflow: visible !important;
                margin-bottom: 40px !important;
                padding-bottom: 30px !important;
            }

            .main-panel {
                margin-left: 241px !important;
                width: calc(100% - 241px) !important;
                min-height: calc(100vh - 97px) !important;
                transition: width 0.25s ease, margin-left 0.25s ease !important;
            }

            /* Collapsed / Minimized Sidebar */
            body.sidebar-icon-only .sidebar {
                width: 70px !important;
                overflow-y: visible !important;
                overflow-x: visible !important;
            }

            body.sidebar-icon-only .main-panel {
                margin-left: 70px !important;
                width: calc(100% - 70px) !important;
            }

            /* Hidden Sidebar */
            body.sidebar-hidden .sidebar {
                width: 0 !important;
                display: none !important;
            }

            body.sidebar-hidden .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        /* ── MOBILE & TABLET VIEW (< 992px): Constant Fixed Height (62px) On Load & On Scroll ── */
        @media (max-width: 991.98px) {
            .navbar.default-layout,
            .navbar.headerLight,
            .navbar.default-layout .navbar-brand-wrapper,
            .navbar.headerLight .navbar-brand-wrapper,
            .navbar.default-layout .navbar-menu-wrapper,
            .navbar.headerLight .navbar-menu-wrapper {
                height: 62px !important;
                min-height: 62px !important;
                max-height: 62px !important;
            }

            .navbar.default-layout {
                padding: 0 10px !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
            }

            .navbar .navbar-brand-wrapper {
                width: auto !important;
                min-width: auto !important;
                max-width: none !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                background: transparent !important;
                border: none !important;
                border-right: none !important;
                flex: 0 0 auto !important;
            }

            .navbar .navbar-menu-wrapper {
                width: auto !important;
                min-width: auto !important;
                flex: 1 1 auto !important;
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: flex-end !important;
            }

            .page-body-wrapper {
                padding-top: 62px !important;
                min-height: calc(100vh - 62px) !important;
            }

            .content-wrapper {
                padding: 1.25rem 0.85rem 1.5rem 0.85rem !important;
            }

            .sidebar {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                height: 100vh !important;
                max-height: 100vh !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                overscroll-behavior: contain !important;
                -webkit-overflow-scrolling: touch !important;
                z-index: 1050 !important;
            }

            .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        /* Sleek Modern Custom Scrollbar for Sidebar */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(230, 97, 54, 0.25) transparent;
        }

        .sidebar:hover {
            scrollbar-color: rgba(230, 97, 54, 0.5) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.12);
            border-radius: 6px;
        }

        .sidebar:hover::-webkit-scrollbar-thumb {
            background: rgba(230, 97, 54, 0.4);
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #e66136;
        }

    </style>
</head>

<body class="with-welcome-text">
<div id="loader">
    <div class="loader-dots">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <?= $this->include('dashboard/navbar.php'); ?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <?= $this->include('dashboard/sidebar.php'); ?>
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <?= $this->renderSection('content'); ?>
                </div>

                <?= $this->include('dashboard/footer.php'); ?>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <?= $this->include('dashboard/mobile_bottom_nav.php'); ?>
    <?= $this->include('dashboard/footer_link.php'); ?>
    <?= $this->renderSection('scripts'); ?>
</body>

</html>