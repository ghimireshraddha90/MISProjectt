<?php
session_start();
require_once 'config.php'; // Uses $conn from config

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user details
$query = "SELECT * FROM register WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Get cart items
$query = "SELECT * FROM cart WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cart_items = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Calculate total
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'];

    $total_products = '';
    foreach ($cart_items as $item) {
        $total_products .= $item['name'] . ' (' . $item['quantity'] . '), ';
    }
    $total_products = rtrim($total_products, ', ');

  $query = "INSERT INTO orders (
  user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "issssssd", $user_id, $name, $phone, $email, $payment_method, $address, $total_products, $total_amount);
mysqli_stmt_execute($stmt);


    $order_id = mysqli_insert_id($conn);

    if ($payment_method === 'khalti') {
        header("Location: khalti-payment.php?order_id=" . $order_id);
    } else {
        header("Location: order-success.php?order_id=" . $order_id);
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Your Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .order-summary, .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-summary h2, .checkout-form h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #5C2D91;
            padding-bottom: 10px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #666;
        }
        
        .item-quantity {
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 0 10px;
        }
        
        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #5C2D91;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-final {
            font-size: 18px;
            font-weight: bold;
            color: #5C2D91;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }
        
        .payment-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover, .payment-option.selected {
            border-color: #5C2D91;
            background: #f8f5ff;
        }
        
        .payment-option input {
            margin-right: 10px;
        }
        
        .khalti-option {
            background: linear-gradient(135deg, #5C2D91, #7B4397);
            color: white;
            border-color: #5C2D91;
        }
        
        .khalti-option:hover, .khalti-option.selected {
            background: linear-gradient(135deg, #4A1A7A, #6A2F85);
        }
        
        .place-order-btn {
            width: 100%;
            background: #5C2D91;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .place-order-btn:hover {
            background: #4A1A7A;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Order Summary -->
        <div class="order-summary">
            <h2>Order Summary</h2>
            
            <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="item-price">Rs. <?php echo number_format($item['price'], 2); ?></div>
                </div>
                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                <div class="item-total">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
            <?php endforeach; ?>
            
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>Rs. <?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Delivery:</span>
                    <span>Free</span>
                </div>
                <div class="total-row total-final">
                    <span>Total:</span>
                    <span>Rs. <?php echo number_format($total_amount, 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <div class="checkout-form">
            <h2>Delivery Information</h2>
            
            <form method="POST" id="checkout-form">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Delivery Address *</label>
                    <textarea id="address" name="address" placeholder="Enter your complete delivery address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Payment Method *</label>
                    <div class="payment-methods">
                        <div class="payment-option khalti-option" onclick="selectPayment('khalti')">
                            <input type="radio" name="payment_method" value="khalti" id="khalti" required>
                            <label for="khalti">
                                <strong>Khalti</strong><br>
                                <small>Digital Wallet</small>
                            </label>
                        </div>
                        
                        <div class="payment-option" onclick="selectPayment('cod')">
                            <input type="radio" name="payment_method" value="cod" id="cod" required>
                            <label for="cod">
                                <strong>Cash on Delivery</strong><br>
                                <small>Pay when delivered</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="create_order" class="place-order-btn">
                    Place Order
                </button>
            </form>
        </div>
    </div>

    <script>
        function selectPayment(method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById(method).checked = true;
        }
        
        // Auto-select payment method when radio button is clicked
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectPayment(this.value);
            });
        });
    </script>
</body>
</html>