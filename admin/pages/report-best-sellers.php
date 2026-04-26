<?php
// รายงานสินค้าขายดี - Best Selling Products Report

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Get all product types for filter dropdown
$all_types = fetch(get_all('product_type'));

// Build WHERE conditions (only orders with status = 4 = delivered successfully)
$where_conditions = ["o.status = '4'"];
if (!empty($search)) {
    $s = mysqli_real_escape_string($GLOBALS['conn'], $search);
    $where_conditions[] = "(p.name LIKE '%$s%')";
}
if ($filter_type !== '') {
    $where_conditions[] = "p.type_id = '$filter_type'";
}
if (!empty($start_date)) {
    $where_conditions[] = "DATE(o.order_date) >= '$start_date'";
}
if (!empty($end_date)) {
    $where_conditions[] = "DATE(o.order_date) <= '$end_date'";
}
$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Main query: products ranked by total quantity sold
$sql = "SELECT p.id, p.name, p.img, p.price, p.stock, p.type_id,
               SUM(od.qty) AS total_qty,
               SUM(od.qty * od.product_price) AS total_revenue,
               COUNT(DISTINCT o.id) AS order_count
        FROM product p
        JOIN order_detail od ON p.id = od.product_id
        JOIN `order` o ON od.order_id = o.id
        $where_clause
        GROUP BY p.id
        ORDER BY total_qty DESC";
$result = query($sql);
$all_products = fetch($result);
$total_filtered = count($all_products);

// Summary stats (always from full dataset – status=4 only)
$r_total_sold = query("SELECT SUM(od.qty) AS total_qty
                       FROM order_detail od
                       JOIN `order` o ON od.order_id = o.id
                       WHERE o.status = '4'");
$row_total_sold = fetch($r_total_sold, 2);
$total_qty_sold = (int) $row_total_sold['total_qty'];

$r_total_revenue = query("SELECT SUM(od.qty * od.product_price) AS revenue
                          FROM order_detail od
                          JOIN `order` o ON od.order_id = o.id
                          WHERE o.status = '4'");
$row_total_revenue = fetch($r_total_revenue, 2);
$total_revenue = (float) $row_total_revenue['revenue'];

$r_total_products = query("SELECT COUNT(DISTINCT od.product_id) AS cnt
                           FROM order_detail od
                           JOIN `order` o ON od.order_id = o.id
                           WHERE o.status = '4'");
$row_total_products = fetch($r_total_products, 2);
$total_products_sold = (int) $row_total_products['cnt'];

$r_total_orders = query("SELECT COUNT(DISTINCT o.id) AS cnt
                         FROM `order` o
                         WHERE o.status = '4'");
$row_total_orders = fetch($r_total_orders, 2);
$total_orders_done = (int) $row_total_orders['cnt'];

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_products = array_slice($all_products, $offset, $per_page);
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
        background-color: #fff8e6;
    }

    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: white;
    }

    .rank-1 {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .rank-2 {
        background: linear-gradient(135deg, #9ca3af, #6b7280);
    }

    .rank-3 {
        background: linear-gradient(135deg, #cd7f32, #a0522d);
    }

    .rank-default {
        background: #e5e7eb;
        color: #6b7280;
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
        background-color: #f59e0b;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #fef3c7;
        color: #d97706;
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
            <h2>รายงานสินค้าขายดี</h2>
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
                            <div class="count text-dark"><?php echo number_format($total_qty_sold); ?></div>
                            <div class="label">จำนวนสินค้าที่ขายได้</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-box-seam-fill"></i>
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
                            <div class="count text-dark"><?php echo number_format($total_products_sold); ?></div>
                            <div class="label">สินค้าที่มียอดขาย</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-trophy-fill"></i>
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
                            <div class="count text-dark"><?php echo number_format($total_orders_done); ?></div>
                            <div class="label">คำสั่งซื้อสำเร็จ</div>
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
                            <div class="count text-dark">฿<?php echo number_format($total_revenue, 2); ?></div>
                            <div class="label">รายได้รวม</div>
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
                <input type="hidden" name="page" value="report-best-sellers">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">ค้นหา</label>
                        <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อสินค้า..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">กรองตามประเภท</label>
                        <select class="form-select" name="type">
                            <option value="">ทุกประเภท</option>
                            <?php foreach ($all_types as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $filter_type === $t['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name']); ?>
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
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 10px;">
                            ค้นหา
                        </button>
                        <a href="?page=report-best-sellers" class="btn btn-outline-secondary"
                            style="border-radius: 10px;">
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
            <h6 class="mb-0 fw-bold">
                อันดับสินค้าขายดี
            </h6>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.8rem;">
                ทั้งหมด <?php echo number_format($total_filtered); ?> รายการ
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table report-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:60px;">อันดับ</th>
                            <th>สินค้า</th>
                            <th>ประเภท</th>
                            <th class="text-end">ราคา/ชิ้น</th>
                            <th class="text-center">จำนวนที่ขายได้</th>
                            <th class="text-center">จำนวนออเดอร์</th>
                            <th class="text-end">รายได้รวม (บาท)</th>
                            <th class="text-center">สต็อกคงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged_products) > 0): ?>
                            <?php $rank = $offset + 1; ?>
                            <?php foreach ($paged_products as $product): ?>
                                <?php
                                // Get product type name
                                $type_result = get_by_id('product_type', $product['type_id']);
                                $type = fetch($type_result, 2);
                                $type_name = $type ? $type['name'] : '-';

                                // Rank badge class
                                if ($rank == 1)
                                    $rank_class = 'rank-1';
                                elseif ($rank == 2)
                                    $rank_class = 'rank-2';
                                elseif ($rank == 3)
                                    $rank_class = 'rank-3';
                                else
                                    $rank_class = 'rank-default';
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="rank-badge <?php echo $rank_class; ?>">
                                            <?php echo $rank; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="../upload/product/<?php echo $product['img']; ?>"
                                                onerror="this.onerror=null; this.src='../assets/images/404.webp';"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="object-fit-cover rounded" style="width: 40px; height: 40px;">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo htmlspecialchars($type_name); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo number_format($product['price'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo number_format($product['total_qty']); ?>
                                        <small class="text-muted">ชิ้น</small>
                                    </td>
                                    <td class="text-center">
                                        <?php echo number_format($product['order_count']); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo number_format($product['total_revenue'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($product['stock'] == 0): ?>
                                            <span class="badge bg-danger">หมด</span>
                                        <?php elseif ($product['stock'] <= 5): ?>
                                            <span class="badge bg-warning text-dark"><?php echo $product['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo $product['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $rank++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลสินค้าขายดี</p>
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
            <!-- Pagination -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted" style="font-size: 0.85rem;">
                    แสดง <?php echo count($paged_products); ?> จาก
                    <?php echo $total_filtered; ?> รายการ
                    (หน้า <?php echo $current_page; ?>/<?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-best-sellers&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($pi = 1; $pi <= $total_pages; $pi++): ?>
                            <li class="page-item <?php echo ($current_page == $pi) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-best-sellers&p=<?php echo $pi; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                    <?php echo $pi; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-best-sellers&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($filter_type); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
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
        <p class="mb-1"><strong><?php echo WEBSITE_NAME; ?></strong> — ระบบรายงานสินค้าขายดี</p>
        <p class="mb-0">พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
    </div>
</div>

<!-- ===== PRINT LAYOUT ===== -->
<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex; justify-content:between;">
                <h1 style="font-weight:bold;">รายงานสินค้าขายดี</h1>
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
                <div>จำนวนสินค้าที่ขายได้</div>
                <div><?= number_format($total_qty_sold) ?> ชิ้น</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>สินค้าที่มียอดขาย</div>
                <div><?= number_format($total_products_sold) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>คำสั่งซื้อสำเร็จ</div>
                <div><?= number_format($total_orders_done) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>รายได้รวม</div>
                <div><?= number_format($total_revenue, 2) ?> บาท</div>
            </div>
        </div>
        <hr class="my-3">

        <p style="font-weight:bold; margin:0;">อันดับสินค้าขายดี</p>
        <table class="table table-bordered mt-2" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th class="text-center" style="text-wrap:nowrap;">อันดับ</th>
                    <th style="text-wrap:nowrap;">สินค้า</th>
                    <th style="text-wrap:nowrap;">ประเภท</th>
                    <th class="text-end" style="text-wrap:nowrap;">ราคา/ชิ้น</th>
                    <th class="text-center" style="text-wrap:nowrap;">ขายได้</th>
                    <th class="text-center" style="text-wrap:nowrap;">ออเดอร์</th>
                    <th class="text-end" style="text-wrap:nowrap;">รายได้รวม (บาท)</th>
                    <th class="text-center" style="text-wrap:nowrap;">สต็อก</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1; ?>
                <?php foreach ($all_products as $product): ?>
                    <?php
                    $type_result = get_by_id('product_type', $product['type_id']);
                    $type = fetch($type_result, 2);
                    $type_name = $type ? $type['name'] : '-';
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $j++; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($type_name); ?></td>
                        <td class="text-end"><?php echo number_format($product['price'], 2); ?></td>
                        <td class="text-center"><?php echo number_format($product['total_qty']); ?></td>
                        <td class="text-center"><?php echo number_format($product['order_count']); ?></td>
                        <td class="text-end"><?php echo number_format($product['total_revenue'], 2); ?></td>
                        <td class="text-center"><?php echo $product['stock']; ?></td>
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