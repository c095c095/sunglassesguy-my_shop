<?php
// รายงานการแจ้งชำระเงิน - Payment Notification Report
function get_bank($id)
{
    switch ($id) {
        case '1':
            return ['กสิกรไทย', 'k-bank.png'];
            break;
        case '2':
            return ['ไทยพาณิชย์', 'scb.png'];
            break;
        case '3':
            return ['กรุงไทย', 'ktb.png'];
            break;
        case '4':
            return ['กรุงเทพ', 'bbl.png'];
            break;
        case '5':
            return ['กรุงศรีอยุธยา', 'bay.png'];
            break;
        case '6':
            return ['ทหารไทยธนชาต', 'ttb.png'];
            break;
        case '7':
            return ['ซีไอเอ็มบี', 'cimb.png'];
            break;
        case '8':
            return ['ยูโอบี', 'uob.png'];
            break;
        case '9':
            return ['พร้อมเพย์', 'promptpay.png'];
            break;
        case '10':
            return ['อื่น ๆ', 'other.png'];
            break;

        default:
            return ['ไม่ทราบ', 'unknown.png'];
            break;
    }
}


// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_bank = isset($_GET['bank_id']) ? $_GET['bank_id'] : '';
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
if ($filter_bank !== '') {
    $where_conditions[] = "p.bank_id = '$filter_bank'";
}
if (!empty($start_date)) {
    $where_conditions[] = "p.pay_date >= '$start_date'";
}
if (!empty($end_date)) {
    $where_conditions[] = "p.pay_date <= '$end_date'";
}
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Main query: payments with order & user info
$sql = "SELECT p.*, b.name as bank_name,
               o.total_price, o.delivery_fee, o.status as order_status,
               u.firstname, u.lastname, u.email, u.phone
        FROM payment p
        LEFT JOIN bank b     ON p.bank_id  = b.id
        LEFT JOIN `order` o  ON p.order_id = o.id
        LEFT JOIN user u     ON o.user_id  = u.id
        $where_clause
        ORDER BY p.pay_date DESC, p.pay_time DESC";
$result = query($sql);
$all_payments = fetch($result);
$total_filtered = count($all_payments);

// Summary stats (full dataset)
$r_total = query("SELECT COUNT(*) as cnt FROM payment");
$row_total = fetch($r_total, 2);
$total_payments_all = (int) $row_total['cnt'];

// รอตรวจสอบ (order status = 2)
$r_pending = query("SELECT COUNT(*) as cnt FROM payment p LEFT JOIN `order` o ON p.order_id = o.id WHERE o.status = '2'");
$row_pending = fetch($r_pending, 2);
$total_pending = (int) $row_pending['cnt'];

// ยืนยันแล้ว (order status >= 3)
$r_approved = query("SELECT COUNT(*) as cnt FROM payment p LEFT JOIN `order` o ON p.order_id = o.id WHERE o.status >= '3'");
$row_approved = fetch($r_approved, 2);
$total_approved = (int) $row_approved['cnt'];

// ยอดรวมที่แจ้งชำระ
$r_amount = query("SELECT SUM(o.total_price + o.delivery_fee) as total FROM payment p LEFT JOIN `order` o ON p.order_id = o.id");
$row_amount = fetch($r_amount, 2);
$total_amount = (float) $row_amount['total'];

// ยอดรวมที่ตรวจสอบแล้ว
$r_amount_approved = query("SELECT SUM(o.total_price + o.delivery_fee) as total FROM payment p LEFT JOIN `order` o ON p.order_id = o.id WHERE o.status >= '3'");
$row_amount_approved = fetch($r_amount_approved, 2);
$total_amount_approved = (float) $row_amount_approved['total'];

// Bank list for filter dropdown
$all_banks = fetch(query("SELECT * FROM bank ORDER BY name ASC"));

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged = array_slice($all_payments, $offset, $per_page);

// Order status helper
function pay_order_status_badge($status)
{
    switch ($status) {
        case '2':
            return ['warning text-dark', 'รอตรวจสอบ'];
        case '3':
            return ['primary', 'รอจัดส่ง'];
        case '4':
            return ['success', 'จัดส่งสำเร็จ'];
        case '0':
            return ['secondary', 'ยกเลิก'];
        case '1':
            return ['secondary', 'รอชำระเงิน'];
        default:
            return ['secondary', 'ไม่ทราบ'];
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
        background-color: #f0f8ff;
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
        background-color: #0d6efd;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #e7f0ff;
        color: #0d6efd;
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
            <h2>รายงานการแจ้งชำระเงิน</h2>
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
                            <div class="count text-dark"><?php echo number_format($total_payments_all); ?></div>
                            <div class="label">แจ้งชำระเงินทั้งหมด</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-credit-card-fill"></i>
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
                            <div class="count text-dark"><?php echo number_format($total_pending); ?></div>
                            <div class="label">รอตรวจสอบ</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-hourglass-split"></i>
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
                            <div class="count text-dark"><?php echo number_format($total_approved); ?></div>
                            <div class="label">ยืนยันแล้ว</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-patch-check-fill"></i>
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
                            <div class="count text-dark"><?php echo number_format($total_amount, 2); ?></div>
                            <div class="label">ยอดรวมที่แจ้งชำระ (บาท)</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-cash-stack"></i>
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
                <input type="hidden" name="page" value="report-payments">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted mb-1">ค้นหา</label>
                        <input type="text" class="form-control" name="search"
                            placeholder="ค้นหาชื่อลูกค้า, เบอร์โทร, อีเมล, รหัสคำสั่งซื้อ..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-1">กรองตามธนาคาร</label>
                        <select class="form-select" name="bank_id">
                            <option value="">ทุกธนาคาร</option>
                            <?php foreach ($all_banks as $bank): ?>
                                <option value="<?php echo $bank['id']; ?>" <?php echo $filter_bank === (string) $bank['id'] ? 'selected' : ''; ?>>
                                    <?php echo get_bank($bank['id'])[0]; ?>
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
                        <a href="?page=report-payments" class="btn btn-outline-secondary" style="border-radius: 10px;">
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
            <h6 class="mb-0 fw-bold">รายละเอียดการแจ้งชำระเงิน</h6>
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
                            <th>ธนาคาร</th>
                            <th>วันที่แจ้งชำระ</th>
                            <th class="text-end">ยอดชำระ (บาท)</th>
                            <th class="text-center">สถานะคำสั่งซื้อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged) > 0): ?>
                            <?php $i = $offset + 1; ?>
                            <?php foreach ($paged as $pay): ?>
                                <?php [$badge_color, $badge_label] = pay_order_status_badge($pay['order_status']); ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?php echo $i++; ?></td>
                                    <td>
                                        <a href="?page=order&id=<?php echo $pay['order_id']; ?>"
                                            class="text-decoration-none fw-semibold">
                                            #ORDER-<?php echo $pay['order_id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?php echo htmlspecialchars($pay['firstname'] . ' ' . $pay['lastname']); ?>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($pay['phone']); ?></small>
                                    </td>
                                    <td>
                                        <span
                                            class="fw-semibold"><?php echo get_bank($pay['bank_id'])[0]; ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo format_datetime_thai($pay['pay_date'] . ' ' . $pay['pay_time']); ?>
                                        </small>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <?php echo number_format($pay['total_price'] + $pay['delivery_fee'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?php echo $badge_color; ?>">
                                            <?php echo $badge_label; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลการแจ้งชำระเงิน</p>
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
                    แสดง <?php echo count($paged); ?> จาก
                    <?php echo $total_filtered; ?> รายการ
                    (หน้า <?php echo $current_page; ?>/<?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-payments&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&bank_id=<?php echo urlencode($filter_bank); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($pi = 1; $pi <= $total_pages; $pi++): ?>
                            <li class="page-item <?php echo ($current_page == $pi) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-payments&p=<?php echo $pi; ?>&search=<?php echo urlencode($search); ?>&bank_id=<?php echo urlencode($filter_bank); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                    <?php echo $pi; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-payments&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&bank_id=<?php echo urlencode($filter_bank); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Print Footer -->
    <div class="text-center mt-3 text-muted d-none d-print-block" style="font-size: 0.8rem;">
        <hr>
        <p class="mb-1"><strong><?php echo WEBSITE_NAME; ?></strong> — ระบบรายงานการแจ้งชำระเงิน</p>
        <p class="mb-0">พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
    </div>
</div>

<!-- ===== PRINT LAYOUT ===== -->
<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex; justify-content:between;">
                <h1 style="font-weight:bold;">รายงานการแจ้งชำระเงิน</h1>
                <h1 style="font-weight:bold;"><?= WEBSITE_NAME ?></h1>
            </div>
            <hr style="margin:0;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display:flex; justify-content:between; color:rgba(33,37,41,0.75);">
                <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
                <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
            </div>
        </div>

        <p style="font-weight:bold; margin:0;">สรุปการแจ้งชำระเงิน</p>
        <div style="margin-left:20px;">
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>แจ้งชำระเงินทั้งหมด</div>
                <div><?= number_format($total_payments_all) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>รอตรวจสอบ</div>
                <div><?= number_format($total_pending) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>ยืนยันแล้ว</div>
                <div><?= number_format($total_approved) ?> รายการ</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>ยอดรวมที่แจ้งชำระ</div>
                <div><?= number_format($total_amount, 2) ?> บาท</div>
            </div>
            <div class="d-flex justify-content-between" style="display:flex; justify-content:between;">
                <div>ยอดรวมที่ตรวจสอบแล้ว</div>
                <div><?= number_format($total_amount_approved, 2) ?> บาท</div>
            </div>
        </div>
        <hr class="my-3">

        <p style="font-weight:bold; margin:0;">รายละเอียดการแจ้งชำระเงิน</p>
        <table class="table table-bordered mt-2" style="font-size:0.85rem;">
            <thead>
                <tr>
                    <th class="text-center" style="text-wrap:nowrap;">ลำดับ</th>
                    <th style="text-wrap:nowrap;">รหัสคำสั่งซื้อ</th>
                    <th style="text-wrap:nowrap;">ลูกค้า</th>
                    <th style="text-wrap:nowrap;">ธนาคาร</th>
                    <th style="text-wrap:nowrap;">วันที่แจ้งชำระ</th>
                    <th class="text-end" style="text-wrap:nowrap;">ยอดชำระ (บาท)</th>
                    <th class="text-center" style="text-wrap:nowrap;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1; ?>
                <?php foreach ($all_payments as $pay): ?>
                    <?php [, $badge_label] = pay_order_status_badge($pay['order_status']); ?>
                    <tr>
                        <td class="text-center"><?php echo $j++; ?></td>
                        <td>#ORDER-<?php echo $pay['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($pay['firstname'] . ' ' . $pay['lastname']); ?></td>
                        <td><?php echo get_bank($pay['bank_id'])[0] ?? '-'; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($pay['pay_date'])); ?>     <?php echo $pay['pay_time']; ?></td>
                        <td class="text-end"><?php echo number_format($pay['total_price'] + $pay['delivery_fee'], 2); ?>
                        </td>
                        <td class="text-center"><?php echo $badge_label; ?></td>
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