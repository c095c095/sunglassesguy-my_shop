<?php
error_reporting(0);
header('Content-Type: application/json');
include_once __DIR__ . "/../services/query.php";

session_start();
if (!isset($_SESSION['permission']) || $_SESSION['permission'] != 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get the type of data to fetch
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID is required']);
    exit;
}

switch ($type) {
    case 'banner':
        $result = get_by_id('banner', $id);
        if (get_num_rows($result) > 0) {
            $banner = fetch($result, 2);
            echo json_encode($banner);
        } else {
            echo json_encode(['error' => 'Banner not found']);
        }
        break;

    case 'product':
        $result = get_by_id('product', $id);
        if (get_num_rows($result) > 0) {
            $product = fetch($result, 2);
            echo json_encode($product);
        } else {
            echo json_encode(['error' => 'Product not found']);
        }
        break;

    case 'user':
        $result = get_by_id('user', $id);
        $user = fetch($result, 2);
        
        if ($user) {
            // Remove sensitive data before sending
            unset($user['password']);
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
        break;

    case 'order':
        // Get order details
        $order_result = get_by_id('order', $id);
        if (get_num_rows($order_result) === 0) {
            echo json_encode(['error' => 'Order not found']);
            exit;
        }
        $order = fetch($order_result, 2);

        // Get buyer information
        $buyer_result = get_by_id('user', $order['user_id']);
        $buyer = null;
        if (get_num_rows($buyer_result) > 0) {
            $buyer = fetch($buyer_result, 2);
            // Remove sensitive information
            unset($buyer['password']);
            unset($buyer['remember_token']);
        }

        // Get order items
        $items_result = get_by_condition('order_detail', ['order_id' => $id]);
        $items = fetch($items_result);

        // Get payment information if exists
        $payment_result = get_by_condition('payment', ['order_id' => $id]);
        $payment = null;
        if (get_num_rows($payment_result) > 0) {
            $payment_data = fetch($payment_result, 2);
            
            // Get bank information
            $bank_result = get_by_id('bank', $payment_data['bank_id']);
            $bank = fetch($bank_result, 2);
            
            $payment = array_merge($payment_data, [
                'bank_name' => $bank['name']
            ]);
        }

        echo json_encode([
            'order' => $order,
            'items' => $items,
            'payment' => $payment,
            'buyer' => $buyer
        ]);
        break;

    default:
        echo json_encode(['error' => 'Invalid data type']);
        break;
} 