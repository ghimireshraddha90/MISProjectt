<?php
include 'config.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (isset($_POST['add_to_cart'])) {
  if (!$user_id) {
    header('Location: login.php');
    exit;
  }

  $pro_name = $_POST['product_name'];
  $pro_price = $_POST['product_price'];
  $pro_quantity = $_POST['product_quantity'];
  $pro_image = $_POST['product_image'];

  // Check product stock
  $stock_check = mysqli_query($conn, "SELECT quantity FROM `products` WHERE name='$pro_name'") or die('query failed');
  $product_data = mysqli_fetch_assoc($stock_check);
  $available_qty = $product_data['quantity'];

  if ($pro_quantity > $available_qty) {
    $message[] = 'Requested quantity exceeds available stock!';
  } else {
    // Check if product already in cart
    $check = mysqli_query($conn, "SELECT * FROM `cart` WHERE name='$pro_name' AND user_id='$user_id'") or die('query failed');

    if (mysqli_num_rows($check) > 0) {
      $message[] = 'Product already in cart!';
    } else {
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES ('$user_id', '$pro_name', '$pro_price', '$pro_quantity', '$pro_image')") or die('query2 failed');
      $message[] = 'Product added to cart!';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>
  
<?php include 'user_header.php'; ?>

<section class="products_cont">
  <div class="pro_box_cont">
    <?php
    $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');

    if (mysqli_num_rows($select_products) > 0) {
      while ($fetch_products = mysqli_fetch_assoc($select_products)) {
    ?>
        <form action="" method="post" class="pro_box">
          <img src="./uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
          <h3><?php echo $fetch_products['name']; ?></h3>
          <p>Rs. <?php echo $fetch_products['price']; ?>/-</p>

          <div class="stock-info <?php echo ($fetch_products['quantity'] <= 5) ? 'low-stock' : ''; ?>">
              Available: <?php echo $fetch_products['quantity']; ?> in stock
            </div>
          

          <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
          <input type="number" name="product_quantity" min="1" max="<?php echo $fetch_products['quantity']; ?>" value="1" required>
          <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
          <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">

          <?php if ($fetch_products['quantity'] > 0): ?>
            <?php if ($user_id): ?>
              <input type="submit" value="Add to Cart" name="add_to_cart" class="product_btn">
            <?php else: ?>
              <a href="login.php" class="product_btn">Login to Add</a>
            <?php endif; ?>
          <?php else: ?>
            <p class="out_of_stock">Out of Stock</p>
          <?php endif; ?>
        </form>
    <?php
      }
    } else {
      echo '<p class="empty">No Products Added Yet!</p>';
    }
    ?>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>
<script src="script.js"></script>

</body>
</html>
