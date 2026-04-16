<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = get_by_condition('user', ['username' => $username]);

    if ($result && get_num_rows($result) > 0) {
        $user = fetch($result, 2);
        if (password_verify($password, $user['password'])) {
            $_SESSION['uid'] = $user['id'];
            $_SESSION['permission'] = $user['permission'];
            update_session_user();

            if ($_SESSION['permission'] == 2) {
                echo "<script> location.href = `./admin`; </script>";
            } elseif ($_SESSION['permission'] == 0) {
                show_alert('บัญชีของคุณถูกระงับการใช้งาน หากมีข้อสงสัยกรุณาติดต่อผู้ดูแลระบบ');
                session_destroy();
            } else {
                redirect_to('home');
            }

        } else {
            // var_dump($user);
            show_alert('รหัสผ่านไม่ถูกต้อง');
        }
    } else {
        show_alert('ชื่อผู้ใช้ไม่ถูกต้อง');
    }
}
?>
<div class="container pt-5">
    <div class="text-center my-3">
        <p class="display-5 fw-bold">ยินดีต้อนรับ</p>
        <p class="h5">เข้าสู่ระบบได้ง่ายด้วย <span class="text-primary">ชื่อผู้ใช้</span> และ <span class="text-primary">รหัสผ่าน</span></p>
    </div>
    <div class="d-flex justify-content-center align-items-center">
        <div class="" style="width: 28rem;">
            <form action="" method="post">
                <div class="mb-4">
                    <label for="username" class="form-label">ชื่อผู้ใช้<span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" placeholder="ชื่อผู้ใช้*" class="form-control py-2" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">รหัสผ่าน<span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" placeholder="รหัสผ่าน*" class="form-control py-2" required>
                    <div class="text-end mt-2">
                        <a href="?page=forgot-password" class="text-primary text-decoration-none small">ลืมรหัสผ่าน?</a>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-4 py-2">เข้าสู่ระบบ</button>
                <p class="text-center my-3">หรือ</p>
                <a href="?page=register" class="btn btn-outline-secondary w-100 py-2">สร้างบัญชี</a>
            </form>
        </div>
    </div>
</div>