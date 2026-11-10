<?php $cur_view = $this->router->method; ?>
<?php
$current_url = uri_string(); // example: bid/BidEvaluated
?>
<?php $employee_det = $this->session->userdata('logged_in');
   // echo "<pre>cur_view menuTree"; print_r($menuTree); exit;
   $currentUrl = strtolower(uri_string());
$isActive   = !empty($currentUrlArray['child'])
              && strtolower($currentUrlArray['child']['MenuUrl']) === $currentUrl;
       // echo "<pre>cur_view isActive"; print_r($isActive); exit;       
     if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }

$currentUserProfile = null;
$currentUserRoleName = 'User';
if (!empty($employee_det['Bid'])) {
    $currentUserProfile = $this->bid_model->getUserRowArray($employee_det['Bid']);
    $userBrid = $currentUserProfile['Brid'] ?? ($employee_det['RoleId'] ?? null);
    if (!empty($userBrid)) {
        $roleRow = $this->db->get_where('BidUsersRoles', ['Brid' => $userBrid])->row_array();
        $currentUserRoleName = $roleRow['RoleName'] ?? 'User';
    }
}
?>
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
<!DOCTYPE html>
<html lang="zxx" class="app-font-family-system-ui">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>Bid Management || Dashboard</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="<?=$theme_path?>/assets/images/favicon.ico" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/css/bootstrap.min.css" />
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/vendors.min.css" />
       <!--  <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/tagify.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/tagify-data.min.css">
   -->  
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/select2-theme.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/datepicker.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/daterangepicker.min.css" />
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/dataTables.bs5.min.css" />
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/vendors/css/buttons.bootstrap5.min.css" />

    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?=$theme_path?>/assets/css/theme.min.css" />
    <link rel="stylesheet" href="<?=$theme_path?>/css/canvas-modal.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?=$theme_path?>/assets/css/oem.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?=$theme_path?>/assets/css/document.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?=$theme_path?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <!--! Core Vendors JS (jQuery, Bootstrap core) loaded in HEAD so view scripts have jQuery immediately !-->
    <script src="<?=$theme_path?>/assets/vendors/js/vendors.min.js"></script>

    <style>
        
        /* System UI Font Default */
        html, body, button, input, select, textarea, .form-control, .table, h1, h2, h3, h4, h5, h6 {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji" !important;
        }

        /* Always keep footer at the bottom of the viewport */
        html, body {
            height: 100%;
        }
        .nxl-container {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .nxl-content {
            flex: 1 0 auto !important;
        }
        footer.footer {
            margin-top: auto !important;
        }

        /* Right-Side Offcanvas Drawer Reset (Ensure zero overlap when closed) */
        .right-form {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            height: 100vh !important;
            background: #fff !important;
            box-shadow: -4px 0 24px rgba(15, 23, 42, 0.2) !important;
            transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), visibility 0.3s ease !important;
            z-index: 1065 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .right-form:not(.open) {
            transform: translateX(105%) !important;
            right: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        .right-form.open {
            transform: translateX(0) !important;
            right: 0 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            display: flex !important;
        }
        #rightForm.right-form {
            width: 720px !important;
            max-width: 95vw !important;
        }

        /* Permanently Visible Header Filters (Proper vertical alignment & text centering) */
        .header-filter-item {
            display: inline-flex;
            align-items: center;
            position: relative;
        }
        .header-filter-item .form-select,
        .header-filter-item .form-select-sm,
        .header-filter-item select {
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 12px !important;
            padding-right: 32px !important;
            line-height: 36px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #283c50 !important;
            border: 1px solid #dcdee4 !important;
            border-radius: 6px !important;
            background-color: #fff !important;
            background-position: right 10px center !important;
            background-size: 12px 10px !important;
            vertical-align: middle !important;
            box-sizing: border-box !important;
        }
        .header-filter-item .form-control,
        .header-filter-item .form-control-sm,
        .header-filter-item input {
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
            line-height: 36px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #283c50 !important;
            border: 1px solid #dcdee4 !important;
            background-color: #fff !important;
            box-sizing: border-box !important;
        }
        .header-filter-item .input-group-text {
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            padding: 0 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 13px !important;
            border: 1px solid #dcdee4 !important;
            border-right: none !important;
            border-radius: 6px 0 0 6px !important;
            background-color: #fff !important;
            box-sizing: border-box !important;
        }
        .header-filter-item .input-group .form-control,
        .header-filter-item .input-group .form-control-sm,
        .header-filter-item .input-group input {
            border-left: none !important;
            border-radius: 0 6px 6px 0 !important;
        }
        .header-filter-item .btn-icon {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #64748b !important;
            background-color: #fff !important;
            border: 1px solid #dcdee4 !important;
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }

                /* Tables: wrap no text (prevent cell text from breaking onto multiple lines) */
        .table th,
        .table td,
        .table.text-nowrap th,
        .table.text-nowrap td,
        .table.wrap-no-text th,
        .table.wrap-no-text td,
        .wrap-no-text th,
        .wrap-no-text td,
        table.text-nowrap th,
                table.text-nowrap td {
            white-space: nowrap !important;
        }

        /* Scoped overrides for right-side drawer panels to prevent table overflow & horizontal shift */
        .right-form {
            overflow-x: hidden !important;
        }
        .right-form-body {
            overflow-x: hidden !important;
        }
        .right-form table {
            table-layout: fixed !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .right-form .table-responsive {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }
        .right-form .text-wrap,
        .right-form td.text-wrap,
        .right-form th.text-wrap,
        .right-form td.col-remarks,
        .right-form td.bid-detail-val {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        /* ── Sidebar Navigation Width Expansion (Prevent Menu Wrapping) ── */
        @media (min-width: 1025px) {
            html:not(.minimenu) .nxl-navigation,
            body:not(.minimenu) .nxl-navigation {
                width: 310px !important;
            }
            html:not(.minimenu) .nxl-navigation .navbar-wrapper,
            body:not(.minimenu) .nxl-navigation .navbar-wrapper {
                width: 310px !important;
            }
            html:not(.minimenu) .nxl-header,
            body:not(.minimenu) .nxl-header {
                left: 310px !important;
            }
            html:not(.minimenu) .nxl-container,
            body:not(.minimenu) .nxl-container {
                margin-left: 310px !important;
            }
        }
        .nxl-navigation .nxl-navbar .nxl-submenu .nxl-item .nxl-link {
            white-space: nowrap !important;
            padding-right: 18px !important;
        }
    </style>

    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
      <script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Manu !-->
    <!--! ================================================================ !-->
    <nav class="nxl-navigation">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="javascript:void(0);" class="b-brand">
                    <!-- ========   change your logo hear   ============ -->
                    <img src="<?=$theme_path?>/assets/images/logo-inet.png" alt="" class="logo logo-lg" />
                    <img src="<?=$theme_path?>/assets/images/logo-inet.png" alt="" class="logo logo-sm" />
                </a>
            </div>
            <div class="navbar-content">
                <!-- <ul class="nxl-navbar">
 

                      <?php foreach ($menuTree as $parent): ?>

                    <?php if (!empty($parent['children'])): ?>
                        <li class="nxl-item">
                            <a href="javascript:void(0);" class="nxl-link">
                                <span class="nxl-micon"><?= $parent['MenuName'] ?></span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>

                            <ul class="nxl-submenu">
                                <?php foreach ($parent['children'] as $child): ?>
                                  <li class="nxl-item"><a class="nxl-link" href="<?= base_url($child['MenuUrl']) ?>"><?= $child['MenuName'] ?></a></li> 
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nxl-item">
                            <a  class="nxl-link" href="<?= base_url($parent['MenuUrl']) ?>">
                                <span class="nxl-micon"><?= $parent['MenuName'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                <?php endforeach; ?>
                </ul> -->

                 <?php if (!empty($menuTree)): ?>
                    <?php
                    $rawUri = uri_string();
                    $normCurrentUrl = strtolower(trim($rawUri, '/'));

                    // URL aliases and variations
                    $urlAliases = [
                        'bid/lbids' => 'bid/l1bids',
                        'bid/l1bids' => 'bid/lbids',
                        'bid/createtender' => 'bid/createbids',
                        'bid/createbid' => 'bid/createbids'
                    ];

                    // Specific sub-actions that map to "Bid Details" under Bids
                    $bidSubActions = [
                        'bid/timeline', 'bid/factsheet', 'bid/view_tender_document',
                        'bid/bid_preview', 'bid/getbidmodaldetails', 'bid/updatebiddetails'
                    ];

                    $effectiveUrl = $normCurrentUrl;
                    foreach ($bidSubActions as $sub) {
                        if ($normCurrentUrl === $sub || strpos($normCurrentUrl . '/', $sub . '/') === 0) {
                            $effectiveUrl = 'bid/biddetails';
                            break;
                        }
                    }
                    ?>
                    <ul class="nxl-navbar">

                 <?php foreach ($menuTree as $parent): ?>

                            <?php
                            $isActiveParent = false;
                            $activeChildUrl = null;
                            $pUrl = strtolower(trim($parent['MenuUrl'] ?? '', '/'));

                            if (!empty($parent['children'])) {
                                foreach ($parent['children'] as $child) {
                                    $cUrl = strtolower(trim($child['MenuUrl'] ?? '', '/'));
                                    if (empty($cUrl) || $cUrl === '#') continue;

                                    if ($effectiveUrl === $cUrl) {
                                        $isActiveParent = true;
                                        $activeChildUrl = $cUrl;
                                        break;
                                    }
                                    if (isset($urlAliases[$effectiveUrl]) && $urlAliases[$effectiveUrl] === $cUrl) {
                                        $isActiveParent = true;
                                        $activeChildUrl = $cUrl;
                                        break;
                                    }
                                    if (strpos($effectiveUrl . '/', $cUrl . '/') === 0) {
                                        $isActiveParent = true;
                                        $activeChildUrl = $cUrl;
                                        break;
                                    }
                                }
                            } else {
                                if (!empty($pUrl) && $pUrl !== '#') {
                                    $isActiveParent = ($effectiveUrl === $pUrl || strpos($effectiveUrl . '/', $pUrl . '/') === 0);
                                }
                            }
                            ?>

                            <?php if (!empty($parent['children'])): ?>

                                <!-- Parent With Children -->
                                <li class="nxl-item nxl-hasmenu <?= $isActiveParent ? 'active nxl-trigger' : ''; ?>" data-menu-id="<?= $parent['Bmid']; ?>" data-menu-name="<?= htmlspecialchars($parent['MenuName']); ?>">

                                    <a href="javascript:void(0);" class="nxl-link">
                                        <span class="nxl-micon">
                                            <i class="<?= !empty($parent['IconClass']) ? $parent['IconClass'] : 'feather-circle'; ?>"></i>
                                        </span>

                                        <span class="nxl-mtext"><?= $parent['MenuName']; ?></span>

                                        <span class="nxl-arrow">
                                            <i class="feather-chevron-right"></i>
                                        </span>
                                    </a>

                                    <ul class="nxl-submenu" style="<?= $isActiveParent ? 'display:block;' : 'display:none;'; ?>">

                                        <?php foreach ($parent['children'] as $child): ?>
                                            <?php
                                            $childUrl = strtolower(trim($child['MenuUrl'] ?? '', '/'));
                                            $isChildActive = false;
                                            if (!empty($childUrl) && $childUrl !== '#') {
                                                if ($effectiveUrl === $childUrl) {
                                                    $isChildActive = true;
                                                } elseif (isset($urlAliases[$effectiveUrl]) && $urlAliases[$effectiveUrl] === $childUrl) {
                                                    $isChildActive = true;
                                                } elseif ($activeChildUrl === $childUrl) {
                                                    $isChildActive = true;
                                                } elseif (strpos($effectiveUrl . '/', $childUrl . '/') === 0) {
                                                    $isChildActive = true;
                                                }
                                            }
                                            ?>
                                            <li class="nxl-item <?= $isChildActive ? 'active' : ''; ?>">

                                                <a class="nxl-link" href="<?= base_url($child['MenuUrl']); ?>">

                                                    <?php $childIcon = !empty($child['IconClass']) ? str_replace('feather-calender', 'feather-calendar', $child['IconClass']) : 'feather-circle'; ?>
                                                    <i class="<?= $childIcon; ?> me-2" aria-hidden="true"></i>

                                                    <?= $child['MenuName']; ?>
                                                </a>

                                            </li>
                                        <?php endforeach; ?>

                                    </ul>
                                </li>

                            <?php else: ?>

                                <!-- Parent Without Children -->
                                <li class="nxl-item <?= $isActiveParent ? 'active' : ''; ?>" data-menu-id="<?= $parent['Bmid']; ?>" data-menu-name="<?= htmlspecialchars($parent['MenuName']); ?>">

                                    <a class="nxl-link" href="<?= base_url($parent['MenuUrl']); ?>">

                                        <span class="nxl-micon">
                                            <i class="<?= !empty($parent['IconClass']) ? $parent['IconClass'] : 'feather-grid'; ?>"></i>
                                        </span>

                                        <span class="nxl-mtext"><?= $parent['MenuName']; ?></span>
                                    </a>

                                </li>

                            <?php endif; ?>

                        <?php endforeach; ?>

                    </ul>
                    <?php endif; ?>
                              
            </div>
        </div>
    </nav>
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Manu !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [Start] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                        <i class="feather-align-left"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
                <!--! [Start] nxl-lavel-mega-menu-toggle !-->
                <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                    <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                        <i class="feather-align-left"></i>
                    </a>
                </div>
                <!--! [End] nxl-lavel-mega-menu-toggle !-->
                <!--! [Start] nxl-lavel-mega-menu !-->
                <div class="nxl-drp-link nxl-lavel-mega-menu">
                    <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                        <a href="javascript:void(0)" id="nxl-lavel-mega-menu-hide">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                   
                </div>
                <!--! [End] nxl-lavel-mega-menu !-->
            </div>
            <!--! [End] Header Left !-->
            <!--! [Start] Header Right !-->
            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">
                   
                   
                    <div class="nxl-h-item d-none d-sm-flex">
                        <div class="full-screen-switcher">
                            <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                                <i class="feather-maximize maximize"></i>
                                <i class="feather-minimize minimize"></i>
                            </a>
                        </div>
                    </div>
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    <!-- <div class="dropdown nxl-h-item">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                            <i class="feather-clock"></i>
                            <span class="badge bg-success nxl-h-badge">2</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-timesheets-menu">
                            <div class="d-flex justify-content-between align-items-center timesheets-head">
                                <h6 class="fw-bold text-dark mb-0">Timesheets</h6>
                                <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto" data-bs-toggle="tooltip" title="Upcomming Timers">
                                    <i class="feather-clock"></i>
                                    <span>3 Upcomming</span>
                                </a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-column timesheets-body">
                                <i class="feather-clock fs-1 mb-4"></i>
                                <p class="text-muted">No started timers found yes!</p>
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary">Started Timer</a>
                            </div>
                            <div class="text-center timesheets-footer">
                                <a href="javascript:void(0);" class="fs-13 fw-semibold text-dark">Alls Timesheets</a>
                            </div>
                        </div>
                    </div> -->
                    <div class="dropdown nxl-h-item">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                            <img src="<?=$theme_path?>/assets/images/avatar/1.png" alt="user-image" class="img-fluid user-avtar me-0" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    <img src="<?=$theme_path?>/assets/images/avatar/1.png" alt="user-image" class="img-fluid user-avtar" />
                                    <div>
                                        <h6 class="text-dark mb-0"><?php echo $employee_det['BName'];?></h6>
                                        <span class="fs-12 fw-medium text-muted"><?php echo $employee_det['BEmail'];?></span>
                                    </div>
                                </div>
                            </div>  
                             <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#userProfileModal">
                                <i class="feather-user"></i>
                                <span>Profile Details</span>
                            </a>
                           <!--  <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-activity"></i>
                                <span>Activity Feed</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-dollar-sign"></i>
                                <span>Billing Details</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-bell"></i>
                                <span>Notifications</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-settings"></i>
                                <span>Account Settings</span>
                            </a> -->
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $this->config->item('base_url'); ?>bid/logout" class="dropdown-item">
                                <i class="feather-log-out"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--! [End] Header Right !-->
        </div>
    </header>

    <main class="nxl-container">
        <div class="nxl-content">
            <!-- [ page-header ] start -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10"><?= !empty($PageTitle) ? htmlspecialchars($PageTitle) : (!empty($currentUrlArray['child']['MenuName']) ? htmlspecialchars($currentUrlArray['child']['MenuName']) : 'Dashboard'); ?></h5>
                    </div>
                          <ul class="breadcrumb <?= $isActive ? 'active' : ''; ?>">

                                <?php if (!empty($currentUrlArray['parent'])): ?>
                                    <li class="breadcrumb-item">
                                        <?php if ($currentUrlArray['parent']['MenuUrl'] !== '#'): ?>
                                            <a href="<?= base_url($currentUrlArray['parent']['MenuUrl']); ?>">
                                                <?= $currentUrlArray['parent']['MenuName']; ?>
                                            </a>
                                        <?php else: ?>
                                            <?= $currentUrlArray['parent']['MenuName']; ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>

                                <?php if (!empty($currentUrlArray['child'])): ?>
                                    <li class="breadcrumb-item active">
                                        <?= $currentUrlArray['child']['MenuName']; ?>
                                    </li>
                                <?php endif; ?>

                            </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="page-header-right-items">
                        <div class="d-flex d-md-none">
                            <a href="javascript:void(0)" class="page-header-right-close-toggle">
                                <i class="feather-arrow-left me-2"></i>
                                <span>Back</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        </div>
                    </div>
                    <div class="d-md-none d-flex align-items-center">
                        <a href="javascript:void(0)" class="page-header-right-open-toggle">
                            <i class="feather-align-right fs-20"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- [ page-header ] end -->
            <div id="page-header-collapse-target"></div>
            <!-- [ Main-Content ] Starts -->
           <div class="main-content">
              <?php echo $content;?>
           </div>
            <!-- [ Main-Content ] End -->
        </div>
        <!-- [ Footer ] start -->
        <footer class="footer d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 px-4">
            <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                <span>Copyright ©</span>
                <script>
                    document.write(new Date().getFullYear());
                </script>
            </p>
            <p class="fs-11 text-muted mb-0"><span>Designed & Developed By: <a target="_blank" href="https://www.i-net.in/">I-NET Secure Labs Pvt Ltd</a></span></p>
        </footer>
        <!-- [ Footer ] end -->
    </main>

    <!-- User Profile Details Modal -->
    <div class="modal fade" id="userProfileModal" tabindex="-1" aria-labelledby="userProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-light py-3 px-4 border-bottom">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center mb-0" id="userProfileModalLabel">
                        <i class="feather-user text-primary me-2 fs-18"></i> Profile Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-4 text-center border-end-md pb-3 pb-md-0">
                            <div class="avatar-image avatar-xl mx-auto mb-3">
                                <img src="<?=$theme_path?>/assets/images/avatar/1.png" alt="user-avatar" class="img-fluid rounded-circle border shadow-sm" style="width: 90px; height: 90px; object-fit: cover;" />
                            </div>
                            <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($currentUserProfile['Name'] ?? ($employee_det['BName'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="text-muted fs-12 mb-2"><?= htmlspecialchars($currentUserProfile['Designation'] ?? 'Bid Management Team', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="mb-2">
                                <span class="badge bg-primary px-3 py-1 fs-12"><?= htmlspecialchars($currentUserRoleName, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div>
                                <?php if (($currentUserProfile['Status'] ?? 1) == 1): ?>
                                    <span class="badge bg-soft-success text-success border border-success px-2 py-1 fs-11"><i class="feather-check-circle me-1"></i>Active Account</span>
                                <?php else: ?>
                                    <span class="badge bg-soft-danger text-danger border border-danger px-2 py-1 fs-11"><i class="feather-x-circle me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">User Role</div>
                                        <div class="fs-14 fw-bold text-dark d-flex align-items-center">
                                            <i class="feather-shield text-primary me-2"></i> <?= htmlspecialchars($currentUserRoleName, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Account Status</div>
                                        <div class="fs-14 fw-bold d-flex align-items-center">
                                            <?php if (($currentUserProfile['Status'] ?? 1) == 1): ?>
                                                <i class="feather-check-circle text-success me-2"></i>
                                                <span class="text-success">Active</span>
                                            <?php else: ?>
                                                <i class="feather-x-circle text-danger me-2"></i>
                                                <span class="text-danger">Inactive</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Email Address</div>
                                        <div class="fs-14 fw-bold text-dark d-flex align-items-center text-truncate" title="<?= htmlspecialchars($currentUserProfile['Email'] ?? ($employee_det['BEmail'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="feather-mail text-primary me-2"></i> <?= htmlspecialchars($currentUserProfile['Email'] ?? ($employee_det['BEmail'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Mobile Number</div>
                                        <div class="fs-14 fw-bold text-dark d-flex align-items-center">
                                            <i class="feather-phone text-primary me-2"></i> <?= !empty($currentUserProfile['Mobile']) ? htmlspecialchars($currentUserProfile['Mobile'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted fw-normal">Not Provided</span>'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Designation</div>
                                        <div class="fs-14 fw-bold text-dark d-flex align-items-center">
                                            <i class="feather-briefcase text-primary me-2"></i> <?= !empty($currentUserProfile['Designation']) ? htmlspecialchars($currentUserProfile['Designation'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted fw-normal">Not Specified</span>'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fs-11 text-muted text-uppercase fw-semibold mb-1">Member Since</div>
                                        <div class="fs-14 fw-semibold text-dark d-flex align-items-center">
                                            <i class="feather-calendar text-primary me-2"></i> <?= (!empty($currentUserProfile['CreationTs']) && strtotime($currentUserProfile['CreationTs'])) ? date('d M Y', strtotime($currentUserProfile['CreationTs'])) : '<span class="text-muted fw-normal">-</span>'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->
    <div class="theme-customizer">
        <div class="customizer-handle">
            <a href="javascript:void(0);" class="cutomizer-open-trigger bg-primary">
                <i class="feather-settings"></i>
            </a>
        </div>
        <div class="customizer-sidebar-wrapper">
            <div class="customizer-sidebar-header px-4 ht-80 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Theme Settings</h5>
                <a href="javascript:void(0);" class="cutomizer-close-trigger d-flex">
                    <i class="feather-x"></i>
                </a>
            </div>
            <div class="customizer-sidebar-body position-relative p-4" data-scrollbar-target="#psScrollbarInit">
                <!--! BEGIN: [Navigation] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Navigation</label>
                    <div class="row g-2 theme-options-items app-navigation" id="appNavigationList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-navigation-light" name="app-navigation" value="1" data-app-navigation="app-navigation-light" checked />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-navigation-light">Light</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-navigation-dark" name="app-navigation" value="2" data-app-navigation="app-navigation-dark" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-navigation-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Navigation] !-->
                <!--! BEGIN: [Header] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set mt-5">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Header</label>
                    <div class="row g-2 theme-options-items app-header" id="appHeaderList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-header-light" name="app-header" value="1" data-app-header="app-header-light" checked />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-header-light">Light</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-header-dark" name="app-header" value="2" data-app-header="app-header-dark" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-header-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Header] !-->
                <!--! BEGIN: [Skins] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-5 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Skins</label>
                    <div class="row g-2 theme-options-items app-skin" id="appSkinList">
                        <div class="col-6 text-center position-relative single-option light-button active">
                            <input type="radio" class="btn-check" id="app-skin-light" name="app-skin" value="1" data-app-skin="app-skin-light" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-skin-light">Light</label>
                        </div>
                        <div class="col-6 text-center position-relative single-option dark-button">
                            <input type="radio" class="btn-check" id="app-skin-dark" name="app-skin" value="2" data-app-skin="app-skin-dark" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-skin-dark">Dark</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Skins] !-->
                <!--! BEGIN: [Typography] !-->
                <div class="position-relative px-3 pb-3 pt-4 mt-3 mb-0 border border-gray-2 theme-options-set">
                    <label class="py-1 px-2 fs-8 fw-bold text-uppercase text-muted text-spacing-2 bg-white border border-gray-2 position-absolute rounded-2 options-label" style="top: -12px">Typography</label>
                    <div class="row g-2 theme-options-items font-family" id="fontFamilyList">
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-lato" name="font-family" value="1" data-font-family="app-font-family-lato" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-lato">Lato</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-rubik" name="font-family" value="2" data-font-family="app-font-family-rubik" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-rubik">Rubik</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-inter" name="font-family" value="3" data-font-family="app-font-family-inter" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-inter">Inter</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-cinzel" name="font-family" value="4" data-font-family="app-font-family-cinzel" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-cinzel">Cinzel</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-nunito" name="font-family" value="6" data-font-family="app-font-family-nunito" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-nunito">Nunito</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-roboto" name="font-family" value="7" data-font-family="app-font-family-roboto" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-roboto">Roboto</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-ubuntu" name="font-family" value="8" data-font-family="app-font-family-ubuntu" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-ubuntu">Ubuntu</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-poppins" name="font-family" value="9" data-font-family="app-font-family-poppins" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-poppins">Poppins</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-raleway" name="font-family" value="10" data-font-family="app-font-family-raleway" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-raleway">Raleway</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-system-ui" name="font-family" value="11" data-font-family="app-font-family-system-ui" checked />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-system-ui">System UI</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-noto-sans" name="font-family" value="12" data-font-family="app-font-family-noto-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-noto-sans">Noto Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-fira-sans" name="font-family" value="13" data-font-family="app-font-family-fira-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-fira-sans">Fira Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-work-sans" name="font-family" value="14" data-font-family="app-font-family-work-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-work-sans">Work Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-open-sans" name="font-family" value="15" data-font-family="app-font-family-open-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-open-sans">Open Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-maven-pro" name="font-family" value="16" data-font-family="app-font-family-maven-pro" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-maven-pro">Maven Pro</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-quicksand" name="font-family" value="17" data-font-family="app-font-family-quicksand" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-quicksand">Quicksand</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-montserrat" name="font-family" value="18" data-font-family="app-font-family-montserrat" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-montserrat">Montserrat</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-josefin-sans" name="font-family" value="19" data-font-family="app-font-family-josefin-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-josefin-sans">Josefin Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-ibm-plex-sans" name="font-family" value="20" data-font-family="app-font-family-ibm-plex-sans" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-ibm-plex-sans">IBM Plex Sans</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-source-sans-pro" name="font-family" value="5" data-font-family="app-font-family-source-sans-pro" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-source-sans-pro">Source Sans Pro</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-montserrat-alt" name="font-family" value="21" data-font-family="app-font-family-montserrat-alt" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-montserrat-alt">Montserrat Alt</label>
                        </div>
                        <div class="col-6 text-center single-option">
                            <input type="radio" class="btn-check" id="app-font-family-roboto-slab" name="font-family" value="22" data-font-family="app-font-family-roboto-slab" />
                            <label class="py-2 fs-9 fw-bold text-dark text-uppercase text-spacing-1 border border-gray-2 w-100 h-100 c-pointer position-relative options-label" for="app-font-family-roboto-slab">Roboto Slab</label>
                        </div>
                    </div>
                </div>
                <!--! END: [Typography] !-->
            </div>
           <!--  <div class="customizer-sidebar-footer px-4 ht-60 border-top d-flex align-items-center gap-2">
                <div class="flex-fill w-50">
                    <a href="javascript:void(0);" class="btn btn-danger" data-style="reset-all-common-style">Reset</a>
                </div>
                <div class="flex-fill w-50">
                    <a href="https://www.themewagon.com/themes/Duralux-admin" target="_blank" class="btn btn-primary">Download</a>
                </div>
            </div> -->
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
   <!--! BEGIN: Vendors JS !-->
    <!-- vendors.min.js loaded in HEAD -->
    <script src="<?=$theme_path?>/assets/vendors/js/bootstrap.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/dataTables.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/dataTables.bs5.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/jszip.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/dataTables.buttons.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/buttons.bootstrap5.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/buttons.html5.min.js"></script>
    <!--! Centralised DataTable Export Buttons Configuration & Initialization in BO Template !-->
    <script>
    (function($) {
        'use strict';
        if (!window.jQuery || !$.fn.DataTable) {
            return;
        }

        // Centralised column filter: excludes columns marked with .no-export, hidden columns,
        // and Action/Actions columns from Copy, CSV, and Excel exports.
        var exportColumnFilter = function(idx, data, node) {
            var $cell = $(node);
            if ($cell.hasClass('no-export')) return false;
            if (!$cell.is(':visible')) return false;
            var headerText = $.trim($cell.text()).toLowerCase();
            if (headerText === 'action' || headerText === 'actions') return false;
            return true;
        };

        // Common button configurations
        var defaultButtons = [
            {
                extend: 'copyHtml5',
                text: '<i class="feather-copy me-1"></i> Copy',
                className: 'btn btn-sm btn-light-brand',
                exportOptions: {
                    columns: exportColumnFilter,
                    modifier: { search: 'applied', order: 'applied' }
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="feather-file-text me-1"></i> CSV',
                className: 'btn btn-sm btn-light-brand',
                exportOptions: {
                    columns: exportColumnFilter,
                    modifier: { search: 'applied', order: 'applied' }
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="feather-download me-1"></i> Excel',
                className: 'btn btn-sm btn-light-brand',
                title: function() {
                    var title = $('.page-header-title h5').text() || document.title || 'Export';
                    return $.trim(title).replace(/\s+/g, '_');
                },
                exportOptions: {
                    columns: exportColumnFilter,
                    modifier: { search: 'applied', order: 'applied' }
                }
            }
        ];

        // Safely preserve PDF export if pdfMake dependency is available
        if (window.pdfMake || ($.fn.dataTable.Buttons && $.fn.dataTable.Buttons.pdfMake)) {
            defaultButtons.push({
                extend: 'pdfHtml5',
                text: '<i class="feather-file me-1"></i> PDF',
                className: 'btn btn-sm btn-light-brand',
                exportOptions: {
                    columns: exportColumnFilter,
                    modifier: { search: 'applied', order: 'applied' }
                }
            });
        }

        // Safely preserve Print if print button extension is available
        if ($.fn.dataTable.ext && $.fn.dataTable.ext.buttons && $.fn.dataTable.ext.buttons.print) {
            defaultButtons.push({
                extend: 'print',
                text: '<i class="feather-printer me-1"></i> Print',
                className: 'btn btn-sm btn-light-brand',
                exportOptions: {
                    columns: exportColumnFilter,
                    modifier: { search: 'applied', order: 'applied' }
                }
            });
        }

        // Centralised DataTable defaults for all BO pages
        $.extend(true, $.fn.dataTable.defaults, {
            retrieve: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom"<"dt-buttons-wrapper d-flex align-items-center gap-2"B><"d-flex align-items-center gap-2 ms-auto"f>>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top"lip>',
            buttons: defaultButtons,
            language: {
                search: "",
                searchPlaceholder: "Search records..."
            }
        });
    })(jQuery);
    </script>
   <!--  <script src="<?=$theme_path?>/assets/vendors/js/tagify.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/tagify-data.min.js"></script> -->
    <script src="<?=$theme_path?>/assets/vendors/js/select2.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/select2-active.min.js"></script>
     <script src="<?=$theme_path?>/assets/vendors/js/datepicker.min.js"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <script src="<?=$theme_path?>/assets/vendors/js/daterangepicker.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/apexcharts.min.js"></script>
    <script src="<?=$theme_path?>/assets/vendors/js/circle-progress.min.js"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="<?=$theme_path?>/assets/js/common-init.min.js"></script>
     <script src="<?=$theme_path?>/assets/js/proposal-create-init.min.js"></script>
         <script src="<?=$theme_path?>/assets/js/customers-create-init.min.js"></script>
         <script src="<?=$theme_path?>/assets/js/customers-init.min.js"></script>

    <script src="<?=$theme_path?>/assets/js/dashboard-init.min.js"></script>
    <script src="<?=$theme_path?>/assets/js/dashboard.js?v=<?= time(); ?>"></script>
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    <script src="<?=$theme_path?>/assets/js/theme-customizer-init.min.js"></script>
    <!--! END: Theme Customizer !-->
    <script>
    $(document).ready(function() {
        var $actions = $('#page-filter-actions');
        if ($actions.length) {
            $('.page-header-right-items-wrapper').append($actions.children().show());
            $actions.remove();
        }
        var $collapse = $('#page-filter-collapse-content');
        if ($collapse.length) {
            $('#page-header-collapse-target').replaceWith($collapse.children());
            $collapse.remove();
        }
    });
    </script>

    <script>
    try {
        sessionStorage.removeItem('nxl_open_menu');
    } catch(e) {}
    </script>
</body>