<?php

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
            break;
    }
}

function get_percent_color($percent) {
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
$sql_last_order = "SELECT * FROM `order` ORDER BY id DESC LIMIT 25";

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
                                                <img src="../upload/product/<?php echo $product['img'] ?>" onerror="this.onerror=null; this.src='../assets/images/404.webp';" alt="<?php echo $product['name'] ?>" class="object-fit-cover rounded" style="width: 3rem; height: 3rem;">
                                                <a href="./../?page=product&id=<?php echo $product['id'] ?>" target="_blank" class="link-primary text-decoration-none"><?php echo $product['name'] ?></a>
                                            </td>
                                            <td class="align-middle text-end">฿<?php echo number_format($product['price'], 2) ?></td>
                                            <td class="align-middle text-center">
                                                <a href="?page=products&type=<?php echo $product['type_id'] ?>" class="link-primary text-decoration-none"><?php echo $type['name'] ?></a>
                                            </td>
                                            <td class="align-middle dropdown">
                                                <button type="button" class="btn w-100 border-0" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="?page=products&search=<?php echo $product['name'] ?>&type=<?php echo $product['type_id'] ?>" class="dropdown-item">รายละเอียด</a></li>
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
                    <span>คำสั่งซื้อล่าสุด</span>
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
                                                <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                            </td>
                                            <td class="align-middle text-center small">
                                                <a href="?page=order&id=<?php echo $order['id'] ?>" class="link-primary text-decoration-none">
                                                    <?php echo format_datetime_thai($order['order_date']) ?>
                                                </a>
                                            </td>
                                            <td class="align-middle text-end">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></td>
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