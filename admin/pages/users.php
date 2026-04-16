<?php
function get_permission_status($permission) {
    switch ($permission) {
        case '0':
            return ['secondary', 'ระงับการใช้งาน'];
            break;
        case '1':
            return ['primary', 'ผู้ใช้ทั่วไป'];
            break;
        case '2':
            return ['danger', 'ผู้ดูแลระบบ'];
            break;
        default:
            return ['secondary', 'ไม่ทราบระดับ'];
            break;
    }
}

$permissions = [
    ['value' => '0', 'color' => 'secondary', 'label' => 'ระงับการใช้งาน'],
    ['value' => '1', 'color' => 'primary', 'label' => 'ผู้ใช้ทั่วไป'],
    ['value' => '2', 'color' => 'danger', 'label' => 'ผู้ดูแลระบบ']
];

// Change these lines at the top of the file
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 10; // Number of users per page
$offset = ($current_page - 1) * $per_page;

// Add these variables at the top of the file after the existing pagination variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$permission_filter = $_GET['permission'] ?? '';

// Modify the query to include search and filtering
$where_conditions = [];
if (!empty($search)) {
    $where_conditions[] = "(username LIKE '%".trim($search)."%' OR 
                          email LIKE '%".trim($search)."%' OR 
                          firstname LIKE '%".trim($search)."%' OR 
                          lastname LIKE '%".trim($search)."%' OR 
                          TRIM(CONCAT(firstname, ' ', lastname)) LIKE '%".trim($search)."%')";
}
if ($permission_filter !== '') {
    $where_conditions[] = "permission = '$permission_filter'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Update the query to include where clause
$result = query("SELECT * FROM user $where_clause ORDER BY id DESC");
$total_users = get_num_rows($result);
$users = array_slice(fetch($result), $offset, $per_page);

$total_pages = ceil($total_users / $per_page);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $check_username = get_by_condition('user', ['username' => $_POST['username']]);
            if (get_num_rows($check_username) > 0) {
                show_alert('ชื่อผู้ใช้นี้ถูกใช้งานแล้ว');
                break;
            }

            $check_email = get_by_condition('user', ['email' => $_POST['email']]);
            if (get_num_rows($check_email) > 0) {
                show_alert('อีเมลนี้ถูกใช้งานแล้ว');
                break;
            }

            if ($_POST['password'] !== $_POST['confirm_password']) {
                show_alert('รหัสผ่านไม่ตรงกัน');
                break;
            }

            $userData = [
                'username' => $_POST['username'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'email' => $_POST['email'],
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address'],
                'permission' => $_POST['permission']
            ];

            if (insert('user', $userData)) {
                show_alert('เพิ่มผู้ใช้สำเร็จ');
                reload_page();
            } else {
                show_alert('เพิ่มผู้ใช้ไม่สำเร็จ');
            }
            break;

        case 'edit':
            $id = $_POST['id'];
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address'],
                'permission' => $_POST['permission']
            ];

            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    show_alert('รหัสผ่านไม่ตรงกัน');
                    break;
                }
                $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if (update_by_id('user', $id, $userData)) {
                show_alert('แก้ไขผู้ใช้สำเร็จ');
                reload_page();
            } else {
                show_alert('แก้ไขผู้ใช้ไม่สำเร็จ');
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            if (delete_by_id('user', $id)) {
                show_alert('ลบผู้ใช้สำเร็จ');
                reload_page();
            } else {
                show_alert('ลบผู้ใช้ไม่สำเร็จ');
            }
            break;
    }
}
?>

<div class="container-fluid mb-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>รายการผู้ใช้</h2>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-circle me-2"></i>เพิ่มผู้ใช้ใหม่
        </button>
    </div>

    <!-- Add search card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="users">
                <div class="row g-3">
                    <div class="col-md-7">
                        <input type="text" class="form-control" name="search" 
                            placeholder="ค้นหาด้วยชื่อผู้ใช้, อีเมล, ชื่อ-นามสกุล..." 
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="permission">
                            <option value="">ทุกระดับผู้ใช้</option>
                            <?php foreach ($permissions as $perm): ?>
                                <option value="<?php echo $perm['value']; ?>" 
                                    <?php echo $permission_filter === $perm['value'] ? 'selected' : ''; ?>>
                                    <?php echo $perm['label']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-0 overflow-y-auto">
            <div class="table-responsive rounded-3" style="min-height: 15rem;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>อีเมล</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>ที่อยู่</th>
                            <th class="text-center">ระดับผู้ใช้</th>
                            <th class="text-center">วันที่สร้าง</th>
                            <th class="text-center">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0) : ?>
                            <?php $i = 1; ?>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td class="align-middle text-center"><?php echo $i++; ?></td>
                                    <td class="align-middle"><?php echo $user['username']; ?></td>
                                    <td class="align-middle"><?php echo $user['email']; ?></td>
                                    <td class="align-middle"><?php echo $user['firstname'] . ' ' . $user['lastname']; ?></td>
                                    <td class="align-middle"><?php echo $user['phone']; ?></td>
                                    <td class="align-middle">
                                        <button type="button" 
                                            class="btn btn-sm btn-outline-dark address-popover" 
                                            data-bs-toggle="popover" 
                                            data-bs-placement="left" 
                                            data-bs-html="true"
                                            data-bs-title="ที่อยู่"
                                            data-address="<?php echo htmlspecialchars($user['address']); ?>">
                                            ดูที่อยู่
                                        </button>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php 
                                            $permission = get_permission_status($user['permission']);
                                        ?>
                                        <span class="badge bg-<?php echo $permission[0]; ?>">
                                            <?php echo $permission[1]; ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td class="align-middle text-center dropdown">
                                        <button type="button" class="btn w-100 border-0" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal" 
                                                    data-id="<?php echo $user['id']; ?>">
                                                    แก้ไข
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                    ลบ
                                                </button>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">ไม่พบข้อมูลผู้ใช้</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Add pagination -->
            <div class="d-flex justify-content-between align-items-center p-3">
                <div>
                    แสดง <?php echo count($users); ?> รายการ จากทั้งหมด <?php echo $total_users; ?> รายการ
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=users&p=<?php echo $current_page-1; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($permission_filter); ?>">ก่อนหน้า</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=users&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($permission_filter); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=users&p=<?php echo $current_page+1; ?>&search=<?php echo urlencode($search); ?>&permission=<?php echo urlencode($permission_filter); ?>">ต่อไป</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">เพิ่มผู้ใช้ใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                    placeholder="กรุณากรอกชื่อผู้ใช้" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    placeholder="example@email.com" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="firstname" class="form-label">ชื่อ</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" 
                                            placeholder="กรุณากรอกชื่อ" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lastname" class="form-label">นามสกุล</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" 
                                            placeholder="กรุณากรอกนามสกุล" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                    placeholder="0xx-xxx-xxxx" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label">ที่อยู่</label>
                                <textarea class="form-control" id="address" name="address" rows="4" 
                                    placeholder="กรุณากรอกที่อยู่" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="permission" class="form-label">ระดับผู้ใช้</label>
                                <select class="form-select" id="permission" name="permission" required>
                                    <option value="" selected disabled>กรุณาเลือกระดับผู้ใช้</option>
                                    <?php
                                        foreach ($permissions as $perm) {
                                            echo "<option value='{$perm['value']}' class='text-{$perm['color']}'>{$perm['label']}</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">รหัสผ่าน</label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                            placeholder="กรุณากรอกรหัสผ่าน" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                            placeholder="กรุณายืนยันรหัสผ่าน" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="addUserForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- edit user modal -->
<div class="modal" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">แก้ไขผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required readonly>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_firstname" class="form-label">ชื่อ</label>
                                        <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_lastname" class="form-label">นามสกุล</label>
                                        <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_address" class="form-label">ที่อยู่</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_permission" class="form-label">ระดับผู้ใช้</label>
                                <select class="form-select" id="edit_permission" name="permission" required>
                                    <?php foreach ($permissions as $perm): ?>
                                        <option value="<?php echo $perm['value']; ?>" 
                                                class="text-<?php echo $perm['color']; ?>">
                                            <?php echo $perm['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_password" class="form-label">รหัสผ่านใหม่</label>
                                        <input type="password" class="form-control" id="edit_password" name="password" 
                                            placeholder="เว้นว่างถ้าไม่ต้องการเปลี่ยน">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                        <input type="password" class="form-control" id="edit_confirm_password" 
                                            name="confirm_password" placeholder="เว้นว่างถ้าไม่ต้องการเปลี่ยน">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="editUserForm" class="btn btn-primary">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all popovers
    const popoverTriggerList = document.querySelectorAll('.address-popover');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => {
        const address = popoverTriggerEl.dataset.address;
        return new bootstrap.Popover(popoverTriggerEl, {
            trigger: 'click',
            html: true,
            sanitize: false,
            template: `
                <div class="popover" role="tooltip">
                    <div class="popover-arrow"></div>
                    <h3 class="popover-header"></h3>
                    <div class="popover-body"></div>
                </div>
            `,
            content: `
                <div class="p-2">
                    <p class="mb-2">${address}</p>
                    <button type="button" class="btn btn-sm btn-dark copy-address" data-address="${address}">
                        <i class="bi bi-clipboard me-1"></i>คัดลอก
                    </button>
                </div>
            `
        });
    });

    // Close other popovers when opening a new one
    document.addEventListener('click', function(e) {
        const target = e.target.closest('.address-popover');
        if (target) {
            popoverList.forEach(popover => {
                if (popover._element !== target) {
                    popover.hide();
                }
            });
        }
    });

    // Handle popover shown event
    document.body.addEventListener('shown.bs.popover', function (e) {
        const popover = document.querySelector('.popover.show');
        if (popover) {
            const copyBtn = popover.querySelector('.copy-address');
            if (copyBtn) {
                copyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const address = this.dataset.address;
                    navigator.clipboard.writeText(address).then(() => {
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<i class="bi bi-check-lg me-1"></i>คัดลอกแล้ว';
                        
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                        }, 2000);
                    });
                });
            }
        }
    });

    // Close popover when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.address-popover') && 
            !e.target.closest('.popover')) {
            popoverList.forEach(popover => {
                popover.hide();
            });
        }
    });

    document.querySelectorAll('[data-bs-target="#editUserModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            fetch(`../core/helpers/get_data.php?type=user&id=${userId}`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('edit_id').value = user.id;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_firstname').value = user.firstname;
                    document.getElementById('edit_lastname').value = user.lastname;
                    document.getElementById('edit_phone').value = user.phone;
                    document.getElementById('edit_address').value = user.address;
                    document.getElementById('edit_permission').value = user.permission;
                });
        });
    });

    window.deleteUser = function(id) {
        if (confirm('คุณต้องการลบผู้ใช้นี้ใช่หรือไม่?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
});
</script> 