<?php
function is_active($page)
{
    if (is_array($page)) {
        return in_array(get_current_page(), $page) ? 'active' : '';
    }

    return get_current_page() === $page ? 'active' : '';
}
?>
<style>
    .sidebar {
        /* width: 17.5rem; */
        width: 100%;
        height: 100vh;
        position: sticky;
        display: block !important;
        overflow-y: auto;
        top: 0;
    }

    .btn-toggle {
        padding: .25rem .5rem;
        font-weight: 600;
        color: var(--bs-emphasis-color);
        background-color: transparent;
    }

    .btn-toggle:hover,
    .btn-toggle:focus {
        color: rgba(var(--bs-emphasis-color-rgb), .85);
        background-color: var(--bs-tertiary-bg);
    }

    .btn-toggle::before {
        width: 1.25em;
        line-height: 0;
        content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba%280,0,0,.5%29' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
        transition: transform .35s ease;
        transform-origin: .5em 50%;
    }

    [data-bs-theme="dark"] .btn-toggle::before {
        content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba%28255,255,255,.5%29' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
    }

    .btn-toggle[aria-expanded="true"] {
        color: rgba(var(--bs-emphasis-color-rgb), .85);
    }

    .btn-toggle[aria-expanded="true"]::before {
        transform: rotate(90deg);
    }

    .btn-toggle-nav a {
        padding: .1875rem .5rem;
        margin-top: .125rem;
        margin-left: 1.25rem;
        text-decoration: none;
        border-radius: 0.375rem;
        border-bottom: 2px solid transparent;
    }

    .btn-toggle-nav a:hover,
    .btn-toggle-nav a:focus {
        background-color: var(--bs-tertiary-bg);
    }

    .btn-toggle-nav a.active {
        font-weight: 600;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-color: black;
    }
</style>

<div class="flex-shrink-0 p-3 bg-white shadow-sm sidebar hide-print">
    <a class="text-dark fw-bold text-decoration-none fs-5 pb-3 mb-3 d-flex border-bottom"
        href="?page=home"><?php echo WEBSITE_NAME ?></a>
    <ul class="list-unstyled ps-0">
        <li class="mb-1">
            <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 w-100"
                data-bs-toggle="collapse" data-bs-target="#main-collapse" aria-expanded="true">
                ทั่วไป
            </button>
            <div class="collapse show" id="main-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="?page=home"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('home'); ?>">หน้าแรก</a></li>
                    <li><a href="?page=users"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('users'); ?>">รายการผู้ใช้</a>
                    </li>
                    <li><a href="?page=banners"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('banners'); ?>">ป้ายโฆษณา</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="mb-1">
            <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 w-100"
                data-bs-toggle="collapse" data-bs-target="#product-collapse" aria-expanded="true">
                สินค้า
            </button>
            <div class="collapse show" id="product-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="?page=products"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('products'); ?>">รายการสินค้า</a>
                    </li>
                    <li><a href="?page=product_stocks"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('product_stocks'); ?>">ตรวจสอบสินค้าคงคลัง</a>
                    </li>
                    <li><a href="?page=product_types"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('product_types'); ?>">ประเภทสินค้า</a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="mb-1">
            <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 w-100"
                data-bs-toggle="collapse" data-bs-target="#order-collapse" aria-expanded="true">
                คำสั่งซื้อ
            </button>
            <div class="collapse show" id="order-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li><a href="?page=orders"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('orders'); ?>">รายการคำสั่งซื้อทั้งหมด</a>
                    </li>
                    <li><a href="?page=orders&status=2"
                            class="link-body-emphasis d-inline-flex">รายการคำสั่งซื้อรอตรวจสอบ</a></li>
                    <li><a href="?page=orders&status=3"
                            class="link-body-emphasis d-inline-flex">รายการคำสั่งซื้อรอจัดส่ง</a></li>
                </ul>
            </div>
        </li>
        <li class="mb-1">
            <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 w-100"
                data-bs-toggle="collapse" data-bs-target="#report-collapse" aria-expanded="true">
                การจัดการรายงาน
            </button>
            <div class="collapse show" id="report-collapse">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                    <li>
                        <a href="?page=reports"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('reports'); ?>">รายงานทั้งหมด</a>
                    </li>
                    <li>
                        <a href="?page=report-user-roles"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-user-roles'); ?>">รายงานสิทธิ์</a>
                    </li>
                    <li>
                        <a href="?page=report-users"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-users'); ?>">รายงานสมาชิก</a>
                    </li>
                    <li>
                        <a href="?page=report-product-types"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-product-types'); ?>">รายงานประเภทสินค้า</a>
                    </li>
                    <li>
                        <a href="?page=report-products"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-products'); ?>">รายงานข้อมูลสินค้า</a>
                    </li>
                    <li>
                        <a href="?page=report-sales"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-sales'); ?>">รายงานขายสินค้า</a>
                    </li>
                    <li>
                        <a href="?page=report-payments"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-payments'); ?>">รายงานการแจ้งชำระเงิน</a>
                    </li>
                    <li>
                        <a href="?page=report-product-stocks"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-product-stocks'); ?>">รายงานยอดสินค้า</a>
                    </li>
                    <li>
                        <a href="?page=report-best-sellers"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-best-sellers'); ?>">รายงานสินค้าขายดี</a>
                    </li>
                    <li>
                        <a href="?page=report-revenue"
                            class="link-body-emphasis d-inline-flex <?php echo is_active('report-revenue'); ?>">รายงานยอดขาย</a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>