<?php

if (is_auth()) {
    $user = get_session_user();

    $cart_result = get_by_condition('cart', ['user_id' => $_SESSION['uid']]);
    $cart = fetch($cart_result);
    $cart_count = get_num_rows($cart_result);

    if ($cart_count == 0) {
        redirect_to('home');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $delivery_type = $_POST['delivery_type'];
        $total = 0;

        foreach ($cart as $item) {
            $product_result = get_by_id('product', $item['product_id']);
            $product = fetch($product_result, 2);

            if (!$product) {
                $is_edit = true;
                continue;
            }

            if ($product['stock'] < $item['qty']) {
                $is_edit = true;
            }

            if ($product['stock'] < 1) {
                $is_edit = true;
            }
        }

        if ($is_edit == true) {
            show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
            redirect_to('cart');
            exit();
        }


        foreach ($cart as $item) {
            $product = get_by_id('product', $item['product_id']);
            $product = fetch($product, 2);
            $total += $product['price'] * $item['qty'];
        }

        if ($delivery_type == 1) {
            if ($total < 500) {
                $delivery_fee = 50;
            } else {
                $delivery_fee = 0;
            }
        } else {
            $delivery_fee = 0;
        }

        $order = [
            'user_id' => $_SESSION['uid'],
            'order_date' => date('Y-m-d H:i:s'),
            'name' => $firstname . ' ' . $lastname,
            'phone' => $phone,
            'address' => $address,
            'total_price' => $total,
            'delivery_fee' => $delivery_fee,
            'delivery_type' => $delivery_type,
            'status' => 1,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $insert_result = insert('order', $order);

        if (!$insert_result) {
            show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
            redirect_to('cart');
            exit();
        }

        $order_sql = "SELECT * FROM `order` WHERE `user_id` = '" . $_SESSION['uid'] . "' ORDER BY `id` DESC LIMIT 1";
        $order_result = query($order_sql);
        $order = fetch($order_result, 2);

        foreach ($cart as $item) {
            $product = get_by_id('product', $item['product_id']);
            $product = fetch($product, 2);

            $order_detail = [
                'order_id' => $order['id'],
                'product_id' => $item['product_id'],
                'product_name' => $product['name'],
                'product_price' => $product['price'],
                'product_img' => $product['img'],
                'qty' => $item['qty'],
            ];

            insert('order_detail', $order_detail);
        }

        foreach ($cart as $item) {
            $product = get_by_id('product', $item['product_id']);
            $product = fetch($product, 2);

            $stock = $product['stock'] - $item['qty'];

            update_by_id('product', $item['product_id'], ['stock' => $stock]);
        }

        delete_by_condition('cart', ['user_id' => $_SESSION['uid']]);
        redirect_to('payment&order_id=' . $order['id']);
    }

    $total = 0;
    ?>
    <style>
        .substring {
            overflow: hidden;
            display: -webkit-box;
            line-clamp: 1;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .free-ship-badge {
            background: linear-gradient(90deg, #2e7d32 0%, #43a047 100%);
            color: #fff;
            border-radius: 10px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.93rem;
            margin-bottom: 1rem;
        }

        .free-ship-promo {
            background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
            border: 1.5px dashed #ffa000;
            border-radius: 10px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.93rem;
            margin-bottom: 1rem;
            color: #7b4f00;
        }

        .btn-check:checked+.btn>#delivery_type_1_hint {
            color: white !important;
        }
    </style>
    <div class="container mt-4">
        <div class="row">
            <p class="fs-2">รายละเอียด</p>
            <div class="col-12">
                <form action="" method="post">
                    <div class="row">
                        <div class="col-12 col-lg-8 mb-3">
                            <div class="row row-cols-1">
                                <div class="col mb-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <p class="fw-bold mb-3">ที่อยู่จัดส่ง</p>
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="firstname" id="firstname"
                                                                class="form-control" placeholder="ชื่อจริง*" maxlength="120"
                                                                required value="<?php echo $user['firstname'] ?>">
                                                            <label for="firstname">ชื่อจริง<span
                                                                    class="text-danger">*</span></label>

                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" name="lastname" id="lastname"
                                                                class="form-control" placeholder="นามสกุล*" maxlength="120"
                                                                required value="<?php echo $user['lastname'] ?>">
                                                            <label for="lastname">นามสกุล<span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input type="tel" name="phone" id="phone" class="form-control"
                                                        placeholder="เบอร์โทรศัพท์*" maxlength="255" required
                                                        value="<?php echo $user['phone'] ?>">
                                                    <label for="phone">เบอร์โทรศัพท์<span
                                                            class="text-danger">*</span></label>

                                                </div>
                                                <div class="form-floating mb-3">
                                                    <textarea name="address" id="address" cols="30" rows="3"
                                                        style="height: 150px;" class="form-control mb-3"
                                                        placeholder="ที่อยู่*"
                                                        required><?php echo $user['address'] ?></textarea>
                                                    <label for="address">ที่อยู่<span class="text-danger">*</span></label>
                                                    <div class="d-flex align-items-center gap-2 px-1"
                                                        style="font-size:0.83rem; color:#555;">
                                                        <i class="bi bi-info-circle-fill text-primary mt-1"
                                                            style="flex-shrink:0;"></i>
                                                        <span>การแก้ไขที่อยู่ในหน้านี้มีผลเฉพาะคำสั่งซื้อนี้เท่านั้น
                                                            <strong>ไม่กระทบที่อยู่ที่บันทึกไว้ในโปรไฟล์ของคุณ</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col mb-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <p class="fw-bold mb-3">วิธีการจัดส่ง<span class="text-danger">*</span></p>
                                            <div class="row row-cols-2 row-cols-lg-auto">
                                                <div class="col">
                                                    <input class="btn-check" type="radio" name="delivery_type"
                                                        id="delivery_type_1" value="1" onclick="update_totals(50);"
                                                        required>
                                                    <label class="btn btn-outline-primary py-4 w-100" for="delivery_type_1">
                                                        จัดส่งปกติ
                                                        <small id="delivery_type_1_hint"
                                                            class="text-success fw-bold"></small>
                                                    </label>
                                                </div>
                                                <div class="col">
                                                    <input class="btn-check" type="radio" name="delivery_type"
                                                        id="delivery_type_2" value="2" onclick="update_totals(0);" required>
                                                    <label class="btn btn-outline-primary py-4 w-100"
                                                        for="delivery_type_2">รับเองที่ร้าน</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div id="free-ship-banner"></div>
                            <div class="d-flex gap-2 justify-content-between mb-2">
                                <span class="fw-bold">สรุปรายการสั่งซื้อสินค้า (<?php echo number_format($cart_count) ?>
                                    รายการ)</span>
                                <a href="?page=cart" class="text-decoration-none text-muted small">แก้ไข <i
                                        class="bi bi-pencil"></i></a>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <?php
                                    foreach ($cart as $item) {
                                        $product = get_by_id('product', $item['product_id']);
                                        $product = fetch($product, 2);
                                        $total += $product['price'] * $item['qty'];
                                        ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between gap-3">
                                                <span class="substring fw-bold"><?php echo $product['name'] ?></span>
                                                <span
                                                    class="text-danger fw-bold">฿<?php echo number_format($product['price'] * $item['qty'], 2) ?></span>
                                            </div>
                                            <span class="small">จำนวน <?php echo number_format($item['qty']) ?></span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-0 text-muted">ค่าจัดส่ง :</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 text-end fw-bold">฿<span id="delivery_fee"></span></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-0 text-muted">ราคาก่อนภาษี :</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 text-end fw-bold">฿<span id="price_before_tax"></span></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-0 text-muted">ภาษี VAT 7% :</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 text-end fw-bold">฿<span id="vat"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row fw-bold mb-3">
                                        <div class="col-6">
                                            <p class="mb-0">ยอดรวมสุทธิ :</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 fs-5 text-end fw-bold">฿<span id="net_total"></p>
                                        </div>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="Privacy" id="Privacy" class="form-check-input"
                                            value="Y" required>
                                        <label for="Privacy" class="form-check-label">คุณยอมรับ <a
                                                href="?page=tos-and-privacy#privacy" target="_blank">นโยบายส่วนตัว</a> และ
                                            <a href="?page=tos-and-privacy#terms"
                                                target="_blank">ข้อตกลงในการใช้บริการ</a></label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">ดำเนินการชำระเงิน</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function format_money(amount) {
            return amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function update_free_ship_ui(total) {
            const banner = document.getElementById('free-ship-banner');
            const hint = document.getElementById('delivery_type_1_hint');
            const remaining = Math.max(0, 500 - total);

            if (remaining <= 0) {
                banner.innerHTML = `
                    <div class="free-ship-badge">
                        <div>
                            <div class="fw-bold">ยินดีด้วย! คุณได้รับ <u>จัดส่งฟรี</u></div>
                            <div style="font-size:0.82rem; opacity:0.9;">ยอดสั่งซื้อของคุณถึง ฿500 แล้ว</div>
                        </div>
                    </div>`;
                if (hint) {
                    hint.innerText = 'ฟรี';
                }
            } else {
                banner.innerHTML = `
                    <div class="free-ship-promo">
                        <div>
                            <div class="fw-bold">ซื้อเพิ่มอีก <span class="text-danger">฿${format_money(remaining)}</span> ส่งฟรี!</div>
                            <div style="font-size:0.82rem;">สั่งครบ ฿500 ขึ้นไป ยกเว้นค่าจัดส่ง (ปกติ ฿50)</div>
                        </div>
                    </div>`;
            }
        }

        function update_totals(amount) {
            const delivery_fee = document.getElementById('delivery_fee');
            const price_before_tax = document.getElementById('price_before_tax');
            const vat = document.getElementById('vat');
            const net_total = document.getElementById('net_total');

            const total = <?php echo $total ?>;
            let delivery_fee_2 = amount;

            if (total >= 500) {
                delivery_fee_2 = 0;
            }

            delivery_fee.innerText = format_money(delivery_fee_2);
            price_before_tax.innerText = format_money((total + delivery_fee_2) * (100 / 107));
            vat.innerText = format_money((total + delivery_fee_2) * (7 / 107));
            net_total.innerText = format_money(total + delivery_fee_2);

            update_free_ship_ui(total);
        }

        update_totals(0);
    </script>
    <?php
} else {
    redirect_to('home');
}
