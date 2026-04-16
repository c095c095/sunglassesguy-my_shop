<?php
if (is_auth()) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $check_sql = "SELECT * FROM user WHERE (email = '$email' OR phone = '$phone') AND id != '" . $_SESSION['uid'] . "'";
        $checK_query = query($check_sql);
        $checK_result = fetch($checK_query);

        if (get_num_rows($checK_query) > 0) {
            show_alert('อีเมลหรือเบอร์โทรศัพท์นี้ถูกใช้งานแล้ว');
        } else {
            $result = update_by_id('user', $_SESSION['uid'], [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'phone' => $phone,
                'address' => $address
            ]);

            if ($result) {
                update_session_user();
                show_alert('บันทึกข้อมูลสำเร็จ');
                redirect_to('profile');
            } else {
                show_alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง');
            }
        }
    }

    $user = get_session_user();
?>
    <div class="container mt-4">
        <p class="fs-2 text-center">แก้ไขข้อมูลส่วนตัว</p>
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
                                <div class="col-12 col-lg-6 mb-3">
                                    <label for="firstname" class="form-label">ชื่อจริง<span class="text-danger">*</span></label>
                                    <input type="text" name="firstname" id="firstname" class="form-control py-2" placeholder="ชื่อจริง*" maxlength="120" required value="<?php echo $user['firstname'] ?>">
                                </div>
                                <div class="col-12 col-lg-6 mb-3">
                                    <label for="lastname" class="form-label">นามสกุล<span class="text-danger">*</span></label>
                                    <input type="text" name="lastname" id="lastname" class="form-control py-2" placeholder="นามสกุล" maxlength="120" required value="<?php echo $user['lastname'] ?>">
                                </div>
                                <div class="col-12 col-lg-6 mb-3">
                                    <label for="email" class="form-label">อีเมล<span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control py-2" placeholder="อีเมล" maxlength="255" required value="<?php echo $user['email'] ?>">
                                </div>
                                <div class="col-12 col-lg-6 mb-3">
                                    <label for="phone" class="form-label">เบอร์โทรศัพท์<span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" id="phone" class="form-control py-2" placeholder="เบอร์โทรศัพท์" maxlength="255" required value="<?php echo $user['phone'] ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">ที่อยู่<span class="text-danger">*</span></label>
                                    <textarea name="address" id="address" class="form-control py-2" placeholder="ที่อยู่" rows="4" required><?php echo $user['address'] ?></textarea>
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
