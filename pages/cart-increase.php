<?php

if (is_auth() && $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['product_id'] != '' && $_POST['qty'] != '') {
    $product_id = $_POST['product_id'];
    $qty = $_POST['qty'];

    $cart_result = get_by_condition('cart', [
        'product_id' => $product_id,
        'user_id' => $_SESSION['uid']
    ]);

    $product_result = get_by_id('product', $product_id);
    $product = fetch($product_result, 2);

    if (get_num_rows($cart_result) > 0) {
        $cart = fetch($cart_result, 2);
        $new_qty = $cart['qty'] + $qty;

        if ($new_qty <= $product['stock']) {
            $update_result = update_by_id('cart', $cart['id'], ['qty' => $new_qty]);

            if (!$update_result) {
                show_alert('เกิดข้อผิดพลาดในการเพิ่มสินค้าลงในตะกร้า');
            }
        } else {
            show_alert('จำนวนสินค้าในสต๊อกไม่เพียงพอ');
        }
    } else {
        $insert_result = insert('cart', [
            'user_id' => $_SESSION['uid'],
            'product_id' => $product_id,
            'qty' => $qty
        ]);

        if (!$insert_result) {
            show_alert('เกิดข้อผิดพลาดในการเพิ่มสินค้าลงในตะกร้า');
        }
    }

    redirect_to('cart');
} else {
    redirect_to('home');
}
