<?php
// Query products with type name
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type = isset($_GET['type_id']) ? $_GET['type_id'] : '';

$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE '%$search%' OR p.detail LIKE '%$search%')";
}
if ($filter_type !== '') {
    $where_conditions[] = "p.type_id = '$filter_type'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$result = query("SELECT p.*, pt.name as type_name 
    FROM product p 
    LEFT JOIN product_type pt ON p.type_id = pt.id 
    $where_clause 
    ORDER BY p.id DESC");
$products = fetch($result);
$total_filtered = count($products);

// Summary counts
$total_products = get_count('product');
$total_types = get_count('product_type');

// สินค้าคงคลังรวม
$result_stock = query("SELECT SUM(stock) as total_stock FROM product");
$row_stock = fetch($result_stock, 2);
$total_stock = (int) $row_stock['total_stock'];

// มูลค่าสินค้ารวม (ราคา x สต๊อก)
$result_value = query("SELECT SUM(price * stock) as total_value FROM product");
$row_value = fetch($result_value, 2);
$total_value = (float) $row_value['total_value'];

// Product types for filter dropdown
$all_types = fetch(get_all('product_type', 'name ASC'));

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_products = array_slice($products, $offset, $per_page);
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

    .product-img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e9ecef;
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
        background-color: #fd7e14;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #fff3e0;
        color: #fd7e14;
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
            <h2>รายงานข้อมูลสินค้า</h2>
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
                            <div class=" count text-dark">
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
                            <div class=" count text-dark">
                                <?php echo number_format($total_types); ?>
                            </div>
                            <div class="label">ประเภทสินค้า</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-tags-fill"></i>
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
                            <div class=" count text-dark">
                                <?php echo number_format($total_stock); ?>
                            </div>
                            <div class="label">สินค้าคงคลังรวม</div>
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
                            <div class=" count text-dark">
                                <?php echo number_format($total_value, 2); ?>
                            </div>
                            <div class="label">มูลค่าสินค้ารวม (บาท)</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-currency-exchange"></i>
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
                <input type="hidden" name="page" value="report-products">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            ค้นหา
                        </label>
                        <input type="text" class="form-control" name="search"
                            placeholder="ค้นหาชื่อสินค้า, รายละเอียด..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            กรองตามประเภท
                        </label>
                        <select class="form-select" name="type_id">
                            <option value="">ทุกประเภท</option>
                            <?php foreach ($all_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" <?php echo $filter_type === $type['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 10px;">
                            ค้นหา
                        </button>
                        <a href="?page=report-products" class="btn btn-outline-secondary" style="border-radius: 10px;">
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
                รายละเอียดข้อมูลสินค้า
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
                            <th class="text-center" style="width: 80px;">รูปภาพ</th>
                            <th>ชื่อสินค้า</th>
                            <th>ประเภท</th>
                            <th class="text-end">ราคา</th>
                            <th class="text-center">คงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged_products) > 0): ?>
                            <?php $i = $offset + 1; ?>
                            <?php foreach ($paged_products as $product): ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted">
                                        <?php echo $i++; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($product['img'])): ?>
                                            <img src="../upload/product/<?php echo $product['img']; ?>"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                                        <?php else: ?>
                                            <div class="product-img d-flex align-items-center justify-content-center bg-light">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?php echo htmlspecialchars($product['name']); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo htmlspecialchars($product['type_name'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-muted">
                                            ฿<?php echo number_format($product['price'], 2); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($product['stock'] <= 0): ?>
                                            <span class="badge bg-danger">หมด</span>
                                        <?php elseif ($product['stock'] <= 5): ?>
                                            <span
                                                class="badge bg-warning text-dark"><?php echo number_format($product['stock']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo number_format($product['stock']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
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
                                href="?page=report-products&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&type_id=<?php echo urlencode($filter_type); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-products&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type_id=<?php echo urlencode($filter_type); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-products&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&type_id=<?php echo urlencode($filter_type); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <h1 style="font-weight: bold;">รายงานข้อมูลสินค้า</h1>
                <h1 style="font-weight: bold;"><?= WEBSITE_NAME ?></h1>
            </div>
            <hr style="margin: 0;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between; color: rgba(33, 37, 41, 0.75);  ">
                <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
                <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
            </div>
        </div>
        <p style="font-weight: bold; margin: 0;">ข้อมูลสินค้า</p>
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
                <div>ประเภทสินค้า</div>
                <div>
                    <?= number_format($total_types) ?> ประเภท
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>สินค้าคงคลังรวม</div>
                <div>
                    <?= number_format($total_stock) ?> ชิ้น
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>มูลค่าสินค้ารวม</div>
                <div>
                    <?= number_format($total_value, 2) ?> บาท
                </div>
            </div>
        </div>
        <hr class="my-3">
        <p style="font-weight: bold; margin: 0;">รายละเอียดสินค้า</p>
        <table class="table table-bordered mt-2" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th style="text-wrap: nowrap;">ชื่อสินค้า</th>
                    <th style="text-wrap: nowrap;">ประเภท</th>
                    <th class="text-end" style="text-wrap: nowrap;">ราคา (บาท)</th>
                    <th class="text-center" style="text-wrap: nowrap;">คงเหลือ (ชิ้น)</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1; ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="text-center"><?php echo $j++; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['type_name'] ?? '-'); ?></td>
                        <td class="text-end"><?php echo number_format($product['price'], 2); ?></td>
                        <td class="text-center"><?php echo number_format($product['stock']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <hr class="mt-3 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-0"
            style="display: flex; justify-content: between; color: rgba(33, 37, 41, 0.75);  ">
            <p>พิมพ์โดย:
                <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?>
            </p>
            <p>พิมพ์เมื่อ:
                <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
            </p>
        </div>
    </div>
</div>