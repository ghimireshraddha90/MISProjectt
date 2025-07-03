<?php
session_start();
require_once 'config.php'; // Make sure this contains $conn for mysqli

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: checkout.php');
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Get order details
$query = "
    SELECT o.*, r.name as user_name, r.email as user_email 
    FROM orders o 
    JOIN register r ON o.user_id = r.id 
    WHERE o.id = ? AND o.user_id = ? AND o.payment_status = 'pending'
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: checkout.php');
    exit;
}

// Prepare Khalti KPG-2 payload
$payload = [
    "return_url" => "http://localhost/bookshopproject/verify-khalti-payment.php", // Updated to your home page
    "website_url" => "http://localhost/bookshopproject/", // Updated to your home page
    "amount" => (int)($order['total_price'] * 100), // in paisa
    "purchase_order_id" => (string)$order['id'],
    "purchase_order_name" => "Order #" . $order['id'],
    "customer_info" => [
        "name" => $order['name'],
        "email" => $order['email'],
        "phone" => $order['number']
    ]
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Authorization: key 546da859901d4600bf91a9ec153884af', // Use your test/live secret key
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

if (isset($data['payment_url'])) {
    header("Location: " . $data['payment_url']);
    exit;
                } else {
    echo "Error initiating Khalti payment: ";
    echo isset($data['detail']) ? $data['detail'] : $response;
}