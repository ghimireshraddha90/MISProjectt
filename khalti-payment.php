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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Payment - Order #<?php echo $order['id']; ?></title>
    
    <!-- Khalti Checkout Script -->
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
    
    <style>
            * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #5C2D91, #7B4397);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-header h1 {
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .payment-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .order-details {
            padding: 30px;
            background: #f8f9ff;
        }
        
        .order-details h3 {
            color: #5C2D91;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #333;
        }
        
        .detail-value {
            color: #666;
            text-align: right;
            max-width: 60%;
        }
        
        .total-amount {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border: 2px solid #5C2D91;
            text-align: center;
        }
        
        .total-amount .amount {
            font-size: 32px;
            font-weight: bold;
            color: #5C2D91;
        }
        
        .payment-section {
            padding: 30px;
            text-align: center;
        }
        
        .khalti-btn {
            background: linear-gradient(135deg, #5C2D91, #7B4397);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(92, 45, 145, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .khalti-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(92, 45, 145, 0.4);
        }
        
        .khalti-btn:active {
            transform: translateY(0);
        }
        
        .khalti-logo {
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #5C2D91;
            font-weight: bold;
        }
        
        .payment-status {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .payment-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .payment-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .payment-status.processing {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .security-info {
            background: #f8f9ff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            border-left: 4px solid #5C2D91;
        }
        
        .security-info h4 {
            color: #5C2D91;
            margin-bottom: 10px;
        }
        
        .security-info p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .cancel-btn {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            margin-left: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .cancel-btn:hover {
            background: #5a6268;
        }
        
        @media (max-width: 768px) {
            .payment-container {
                margin: 20px auto;
                border-radius: 10px;
            }
            
            .payment-header, .order-details, .payment-section {
                padding: 20px;
            }
            
            .khalti-btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .cancel-btn {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Payment Header -->
        <div class="payment-header">
            <h1>Complete Your Payment</h1>
            <p>Secure payment powered by Khalti</p>
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
            <h3>Order Summary</h3>
            <div class="detail-row">
                <span class="detail-label">Order ID:</span>
                <span class="detail-value">#<?php echo $order['id']; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Customer:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['number']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Products:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['total_products']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Delivery Address:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['address']); ?></span>
            </div>
            
            <div class="total-amount">
                <div>Total Amount</div>
                <div class="amount">Rs. <?php echo number_format($order['total_price'], 2); ?></div>
            </div>
        </div>
        
        <!-- Payment Section -->
        <div class="payment-section">
            <button id="payment-button" class="khalti-btn">
                <span class="khalti-logo">K</span>
                Pay with Khalti
            </button>
            <button onclick="cancelPayment()" class="cancel-btn">Cancel Order</button>
            
            <div id="payment-status" class="payment-status"></div>
            
            <div class="security-info">
                <h4>🔒 Secure Payment</h4>
                <p>Your payment information is encrypted and secure. Khalti supports various payment methods including digital wallet, online banking, and mobile banking.</p>
            </div>
        </div>
    </div>

    <script>
        // Order details from PHP
        const ORDER_ID = <?php echo json_encode($order['id']); ?>;
        const TOTAL_AMOUNT = <?php echo json_encode($order['total_price'] * 100); ?>; // Convert to paisa
        const USER_NAME = <?php echo json_encode($order['name']); ?>;
        const USER_EMAIL = <?php echo json_encode($order['email']); ?>;
        const USER_PHONE = <?php echo json_encode($order['number']); ?>;

        // Initialize Khalti Checkout
        var config = {
            // Replace with your actual Khalti test public key
            "publicKey": "test_public_key_dc74e0fd57cb46cd93832aee0a390234",
            "productIdentity": "order_" + ORDER_ID,
            "productName": "E-commerce Order #" + ORDER_ID,
            "productUrl": window.location.origin + "/order-details.php?id=" + ORDER_ID,
            "paymentPreference": [
                "KHALTI",
                "EBANKING", 
                "MOBILE_BANKING",
                "CONNECT_IPS",
                "SCT"
            ],
            "eventHandler": {
                onSuccess(payload) {
                    console.log("Payment successful:", payload);
                    showStatus('processing', 'Payment successful! Verifying transaction...');
                    verifyPayment(payload);
                },
                
                onError(error) {
                    console.log("Payment error:", error);
                    showStatus('error', 'Payment failed: ' + (error.message || 'Unknown error occurred'));
                },
                
                onClose() {
                    console.log('Payment widget closed');
                }
            }
        };

        var checkout = new KhaltiCheckout(config);
        
        // Event listener for payment button
        document.getElementById('payment-button').addEventListener('click', function() {
            // Show Khalti checkout
            checkout.show({
                amount: TOTAL_AMOUNT,
                mobile: USER_PHONE,
                email: USER_EMAIL
            });
        });

        // Verify payment on server
        function verifyPayment(payload) {
            fetch('verify-khalti-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: payload.token,
                    amount: payload.amount,
                    order_id: ORDER_ID
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus('success', 'Payment verified successfully! Redirecting...');
                    setTimeout(() => {
                        window.location.href = 'payment-success.php?order_id=' + ORDER_ID;
                    }, 2000);
                } else {
                    showStatus('error', 'Payment verification failed: ' + (data.message || 'Please contact support'));
                }
            })
            .catch(error => {
                console.error('Verification error:', error);
                showStatus('error', 'Payment verification failed. Please contact support.');
            });
        }

        // Show payment status
        function showStatus(type, message) {
            const statusDiv = document.getElementById('payment-status');
            statusDiv.className = 'payment-status ' + type;
            statusDiv.textContent = message;
            statusDiv.style.display = 'block';
            
            // Auto-hide after 10 seconds for error messages
            if (type === 'error') {
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 10000);
            }
        }

        // Cancel payment
        function cancelPayment() {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('cancel-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: ORDER_ID
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'checkout.php';
                    } else {
                        alert('Failed to cancel order. Please try again.');
                    }
                });
            }
        }

        // Auto-refresh page if payment is pending for too long
        setTimeout(() => {
            const statusDiv = document.getElementById('payment-status');
            if (statusDiv.style.display === 'none') {
                showStatus('error', 'Payment session expired. Please try again.');
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>