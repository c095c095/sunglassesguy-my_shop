<?php

function is_active($page)
{
    if (is_array($page)) {
        return in_array(get_current_page(), $page) ? 'active' : '';
    }

    return get_current_page() === $page ? 'active' : '';
}

?>
<nav class="navbar navbar-expand-lg shadow-sm bg-primary" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand" href="?page=home"><?php echo WEBSITE_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active('home'); ?>" href="?page=home">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active('products'); ?>" href="?page=products">สินค้าทั้งหมด</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active('faq'); ?>" href="?page=faq">วิธีการสั่งซื้อ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo is_active('contact'); ?>" href="?page=contact">ติดต่อเรา</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_auth()): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative <?php echo is_active('cart'); ?>" href="?page=cart">
                            <i class="bi bi-basket3-fill"></i>
                            ตะกร้า
                            <?php
                            $nav_cart_count_result = get_by_condition('cart', ['user_id' => $_SESSION['uid']]);
                            $nav_cart_count = get_num_rows($nav_cart_count_result);

                            if ($nav_cart_count > 0) {
                            ?>
                                <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="top: 30%; left: 98%; padding-left: .250rem; padding-right: .250rem;"><?php echo number_format($nav_cart_count) ?></span>
                            <?php
                            }
                            ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle " role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?page=profile">บัญชีของฉัน</a></li>
                            <li><a class="dropdown-item" href="?page=order-history">ประวัติการสั่งซื้อ</a></li>
                            <li><a class="dropdown-item" href="?page=logout">ออกจากระบบ</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="?page=login" class="nav-link <?php echo is_active('login'); ?>">เข้าสู่ระบบ</a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=register" class="btn btn-light text-dark">สมัครสมาชิก</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>