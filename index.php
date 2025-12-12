<?php
    session_start();
    error_reporting( E_ALL );
    //error_reporting( E_ERROR | E_WARNING | E_PARSE );

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
    <!-- <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/fonts/maledpan/maledpan.css">
    <link rel="stylesheet" href="<?php echo ASSET_PATH; ?>/fonts/chatthai/chatthai.css"> -->
    <link href="<?php echo SITE_URL;?>/css/main.min.css" rel="stylesheet">
</head>

<body>

    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <div class="content-wrapper">

            <?php
                // กำหนดตัวแปรสำหรับ JavaScript ที่จะโหลด และโหลด page content
                $jsReq  = '';
                $jsExt = '';
                $pageFile = '';

                switch ( $GET_DEV ) {

                    case '':
                    case 'main':
                        $jsReq = '';
                        $pageFile = 'pages/main-menu.php';
                        break;

                    case 'dashboard':
                        $jsReq = 'js/dashboard.min.js';
                        $pageFile = 'pages/dashboard-page.php';
                        break;

                    case 'register':
                        $jsReq  = 'js/register.min.js';
                        $jsExt = "const CURRENT_USER_ID = '" . ( $_SESSION['user_id'] ?? '' ) . "';";
                        $pageFile = 'pages/register-page.php';
                        break;

                    case 'tracking':
                        $pageFile = 'pages/tracking-page.php';
                        break;

                    case 'report':
                        $pageFile = 'pages/report-page.php';
                        break;

                    case 'settings':
                        $pageFile = 'pages/settings-page.php';
                        break;

                    case 'scan-history':
                        $pageFile = 'pages/scan-history-page.php';
                        break;

                    case 'workflow-settings':
                        $pageFile = 'pages/workflow-settings-page.php';
                        break;

                    default:
                        $pageFile = 'pages/page-not-found.php';
                        break;
                }

                // โหลด page content (แต่ละ page จะตั้งค่า $page_title และ $header_class เอง)
                require $pageFile;

            ?>

        </div><!-- .content-wrapper -->
    </div><!-- .d-flex -->

    <!-- Core JavaScript -->
    <script>
        const site_url = '<?php echo SITE_URL; ?>';
    </script>
    <!-- <script src="<?php echo ASSET_PATH; ?>/jquery/dist/jquery.min.js"></script> -->
    <script src="<?php echo ASSET_PATH; ?>/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <!-- Global Scripts -->
    <script src="<?php echo SITE_URL; ?>/js/global.min.js?v=<?php echo filemtime( 'js/global.min.js' ); ?>"></script>


    <script async>
    'use strict';
    <?php echo( isset( $jsExt ) ) ? $jsExt : ''; ?>
    <?php ( isset( $jsReq ) ) ? require $jsReq : ''; ?>
  </script>

</body>
</html>