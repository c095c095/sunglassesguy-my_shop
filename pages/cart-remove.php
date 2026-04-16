<?php

if (is_auth() && $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['product_id'] != '') {
    $product_id = $_POST['product_id'];

    $cart_result = get_by_condition('cart', [
        'product_id' => $product_id,
        'user_id' => $_SESSION['uid']
    ]);

    if (get_num_rows($cart_result) > 0) {
        $cart = fetch($cart_result, 2);
        $delete_result = delete_by_id('cart', $cart['id']);

        if (!$delete_result) {
            show_alert('เกิดข้อผิดพลาดในการลบสินค้าออกจากตะกร้า');
        }
    }

    redirect_to('cart');
} else {
    redirect_to('home');
}
