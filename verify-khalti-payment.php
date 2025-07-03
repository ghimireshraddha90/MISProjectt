<?php
// verify-khalti-payment.php
require_once 'config.php';

if (!isset($_GET['pidx'])) {
    die('Invalid request: missing pidx.');
}
$pidx = $_GET['pidx'];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://a.khalti.com/api/v2/epayment/lookup/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
    CURLOPT_HTTPHEADER => [
        'Authorization: key 546da859901d4600bf91a9ec153884af', // Use your test/live secret key
        'Content-Type: application/json',
    ],
]);
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

if (isset($data['status']) && $data['status'] === 'Completed') {
    // Payment successful, update order status
    $order_id = isset($_GET['purchase_order_id']) ? $_GET['purchase_order_id'] : null;
    if ($order_id) {
        $stmt = $conn->prepare("UPDATE orders SET payment_status='paid' WHERE id=?");
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        // Get user_id and total_products for this order
        $user_stmt = $conn->prepare("SELECT user_id, total_products FROM orders WHERE id=?");
        $user_stmt->bind_param('i', $order_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_row = $user_result->fetch_assoc()) {
            $user_id = $user_row['user_id'];
            // Decrease stock for each product in the order
            $total_products = $user_row['total_products'];
            $products = explode(',', $total_products);
            foreach ($products as $prod) {
                if (preg_match('/(.+) \((\d+)\)/', trim($prod), $matches)) {
                    $prod_name = trim($matches[1]);
                    $prod_qty = (int)$matches[2];
                    $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE name = ?");
                    $update_stock->bind_param('is', $prod_qty, $prod_name);
                    $update_stock->execute();
                }
            }
            // Clear cart for this user
            $del_stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
            $del_stmt->bind_param('i', $user_id);
            $del_stmt->execute();
        }
        echo '<div style="max-width:500px;margin:60px auto;padding:40px 30px;background:#f8f9ff;border-radius:16px;box-shadow:0 4px 24px rgba(92,45,145,0.08);text-align:center;">';
        echo '<h2 style="color:#5C2D91;margin-bottom:16px;">Order Successful!</h2>';
        echo '<p style="font-size:18px;margin-bottom:24px;">Thank you for your payment.<br>Your order <b>#' . htmlspecialchars($order_id) . '</b> was placed successfully.</p>';
        echo '<a href="http://localhost/bookshopproject/" style="display:inline-block;padding:12px 32px;background:#5C2D91;color:#fff;border-radius:8px;font-size:16px;text-decoration:none;font-weight:600;">Go to Home</a>';
        echo '</div>';
    } else {
        echo '<div style="max-width:500px;margin:60px auto;padding:40px 30px;background:#f8f9ff;border-radius:16px;box-shadow:0 4px 24px rgba(92,45,145,0.08);text-align:center;">';
        echo '<h2 style="color:#5C2D91;margin-bottom:16px;">Order Successful!</h2>';
        echo '<p style="font-size:18px;margin-bottom:24px;">Thank you for your payment.<br>Your order was placed successfully, but the order ID could not be determined.</p>';
        echo '<a href="http://localhost/bookshopproject/" style="display:inline-block;padding:12px 32px;background:#5C2D91;color:#fff;border-radius:8px;font-size:16px;text-decoration:none;font-weight:600;">Go to Home</a>';
        echo '</div>';
    }
} else {
    echo "<h2>Payment failed or pending.</h2><pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
}
