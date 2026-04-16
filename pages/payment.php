<?php

include_once "core/helpers/image_upload.php";

function get_color_by_status($status)
{
    switch ($status) {
        case '0':
            return ['secondary', 'ยกเลิก'];
            break;
        case '1':
            return ['secondary', 'รอชำระเงิน'];
            break;
        case '2':
            return ['primary', 'รอตรวจสอบ'];
            break;
        case '3':
            return ['primary', 'รอจัดส่ง'];
            break;
        case '4':
            return ['success', 'จัดส่งสำเร็จ'];
            break;

        default:
            return ['danger', 'ไม่ทราบสถานะ'];
            break;
    }
}

function get_bank($id)
{
    switch ($id) {
        case '1':
            return ['ธนาคารกสิกรไทย', 'k-bank.png'];
            break;
        case '2':
            return ['ธนาคารไทยพาณิชย์', 'scb.png'];
            break;
        case '3':
            return ['ธนาคารกรุงไทย', 'ktb.png'];
            break;
        case '4':
            return ['ธนาคารกรุงเทพ', 'bbl.png'];
            break;
        case '5':
            return ['ธนาคารกรุงศรีอยุธยา', 'bay.png'];
            break;
        case '6':
            return ['ธนาคารทหารไทยธนชาต', 'ttb.png'];
            break;
        case '7':
            return ['ธนาคารซีไอเอ็มบี', 'cimb.png'];
            break;
        case '8':
            return ['ธนาคารยูโอบี', 'uob.png'];
            break;
        case '9':
            return ['พร้อมเพย์', 'promptpay.png'];
            break;
        case '10':
            return ['อื่น ๆ', 'other.png'];
            break;

        default:
            return ['ไม่ทราบ', 'unknown.png'];
            break;
    }
}

if (is_auth()) {
    $order_id = $_GET['order_id'];

    $order_result = get_by_condition('order', [
        'id' => $order_id,
        'user_id' => $_SESSION['uid'],
        'status' => 1
    ]);

    if (get_num_rows($order_result) == 0) {
        redirect_to('home');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $slip = $_FILES['slip'];
        $pay_date = $_POST['pay_date'];
        $pay_time = $_POST['pay_time'];
        $bank_id = $_POST['bank_id'];

        if (is_image($slip)) {
            $slip_name = generate_image_name($slip);
            if (upload_image($slip, $slip_name, 'payment/')) {
                $result = insert('payment', [
                    'order_id' => $order_id,
                    'bank_id' => $bank_id,
                    'pay_date' => $pay_date,
                    'pay_time' => $pay_time,
                    'img' => $slip_name,
                    'submit_date' => date('Y-m-d H:i:s'),
                ]);

                if ($result) {
                    $update_result = update_by_id('order', $order_id, [
                        'status' => 2
                    ]);

                    if (!$update_result) {
                        show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
                    }

                    redirect_to('order-detail&order_id=' . $order_id);
                } else {
                    show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
                }
            } else {
                show_alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
            }
        } else {
            show_alert('ไม่สามารถอัพโหลดไฟล์ประเภทนี้ได้ รองรับเฉพาะไฟล์ JPG, JPEG, PNG, GIF, และ WEBP เท่านั้น');
        }
    }

    $order = fetch($order_result, 2);
    $order_item_result = get_by_condition('order_detail', ['order_id' => $order_id]);
    $order_items = fetch($order_item_result);
    $bank_result = get_all('bank');
    $banks = fetch($bank_result);
    $color = get_color_by_status($order['status']);
?>
    <div class="container mt-4">
        <p class="fs-2 text-center">การชำระเงิน</p>
        <a href="?page=order-detail&order_id=<?php echo $order_id ?>" class="d-block text-dark text-decoration-none mb-3">
            <i class="bi bi-chevron-left"></i>
            ย้อนกลับ
        </a>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <div class="row row-cols-1 row-cols-lg-3">
                    <div class="col">
                        <span class="text-muted small">หมายเลขคำสั่งซื้อ:</span>
                        <span><?php ?>#ORDER-<?php echo $order['id']; ?></span>
                    </div>
                    <div class="col">
                        <div class="d-none d-lg-block text-center">
                            <span class="text-muted small">วันที่ทำรายการ:</span>
                            <span><?php echo format_datetime_thai($order['order_date']) ?></span>
                        </div>
                        <div class="d-block d-lg-none text-start">
                            <span class="text-muted small">วันที่ทำรายการ:</span>
                            <span><?php echo format_datetime_thai($order['order_date']) ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-none d-lg-block text-end">
                            <span class="text-muted small">สถานะการสั่งซื้อ:</span>
                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                        </div>
                        <div class="d-block d-lg-none text-start">
                            <span class="text-muted small">สถานะการสั่งซื้อ:</span>
                            <span class="badge text-bg-<?php echo $color[0]; ?>"><?php echo $color[1] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    ยอดชำระระทั้งหมด:
                    <span class="text-danger fw-bold fs-5">฿<?php echo number_format($order['total_price'] + $order['delivery_fee'], 2) ?></span>
                </p>
                <hr style="margin-left: -1rem; margin-right: -1rem;">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3 text-center" id="upload-preview"></div>
                    <div class="mb-3">
                        <label for="slip" class="form-label">หลักฐานการโอนเงิน<span class="text-danger">*</span></label>
                        <input class="form-control py-2" type="file" id="slip" name="slip" accept=".jpg,.jpeg,.png,.gif,.webp" required>
                        <span class="text-danger small d-block">*** รองรับขนาดไฟล์สูงสุด <?php echo MAX_IMAGE_SIZE / 1024 / 1024 ?> MB</span>
                        <span class="text-danger small">*** รองรับเฉพาะไฟล์ JPG, JPEG, PNG, GIF, และ WEBP เท่านั้น</span>
                    </div>
                    <div class="row row-cols-1 row-cols-lg-2">
                        <div class="col mb-3">
                            <label for="pay_date" class="form-label">วันที่โอนเงินามหลักฐานการโอนเงิน<span class="text-danger">*</span></label>
                            <input type="date" class="form-control py-2" name="pay_date" id="pay_date" placeholder="เลือกวันที่" required>
                        </div>
                        <div class="col mb-3">
                            <label for="pay_time" class="form-label">เวลาโอนเงินามหลักฐานการโอนเงิน<span class="text-danger">*</span></label>
                            <input type="time" class="form-control py-2" name="pay_time" id="pay_time" placeholder="เลือกเวลา" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p>ธนาคาร<span class="text-danger">*</span></p>
                        <?php
                        foreach ($banks as $bank) {
                            $bank_meta = get_bank($bank['type']);
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bank_id" id="bank_<?php echo $bank['id'] ?>" value="<?php echo $bank['type'] ?>" required>
                                <label class="form-check-label" for="bank_<?php echo $bank['id'] ?>">
                                    <div class="d-flex gap-3">
                                        <img src="assets/images/banks/<?php echo $bank_meta[1] ?>" style="width: 75px; height: 75px;" alt="">
                                        <div>
                                            <p class="mb-0"><?php echo $bank_meta[0] ?></p>
                                            <p class="mb-0"><?php echo $bank['number'] ?></p>
                                            <p class="mb-0"><?php echo $bank['name'] ?></p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <button type="submit" class="btn btn-primary">แจ้งโอนเงิน</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('slip').addEventListener('change', function(event) {
            const preview = document.getElementById('upload-preview');
            preview.innerHTML = '';
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-fluid shadow-sm';
                    img.style.maxHeight = '400px';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
<?php
} else {
    redirect_to('home');
}
