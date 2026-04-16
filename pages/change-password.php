<?php
if ($_SESSION['uid'] != "") {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $password = $_POST['password'];
        $current_password = $_POST['current_password'];
        $confirm_current_password = $_POST['confirm_current_password'];

        $user = get_session_user();

        if (password_verify($password, $user['password'])) {
            if ($current_password == $confirm_current_password) {
                $result = update_by_id('user', $_SESSION['uid'], [
                    'password' => password_hash($current_password, PASSWORD_BCRYPT)
                ]);

                if ($result) {
                    show_alert('เปลี่ยนรหัสผ่านสำเร็จ');
                    redirect_to('profile');
                } else {
                    show_alert('เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน กรุณาลองใหม่อีกครั้ง');
                }
            } else {
                show_alert('รหัสผ่านใหม่ไม่ตรงกัน');
            }
        } else {
            show_alert('รหัสผ่านเดิมไม่ถูกต้อง');
        }
    }
?>
    <div class="container mt-4">
        <p class="fs-2 text-center">เปลี่ยนรหัสผ่าน</p>
        <div class="row justify-content-center">
            <div class="col col-lg-9">
                <div class="d-flex justify-content-end mb-3">
                    <a href="?page=profile" class="btn btn-primary">
                        <i class="bi bi-caret-left-fill"></i>
                        กลับไปหน้าข้อมูลส่วนตัว
                    </a>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="" method="post">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="password" class="form-label">รหัสผ่านเดิม<span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control py-2" placeholder="รหัสผ่านเดิม*" maxlength="255" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="current_password" class="form-label">รหัสผ่านใหม่<span class="text-danger">*</span></label>
                                    <input type="password" name="current_password" id="current_password" class="form-control py-2" placeholder="รหัสผ่านใหม่" maxlength="255" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="confirm_current_password" class="form-label">ใส่รหัสผ่านใหม่อีกครั้ง<span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_current_password" id="confirm_current_password" class="form-control py-2" placeholder="ใส่รหัสผ่านใหม่อีกครั้ง" maxlength="255" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">บันทึก</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    redirect_to('home');
}
