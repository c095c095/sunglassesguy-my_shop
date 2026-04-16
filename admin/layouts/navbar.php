<header class="py-2 text-bg-dark hide-print" data-bs-theme="dark">
    <div class="container-fluid d-flex gap-3 align-items-center justify-content-end">
        <div class="dropdown">
            <a href="#" class="link-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($_SESSION['name']); ?>
            </a>
            <ul class="dropdown-menu text-small shadow">
                <li><a class="dropdown-item" href="?page=profile">บัญชีของฉัน</a></li>
                <li><a class="dropdown-item" href="?page=logout">ออกจากระบบ</a></li>
            </ul>
        </div>
    </div>
</header>
