<?php
// รายงานขายสินค้า - Product Sales Report

$statuses = [
    ['value' => '0', 'color' => 'secondary', 'label' => 'ยกเลิก'],
    ['value' => '1', 'color' => 'secondary', 'label' => 'รอชำระเงิน'],
    ['value' => '2', 'color' => 'primary', 'label' => 'รอตรวจสอบ'],
    ['value' => '3', 'color' => 'primary', 'label' => 'รอจัดส่ง'],
    ['value' => '4', 'color' => 'success', 'label' => 'จัดส่งสำเร็จ'],
];

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build WHERE
$where_conditions = [];
if (!empty($search)) {
    $s = mysqli_real_escape_string($GLOBALS['conn'], $search);
    $s = preg_replace('/\s+/', ' ', $s);
    $where_conditions[] = "(CONCAT(TRIM(u.firstname),' ',TRIM(u.lastname)) LIKE '%$s%'
                            OR u.phone LIKE '%$s%'
                            OR u.email LIKE '%$s%'
                            OR o.id LIKE '%$s%')";
}
if ($filter_status !== '') {
    $where_conditions[] = "o.status = '$filter_status'";
}
if (!empty($start_date)) {
    $where_conditions[] = "DATE(o.order_date) >= '$start_date'";
}
if (!empty($end_date)) {
    $where_conditions[] = "DATE(o.order_date) <= '$end_date'";
}
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Main query: orders with user info
$sql = "SELECT o.*, u.firstname, u.lastname, u.email, u.phone
        FROM `order` o
        LEFT JOIN user u ON o.user_id = u.id
        $where_clause
        ORDER BY o.order_date DESC";
$result = query($sql);
$all_orders = fetch($result);
$total_filtered = count($all_orders);

// Summary stats (always from full dataset)
$r_all = query("SELECT COUNT(*) as cnt, SUM(total_price + delivery_fee) as revenue FROM `order`");
$row_all = fetch($r_all, 2);
$total_orders_all = (int) $row_all['cnt'];
$total_revenue = (float) $row_all['revenue'];

$r_done = query("SELECT COUNT(*) as cnt, SUM(total_price + delivery_fee) as revenue FROM `order` WHERE status = '4'");
$row_done = fetch($r_done, 2);
$total_done = (int) $row_done['cnt'];
$revenue_done = (float) $row_done['revenue'];

$r_cancel = query("SELECT COUNT(*) as cnt FROM `order` WHERE status = '0'");
$row_cancel = fetch($r_cancel, 2);
$total_cancel = (int) $row_cancel['cnt'];

// Total items sold (status = 4)
$r_qty = query("SELECT SUM(od.qty) as total_qty
                FROM order_detail od
                INNER JOIN `order` o ON od.order_id = o.id
                WHERE o.status = '4'");
$row_qty = fetch($r_qty, 2);
$total_qty_sold = (int) $row_qty['total_qty'];

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_orders = array_slice($all_orders, $offset, $per_page);

// Helper
function sales_status_label($val, $statuses)
{
    foreach ($statuses as $s) {
        if ($s['value'] === $val)
            return $s;
    }
    return ['color' => 'secondary', 'label' => 'ไม่ทราบ'];
}
?>

<style>
    /* ===== Summary Cards ===== */
    .summary-card {
        border: none;
        position: relative;
        overflow: hidden;
    }

    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color);
    }

    .summary-card .card-body {
        padding: 1.25rem;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .summary-card .count {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .summary-card .label {
        font-size: 0.82rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* ===== Filter Card ===== */
    .filter-card {
        border: none;
        border-radius: 14px;
    }

    /* ===== Table ===== */
    .report-table-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
    }

    .report-table thead th {
        background-color: #f8f9fc;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding: 0.85rem 0.75rem;
        white-space: nowrap;
    }

    .report-table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .report-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .report-table tbody tr:hover {
        background-color: #f0fff8;
    }

    /* ===== Pagination ===== */
    .report-pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #495057;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .report-pagination .page-item.active .page-link {
        background-color: #198754;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #d1f0e0;
        color: #198754;
    }

    @media print {
        body {
            background: white;
        }

        .report-paper {
            min-width: 100vw;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }
    }
</style>

<div class="container-fluid mb-3 hide-print">
    <!-- Report Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h2>รายงานขายสินค้า</h2>
            <button type="button" class="btn btn-dark" onclick="window.print();">
                <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
            </button>
        </div>
        <span class="subtitle mb-0">วันที่พิมพ์:
            <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
        </span>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_orders_all); ?></div>
                            <div class="label">คำสั่งซื้อทั้งหมด</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-cart3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_done); ?></div>
                            <div class="label">จัดส่งสำเร็จ</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_cancel); ?></div>
                            <div class="label">ยกเลิก</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark">฿<?php echo number_format($revenue_done, 2); ?></div>
                            <div class="label">รายได้จากคำสั่งซื้อสำเร็จ</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="">
                <input type="hidden" name="page" value="report-sales">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted mb-1">ค้นหา</label>
                        <input type="text" class="form-control" name="search"
                            placeholder="ค้นหาชื่อลูกค้า, เบอร์โทร, อีเมล, รหัสคำสั่งซื้อ..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">กรองตามสถานะ</label>
                        <select class="form-select" name="status">
                            <option value="">ทุกสถานะ</option>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?php echo $st['value']; ?>" <?php echo $filter_status === $st['value'] ? 'selected' : ''; ?>>
                                    <?php echo $st['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted mb-1">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" name="start_date"
                            value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small text-muted mb-1">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" name="end_date"
                            value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-1 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 10px;">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="?page=report-sales" class="btn btn-outline-secondary" style="border-radius: 10px;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card report-table-card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">รายละเอียดคำสั่งซื้อ</h6>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.8rem;">
                ทั้งหมด <?php echo number_format($total_filtered); ?> รายการ
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table report-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:60px;">ลำดับ</th>
                            <th>รหัสคำสั่งซื้อ</th>
                            <th>ลูกค้า</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th class="text-end">ราคารวม (บาท)</th>
                            <th class="text-center">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged_orders) > 0): ?>
                            <?php $i = $offset + 1; ?>
                            <?php foreach ($paged_orders as $order): ?>
                                <?php $st = sales_status_label($order['status'], $statuses); ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?php echo $i++; ?></td>
                                    <td>
                                        <a href="?page=order&id=<?php echo $order['id']; ?>"
                                            class="text-decoration-none fw-semibold">
                                            #ORDER-<?php echo $order['id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo format_datetime_thai($order['order_date']); ?>
                                        </small>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <?php echo number_format($order['total_price'] + $order['delivery_fee'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $st['color']; ?>">
                                            <?php echo $st['label']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลคำสั่งซื้อ</p>
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
                <div class="text-muted" style="font-size: 0.85rem;">
                    แสดง <?php echo count($paged_orders); ?> จาก
                    <?php echo $total_filtered; ?> รายการ
                    (หน้า <?php echo $current_page; ?>/<?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-sales&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($pi = 1; $pi <= $total_pages; $pi++): ?>
                            <li class="page-item <?php echo ($current_page == $pi) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-sales&p=<?php echo $pi; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                    <?php echo $pi; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-sales&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Print Footer Info -->
    <div class="text-center mt-3 text-muted d-none d-print-block" style="font-size: 0.8rem;">
        <hr>
        <p class="mb-1"><strong><?php echo WEBSITE_NAME; ?></strong> — ระบบรายงานขายสินค้า</p>
        <p class="mb-0">พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
    </div>
</div>

<!-- ===== PRINT LAYOUT ===== -->
<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex; justify-content:between;">
                <h1 style="font-weight:bold;">รายงานขายสินค้า</h1>
                <h1 style="font-weight:bold;"><?= WEBSITE_NAME ?></h1>
            </div>
            <hr style="margin:0;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex; justify-content:between; color:rgba(33,37,41,0.75);">
                <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
                <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
            </div>
        </div>

        <p style="font-weight:bold; margin:0;">สรุปยอดขาย</p>
        <div style="margin-left:20px;">
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>คำสั่งซื้อทั้งหมด</div>
                <div><?= number_format($total_orders_all) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>จัดส่งสำเร็จ</div>
                <div><?= number_format($total_done) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>ยกเลิก</div>
                <div><?= number_format($total_cancel) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>รายได้รวม (จัดส่งสำเร็จ)</div>
                <div><?= number_format($revenue_done, 2) ?> บาท</div>
            </div>
        </div>
        <hr class="my-3">

        <p style="font-weight:bold; margin:0;">รายละเอียดคำสั่งซื้อ</p>
        <table class="table table-bordered mt-2" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th class="text-center" style="text-wrap:nowrap;">ลำดับ</th>
                    <th style="text-wrap:nowrap;">รหัสคำสั่งซื้อ</th>
                    <th style="text-wrap:nowrap;">ลูกค้า</th>
                    <th style="text-wrap:nowrap;">วันที่สั่งซื้อ</th>
                    <th class="text-end" style="text-wrap:nowrap;">ราคารวม (บาท)</th>
                    <th class="text-center" style="text-wrap:nowrap;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1; ?>
                <?php foreach ($all_orders as $order): ?>
                    <?php $st = sales_status_label($order['status'], $statuses); ?>
                    <tr>
                        <td class="text-center"><?php echo $j++; ?></td>
                        <td>#ORDER-<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                        <td class="text-end"><?php echo number_format($order['total_price'] + $order['delivery_fee'], 2); ?>
                        </td>
                        <td class="text-center"><?php echo $st['label']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="mt-3 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-0"
            style="display:flex; justify-content:between; color:rgba(33,37,41,0.75);">
            <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
            <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
        </div>
    </div>
</div>