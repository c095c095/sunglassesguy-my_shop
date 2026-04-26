<?php
// รายงานสิทธิ์ - User Roles/Permissions Report

// Permission definitions
$permission_types = [
    ['value' => '0', 'color' => 'secondary', 'label' => 'ระงับการใช้งาน', 'icon' => 'bi-slash-circle'],
    ['value' => '1', 'color' => 'primary', 'label' => 'ผู้ใช้ทั่วไป', 'icon' => 'bi-person'],
    ['value' => '2', 'color' => 'danger', 'label' => 'ผู้ดูแลระบบ', 'icon' => 'bi-shield-check'],
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
                          TRIM(CONCAT(firstname, ' ', lastname)) LIKE '%$search%')";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query all users with filter
$result = query("SELECT * FROM user $where_clause ORDER BY permission DESC, id ASC");
$users = fetch($result);
$total_filtered = count($users);

// Count by permission (always from full dataset for summary cards)
$total_all = get_count('user');
$total_suspended = get_count('user', ['permission' => '0']);
$total_regular = get_count('user', ['permission' => '1']);
$total_admin = get_count('user', ['permission' => '2']);

// Pagination
$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$per_page = 15;
$total_pages = ceil($total_filtered / $per_page);
$offset = ($current_page - 1) * $per_page;
$paged_users = array_slice($users, $offset, $per_page);

// Print date
$print_date = date('d/m/Y H:i:s');
?>

<style>
    /* ===== Report Header ===== */
    .report-header {
        background: linear-gradient(135deg, #4a1a6b 0%, #6f42c1 50%, #8b5cf6 100%);
        border-radius: 16px;
        color: white;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .report-header::after {
        content: '';
        position: absolute;
        top: -30%;
        right: -5%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }

    .report-header h2 {
        font-weight: 800;
        font-size: 1.6rem;
        margin-bottom: 0.25rem;
    }

    .report-header .subtitle {
        opacity: 0.85;
        font-size: 0.9rem;
    }

    /* ===== Summary Cards ===== */
    .summary-card {
        border: none;
        border-radius: 14px;
        transition: all 0.3s ease;
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

    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
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
        color: white;
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
        background-color: #f8f0ff;
    }

    .permission-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.75em;
        border-radius: 8px;
        font-weight: 600;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: white;
    }

    /* ===== Action Buttons ===== */
    .btn-print {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .btn-print:hover {
        background: linear-gradient(135deg, #16213e, #0f3460);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-back {
        border-radius: 10px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* ===== Print Styles ===== */
    @media print {
        .report-print-area,
        .report-print-area * {
            visibility: visible !important;
        }

        .report-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .report-header {
            background: #6f42c1 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .summary-card::before {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            display: none !important;
        }

        .report-table thead th {
            background-color: #f8f9fc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .permission-badge {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .summary-icon {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
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
        background-color: #6f42c1;
        color: white;
    }

    .report-pagination .page-link:hover {
        background-color: #f0e6ff;
        color: #6f42c1;
    }
</style>

<div class="container-fluid mb-3 report-print-area">
    <!-- Report Header -->
    <div class="report-header shadow">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-shield-lock me-3" style="font-size: 2.2rem; opacity: 0.9;"></i>
                <div>
                    <h2 class="mb-0">รายงานสิทธิ์</h2>
                    <span class="subtitle">
                        <i class="bi bi-calendar3 me-1"></i>
                        วันที่พิมพ์: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?>
                    </span>
                </div>
            </div>
            <div class="d-flex gap-2 no-print">
                <a href="?page=reports" class="btn btn-back btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i>กลับ
                </a>
                <button onclick="window.print();" class="btn btn-print">
                    <i class="bi bi-printer me-1"></i>พิมพ์รายงาน
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm" style="--card-color: #6f42c1;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_all); ?></div>
                            <div class="label">ผู้ใช้ทั้งหมด</div>
                        </div>
                        <div class="summary-icon" style="background: linear-gradient(135deg, #6f42c1, #8b5cf6);">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm" style="--card-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_admin); ?></div>
                            <div class="label">ผู้ดูแลระบบ</div>
                        </div>
                        <div class="summary-icon" style="background: linear-gradient(135deg, #dc3545, #e35d6a);">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm" style="--card-color: #0d6efd;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_regular); ?></div>
                            <div class="label">ผู้ใช้ทั่วไป</div>
                        </div>
                        <div class="summary-icon" style="background: linear-gradient(135deg, #0d6efd, #3d8bfd);">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card summary-card shadow-sm" style="--card-color: #6c757d;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="count text-dark"><?php echo number_format($total_suspended); ?></div>
                            <div class="label">ระงับการใช้งาน</div>
                        </div>
                        <div class="summary-icon" style="background: linear-gradient(135deg, #6c757d, #8c959d);">
                            <i class="bi bi-slash-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card filter-card shadow-sm mb-4 no-print">
        <div class="card-body py-3">
            <form method="GET" action="">
                <input type="hidden" name="page" value="report-user-roles">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </label>
                        <input type="text" class="form-control" name="search"
                            placeholder="ค้นหาชื่อผู้ใช้, อีเมล, ชื่อ-นามสกุล..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-funnel me-1"></i>กรองตามสิทธิ์
                        </label>
                        <select class="form-select" name="permission">
                            <option value="">ทุกระดับสิทธิ์</option>
                            <?php foreach ($permission_types as $perm): ?>
                                <option value="<?php echo $perm['value']; ?>"
                                    <?php echo $filter_permission === $perm['value'] ? 'selected' : ''; ?>>
                                    <?php echo $perm['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark flex-grow-1" style="border-radius: 10px;">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </button>
                        <a href="?page=report-user-roles" class="btn btn-outline-secondary" style="border-radius: 10px;">
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
                <i class="bi bi-table me-2 text-muted"></i>
                รายละเอียดข้อมูลสิทธิ์ผู้ใช้
            </h6>
            <span class="badge bg-light text-dark border px-3 py-2" style="font-size: 0.8rem;">
                ทั้งหมด <?php echo number_format($total_filtered); ?> รายการ
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
                                    $perm_icon = 'bi-question-circle';
                                    foreach ($permission_types as $pt) {
                                        if ($pt['value'] === $user['permission']) {
                                            $perm_color = $pt['color'];
                                            $perm_label = $pt['label'];
                                            $perm_icon = $pt['icon'];
                                            break;
                                        }
                                    }
                                    // Generate avatar color based on permission
                                    $avatar_colors = ['0' => '#6c757d', '1' => '#0d6efd', '2' => '#dc3545'];
                                    $avatar_bg = $avatar_colors[$user['permission']] ?? '#6c757d';
                                    $initials = mb_substr($user['firstname'], 0, 1);
                                ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?php echo $i++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="background: <?php echo $avatar_bg; ?>;">
                                                <?php echo $initials; ?>
                                            </div>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                    <td>
                                        <span class="text-muted">
                                            <i class="bi bi-envelope me-1"></i>
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <i class="bi bi-telephone me-1"></i>
                                            <?php echo htmlspecialchars($user['phone']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="permission-badge badge bg-<?php echo $perm_color; ?>">
                                            <i class="bi <?php echo $perm_icon; ?> me-1"></i>
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">ไม่พบข้อมูลผู้ใช้</p>
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
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 no-print">
                <div class="text-muted" style="font-size: 0.85rem;">
                    แสดง <?php echo count($paged_users); ?> จาก <?php echo $total_filtered; ?> รายการ
                    (หน้า <?php echo $current_page; ?>/<?php echo $total_pages; ?>)
                </div>
                <nav>
                    <ul class="pagination report-pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=report-user-roles&p=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($filter_permission); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=report-user-roles&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($filter_permission); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=report-user-roles&p=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($filter_permission); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Print Footer Info -->
    <div class="text-center mt-3 text-muted d-none d-print-block" style="font-size: 0.8rem;">
        <hr>
        <p class="mb-1">
            <strong><?php echo WEBSITE_NAME; ?></strong> — ระบบรายงานสิทธิ์ผู้ใช้
        </p>
        <p class="mb-0">พิมพ์เมื่อ: <?php echo format_datetime_thai(date('Y-m-d H:i:s'), 1); ?></p>
    </div>
</div>
