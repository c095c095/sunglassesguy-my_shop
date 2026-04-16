<?php
// Orders management page for admin section

function get_color_by_status($status) {
    switch ($status) {
        case '0':
            return ['secondary', 'ยกเลิก'];
            break;
        case '1':
            return ['secondary', 'รอชำระเงิน'];
            break;
        case '2':
            return ['primary', 'รอตรวจสอบ'];
            break;
        case '3':
            return ['primary', 'รอจัดส่ง'];
            break;
        case '4':
            return ['success', 'จัดส่งสำเร็จ'];
            break;
        default:
            return ['danger', 'ไม่ทราบสถานะ'];
    }
}

function get_delivery_type_label($type) {
    switch ($type) {
        case '1':
            return 'ไปรษณีย์ไทย';
        case '2':
            return 'รับเองที่ร้าน';
        default:
            return 'ไม่ระบุ';
    }
}

$statuses = [
    ['value' => '0', 'color' => 'secondary', 'label' => 'ยกเลิก'],
    ['value' => '1', 'color' => 'secondary', 'label' => 'รอชำระเงิน'],
    ['value' => '2', 'color' => 'primary', 'label' => 'รอตรวจสอบ'],
    ['value' => '3', 'color' => 'primary', 'label' => 'รอจัดส่ง'],
    ['value' => '4', 'color' => 'success', 'label' => 'จัดส่งสำเร็จ']
];

// Pagination setup
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10;
$offset = ($current_page - 1) * $per_page;

// Search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_by = $_GET['search_by'] ?? '';
$status_filter = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build WHERE conditions for the query
$where_conditions = [];
if (!empty($search)) {
    if ($search_by === 'id') {
        // Remove any non-numeric characters from the search string
        $clean_id = preg_replace('/[^0-9]/', '', $search);
        $where_conditions[] = "o.id = '$clean_id'";
    } else {
        $search = mysqli_real_escape_string($GLOBALS['conn'], $search);
        // Replace multiple spaces with a single space for the search term
        $search = preg_replace('/\s+/', ' ', $search);
        $where_conditions[] = "(CONCAT(TRIM(u.firstname), ' ', TRIM(u.lastname)) LIKE '%$search%' OR u.firstname LIKE '%$search%' OR u.lastname LIKE '%$search%' OR u.phone LIKE '%$search%' OR u.email LIKE '%$search%')";
    }
}
if ($status_filter !== '') {
    $where_conditions[] = "o.status = '$status_filter'";
}
if (!empty($start_date)) {
    $where_conditions[] = "DATE(o.order_date) >= '$start_date'";
}
if (!empty($end_date)) {
    $where_conditions[] = "DATE(o.order_date) <= '$end_date'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get orders with user information
$sql = "SELECT o.*, u.firstname, u.lastname, u.email, u.phone 
        FROM `order` o 
        LEFT JOIN user u ON o.user_id = u.id 
        $where_clause 
        ORDER BY o.order_date DESC";
$result = query($sql);
$total_orders = get_num_rows($result);
$orders = array_slice(fetch($result), $offset, $per_page);

$total_pages = ceil($total_orders / $per_page);

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];
        $cancel_and_restock = isset($_POST['cancel_and_restock']) && $_POST['cancel_and_restock'] == 'true';
        
        // Check if canceling and restocking
        if ($new_status === '0' && $cancel_and_restock) {
            // Get all order items
            $items_sql = "SELECT product_id, qty FROM order_detail WHERE order_id = $order_id";
            $items_result = query($items_sql);
            $order_items = fetch($items_result);
            
            // Restore stock for each item
            if (is_array($order_items) && count($order_items) > 0) {
                foreach ($order_items as $item) {
                    $product_id = $item['product_id'];
                    $qty = $item['qty'];
                    
                    // Update product stock
                    $restore_sql = "UPDATE product SET stock = stock + $qty WHERE id = $product_id";
                    query($restore_sql);
                }
            }
        }
        
        $update_data = [
            'status' => $new_status,
            'tracking' => isset($_POST['tracking']) ? $_POST['tracking'] : ''
        ];
        
        if (update_by_id('order', $order_id, $update_data)) {
            show_alert('อัปเดตสถานะสำเร็จ');
            reload_page();
        } else {
            show_alert('อัปเดตสถานะไม่สำเร็จ');
        }
    }
}
?>

<div class="container-fluid hide-print">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>รายการคำสั่งซื้อ</h2>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="orders">
                <div class="row g-3">
                    <!-- Search Group -->
                    <div class="col-lg-6">
                        <label class="form-label">คำค้นหา</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="ค้นหา..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-lg-2">
                        <label class="form-label">ค้นหาตาม</label>
                        <select class="form-select" name="search_by">
                            <option value="id" <?php echo $search_by === 'customer' ? '' : 'selected'; ?>>รหัสคำสั่งซื้อ</option>
                            <option value="customer" <?php echo $search_by === 'customer' ? 'selected' : ''; ?>>ข้อมูลลูกค้า</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-lg-2">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" name="status">
                            <option value="">ทุกสถานะ</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['value']; ?>" 
                                        <?php echo $status_filter === $status['value'] ? 'selected' : ''; ?>>
                                    <?php echo $status['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="col-lg-2">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>ค้นหา
                        </button>
                    </div>

                    <!-- Date Range -->
                    <div class="col-12">
                        <hr class="text-muted">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" name="start_date" 
                               value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" name="end_date" 
                               value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearDateRange()">
                            <i class="bi bi-calendar-x me-2"></i>ล้างช่วงวันที่
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-0 overflow-y-auto">
            <div class="table-responsive rounded-3" style="min-height: 15rem;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>รหัสคำสั่งซื้อ</th>
                            <th>ชื่อลูกค้า</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th class="text-end">ราคารวม</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0) : ?>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td class="align-middle">
                                        <a href="./?page=order&id=<?php echo $order['id']; ?>" class="text-decoration-none" target="_self">
                                            <?php echo format_order_id($order['id']); ?>
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <a href="?page=orders&search_by=customer&search=<?php echo $order['firstname'] . ' ' . $order['lastname']; ?>" class="text-decoration-none" target="_self">
                                            <?php echo $order['firstname'] . ' ' . $order['lastname']; ?>
                                        </a><br>
                                        <a href="?page=orders&search_by=customer&search=<?php echo $order['phone']; ?>" class="text-decoration-none" target="_self">
                                            <small style="color: rgb(91, 154, 253);"><?php echo $order['phone']; ?></small>
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <?php echo format_datetime_thai($order['order_date']); ?>
                                    </td>
                                    <td class="align-middle text-end">
                                        ฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2); ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php 
                                            $status = get_color_by_status($order['status']);
                                        ?>
                                        <span class="badge bg-<?php echo $status[0]; ?>">
                                            <?php echo $status[1]; ?>
                                        </span>
                                        <?php if ($order['status'] >= 3 && !empty($order['tracking'])) : ?>
                                            <br>
                                            <small class="text-muted"><?php echo $order['tracking']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center dropdown">
                                        <button type="button" class="btn w-100 border-0" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="?page=order&id=<?php echo $order['id']; ?>" class="dropdown-item">
                                                    ดูรายละเอียด
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item view-order" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#orderDetailModal"
                                                    data-id="<?php echo $order['id']; ?>">
                                                    อัปเดตสถานะ
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="printOrder(<?php echo $order['id']; ?>)">
                                                    พิมพ์
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">ไม่พบข้อมูลคำสั่งซื้อ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-3">
                <div>
                    แสดง <?php echo count($orders); ?> รายการ จากทั้งหมด <?php echo $total_orders; ?> รายการ
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=orders&p=<?php echo $current_page-1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo urlencode($search_by); ?>&status=<?php echo urlencode($status_filter); ?>">ก่อนหน้า</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=orders&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo urlencode($search_by); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=orders&p=<?php echo $current_page+1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo urlencode($search_by); ?>&status=<?php echo urlencode($status_filter); ?>">ต่อไป</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailModalLabel">อัปเดตสถานะคำสั่งซื้อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailContent" tabindex="-1">
                    <!-- Order details will be loaded here dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="updateStatusBtn">อัปเดตสถานะ</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Order Modal -->
<div class="modal" id="printOrderModal" tabindex="-1" aria-labelledby="printOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printOrderModalLabel">พิมพ์ใบสั่งซื้อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="printOrderContent">
                <!-- Print content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>พิมพ์
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderDetailModal = document.getElementById('orderDetailModal');
    const bsModal = new bootstrap.Modal(orderDetailModal);

    // Handle view order button click
    document.querySelectorAll('.view-order').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.id;
            
            // Show loading state
            document.getElementById('orderDetailContent').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2">กำลังโหลดข้อมูล...</p>
                </div>
            `;
            
            fetch(`../core/helpers/get_data.php?type=order&id=${orderId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    const order = data.order;
                    const items = data.items;
                    const payment = data.payment;
                    
                    let statusOptions = '';
                    <?php foreach ($statuses as $status): ?>
                        statusOptions += `
                            <option value="<?php echo $status['value']; ?>" 
                                    class="text-<?php echo $status['color']; ?>"
                                    ${order.status === '<?php echo $status['value']; ?>' ? 'selected' : ''}>
                                <?php echo $status['label']; ?>
                            </option>
                        `;
                    <?php endforeach; ?>

                    let itemsHtml = '';
                    items.forEach(item => {
                        itemsHtml += `
                            <tr>
                                <td>
                                    <img src="../upload/product/${item.product_img}" 
                                         alt="${item.product_name}" 
                                         class="img-thumbnail" 
                                         style="width: 50px;">
                                </td>
                                <td>${item.product_name}</td>
                                <td class="text-end">฿${parseFloat(item.product_price).toFixed(2)}</td>
                                <td class="text-center">${item.qty}</td>
                                <td class="text-end">฿${(item.product_price * item.qty).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    let paymentHtml = '';
                    if (payment) {
                        paymentHtml = `
                            <div class="mb-3">
                                <h6>ข้อมูลการชำระเงิน</h6>
                                <p class="mb-1">วันที่ชำระ: ${payment.pay_date} ${payment.pay_time}</p>
                                <p class="mb-1">ธนาคาร: ${payment.bank_name}</p>
                                <img src="../upload/payment/${payment.img}" 
                                     alt="หลักฐานการชำระเงิน" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px;">
                            </div>
                        `;
                    }

                    document.getElementById('orderDetailContent').innerHTML = `
                        <form id="updateOrderForm" method="POST">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="${order.id}">
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>ข้อมูลการจัดส่ง</h6>
                                    <p class="mb-1">ชื่อผู้รับ: ${order.name}</p>
                                    <p class="mb-1">เบอร์โทร: ${order.phone}</p>
                                    <p class="mb-1">ที่อยู่: ${order.address}</p>
                                    <p class="mb-3">วิธีการจัดส่ง: ${order.delivery_type === '1' ? 'ไปรษณีย์ไทย' : 'รับเองที่ร้าน'}</p>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="orderStatus">สถานะ</label>
                                            <select class="form-select" id="orderStatus" name="status" required>
                                                ${statusOptions}
                                            </select>
                                        </div>
                                        <div class="col-md-6 ${order.delivery_type === '2' ? 'd-none' : ''}">
                                            <label class="form-label" for="trackingNumber">เลขพัสดุ</label>
                                            <input type="text" class="form-control" id="trackingNumber" name="tracking" 
                                                    value="${order.tracking || ''}" 
                                                    placeholder="กรุณากรอกเลขพัสดุ"
                                                    ${order.delivery_type === '2' ? 'disabled' : ''}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    ${paymentHtml}
                                </div>
                            </div>

                            <div class="mb-3 ${order.status === '0' ? '' : 'd-none'}" id="restockGroup">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="cancelAndRestock" name="cancel_and_restock" value="true">
                                    <label class="form-check-label" for="cancelAndRestock">
                                        ยกเลิกและคืนสต็อก (คืนสินค้าเข้าคลังสินค้า)
                                    </label>
                                </div>
                                <small class="form-text text-muted d-block mt-2">
                                    เลือกตัวเลือกนี้เมื่อต้องการคืนสต็อกสินค้าทั้งหมดของคำสั่งซื้อนี้เข้าคลังสินค้า
                                </small>
                            </div>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th width="60">รูป</th>
                                            <th>สินค้า</th>
                                            <th class="text-end" width="120">ราคา</th>
                                            <th class="text-center" width="80">จำนวน</th>
                                            <th class="text-end" width="120">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end">ราคารวมสินค้า</td>
                                            <td class="text-end">฿${parseFloat(order.total_price).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">ค่าจัดส่ง</td>
                                            <td class="text-end">฿${parseFloat(order.delivery_fee).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>ราคารวมทั้งหมด</strong></td>
                                            <td class="text-end"><strong>฿${(parseFloat(order.total_price) + parseFloat(order.delivery_fee)).toFixed(2)}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    `;

                    // Set focus to the first focusable element
                    const firstInput = document.getElementById('orderStatus');
                    if (firstInput) {
                        firstInput.focus();
                        
                        // Add event listener for status change to show/hide restock option
                        firstInput.addEventListener('change', function() {
                            const restockGroup = document.getElementById('restockGroup');
                            const cancelAndRestockCheckbox = document.getElementById('cancelAndRestock');
                            
                            if (this.value === '0') {
                                restockGroup.classList.remove('d-none');
                            } else {
                                restockGroup.classList.add('d-none');
                                cancelAndRestockCheckbox.checked = false;
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetailContent').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            เกิดข้อผิดพลาดในการโหลดข้อมูล: ${error.message}
                        </div>
                    `;
                });
        });
    });

    // Handle update status button click
    document.getElementById('updateStatusBtn').addEventListener('click', function() {
        document.getElementById('updateOrderForm').submit();
    });

    // Handle modal events
    orderDetailModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('orderDetailContent').innerHTML = '';
    });

    orderDetailModal.addEventListener('shown.bs.modal', function () {
        const firstInput = document.getElementById('orderStatus');
        if (firstInput) {
            firstInput.focus();
        }
    });
});

// Add date range clear function
function clearDateRange() {
    document.querySelector('input[name="start_date"]').value = '';
    document.querySelector('input[name="end_date"]').value = '';
    // Submit the form after clearing
    document.querySelector('form').submit();
}

function printOrder(orderId) {
    console.log('printOrder', orderId);
    const printModal = new bootstrap.Modal(document.getElementById('printOrderModal'));
    
    // Show loading state
    document.getElementById('printOrderContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            <p class="mt-2">กำลังโหลดข้อมูล...</p>
        </div>
    `;
    
    // Show modal
    printModal.show();
    
    // Fetch order data
    fetch(`../core/helpers/get_data.php?type=order&id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            const order = data.order;
            const items = data.items;
            const payment = data.payment;
            const buyer = data.buyer;
            const status = getStatusLabel(order.status);
            
            let itemsHtml = '';
            let subtotal = 0;
            
            items.forEach(item => {
                const total = item.product_price * item.qty;
                subtotal += total;
                itemsHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td class="text-end">฿${parseFloat(item.product_price).toFixed(2)}</td>
                        <td class="text-center">${item.qty}</td>
                        <td class="text-end">฿${total.toFixed(2)}</td>
                    </tr>
                `;
            });

            document.getElementById('printOrderContent').innerHTML = `
                <div class="print-content p-3">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">ใบเสร็จรับเงิน</h3>
                        <h5 class="mb-2">ร้านค้าออนไลน์</h5>
                        <p class="mb-1">เลขที่: ${formatOrderId(order.id)}</p>
                        <p class="mb-0">วันที่ออกใบเสร็จ: ${payment ? payment.pay_date : order.order_date}</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="mb-2">ข้อมูลลูกค้า</h6>
                            <p class="mb-1">ชื่อ-นามสกุล: ${buyer.firstname || ''} ${buyer.lastname || ''}</p>
                            <p class="mb-1">เบอร์โทรศัพท์: ${buyer.phone || ''}</p>
                            <p class="mb-1">อีเมล: ${buyer.email || ''}</p>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-2">ข้อมูลการจัดส่ง</h6>
                            <p class="mb-1">วิธีการจัดส่ง: ${order.delivery_type === '1' ? 'ไปรษณีย์ไทย' : 'รับเองที่ร้าน'}</p>
                            <p class="mb-1">ชื่อผู้รับ: ${order.name || ''}</p>
                            <p class="mb-1">เบอร์โทรศัพท์: ${order.phone || ''}</p>
                            <p class="mb-1">ที่อยู่จัดส่ง: ${order.address || ''}</p>
                            ${order.delivery_type === '1' && order.tracking ? `<p class="mb-1">เลขพัสดุ: ${order.tracking}</p>` : ''}
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">ลำดับ</th>
                                <th>รายการสินค้า</th>
                                <th class="text-end" width="100">ราคาต่อชิ้น</th>
                                <th class="text-center" width="80">จำนวน</th>
                                <th class="text-end" width="140">จำนวนเงิน(บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map((item, index) => {
                                const total = item.product_price * item.qty;
                                return `
                                    <tr>
                                        <td class="text-center">${index + 1}</td>
                                        <td>${item.product_name}</td>
                                        <td class="text-end">${parseFloat(item.product_price).toFixed(2)}</td>
                                        <td class="text-center">${item.qty}</td>
                                        <td class="text-end">${total.toFixed(2)}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end">รวมเป็นเงิน</td>
                                <td class="text-end">${subtotal.toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">ค่าจัดส่ง</td>
                                <td class="text-end">${parseFloat(order.delivery_fee).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>จำนวนเงินทั้งสิ้น</strong></td>
                                <td class="text-end"><strong>${(subtotal + parseFloat(order.delivery_fee)).toFixed(2)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="row mt-4">
                        <div class="col-7">
                            <div class="mb-3">
                                <h6 class="mb-2">ข้อมูลการชำระเงิน</h6>
                                ${payment ? `
                                    <p class="mb-1">วันที่ชำระเงิน: ${payment.pay_date} ${payment.pay_time}</p>
                                    <p class="mb-1">ธนาคาร: ${payment.bank_name}</p>
                                ` : '<p class="mb-1">ยังไม่ได้ชำระเงิน</p>'}
                            </div>
                            <div>
                                <h6 class="mb-2">หมายเหตุ</h6>
                                <p class="mb-1">สถานะ: ${status}</p>
                            </div>
                        </div>
                        <div class="col-5 text-center">
                            <div style="margin-top: 80px;">
                                <p class="mb-1">_______________________</p>
                                <p class="mb-0">ผู้รับเงิน</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('printOrderContent').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    เกิดข้อผิดพลาดในการโหลดข้อมูล: ${error.message}
                </div>
            `;
        });
}

function formatOrderId(id) {
    return '#ORDER-' + id.toString();
}

function getStatusLabel(status) {
    const statuses = {
        '0': 'ยกเลิก',
        '1': 'รอชำระเงิน',
        '2': 'รอตรวจสอบ',
        '3': 'รอจัดส่ง',
        '4': 'จัดส่งสำเร็จ'
    };
    return statuses[status] || 'ไม่ทราบสถานะ';
}

// Add print styles
const printStyles = `
    @media print {
        /* Reset body and html for printing */
        html, body {
            height: auto !important;
            overflow: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: none !important;
        }

        /* Modal print setup */
        #printOrderModal {
            display: block !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            height: auto !important;
            overflow: visible !important;
            background: none !important;
        }

        .modal-dialog {
            transform: none !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .modal-content {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Hide unnecessary elements */
        .modal-header,
        .modal-footer,
        .btn,
        .btn-close {
            display: none !important;
        }

        /* Show print content */
        .print-content {
            display: block !important;
            visibility: visible !important;
            position: relative !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
        }

        .print-content * {
            visibility: visible !important;
        }

        /* Table styles for printing */
        .table {
            width: 100% !important;
            margin-bottom: 1rem !important;
            page-break-inside: auto !important;
        }

        /* Ensure rows don't break across pages */
        tr {
            page-break-inside: avoid !important;
        }

        /* Page setup */
        @page {
            size: A4;
            margin: 1.5cm;
        }
    }
`;

const styleSheet = document.createElement("style");
styleSheet.innerText = printStyles;
document.head.appendChild(styleSheet);
</script> 