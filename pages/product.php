<?php

$product_id = 0;

if (@$_GET['id'] != '') {
    $product_id = $_GET['id'];
}

$product_result = get_by_id('product', $product_id);

if (get_num_rows($product_result) == 1) {
    $product = fetch($product_result, 2);

    $type_result = get_by_id('product_type', $product['type_id']);
    $type = fetch($type_result, 2);

    $related_product_sql = "SELECT * FROM product WHERE type_id = '" . $type['id'] . "' AND id != $product_id ORDER BY RAND() LIMIT 4";
    $related_product_result = query($related_product_sql);
    $related_product = fetch($related_product_result);

    $cart_result = get_by_condition('cart', [
        'product_id' => $product_id,
        'user_id' => $_SESSION['uid']
    ]);
    $cart = fetch($cart_result, 2);
?>
    <style>
        .adjust-align {
            justify-content: center;

            @media (min-width: 992px) {
                & {
                    justify-content: start;
                }
            }
        }

        .auto-width {
            width: 100%;

            @media (min-width: 992px) {
                & {
                    width: auto;
                }
            }
        }
    </style>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12 col-lg-6 mb-3 text-center">
                <img src="upload/product/<?php echo $product['img'] ?>" class="img-fluid rounded shadow-sm" style="min-width: 25vw;" onerror="this.onerror=null; this.src='assets/images/404.webp';" alt="<?php echo $product['detail'] ?>">
            </div>
            <div class="col-12 col-lg-6 mb-3">
                <?php
                if ($product['stock'] > 0) {
                ?>
                    <span class="badge text-bg-success mb-3">มีสินค้า</span>
                <?php
                } else {
                ?>
                    <span class="badge text-bg-danger mb-3">สินค้าหมด</span>
                <?php
                }
                ?>
                <p class="fs-4 fw-bold"><?php echo $product['name'] ?></p>
                <span class="text-muted">ประเภท: <?php echo $type['name'] ?></span>
                <p class="fs-2 fw-bold my-3">฿<?php echo number_format($product['price'], 2) ?></p>
                <div class="d-flex gap-3 align-items-center mb-3 adjust-align">
                    <button type="button" class="btn btn-outline-primary" id="decrease_qty">
                        <i class="bi bi-dash"></i>
                    </button>
                    <span class="fw-bold fs-5">1</span>
                    <button type="button" class="btn btn-outline-primary" id="increase_qty">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <?php
                if (@$_SESSION['uid'] != '') {
                ?>
                    <form action="?page=cart-increase" method="post">
                        <input type="hidden" name="product_id" id="product_id" value="<?php echo $product['id'] ?>">
                        <input type="hidden" name="qty" id="product_qty" value="1">
                        <button type="submit" class="btn btn-primary py-2 px-3 auto-width <?php if ($product['stock'] <= 0) {
                                                                                                echo 'disabled';
                                                                                            } ?>">หยิบใส่ตะกร้า</button>
                    </form>
                <?php
                } else {
                ?>
                    <a href="?page=login" class="btn btn-primary py-2 px-3 auto-width">หยิบใส่ตะกร้า</a>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="mt-3 mb-4" style="border-bottom: 1px solid rgb(218, 225, 231);">
            <span class="fs-5" style="border-bottom: 2px solid #0d6efd;">รายละเอียด</span>
        </div>
        <pre class="mb-5"><?php echo $product['detail'] ?></pre>
        <p class="fw-bold">จากหมวดหมู่เดียวกัน</p>
        <div class="row row-cols-2 row-cols-lg-4">
            <?php
            foreach ($related_product as $r_product) {
            ?>
                <div class="col mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <a href="?page=product&id=<?php echo $r_product['id']; ?>" class="text-dark text-decoration-none">
                            <img src="upload/product/<?php echo $r_product['img'] ?>" onerror="this.onerror=null; this.src='assets/images/404.webp';" alt="<?php echo $r_product['name'] ?>" class="object-fit-cover card-img-top w-100" style="max-height: 17rem; height: auto;">
                        </a>
                        <div class="card-body">
                            <a href="?page=product&id=<?php echo $r_product['id']; ?>" class="text-dark text-decoration-none">
                                <p class="h5 mt-3"><?php echo $r_product['name'] ?></p>
                            </a>
                            <p class="h6 fw-bold text-danger"><small>฿</small><?php echo number_format($r_product['price'], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <script>
        const product_stock = <?php echo $product['stock'] - @$cart['qty'] ?>;
        const increase_qty = document.getElementById('increase_qty');
        const decrease_qty = document.getElementById('decrease_qty');

        increase_qty.addEventListener('click', () => {
            if (parseInt(increase_qty.previousElementSibling.textContent) < product_stock) {
                increase_qty.previousElementSibling.textContent = parseInt(increase_qty.previousElementSibling.textContent) + 1;

                document.getElementById('product_qty').value = parseInt(increase_qty.previousElementSibling.textContent);

                if (parseInt(increase_qty.previousElementSibling.textContent) == product_stock) {
                    increase_qty.classList.add('disabled');
                }
            }
        });

        decrease_qty.addEventListener('click', () => {
            if (parseInt(decrease_qty.nextElementSibling.textContent) > 1) {
                decrease_qty.nextElementSibling.textContent = parseInt(decrease_qty.nextElementSibling.textContent) - 1;

                document.getElementById('product_qty').value = parseInt(decrease_qty.nextElementSibling.textContent);

                if (parseInt(decrease_qty.nextElementSibling.textContent) < product_stock) {
                    increase_qty.classList.remove('disabled');
                }
            }
        });
    </script>
<?php
} else {
?>
    <div class="text-center" style="margin-top: 5rem; margin-bottom: 5rem;">
        <p class="h1 fw-bold text-muted">ไม่พบสินค้า</p>
        <a href="?page=home" class="link-primary">กลับหน้าแรก</a>
    </div>
<?php
}
