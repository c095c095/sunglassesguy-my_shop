<?php
// รายงานยอดสินค้า - Product Stock Report

// Stock level definitions
$stock_levels = [
    ['value' => 'out', 'color' => 'danger', 'label' => 'สินค้าหมด', 'icon' => 'bi-x-circle-fill'],
    ['value' => 'low', 'color' => 'warning', 'label' => 'สต็อกต่ำ (≤10)', 'icon' => 'bi-exclamation-triangle-fill'],
    ['value' => 'normal', 'color' => 'success', 'label' => 'สต็อกปกติ', 'icon' => 'bi-check-circle-fill'],
];

// Get filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['type_id']) ? $_GET['type_id'] : '';
$filter_stock = isset($_GET['stock_level']) ? $_GET['stock_level'] : '';

// Get product types for filter dropdown
$types_result = query("SELECT * FROM product_type ORDER BY name ASC");
$product_types = fetch($types_result);

// Build WHERE clause
$where_conditions = [];
if (!empty($search)) {
    $s = mysqli_real_escape_string($GLOBALS['conn'], $search);
    $where_conditions[] = "(p.name LIKE '%$s%' OR p.detail LIKE '%$s%')";
}
if (!empty($filter_type)) {
    $where_conditions[] = "p.type_id = '$filter_type'";
}
if ($filter_stock === 'out') {
    $where_conditions[] = "p.stock = 0";
} elseif ($filter_stock === 'low') {
    $where_conditions[] = "p.stock > 0 AND p.stock <= 10";
} elseif ($filter_stock === 'normal') {
    $where_conditions[] = "p.stock > 10";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Main query: products with type info
$sql = "SELECT p.*, pt.name as type_name
        FROM product p
        LEFT JOIN product_type pt ON p.type_id = pt.id
        $where_clause
        ORDER BY p.stock ASC, p.name ASC";
$result = query($sql);
$products = fetch($result);
$total_filtered = count($products);

// Summary stats (always from full dataset)
$r_all = query("SELECT COUNT(*) as cnt, SUM(stock) as total_stock FROM product");
$row_all = fetch($r_all, 2);
$total_products = (int) $row_all['cnt'];
$total_stock = (int) $row_all['total_stock'];

$r_value = query("SELECT SUM(price * stock) as total_value FROM product");
$row_value = fetch($r_value, 2);
$total_stock_value = (float) $row_value['total_value'];

$r_out = query("SELECT COUNT(*) as cnt FROM product WHERE stock = 0");
$row_out = fetch($r_out, 2);
$total_out_of_stock = (int) $row_out['cnt'];

$r_low = query("SELECT COUNT(*) as cnt FROM product WHERE stock > 0 AND stock <= 10");
$row_low = fetch($r_low, 2);
$total_low_stock = (int) $row_low['cnt'];

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_products = array_slice($products, $offset, $per_page);

// Helper: stock level badge
function stock_level_info($stock)
{
    if ($stock == 0) {
        return ['color' => 'danger', 'label' => 'สินค้าหมด', 'icon' => 'bi-x-circle-fill'];
    } elseif ($stock <= 10) {
        return ['color' => 'warning', 'label' => 'สต็อกต่ำ', 'icon' => 'bi-exclamation-triangle-fill'];
    } else {
        return ['color' => 'success', 'label' => 'ปกติ', 'icon' => 'bi-check-circle-fill'];
    }
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
        background-color: #fff8f0;
    }

    .stock-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.75em;
        border-radius: 8px;
        font-weight: 600;
    }

    .product-thumb {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e9ecef;
    }

    .stock-bar-container {
        width: 80px;
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
    }

    .stock-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
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
        background-color: #e67e22;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #fef0e0;
        color: #e67e22;
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
            <h2>รายงานยอดสินค้า</h2>
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
                            <div class="count text-dark">
                                <?php echo number_format($total_products); ?>
                            </div>
                            <div class="label">สินค้าทั้งหมด</div>
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
                            <div class="count text-dark">
                                <?php echo number_format($total_stock); ?>
                            </div>
                            <div class="label">จำนวนสต็อกรวม (ชิ้น)</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-archive-fill"></i>
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
                            <div class="count text-dark">
                                <?php echo number_format($total_low_stock); ?>
                            </div>
                            <div class="label">สต็อกต่ำ (≤10 ชิ้น)</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
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
                            <div class="count text-dark">
                                <?php echo number_format($total_out_of_stock); ?>
                            </div>
                            <div class="label">สินค้าหมด (0 ชิ้น)</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-x-circle-fill"></i>
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
                <input type="hidden" name="page" value="report-product-stocks">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            ค้นหา
                        </label>
                        <input type="text" class="form-control" name="search" placeholder="ค้นหาชื่อสินค้า..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            กรองตามประเภท
                        </label>
                        <select class="form-select" name="type_id">
                            <option value="">ทุกประเภท</option>
                            <?php foreach ($product_types as $pt): ?>
                                <option value="<?php echo $pt['id']; ?>" <?php echo $filter_type == $pt['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pt['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            กรองตามระดับสต็อก
                        </label>
                        <select class="form-select" name="stock_level">
                            <option value="">ทุกระดับ</option>
                            <?php foreach ($stock_levels as $sl): ?>
                                <option value="<?php echo $sl['value']; ?>" <?php echo $filter_stock === $sl['value'] ? 'selected' : ''; ?>>
                                    <?php echo $sl['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 10px;">
                            ค้นหา
                        </button>
                        <a href="?page=report-product-stocks" class="btn btn-outline-secondary"
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
                รายละเอียดยอดสินค้าคงเหลือ
            </h6>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.8rem;">
                ทั้งหมด
                <?php echo number_format($total_filtered); ?> รายการ
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table report-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">ลำดับ</th>
                            <th>สินค้า</th>
                            <th>ประเภท</th>
                            <th class="text-end">ราคา (บาท)</th>
                            <th class="text-center">สต็อก (ชิ้น)</th>
                            <th class="text-end">มูลค่าคงเหลือ (บาท)</th>
                            <th class="text-center">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged_products) > 0): ?>
                            <?php $i = $offset + 1; ?>
                            <?php foreach ($paged_products as $product): ?>
                                <?php $sl = stock_level_info($product['stock']); ?>
                                <?php
                                $stock_value = $product['price'] * $product['stock'];
                                // stock bar percentage (max 200)
                                $stock_pct = min(100, ($product['stock'] / 200) * 100);
                                $bar_color = $sl['color'] === 'danger' ? '#dc3545' : ($sl['color'] === 'warning' ? '#ffc107' : '#198754');
                                ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted">
                                        <?php echo $i++; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../upload/product/<?php echo htmlspecialchars($product['img']); ?>" alt=""
                                                class="product-thumb me-2">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo htmlspecialchars($product['type_name'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php echo number_format($product['price'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <div>
                                            <span class="fw-semibold"><?php echo number_format($product['stock']); ?></span>
                                        </div>
                                        <div class="stock-bar-container mt-1">
                                            <div class="stock-bar"
                                                style="width: <?php echo $stock_pct; ?>%; background: <?php echo $bar_color; ?>;">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <?php echo number_format($stock_value, 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-badge badge bg-<?php echo $sl['color']; ?>">
                                            <i class="bi <?php echo $sl['icon']; ?> me-1"></i>
                                            <?php echo $sl['label']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลสินค้า</p>
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
                    แสดง
                    <?php echo count($paged_products); ?> จาก
                    <?php echo $total_filtered; ?> รายการ
                    (หน้า
                    <?php echo $current_page; ?>/
                    <?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-product-stocks&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&type_id=<?php echo urlencode($filter_type); ?>&stock_level=<?php echo urlencode($filter_stock); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($pi = 1; $pi <= $total_pages; $pi++): ?>
                            <li class="page-item <?php echo ($current_page == $pi) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-product-stocks&p=<?php echo $pi; ?>&search=<?php echo urlencode($search); ?>&type_id=<?php echo urlencode($filter_type); ?>&stock_level=<?php echo urlencode($filter_stock); ?>">
                                    <?php echo $pi; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-product-stocks&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&type_id=<?php echo urlencode($filter_type); ?>&stock_level=<?php echo urlencode($filter_stock); ?>">
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
        <p class="mb-1">
            <strong>
                <?php echo WEBSITE_NAME; ?></strong> — ระบบรายงานยอดสินค้า
        </p>
        <p class="mb-0">พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
        </p>
    </div>
</div>

<!-- ===== PRINT LAYOUT ===== -->
<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <h1 style="font-weight: bold;">รายงานยอดสินค้า</h1>
                <h1 style="font-weight: bold;"><?= WEBSITE_NAME ?></h1>
            </div>
            <hr style="margin: 0;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between; color: rgba(33, 37, 41, 0.75);">
                <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
                <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
            </div>
        </div>

        <p style="font-weight: bold; margin: 0;">สรุปยอดสินค้าคงเหลือ</p>
        <div style="margin-left: 20px;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>สินค้าทั้งหมด</div>
                <div>
                    <?= number_format($total_products) ?> รายการ
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>จำนวนสต็อกรวม</div>
                <div>
                    <?= number_format($total_stock) ?> ชิ้น
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>มูลค่าสินค้าคงเหลือ</div>
                <div>
                    <?= number_format($total_stock_value, 2) ?> บาท
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>สต็อกต่ำ (≤10 ชิ้น)</div>
                <div>
                    <?= number_format($total_low_stock) ?> รายการ
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>สินค้าหมด (0 ชิ้น)</div>
                <div>
                    <?= number_format($total_out_of_stock) ?> รายการ
                </div>
            </div>
        </div>
        <hr class="my-3">

        <p style="font-weight: bold; margin: 0;">รายละเอียดยอดสินค้าคงเหลือ</p>
        <table class="table table-bordered mt-2" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th class="text-center" style="text-wrap: nowrap;">ID</th>
                    <th style="text-wrap: nowrap;">ชื่อสินค้า</th>
                    <th style="text-wrap: nowrap;">ประเภท</th>
                    <th class="text-end" style="text-wrap: nowrap;">ราคา (บาท)</th>
                    <th class="text-center" style="text-wrap: nowrap;">สต็อก (ชิ้น)</th>
                    <th class="text-end" style="text-wrap: nowrap;">มูลค่า (บาท)</th>
                    <th class="text-center" style="text-wrap: nowrap;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1; ?>
                <?php foreach ($products as $product): ?>
                    <?php $sl = stock_level_info($product['stock']); ?>
                    <tr>
                        <td class="text-center"><?php echo $j++; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['type_name'] ?? '-'); ?></td>
                        <td class="text-end"><?php echo number_format($product['price'], 2); ?></td>
                        <td class="text-center"><?php echo number_format($product['stock']); ?></td>
                        <td class="text-end"><?php echo number_format($product['price'] * $product['stock'], 2); ?></td>
                        <td class="text-center"><?php echo $sl['label']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr class="mt-3 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-0"
            style="display: flex; justify-content: between; color: rgba(33, 37, 41, 0.75);">
            <p>พิมพ์โดย:
                <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?>
            </p>
            <p>พิมพ์เมื่อ:
                <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
            </p>
        </div>
    </div>
</div>