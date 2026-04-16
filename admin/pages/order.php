<?php
// Order detail page for admin section

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

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    show_alert('ไม่พบข้อมูลคำสั่งซื้อ');
    redirect_to('orders');
    exit();
}

// Get order details with user information
$sql = "SELECT o.*, u.firstname, u.lastname, u.email, u.phone as user_phone 
        FROM `order` o 
        LEFT JOIN user u ON o.user_id = u.id 
        WHERE o.id = $order_id";
$result = query($sql);

if (get_num_rows($result) == 0) {
    show_alert('ไม่พบข้อมูลคำสั่งซื้อ');
    redirect_to('orders');
    exit();
}

$order = fetch($result, 2);

// Get order items
$items_sql = "SELECT * FROM order_detail WHERE order_id = $order_id";
$items_result = query($items_sql);
$items = fetch($items_result);

// Get payment information if exists
$payment_sql = "SELECT p.*, b.name as bank_name 
                FROM payment p 
                LEFT JOIN bank b ON p.bank_id = b.id 
                WHERE p.order_id = $order_id";
$payment_result = query($payment_sql);
$payment = get_num_rows($payment_result) > 0 ? fetch($payment_result, 2) : null;

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $new_status = $_POST['status'];
        $tracking = isset($_POST['tracking']) ? $_POST['tracking'] : '';
        $note = isset($_POST['note']) ? $_POST['note'] : '';
        
        $update_data = [
            'status' => $new_status,
            'tracking' => $tracking,
            'note' => $note
        ];
        
        if (update_by_id('order', $order_id, $update_data)) {
            show_alert('อัปเดตสถานะสำเร็จ');
            reload_page();
        } else {
            show_alert('อัปเดตสถานะไม่สำเร็จ');
        }
    }
}

$status = get_color_by_status($order['status']);
?>

<div class="container-fluid hide-print">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>รายละเอียดคำสั่งซื้อ <?php echo format_order_id($order['id']); ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="?page=orders" class="text-decoration-none">รายการคำสั่งซื้อ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียดคำสั่งซื้อ</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-primary me-2" onclick="printOrder(<?php echo $order['id']; ?>)">
                <i class="bi bi-printer me-2"></i>พิมพ์
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                <i class="bi bi-pencil-square me-2"></i>อัปเดตสถานะ
            </button>
        </div>
    </div>
</div>

<div class="row hide-print">
    <div class="col-lg-8">
        <!-- Order Status Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="progresses" style="margin-bottom: 5rem;">
                    <div class="steps <?php if ($order['status'] == 2 || $order['status'] == 3 || $order['status'] == 4){ echo 'active'; } ?>">
                        <div class="icon-container">
                            <div class="icon-align">
                                <div class="icon-warpper">
                                    <i class="bi bi-box-seam-fill icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-container">
                            <p class="text">รอดำเนินการ</p>
                        </div>
                    </div>
                    <span class="line <?php if ($order['status'] == 3 || $order['status'] == 4){ echo 'active'; } ?>"></span>
                    <div class="steps <?php if ($order['status'] == 3 || $order['status'] == 4){ echo 'active'; } ?>">
                        <div class="icon-container">
                            <div class="icon-align">
                                <div class="icon-warpper">
                                    <i class="bi bi-truck-front-fill icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-container">
                            <p class="text">เตรียมการจัดส่ง</p>
                        </div>
                    </div>
                    <span class="line <?php if ($order['status'] == 4){ echo 'active'; } ?>"></span>
                    <div class="steps <?php if ($order['status'] == 4){ echo 'active'; } ?>">
                        <div class="icon-container">
                            <div class="icon-align">
                                <div class="icon-warpper">
                                    <i class="bi bi-check-circle-fill icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="text-container">
                            <p class="text">จัดส่งแล้ว</p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted">สถานะการสั่งซื้อ</label>
                            <div>
                                <span class="badge bg-<?php echo $status[0]; ?> px-3 py-2">
                                    <?php echo $status[1]; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 text-end">
                            <label class="text-muted">วิธีการจัดส่ง</label>
                            <div>
                                <span class="badge bg-primary px-3 py-2">
                                    <?php echo get_delivery_type_label($order['delivery_type']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($order['tracking']) : ?>
                <div class="alert alert-info mt-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-truck fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">เลขพัสดุ</h6>
                            <p class="mb-0" style="user-select: all;"><?php echo $order['tracking']; ?></p>
                        </div>
                        <?php if ($order['delivery_type'] == '1') : ?>
                        <div class="ms-auto">
                            <a href="https://track.thailandpost.co.th/?trackNumber=<?php echo $order['tracking']; ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-primary">
                                ติดตามพัสดุ
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($order['note']) : ?>
                <div class="alert alert-warning mt-3">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-info-circle fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">หมายเหตุ</h6>
                            <p class="mb-0"><?php echo $order['note']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($order['status'] == '2' && $payment) : ?>
        <!-- Payment Verification Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>ตรวจสอบการชำระเงิน
                </h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-between align-items-center mb-4">
                    <div class="col-12 col-md-2 col-xxl-1">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-circle text-warning fs-3"></i>
                        </div>
                    </div>
                    <div class="col-12 col-md-10 col-xxl-11">
                        <h6 class="mb-2">รอการตรวจสอบการชำระเงิน</h6>
                        <p class="mb-3">กรุณาตรวจสอบรายละเอียดการชำระเงินต่อไปนี้:</p>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">หลักฐานการชำระเงิน</small>
                        <div class="position-relative payment-image-container" onclick="openPaymentImage('../upload/payment/<?php echo $payment['img']; ?>')">
                            <img src="../upload/payment/<?php echo $payment['img']; ?>" 
                                 alt="หลักฐานการชำระเงิน" 
                                 class="img-fluid rounded shadow-sm"
                                 style="max-height: 450px;"
                                 data-bs-toggle="tooltip"
                                 data-bs-placement="top"
                                 title="คลิกเพื่อดูรูปขนาดใหญ่">
                            <div class="payment-image-overlay">
                                <i class="bi bi-zoom-in"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">จำนวนเงินที่ต้องชำระ</small>
                                        <strong class="fs-5">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2); ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">วันที่และเวลา</small>
                                        <strong><?php echo format_datetime_thai($payment['pay_date'] . ' ' . $payment['pay_time']); ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">ธนาคาร</small>
                                        <strong><?php echo $payment['bank_name']; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="rejectPayment(<?php echo $order['id']; ?>)">
                                <i class="bi bi-x-circle me-2"></i>ปฏิเสธการชำระเงิน
                            </button>
                            <button type="button" 
                                    class="btn btn-success" 
                                    onclick="approvePayment(<?php echo $order['id']; ?>)">
                                <i class="bi bi-check-circle me-2"></i>ยืนยันการชำระเงิน
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($order['status'] == 3 && $order['delivery_type'] == '1') : ?>
        <!-- Tracking Number Management Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-truck me-2"></i>ส่งเลขพัสดุ
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="mb-0">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="status" value="4">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        เมื่อบันทึกเลขพัสดุ สถานะคำสั่งซื้อจะเปลี่ยนเป็น "จัดส่งสำเร็จ" โดยอัตโนมัติ
                    </div>

                    <div>
                        <label for="trackingInput" class="form-label">เลขพัสดุ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-box-seam"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="trackingInput" 
                                   name="tracking"
                                   value="<?php echo $order['tracking']; ?>"
                                   placeholder="กรุณากรอกเลขพัสดุ"
                                   required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="trackingNote" class="form-label">หมายเหตุ (ถ้ามี)</label>
                        <textarea class="form-control" 
                                  id="trackingNote" 
                                  name="note" 
                                  rows="2"
                                  placeholder="กรุณากรอกหมายเหตุเกี่ยวกับการจัดส่ง (ถ้ามี)"><?php echo $order['note']; ?></textarea>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-2"></i>บันทึกเลขพัสดุและอัปเดตสถานะ
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Order Items Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-cart me-2"></i>รายการสินค้า
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive rounded-3">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="60">รูป</th>
                                <th>สินค้า</th>
                                <th class="text-end" width="120">ราคา</th>
                                <th class="text-center" width="80">จำนวน</th>
                                <th class="text-end" width="120">รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item) : ?>
                            <tr>
                                <td>
                                    <img src="../upload/product/<?php echo $item['product_img']; ?>" 
                                         alt="<?php echo $item['product_name']; ?>" 
                                         class="img-thumbnail"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <a href="?page=products&search=<?php echo urlencode($item['product_name']); ?>" 
                                       class="text-decoration-none">
                                        <?php echo $item['product_name']; ?>
                                    </a>
                                </td>
                                <td class="text-end">฿<?php echo number_format($item['product_price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['qty']; ?></td>
                                <td class="text-end">฿<?php echo number_format($item['product_price'] * $item['qty'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end">ราคารวมสินค้า</td>
                                <td class="text-end">฿<?php echo number_format($order['total_price'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">ค่าจัดส่ง</td>
                                <td class="text-end">฿<?php echo number_format($order['delivery_fee'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>ราคารวมทั้งหมด</strong></td>
                                <td class="text-end"><strong>฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Customer Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-person me-2"></i>ข้อมูลลูกค้า
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted mb-1">ชื่อ-นามสกุล</label>
                    <p class="mb-0 text-black">
                        <a href="?page=orders&search_by=customer&search=<?php echo urlencode($order['firstname'] . ' ' . $order['lastname']); ?>" 
                           class="text-decoration-none">
                            <?php echo $order['firstname'] . ' ' . $order['lastname']; ?>
                        </a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="text-muted mb-1">อีเมล</label>
                    <p class="mb-0 text-black">
                        <a href="?page=orders&search_by=customer&search=<?php echo urlencode($order['email']); ?>" 
                           class="text-decoration-none">
                            <?php echo $order['email']; ?>
                        </a>
                    </p>
                </div>
                <div class="mb-0">
                    <label class="text-muted mb-1">เบอร์โทรศัพท์</label>
                    <p class="mb-0 text-black">
                        <a href="?page=orders&search_by=customer&search=<?php echo urlencode($order['user_phone']); ?>" 
                           class="text-decoration-none">
                            <?php echo $order['user_phone']; ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-truck me-2"></i>ข้อมูลการจัดส่ง
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted mb-1">ชื่อผู้รับ</label>
                    <p class="mb-0 text-black"><?php echo $order['name']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted mb-1">เบอร์โทรศัพท์</label>
                    <p class="mb-0 text-black"><?php echo $order['phone']; ?></p>
                </div>
                <div class="mb-0">
                    <label class="text-muted mb-1">ที่อยู่จัดส่ง</label>
                    <p class="mb-0 text-black"><?php echo $order['address']; ?></p>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <?php if ($payment) : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-credit-card me-2"></i>ข้อมูลการชำระเงิน
                </h5>
                <?php if ($order['status'] == '2') : ?>
                <div class="badge bg-warning px-3 py-2">รอตรวจสอบ</div>
                <?php elseif ($order['status'] >= '3') : ?>
                <div class="badge bg-success px-3 py-2">ตรวจสอบแล้ว</div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted mb-1">วันที่ชำระเงิน</label>
                            <p class="mb-0 text-black"><?php echo format_datetime_thai($payment['pay_date'] . ' ' . $payment['pay_time']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted mb-1">ธนาคาร</label>
                            <p class="mb-0 text-black"><?php echo $payment['bank_name']; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted mb-1">จำนวนเงินที่ต้องชำระ</label>
                            <p class="mb-0 fw-bold fs-5 text-black">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted mb-2">หลักฐานการชำระเงิน</label>
                        <div class="position-relative payment-image-container" onclick="openPaymentImage('../upload/payment/<?php echo $payment['img']; ?>')">
                            <img src="../upload/payment/<?php echo $payment['img']; ?>" 
                                 alt="หลักฐานการชำระเงิน" 
                                 class="img-fluid rounded"
                                 style="max-width: 100%; cursor: pointer;"
                                 data-bs-toggle="tooltip"
                                 data-bs-placement="top"
                                 title="คลิกเพื่อดูรูปขนาดใหญ่">
                            <div class="payment-image-overlay">
                                <i class="bi bi-zoom-in"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else : ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">ข้อมูลการชำระเงิน</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="bi bi-hourglass text-muted fs-1"></i>
                    <p class="text-muted mt-2 mb-0">ยังไม่มีการชำระเงิน</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">อัปเดตสถานะคำสั่งซื้อ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="orderStatus" class="form-label">สถานะ</label>
                        <select class="form-select" id="orderStatus" name="status" required>
                            <option value="0" <?php echo $order['status'] == '0' ? 'selected' : ''; ?>>ยกเลิก</option>
                            <option value="1" <?php echo $order['status'] == '1' ? 'selected' : ''; ?>>รอชำระเงิน</option>
                            <option value="2" <?php echo $order['status'] == '2' ? 'selected' : ''; ?>>รอตรวจสอบ</option>
                            <option value="3" <?php echo $order['status'] == '3' ? 'selected' : ''; ?>>รอจัดส่ง</option>
                            <option value="4" <?php echo $order['status'] == '4' ? 'selected' : ''; ?>>จัดส่งสำเร็จ</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 <?php echo $order['delivery_type'] == '2' ? 'd-none' : ''; ?>" id="trackingGroup">
                        <label for="tracking" class="form-label">เลขพัสดุ</label>
                        <input type="text" class="form-control" id="tracking" name="tracking" 
                               value="<?php echo $order['tracking']; ?>"
                               placeholder="กรุณากรอกเลขพัสดุ">
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" id="note" name="note" rows="3"
                                  placeholder="กรุณากรอกหมายเหตุ (ถ้ามี)"><?php echo $order['note']; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Print Order Modal -->
<div class="modal" id="printOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">พิมพ์ใบสั่งซื้อ</h5>
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

<!-- Payment Image Modal -->
<div class="modal" id="paymentImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">หลักฐานการชำระเงิน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img src="" id="paymentImageLarge" class="img-fluid shadow-sm" alt="หลักฐานการชำระเงิน">
            </div>
        </div>
    </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal" id="rejectPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">ปฏิเสธการชำระเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">กรุณาระบุเหตุผลที่ปฏิเสธการชำระเงิน</p>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        เมื่อยืนยันแล้ว สถานะคำสั่งซื้อจะเปลี่ยนเป็น "รอชำระเงิน" และจะต้องรอการชำระเงินใหม่
                    </div>
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">เหตุผลที่ปฏิเสธ</label>
                        <textarea class="form-control" id="rejectReason" name="note" rows="3" required
                                  placeholder="กรุณาระบุเหตุผลที่ปฏิเสธการชำระเงิน"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger">ยืนยันการปฏิเสธ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Payment Modal -->
<div class="modal" id="approvePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="3">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการชำระเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการยืนยันการชำระเงินของคำสั่งซื้อนี้ใช่หรือไม่?</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        เมื่อยืนยันแล้ว สถานะคำสั่งซื้อจะเปลี่ยนเป็น "รอจัดส่ง"
                    </div>
                    <div class="mb-3">
                        <label for="approveNote" class="form-label">หมายเหตุ (ถ้ามี)</label>
                        <textarea class="form-control" id="approveNote" name="note" rows="2"
                                  placeholder="กรุณากรอกหมายเหตุ (ถ้ามี)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">ยืนยันการชำระเงิน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.payment-image-container {
    position: relative;
    display: inline-block;
}

.payment-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    border-radius: 0.25rem;
}

.payment-image-overlay i {
    color: white;
    font-size: 2rem;
}

.payment-image-container:hover .payment-image-overlay {
    opacity: 1;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle status change to show/hide tracking number field
    const orderStatus = document.getElementById('orderStatus');
    const trackingGroup = document.getElementById('trackingGroup');
    
    orderStatus.addEventListener('change', function() {
        const status = this.value;
        if (status >= 3) {
            trackingGroup.classList.remove('d-none');
        } else {
            trackingGroup.classList.add('d-none');
        }
    });
});

function printOrder(orderId) {
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
                            <p class="mb-1">ชื่อ-นามสกุล: ${buyer.firstname} ${buyer.lastname}</p>
                            <p class="mb-1">เบอร์โทรศัพท์: ${buyer.phone}</p>
                            <p class="mb-1">อีเมล: ${buyer.email}</p>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-2">ข้อมูลการจัดส่ง</h6>
                            <p class="mb-1">วิธีการจัดส่ง: ${order.delivery_type === '1' ? 'ไปรษณีย์ไทย' : 'รับเองที่ร้าน'}</p>
                            <p class="mb-1">ชื่อผู้รับ: ${order.name}</p>
                            <p class="mb-1">เบอร์โทรศัพท์: ${order.phone}</p>
                            <p class="mb-1">ที่อยู่จัดส่ง: ${order.address}</p>
                            ${order.delivery_type === '1' && order.tracking ? `<p class="mb-1">เลขพัสดุ: ${order.tracking}</p>` : ''}
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">ลำดับ</th>
                                <th>รายการสินค้า</th>
                                <th class="text-end" width="120">ราคาต่อชิ้น</th>
                                <th class="text-center" width="80">จำนวน</th>
                                <th class="text-end" width="120">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map((item, index) => {
                                const total = item.product_price * item.qty;
                                return `
                                    <tr>
                                        <td class="text-center">${index + 1}</td>
                                        <td>${item.product_name}</td>
                                        <td class="text-end">฿${parseFloat(item.product_price).toFixed(2)}</td>
                                        <td class="text-center">${item.qty}</td>
                                        <td class="text-end">฿${total.toFixed(2)}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end">รวมเป็นเงิน</td>
                                <td class="text-end">฿${parseFloat(order.total_price).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">ค่าจัดส่ง</td>
                                <td class="text-end">฿${parseFloat(order.delivery_fee).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>จำนวนเงินทั้งสิ้น</strong></td>
                                <td class="text-end"><strong>฿${(parseFloat(order.total_price) + parseFloat(order.delivery_fee)).toFixed(2)}</strong></td>
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
                                ${order.note ? `<p class="mb-1">หมายเหตุ: ${order.note}</p>` : ''}
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

function openPaymentImage(src) {
    const modal = new bootstrap.Modal(document.getElementById('paymentImageModal'));
    document.getElementById('paymentImageLarge').src = src;
    modal.show();
}

function rejectPayment(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('rejectPaymentModal'));
    modal.show();
}

function approvePayment(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('approvePaymentModal'));
    modal.show();
}
</script>
