<?php

$types_result = get_all('product_type');
$types = fetch($types_result);
$type_id = $types[0]['id'];

if (@$_GET['type_id'] != '') {
    $type_id = $_GET['type_id'];
}

$product_result = get_by_condition('product', ['type_id' => $type_id]);
$products = fetch($product_result);

?>
<style>
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background-color: white;
        color: #0d6efd;
        font-weight: bold;
        box-shadow: rgba(0, 0, 0, 0.06) 0px 3px 5px -1px, rgba(0, 0, 0, 0.043) 0px 5px 8px 0px, rgba(0, 0, 0, 0.035) 0px 1px 14px 0px !important;
    }

    .nav-pills .nav-link:hover {
        box-shadow: rgba(0, 0, 0, 0.05) 0px 6px 26px;
    }
</style>
<div class="container mt-4">
    <div class="row">
        <div class="col-12 col-md-4 col-lg-3 mb-3">
            <div class="nav flex-column nav-pills me-3" role="tablist">
                <?php
                foreach ($types as $type) {
                ?>
                    <a href="?page=products&type_id=<?php echo $type['id'] ?>"
                        class="nav-link text-start text-truncate <?php if ($type['id'] == $type_id) {
                                                                        echo 'active';
                                                                    } ?>"
                        style="padding-top: .75rem; padding-bottom: .75rem;">
                        <?php echo $type['name'] ?>
                    </a>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-8 col-lg-9">
            <?php
            $type_result = get_by_id('product_type', $type_id);
            $type = fetch($type_result, 2);
            ?>
            <p class="h3 fw-bold mb-3"><?php echo $type['name'] ?></p>
            <div class="row row-cols-2 row-cols-lg-3">
                <?php
                foreach ($products as $product) {
                ?>
                    <div class="col">
                        <div class="card hover h-100">
                            <a href="?page=product&id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none">
                                <img src="upload/product/<?php echo $product['img'] ?>" onerror="this.onerror=null; this.src='assets/images/404.webp';" alt="<?php echo $product['name'] ?>" class="object-fit-cover card-img-top w-100" style="max-height: 17rem; height: auto;">
                            </a>
                            <div class="card-body">
                                <a href="?page=product&id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none">
                                    <p class="h5 mt-3"><?php echo $product['name'] ?></p>
                                </a>
                                <p class="h6 fw-bold text-danger"><small>฿</small><?php echo number_format($product['price'], 2) ?></p>
                                <?php
                                if ($product['stock'] > 0) {
                                    if (@$_SESSION['uid'] != '') {
                                ?>
                                        <form action="?page=cart-increase" method="post">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id'] ?>">
                                            <input type="hidden" name="qty" value="1">
                                            <button type="submit" class="btn btn-outline-primary mt-3">หยิบใส่ตะกร้า</button>
                                        </form>
                                    <?php
                                    } else {
                                    ?>
                                        <a href="?page=login" class="btn btn-outline-primary mt-3">หยิบใส่ตะกร้า</a>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <span class="text-danger d-block small">สินค้าหมด</span>
                                    <button type="button" class="btn btn-outline-primary mt-3" disabled>หยิบใส่ตะกร้า</button>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>