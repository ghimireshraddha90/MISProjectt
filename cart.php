<?php
include 'config.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$message = [];

if (!$user_id) {
  header('location:login.php');
  exit;
}

if (isset($_POST['update_cart'])) {
  // Sanitize inputs
  $cart_id = (int)$_POST['cart_id'];
  $cart_quantity = (int)$_POST['cart_quantity'];
  
  // Validate inputs
  if ($cart_quantity < 1) {
    $cart_quantity = 1;
  }
  
  // Use prepared statement for updating cart
  $update_stmt = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
  $update_stmt->bind_param("iii", $cart_quantity, $cart_id, $user_id);
  
  if ($update_stmt->execute()) {
    $message[] = 'Cart Quantity Updated';
  } else {
    $message[] = 'Failed to update cart';
  }
  $update_stmt->close();
}

if (isset($_GET['delete'])) {
  // Sanitize and validate the delete ID
  $delete_id = (int)$_GET['delete'];
  
  // Use prepared statement for deleting item
  $delete_stmt = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
  $delete_stmt->bind_param("ii", $delete_id, $user_id);
  $delete_stmt->execute();
  $delete_stmt->close();
  
  header('location:cart.php');
  exit;
}

if (isset($_GET['delete_all'])) {
  // Use prepared statement for deleting all items
  $delete_all_stmt = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
  $delete_all_stmt->bind_param("i", $user_id);
  $delete_all_stmt->execute();
  $delete_all_stmt->close();
  
  header('location:cart.php');
  exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>
  
<?php include 'user_header.php'; ?>

<!-- Display messages if any -->
<?php if (!empty($message)): ?>
  <div class="message-container">
    <?php foreach ($message as $msg): ?>
      <div class="message"><?php echo htmlspecialchars($msg); ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<section class="shopping_cart">
  <h1>Products Added</h1>

  <div class="cart_box_cont">
    <?php
    $grand_total = 0;
    
    // Use prepared statement for fetching cart items
    $select_stmt = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $select_stmt->bind_param("i", $user_id);
    $select_stmt->execute();
    $select_cart = $select_stmt->get_result();

    if ($select_cart->num_rows > 0) {
      while ($fetch_cart = $select_cart->fetch_assoc()) {
    ?>
    <div class="cart_box">
      <a href="cart.php?delete=<?php echo htmlspecialchars($fetch_cart['id']); ?>" class="fas fa-times" 
         onclick="return confirm('Are you sure you want to delete this product from cart?');"></a>
      <img src="./uploaded_img/<?php echo htmlspecialchars($fetch_cart['image']); ?>" alt="Product image">
      <h3><?php echo htmlspecialchars($fetch_cart['name']); ?></h3>
      <p>Rs. <?php echo htmlspecialchars($fetch_cart['price']); ?>/-</p>

      <form action="" method="post">
        <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($fetch_cart['id']); ?>">
        <input type="number" name="cart_quantity" min="1" value="<?php echo htmlspecialchars($fetch_cart['quantity']); ?>" class="quantity-input">
        <input type="submit" value="Update" name="update_cart" class="product_btn">
      </form>
      
      <?php 
        // Calculate subtotal with type safety
        $price = (float)$fetch_cart['price'];
        $quantity = (int)$fetch_cart['quantity'];
        $sub_total = $price * $quantity;
        $grand_total += $sub_total;
      ?>
      
      <p>Total: <span>Rs. <?php echo htmlspecialchars(number_format($sub_total, 2)); ?>/-</span></p>
    </div>
    <?php
      }
      $select_stmt->close();
    } else {
      echo '<p class="empty">Your Cart is Empty!</p>';
    }
    ?>
  </div>

  <div class="cart_total">
    <h2>Total Cart Price: <span>Rs. <?php echo htmlspecialchars(number_format($grand_total, 2)); ?>/-</span></h2>
    <div class="btns_cart">
      <a href="cart.php?delete_all" class="product_btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>" 
         onclick="return confirm('Are you sure you want to delete all cart items?');">Delete All</a>
      <a href="shop.php" class="product_btn">Continue Shopping</a>
      <a href="checkout.php" class="product_btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Checkout</a>
    </div>
  </div>
    
</section>

<?php include 'footer.php'; ?>

<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>
<script src="script.js"></script>

</body>
</html>