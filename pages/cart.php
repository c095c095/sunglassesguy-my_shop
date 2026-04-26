<?php

if (is_auth()) {
    $cart_result = get_by_condition('cart', ['user_id' => $_SESSION['uid']]);
    $cart = fetch($cart_result);
    $is_edit = false;

    foreach ($cart as $item) {
        $product_result = get_by_id('product', $item['product_id']);
        $product = fetch($product_result, 2);

        if (!$product) {
            delete_by_id('cart', $item['id']);
            $is_edit = true;
            continue;
        }

        if ($product['stock'] < $item['qty']) {
            update_by_id('cart', $item['id'], ['qty' => $product['stock']]);
            $is_edit = true;
        }

        if ($product['stock'] < 1) {
            delete_by_id('cart', $item['id']);
            $is_edit = true;
        }
    }

    if ($is_edit == true) {
        show_alert('สินค้าในตะกร้ามีการเปลี่ยนแปลง!');
    }

    $cart_result = get_by_condition('cart', ['user_id' => $_SESSION['uid']]);
    $cart = fetch($cart_result);
    $cart_count = get_num_rows($cart_result);
    $total = 0;

    ?>
    <style>
        .free-shipping-banner {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border: 1.5px dashed #4caf50;
            border-radius: 12px;
            padding: 14px 16px;
        }

        .free-shipping-achieved {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 1.5px solid #4caf50;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2e7d32;
            font-size: 0.92rem;
        }
    </style>
    <div class="container mt-4">
        <p class="fs-2">ตะกร้าสินค้า <?php if ($cart_count > 0) { ?> <span
                    class="text-danger fw-bold">(<?php echo number_format($cart_count) ?>)</span> <?php } ?></p>
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="row row-cols-1">
                    <?php
                    foreach ($cart as $item) {
                        $product_result = get_by_id('product', $item['product_id']);
                        $product = fetch($product_result, 2);
                        $total += $product['price'] * $item['qty'];
                        ?>
                        <div class="col mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3 col-lg-2">
                                            <a href="?page=product&id=<?php echo $product['id'] ?>">
                                                <img src="upload/product/<?php echo $product['img'] ?>" class="object-fit-cover"
                                                    style="width: 100px; height: 100px; max-width: 100%;"
                                                    onerror="this.onerror=null; this.src='assets/images/404.webp';" alt="">
                                            </a>
                                        </div>
                                        <div class="col-9 col-lg-10">
                                            <div class="d-flex flex-column justify-content-between h-100">
                                                <div class="d-flex gap-2 justify-content-between">
                                                    <a href="?page=product&id=<?php echo $product['id'] ?>"
                                                        class="mb-0 fw-bold text-dark text-decoration-none"><?php echo $product['name'] ?></a>
                                                    <form action="?page=cart-remove" method="post">
                                                        <input type="hidden" name="product_id"
                                                            value="<?php echo $product['id'] ?>">
                                                        <button type="submit" class="btn btn-sm">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex gap-2">
                                                        <span
                                                            class="text-muted">฿<?php echo number_format($product['price'], 2) ?></span>
                                                        <span class="text-muted">x</span>
                                                        <span
                                                            class="text-danger fw-bold">฿<?php echo number_format($product['price'] * $item['qty']) ?></span>
                                                    </div>
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <form action="?page=cart-decrease" method="post">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                <input type="hidden" name="product_id"
                                                                    value="<?php echo $product['id'] ?>">
                                                                <i class="bi bi-dash"></i>
                                                            </button>
                                                        </form>
                                                        <span><?php echo $item['qty'] ?></span>
                                                        <form action="?page=cart-increase" method="post">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary <?php if ($item['qty'] >= $product['stock']) {
                                                                echo "disabled";
                                                            } ?>">
                                                                <input type="hidden" name="product_id"
                                                                    value="<?php echo $product['id'] ?>">
                                                                <input type="hidden" name="qty" value="1">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }

                    if ($cart_count == 0) {
                        ?>
                        <div class="col mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-center mb-0">ไม่มีสินค้าในตะกร้า</p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <?php
                $free_shipping_threshold = 500;
                $remaining = max(0, $free_shipping_threshold - $total);
                $progress = min(100, ($total / $free_shipping_threshold) * 100);
                ?>
                <?php if ($cart_count > 0): ?>
                    <?php if ($remaining > 0): ?>
                        <div class="free-shipping-banner mb-3">
                            <div class="free-shipping-inner">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold" style="font-size:0.92rem;">ซื้อเพิ่มอีก <strong
                                            class="text-danger">฿<?php echo number_format($remaining, 2); ?></strong> รับสิทธิ์
                                        <strong>ส่งฟรี!</strong></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="free-shipping-achieved mb-3">
                            <span class="fw-semibold">ยินดีด้วย! คุณได้รับสิทธิ์ <strong>จัดส่งฟรี</strong> แล้ว!</span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-0">ยอดรวม</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-0 text-end">฿<?php echo number_format($total, 2); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-0">ยอดรวมสุทธิ</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-0 text-end">฿<?php echo number_format($total, 2); ?></p>
                            </div>
                        </div>
                        <div class="mt-3 p-2 rounded" style="background:#f8f9fa; font-size:0.82rem; color:#555; ">
                            สั่งครบ <strong>฿500</strong> ขึ้นไป <span class="fw-bold">ส่งฟรี</span>
                            (จัดส่งปกติ ฿50)
                        </div>
                        <a href="?page=confirm" class="btn btn-primary w-100 mt-3 <?php if ($cart_count < 1) {
                            echo "disabled";
                        } ?>">ดำเนินการต่อ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    redirect_to('home');
}
