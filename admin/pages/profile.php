<?php
$user = get_session_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
        
    switch ($action) {
        case 'update_profile':
            // Check if email is already taken by another user
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $uid = (int)$_SESSION['uid'];
            $check_email_sql = "SELECT * FROM `user` WHERE `email` = '$email' AND `id` != $uid";
            $check_email = query($check_email_sql);
            
            if (get_num_rows($check_email) > 0) {
                show_alert('อีเมลนี้ถูกใช้งานแล้ว');
                break;
            }

            $userData = [
                'email' => $_POST['email'],
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address']
            ];

            if (update_by_id('user', $_SESSION['uid'], $userData)) {
                update_session_user();
                show_alert('อัพเดทข้อมูลสำเร็จ');
                reload_page();
            } else {
                show_alert('อัพเดทข้อมูลไม่สำเร็จ');
            }
            break;

        case 'change_password':
            if (!password_verify($_POST['current_password'], $user['password'])) {
                show_alert('รหัสผ่านปัจจุบันไม่ถูกต้อง');
                break;
            }

            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                show_alert('รหัสผ่านใหม่ไม่ตรงกัน');
                break;
            }

            $userData = [
                'password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT)
            ];

            if (update_by_id('user', $_SESSION['uid'], $userData)) {
                show_alert('เปลี่ยนรหัสผ่านสำเร็จ');
                reload_page();
            } else {
                show_alert('เปลี่ยนรหัสผ่านไม่สำเร็จ');
            }
            break;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ข้อมูลส่วนตัว</h2>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <img src="../assets/images/profile.png" class="rounded-circle mb-3" style="width: 150px;" alt="Profile Image">
                    <h5 class="mb-1"><?php echo $user['firstname'] . ' ' . $user['lastname']; ?></h5>
                    <p class="text-muted mb-3">@<?php echo $user['username']; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">แก้ไขข้อมูลส่วนตัว</h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" value="<?php echo $user['username']; ?>" 
                                    placeholder="ชื่อผู้ใช้" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="email" 
                                    value="<?php echo $user['email']; ?>" 
                                    placeholder="example@email.com" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" name="firstname" 
                                    value="<?php echo $user['firstname']; ?>" 
                                    placeholder="กรุณากรอกชื่อ" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" name="lastname" 
                                    value="<?php echo $user['lastname']; ?>" 
                                    placeholder="กรุณากรอกนามสกุล" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="tel" class="form-control" name="phone" 
                                value="<?php echo $user['phone']; ?>" 
                                placeholder="0xx-xxx-xxxx" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ที่อยู่</label>
                            <textarea class="form-control" name="address" rows="3" 
                                placeholder="กรุณากรอกที่อยู่" required><?php echo $user['address']; ?></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">เปลี่ยนรหัสผ่าน</h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label class="form-label">รหัสผ่านปัจจุบัน</label>
                            <input type="password" class="form-control" name="current_password" 
                                placeholder="กรุณากรอกรหัสผ่านปัจจุบัน" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" class="form-control" name="new_password" 
                                    placeholder="กรุณากรอกรหัสผ่านใหม่" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" class="form-control" name="confirm_password" 
                                    placeholder="กรุณายืนยันรหัสผ่านใหม่" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>