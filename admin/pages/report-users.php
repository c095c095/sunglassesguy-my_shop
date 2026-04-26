<?php
// Permission definitions
$permission_types = [
    ['value' => '0', 'color' => 'secondary', 'label' => 'ระงับการใช้งาน'],
    ['value' => '1', 'color' => 'primary', 'label' => 'ผู้ใช้ทั่วไป'],
    ['value' => '2', 'color' => 'danger', 'label' => 'ผู้ดูแลระบบ'],
];

// Get filter
$filter_permission = isset($_GET['permission']) ? $_GET['permission'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_conditions = [];
if ($filter_permission !== '') {
    $where_conditions[] = "permission = '$filter_permission'";
}
if (!empty($search)) {
    $where_conditions[] = "(username LIKE '%$search%' OR 
                          email LIKE '%$search%' OR 
                          firstname LIKE '%$search%' OR 
                          lastname LIKE '%$search%' OR 
                          phone LIKE '%$search%' OR
                          TRIM(CONCAT(firstname, ' ', lastname)) LIKE '%$search%')";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query all users with filter
$result = query("SELECT * FROM user $where_clause ORDER BY id DESC");
$users = fetch($result);
$total_filtered = count($users);

// Count summary (always from full dataset for summary cards)
$total_all = get_count('user');
$total_suspended = get_count('user', ['permission' => '0']);
$total_regular = get_count('user', ['permission' => '1']);
$total_admin = get_count('user', ['permission' => '2']);

// Count by month (สมาชิกใหม่เดือนนี้)
$current_month = date('Y-m');
$result_new = query("SELECT COUNT(*) as total FROM user WHERE DATE_FORMAT(created_at, '%Y-%m') = '$current_month'");
$row_new = fetch($result_new, 2);
$total_new_this_month = (int) $row_new['total'];

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_users = array_slice($users, $offset, $per_page);
?>

<style>
    /* ===== Summary Cards ===== */
    .summary-card {
        border: none;
        position: relative;
        overflow: hidden;
    }

    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color);
    }

    .summary-card .card-body {
        padding: 1.25rem;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .summary-card .count {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .summary-card .label {
        font-size: 0.82rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* ===== Filter Card ===== */
    .filter-card {
        border: none;
        border-radius: 14px;
    }

    /* ===== Table ===== */
    .report-table-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
    }

    .report-table thead th {
        background-color: #f8f9fc;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding: 0.85rem 0.75rem;
        white-space: nowrap;
    }

    .report-table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .report-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .report-table tbody tr:hover {
        background-color: #f0f4ff;
    }

    .permission-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.75em;
        border-radius: 8px;
        font-weight: 600;
    }

    /* ===== Pagination ===== */
    .report-pagination .page-link {
        border-radius: 8px;
        margin: 0 2px;
        border: none;
        color: #495057;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .report-pagination .page-item.active .page-link {
        background-color: #0d6efd;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #e8f0fe;
        color: #0d6efd;
    }

    @media print {
        body {
            background: white;
        }

        .report-paper {
            min-width: 100vw;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }
    }
</style>

<div class="container-fluid mb-3 hide-print">
    <!-- Report Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-0">
            <h2>รายงานสมาชิก</h2>
            <button type="button" class="btn btn-dark" onclick="window.print();">
                <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
            </button>
        </div>
        <span class="subtitle mb-0">วันที่พิมพ์:
            <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
        </span>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class=" count text-dark">
                                <?php echo number_format($total_all); ?>
                            </div>
                            <div class="label">สมาชิกทั้งหมด</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class=" count text-dark">
                                <?php echo number_format($total_regular); ?>
                            </div>
                            <div class="label">ผู้ใช้ทั่วไป</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class=" count text-dark">
                                <?php echo number_format($total_admin); ?>
                            </div>
                            <div class="label">ผู้ดูแลระบบ</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class=" count text-dark">
                                <?php echo number_format($total_new_this_month); ?>
                            </div>
                            <div class="label">สมัครเดือนนี้</div>
                        </div>
                        <div class="summary-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="">
                <input type="hidden" name="page" value="report-users">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            ค้นหา
                        </label>
                        <input type="text" class="form-control" name="search"
                            placeholder="ค้นหาชื่อผู้ใช้, อีเมล, ชื่อ-นามสกุล, เบอร์โทร..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            กรองตามสิทธิ์
                        </label>
                        <select class="form-select" name="permission">
                            <option value="">ทุกระดับสิทธิ์</option>
                            <?php foreach ($permission_types as $perm): ?>
                                <option value="<?php echo $perm['value']; ?>" <?php echo $filter_permission === $perm['value'] ? 'selected' : ''; ?>>
                                    <?php echo $perm['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius: 10px;">
                            ค้นหา
                        </button>
                        <a href="?page=report-users" class="btn btn-outline-secondary" style="border-radius: 10px;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card report-table-card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                รายละเอียดข้อมูลสมาชิก
            </h6>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.8rem;">
                ทั้งหมด
                <?php echo number_format($total_filtered); ?> รายการ
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table report-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">ลำดับ</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>อีเมล</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>ที่อยู่</th>
                            <th class="text-center">ระดับสิทธิ์</th>
                            <th class="text-center">วันที่สมัคร</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paged_users) > 0): ?>
                            <?php $i = $offset + 1; ?>
                            <?php foreach ($paged_users as $user): ?>
                                <?php
                                $perm_color = 'secondary';
                                $perm_label = 'ไม่ทราบ';
                                foreach ($permission_types as $pt) {
                                    if ($pt['value'] === $user['permission']) {
                                        $perm_color = $pt['color'];
                                        $perm_label = $pt['label'];
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted">
                                        <?php echo $i++; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo htmlspecialchars($user['phone']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted"
                                            style="max-width: 200px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars($user['address']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="permission-badge badge bg-<?php echo $perm_color; ?>">
                                            <?php echo $perm_label; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลสมาชิก</p>
                                        <small>ลองเปลี่ยนเงื่อนไขการค้นหา</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <!-- Pagination -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="text-muted" style="font-size: 0.85rem;">
                    แสดง
                    <?php echo count($paged_users); ?> จาก
                    <?php echo $total_filtered; ?> รายการ
                    (หน้า
                    <?php echo $current_page; ?>/
                    <?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-users&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($filter_permission); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=report-users&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($filter_permission); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=report-users&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($filter_permission); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="show-print">
    <div class="report-paper">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <h1 style="font-weight: bold;">รายงานสมาชิก</h1>
                <h1 style="font-weight: bold;"><?= WEBSITE_NAME ?></h1>
            </div>
            <hr style="margin: 0;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between; color: rgba(33, 37, 41, 0.75);  ">
                <p>พิมพ์โดย: <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?></p>
                <p>พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
            </div>
        </div>
        <p style="font-weight: bold; margin: 0;">ข้อมูลสมาชิก</p>
        <div style="margin-left: 20px;">
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>สมาชิกทั้งหมด</div>
                <div>
                    <?= number_format($total_all) ?>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>ผู้ใช้ทั่วไป</div>
                <div>
                    <?= number_format($total_regular) ?>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>ผู้ดูแลระบบ</div>
                <div>
                    <?= number_format($total_admin) ?>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>ระงับการใช้งาน</div>
                <div>
                    <?= number_format($total_suspended) ?>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-0"
                style="display: flex; justify-content: between">
                <div>สมัครเดือนนี้</div>
                <div>
                    <?= number_format($total_new_this_month) ?>
                </div>
            </div>
        </div>
        <hr class="my-3">
        <p style="font-weight: bold; margin: 0;">รายละเอียดสมาชิก</p>
        <div style="margin-left: 20px">
            <?php
            $j = 1;
            foreach ($users as $user):
                $role = 'ไม่ทราบ';
                foreach ($permission_types as $pt) {
                    if ($pt['value'] === $user['permission']) {
                        $role = $pt['label'];
                        break;
                    }
                }
                ?>
                <div style="font-weight: bold;">
                    <?php echo $j++ . '. ' . $user['firstname'] . ' ' . $user['lastname'] ?>
                </div>
                <div style="padding-left: 20px;">
                    <div>ชื่อผู้ใช้: <?php echo $user['username'] ?></div>
                    <div>ระดับสิทธิ์:
                        <?php echo $role ?>
                    </div>
                    <div>อีเมล: <?php echo $user['email'] ?></div>
                    <div>เบอร์โทรศัพท์: <?php echo $user['phone'] ?></div>
                    <div>ที่อยู่: <?php echo $user['address'] ?></div>
                    <div>วันที่สมัคร: <?php echo format_date_thai($user['created_at'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <hr class="mt-3 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-0"
            style="display: flex; justify-content: between; color: rgba(33, 37, 41, 0.75);  ">
            <p>พิมพ์โดย:
                <?= @$_SESSION['firstname'] . ' ' . @$_SESSION['lastname'] ?>
            </p>
            <p>พิมพ์เมื่อ:
                <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
            </p>
        </div>
    </div>
</div>