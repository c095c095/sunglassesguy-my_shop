<?php
// รายงานยอดขาย - Revenue Summary Report

// Filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_view = isset($_GET['view']) ? $_GET['view'] : 'monthly';

// Date conditions for filtered queries
$date_conds = ["o.status = '4'"];
if (!empty($start_date))
    $date_conds[] = "DATE(o.order_date) >= '$start_date'";
if (!empty($end_date))
    $date_conds[] = "DATE(o.order_date) <= '$end_date'";
$date_where = 'WHERE ' . implode(' AND ', $date_conds);

// === Summary Stats (always full dataset, status=4) ===
$r1 = query("SELECT COUNT(*) as cnt, SUM(total_price + delivery_fee) as rev FROM `order` WHERE status='4'");
$s1 = fetch($r1, 2);
$total_orders_done = (int) $s1['cnt'];
$total_revenue_all = (float) $s1['rev'];

$r2 = query("SELECT SUM(od.qty) as qty FROM order_detail od JOIN `order` o ON od.order_id=o.id WHERE o.status='4'");
$s2 = fetch($r2, 2);
$total_qty_sold = (int) $s2['qty'];

$r3 = query("SELECT COUNT(*) as cnt FROM `order` WHERE status='0'");
$s3 = fetch($r3, 2);
$total_cancel = (int) $s3['cnt'];

$r4 = query("SELECT AVG(total_price + delivery_fee) as avg_rev FROM `order` WHERE status='4'");
$s4 = fetch($r4, 2);
$avg_order_value = (float) $s4['avg_rev'];

// === Monthly/Daily breakdown ===
if ($filter_view === 'daily') {
    $group_col = "DATE(o.order_date)";
    $label_alias = "period_label";
    $order_dir = "DESC";
} else {
    $group_col = "DATE_FORMAT(o.order_date, '%Y-%m')";
    $label_alias = "period_label";
    $order_dir = "DESC";
}

$sql = "SELECT $group_col AS $label_alias,
               COUNT(o.id) AS order_count,
               SUM(o.total_price + o.delivery_fee) AS revenue,
               SUM(o.total_price) AS product_revenue,
               SUM(o.delivery_fee) AS delivery_revenue
        FROM `order` o
        $date_where
        GROUP BY $group_col
        ORDER BY $label_alias $order_dir";
$result = query($sql);
$all_rows = fetch($result);
$total_filtered = count($all_rows);

// Grand totals for filtered data
$filtered_revenue = 0;
$filtered_orders = 0;
foreach ($all_rows as $row) {
    $filtered_revenue += (float) $row['revenue'];
    $filtered_orders += (int) $row['order_count'];
}

// Top 5 best selling products (status=4, with date filter)
$top_sql = "SELECT p.name, p.img, SUM(od.qty) AS total_qty, SUM(od.qty * od.product_price) AS total_rev
            FROM product p
            JOIN order_detail od ON p.id = od.product_id
            JOIN `order` o ON od.order_id = o.id
            $date_where
            GROUP BY p.id ORDER BY total_qty DESC LIMIT 5";
$top_result = query($top_sql);
$top_products = fetch($top_result);

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_rows = array_slice($all_rows, $offset, $per_page);
?>

<style>
    .summary-card {
        border: none;
        position: relative;
        overflow: hidden
    }

    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color)
    }

    .summary-card .card-body {
        padding: 1.25rem
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem
    }

    .summary-card .count {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1
    }

    .summary-card .label {
        font-size: .82rem;
        color: #6c757d;
        font-weight: 500
    }

    .filter-card {
        border: none;
        border-radius: 14px
    }

    .report-table-card {
        border: none;
        border-radius: 14px;
        overflow: hidden
    }

    .report-table thead th {
        background-color: #f8f9fc;
        font-weight: 700;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding: .85rem .75rem;
        white-space: nowrap
    }

    .report-table tbody td {
        padding: .75rem;
        vertical-align: middle;
        font-size: .9rem
    }

    .report-table tbody tr {
        transition: background-color .15s ease
    }

    .report-table tbody tr:hover {
        background-color: #eef6ff
    }

    .top-product-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem .75rem;
        border-radius: 10px;
        transition: background .15s
    }

    .top-product-item:hover {
        background: #f8f9fc
    }

    .top-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .8rem;
        color: #fff
    }

    .rank-g {
        background: linear-gradient(135deg, #f59e0b, #d97706)
    }

    .rank-s {
        background: linear-gradient(135deg, #9ca3af, #6b7280)
    }

    .rank-b {
        background: linear-gradient(135deg, #cd7f32, #a0522d)
    }

    .rank-d {
        background: #e5e7eb;
        color: #6b7280
    }

    .report-pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #495057;
        font-weight: 500;
        font-size: .85rem
    }

    .report-pagination .page-item.active .page-link {
        background-color: #0d6efd;
        color: #fff
    }

    .report-pagination .page-link:hover {
        background-color: #e0edff;
        color: #0d6efd
    }

    @media print {
        body {
            background: #fff
        }

        .report-paper {
            min-width: 100vw;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-shadow: none
        }
    }
</style>

<div class="container-fluid mb-3 hide-print">
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h2>รายงานยอดขาย</h2>
            <button type="button" class="btn btn-dark" onclick="window.print();">
                <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
            </button>
        </div>
        <span class="subtitle mb-0">วันที่พิมพ์: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></span>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark">฿<?php echo number_format($total_revenue_all, 2); ?></div>
                            <div class="label">รายได้รวมทั้งหมด</div>
                        </div>
                        <div class="summary-icon"><i class="bi bi-cash-coin"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_orders_done); ?></div>
                            <div class="label">คำสั่งซื้อสำเร็จ</div>
                        </div>
                        <div class="summary-icon"><i class="bi bi-bag-check-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_qty_sold); ?></div>
                            <div class="label">สินค้าที่ขายได้ (ชิ้น)</div>
                        </div>
                        <div class="summary-icon"><i class="bi bi-box-seam-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark">฿<?php echo number_format($avg_order_value, 2); ?></div>
                            <div class="label">เฉลี่ยต่อออเดอร์</div>
                        </div>
                        <div class="summary-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 Best Sellers -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>สินค้าขายดี Top 5</h6>
        </div>
        <div class="card-body py-2">
            <?php if (count($top_products) > 0): ?>
                <?php $ri = 1;
                foreach ($top_products as $tp):
                    $rc = $ri == 1 ? 'rank-g' : ($ri == 2 ? 'rank-s' : ($ri == 3 ? 'rank-b' : 'rank-d'));
                    ?>
                    <div class="top-product-item">
                        <span class="top-rank <?php echo $rc; ?>"><?php echo $ri; ?></span>
                        <img src="../upload/product/<?php echo $tp['img']; ?>"
                            onerror="this.onerror=null;this.src='../assets/images/404.webp';" class="rounded object-fit-cover"
                            style="width:36px;height:36px;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?php echo htmlspecialchars($tp['name']); ?></div>
                            <small class="text-muted"><?php echo number_format($tp['total_qty']); ?> ชิ้น</small>
                        </div>
                        <div class="text-end fw-semibold">฿<?php echo number_format($tp['total_rev'], 2); ?></div>
                    </div>
                    <?php $ri++; endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3 mb-0">ไม่พบข้อมูล</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter -->
    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="">
                <input type="hidden" name="page" value="report-revenue">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">มุมมอง</label>
                        <select class="form-select" name="view">
                            <option value="monthly" <?php echo $filter_view === 'monthly' ? 'selected' : ''; ?>>รายเดือน
                            </option>
                            <option value="daily" <?php echo $filter_view === 'daily' ? 'selected' : ''; ?>>รายวัน
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" name="start_date"
                            value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" name="end_date"
                            value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius:10px;"><i
                                class="bi bi-search"></i> ค้นหา</button>
                        <a href="?page=report-revenue" class="btn btn-outline-secondary" style="border-radius:10px;"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card report-table-card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">สรุปยอดขาย<?php echo $filter_view === 'daily' ? 'รายวัน' : 'รายเดือน'; ?></h6>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size:.8rem;">ทั้งหมด
                <?php echo number_format($total_filtered); ?> รายการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table report-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:60px;">ลำดับ</th>
                            <th><?php echo $filter_view === 'daily' ? 'วันที่' : 'เดือน'; ?></th>
                            <th class="text-center">จำนวนออเดอร์</th>
                            <th class="text-end">ยอดสินค้า (บาท)</th>
                            <th class="text-end">ค่าจัดส่ง (บาท)</th>
                            <th class="text-end">รายได้รวม (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged_rows) > 0): ?>
                            <?php $i = $offset + 1;
                            foreach ($paged_rows as $row):
                                if ($filter_view === 'daily') {
                                    $display_label = date('d/m/Y', strtotime($row['period_label']));
                                } else {
                                    $parts = explode('-', $row['period_label']);
                                    $thai_months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                                    $display_label = $thai_months[(int) $parts[1]] . ' ' . ((int) $parts[0] + 543);
                                }
                                ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?php echo $i++; ?></td>
                                    <td class="fw-semibold"><?php echo $display_label; ?></td>
                                    <td class="text-center"><?php echo number_format($row['order_count']); ?></td>
                                    <td class="text-end"><?php echo number_format($row['product_revenue'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($row['delivery_revenue'], 2); ?></td>
                                    <td class="text-end fw-semibold"><?php echo number_format($row['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Grand Total Row -->
                            <tr class="table-light">
                                <td colspan="2" class="text-end fw-bold">รวมทั้งหมด</td>
                                <td class="text-center fw-bold"><?php echo number_format($filtered_orders); ?></td>
                                <td class="text-end fw-bold"><?php
                                $fp = 0;
                                foreach ($all_rows as $r)
                                    $fp += (float) $r['product_revenue'];
                                echo number_format($fp, 2);
                                ?></td>
                                <td class="text-end fw-bold"><?php
                                $fd = 0;
                                foreach ($all_rows as $r)
                                    $fd += (float) $r['delivery_revenue'];
                                echo number_format($fd, 2);
                                ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($filtered_revenue, 2); ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลยอดขาย</p>
                                        <small>ลองเปลี่ยนเงื่อนไขการค้นหา</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted" style="font-size:.85rem;">
                    แสดง <?php echo count($paged_rows); ?> จาก <?php echo $total_filtered; ?> รายการ
                    (หน้า <?php echo $current_page; ?>/<?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-revenue&p=<?php echo $current_page - 1; ?>&view=<?php echo urlencode($filter_view); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>"><i
                                    class="bi bi-chevron-left"></i></a>
                        </li>
                        <?php for ($pi = 1; $pi <= $total_pages; $pi++): ?>
                            <li class="page-item <?php echo ($current_page == $pi) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-revenue&p=<?php echo $pi; ?>&view=<?php echo urlencode($filter_view); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>"><?php echo $pi; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-revenue&p=<?php echo $current_page + 1; ?>&view=<?php echo urlencode($filter_view); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>"><i
                                    class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-3 text-muted d-none d-print-block" style="font-size:.8rem;">
        <hr>
        <p class="mb-1"><strong><?php echo WEBSITE_NAME; ?></strong> — ระบบรายงานยอดขาย</p>
        <p class="mb-0">พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
    </div>
</div>

<!-- ===== PRINT LAYOUT ===== -->
<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex;justify-content:between;">
                <h1 style="font-weight:bold;">รายงานยอดขาย</h1>
                <h1 style="font-weight:bold;"><?= WEBSITE_NAME ?></h1>
            </div>
            <hr style="margin:0;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex;justify-content:between;color:rgba(33,37,41,0.75);">
                <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
                <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
            </div>
        </div>

        <p style="font-weight:bold;margin:0;">สรุปภาพรวม</p>
        <div style="margin-left:20px;">
            <div class="d-flex justify-content-between" style="display:flex;justify-content:between;">
                <div>รายได้รวมทั้งหมด</div>
                <div><?= number_format($total_revenue_all, 2) ?> บาท</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex;justify-content:between;">
                <div>คำสั่งซื้อสำเร็จ</div>
                <div><?= number_format($total_orders_done) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex;justify-content:between;">
                <div>สินค้าที่ขายได้</div>
                <div><?= number_format($total_qty_sold) ?> ชิ้น</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex;justify-content:between;">
                <div>ยอดเฉลี่ยต่อออเดอร์</div>
                <div><?= number_format($avg_order_value, 2) ?> บาท</div>
            </div>
        </div>
        <hr class="my-3">

        <p style="font-weight:bold;margin:0;">สินค้าขายดี Top 5</p>
        <table class="table table-bordered mt-2" style="font-size:.85rem;">
            <thead>
                <tr>
                    <th class="text-center" style="text-wrap:nowrap;">อันดับ</th>
                    <th style="text-wrap:nowrap;">สินค้า</th>
                    <th class="text-center" style="text-wrap:nowrap;">จำนวนที่ขาย</th>
                    <th class="text-end" style="text-wrap:nowrap;">รายได้ (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php $tj = 1;
                foreach ($top_products as $tp): ?>
                    <tr>
                        <td class="text-center"><?php echo $tj++; ?></td>
                        <td><?php echo htmlspecialchars($tp['name']); ?></td>
                        <td class="text-center"><?php echo number_format($tp['total_qty']); ?></td>
                        <td class="text-end"><?php echo number_format($tp['total_rev'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <hr class="my-3">

        <p style="font-weight:bold;margin:0;">สรุปยอดขาย<?php echo $filter_view === 'daily' ? 'รายวัน' : 'รายเดือน'; ?>
        </p>
        <table class="table table-bordered mt-2" style="font-size:.85rem;">
            <thead>
                <tr>
                    <th class="text-center" style="text-wrap:nowrap;">ลำดับ</th>
                    <th style="text-wrap:nowrap;"><?php echo $filter_view === 'daily' ? 'วันที่' : 'เดือน'; ?></th>
                    <th class="text-center" style="text-wrap:nowrap;">ออเดอร์</th>
                    <th class="text-end" style="text-wrap:nowrap;">ยอดสินค้า</th>
                    <th class="text-end" style="text-wrap:nowrap;">ค่าจัดส่ง</th>
                    <th class="text-end" style="text-wrap:nowrap;">รายได้รวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1;
                foreach ($all_rows as $row):
                    if ($filter_view === 'daily') {
                        $dl = date('d/m/Y', strtotime($row['period_label']));
                    } else {
                        $pp = explode('-', $row['period_label']);
                        $tm = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                        $dl = $tm[(int) $pp[1]] . ' ' . ((int) $pp[0] + 543);
                    }
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $j++; ?></td>
                        <td><?php echo $dl; ?></td>
                        <td class="text-center"><?php echo number_format($row['order_count']); ?></td>
                        <td class="text-end"><?php echo number_format($row['product_revenue'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($row['delivery_revenue'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($row['revenue'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight:bold;">
                    <td colspan="2" class="text-end">รวมทั้งหมด</td>
                    <td class="text-center"><?php echo number_format($filtered_orders); ?></td>
                    <td class="text-end"><?php echo number_format($fp, 2); ?></td>
                    <td class="text-end"><?php echo number_format($fd, 2); ?></td>
                    <td class="text-end"><?php echo number_format($filtered_revenue, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <hr class="mt-3 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-0"
            style="display:flex;justify-content:between;color:rgba(33,37,41,0.75);">
            <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
            <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
        </div>
    </div>
</div>