<?php

define('ROUTE_PATH', 'pages/');
define('DEFAULT_ROUTE', 'home');
define('ERROR_ROUTE', '404');
define('ROUTE_PARAM', 'page');
define('ROUTE_EXTENSION', '.php');
define('ROUTES', [
    '404' => '404',
    'home' => 'หน้าแรก',
    'login' => 'เข้าสู่ระบบ',
    'register' => 'สร้างบัญชี',
    'profile' => 'โปรไฟล์',
    'logout' => 'ออกจากระบบ',
    'cart' => 'ตะกร้า',
    'cart-remove' => 'ลบสินค้าในตะกร้า',
    'cart-increase' => 'เพิ่มจำนวนสินค้าในตะกร้า',
    'cart-decrease' => 'ลดจำนวนสินค้าในตะกร้า',
    'confirm' => 'ยืนยันการสั่งซื้อ',
    'order-history' => 'ประวัติการสั่งซื้อ',
    'payment' => 'ชำระการสั่งซื้อ',
    'order-detail' => 'รายละเอียดการสั่งซื้อ',
    'profile-edit' => 'แก้ไขข้อมูลส่วนตัว',
    'change-password' => 'เปลี่ยนรหัสผ่าน',
    'products' => 'สินค้า',
    'product' => 'รายละเอียดสินค้า',
    'faq' => 'วิธีการสั่งซื้อ',
    'contact' => 'ติดต่อเรา',
    'forgot-password' => 'ลืมรหัสผ่าน',
    'tos-and-privacy' => 'ข้อกำหนดและนโยบายความเป็นส่วนตัว',
]);
define('ADMIN_ROUTE_PATH', 'admin/pages/');
define('ADMIN_ROUTES', [
    '404' => '404',
    'home' => 'หน้าแรก',
    'profile' => 'โปรไฟล์',
    'logout' => 'ออกจากระบบ',
    'users' => 'รายการผู้ใช้',
    'banners' => 'ป้ายโฆษณา',
    'products' => 'รายการสินค้า',
    'product_stocks' => 'ตรวจสอบสินค้าคงคลัง',
    'product_types' => 'ประเภทสินค้า',
    'orders' => 'รายการคำสั่งซื้อ',
    'order' => 'รายละเอียดการสั่งซื้อ',
    'reports' => 'รายงาน',
    'report-user-roles' => 'รายงานสิทธิ์',
    'report-users' => 'รายงานสมาชิก',
    'report-product-types' => 'รายงานประเภทสินค้า',
    'report-products' => 'รายงานข้อมูลสินค้า',
    'report-sales' => 'รายงานขายสินค้า',
    'report-payments' => 'รายงานการแจ้งชำระเงิน',
    'report-product-stocks' => 'รายงานยอดสินค้า',
    'report-best-sellers' => 'รายงานสินค้าขายดี',
    'report-revenue' => 'รายงานยอดขาย',
]);