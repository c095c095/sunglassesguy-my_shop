<?php

if (is_auth()) {
    function get_color_by_status($status) {
        switch ($status) {
            case '0':
                return [
                    ['secondary', 'ยกเลิก'],
                    ['secondary', 'ยกเลิก']
                ];
                break;
            case '1':
                return [
                    ['secondary', 'รอชำระเงิน'],
                    ['secondary', 'รอชำระเงิน']
                ];
                break;
            case '2':
                return [
                    ['primary', 'รอตรวจสอบ'],
                    ['primary', 'รอตรวจสอบ']
                ];
                break;
            case '3':
                return [
                    ['primary', 'รอจัดส่ง'],
                    ['success', 'ชำระเงินแล้ว']
                ];
                break;
            case '4':
                return [
                    ['success', 'จัดส่งสำเร็จ'],
                    ['success', 'ชำระเงินแล้ว']
                ];
                break;

            default:
                return [
                    ['danger', 'ไม่ทราบสถานะ'],
                    ['danger', 'ไม่ทราบสถานะ']
                ];
                break;
        }
    }

    $order_id = $_GET['order_id'];

    $order_result = get_by_condition('order', [
        'id' => $order_id,
        'user_id' => $_SESSION['uid']
    ]);

    if (get_num_rows($order_result) == 0) {
        redirect_to('home');
        exit();
    }

    if (isset($_POST['cancel_order'])) {
        $update_result = update_by_id('order', $order_id, ['status' => 0]);
        if ($update_result) {
            show_alert('ยกเลิกคำสั่งซื้อสำเร็จ');
            redirect_to('order-detail&order_id=' . $order_id);
        } else {
            show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
            redirect_to('order-detail&order_id=' . $order_id);
        }
    }

    $order = fetch($order_result, 2);
    $order_item_result = get_by_condition('order_detail', ['order_id' => $order_id]);
    $order_items = fetch($order_item_result);
    $color = get_color_by_status($order['status']);
?>
    <div class="container mt-4">
        <p class="fs-2 text-center">รายละเอียดคำสั่งซื้อ</p>
        <a href="?page=order-history" class="d-block text-dark text-decoration-none mb-3">
            <i class="bi bi-chevron-left"></i>
            ย้อนกลับ
        </a>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="progresses">
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
                <div class="mt-5 text-end">
                    <p class="badge fs-6 px-3 py-2 mb-0 rounded-pill" style="background-color: #b3d7ff; color: #0d6efd;">
                        <span class="fw-normal">วิธีการจัดส่ง</span>
                        <span><?php if ($order['delivery_type'] == 1){ echo 'ไปรษณีย์ไทย'; } else { echo 'รับเองที่ร้าน'; } ?></span>
                    </p>
                </div>
                <?php
                if ($order['status'] == 1) {
                ?>
                    <div class="text-end mt-3">
                        <form action="" method="post">
                            <input type="hidden" name="cancel_order" value="true">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('คุณต้องการยกเลิกคำสั่งซื้อนี้ใช่หรือไม่?');">ยกเลิกคำสั่งซื้อ</button>
                        </form>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
        <?php
        if (($order['status'] == 4 && $order['delivery_type'] == 1) || $order['tracking'] != '') {
        ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row align-items-center row-cols-1 row-cols-lg-2">
                        <div class="col">
                            <span class="text-muted">เลขพัสดุ:</span>
                            <span class="fw-bold" style="user-select: all;"><?php if ($order['tracking'] != ''){ echo $order['tracking']; } else { echo '-'; } ?></span>
                        </div>
                        <div class="d-block d-lg-none mb-3"></div>
                        <div class="col">
                            <div class="d-none d-lg-block text-end">
                                <a href="https://track.thailandpost.co.th/?trackNumber=<?php echo $order['tracking'] ?>" target="_blank" class="btn btn-primary <?php if ($order['tracking'] == ''){ echo 'disabled'; } ?>">ติดตามพัสดุ</a>
                            </div>
                            <a href="https://track.thailandpost.co.th/?trackNumber=<?php echo $order['tracking'] ?>" target="_blank" class="btn btn-primary d-block d-lg-none w-100 <?php if ($order['tracking'] == ''){ echo 'disabled'; } ?>">ติดตามพัสดุ</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row row-cols-1 row-cols-lg-2 align-items-center">
                    <div class="col">
                        <span class="text-muted">สถานะการชำระเงิน:</span>
                        <span class="badge text-bg-<?php echo $color[1][0]; ?>"><?php echo $color[1][1] ?></span>
                        <?php
                        if ($order['note'] != '') {
                        ?>
                            <div>
                                <span class="text-muted">หมายเหตุ:</span>
                                <span class="fw-bold"><?php echo $order['note'] ?></span>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    if ($order['status'] == 1) {
                    ?>
                        <div class="col text-end">
                            <a href="?page=payment&order_id=<?php echo $order_id ?>" class="btn btn-primary d-none d-lg-inline">ชำระเงิน</a>
                            <a href="?page=payment&order_id=<?php echo $order_id ?>" class="btn btn-primary d-block d-lg-none mt-3">ชำระเงิน</a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <div class="row row-cols-1 row-cols-lg-3">
                    <div class="col">
                        <span class="text-muted small">หมายเลขคำสั่งซื้อ:</span>
                        <span><?php ?>#ORDER-<?php echo $order['id']; ?></span>
                    </div>
                    <div class="col">
                        <div class="d-none d-lg-block text-center">
                            <span class="text-muted small">วันที่ทำรายการ:</span>
                            <span><?php echo format_datetime_thai($order['order_date']) ?></span>
                        </div>
                        <div class="d-block d-lg-none text-start">
                            <span class="text-muted small">วันที่ทำรายการ:</span>
                            <span><?php echo format_datetime_thai($order['order_date']) ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-none d-lg-block text-end">
                            <span class="text-muted small">สถานะการสั่งซื้อ:</span>
                            <span class="badge text-bg-<?php echo $color[0][0]; ?>"><?php echo $color[0][1] ?></span>
                        </div>
                        <div class="d-block d-lg-none text-start">
                            <span class="text-muted small">สถานะการสั่งซื้อ:</span>
                            <span class="badge text-bg-<?php echo $color[0][0]; ?>"><?php echo $color[0][1] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                foreach ($order_items as $item) {
                ?>
                    <div class="row align-items-center my-3">
                        <div class="col-12 col-lg-1">
                            <img src="upload/product/<?php echo $item['product_img'] ?>" class="object-fit-cover" style="width: 75px; height:  75px; max-width: 100%;" onerror="this.onerror=null; this.src='assets/images/404.webp';" alt="">
                        </div>
                        <div class="col-12 col-lg-4">
                            <span class="text-muted"><?php echo $item['product_price'] ?> x <?php echo $item['qty'] ?></span>
                        </div>
                        <div class="col-12 col-lg-7">
                            <span class="text-muted"><?php echo $item['product_name'] ?></span>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-lg-2">
            <div class="col mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="fw-bold mb-4">ที่อยู่ในการจัดส่ง</p>
                        <p class="text-muted">คุณ <?php echo $order['name'] ?> (<?php echo $order['phone'] ?>)</p>
                        <p class="text-muted"><?php echo $order['address'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="fw-bold mb-4">ที่อยู่ในราคารวมทั้งหมด</p>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <span class="text-muted">ราคารวม:</span>
                            <span class="fw-bold">฿<?php echo number_format($order['total_price'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <span class="text-muted">ราคาก่อนภาษี:</span>
                            <span class="fw-bold">฿<?php echo number_format((($order['total_price'] + $order['delivery_fee']) * (100 / 107)), 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <span class="text-muted">ภาษี:</span>
                            <span class="fw-bold">฿<?php echo number_format((($order['total_price'] + $order['delivery_fee']) * (7 / 107)), 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
                            <span class="text-muted">ค่าส่ง:</span>
                            <span class="fw-bold">฿<?php echo number_format($order['delivery_fee'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                            <span class="fw-bold">รวมทั้งหมด:</span>
                            <span class="fw-bold">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    redirect_to('home');
}
