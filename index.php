<?php

session_start(); // เริ่มต้นใช้งาน session
date_default_timezone_set("Asia/Bangkok"); // ตั้งค่า timezone ให้เป็นเวลาประเทศไทย
error_reporting(0); // ปิดการแจ้งเตือน error

include_once "./core/config/config_website.php"; // ไฟล์กำหนดค่าเว็บไซต์
include_once "./core/services/route.php"; // ไฟล์จัดการเส้นทาง
include_once "./core/services/query.php"; // ไฟล์จัดการคำสั่ง SQL
include_once "./core/helpers/utility.php"; // ไฟล์ช่วยเหลือ
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_page_title(), ' - ', WEBSITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light">
    <?php

    include_once "./layouts/navbar.php";
    include_once get_page_path();
    include_once "./layouts/footer.php";

    ?>
    <script src="assets/swiper/swiper-bundle.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="assets/script.js"></script>
</body>
</html>