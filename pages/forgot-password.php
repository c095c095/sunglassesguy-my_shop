<?php
$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 1) {
        // Step 1: Verify username and email
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';

        if (!empty($username) && !empty($email)) {
            $result = get_by_condition('user', ['username' => $username, 'email' => $email]);

            if ($result && get_num_rows($result) > 0) {
                $user = fetch($result, 2);
                $_SESSION['reset_uid'] = $user['id'];
                $_SESSION['reset_username'] = $username;
                $_SESSION['reset_email'] = $email;
                
                // Generate 6-digit OTP (mock setup)
                $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['reset_otp'] = $otp;
                
                // TODO: Send OTP to email: $email
                $step = 2;
                $success = 'OTP ได้รับการส่งไปยังอีเมลของคุณ กรุณากรอก OTP เพื่อดำเนินการต่อ';
            } else {
                $error = 'ชื่อผู้ใช้หรืออีเมลไม่ถูกต้อง';
            }
        } else {
            $error = 'กรุณากรอกชื่อผู้ใช้และอีเมล';
        }
    } elseif ($step == 2) {
        // Step 2: Verify OTP
        if (isset($_SESSION['reset_uid']) && isset($_SESSION['reset_otp'])) {
            $otp_input = $_POST['otp'] ?? '';

            if (!empty($otp_input)) {
                if ($otp_input == $_SESSION['reset_otp']) {
                    $step = 3;
                    $success = 'OTP ยืนยันสำเร็จ! กรุณากำหนดรหัสผ่านใหม่';
                } else {
                    $error = 'OTP ไม่ถูกต้อง';
                }
            } else {
                $error = 'กรุณากรอก OTP';
            }
        } else {
            $error = 'เซสชันหมดอายุ กรุณาเริ่มต้นใหม่';
            $step = 1;
            unset($_SESSION['reset_uid']);
            unset($_SESSION['reset_username']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
        }
    } elseif ($step == 3) {
        // Step 3: Reset password
        if (isset($_SESSION['reset_uid'])) {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!empty($new_password) && !empty($confirm_password)) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 6) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $updated = update_by_id('user', $_SESSION['reset_uid'], ['password' => $hashed_password]);

                        if ($updated) {
                            unset($_SESSION['reset_uid']);
                            unset($_SESSION['reset_username']);
                            unset($_SESSION['reset_email']);
                            unset($_SESSION['reset_otp']);
                            show_alert('รหัสผ่านเปลี่ยนแปลงสำเร็จ! กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่');
                            redirect_to('login');
                        } else {
                            $error = 'เกิดข้อผิดพลาด กรุณาลองอีกครั้ง';
                        }
                    } else {
                        $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
                    }
                } else {
                    $error = 'รหัสผ่านไม่ตรงกัน';
                }
            } else {
                $error = 'กรุณากรอกรหัสผ่านให้ครบถ้วน';
            }
        } else {
            $error = 'เซสชันหมดอายุ กรุณาเริ่มต้นใหม่';
            $step = 1;
            unset($_SESSION['reset_uid']);
            unset($_SESSION['reset_username']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
        }
    }
}

// Check if user has valid session for step 2 and 3
if ($step == 2 && !isset($_SESSION['reset_uid'])) {
    $step = 1;
}
if ($step == 3 && !isset($_SESSION['reset_uid'])) {
    $step = 1;
}
?>

<div class="container pt-5">
    <div class="text-center my-3">
        <p class="display-5 fw-bold">รีเซ็ตรหัสผ่าน</p>
        <p class="h5">
            <?php 
            if ($step == 1) {
                echo 'ขั้นตอนที่ 1: ยืนยันตัวตน';
            } elseif ($step == 2) {
                echo 'ขั้นตอนที่ 2: ยืนยัน OTP';
            } elseif ($step == 3) {
                echo 'ขั้นตอนที่ 3: กำหนดรหัสผ่านใหม่';
            }
            ?>
        </p>
    </div>

    <div class="d-flex justify-content-center align-items-center">
        <div class="" style="width: 28rem;">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <!-- Step 1: Verify Username and Email -->
                <form action="?page=forgot-password&step=1" method="post">
                    <div class="mb-4">
                        <label for="username" class="form-label">ชื่อผู้ใช้<span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" placeholder="ชื่อผู้ใช้" class="form-control py-2" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">อีเมล<span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" placeholder="อีเมลของคุณ" class="form-control py-2" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4 py-2">ยืนยันตัวตน</button>

                    <p class="text-center my-3">
                        <a href="?page=login" class="text-primary text-decoration-none">กลับไปเข้าสู่ระบบ</a>
                    </p>
                </form>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: Verify OTP -->
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    OTP: <code><?php echo $_SESSION['reset_otp']; ?></code>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <form action="?page=forgot-password&step=2" method="post">
                    <div class="mb-3">
                        <p class="text-muted small">อีเมล: <strong><?php echo htmlspecialchars(substr($_SESSION['reset_email'], 0, 3) . '***' . substr($_SESSION['reset_email'], -10)); ?></strong></p>
                    </div>

                    <div class="mb-4">
                        <label for="otp" class="form-label">OTP (6 หลัก)<span class="text-danger">*</span></label>
                        <input type="text" name="otp" id="otp" placeholder="กรอก OTP 6 หลัก" class="form-control py-2 text-center fs-4 tracking-wider" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required>
                        <small class="text-muted">โปรดตรวจสอบอีเมลของคุณเพื่อรับ OTP</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4 py-2">ยืนยัน OTP</button>

                    <p class="text-center my-3">
                        <a href="?page=forgot-password" class="text-primary text-decoration-none small">เริ่มต้นใหม่</a>
                    </p>
                </form>

            <?php elseif ($step == 3): ?>
                <!-- Step 3: Reset Password -->
                <form action="?page=forgot-password&step=3" method="post">
                    <div class="mb-3">
                        <p class="text-muted small">ชื่อผู้ใช้: <strong><?php echo htmlspecialchars($_SESSION['reset_username']); ?></strong></p>
                    </div>

                    <div class="mb-4">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่<span class="text-danger">*</span></label>
                        <input type="password" name="new_password" id="new_password" placeholder="รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)" class="form-control py-2" required minlength="6">
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน<span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="ยืนยันรหัสผ่าน" class="form-control py-2" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4 py-2">เปลี่ยนรหัสผ่าน</button>
            <?php endif; ?>
        </div>
    </div>
</div>
