<?php

$result_product_type = get_all('product_type');
$product_type = fetch($result_product_type);

$result_banners = get_by_condition('banner', ['status' => 1], 'sort', 'ASC');
$banners = fetch($result_banners);

?>
<style>
    .bd-placeholder-img {
        font-size: 1.125rem;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
        text-anchor: middle;
    }

    .bd-placeholder-img-lg {
        font-size: calc(1.475rem + 2.7vw);
    }

    @media (min-width: 1200px) {
        .bd-placeholder-img-lg {
            font-size: 3.5rem;
        }
    }

    .swiper-slide {
        display: flex;
        justify-content: center;
        align-items: center;
    }

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

<?php
if (count($banners) > 0) {
?>
    <div id="carouselExample" class="carousel slide">
        <div class="carousel-inner">
            <?php
        
            foreach ($banners as $index => $banner) {
            ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="upload/banner/<?php echo $banner['img']; ?>" class="d-block w-100" alt="Banner" style="object-fit: cover; height: 400px;" onerror="this.onerror=null; this.src='assets/images/404_banner.png';">
                </div>
            <?php
            }
        ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>
<?php
}
?>

<div class="container mt-4">
    <div class="swiper">
        <div class="swiper-wrapper align-items-center">
            <?php
            foreach ($product_type as $type) {
            ?>
                <div class="swiper-slide">
                    <a href="?page=products&type_id=<?php echo $type['id'] ?>" class="text-decoration-none text-dark link-primary text-center">
                        <img src="upload/type/<?php echo $type['img'] ?>" style="width: 7.5rem; height: 7.5rem; object-fit: cover;" alt="<?php echo $type['name'] ?>" onerror="this.onerror=null; this.src='assets/images/404.webp';">
                        <p class="h5 mt-2"><?php echo $type['name'] ?></p>
                    </a>
                </div>
            <?php
            }
            ?>
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>
<hr class="mb-0" style="margin-top: 7.5rem;">
<div class="container-fluid bg-white">
    <div class="row row-cols-2 row-cols-lg-4">
        <div class="col text-center">
            <i class="bi bi-truck display-2 text-primary"></i>
            <p class="fs-5 mt-3 text-muted">จัดส่งฟรีสำหรับคำสั่งซื้อมากกว่า ฿500</p>
        </div>
        <div class="col text-center">
            <i class="bi bi-check-circle display-2 text-primary"></i>
            <p class="fs-5 mt-3 text-muted">สินค้าคุณภาพและเป็นสินค้าแท้</p>
        </div>
        <div class="col text-center">
            <i class="bi bi-credit-card display-2 text-primary"></i>
            <p class="fs-5 mt-3 text-muted">การชำระเงินปลอดภัย 100%</p>
        </div>
        <div class="col text-center">
            <i class="bi bi-wechat display-2 text-primary"></i>
            <p class="fs-5 mt-3 text-muted">บริการลูกค้าตลอด 24 ชม.</p>
        </div>
    </div>
</div>
<hr class="mt-0" style="margin-bottom: 7.5rem;">
<div class="container">
    <p class="h1">สินค้ายอดนิยม</p>
    <div class="row mt-5">
        <div class="col-12 col-md-4 col-lg-3">
            <div class="nav flex-column nav-pills me-3" role="tablist">
                <?php
                foreach ($product_type as $type) {
                ?>
                    <button class="nav-link text-start text-truncate <?php echo $type === reset($product_type) ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#v-pills-<?php echo $type['id'] ?>" type="button" role="tab" style="padding-top: .75rem; padding-bottom: .75rem;">
                        <?php echo $type['name'] ?>
                    </button>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-8 col-lg-9">
            <div class="tab-content">
                <?php
                foreach ($product_type as $type) {
                ?>
                    <div class="tab-pane fade <?php echo $type === reset($product_type) ? 'show active' : '' ?>" id="v-pills-<?php echo $type['id'] ?>" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="h5 fw-bold mb-0"><?php echo $type['name'] ?></p>
                            <a href="?page=products&type_id=<?php echo $type['id'] ?>" class="text-muted text-decoration-none">ดูทั้งหมด <i class="bi bi-caret-right-fill"></i></a>
                        </div>
                        <div class="row row-cols-2 row-cols-lg-3">
                            <?php
                            $sql_product = "SELECT p.*, COALESCE(SUM(od.qty), 0) AS total_sold FROM product p LEFT JOIN order_detail od ON p.id = od.product_id WHERE p.type_id = " . $type['id'] . " GROUP BY p.id ORDER BY total_sold DESC, p.id DESC LIMIT 6"; // Thx to the Copilot bro i don't even know what is this
                            $result_products = query($sql_product);
                            $products = fetch($result_products);
                            foreach ($products as $product) {
                                if ($product['stock'] > '0') {
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
                            }
                            ?>
                        </div>

                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>