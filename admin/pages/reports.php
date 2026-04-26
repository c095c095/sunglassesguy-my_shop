<?php
// รายงานทั้งหมด - Reports Landing Page

$report_items = [
    [
        'page' => 'report-user-roles',
        'icon' => 'bi-shield-lock',
        'title' => 'รายงานสิทธิ์',
        'desc' => 'รายงานข้อมูลระดับสิทธิ์ของผู้ใช้ในระบบ',
    ],
    [
        'page' => 'report-users',
        'icon' => 'bi-people',
        'title' => 'รายงานสมาชิก',
        'desc' => 'รายงานข้อมูลสมาชิกทั้งหมดในระบบ',
    ],
    [
        'page' => 'report-product-types',
        'icon' => 'bi-tags',
        'title' => 'รายงานประเภทสินค้า',
        'desc' => 'รายงานข้อมูลประเภทสินค้าทั้งหมด',
    ],
    [
        'page' => 'report-products',
        'icon' => 'bi-box-seam',
        'title' => 'รายงานข้อมูลสินค้า',
        'desc' => 'รายงานข้อมูลรายละเอียดสินค้าทั้งหมด',
    ],
    [
        'page' => 'report-sales',
        'icon' => 'bi-cart-check',
        'title' => 'รายงานขายสินค้า',
        'desc' => 'รายงานข้อมูลการขายสินค้าแต่ละรายการ',
    ],
    [
        'page' => 'report-payments',
        'icon' => 'bi-credit-card',
        'title' => 'รายงานการแจ้งชำระเงิน',
        'desc' => 'รายงานข้อมูลการแจ้งชำระเงินทั้งหมด',
    ],
    [
        'page' => 'report-product-stocks',
        'icon' => 'bi-archive',
        'title' => 'รายงานยอดสินค้า',
        'desc' => 'รายงานข้อมูลสินค้าคงคลังและยอดสต๊อก',
    ],
    [
        'page' => 'report-best-sellers',
        'icon' => 'bi-trophy',
        'title' => 'รายงานสินค้าขายดี',
        'desc' => 'รายงานสินค้าที่มียอดขายสูงสุด',
    ],
    [
        'page' => 'report-revenue',
        'icon' => 'bi-graph-up-arrow',
        'title' => 'รายงานยอดขาย',
        'desc' => 'รายงานสรุปยอดขายและรายได้ทั้งหมด',
    ],
];
?>

<style>
    .report-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        overflow: hidden;
        position: relative;
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-accent);
        transition: height 0.3s ease;
    }

    .report-card:hover::before {
        height: 6px;
    }

    .report-card .card-body {
        padding: 1.5rem;
    }

    .report-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .report-card .card-title {
        font-weight: 700;
        font-size: 1.05rem;
        margin-bottom: 0.5rem;
    }

    .report-card .card-text {
        color: #6c757d;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .report-card .btn-report {
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .page-header-report {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 16px;
        color: white;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }

    .page-header-report h2 {
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .page-header-report p {
        opacity: 0.8;
        margin-bottom: 0;
    }
</style>

<div class="container-fluid mb-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ระบบรายงาน</h2>
    </div>

    <!-- Report Cards Grid -->
    <div class="row g-4">
        <?php foreach ($report_items as $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card report-card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="report-icon bg-dark">
                            <i class="bi <?php echo $item['icon']; ?>"></i>
                        </div>
                        <h5 class="card-title"><?php echo $item['title']; ?></h5>
                        <p class="card-text flex-grow-1"><?php echo $item['desc']; ?></p>
                        <div class="mt-3">
                            <a href="?page=<?php echo $item['page']; ?>" class="btn btn-report btn-outline-dark">
                                ดูรายงาน
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>