<?php
session_start(); // เริ่มต้นใช้งาน session
date_default_timezone_set("Asia/Bangkok"); // ตั้งค่า timezone ให้เป็นเวลาประเทศไทย
// error_reporting(0); // ปิดการแจ้งเตือน error

include_once "../core/config/config_website.php"; // ไฟล์กำหนดค่าเว็บไซต์
include_once "../core/services/route.php"; // ไฟล์จัดการเส้นทาง
include_once "../core/services/query.php"; // ไฟล์จัดการคำสั่ง SQL
include_once "../core/helpers/utility.php"; // ไฟล์ช่วยเหลือ

function format_date_thai($date, $format = 1)
{
    $month_th = array(
        '01' => 'มกราคม',
        '02' => 'กุมภาพันธ์',
        '03' => 'มีนาคม',
        '04' => 'เมษายน',
        '05' => 'พฤษภาคม',
        '06' => 'มิถุนายน',
        '07' => 'กรกฎาคม',
        '08' => 'สิงหาคม',
        '09' => 'กันยายน',
        '10' => 'ตุลาคม',
        '11' => 'พฤศจิกายน',
        '12' => 'ธันวาคม'
    );
    if ($format == 1) {
        $thai_date = date('d', strtotime($date)) . ' ' . $month_th[date('m', strtotime($date))] . ' ' . (date('Y', strtotime($date)) + 543);
        return $thai_date;
    } else {
        $thai_date = date('d', strtotime($date)) . ' ' . $month_th[date('m', strtotime($date))] . ' ' . (date('Y', strtotime($date)) + 543) . ' เวลา ' . date('H:i น.', strtotime($date));
        return $thai_date;
    }
}

if (!is_admin()) {
    echo "<script> location.href = '../?page=home'; </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_admin_page_title(), ' - หลังบ้าน ', WEBSITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            min-height: 100vh;
            min-height: -webkit-fill-available;
        }

        html {
            height: -webkit-fill-available;
        }

        .layout {
            display: grid;
            grid-template-areas: "sidebar main";
            grid-template-columns: 1fr 5fr;
            gap: 1.5rem;
            padding-left: 0;
            padding-right: 0;
        }

        .main-content {
            margin-top: 1rem !important;
            margin-right: 1.5rem;
        }

        .show-print {
            display: none !important;
        }

        @media print {

            .hide-print {
                display: none !important;
            }

            .show-print {
                display: block !important;
            }
        }
    </style>
</head>

<body class="bg-light">
    <?php include_once "./layouts/navbar.php"; ?>
    <div class="layout container-fluid">
        <?php include_once "./layouts/sidebar.php"; ?>
        <div class="main-content">
            <?php include_once get_admin_page_path(); ?>
        </div>
    </div>
    <?php include_once "./layouts/footer.php"; ?>
    <script src="../assets/bootstrap/js/bootstrap.bundle.js"></script>
</body>

</html>