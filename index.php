<?php
    session_start();
    error_reporting( E_ERROR | E_WARNING | E_PARSE );

    // ตรวจสอบการ login
    if ( !isset( $_SESSION['user_id'] ) ) {
        header( "Location: login.php" );
        exit;
    }
    require realpath( '../dv-config.php' );
    require DEV_PATH . '/classes/db.class.v2.php';
    require DEV_PATH . '/functions/global.php';
    // require_once realpath('config/db.php');

    // สำหรับ dev parameter (อนุญาตเฉพาะ alphanumeric)
    $GET_DEV = sanitizeGetParam( 'dev', 'alphanumeric', '', 50 );

    define( 'Q_VERSION', '1.0.0' );
    define( 'Q_TITLE', 'EDE System - ระบบจัดการเอกสาร' );
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="ATH Development Team">
    <meta name="keywords" content="document,management,system,ede,tracking">
    <meta name="description" content="ระบบจัดการเอกสารอิเล็กทรอนิกส์ โรงพยาบาลอ่างทอง">
    <title><?php echo Q_TITLE . Q_VERSION; ?> </title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/@fortawesome/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/select2/dist/css/select2.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/fonts/maledpan/maledpan.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/fonts/chatthai/chatthai.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div class="content-wrapper">

            <?php
                // กำหนดตัวแปรสำหรับ JavaScript ที่จะโหลด
                $jsReq  = '';
                $jsVars = ''; // ตัวแปร JavaScript ที่ต้องการส่งจาก PHP

                switch ( $GET_DEV ) {

                    case '':
                    case 'main':
                        // หน้าเมนูหลัก
                        $page_title = "เมนูหลัก (Main Menu)";
                        $header_class = "header-menu";
                        break;

                    case 'dashboard':
                        // หน้า Dashboard
                        $page_title = "Dashboard (ภาพรวม)";
                        $header_class = "header-dashboard";
                        $jsReq = '_scripts/dashboard.js';
                        break;

                    case 'register':
                        // หน้าลงทะเบียนเอกสาร
                        $page_title = "ลงทะเบียน";
                        $header_class = "header-register";
                        $jsReq  = '_scripts/register.js';
                        $jsVars = "const CURRENT_USER_ID = '" . ( $_SESSION['user_id'] ?? '' ) . "';";
                        break;

                    case 'tracking':
                        // หน้าติดตามเอกสาร
                        $page_title = "ติดตามเอกสาร";
                        $header_class = "header-tracking";
                        break;

                    case 'report':
                        // หน้ารายงาน
                        $page_title = "รายงาน";
                        $header_class = "header-report";
                        break;

                    case 'settings':
                        // หน้าตั้งค่า
                        $page_title = "ตั้งค่าระบบ";
                        $header_class = "header-settings";
                        break;

                    case 'scan-history':
                        // หน้าประวัติการสแกน
                        $page_title = "ประวัติการสแกน";
                        $header_class = "header-settings";
                        break;

                    case 'workflow-settings':
                        // หน้าจัดการสถานะ
                        $page_title = "จัดการสถานะ";
                        $header_class = "header-status_settings";
                        break;

                    default:
                        // หน้า 404
                        $page_title = "404 - ไม่พบหน้าที่ต้องการ";
                        $header_class = "header-danger";
                        break;
                }

                // แสดง topbar ก่อน page content
                include 'includes/topbar.php';

                // จากนั้นโหลด page content
                switch ( $GET_DEV ) {

                    case '':
                    case 'main':
                        require 'pages/main-menu.php';
                        break;

                    case 'dashboard':
                        require 'pages/dashboard-page.php';
                        break;

                    case 'register':
                        require 'pages/register-page.php';
                        break;

                    case 'tracking':
                        require 'pages/tracking-page.php';
                        break;

                    case 'report':
                        require 'pages/report-page.php';
                        break;

                    case 'settings':
                        require 'pages/settings-page.php';
                        break;

                    case 'scan-history':
                        require 'pages/scan-history-page.php';
                        break;

                    case 'workflow-settings':
                        require 'pages/workflow-settings-page.php';
                        break;

                    default:
                        require 'pages/page-not-found.php';
                        break;
                }

            ?>

        </div><!-- .content-wrapper -->
    </div><!-- .d-flex -->

    <!-- Core JavaScript -->
    <!-- <script src="<?php echo ASSET_PATH; ?>/jquery/dist/jquery.min.js"></script> -->
    <script src="<?php echo ASSET_PATH; ?>/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <!-- Global Scripts -->
    <script src="_scripts/global.js"></script>

    <!-- Page Specific Variables & Scripts -->
    <?php if ( !empty( $jsVars ) ): ?>
    <script>
        <?php echo $jsVars; ?>
    </script>
    <?php endif; ?>

    <?php if ( !empty( $jsReq ) && file_exists( $jsReq ) ): ?>
    <script src="<?php echo $jsReq; ?>?v=<?php echo filemtime( $jsReq ); ?>"></script>
    <?php endif; ?>

</body>
</html>