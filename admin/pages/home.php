<?php
include_once "../core/helpers/image_upload.php";

// ── Cancel overdue unpaid orders (status=1, older than 7 days) ──────────────
function cancel_order_and_restock($order_id)
{
    // Restore stock for each item in the order
    $items_sql = "SELECT product_id, qty FROM order_detail WHERE order_id = " . (int) $order_id;
    $items_result = query($items_sql);
    $order_items = fetch($items_result);
    if (is_array($order_items) && count($order_items) > 0) {
        foreach ($order_items as $item) {
            $pid = (int) $item['product_id'];
            $qty = (int) $item['qty'];
            query("UPDATE product SET stock = stock + $qty WHERE id = $pid");
        }
    }
    // Set order status to 0 (cancelled)
    update_by_id('order', (int) $order_id, ['status' => '0']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $home_action = $_POST['action'] ?? '';

    // Cancel a single overdue order
    if ($home_action === 'cancel_overdue_order') {
        $oid = (int) ($_POST['order_id'] ?? 0);
        if ($oid > 0) {
            cancel_order_and_restock($oid);
            show_alert('ยกเลิกคำสั่งซื้อ #ORDER-' . $oid . ' เรียบร้อยแล้ว');
        }
        reload_page();
        exit();
    }

    // Cancel ALL overdue orders at once
    if ($home_action === 'cancel_all_overdue') {
        $overdue_ids_raw = $_POST['overdue_ids'] ?? '';
        $overdue_ids = array_filter(array_map('intval', explode(',', $overdue_ids_raw)));
        $count = 0;
        foreach ($overdue_ids as $oid) {
            cancel_order_and_restock($oid);
            $count++;
        }
        show_alert('ยกเลิกคำสั่งซื้อที่ค้างชำระทั้งหมด ' . $count . ' รายการเรียบร้อยแล้ว');
        reload_page();
        exit();
    }
}

// Handle edit product form submitted from home page modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_from_home') {
    $id = $_POST['id'];
    $productData = [
        'name' => $_POST['product_name'],
        'detail' => $_POST['product_description'],
        'price' => $_POST['product_price'],
        'stock' => $_POST['product_quantity'],
        'type_id' => $_POST['product_type'],
    ];
    if (!empty($_FILES['product_image']['name'])) {
        $image = $_FILES['product_image'];
        if (is_image($image)) {
            $image_name = generate_image_name($image);
            if (upload_image($image, $image_name, 'product/')) {
                $productData['img'] = $image_name;
            }
        }
    }
    if (update_by_id('product', $id, $productData)) {
        show_alert('แก้ไขสินค้าสำเร็จ');
    } else {
        show_alert('แก้ไขสินค้าไม่สำเร็จ');
    }
    reload_page();
}

function get_color_by_status($status)
{
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
            break;
    }
}

function get_percent_color($percent)
{
    if ($percent > 0) {
        return ['success', 'arrow-up'];
    } elseif ($percent < 0) {
        return ['danger', 'arrow-down'];
    } else {
        return ['secondary', 'arrow-repeat'];
    }
}

$sql_week_sale = "SELECT SUM(total_price) as total_price FROM `order` WHERE WEEK(order_date) = WEEK(CURDATE()) AND status='4'";
$sql_last_week_sale = "SELECT SUM(total_price) as total_price FROM `order` WHERE WEEK(order_date) = WEEK(CURDATE()) - 1 AND status='4'";
$sql_all_sale = "SELECT SUM(total_price) as total_price FROM `order` WHERE status='4'";
$sql_this_month_sale = "SELECT SUM(total_price) as total_price FROM `order` WHERE MONTH(order_date) = MONTH(CURDATE()) AND status='4'";
$sql_last_month_sale = "SELECT SUM(total_price) as total_price FROM `order` WHERE MONTH(order_date) = MONTH(CURDATE()) - 1 AND status='4'";
$sql_total_order = "SELECT COUNT(id) as total_order FROM `order`";
$sql_this_month_order = "SELECT COUNT(id) as total_order FROM `order` WHERE MONTH(order_date) = MONTH(CURDATE())";
$sql_last_month_order = "SELECT COUNT(id) as total_order FROM `order` WHERE MONTH(order_date) = MONTH(CURDATE()) - 1";
$sql_total_user = "SELECT COUNT(id) as total_user FROM user WHERE permission = '1'";
$sql_this_month_user = "SELECT COUNT(id) as total_user FROM user WHERE permission = '1' AND MONTH(created_at) = MONTH(CURDATE())";
$sql_last_month_user = "SELECT COUNT(id) as total_user FROM user WHERE permission = '1' AND MONTH(created_at) = MONTH(CURDATE()) - 1";
$sql_top_sell = "SELECT p.*, SUM(od.qty) as total_quantity
    FROM product p
    JOIN order_detail od ON p.id = od.product_id
    JOIN `order` o ON od.order_id = o.id
    WHERE o.status = '4'
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 25";
$sql_last_order = "SELECT * FROM `order` ORDER BY id DESC LIMIT 15";
// รวม out-of-stock ไว้ใน query เดียว เพื่อใช้ทั้งสรุปและตาราง
$sql_low_stock = "SELECT id, name, img, price, stock, type_id FROM product WHERE stock <= 10 ORDER BY stock ASC";
$sql_out_of_stock = "SELECT COUNT(id) as total FROM product WHERE stock = 0";
// คำสั่งซื้อที่ยังไม่ได้ชำระเงินและเกิน 7 วัน
$sql_overdue_orders = "SELECT o.id, o.order_date, o.total_price, o.delivery_fee,
    u.firstname, u.lastname
    FROM `order` o
    LEFT JOIN user u ON o.user_id = u.id
    WHERE o.status = '1'
      AND o.order_date <= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY o.order_date ASC";

$query_week_sale = query($sql_week_sale);
$query_last_week_sale = query($sql_last_week_sale);
$query_all_sale = query($sql_all_sale);
$query_this_month_sale = query($sql_this_month_sale);
$query_last_month_sale = query($sql_last_month_sale);
$query_total_order = query($sql_total_order);
$query_this_month_order = query($sql_this_month_order);
$query_last_month_order = query($sql_last_month_order);
$query_total_user = query($sql_total_user);
$query_this_month_user = query($sql_this_month_user);
$query_last_month_user = query($sql_last_month_user);
$query_top_sell = query($sql_top_sell);
$query_last_order = query($sql_last_order);
$query_low_stock = query($sql_low_stock);
$query_out_of_stock = query($sql_out_of_stock);
$overdue_orders = fetch(query($sql_overdue_orders));

$week_sale = fetch($query_week_sale, 2);
$last_week_sale = fetch($query_last_week_sale, 2);
$all_sale = fetch($query_all_sale, 2);
$this_month_sale = fetch($query_this_month_sale, 2);
$last_month_sale = fetch($query_last_month_sale, 2);
$total_order = fetch($query_total_order, 2);
$this_month_order = fetch($query_this_month_order, 2);
$last_month_order = fetch($query_last_month_order, 2);
$total_user = fetch($query_total_user, 2);
$this_month_user = fetch($query_this_month_user, 2);
$last_month_user = fetch($query_last_month_user, 2);
$out_of_stock = fetch($query_out_of_stock, 2);

$week_sale_percent = $last_week_sale['total_price'] > 0 ? round((($week_sale['total_price'] - $last_week_sale['total_price']) / $last_week_sale['total_price']) * 100) : null;
$month_sale_percent = $last_month_sale['total_price'] > 0 ? round((($this_month_sale['total_price'] - $last_month_sale['total_price']) / $last_month_sale['total_price']) * 100) : null;
$month_order_percent = $last_month_order['total_order'] > 0 ? round((($this_month_order['total_order'] - $last_month_order['total_order']) / $last_month_order['total_order']) * 100) : null;
$month_user_percent = $last_month_user['total_user'] > 0 ? round((($this_month_user['total_user'] - $last_month_user['total_user']) / $last_month_user['total_user']) * 100) : null;

$week_sale_percent_color = get_percent_color($week_sale_percent);
$month_sale_percent_color = get_percent_color($month_sale_percent);
$month_order_percent_color = get_percent_color($month_order_percent);
$month_user_percent_color = get_percent_color($month_user_percent);
?>
<div class="container-fluid mb-3">
    <div class="row row-cols-4 align-items-center mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between gap-2">
                        <span class="text-muted">ยอดขายสัปดาห์นี้</span>
                        <span class="fs-3 fw-bold">฿<?php echo number_format($week_sale['total_price']) ?></span>
                        <div class="d-flex gap-2 small">
                            <span class="badge text-bg-<?php echo $week_sale_percent_color[0] ?>">
                                <i class="bi bi-<?php echo $week_sale_percent_color[1] ?>"></i>
                                <?php echo $week_sale_percent ? $week_sale_percent . '%' : 'N/A' ?>
                            </span>
                            <span class="text-muted">จากสัปดาห์ที่แล้ว</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between gap-2">
                        <span class="text-muted">ยอดขายทั้งหมด</span>
                        <span class="fs-3 fw-bold">฿<?php echo number_format($all_sale['total_price'], 2) ?></span>
                        <div class="d-flex gap-2 small">
                            <span class="badge text-bg-<?php echo $month_sale_percent_color[0] ?>">
                                <i class="bi bi-<?php echo $month_sale_percent_color[1] ?>"></i>
                                <?php echo $month_sale_percent ? $month_sale_percent . '%' : 'N/A' ?>
                            </span>
                            <span class="text-muted">จากเดือนที่แล้ว</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between gap-2">
                        <span class="text-muted">จำนวนการสั่งซื้อทั้งหมด</span>
                        <span class="fs-3 fw-bold"><?php echo number_format($total_order['total_order']) ?></span>
                        <div class="d-flex gap-2 small">
                            <span class="badge text-bg-<?php echo $month_order_percent_color[0] ?>">
                                <i class="bi bi-<?php echo $month_order_percent_color[1] ?>"></i>
                                <?php echo $month_order_percent ? $month_order_percent . '%' : 'N/A' ?>
                            </span>
                            <span class="text-muted">จากเดือนที่แล้ว</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column justify-content-between gap-2">
                        <span class="text-muted">จำนวนลูกค้าทั้งหมด</span>
                        <span class="fs-3 fw-bold"><?php echo number_format($total_user['total_user']) ?></span>
                        <div class="d-flex gap-2 small">
                            <span class="badge text-bg-<?php echo $month_user_percent_color[0] ?>">
                                <i class="bi bi-<?php echo $month_user_percent_color[1] ?>"></i>
                                <?php echo $month_user_percent ? $month_user_percent . '%' : 'N/A' ?>
                            </span>
                            <span class="text-muted">จากเดือนที่แล้ว</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Alert Section: Low Stock & Out of Stock Products -->
    <?php
    $low_stock_products = fetch($query_low_stock); // fetch once, reuse below
    $total_low_stock = count($low_stock_products);
    $total_out_of_stock = (int) $out_of_stock['total'];
    $critical_count = 0; // stock 1-5
    $low_count = 0; // stock 6-10
    foreach ($low_stock_products as $p) {
        if ($p['stock'] == 0) { /* counted separately */
        } elseif ($p['stock'] <= 5)
            $critical_count++;
        else
            $low_count++;
    }
    $has_alert = $total_low_stock > 0 || $total_out_of_stock > 0;
    // Auto-expand the table when there are out-of-stock or critical items
    $auto_expand = ($total_out_of_stock > 0 || $critical_count > 0) ? 'show' : '';
    ?>
    <?php
    // ── Overdue unpaid orders section ───────────────────────────────────────────
    if (is_array($overdue_orders) && count($overdue_orders) > 0):
        $overdue_ids = implode(',', array_column($overdue_orders, 'id'));
        ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">คำสั่งซื้อค้างชำระเกิน 7 วัน</span>
                            <span class="badge text-bg-danger rounded-pill px-3 py-2">
                                <?php echo count($overdue_orders); ?> รายการ
                            </span>
                        </div>
                        <form method="POST" class="d-inline" id="cancelAllOverdueForm">
                            <input type="hidden" name="action" value="cancel_all_overdue">
                            <input type="hidden" name="overdue_ids" value="<?php echo $overdue_ids; ?>">
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="confirmCancelAll(<?php echo count($overdue_orders); ?>)">
                                <i class="bi bi-x-circle-fill me-1"></i>
                                ยกเลิกทั้งหมด <?php echo count($overdue_orders); ?> รายการ
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <td class="small text-muted text-center" style="width:6rem;">รหัสคำสั่งซื้อ</td>
                                        <td class="small text-muted">ชื่อลูกค้า</td>
                                        <td class="small text-muted text-center">วันที่สั่งซื้อ</td>
                                        <td class="small text-muted text-center">ค้างมา (วัน)</td>
                                        <td class="small text-muted text-end">ราคารวม</td>
                                        <td class="small text-muted text-center" style="width:9rem;">จัดการ</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($overdue_orders as $od):
                                        $days_overdue = (int) floor((time() - strtotime($od['order_date'])) / 86400);
                                        ?>
                                        <tr>
                                            <td class="text-center ps-3">
                                                <a href="?page=order&id=<?php echo $od['id']; ?>"
                                                    class="link-danger fw-semibold text-decoration-none text-nowrap">
                                                    <?php echo format_order_id($od['id']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($od['firstname'] . ' ' . $od['lastname']); ?></td>
                                            <td class="text-center small text-muted">
                                                <?php echo format_datetime_thai($od['order_date'], 2); ?>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge text-bg-<?php echo $days_overdue >= 14 ? 'danger' : 'warning text-dark'; ?> rounded-pill px-3">
                                                    <?php echo $days_overdue; ?> วัน
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                ฿<?php echo number_format($od['total_price'] + $od['delivery_fee'], 2); ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                                    <a href="?page=order&id=<?php echo $od['id']; ?>"
                                                        class="btn btn-sm btn-ghost-secondary py-0 px-2">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="cancel_overdue_order">
                                                        <input type="hidden" name="order_id" value="<?php echo $od['id']; ?>">
                                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                                            onclick="confirmCancelOne(<?php echo $od['id']; ?>, this.form)">
                                                            ยกเลิก
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($has_alert): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <!-- Card Header -->
                    <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                        <div class="d-flex align-items-center gap-2">แจ้งเตือนสต็อกสินค้า</div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Summary pills in header -->
                            <?php if ($total_out_of_stock > 0): ?>
                                <span class="badge rounded-pill text-bg-danger px-3 py-2">
                                    <i class="bi bi-x-circle-fill me-1"></i>สินค้าหมด <?php echo $total_out_of_stock; ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($critical_count > 0): ?>
                                <span class="badge rounded-pill text-bg-warning px-3 py-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>ใกล้หมด <?php echo $critical_count; ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($low_count > 0): ?>
                                <span class="badge rounded-pill text-bg-info px-3 py-2">
                                    <i class="bi bi-info-circle-fill me-1"></i>เหลือน้อย <?php echo $low_count; ?>
                                </span>
                            <?php endif; ?>
                            <a href="?page=products" class="btn btn-sm btn-outline-secondary ms-2">
                                จัดการสินค้า
                            </a>
                        </div>
                    </div>

                    <!-- Product Card Grid -->
                    <?php if ($total_low_stock > 0): ?>
                        <div class="card-body p-3">
                            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                                <?php foreach ($low_stock_products as $product):
                                    if ($product['stock'] == 0) {
                                        $border_color = '#dc3545'; // danger
                                        $bar_color = 'bg-danger';
                                        $level_label = 'สินค้าหมด';
                                        $level_badge = 'text-bg-danger';
                                        $stock_pct = 0;
                                    } elseif ($product['stock'] <= 5) {
                                        $border_color = '#ffc107'; // warning
                                        $bar_color = 'bg-warning';
                                        $level_label = 'ใกล้หมด';
                                        $level_badge = 'text-bg-warning text-dark';
                                        $stock_pct = ($product['stock'] / 10) * 100;
                                    } else {
                                        $border_color = '#0dcaf0'; // info
                                        $bar_color = 'bg-info';
                                        $level_label = 'เหลือน้อย';
                                        $level_badge = 'text-bg-info';
                                        $stock_pct = ($product['stock'] / 10) * 100;
                                    }
                                    ?>
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden"
                                            style="border-left: 4px solid <?php echo $border_color; ?> !important;">
                                            <div class="card-body p-3 d-flex gap-3 align-items-start">
                                                <!-- Product Image -->
                                                <img src="../upload/product/<?php echo $product['img']; ?>"
                                                    onerror="this.onerror=null; this.src='../assets/images/404.webp';"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                    class="object-fit-cover rounded flex-shrink-0" style="width:3rem; height:3rem;">
                                                <!-- Info -->
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <span class="fw-semibold text-truncate small" style="max-width:9rem;"
                                                            title="<?php echo htmlspecialchars($product['name']); ?>">
                                                            <?php echo htmlspecialchars($product['name']); ?>
                                                        </span>
                                                        <span class="badge <?php echo $level_badge; ?> ms-1 flex-shrink-0">
                                                            <?php echo $level_label; ?>
                                                        </span>
                                                    </div>
                                                    <!-- Stock count -->
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <span class="fs-5 fw-bold lh-1" style="color:<?php echo $border_color; ?>">
                                                            <?php echo $product['stock']; ?>
                                                        </span>
                                                        <span class="text-muted small">หน่วย</span>
                                                    </div>
                                                    <!-- Mini stock bar -->
                                                    <div class="progress mb-2" style="height:5px;"
                                                        title="สต็อกเหลือ <?php echo $product['stock']; ?>/10">
                                                        <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar"
                                                            style="width:<?php echo $stock_pct; ?>%"></div>
                                                    </div>
                                                    <!-- Price + Edit -->
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span
                                                            class="small text-muted">฿<?php echo number_format($product['price'], 2); ?></span>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary py-0 px-2 small mt-3"
                                                            onclick="editProductFromHome(<?php echo (int) $product['id']; ?>)">
                                                            แก้ไข
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header">
                    <span>สินค้าขายดี</span>
                </div>
                <div class="card-body p-0 overflow-y-auto" style="max-height: 30rem;">
                    <?php
                    if (get_num_rows($query_top_sell) > 0) {
                        ?>
                        <div class="table-responsive" style="min-height: 15rem;">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <td class="small text-muted text-center">ชื่อ</td>
                                        <td class="small text-muted text-end">ราคา</td>
                                        <td class="small text-muted text-center">ประเภท</td>
                                        <td class="small text-muted text-center">จัดการ</td>
                                    </tr>
                                </thead>
                                <tbody class="align-content-center">
                                    <?php
                                    foreach (fetch($query_top_sell) as $product) {
                                        $type_result = get_by_id('product_type', $product['type_id']);
                                        $type = fetch($type_result, 2);
                                        ?>
                                        <tr>
                                            <td class="align-middle">
                                                <img src="../upload/product/<?php echo $product['img'] ?>"
                                                    onerror="this.onerror=null; this.src='../assets/images/404.webp';"
                                                    alt="<?php echo $product['name'] ?>" class="object-fit-cover rounded"
                                                    style="width: 3rem; height: 3rem;">
                                                <a href="./../?page=product&id=<?php echo $product['id'] ?>" target="_blank"
                                                    class="link-primary text-decoration-none"><?php echo $product['name'] ?></a>
                                            </td>
                                            <td class="align-middle text-end">฿<?php echo number_format($product['price'], 2) ?>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="?page=products&type=<?php echo $product['type_id'] ?>"
                                                    class="link-primary text-decoration-none"><?php echo $type['name'] ?></a>
                                            </td>
                                            <td class="align-middle dropdown">
                                                <button type="button" class="btn w-100 border-0" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="?page=products&search=<?php echo $product['name'] ?>&type=<?php echo $product['type_id'] ?>"
                                                            class="dropdown-item">รายละเอียด</a></li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="text-center my-3">
                            <span class="text-muted">ไม่มีข้อมูล</span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header">
                    <span>15 คำสั่งซื้อล่าสุด</span>
                </div>
                <div class="card-body p-0 overflow-y-auto" style="max-height: 30rem;">
                    <?php
                    if (get_num_rows($query_last_order) > 0) {
                        ?>
                        <div class="table-responsive" style="min-height: 15rem;">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <td class="small text-muted text-center">สถานะ</td>
                                        <td class="small text-muted text-center">เวลา</td>
                                        <td class="small text-muted text-end">ยอดรวม</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach (fetch($query_last_order) as $order) {
                                        $user_result = get_by_id('user', $order['user_id']);
                                        $user = fetch($user_result, 2);
                                        $color = get_color_by_status($order['status']);
                                        ?>
                                        <tr>
                                            <a href="?page=order&id=<?php echo $order['id'] ?>"></a>
                                            <td class="align-middle text-center">
                                                <span
                                                    class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                            </td>
                                            <td class="align-middle text-center small">
                                                <a href="?page=order&id=<?php echo $order['id'] ?>"
                                                    class="link-primary text-decoration-none">
                                                    <?php echo format_datetime_thai($order['order_date']) ?>
                                                </a>
                                            </td>
                                            <td class="align-middle text-end">
                                                ฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="text-center my-3">
                            <span class="text-muted">ไม่มีข้อมูล</span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal (Home Page) -->
<?php $home_product_types = fetch(get_all('product_type')); ?>
<div class="modal fade" id="homeEditProductModal" tabindex="-1" aria-labelledby="homeEditProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="homeEditProductModalLabel">แก้ไขสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="homeEditProductForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_from_home">
                    <input type="hidden" name="id" id="home_edit_id">
                    <div class="mb-3">
                        <label for="home_edit_product_name" class="form-label">ชื่อสินค้า</label>
                        <input type="text" class="form-control" id="home_edit_product_name" name="product_name"
                            maxlength="255" placeholder="กรุณากรอกชื่อสินค้า" required>
                    </div>
                    <div class="mb-3">
                        <label for="home_edit_product_type" class="form-label">ประเภทสินค้า</label>
                        <select class="form-select" id="home_edit_product_type" name="product_type" required>
                            <option value="" disabled>กรุณาเลือกประเภทสินค้า</option>
                            <?php foreach ($home_product_types as $pt): ?>
                                <option value="<?php echo $pt['id']; ?>"><?php echo htmlspecialchars($pt['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="home_edit_product_description" class="form-label">รายละเอียดสินค้า</label>
                        <textarea class="form-control" id="home_edit_product_description" name="product_description"
                            rows="4" placeholder="กรุณากรอกรายละเอียดสินค้า"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="home_edit_product_price" class="form-label">ราคา (บาท)</label>
                            <input type="number" class="form-control" id="home_edit_product_price" name="product_price"
                                min="0" step="0.01" placeholder="ราคาสินค้า" required>
                        </div>
                        <div class="col-md-6">
                            <label for="home_edit_product_quantity" class="form-label">จำนวน</label>
                            <input type="number" class="form-control" id="home_edit_product_quantity"
                                name="product_quantity" min="0" placeholder="จำนวนสต็อก" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปภาพสินค้า <small class="text-muted">(ไม่บังคับ
                                หากไม่เปลี่ยนให้เว้นว่าง)</small></label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img id="home_edit_product_preview" src="" alt="preview"
                                class="object-fit-cover rounded border flex-shrink-0"
                                style="width:5rem; height:5rem; display:none;">
                            <input type="file" class="form-control" id="home_edit_product_image" name="product_image"
                                accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="homeEditProductForm" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Confirm & submit single cancel
    function confirmCancelOne(orderId, form) {
        if (confirm('ยืนยันการยกเลิกคำสั่งซื้อ #ORDER-' + orderId + '?\n\nระบบจะคืนสินค้ากลับเข้าสต็อกโดยอัตโนมัติ')) {
            form.submit();
        }
    }

    // Confirm & submit bulk cancel
    function confirmCancelAll(count) {
        if (confirm('ยืนยันการยกเลิกคำสั่งซื้อค้างชำระทั้งหมด ' + count + ' รายการ?\n\nระบบจะคืนสินค้ากลับเข้าสต็อกทุกรายการโดยอัตโนมัติ')) {
            document.getElementById('cancelAllOverdueForm').submit();
        }
    }

    (function () {
        window.editProductFromHome = function (id) {
            fetch('../core/helpers/get_data.php?type=product&id=' + id)
                .then(function (r) { return r.json(); })
                .then(function (p) {
                    document.getElementById('home_edit_id').value = p.id;
                    document.getElementById('home_edit_product_name').value = p.name;
                    document.getElementById('home_edit_product_description').value = p.detail || '';
                    document.getElementById('home_edit_product_price').value = p.price;
                    document.getElementById('home_edit_product_quantity').value = p.stock;
                    document.getElementById('home_edit_product_type').value = p.type_id;
                    var preview = document.getElementById('home_edit_product_preview');
                    if (p.img) {
                        preview.src = '../upload/product/' + p.img;
                        preview.onerror = function () { this.style.display = 'none'; };
                        preview.style.display = 'block';
                    } else {
                        preview.style.display = 'none';
                    }
                    new bootstrap.Modal(document.getElementById('homeEditProductModal')).show();
                })
                .catch(function () { alert('ไม่สามารถดึงข้อมูลสินค้าได้'); });
        };

        // Reset image preview when modal closes
        document.getElementById('homeEditProductModal').addEventListener('hidden.bs.modal', function () {
            var preview = document.getElementById('home_edit_product_preview');
            preview.src = '';
            preview.style.display = 'none';
            document.getElementById('home_edit_product_image').value = '';
        });

        // Live image preview
        document.getElementById('home_edit_product_image').addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var preview = document.getElementById('home_edit_product_preview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        });
    })();
</script>