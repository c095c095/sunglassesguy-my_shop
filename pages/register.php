<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql_check = "SELECT * FROM user WHERE email = '$email' OR phone = '$phone'";
    $query_check = query($sql_check);
    $result_check = fetch($query_check);

    if (get_num_rows($query_check) > 0) {
        show_alert('อีเมลหรือเบอร์โทรศัพท์นี้ถูกใช้งานแล้ว');
    } else {
        $data = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'username' => $username,
            'password' => $password,
            'permission' => 1
        ];

        if (insert('user', $data)) {
            show_alert('สมัครสมาชิกเรียบร้อย!');
            redirect_to('login');
        } else {
            show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
        }
    }
}
?>
<div class="container pt-5">
    <div class="text-center my-3">
        <p class="display-5 fw-bold">ยินดีต้อนรับสู่ ร้าน ก.กีฬา</p>
        <p class="h5">กรอกแบบฟอร์มด้านล่างเพื่อเริ่มต้นการสร้างบัญชีของคุณ <span class="text-primary">ฟรี</span></p>
    </div>
    <div class="d-flex justify-content-center align-items-center">
        <div class="" style="width: 28rem;">
            <form action="" method="post">
                <div class="mb-4">
                    <label for="firstname" class="form-label">ชื่อจริง<span class="text-danger">*</span></label>
                    <input type="text" name="firstname" id="firstname" placeholder="ชื่อจริง*" class="form-control py-2" maxlength="120" required>
                </div>
                <div class="mb-4">
                    <label for="lastname" class="form-label">นามสกุล<span class="text-danger">*</span></label>
                    <input type="text" name="lastname" id="lastname" placeholder="นามสกุล*" class="form-control py-2" maxlength="120" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class="form-label">เบอร์โทรศัพท์<span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phone" placeholder="เบอร์โทรศัพท์*" class="form-control py-2" maxlength="16" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="form-label">อีเมล<span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" placeholder="อีเมล*" class="form-control py-2" maxlength="255" required>
                </div>
                <div class="mb-4">
                    <label for="address" class="form-label">ที่อยู่<span class="text-danger">*</span></label>
                    <textarea name="address" id="address" placeholder="ที่อยู่*" class="form-control py-2" rows="4" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="username" class="form-label">ชื่อผู้ใช้<span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" placeholder="ชื่อผู้ใช้*" class="form-control py-2" maxlength="255" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">รหัสผ่าน<span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" placeholder="รหัสผ่าน*" class="form-control py-2" maxlength="255" required>
                </div>
                <div class="mb-4 form-check">
                    <input type="checkbox" name="agree" id="agree" class="form-check-input" required>
                    <label for="agree" class="form-check-label" style="line-height: 25px;">ข้าพเจ้าได้อ่านและยอมรับ <a href="?page=tos-and-privacy#terms" target="_blank">เงื่อนไขการให้บริการ</a> และ <a href="?page=tos-and-privacy#privacy" target="_blank">ข้อตกลงความเป็นส่วนตัว</a> แล้ว</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-4 py-2">สร้างบัญชี</button>
                <p class="text-center my-3">หรือ</p>
                <a href="?page=login" class="btn btn-outline-secondary w-100 py-2">เข้าสู่ระบบ</a>
            </form>
        </div>
    </div>
</div>