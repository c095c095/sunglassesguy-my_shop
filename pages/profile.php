<?php

if (is_auth()) {
    $user = get_session_user();

    // if status = 0 then it's canceled
    // if status = 1 then it's unpaid
    // if status = 2 then it's pending
    // if status = 3 then it's verified
    // if status = 4 then it's delivered

    $all = get_by_condition('order', [
        'user_id' => $_SESSION['uid']
    ]);
    
    $delivered = get_by_condition('order', [
        'user_id' => $_SESSION['uid'],
        'status' => 4
    ]);

    $pending_sql = "SELECT * FROM `order` WHERE `user_id` = " . $_SESSION['uid'] . " AND (`status` = 2 OR `status` = 3)";
    $pending = query($pending_sql);

    $unpaid = get_by_condition('order', [
        'user_id' => $_SESSION['uid'],
        'status' => 1
    ]);

    $count = [
        'all' => get_num_rows($all),
        'delivered' => get_num_rows($delivered),
        'pending' => get_num_rows($pending),
        'unpaid' => get_num_rows($unpaid)
    ];
?>
    <div class="container mt-4">
        <p class="fs-2 text-center">ข้อมูลส่วนตัว</p>
        <div class="d-flex justify-content-end gap-3 mb-3">
            <a href="?page=change-password" class="btn btn-primary">เปลี่ยนรหัสผ่าน</a>
            <a href="?page=profile-edit" class="btn btn-primary">แก้ไขข้อมูลส่วนตัว</a>
        </div>
        <div class="row">
            <div class="col-12 col-lg-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex gap-3 align-items-center">
                            <img src="assets/images/profile.png" class="w-100 rounded-pill" style="max-width: 75px;" alt="">
                            <div>
                                <p class="mb-0" style="margin-bottom: -.5rem !important;"><?php echo $_SESSION['name'] ?></p>
                                <span class="text-muted small">@<?php echo $user['username'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <p class="h5 fw-bold text-danger mb-1"><?php echo number_format($count['all']) ?></p>
                        <p class="mb-0">ทั้งหมด</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <p class="h5 fw-bold text-danger mb-1"><?php echo number_format($count['delivered']) ?></p>
                        <p class="mb-0">จัดส่งแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <p class="h5 fw-bold text-danger mb-1"><?php echo number_format($count['pending']) ?></p>
                        <p class="mb-0">รอดำเนินการ</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <p class="h5 fw-bold text-danger mb-1"><?php echo number_format($count['unpaid']) ?></p>
                        <p class="mb-0">รอชำระเงิน</p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-lg-4 mb-3">
                                <p class="mb-0 text-muted small">ชื่อ - นามสกุล</p>
                                <span><?php echo $user['firstname'], ' ', $user['lastname'] ?></span>
                            </div>
                            <div class="col-12 col-lg-4 mb-3">
                                <p class="mb-0 text-muted small">อีเมล</p>
                                <span><?php echo $user['email'] ?></span>
                            </div>
                            <div class="col-12 col-lg-4 mb-3">
                                <p class="mb-0 text-muted small">หมายเลขโทรศัพท์</p>
                                <span><?php echo $user['phone'] ?></span>
                            </div>
                            <div class="col-12">
                                <p class="mb-0 text-muted small">ที่อยู่</p>
                                <span><?php echo $user['address'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    redirect_to('home');
}
?>