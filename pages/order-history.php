<?php

if (is_auth()) {
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

    $order_result = get_by_condition('order', ['user_id' => $_SESSION['uid']], 'order_date', 'DESC');
    $orders = fetch($order_result);
?>
    <style>
        .nav-underline .nav-link.active,
        .nav-underline .show>.nav-link {
            color: #0d6efd;
        }

        .custom-nav-item>button {
            background: none;
            padding: 1rem;
            border: 0;
            color: black;
            border-bottom: .125rem solid transparent;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        }

        .custom-nav-item>button:hover,
        .custom-nav-item>button:focus {
            border-bottom-color: currentcolor;
            color: #0d6efd;
        }

        .custom-nav-item>button.active {
            border-bottom-color: #0d6efd;
            color: #0d6efd;
            font-weight: 700;
        }
    </style>
    <div class="container mt-4">
        <p class="fs-2 text-center">ประวัติการสั่งซื้อ</p>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="row row-cols-3 row-cols-lg-6 nav">
                    <div class="col custom-nav-item">
                        <button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#all-tab" type="button">ทั้งหมด</button>
                    </div>
                    <div class="col custom-nav-item">
                        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#unpaid-tab" type="button">ทีต้องชำระ</button>
                    </div>
                    <div class="col custom-nav-item">
                        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#pending-tab" type="button">ที่รอตรวจสอบ</button>
                    </div>
                    <div class="col custom-nav-item">
                        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#verified-tab" type="button">ที่ต้องจัดส่ง</button>
                    </div>
                    <div class="col custom-nav-item">
                        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#delivered-tab" type="button">สำเร็จแล้ว</button>
                    </div>
                    <div class="col custom-nav-item">
                        <button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#canceled-tab" type="button">ยกเลิก</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-5 mb-2 d-none d-lg-flex">
            <div class="col">
                <span class="fw-bold text-muted">หมายเลขคำสั่งซื้อ</span>
            </div>
            <div class="col text-center">
                <span class="fw-bold text-muted">สถานะการสั่งซื้อ</span>
            </div>
            <div class="col text-center">
                <span class="fw-bold text-muted">เลขพัศดุ</span>
            </div>
            <div class="col text-center">
                <span class="fw-bold text-muted">ยอดทั้งหมด</span>
            </div>
            <div class="col"></div>
        </div>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="all-tab">
                <?php
                $all_total = 0;
                foreach ($orders as $order) {
                    $all_total += 1;
                    $color = get_color_by_status($order['status']);
                ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="stretched-link"></a>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-lg-5 align-items-center">
                                <div class="col">
                                    <p class="mb-0 fw-bold">#ORDER-<?php echo $order['id']; ?></p>
                                    <span class="small"><?php echo format_datetime_thai($order['order_date']) ?></span>
                                </div>
                                <div class="col">
                                    <div class="d-none d-lg-block text-center">
                                        <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                    </div>
                                    <div class="d-block d-lg-none text-start">
                                        <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                    </div>
                                </div>
                                <div class="col text-center">
                                    <?php
                                    if ($order['tracking'] != '') {
                                    ?>
                                        <div class="d-none d-lg-block text-center">
                                            <span><?php echo $order['tracking'] ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span><?php echo $order['tracking'] ?></span>
                                        </div>
                                    <?php
                                    } else {
                                    ?>
                                        <div class="d-none d-lg-block text-center">
                                            <span>-</span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span>-</span>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col text-center">
                                    <div class="d-none d-lg-block text-center">
                                        <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                    </div>
                                    <div class="d-block d-lg-none text-start">
                                        <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="d-none d-lg-block text-center">
                                        <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm w-100">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                    <div class="d-block d-lg-none text-end">
                                        <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }

                if ($all_total == 0) {
                ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <p class="text-center text-muted mb-0">ไม่มีประวัติการสั่งซื้อในประเภทนี้</p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="tab-pane fade" id="unpaid-tab">
                <?php
                $unpaid_total = 0;
                foreach ($orders as $order) {
                    if ($order['status'] == '1') {
                        $unpaid_total += 1;
                        $color = get_color_by_status($order['status']);
                ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="stretched-link"></a>
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-lg-5 align-items-center">
                                    <div class="col">
                                        <p class="mb-0 fw-bold"> #ORDER-<?php echo $order['id']; ?></p>
                                        <span class="small"><?php echo format_datetime_thai($order['order_date']) ?></span>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                    </div>
                                    <div class="col text-center">
                                        <?php
                                        if ($order['tracking'] != '') {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span>-</span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span>-</span>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="col text-center">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm w-100">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                        <div class="d-block d-lg-none text-end">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                }

                if ($unpaid_total == 0) {
                    ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <p class="text-center text-muted mb-0">ไม่มีประวัติการสั่งซื้อในประเภทนี้</p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="tab-pane fade" id="pending-tab">
                <?php
                $panding_total = 0;
                foreach ($orders as $order) {
                    if ($order['status'] == '2') {
                        $panding_total += 1;
                        $color = get_color_by_status($order['status']);
                ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="stretched-link"></a>
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-lg-5 align-items-center">
                                    <div class="col">
                                        <p class="mb-0 fw-bold"> #ORDER-<?php echo $order['id']; ?></p>
                                        <span class="small"><?php echo format_datetime_thai($order['order_date']) ?></span>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                    </div>
                                    <div class="col text-center">
                                        <?php
                                        if ($order['tracking'] != '') {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span>-</span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span>-</span>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="col text-center">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm w-100">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                        <div class="d-block d-lg-none text-end">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                }

                if ($panding_total == 0) {
                    ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <p class="text-center text-muted mb-0">ไม่มีประวัติการสั่งซื้อในประเภทนี้</p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="tab-pane fade" id="verified-tab">
                <?php
                $verified_total = 0;
                foreach ($orders as $order) {
                    if ($order['status'] == '3') {
                        $verified_total += 1;
                        $color = get_color_by_status($order['status']);
                ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="stretched-link"></a>
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-lg-5 align-items-center">
                                    <div class="col">
                                        <p class="mb-0 fw-bold"> #ORDER-<?php echo $order['id']; ?></p>
                                        <span class="small"><?php echo format_datetime_thai($order['order_date']) ?></span>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                    </div>
                                    <div class="col text-center">
                                        <?php
                                        if ($order['tracking'] != '') {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span>-</span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span>-</span>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="col text-center">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm w-100">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                        <div class="d-block d-lg-none text-end">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                }

                if ($verified_total == 0) {
                    ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <p class="text-center text-muted mb-0">ไม่มีประวัติการสั่งซื้อในประเภทนี้</p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="tab-pane fade" id="delivered-tab">
                <?php
                $delivered_total = 0;
                foreach ($orders as $order) {
                    if ($order['status'] == '4') {
                        $delivered_total += 1;
                        $color = get_color_by_status($order['status']);
                ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="stretched-link"></a>
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-lg-5 align-items-center">
                                    <div class="col">
                                        <p class="mb-0 fw-bold"> #ORDER-<?php echo $order['id']; ?></p>
                                        <span class="small"><?php echo format_datetime_thai($order['order_date']) ?></span>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                    </div>
                                    <div class="col text-center">
                                        <?php
                                        if ($order['tracking'] != '') {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span>-</span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span>-</span>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="col text-center">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm w-100">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                        <div class="d-block d-lg-none text-end">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                }

                if ($delivered_total == 0) {
                    ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <p class="text-center text-muted mb-0">ไม่มีประวัติการสั่งซื้อในประเภทนี้</p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="tab-pane fade" id="canceled-tab">
                <?php
                $canceled_total = 0;
                foreach ($orders as $order) {
                    if ($order['status'] == '0') {
                        $canceled_total += 1;
                        $color = get_color_by_status($order['status']);
                ?>
                        <div class="card border-0 shadow-sm mb-3">
                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="stretched-link"></a>
                            <div class="card-body">
                                <div class="row row-cols-1 row-cols-lg-5 align-items-center">
                                    <div class="col">
                                        <p class="mb-0 fw-bold"> #ORDER-<?php echo $order['id']; ?></p>
                                        <span class="small"><?php echo format_datetime_thai($order['order_date']) ?></span>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                                        </div>
                                    </div>
                                    <div class="col text-center">
                                        <?php
                                        if ($order['tracking'] != '') {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span><?php echo $order['tracking'] ?></span>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                            <div class="d-none d-lg-block text-center">
                                                <span>-</span>
                                            </div>
                                            <div class="d-block d-lg-none text-start">
                                                <span>-</span>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="col text-center">
                                        <div class="d-none d-lg-block text-center">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                        <div class="d-block d-lg-none text-start">
                                            <span class="text-muted">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-none d-lg-block text-center">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm w-100">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                        <div class="d-block d-lg-none text-end">
                                            <a href="?page=order-detail&order_id=<?php echo $order['id'] ?>" class="btn btn-sm">
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                }

                if ($canceled_total == 0) {
                    ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <p class="text-center text-muted mb-0">ไม่มีประวัติการสั่งซื้อในประเภทนี้</p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php
} else {
    redirect_to('home');
}
