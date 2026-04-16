<?php
// Add this at the top with other PHP code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_stock') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $stock_add = isset($_POST['stock_add']) ? (int)$_POST['stock_add'] : 0;

    // Validate input
    if ($stock_add <= 0 || $stock_add > 999999999) {
        show_alert('จำนวนสินค้าที่เพิ่มต้องอยู่ระหว่าง 1 ถึง 999,999,999');
        reload_page();
    }

    $result = get_by_id('product', $product_id);
    if (get_num_rows($result) === 0) {
        show_alert('ไม่พบข้อมูลสินค้า');
        reload_page();
    }

    $product = fetch($result, 2);
    $new_stock = $product['stock'] + $stock_add;

    // Check if new stock exceeds database limit
    if ($new_stock > 999999999) {
        show_alert('จำนวนสินค้ารวมต้องไม่เกิน 999,999,999');
        reload_page();
    }

    if (update_by_id('product', $product_id, ['stock' => $new_stock])) {
        show_alert('เพิ่มสต็อกสินค้าสำเร็จ');
        reload_page();
    } else {
        show_alert('ไม่สามารถอัพเดทสต็อกได้');
        reload_page();
    }
}

// Get all product types for the dropdown
$product_types = get_all('product_type');
$types = fetch($product_types);

$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10; // Number of products per page
$offset = ($current_page - 1) * $per_page;

// Handle search and filtering
$where_conditions = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : 'all';

if (!empty($search)) {
    $where_conditions[] = "p.name LIKE '%$search%'";
}

if (!empty($type)) {
    $where_conditions[] = "p.type_id = '$type'";
}

$stock_level = 10;

if ($stock_status === 'low') {
    $where_conditions[] = "p.stock <= $stock_level AND p.stock > 0";
} else if ($stock_status === 'out') {
    $where_conditions[] = "p.stock = 0";
} else if ($stock_status === 'normal') {
    $where_conditions[] = "p.stock > $stock_level";
}

// Build the SQL query
$sql = "SELECT p.*, pt.name as type_name 
        FROM product p 
        LEFT JOIN product_type pt ON p.type_id = pt.id";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY 
    CASE 
        WHEN p.stock = 0 THEN 1
        WHEN p.stock <= $stock_level THEN 2
        ELSE 3
    END ASC,
    p.stock ASC";

// Modify the query section to include pagination
$result = query($sql);
$total_products = get_num_rows($result);
$products = array_slice(fetch($result), $offset, $per_page);

$total_pages = ceil($total_products / $per_page);
?>

<div class="container-fluid mb-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ตรวจสอบสินค้าคงคลัง</h2>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="product_stocks">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="ค้นหาตามชื่อสินค้า..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">ทุกประเภท</option>
                        <?php foreach ($types as $type_item): ?>
                            <option value="<?php echo $type_item['id']; ?>" 
                                    <?php echo $type == $type_item['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type_item['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="stock_status">
                        <option value="all" <?php echo $stock_status == 'all' ? 'selected' : ''; ?>>ทุกสถานะ</option>
                        <option value="normal" <?php echo $stock_status == 'normal' ? 'selected' : ''; ?>>สินค้าปกติ</option>
                        <option value="low" <?php echo $stock_status == 'low' ? 'selected' : ''; ?>>สินค้าใกล้หมด</option>
                        <option value="out" <?php echo $stock_status == 'out' ? 'selected' : ''; ?>>สินค้าหมด</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อสินค้า</th>
                            <th>ประเภท</th>
                            <th class="text-end">จำนวนคงเหลือ</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">ไม่พบข้อมูลสินค้าคงคลัง</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $index => $product): ?>
                                <tr>
                                    <td class="align-middle"><?php echo $index + 1; ?></td>
                                    <td class="align-middle">
                                        <div class="text-truncate" style="max-width: 15rem;" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="<?php echo htmlspecialchars($product['name']); ?>">
                                            <a href="./../?page=product&id=<?php echo $product['id']; ?>" class="text-decoration-none" target="_blank">
                                                <?php echo $product['name'] ?>
                                            </a>
                                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.6rem;"></i>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?php echo htmlspecialchars($product['type_name']); ?></td>
                                    <td class="align-middle text-end"><?php echo number_format($product['stock']); ?></td>
                                    <td class="align-middle text-center">
                                        <?php if ($product['stock'] == 0): ?>
                                            <span class="badge bg-danger">สินค้าหมด</span>
                                        <?php elseif ($product['stock'] <= $stock_level): ?>
                                            <span class="badge bg-warning">ใกล้หมด</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">ปกติ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#addStockModal" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                            เพิ่มสต็อก
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <div>
                    แสดง <?php echo count($products); ?> รายการ จากทั้งหมด <?php echo $total_products; ?> รายการ
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=product_stocks&p=<?php echo $current_page-1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&stock_status=<?php echo urlencode($stock_status); ?>">ก่อนหน้า</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=product_stocks&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&stock_status=<?php echo urlencode($stock_status); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=product_stocks&p=<?php echo $current_page+1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&stock_status=<?php echo urlencode($stock_status); ?>">ต่อไป</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">เพิ่มสต็อกสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStockForm" method="POST">
                    <input type="hidden" name="action" value="add_stock">
                    <input type="hidden" name="product_id" id="stock_product_id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้า</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="stock_product_name" readonly>
                            <a id="view_product_link" href="" class="btn btn-dark" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> ดูสินค้า
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="stock_current" class="form-label">จำนวนคงเหลือปัจจุบัน</label>
                        <input type="number" class="form-control" id="stock_current" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="stock_add" class="form-label">จำนวนที่ต้องการเพิ่ม</label>
                        <input type="number" class="form-control" id="stock_add" name="stock_add" 
                               min="1" max="999999999" required
                               placeholder="กรุณากรอกจำนวนที่ต้องการเพิ่ม">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="addStockForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
        new bootstrap.Tooltip(tooltipTriggerEl)
    );

    // Handle add stock modal
    const addStockModal = document.getElementById('addStockModal');
    addStockModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        
        // Get current product data from the table row
        const row = button.closest('tr');
        const currentStock = parseInt(row.querySelector('td:nth-child(4)').textContent.replace(/,/g, ''));
        
        // Set modal values
        document.getElementById('stock_product_id').value = productId;
        document.getElementById('stock_product_name').value = productName;
        document.getElementById('stock_current').value = currentStock;
        document.getElementById('stock_add').value = '';
        document.getElementById('stock_add').focus();

        // Update view product link
        document.getElementById('view_product_link').href = `./../?page=product&id=${productId}`;
    });

    // form validation
    document.getElementById('addStockForm').addEventListener('submit', function(e) {
        const stockAdd = parseInt(document.getElementById('stock_add').value);
        const currentStock = parseInt(document.getElementById('stock_current').value);
        const maxStock = 999999999;

        if (isNaN(stockAdd) || stockAdd <= 0 || stockAdd > maxStock) {
            e.preventDefault();
            alert('กรุณากรอกจำนวนที่ต้องการเพิ่มให้ถูกต้อง (1-999,999,999)');
            return false;
        }

        // Check if total stock would exceed the maximum
        if (currentStock + stockAdd > maxStock) {
            e.preventDefault();
            alert('จำนวนสินค้ารวมต้องไม่เกิน 999,999,999');
            return false;
        }
    });
});
</script> 